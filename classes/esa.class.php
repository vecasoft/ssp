<?php 

class SSP_esa
{ // define the class


    // ========================================================================================
    // Check geboortedatum geldig voor stage
    //
    // In:	Stage ID
    //      Geboortedatum
    //
    // Return: *OK of foutboodschap
    // ========================================================================================

    static function ChkGeboortedatum($pStage, $pGeboortedatum){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "select MIN(slGeboortejaarVan) AS minJaar, MAX(slGeboortejaarTot) AS maxJaar from esa_sl_stage_lichtingen where slStage = $pStage";
        $db->Query($sqlStat);

        $geboortejaar = substr($pGeboortedatum,0,4);

        if ($slRec = $db->Row())
            if ($geboortejaar < $slRec->minJaar or $geboortejaar > $slRec->maxJaar)
                return "Geboortejaar met tussen $slRec->minJaar en $slRec->maxJaar";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }
    // ========================================================================================
    // Check "opmerkingen" geldig voor stage
    //
    // In:	Stage ID
    //      Opmerkingen
    //
    // Return: *OK of foutboodschap
    // ========================================================================================

    static function ChkOpmerkingen($pStage, $pOpmerkingen){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from esa_sh_stage_headers where shId = $pStage";
        $db->Query($sqlStat);

        $shRec = $db->Row();

        if ($shRec and ($shRec->shOpmerkingenVerplicht == 1) and (! $pOpmerkingen)){

            $message = 'Opmerkingen verplicht in te geven';

            if ($shRec->shExtraInfoFormulier)
                $message = $shRec->shExtraInfoFormulier;

            return $message;

        }

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }
    // ========================================================================================
    // Update aantal inschrijvingen
    //
    // In:	Stage ID
    //
    // Return: Geen
    // ========================================================================================

