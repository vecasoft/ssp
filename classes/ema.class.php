<?php 

class SSP_ema
{ // define the class

    // ========================================================================================
    // Aanmaken deelnemers bepaalde meeting
    //
    // In:	Meeting ID
    //
    // Return: Niets...
    // ========================================================================================

    static function CrtMeetingDeelnemers($pMeeting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(Sx::GetClassPath("_db.class"));

        // -----------
        // Get Meeting
        // -----------

        $meRec = SSP_db::Get_EMA_meRec($pMeeting);

        if (!$meRec)
            return;

        // -----------------
        // Get Meeting-groep
        // -----------------

        $groep = $meRec->meGroep;

        $mgRec = SSP_db::Get_EMA_grRec($groep);

        if (!$mgRec)
            return;

        // ---------------
        // Get groep-leden
        // ---------------

        $personen = array();

        $sqlStat = "Select * from ema_gl_groep_leden where glGroep = '$groep'";

        $db->Query($sqlStat);

        while ($glRec = $db->Row()) {

            if ($glRec->glPersoon)
                $personen[] = $glRec->glPersoon;

            if ($glRec->glRole) {

                $role = $glRec->glRole;

                $sqlStat = "Select * from sx_rp_role_personen where rpRole= '$role'";
                $db2->Query($sqlStat);

                while ($rpRec = $db2->Row())
                    $personen[] = $rpRec->rpPersoon;

            }

        }


        // ------------------------------
        // Fill "meeting deelnemers" file
        // ------------------------------

        $curDateTime = date('Y-m-d H:i:s');

        $userId = $meRec->meUserCreatie;

        foreach ($personen as $persoon) {

            $values = array();

            $values["mdMeeting"] = MySQL::SQLValue($pMeeting, MySQL::SQLVALUE_NUMBER);
            $values["mdPersoon"] = MySQL::SQLValue($persoon);
            $values["mdStatus"] = MySQL::SQLValue("*VERWACHT");

            $values["mdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["mdUserCreatie"] = MySQL::SQLValue($userId);
            $values["mdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["mdUserUpdate"] = MySQL::SQLValue($userId);
            $values["mdRecStatus"] = MySQL::SQLValue("A");

            $db->InsertRow("ema_md_meeting_deelnemers", $values);


        }

        self::UpdMeetingLevel($pMeeting);

    }

    // ========================================================================================
    //  Set status deelnemer
    //
    // In:	Meeting ID
    //      Persoon
    //      Status
    //
    // Return: Niets...
    // ========================================================================================

    static function SetDeelnemerStatus($pMeeting, $pPersoon, $pStatus = '*AANWEZIG') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update ema_md_meeting_deelnemers set mdStatus = '$pStatus' where mdMeeting = $pMeeting and mdPersoon = '$pPersoon'";

        $db->Query($sqlStat);

        self::UpdMeetingLevel($pMeeting);

    }

    // ========================================================================================
    // Opvullen Meeting-NAAM (op basis datum & type)
    //
    // In:	Meeting ID
    //
    // Return: Niets...
    // ========================================================================================

    static function UpdMeetingNaam($pMeeting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(Sx::GetClassPath("_db.class"));

        // --------------------
        // Ophalen Meeting-info
        // --------------------

        $meRec = SSP_db::Get_EMA_meRec($pMeeting);

        if (!$meRec)
            return;

        $meetingGroep = $meRec->meGroep;
        $meetingType = $meRec->meType;

        $mtRec = SSP_db::Get_EMA_mtRec($meetingGroep, $meetingType);

        if (!$mtRec)
            return;

        $meDate = strtotime($meRec->meDatum);
        $naam = $mtRec->mtNaam . " " . date("d-m-Y", $meDate);

        // ------
        // update
        // ------

        $values = array();
        $where = array();

        $values["meNaam"] = MySQL::SQLValue($naam);
        $where["meId"] = MySQL::SQLValue($pMeeting, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("ema_me_meetings", $values, $where);


    }

    // ========================================================================================
    // Wissen meeting (alle afhankelijke gegevens - deelnemers, agenda, etc)
    //
    // In:	Meeting ID
    //
    // Return: Niets...
    // ========================================================================================

    static function DltMeeting($pMeeting)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Delete From ema_me_meetings where meId = $pMeeting";
        $db->Query($sqlStat);

        $sqlStat = "Delete From ema_md_meeting_deelnemers where mdMeeting = $pMeeting";
        $db->Query($sqlStat);

        $sqlStat = "Delete From ema_ml_mail_log where vmMeeting = $pMeeting";
        $db->Query($sqlStat);

    }

    // ========================================================================================
    // Actiepunt overtijd?
    //
    // In:	actiepunt
    //
    // Return: Niets...
    // ========================================================================================

    static function IsActiepuntOvertijd($pActiepunt)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_ac_actie_punten where acId = $pActiepunt and date(acStreefdatum) < current_date";
        $db->Query($sqlStat);

        if (!$acRec = $db->Row())
            return false;

        if ($acRec->acStatus == '*UITGEVOERD')
            return false;

        return true;


    }


    // ========================================================================================
    // Bijlage verzenden naar Cloudinary
    //
    // In:	Bijlage ID
    //
    // Return: Niets...
    // ========================================================================================