    static function UpdAantalInschrijvingen($pStage){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        // -------------------
        // Voor de ganse stage
        // -------------------

        $aantalInschrijvingen = 0;
        $aantalInschrijvingenBetaald = 0;

        $sqlStat = "Select count(*) as aantal from esa_si_stage_inschrijvingen where siStage = $pStage and siRecStatus = 'A'";
        $db->Query($sqlStat);

        if ($siRec = $db->Row())
            $aantalInschrijvingen = $siRec->aantal;

        $sqlStat = "Select count(*) as aantal from esa_si_stage_inschrijvingen where siStage = $pStage and siRecStatus = 'A' and siBetaald >= siTeBetalen";
        $db->Query($sqlStat);

        if ($siRec = $db->Row())
            $aantalInschrijvingenBetaald = $siRec->aantal;

        $values = array();
        $where = array();

        $values["shAantalInschrijvingen"] =  MySQL::SQLValue($aantalInschrijvingen, MySQL::SQLVALUE_NUMBER);
        $values["shAantalInschrijvingenBetaald"] =  MySQL::SQLValue($aantalInschrijvingenBetaald, MySQL::SQLVALUE_NUMBER);

        $where["shId"] =  MySQL::SQLValue($pStage, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("esa_sh_stage_headers", $values, $where);

        // ------------
        // Per lichting
        // ------------

        $sqlStat = "Select * from esa_sl_stage_lichtingen where slStage = $pStage";
        $db->Query($sqlStat);

        while ($slRec = $db->Row()){

            $lichting = $slRec->slId;
            $aantalInschrijvingen = 0;
            $aantalInschrijvingenBetaald = 0;

            $sqlStat = "Select count(*) as aantal from esa_si_stage_inschrijvingen where siStage = $pStage and siLichting = $lichting and siRecStatus = 'A'";
            $db2->Query($sqlStat);

            if ($siRec = $db2->Row())
                $aantalInschrijvingen = $siRec->aantal;

            $sqlStat = "Select count(*) as aantal from esa_si_stage_inschrijvingen where siStage = $pStage and siLichting = $lichting and siRecStatus = 'A' and siBetaald >= siTeBetalen";
            $db2->Query($sqlStat);

            if ($siRec = $db2->Row())
                $aantalInschrijvingenBetaald = $siRec->aantal;

            $values = array();
            $where = array();

            $values["slAantalInschrijvingen"] =  MySQL::SQLValue($aantalInschrijvingen, MySQL::SQLVALUE_NUMBER);
            $values["slAantalInschrijvingenBetaald"] =  MySQL::SQLValue($aantalInschrijvingenBetaald, MySQL::SQLVALUE_NUMBER);

            $where["slId"] =  MySQL::SQLValue($lichting, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("esa_sl_stage_lichtingen", $values, $where);


        }


        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    // Get stages toegang voor bepaalde gebruiker (partner)
    //
    // In:	user
    //
    // Return: Array met stage-id's (null indien geen stages)
    // ========================================================================================

    static function GetStages($pUser){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pUser);
        if (! $adRec)
            return null;

        $isBestuur = false;
        if (strpos($adRec->adFunctieVB, 'bestuur') !== false)
            $isBestuur = true;

        if ($isBestuur)
            $sqlStat= "Select * from esa_sh_stage_headers Order by shId desc";
        else
            $sqlStat = "Select * from esa_sh_stage_headers"
                    . " Inner join esa_pu_partner_users on puPartner = shPartner"
                    . " Where puUser = '$pUser'"
                    . " Order by shId desc";

        $db->Query($sqlStat);

        $aantal = 0;
        $stages = array();

        while ($shRec = $db->Row()){

            $aantal++;
            $stages[] = $shRec->shId;

        }

        if ($aantal < 0)
            $stages = null;

        // -------------
        // Einde functie
        // -------------

        return $stages;

    }

    // ===============================================================================================
    // Ophalen stages waartoe iemand toegang heeft (in de vorm van "sql WHERE-clause"-
    //
    // In:	User
    //
    // Return: Where clause
    // ===============================================================================================

    static function GetStagesSqlExtraWhere($pUser){

        if ($pUser == 'webmaster')
            return '1=1';

        $stages = self::GetStages($pUser);

        if (! $stages)
            return '1=2';

        $in = '';

        foreach ($stages as $stages){

            if (! $in)
                $in = $stages;
            else
                $in .= ',' . $stages;

        }

        if ($in)
            $where= "shId in ($in)";
        else
            $where = "1=2";

        // -------------
        // Einde functie
        // -------------

        return $where;

    }


    // ========================================================================================
    // OPhalen warning tekst voor inschrijf-forumier ivm afgesloten categorieën
    //
    // In:	Stage ID
    //
    // Return: Tekst (null indien geen afgesloten categoieën)
    // ========================================================================================

    static function GetStageTekstAfgeslotenCats($pStage){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("settings.class"));

        $yellow = SSP_settings::GetBackgroundColor('yellow');

        $sqlStat = "Select count(*) as aantal from esa_sl_stage_lichtingen where slStage = $pStage and slRecStatus <> 'A'";
        $db->Query($sqlStat);

        $tekst = null;

        $slRec = $db->Row();

        if ($slRec and  ($slRec->aantal > 0)) {

            if ($slRec->aantal == 1)
                $tekst = "OPGELET: Volgende categorie is reeds volzet:";
            else
                $tekst = "OPGELET: Volgende categorieën zijn reeds volzet:";

            $sqlStat = "Select * from esa_sl_stage_lichtingen where slStage = $pStage and slRecStatus <> 'A'";
            $db->Query($sqlStat);
            $i = 0;

            while ($slRec = $db->Row()) {

                $i++;
                $naam = $slRec->slNaam;

                if ($i == 1)
                    $tekst = "$tekst $naam";
                else
                    $tekst = "$tekst, $naam";

            }

        }

        $height = "35px";

        if (1==2) {

            $extraInfoFormulier = self::GetStageExtraInfoFormulier($pStage);

            if ($extraInfoFormulier) {

                if (!$tekst) {
                    $tekst = $extraInfoFormulier;
                    $height = "35px";

                } else {
                    $tekst = "$extraInfoFormulier <br/>$tekst";
                    $height = "60px";
                }
            }
        }

        // -------------
        // Einde functie
        // -------------

        $tekst = utf8_decode($tekst);

        if ($tekst)
            return "<div style='background-color: $yellow; height: $height; padding: 8px'>$tekst</div>";
        else
            return null;

    }
    // ========================================================================================
    // Ophalen HTML-snippet stage status
    //
    // In: Stage (0 = Alle actieve stages)
    //
    // Return: Tekst (null indien geen afgesloten categoieën)
    // ========================================================================================

    static function GetStageStatusHtmlSnippet($pStage = 0){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("settings.class"));

        // -----
        // Inits
        // -----
        $yellow = SSP_settings::GetBackgroundColor('yellow');

        $html = null;

        // ---------------------------
        // Ophalen alle actieve stages
        // ---------------------------

        $stages = array();
        $stageNamen = array();
        $stageStatussen = array();
        $stageTeksten = array();
        $height = 0;

        $sqlStat = "Select * from esa_sh_stage_headers where shRecStatus = 'A' and shStartDatum >= current_date and ($pStage = 0 or $pStage = shId)";
        $db->Query($sqlStat);

        while ($shRec= $db->Row()){
            $stages[] = $shRec->shId;
            $stageNamen[] = $shRec->shNaam;
        }

        foreach ($stages as $key=>$stage){

            // -------------------------------
            // Stage volledig/partieel volzet?
            // -------------------------------

            $stageStatussen[$key] = '*VOLLEDIG_VOLZET';

            $sqlStat = "Select * from esa_sl_stage_lichtingen where slStage = $stage";
            $db->Query($sqlStat);

            While ($slRec = $db->Row()){

                if ($slRec->slRecStatus == 'A' and $stageStatussen[$key] == '*VOLLEDIG_VOLZET')
                    $stageStatussen[$key] = '*VOLLEDIG_OPEN';

                if ($slRec->slRecStatus == 'H' and $stageStatussen[$key] == '*VOLLEDIG_OPEN')
                    $stageStatussen[$key] = '*DEEL_VOLZET';

                if ($stageStatussen[$key] == '*DEEL_VOLZET')
                    break;

            }

            if ($stageStatussen[$key] == '*VOLLEDIG_VOLZET')
                continue;

            if ($stageStatussen[$key] == '*DEEL_VOLZET')
                continue;


        }


        foreach ($stages as $key=>$stage){

            $stageNaam = $stageNamen[$key];

            $stageTeksten[$key] = null;

            if ($stageStatussen[$key] == '*VOLLEDIG_OPEN')
                continue;

            if ($stageStatussen[$key] == '*VOLLEDIG_VOLZET') {
                $stageTeksten[$key] = "$stageNaam: VOLLEDIG VOLZET";
                continue;
            }


            if ($stageStatussen[$key] == '*DEEL_VOLZET') {

                $lichtingen = null;

                $sqlStat = "Select * from esa_sl_stage_lichtingen where slStage = $stage and slRecStatus <> 'A' and slCode <> '*WL'";
                $db->Query($sqlStat);

                $isZijn = "is";

                while ($slRec = $db->Row())
                    if (! $lichtingen)
                        $lichtingen = $slRec->slNaam;
                    else {
                        $lichtingen = $lichtingen . ', ' . $slRec->slNaam;
                        $isZijn = "zijn";
                    }

                $stageTeksten[$key] = "$stageNaam: $lichtingen $isZijn volzet";
                continue;
            }


        }

        // ---------------------------------------
        // Opbouwen HTML snippet met stages-status
        // ---------------------------------------

        foreach ($stageTeksten as $stageTekst){

            if ($stageTekst <= ' ')
                continue;

            $height += 30;

            if (! $html)
                $html = $stageTekst;
            else
                $html .= "<br/>$stageTekst";

        }


        // -------------
        // Einde functie
        // -------------

        if ($html) {
            $html = utf8_decode($html);
            return "<div style='background-color: $yellow; height: $height px; padding: 8px'>$html</div>";
        }
        else
            return null;


    }


    // ========================================================================================
    // Ophalen Stage-naam
    //
    // In:	Stage ID
    //
    // Return: Naam
    // ========================================================================================

    static function GetStageNaam($pStage){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $naam = null;

        $sqlStat = "Select * from esa_sh_stage_headers where shId = $pStage";
        $db->Query($sqlStat);

        if ($shRec = $db->Row())
            $naam = $shRec->shNaam;

        // -------------
        // Einde functie
        // -------------

        return $naam;



    }

    // ========================================================================================
    // OPhalen Stage "Extra Info Formuluer
    //
    // In:	Stage ID
    //
    // Return: Tekst
    // ========================================================================================

    static function GetStageExtraInfoFormulier($pStage){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $extraInfoFormulier = null;

        $sqlStat = "Select * from esa_sh_stage_headers where shId = $pStage";
        $db->Query($sqlStat);

        if ($shRec = $db->Row())
            $extraInfoFormulier = $shRec->shExtraInfoFormulier;

        // -------------
        // Einde functie
        // -------------

        return $extraInfoFormulier;

    }

    // ========================================================================================
    // Registratie inschrijving
    //
    // In:	Inschrijving ID
    //
    // Return: Niets
    // ========================================================================================

    static function RegInschrijving($pInschrijving){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("efin.class"));
        include_once(SX::GetClassPath("evim.class"));