    static function SndBijlageNaarCloud($pBijlage) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("cloudinary.class"));

        $sqlStat = "Select * from ema_mb_meeting_bijlagen where mbId = $pBijlage";
        $db->Query($sqlStat);

        if (!$mbRec = $db->Row())
            return;

        // ---------------------------------
        // Delete old document in cloudinary
        // ---------------------------------

        if ($mbRec->mbPublicId > ' ')
            SSP_cloudinary::DelFile($mbRec->mbPublicId);

        if (! $mbRec->mbBijlage)
            return;


        // -------
        // Bijlage
        // -------

        $url = null;
        $type = null;
        $publicId = null;
        $titel = $mbRec->mbTitel;

        if ($mbRec->mbBijlage) {


            $fileArray = my_json_decode($mbRec->mbBijlage);

            $fileName = basename($fileArray[0]["name"]);

            $origName = $fileArray[0]["usrName"];
            $origBaseName = pathinfo($origName)['filename'];

            $uploadFile = $_SESSION["SX_BASEPATH"] . '/_files/ema/' . $fileName;

            $randomString = substr(str_shuffle("23456789abcdefghjkmnprstuvwxyz"), 0, 5);
            $folder = "ema/" . $randomString;

            //$array = SSP_cloudinary::SendFile($uploadFile, $origBaseName, $folder);
            $array = SSP_cloudinary::SendFile($uploadFile, $titel, $folder);

            $url = $array["url"];
            $type = $array["format"];
            $publicId = $array["public_id"];

        }


        // ------
        // update
        // ------

        $values = array();
        $where = array();

        $values["mbURL"] = MySQL::SQLValue($url);
        $values["mbType"] = MySQL::SQLValue($type);
        $values["mbPublicId"] = MySQL::SQLValue($publicId);
        $values["mbTitel"] = MySQL::SQLValue($titel);

        $where["mbId"] = MySQL::SQLValue($pBijlage, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("ema_mb_meeting_bijlagen", $values, $where);

    }
    // ========================================================================================
    // Get HTML code voor bijlagen bepaald agendapunt
    //
    // In:	Agendapunt
    //
    // Return: HTML
    // ========================================================================================

    static function GetAgendaBijlagenHTML($pAgendapunt) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_mb_meeting_bijlagen where mbAgendapunt = $pAgendapunt order by mbSortering";

        $db->Query($sqlStat);

        $html = "";

        while ($mbRec = $db->Row()){

            if ($mbRec->mbExterneURL > " ") {

                $bijlage = "<a href='"
                    . $mbRec->mbExterneURL
                    . "' target='_blank'>"
                    . "- " . $mbRec->mbTitel
                    . " ("
                    . $mbRec->mbType
                    . ")"
                    . "</a>";

                if ($html)
                    $html .= "<br/>$bijlage";
                else
                    $html = $bijlage;
            
                
            }
  
            if ($mbRec->mbURL > " ") {

                $bijlage = "<a href='"
                    . $mbRec->mbURL
                    . "' target='_blank'>"
                    . "- " . $mbRec->mbTitel
                    . " ("
                    . $mbRec->mbType
                    . ")"
                    . "</a>";

                if ($html)
                    $html .= "<br/>$bijlage";
                else
                    $html = $bijlage;


            }

        }

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ========================================================================================
    // HTML Agenda
    //
    // In:	meeting
    //
    // Return: HTML
    // ========================================================================================

    static function GetAgendaHTML($pMeeting)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetClassPath("_db.class"));

        include_once(Sx::GetClassPath("settings.class"));

        $green = SSP_settings::GetBackgroundColor('green');
        $yellow = SSP_settings::GetBackgroundColor('yellow');
        $red = SSP_settings::GetBackgroundColor('red');
        $orange = SSP_settings::GetBackgroundColor('orange');
        $blue = SSP_settings::GetBackgroundColor('blue');
        $grey = SSP_settings::GetBackgroundColor('grey');

        // -----------
        // Progressbar
        // -----------

        $aantalAgendapunten = 0;
        $aantalBesproken = 0;
        $percentageBesproken = 0;

        $sqlStat = "Select count(*) as aantal from ema_ma_meeting_agendapunten where maMeeting = $pMeeting";
        $db->Query($sqlStat);

        if ($maRec = $db->Row())
            $aantalAgendapunten = $maRec->aantal;

        $sqlStat = "Select count(*) as aantal from ema_ma_meeting_agendapunten where maMeeting = $pMeeting and maAfgehandeld = 1";
        $db->Query($sqlStat);

        if ($maRec = $db->Row())
            $aantalBesproken = $maRec->aantal;

        if ($aantalAgendapunten)
            $percentageBesproken = round(($aantalBesproken / $aantalAgendapunten) * 100);

        $htmlPath = $_SESSION["SX_BASEPATH"] . '/ema_agenda_progressbar.html';
        $htmlProgressbar = file_get_contents($htmlPath, true);
        $htmlProgressbar = str_replace("x/y", "$aantalBesproken/$aantalAgendapunten", $htmlProgressbar);
        $htmlProgressbar = str_replace("xxx", "$percentageBesproken", $htmlProgressbar);

        $html = "<table class='table'><tr><td>$htmlProgressbar</td></tr></table>";


        $sqlStat = "Select * from ema_ma_meeting_agendapunten where maMeeting = $pMeeting order by maSortering, maId";
        $db->Query($sqlStat);

        $jsPath = $_SESSION["SX_BASEPATH"] . '/ema_agenda.js';
        $jsScript = file_get_contents($jsPath, true);
        $jsScript = '<script>' . $jsScript . '</script>';

        $agendapuntHeaderHTML = "<tr style=\"background-color: #A9E2F3 \"><th>Besproken</th><th>Actie</th><th>Vanwege</th><th>Agendapunt</th><th>Extra omschrijving</th><th>Bijlage(n)</th><th></th><th>Besluit</th></tr>";


        $html .= "<table class=\"table\">";

        while ($maRec = $db->Row()) {

            $html .= $agendapuntHeaderHTML;

            $maId = $maRec->maId;

            $vanwege = $maRec->maVanwege;
            $titel = $maRec->maTitel;
            $omschrijving = nl2br($maRec->maOmschrijving);

            $besluit = nl2br($maRec->maBesluit);

            $besluitButton = "<a type=\"button\" class=\"btn-link glyphicon glyphicon-pencil\" href=\"Agendapunten_edit.php?page=besluit&amp;editid1=4\" id=\"editLink10\" name=\"editLink10\" data-gridlink=\"\" title=\"Bewerken\" data-page=\"edit\" event508=\"true\"></a>";


            $jsPath = $_SESSION["SX_BASEPATH"] . '/ema_agenda_besluit_update.js';
            $onClick = file_get_contents($jsPath, true);
            $onClick = str_replace("editid1=X", "editid1=$maId", $onClick);
            $onClick = htmlspecialchars($onClick);

            $besluitButton = "<a type=\"button\"  href=\"javascript:void()\"  onclick=\"$onClick\" class=\"btn-link glyphicon glyphicon-pencil\" href=\"#\" title=\"Besluit\"</a>";

            $jsPath = $_SESSION["SX_BASEPATH"] . '/ema_agenda_actiepunt_add.js';
            $onClick = file_get_contents($jsPath, true);
            $onClick = str_replace("masterkey1=x", "masterkey1=$maId", $onClick);
            $onClick = htmlspecialchars($onClick);
            $actieAddButton = "<a type=\"button\"  href=\"javascript:void()\"   onclick=\"$onClick\" class=\"btn-link glyphicon glyphicon-plus-sign\" href=\"#\" title=\"Besluit\"</a>";

            // --------
            // Bijlages
            // --------

            $bijlages = self::GetAgendaBijlagenHTML($maId);


            if (!$bijlages)
                $bijlages = "&nbsp;";


            $id = $maRec->maId;

            $checked = "";
            $backgroundColor = "";
            if ($maRec->maAfgehandeld == 1)
                $checked = "checked";

            $checkbox = "<input type='checkbox' class='form-check-input checkAfgehandeld' $checked data-id='$id' value=''>&nbsp;</input>";

            if ($maRec->maAfgehandeld == 1) {
                $backgroundColor = $green;
            }

            $html .= "<tr style=\"background-color: $backgroundColor\"><td style=\"width:20px\"> $checkbox</td><td>$actieAddButton</td><td>$vanwege </td><td><b>$titel</b> </td><td>$omschrijving </td><td>$bijlages </td><td>$besluitButton</td>  <td>$besluit</td></tr>";

            // -----------
            // Actiepunten
            // -----------

            $sqlStat = "Select * from ema_ac_actie_punten where acAgendapunt = $maId";

            $db2->Query($sqlStat);

            $existActiePunten = false;

            $htmlACTIE = "<table class=\"table table-borderless\">";
            $htmlActieDetail = "";

            while ($acRec = $db2->Row()) {

                $existActiePunten = true;

                $acId = $acRec->acId;

                $acTitle = $acRec->acTitel;
                $acOmschrijving = nl2br($acRec->acOmschrijving);
                $acPersoon = $acRec->acPersoon;
                $acStreefdatum = $acRec->acStreefdatum;
                $acStreefdatum = SX_tools::EdtDate($acStreefdatum);

                $acStatus = $acRec->acStatus;
                $taRec = SSP_db::Get_SX_taRec('EMA_ACTIEPUNT_STATUS', $acStatus);
                if ($taRec)
                    $acStatus = $taRec->taName;

                $jsPath = $_SESSION["SX_BASEPATH"] . '/ema_agenda_actiepunt_update.js';
                $onClick = file_get_contents($jsPath, true);
                $onClick = str_replace("editid1=X", "editid1=$acId", $onClick);
                $onClick = htmlspecialchars($onClick);

                $actieUpdateButton = "<a type=\"button\" href=\"javascript:void(0)\"   onclick=\"$onClick\" class=\"btn-link glyphicon glyphicon-pencil\" href=\"javascript:void()\"  title=\"Wijzig actiepunt\"</a>";

                $actieDeleteButton = "<a type=\"button\"  data-id='$acId' class=\"btn-link glyphicon glyphicon-remove delActiepunt\" href=\"javascript:void()\"  title=\"Wis actiepunt\"</a>";

                $backgroundColorAP = $yellow;

                if ($acRec->acStatus == '*UITGEVOERD')
                    $backgroundColorAP = $green;

                $htmlActieDetail .= "<tr style=\"background-color: $backgroundColorAP\"><td style=\"border: none;\">$actieUpdateButton&nbsp$actieDeleteButton</td><td style=\"border: none;\">$acTitle</td><td style=\"border: none;\">$acOmschrijving</td><td style=\"border: none;\">$acPersoon</td><td style=\"border: none;\">$acStreefdatum</td><td style=\"border: none;\">$acStatus</td></tr>";

            }

            if ($existActiePunten)
                $htmlACTIE .= "<tr><th style=\"border: none;\"></th><th style=\"border: none;\">Actiepunt</th><th style=\"border: none;\">Extra omschrijving</th><th style=\"border: none;\">Verantwoordelijke</th><th style=\"border: none;\">Streefdatum</th><th style=\"border: none;\">Status</th></tr>" . $htmlActieDetail;

            $htmlACTIE .= "</table>";

            if ($existActiePunten)
                $html .= "<tr><td>&nbsp;</td><td colspan=\"7\">$htmlACTIE</td></tr>";
        }

        $html .= "</table>";

        return "$html $jsScript";


    }

    // ========================================================================================
    // HTML Deelnemers
    //
    // In:	meeting
    //
    // Return: HTML
    // ========================================================================================

    static function GetDeelnemersHTML($pMeeting)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(Sx::GetClassPath("settings.class"));

        $green = SSP_settings::GetBackgroundColor('green');
        $yellow = SSP_settings::GetBackgroundColor('yellow');
        $red = SSP_settings::GetBackgroundColor('red');
        $orange = SSP_settings::GetBackgroundColor('orange');
        $blue = SSP_settings::GetBackgroundColor('blue');

        $sqlStat = "Select * from ema_md_meeting_deelnemers inner join ssp_ad on adCode = mdPersoon where mdMeeting = $pMeeting order by adVoornaamNaam";

        $db->Query($sqlStat);

        $html = "<table class=\"table\"><tr><th>Persoon</th><th>Aanwezig</th><th>Verontschuldigd</th><th>Afwezig</th></tr>";

        while ($mdRec = $db->Row()) {

            $persoon = $mdRec->mdPersoon;
            $meeting = $mdRec->mdMeeting;

            $naam = $mdRec->adVoornaamNaam;

            $aanwezig = "";
            $verontschuldigd = "";
            $afwezig = "";
            $color = "";

            if ($mdRec->mdStatus == "*AANWEZIG") {
                $aanwezig = "checked";
                $color = $green;
            }

            if ($mdRec->mdStatus == "*VERONTSCHULDIGD") {
                $verontschuldigd = "checked";
                $color = $yellow;
            }

            if ($mdRec->mdStatus == "*AFWEZIG") {
                $afwezig = "checked";
                $color = $red;
            }

            $cbAanwezig = "<input type='checkbox' class='form-check-input checkAanwezig' $aanwezig data-status='*AANWEZIG' data-meeting='$pMeeting' data-persoon='$persoon' value=''>&nbsp;</input>";
            $cbVerontschuldigd = "<input type='checkbox' class='form-check-input checkAanwezig' data-status='*VERONTSCHULDIGD' $verontschuldigd  data-meeting='$pMeeting' data-persoon='$persoon' value=''>&nbsp;</input>";
            $cbAfwezig = "<input type='checkbox' class='form-check-input checkAanwezig'  data-status='*AFWEZIG' $afwezig data-meeting='$pMeeting' data-persoon='$persoon' value=''>&nbsp;</input>";

            $html .= "<tr><td style='background-color: $color'>$naam</td><td>$cbAanwezig</td><td>$cbVerontschuldigd</td><td>$cbAfwezig</td></tr></tr>";

        }

        $html .= "</table>";

        // ----------
        // Javascript
        // ---------

        $jsPath = $_SESSION["SX_BASEPATH"] . '/ema_deelnemers.js';
        $jsScript = file_get_contents($jsPath, true);
        $jsScript = '<script>' . $jsScript . '</script>';

        // -------------
        // Einde functie
        // -------------

        return "$html $jsScript";

    }


    // ========================================================================================
    // Opghalen deelnemers (string)
    //
    // In:	meeting
    //      status
    //
    // Return: HTML
    // ========================================================================================

    static function GetDeelnemersString($pMeeting, $pStatus = '*AANWEZIG')
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_md_meeting_deelnemers inner join ssp_ad on adCode = mdPersoon where mdMeeting = $pMeeting order by adVoornaamNaam";

        $db->Query($sqlStat);

        $deelnemers = "";

        while ($mdRec = $db->Row()) {

            if ($mdRec->mdStatus != $pStatus)
                continue;

            if ($deelnemers)
                $deelnemers .= ", $mdRec->adVoornaamNaam";
            else
                $deelnemers = $mdRec->adVoornaamNaam;

        }

        // -------------
        // Einde functie
        // -------------

        return $deelnemers;
    }

    // ========================================================================================
    // Ophalen volgende sequence agendapunt
    //
    // In:	meeting
    //
    // Return: Sortering
    // ========================================================================================

    static function GetAgendaSort($pMeeting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sortering = 10;

        $sqlStat = "Select max(maSortering) as maxSort from ema_ma_meeting_agendapunten where maMeeting = $pMeeting";
        $db->Query($sqlStat);

        if ($maRec = $db->Row()) {

            $sortering = round(($maRec->maxSort + 10) / 10) * 10;

        }

        // -------------
        // Einde functie
        // -------------

        return $sortering;

    }
    // ========================================================================================
    // Ophalen volgende sequence bijlage
    //
    // In:	Agendapunt
    //
    // Return: Sortering
    // ========================================================================================

    static function GetBijlageSort($pAgendapunt) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sortering = 10;

        $sqlStat = "Select max(mbSortering) as maxSort from ema_mb_meeting_bijlagen where mbAgendapunt = $pAgendapunt";
        $db->Query($sqlStat);

        if ($mbRec = $db->Row()) {

            $sortering = round(($mbRec->maxSort + 10) / 10) * 10;

        }

        // -------------
        // Einde functie
        // -------------

        return $sortering;

    }
    // ========================================================================================
    // Mail Meeting uitnodiging
    //
    // In:	meeting
    //      persoon (persoon-code of *DEELNEMERS)
    //      afzender
    //      user-id
    //
    // Return: Verzonden? (true/false)
    // ========================================================================================

    static function MailMeetingUitnodiging($pMeeting, $pPersoon, $pAfzender, $pUserId) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetSxClassPath("tools.class"));

        // -------------------
        // ophalen nodige data
        // -------------------

        $meRec = SSP_db::Get_EMA_meRec($pMeeting);
        $mtRec = SSP_db::Get_EMA_mtRec($meRec->meGroep, $meRec->meType);
        $grRec = SSP_db::Get_EMA_grRec($meRec->meGroep);

        $meetingDatum = SX_tools::EdtDate($meRec->meDatum);
        $meetingType = $mtRec->mtNaam;
        $aanvang = substr($meRec->meAanvang, 0, 5);
        $locatie = $meRec->meLocatie;

        // ------------
        // Agendapunten
        // ------------

        $sqlStat = "Select count(*) as aantal from ema_ma_meeting_agendapunten where maMeeting = $pMeeting";

        $db->Query($sqlStat);
        $maRec = $db->Row();

        $agendaHTML = "";

        if ($maRec && ($maRec->aantal > 0)) {


            $agendaHTML = "<br/><br/>Volgende agendapunten zijn reeds doorgegeven:<br/><ul>";

            $sqlStat = "Select * from ema_ma_meeting_agendapunten where maMeeting = $pMeeting order by maSortering";

            $db->Query($sqlStat);

            while ($maRec = $db->Row()) {

                $agendaHTML .= "<li>$maRec->maTitel</li>";


            }

            $agendaHTML .= "</ul>";

        }

        // --------
        // Afzender
        // --------

        $afzenderMail = "ema@schellesport.be";
        $afzenderNaam = "Schelle Sport - EMA";

        $adRec = SSP_db::Get_SSP_adRec($pAfzender);

        if ($adRec && ($adRec->adMail > ' ')) {

            $afzenderMail = $adRec->adMail;
            $afzenderNaam = $adRec->adVoornaamNaam;

        }

        // --------------
        // Bestemmelingen
        // --------------

        $personen = array();

        if ($pPersoon != '*DEELNEMERS') {

            $adRec = SSP_db::Get_SSP_adRec($pPersoon);

            if (!$adRec)
                return false;

            if (!$adRec->adMail)
                return false;

            $personen[] = $adRec->adCode;

        }


        if ($pPersoon == '*DEELNEMERS') {

            $sqlStat = "Select * from ema_md_meeting_deelnemers where mdMeeting = $pMeeting";

            $db->Query($sqlStat);

            while ($mdRec = $db->Row()) {

                $persoon = $mdRec->mdPersoon;

                $adRec = SSP_db::Get_SSP_adRec($persoon);

                if ($adRec->adMail) {
                    $personen[] = $adRec->adCode;
                }

            }

        }

        // --------------------------------------
        // Opbouwen & versturen uitnodiging-mails
        // --------------------------------------

        foreach ($personen as $persoon) {

            $adRec = SSP_db::Get_SSP_adRec($persoon);

            if (!$adRec)
                continue;

            if (!$adRec->adMail)
                continue;

            $voornaam = $adRec->adVoornaam;
            $mailTo = $adRec->adMail;

            $fromMail = $afzenderMail;
            $fromName = $afzenderNaam;

            $subject = "Schelle Sport - Uitnodiging vergadering $meetingDatum";

            $mailBody = "Beste $voornaam, "
                . "<br/><br/>"
                . "Hierbij wordt je uitgenodigd op onze eerstvolgende $meetingType"
                . "<br/><br/>Datum: $meetingDatum"
                . "<br/><br/>Aanvang: $aanvang"
                . "<br/><br/>Locatie: $locatie"
                . "<br/><br/>Graag uw eventuele agendapunten."
                . $agendaHTML
                . "<br/><br/>Sportieve groet,"
                . "<br/><br/>$afzenderNaam"
                . "<br/><br/><span style='font-size: 80%; font-style: italic'>Schelle Sport EMA - Eenvoudige Meeting Administratie</span>";

            $bccMail = "gvh@vecasoftware.com";

            $mailBody = utf8_encode($mailBody);

            SX_tools::SendMail($subject, $mailBody, $mailTo, $bccMail, $fromMail, $fromName, "", 'UTF-8');

            // -----------------
            // Create log-record
            // -----------------

            $curDateTime = date('Y-m-d H:i:s');

            $values = array();

            $values["mlMeeting"] = MySQL::SQLValue($pMeeting, MySQL::SQLVALUE_NUMBER);
            $values["mlPersoon"] = MySQL::SQLValue($persoon);
            $values["mlMailType"] = MySQL::SQLValue("Uitnodiging");
            $values["mlAfzender"] = MySQL::SQLValue($pAfzender);
            $values["mlDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["mlUserCreatie"] = MySQL::SQLValue($pUserId);

            $db->InsertRow("ema_ml_mail_log", $values);

            // -----------------------------------
            // Set status persoon op "uitgenodigd"
            // -----------------------------------

            $mdRec = SSP_db::Get_EMA_mdRec($pMeeting, $persoon);

            if ($mdRec) {
                if ($mdRec->mdStatus == '*VERWACHT') {

                    $sqlStat = "Update ema_md_meeting_deelnemers set mdStatus = '*UITGENODIGD' where mdMeeting = $pMeeting and mdPersoon = '$persoon'";

                    $db->Query($sqlStat);

                }

            }

        }

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    // Mail Meeting verslag
    //
    // In:	meeting
    //      persoon (persoon-code of *DEELNEMERS)
    //      afzender
    //      user-id
    //
    // Return: Verzonden? (true/false)
    // ========================================================================================

    static function MailMeetingVerslag($pMeeting, $pPersoon, $pAfzender, $pUserId){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("_db.class"));

        // --------
        // Afzender
        // --------

        $afzenderMail = "ema@schellesport.be";
        $afzenderNaam = "Schelle Sport - EMA";

        $adRec = SSP_db::Get_SSP_adRec($pAfzender);

        if ($adRec && ($adRec->adMail > ' ')) {

            $afzenderMail = $adRec->adMail;
            $afzenderNaam = $adRec->adVoornaamNaam;

        }

        // ---------------------
        // Bepalen mail-adressen
        // ---------------------

        $mailTo = "";
        $personen = array();

        if ($pPersoon != '*DEELNEMERS') {

            $adRec = SSP_db::Get_SSP_adRec($pPersoon);

            if (!$adRec)
                return false;

            if (!$adRec->adMail)
                return false;

            $mailTo = $adRec->adMail;

            $personen[] = $adRec->adCode;

        }


        if ($pPersoon == '*DEELNEMERS') {

            $sqlStat = "Select * from ema_md_meeting_deelnemers where mdMeeting = $pMeeting";

            $db->Query($sqlStat);

            while ($mdRec = $db->Row()) {

                $persoon = $mdRec->mdPersoon;

                $adRec = SSP_db::Get_SSP_adRec($persoon);

                if ($adRec->adMail) {

                    $personen[] = $adRec->adCode;

                    if ($mailTo)
                        $mailTo .= "; $adRec->adMail";
                    else
                        $mailTo = $adRec->adMail;

                }

            }

        }

        if (!$mailTo)
            return false;

        // --------------------
        // Aanmaken verslag PDF
        // --------------------

        $meetingVerslagPath = self::CrtMeetingVerslagPDF($pMeeting, 'file');

        // ------------------------------------
        // Zend verslag naar opgegeven personen
        // ------------------------------------

        $fromMail = $afzenderMail;
        $fromName = $afzenderNaam;


        $curDateTime = date('Y-m-d H:i:s');

        foreach ($personen as $persoon) {

            $adRec = SSP_db::Get_SSP_adRec($persoon);

            $mailTo = $adRec->adMail;

            $mailBody = self::GetVerslagMailBody($pMeeting, $persoon);

            $bccMail = "gvh@vecasoftware.com";

            SX_tools::SendMail('Schelle Sport - Verslag vergadering', $mailBody, $mailTo, $bccMail, $fromMail, $fromName, $meetingVerslagPath, 'UTF-8');

            // -----------------
            // Create log-record
            // -----------------

            $values = array();

            $values["mlMeeting"] = MySQL::SQLValue($pMeeting, MySQL::SQLVALUE_NUMBER);
            $values["mlPersoon"] = MySQL::SQLValue($persoon);
            $values["mlMailType"] = MySQL::SQLValue("Verslag");
            $values["mlAfzender"] = MySQL::SQLValue($pAfzender);
            $values["mlDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["mlUserCreatie"] = MySQL::SQLValue($pUserId);

            $db->InsertRow("ema_ml_mail_log", $values);

        }

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    // Ophalen mail-body verslag
    //
    // In:	meeting
    //
    // Return: HTML
    // ========================================================================================

    static function GetVerslagMailBody($pMeeting, $pPersoon)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));

        // -------------------
        // ophalen nodige data
        // -------------------

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);
        $meRec = SSP_db::Get_EMA_meRec($pMeeting);
        $mtRec = SSP_db::Get_EMA_mtRec($meRec->meGroep, $meRec->meType);
        $grRec = SSP_db::Get_EMA_grRec($meRec->meGroep);

        $meetingDatum = SX_tools::EdtDate($meRec->meDatum);

        // ----------
        // HTML Start
        // ----------

        $bodyHTML = "<!DOCTYPE html>";
        $bodyHTML .= "<html>";
        $bodyHTML .= "<head>";

        $bodyHTML .= "<style>";
        $bodyHTML .= "table, th, td { ";
        $bodyHTML .= " border: 1px solid black; ";
        $bodyHTML .= " border-collapse: collapse;";
        $bodyHTML .= "} ";
        $bodyHTML .= "th, td { ";
        $bodyHTML .= "  padding: 5px; ";
        $bodyHTML .= "  text-align: left;";
        $bodyHTML .= " } ";
        $bodyHTML .= "</style>";

        $bodyHTML .= "</head>";

        $bodyHTML .= "<body>" . "\r\n";

        // ------------------
        // Aanmaken mail-body
        // ------------------

        $bodyHTML .= "Beste $adRec->adVoornaamNaam,"
            . "<br/><br/>"
            . "In bijlage vind je het verslag van onderstaande vergadering:"
            . "<br/><br/>"
            . "Type: $mtRec->mtNaam $grRec->grNaam"
            . "<br/>"
            . "Datum: $meetingDatum";

        $actiePuntenHTML = self::GetActiepuntenMailBody($pPersoon, $pMeeting);

        if ($actiePuntenHTML) {

            $bodyHTML .= "<br/><br/>Uw actiepunten:<br/><br/> $actiePuntenHTML";


        }

        $bodyHTML .= "<br/><br/>Met sportieve groet,<br/><br/>Schelle Sport EMA";


        // --------
        // End HTML
        // --------

        $bodyHTML .= "</body></html>" . "\r\n";

        // -------------
        // Einde functie
        // -------------

        return $bodyHTML;

    }
    // ========================================================================================
    // Ophalen mail-body actiepunten
    //
    // In:	persoon
    //      meeting (optioneel)
    //
    // Return: HTML
    // ========================================================================================
    static function GetActiepuntenMailBody($pPersoon, $pMeeting = 0)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetSxClassPath("tools.class"));

        $actiepuntenHTML = "";

        $sqlStat = "Select * from ema_ac_actie_punten inner join ema_ma_meeting_agendapunten on maId = acAgendapunt where acPersoon = '$pPersoon' and (maMeeting = $pMeeting or $pMeeting = 0 )";
        $db->Query($sqlStat);

        while ($acRec = $db->Row()) {

            $streefdatum = SX_tools::EdtDate($acRec->acStreefdatum);
            $omschrijving = nl2br($acRec->acOmschrijving);

            $status = $acRec->acStatus;
            $taRec = SSP_db::Get_SX_taRec('EMA_ACTIEPUNT_STATUS', $status);
            if ($taRec)
                $status = $taRec->taName;

            $actiepuntHTML = "<tr><td>$acRec->acTitel</td><td>$omschrijving</td><td> $streefdatum </td><td>$status</td></tr>";

            $actiepuntenHTML .= $actiepuntHTML;

        }


        if ($actiepuntenHTML) {

            $actiepuntenHTML = "<table><tr><th>Actiepunt</th><th>Extra omschrijving</th><th>Streefdatum</th><th>Status</th></tr>"
                . $actiepuntenHTML
                . "</table>";


        }

        // -------------
        // Einde functie
        // -------------

        return $actiepuntenHTML;


    }


    // ===================================================================================================
    // Functie: Create Meeting-verslag PDF
    //
    // In:	Meeting
    //      Modus (file, display)
    //
    // Out: Path (als modus = 'file')
    //
    // ===================================================================================================

    static function CrtMeetingVerslagPDF($pMeeting, $pModus = 'file')
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("ema.meeting_verslag.class"));
        include_once(SX::GetSxClassPath("tools.class"));

        // --------------------
        // Ophalen meeting-info
        // --------------------

        $meRec = SSP_db::Get_EMA_meRec($pMeeting);

        if (!$meRec)
            return "";


        // -------------------
        // Create PDF-document
        // -------------------

        $meetingVerslag = new Meetingverslag(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set margins
        $meetingVerslag->SetMargins(PDF_MARGIN_LEFT, 42, PDF_MARGIN_RIGHT);
        $meetingVerslag->SetHeaderMargin(5);
        $meetingVerslag->SetFooterMargin(5);
        $meetingVerslag->SetAuthor('Schelle Sport');
        $meetingVerslag->SetTitle('Vergadering VERSLAG - Schelle Sport');
        $meetingVerslag->SetSubject($adRec->adVoornaamNaam);


        $logoSSP = $_SESSION["SX_BASEPATH"] . sx::GetSiteImgPath('logo_klein.jpg');
        $meetingVerslag->logoSPP = $logoSSP;

        $meDatum = new DateTime($meRec->meDatum);
        $meetingVerslag->meetingDatum = $meDatum->format('d/m/Y');
        $meetingVerslag->aanwezig = utf8_encode(SSP_ema::GetDeelnemersString($pMeeting, '*AANWEZIG'));
        $meetingVerslag->verontschuldigd = utf8_encode(SSP_ema::GetDeelnemersString($pMeeting, '*VERONTSCHULDIGD'));

        $grRec = SSP_db::Get_EMA_grRec($meRec->meGroep);
        if ($grRec)
            $meetingVerslag->meetingGroep = $grRec->grNaam;
        else
            $meetingVerslag->meetingGroep = "???";

        $mtRec = SSP_db::Get_EMA_mtRec($meRec->meGroep, $meRec->meType);

        if ($mtRec)
            $meetingVerslag->meetingType = $mtRec->mtNaam;
        else
            $meetingVerslag->meetingType = "???";

        $meetingVerslag->AddPage();

        // ------------
        // Agendapunten
        // -------------

        $sqlStat = "Select * from ema_ma_meeting_agendapunten where maMeeting = $pMeeting and maInVerslag = 1 order by maSortering, maId";

        $db->Query($sqlStat);
        $nummer = 0;
        $y_pos = 0;

        while ($maRec = $db->Row()) {

            if ($y_pos > 220)
                $meetingVerslag->AddPage();

            $nummer++;
            $maTitel = $maRec->maTitel;
            $maTitel = utf8_encode($maTitel);

            $maOmschrijving = utf8_encode(wordwrap($maRec->maOmschrijving,120) . "\n");

            $maBesluit = utf8_encode(wordwrap($maRec->maBesluit, 120) . "\n");

            $y_pos = $meetingVerslag->Agendapunt($nummer, $maTitel, $maOmschrijving, $maBesluit);


            // -----------
            // Actiepunten
            // -----------

            $agendapunt = $maRec->maId;

            $sqlStat = "Select * from ema_ac_actie_punten where acAgendapunt = $agendapunt";

            $db2->Query($sqlStat);

            while ($acRec = $db2->Row()) {

                $acTitel = $acRec->acTitel;
                $acTitel = utf8_encode($acTitel);

                $acOmschrijving = wordwrap($acRec->acOmschrijving, 120);
                $acOmschrijving = utf8_encode($acOmschrijving);

                $verantwoordelijke = "";

                $adRec = SSP_db::Get_SSP_adRec($acRec->acPersoon);
                if ($adRec)
                    $verantwoordelijke = utf8_encode($adRec->adVoornaamNaam);

                $acStreefdatum = $acRec->acStreefdatum;
                $streefdatum = SX_tools::EdtDate($acStreefdatum);

                $acOmschrijving = "(Verantwoordelijke: $verantwoordelijke, Streefdatum: $streefdatum)\n$acOmschrijving\n";


                $meetingVerslag->Actiepunt($acTitel, $acOmschrijving);

            }

        }

        if ($pModus == 'display')
            $meetingVerslag->Output();

        if ($pModus == 'file') {

            $filePath = SSP_ema::GetMeetingVerslagPath($pMeeting);
            $meetingVerslag->Output($filePath, 'F');
        }


        // -------------
        // Einde functie
        // -------------

        if ($pModus == 'file')
            return $filePath;
        else
            return "";

    }


    // ===================================================================================================
    // Functie: Get file path Meeting verslag
    //
    // In:	- Bestelbon
    //
    // Out: - OK?
    //
    // ===================================================================================================

    static function GetMeetingVerslagPath($pMeeting)
    {

        include_once(SX::GetClassPath("_db.class"));

        $meRec = SSP_db::Get_EMA_meRec($pMeeting);

        $fileName = "";

        if ($meRec) {

            $grRec = SSP_db::Get_EMA_grRec($meRec->meGroep);
            $mtRec = SSP_db::Get_EMA_mtRec($meRec->meGroep, $meRec->meType);

            if ($grRec)
                $grNaam = $grRec->grNaam;

            if ($mtRec)
                $mtNaam = $mtRec->mtNaam;


            $fileName = "verslag_" . $grNaam . "_" . $mtNaam . "_" . $meRec->meDatum;

        }

        if (!$fileName)
            $fileName = 'verslag_meeting_' . $pMeeting;


        $rootDir = $_SESSION["SX_BASEPATH"];

        $filePath = $rootDir . '/_generated_files/ema/' . $fileName . '.pdf';

        return $filePath;

    }

    // ===================================================================================================
    // Functie: Mag vergadering gewist
    //
    // In:	Meeting

    // Out: Mag gewist? (true/false)
    //
    // ===================================================================================================

    static function AllowDeleteMeeting($pMeeting)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ------------------------------
        // Niet indien nog "agendapunten"
        // ------------------------------

        $sqlStat = "Select count(*) as aantal from ema_ma_meeting_agendapunten where maMeeting = $pMeeting";

        $db->Query($sqlStat);

        if ($maRec = $db->Row())
            if ($maRec->aantal > 0)
                return false;

        // ----------------------------
        // Niet indien nog "deelnemers"
        // ----------------------------

        $sqlStat = "Select count(*) as aantal from ema_md_meeting_deelnemers where mdMeeting = $pMeeting";

        $db->Query($sqlStat);

        if ($mdRec = $db->Row())
            if ($mdRec->aantal > 0)
                return false;

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ===================================================================================================
    // Functie: Mag agendapunt gewist
    //
    // In:	Agendapunt

    // Out: Mag gewist? (true/false)
    //
    // ===================================================================================================

    static function AllowDeleteAgendapunt($pAgendapunt)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ----------------------------
        // INdien nog niet "besproken"
        // ---------------------------

        $maRec = SSP_db::Get_EMA_maRec($pAgendapunt);

        if ($maRec->maAfgehandeld == 1 )
            return false;

        // ------------------------------
        // Niet indien nog "actiepunten"
        // ------------------------------

        $sqlStat = "Select count(*) as aantal from ema_ac_actie_punten where acAgendapunt = $pAgendapunt";

        $db->Query($sqlStat);

        if ($acRec = $db->Row())
            if ($acRec->aantal > 0)
                return false;

        // --------------------------
        // Niet indien nog "bijlagen"
        // --------------------------

        $sqlStat = "Select count(*) as aantal from ema_mb_meeting_bijlagen where mbAgendapunt = $pAgendapunt";

        $db->Query($sqlStat);

        if ($mbRec = $db->Row())
            if ($mbRec->aantal > 0)
                return false;


        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ===================================================================================================
    // Functie: Unlink Angendapunt
    //
    // In:	Agendapunt

    //
    // ===================================================================================================

    static function UnlinkAgendapunt($pAgendapunt) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $meeting = self::GetAgendapuntMeeting($pAgendapunt);

        $sqlStat = "update ema_ma_meeting_agendapunten set maMeeting = null where maId = $pAgendapunt";

        $db->Query($sqlStat);

        if ($meeting)
            self::UpdMeetingLevel($meeting);

    }

    // ===================================================================================================
    // Functie: Link Angendapunt
    //
    // In:	Agendapunt
    //      Meeting
    //
    // ===================================================================================================

    static function LinkAgendapunt($pAgendapunt, $pMeeting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "update ema_ma_meeting_agendapunten set maMeeting = $pMeeting where maId = $pAgendapunt";

        $db->Query($sqlStat);

        self::UpdMeetingLevel($pMeeting);

    }

    // ========================================================================================
    //  Update Meeting Level
    //
    // In:	Meeting
    //
    // Return: New Level
    // ========================================================================================

    static function UpdMeetingLevel($pMeeting){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update ema_me_meetings set meLevel = meLevel + 1 where meId = $pMeeting";
        $db->Query($sqlStat);

        $sqlStat = "Select * from ema_me_meetings where meId = $pMeeting";
        $db->Query($sqlStat);

        $level = 0;

        if ($meRec = $db->Row())
            $level = $meRec->meLevel;

        // -------------
        // Einde functie
        // -------------

        return $level;

    }

    // ========================================================================================
    // Ophalen meeting van agendapunt
    //
    // In:	Agendapunt
    //
    // Return: Meeting
    // ========================================================================================

    static function GetAgendapuntMeeting($pAgendapunt){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $meeting = null;

        $sqlStat = "Select * from ema_ma_meeting_agendapunten where maId = $pAgendapunt";
        $db->Query($sqlStat);

        if ($maRec = $db->Row())
            $meeting = $maRec->maMeeting;

        // -------------
        // Einde functie
        // -------------

        return $meeting;

    }

    // ========================================================================================
    // Ophalen meeting van actiepunt
    //
    // In:	Actiepunt
    //
    // Return: Meeting
    // ========================================================================================

    static function GetActiepuntMeeting($pActiepunt){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $meeting = null;

        $acRec = SSP_db::Get_EMA_acRec($pActiepunt);

        if ($acRec) {

            $agendapunt = $acRec->acAgendapunt;

            $maRec = SSP_db::Get_EMA_maRec($agendapunt);

            if ($maRec)
                $meeting = $maRec->maMeeting;

        }

        // -------------
        // Einde functie
        // -------------

        return $meeting;

    }

    // ========================================================================================
    // Ophalen meeting van bijlage
    //
    // In:	Bijlage
    //
    // Return: Meeting
    // ========================================================================================

    static function GetBijlageMeeting($pBijlage) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $meeting = null;

        $mbRec = SSP_db::Get_EMA_mbRec($pBijlage);

        if ($mbRec) {

            $agendapunt = $mbRec->mbAgendapunt;

            $maRec = SSP_db::Get_EMA_maRec($agendapunt);

            if ($maRec)
                $meeting = $maRec->maMeeting;

        }

        // -------------
        // Einde functie
        // -------------

        return $meeting;
    }

    // ========================================================================================
    // Ophalen meeting-level
    //
    // In:	Meeting
    //
    // Return: Level
    // ========================================================================================

    static function GetMeetingLevel($pMeeting) {

        include_once(SX::GetClassPath("_db.class"));

        $meRec = SSP_db::Get_EMA_meRec($pMeeting);

        return $meRec->meLevel;


    }


}
?>