        include_once(SX::GetSxClassPath("tools.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $siRec = SSP_db::Get_ESA_siRec($pInschrijving);

        if (! $siRec)
            return;

        $stage = $siRec->siStage;

        $naamDeelnemer = $siRec->siVoornaam . " " . $siRec->siNaam;
        $naamDeelnemer = utf8_encode($naamDeelnemer);

        $shRec = SSP_db::Get_ESA_shRec($stage);

        if (! $shRec)
            return;

        $stagenaam = $shRec->shNaam;

        $slRec = SSP_db::Get_ESA_slRec($siRec->siLichting);
        $sgRec = SSP_db::Get_ESA_sgRec($siRec->siGadget);

        // --------------------------
        // Gestructureerde mededeling
        // --------------------------

        $GM = SSP_efin::GetNextGM('*STAGE');
        $GMn = SSP_efin::CvtGmToNum($GM);

        // -------
        // Bijlage
        // -------

        $bijlagePath = "";
        $bijlageNaam = "";

        if ($shRec->shBijlage){

            $bijlagen = json_decode($shRec->shBijlage);

            if ($bijlagen) {

                foreach ($bijlagen as $bijlage) {

                    $filePath = basename($bijlage->name);
                    $bijlageNaam = $bijlage->usrName;
                    break;
                }


            }

            $bijlagePath = $_SESSION["SX_BASEPATH"] . '/_files/esa/bijlagen/' . $filePath;

        }


        // --------------
        // Aanvullen data
        // --------------

        $values = array();
        $where = array();

        $values["siTeBetalen"] =  MySQL::SQLValue($shRec->shPrijs, MySQL::SQLVALUE_NUMBER);

        $values["siGM"] =  MySQL::SQLValue($GM, MySQL::SQLVALUE_TEXT);
        $values["siGMn"] =  MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

        $where["siId"] =  MySQL::SQLValue($pInschrijving, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("esa_si_stage_inschrijvingen", $values, $where);

        $siRec = SSP_db::Get_ESA_siRec($pInschrijving);

        // ---------------------------------------------------------
        // Indien voucher -> link wachtlijst-record met inschrijving
        // ---------------------------------------------------------

        if ($siRec->siVoucher) {

            $inschrijving = $siRec->siId;
            $voucher = $siRec->siVoucher;

            $sqlStat = "Update esa_sw_stage_wachtlijst set swInschrijving = $inschrijving where swVoucher = '$voucher'";
            $db->Query($sqlStat);

        }

        // ---------------------
        // Mail naar INSCHRIJVER
        // ---------------------

        if ($shRec->shMailTemplate) {

            $arr_VARS = array();
            $arr_VALUES = array();

            $arr_VARS[] = "NAAM_DEELNEMER";
            $arr_VALUES[] = $naamDeelnemer;

            $arr_VARS[] = "GM";
            $arr_VALUES[] = $GM;

            $arr_VARS[] = "STAGE";
            $arr_VALUES[] = $stagenaam;

            $arr_VARS[] = "CATEGORIE";
            $arr_VALUES[] = $slRec->slNaam;

            $arr_VARS[] = "GADGET";
            $arr_VALUES[] = $sgRec->sgNaam;

            $arr_VARS[] = "BEDRAG";
            $arr_VALUES[] = $shRec->shPrijs + 0;

            $mailBody = '<html><body>';
            $mailBody .= nl2br($shRec->shMailTemplate);
            $mailBody .= '</body></html>';

            // $mailBody = utf8_encode($mailBody);

            $mailBody = SSP_evim::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);

            $mailSubject = "Schelle Sport - Inschrijving $stagenaam";

            $mailTo = $siRec->siContactMail;
            $mailBCC = "gvh@vecasoftware.com";

            $fromMail = "secretariaat@schellesport.be";
            $fromName = "Schelle Sport - Secretariaat";

            SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName, $bijlagePath, 'UTF-8', '', $bijlageNaam);

        }

        // -----------------------------------------------
        // Mail naar ScHELLE SPORT (stages@schellesport.be)
        // ------------------------------------------------

        $mailSubject = "Schelle Sport - Inschrijving voor $stagenaam";

        $mailBody = "<body>". "\r\n";

        $mailBody .= "<style>". "\r\n";
        $mailBody .= "table, th, td { ". "\r\n";
        $mailBody .= " border: 1px solid black; ". "\r\n";
        $mailBody .= " border-collapse: collapse;". "\r\n";
        $mailBody .= "} ". "\r\n";
        $mailBody .= "th, td { ". "\r\n";
        $mailBody .= "  padding: 5px; ". "\r\n";
        $mailBody .= "  text-align: left;". "\r\n";
        $mailBody .= " } ". "\r\n";
        $mailBody .= "</style>". "\r\n";

        $mailBody .= "Er was een inschrijving voor $stagenaam:" . "\r\n";
        $mailBody .= "<br/><br/>". "\r\n";
        $mailBody .= "Deelnemer: $naamDeelnemer";
        $mailBody .= "<br/><br/>". "\r\n";
        $mailBody .= "Categorie: $slRec->slNaam";
        $mailBody .= "<br/><br/>". "\r\n";
        $mailBody .= "Club: $siRec->siClub";

        if ($siRec->siOpmerkingen)
            $mailBody .= "<br/><br/><b>Opmerkingen:</b><br/>" . nl2br(utf8_encode($siRec->siOpmerkingen)) . "\r\n";

        $mailBody .= "<br/><br/>". "\r\n";
        $mailBody .= "=> Een voledig overzicht is op te vragen via de ESA Partner APP" . "\r\n";


        $mailBody .= "<br/><br/>". "\r\n";
        $mailBody .= "Sportieve groet,". "\r\n";
        $mailBody .= "<br/><br/>Schelle Sport Secretariaat". "\r\n";

        $mailBody .= "</body><br/><br/>". "\r\n";

        $mailBCC = "";

        $fromMail = "secretariaat@schellesport.be";
        $fromName = "Schelle Sport - Secretariaat";

        SX_tools::SendMail($mailSubject, $mailBody, "stages@schellesport.be", $mailBCC, $fromMail, $fromName,'','UTF-8');

        // ----------------------------
        // Update aantal inschrijvingen
        // ----------------------------

        self::UpdAantalInschrijvingen($stage);

        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    // Reminder betaling versturen
    //
    // In:	Inschrijving ID
    //
    // Return: Mail gezonder?
    // ========================================================================================

    static function SndReminderBetaling($pInschrijving){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("efin.class"));
        include_once(SX::GetClassPath("evim.class"));

        include_once(SX::GetSxClassPath("tools.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $siRec = SSP_db::Get_ESA_siRec($pInschrijving);

        if (! $siRec)
            return false;

        // -----------------------------
        // Enkel indien nog niet betaald
        // -----------------------------

        if ($siRec->siBetaald)
            return false;

        $stage = $siRec->siStage;

        $naamDeelnemer = $siRec->siVoornaam . " " . $siRec->siNaam;
        $naamDeelnemer = utf8_encode($naamDeelnemer);

        $shRec = SSP_db::Get_ESA_shRec($stage);

        if (! $shRec)
            return false;

        $stagenaam = $shRec->shNaam;

        $slRec = SSP_db::Get_ESA_slRec($siRec->siLichting);
        $sgRec = SSP_db::Get_ESA_sgRec($siRec->siGadget);

        // --------------------------
        // Gestructureerde mededeling
        // --------------------------

        $GM =  $siRec->siGM;
        $GMn = $siRec->siGMn;

        // -------
        // Bijlage
        // -------

        $bijlagePath = "";
        $bijlageNaam = "";

        if ($shRec->shBijlage){

            $bijlagen = json_decode($shRec->shBijlage);

            if ($bijlagen) {

                foreach ($bijlagen as $bijlage) {

                    $filePath = basename($bijlage->name);
                    $bijlageNaam = $bijlage->usrName;
                    break;
                }


            }

            $bijlagePath = $_SESSION["SX_BASEPATH"] . '/_files/esa/bijlagen/' . $filePath;

        }

        // ---------------------
        // Mail naar INSCHRIJVER
        // ---------------------

        if ($shRec->shMailTemplateReminder) {

            $arr_VARS = array();
            $arr_VALUES = array();

            $arr_VARS[] = "NAAM_DEELNEMER";
            $arr_VALUES[] = $naamDeelnemer;

            $arr_VARS[] = "GM";
            $arr_VALUES[] = $GM;

            $arr_VARS[] = "STAGE";
            $arr_VALUES[] = $stagenaam;

            $arr_VARS[] = "CATEGORIE";
            $arr_VALUES[] = $slRec->slNaam;

            $arr_VARS[] = "GADGET";
            $arr_VALUES[] = $sgRec->sgNaam;

            $arr_VARS[] = "BEDRAG";
            $arr_VALUES[] = $shRec->shPrijs + 0;

            $mailBody = '<html><body>';
            $mailBody .= nl2br($shRec->shMailTemplateReminder);
            $mailBody .= '</body></html>';

            // $mailBody = utf8_encode($mailBody);

            $mailBody = SSP_evim::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);

            $mailSubject = "Reminder: Schelle Sport - Inschrijving $stagenaam nog niet betaald";

            $mailTo = $siRec->siContactMail;
            $mailBCC = "gvh@vecasoftware.com";

            $fromMail = "secretariaat@schellesport.be";
            $fromName = "Schelle Sport - Secretariaat";

            SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName, $bijlagePath, 'UTF-8', '', $bijlageNaam);

        }

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    // Function: Registratie Betaling
    //
    // In:	Betaling
    //      Database actiecode (*ADD, *UPD, *DEL)
    //
    // ========================================================================================

    static function RegBetaling($pBetaling, $pDbAction = '*UPD'){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("scanning.class"));
        include_once(SX::GetClassPath("_db.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $sqlStat = "Select * from esa_sb_stage_betalingen where sbId = $pBetaling";
        $db->Query($sqlStat);

        if (! $sbRec = $db->Row())
            return;

        $stageInschrijving = $sbRec->sbStageInschrijving;

        $siRec = SSP_db::Get_ESA_siRec($stageInschrijving);
        if ( ! $siRec)
            return;

        // ------------------------------------------------------
        // Som alle betalingen met voor zelfde stage-inschrijving
        // ------------------------------------------------------

        $betaald = 0;

        $sqlStat = "Select * from esa_sb_stage_betalingen where sbStageInschrijving = $stageInschrijving";
        $db->Query($sqlStat);

        while ($sbRec = $db->Row()) {

            if (($pDbAction == '*DEL') and ($sbRec->sbId == $pBetaling))
                continue;

            $betaald += $sbRec->sbBedrag;

        }

        // --------------------------
        // Registratie betaald bedrag
        // --------------------------

        $sqlStat = "update esa_si_stage_inschrijvingen set siBetaald = $betaald where siId = $stageInschrijving";
        $db->Query($sqlStat);

        self::UpdAantalInschrijvingen($siRec->siStage);


        // -------------
        // Einde functie
        // -------------

        return;

    }


    // ===================================================================================================
    // Functie: Aanmaken unieke voucher code (5 posities)
    //
    // In:	Niets
    //
    // Uit:	Unieke voucher
    //
    // ===================================================================================================

    Static function GetNewVoucher() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $voucher = null;

        for ($i=0; $i < 99; $i++) {

            $voucher = substr(str_shuffle("123456789abcdefghkmnprstuvwxyz"), 0, 5);

            $sqlStat = "Select count(*) as aantal from esa_sw_stage_wachtlijst where swVoucher = '$voucher'";
            $db->Query($sqlStat);

            $swRec = $db->Row();

            if (! $swRec)
                break;

            if ($swRec->aantal <= 0)
                break;

            $voucher = null;

        }

        // -------------
        // Einde functie
        // -------------

        return $voucher;

    }

    // ===================================================================================================
    // Functie:Check Lichting/Voucher combinatie
    //
    // In   Lichting ID
    //      Voucher Code
    //
    // Uit:	Lichting id (of *ONGELDIG, *REEDS_GEBRUIKT indien ongeldige code)
    //
    // ===================================================================================================

    Static function ChkLichtingVoucerCombi($pLichting, $pVoucher){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from esa_sl_stage_lichtingen where slId = $pLichting";
        $db->Query($sqlStat);

        if (! $slRec = $db->Row())
            return "*ONGELDIGE_LICHTING"; // Kan eigenlijk niet

        if (($slRec->slCode != '*WL') && (! $pVoucher))
            return $pLichting; // Gewone lichting zonder voucher...

        if (($slRec->slCode != '*WL') && ($pVoucher))
            return '*OVERBODIG';

        if (($slRec->slCode == '*WL') && (! $pVoucher))
            return '*NODIG';


        $stage = $slRec->slStage;
        $lichting = "*ONGELDIG";

        $sqlStat = "Select * from esa_sw_stage_wachtlijst where swStage = $stage and swVoucher = '$pVoucher'";
        $db->Query($sqlStat);

        If ($swRec = $db->Row()){

            if ($swRec->swInschrijving)
                $lichting = "*REEDS_GEBRUIKT";

            elseif (! $swRec->swMagInschrijven)
                $lichting = "*NIET_GEACTIVEERD";

            else
                $lichting = $swRec->swLichting;

        }

        // -------------
        // Einde functie
        // -------------

        return $lichting;

    }

    // ===================================================================================================
    // Functie: Copy "lichtingen" van stage > ,stage
    //
    // In   Stage van
    //      Stage naar
    //      User
    //
    // Return: Lichtingen gecopieerd?
    //
    // ===================================================================================================

    Static function CpyLichtingen($pStageVan, $pStageNaar, $pUser) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $return = false;

        // -----------------------------------------------
        // Niet indien er reeds lichtingen zijn aangemaakt
        // -----------------------------------------------

        $sqlStat = "Select count(*) as aantal from esa_sl_stage_lichtingen where slStage = $pStageNaar";

        $db->Query($sqlStat);

        if ($slRec = $db->Row())
            if ($slRec->aantal > 0)
                return false;

        // -----
        // Copy
        // -----

        $sqlStat = "Select * from esa_sl_stage_lichtingen where slStage = $pStageVan";

        $db->Query($sqlStat);

        while ($slRec = $db->Row()){

            $values = array();

            $curDateTime = date('Y-m-d H:i:s');

            $values["slStage"] = MySQL::SQLValue($pStageNaar, MySQL::SQLVALUE_NUMBER);
            $values["slCode"] = MySQL::SQLValue($slRec->slCode, MySQL::SQLVALUE_TEXT);
            $values["slNaam"] = MySQL::SQLValue($slRec->slNaam, MySQL::SQLVALUE_TEXT);
            $values["slGeboortejaarVan"] = MySQL::SQLValue($slRec->slGeboortejaarVan);
            $values["slGeboortejaarTot"] = MySQL::SQLValue($slRec->slGeboortejaarTot);
            $values["slSort"] = MySQL::SQLValue($slRec->slSort, MySQL::SQLVALUE_NUMBER);

            $values["slUserCreatie"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
            $values["slUserUpdate"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);

            $values["slDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["slDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db2->InsertRow("esa_sl_stage_lichtingen", $values);

            if ($id)
                $return = true;

        }

        // -------------
        // Einde functie
        // -------------

        return $return;

    }

    // ===================================================================================================
    // Functie: Copy "Gadgets" van stage > ,stage
    //
    // In   Stage van
    //      Stage naar
    //      User
    //
    // Return: Gadgets gecopieerd?
    //
    // ===================================================================================================

    Static function CpyGadgets($pStageVan, $pStageNaar, $pUser) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $return = false;

        // -----------------------------------------------
        // Niet indien er reeds lichtingen zijn aangemaakt
        // -----------------------------------------------

        $sqlStat = "Select count(*) as aantal from esa_sg_stage_gadgets where sgStage = $pStageNaar";

        $db->Query($sqlStat);

        if ($sgRec = $db->Row())
            if ($sgRec->aantal > 0)
                return false;

        // -----
        // Copy
        // -----

        $sqlStat = "Select * from esa_sg_stage_gadgets where sgStage = $pStageVan";

        $db->Query($sqlStat);

        while ($sgRec = $db->Row()){

            $values = array();

            $curDateTime = date('Y-m-d H:i:s');

            $values["sgStage"] = MySQL::SQLValue($pStageNaar, MySQL::SQLVALUE_NUMBER);
            $values["sgNaam"] = MySQL::SQLValue($sgRec->sgNaam, MySQL::SQLVALUE_TEXT);
            $values["sgSort"] = MySQL::SQLValue($sgRec->sgSort, MySQL::SQLVALUE_NUMBER);

            $values["sgUserCreatie"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
            $values["sgUserUpdate"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);

            $values["sgDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["sgDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db2->InsertRow("esa_sg_stage_gadgets", $values);

            if ($id)
                $return = true;

        }

        // -------------
        // Einde functie
        // -------------

        return $return;

    }

    // ------------
    // EINDE CLASS
    // ------------
}

?>