<?php 

     class SSP_padel { // define the class

         // ========================================================================================
         // Function: Registratie "Start To Padel"
         //
         // In:	Aanvraag ID
         //
         // ========================================================================================
         static function RegStartToPadelAanvraag($pAanvraag) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));
             include_once(SX::GetSxClassPath("tools.class"));

             $sqlStat = "Select * from padel_sa_start_to_padel_aanvragen where saId = $pAanvraag";
             $db->Query($sqlStat);

             if (! $saRec = $db->Row())
                return; // Kan in principe niet

             $aantalDeelnemers = $saRec->saAantalDeelnemers;

             $deelnemers = array();

             $naam = $saRec->saNaam;

             $deelnemers[] = $saRec->saNaam;
             if ($saRec->saDeelnemer2)
                 $deelnemers[] = $saRec->saDeelnemer2;
             if ($saRec->saDeelnemer3)
                 $deelnemers[] = $saRec->saDeelnemer3;
             if ($saRec->saDeelnemer4)
                 $deelnemers[] = $saRec->saDeelnemer4;

             $taRec = SSP_db::Get_SX_taRec('PADEL_LESDAGEN_1', $saRec->saDagUur);
             $dagUur = $taRec->taName;

             // ---------------------------------
             // Zet lesdag/uur op "*GERESERVEERD"
             // ---------------------------------

             self::SetStartToPadelReservaties();

             // ----------------------------------
             // Bevestigings-mail naar inschrijver
             // ----------------------------------

             $mailSubject = "Schelle Sport Padel - Uw aanvraag 'Start To Padel'";

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

             $mailBody .= "Beste $saRec->naam,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Uw aanvraag 'Start To Padel' werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Contactpersoon: <b>$naam</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal deelnemers: <b>$aantalDeelnemers</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Dag & uur: <b>$dagUur</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: <b>10 lessen van 60 min.</b>";
             $mailBody .= "<br/><br/>". "\r\n";

             $i = 0;

             foreach ($deelnemers as $deelnemer){

                 $i++;

                 $mailBody .= "<br/>". "\r\n";
                 $mailBody .= "Deelnemer $i: <b>$deelnemer</b>";

             }

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Uw inschrijving is pas definitief na betaling. We reserveren de gevraagde dag/tijd gedurende 3 werkdagen";

             $teBetalen = $saRec->saTeBetalen + 0;
             $mededeling = "Start to padel - " . $saRec->saNaam;

             $rekening = "BE56 0015 0154 9488";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "<b>Gelieve het totaal bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met mededeling: '$mededeling'</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Alvast bedankt voor je inschrijving!";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Bestuur";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $saRec->saMail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // ----------------
             // Mail naar padel
             // ----------------

             $mailSubject = "Schelle Sport Padel - aanvraag 'Start To Padel'";

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

             $mailBody .= "Beste,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Er werd een 'Start To Padel' aanvraag geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Contactpersoon: <b>$naam</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal deelnemers: <b>$aantalDeelnemers</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Dag & uur: <b>$dagUur</b>";
             $mailBody .= "<br/><br/>". "\r\n";

             $i = 0;

             foreach ($deelnemers as $deelnemer){

                 $i++;

                 $mailBody .= "<br/>". "\r\n";
                 $mailBody .= "Deelnemer $i: <b>$deelnemer</b>";

             }
             If ($saRec->saOpmerkingen){

                 $mailBody .= "<br/><br/>Opmerkingen:<br/><br/>" . "\r\n";
                 $mailBody .= nl2br($saRec->saOpmerkingen);

             }


             $mailBody .= "<br/><br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Secretariaat";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = "padel@schellesport.be";
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -------------
             // Einde functie
             // -------------


         }

         // ========================================================================================
         // Function: Zet status Start To Padel (zet dag/tijd op *GERESERVEERD)
         //
         // In:	Aanvraag ID
         //
         // ========================================================================================
         static function SetStartToPadelReservaties() {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             // --------------------------
             // Verwijder alle reservaties
             // --------------------------

             $sqlStat = "update sx_ta_tables set taAlfaData = null where taTable = 'PADEL_LESDAGEN_1' and taRecStatus = 'A' and  taAlfaData = '*GERESERVEERD'";
             $db->Query($sqlStat);

             $sqlStat = "update sx_ta_tables set taAlfaData = '*VRIJ' where taTable = 'PADEL_LESDAGEN_1' and taRecStatus = 'A' and  (taAlfaData is null or taAlfaData <= ' ')";
             $db->Query($sqlStat);

             // ---------------------------
             // Registreer alle reservaties
             // ---------------------------

             $sqlStat = "Select * from padel_sa_start_to_padel_aanvragen where saRecStatus = 'A'";
             $db->Query($sqlStat);

             while ($saRec = $db->Row()){

                 $dagUur = $saRec->saDagUur;

                 $sqlStat = "Update sx_ta_tables set taAlfaData = '*GERESERVEERD' where taTable = 'PADEL_LESDAGEN_1' and taCode = '$dagUur'";
                 $db2->Query($sqlStat);

             }

         }

         // ========================================================================================
         // Start To Padel - Ophalen warning tekst met reeds bezette dagen/uren
         //
         // Return: Tekst (null indien geen bezette dagen/uren)
         // ========================================================================================

         static function GetStartToPadelBezet(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(Sx::GetClassPath("settings.class"));

             $yellow = SSP_settings::GetBackgroundColor('yellow');

             $sqlStat = "Select * from sx_ta_tables where taTable = 'PADEL_LESDAGEN_1' and not (taAlfaData = '*VRIJ' or taAlfaData is null or taAlfaData <= ' ') ";
             $db->Query($sqlStat);

             $tekst = null;

             while ($taRec = $db->Row()) {

                 $name = $taRec->taName;

                 if ($tekst)
                     $tekst = "$tekst, $name";
                 else
                     $tekst = $name;

             }


             if ($tekst)
                 $tekst = "Reeds volzet: $tekst";

             // -------------
             // Einde functie
             // -------------

             $tekst = utf8_decode($tekst);

             if ($tekst)
                 return "<div style='background-color: $yellow; padding: 8px'>$tekst</div>";
             else
                 return null;

         }

         // ========================================================================================
         // Function: Ophalen padel interclub event code
         //
         // In:	None
         //
         //  Return: Event code
         //
         // ========================================================================================
         static function GetPadelInterclubEvent(){

             return '*PADEL_INTERCLUB';

         }


         // ========================================================================================
         // Function: Ophalen padel interclub event file
         //
         // In:	None
         //
         //  Return: Event file
         //
         // ========================================================================================
         static function GetPadelInterclubEventFile(){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $event = self::GetPadelInterclubEvent();
             $file = null;

             $sqlStat = "Select * from events_eh_event_headers where ehCode = '$event'";
             $db->Query($sqlStat);

             if ($ehRec = $db->Row())
                $file = $ehRec->ehFile;

             // -------------
             // Einde functie
             // -------------

             return $file;


         }

         // ========================================================================================
         // Function: Aanmaken alle padel interclub deelnemers
         //
         // In:	Editie
         //     User
         //
         //  Return: # deelnemers aangemaakt
         //
         // ========================================================================================
         static function CrtAllePadelInterclubDeelnemers($pEditie, $pUser) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             // ----------------------------
             // Ophalen nodige (header-)data
             // ----------------------------

             $prijs = 0;

             $sqlStat = "Select * from padel_ie_interclub_edities where ieEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $ieRec = $db->Row())
                 return 0;

             $prijs = $ieRec->iePrijs;

             // --------------------------------------------------------------------------
             // Ophalen alle padel interclubbers nog niet gekoppeld aan betreffende editie
             // --------------------------------------------------------------------------

             $sqlStat = "Select * from ssp_ad where adRelatieMet like '%P%' AND adFunctieT LIKE '%padel-intercl%' and adRecStatus = 'A'";
             $db->Query($sqlStat);

             $personen = array();

             while ($adRec = $db->Row()) {

                 $persoon = $adRec->adCode;;

                 $sqlStat = "Select count(*) as aantal from padel_id_interclub_deelnemers where idEditie = '$pEditie' and idPersoon = '$persoon'";
                 $db2->Query($sqlStat);

                 if ($idRec = $db2->Row())
                     if ($idRec->aantal >= 1)
                         continue;

                 $personen[] = $persoon;

             }


             // ---------------------------------------
             // Aanmaken deelnemers bettreffende editie
             // ---------------------------------------

             $aantal = 0;

             foreach ($personen as $persoon){

                $values = array();

                $curDateTime = date('Y-m-d H:i:s');

                $values["idEditie"] = MySQL::SQLValue($pEditie, MySQL::SQLVALUE_TEXT);
                $values["idPersoon"] = MySQL::SQLValue($persoon, MySQL::SQLVALUE_TEXT);

                 $values["idTeBetalen"] = MySQL::SQLValue($prijs, MySQL::SQLVALUE_NUMBER);

                $values["idUserCreatie"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
                $values["idDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["idUserUpdate"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
                $values["idDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $db->InsertRow("padel_id_interclub_deelnemers", $values);

                self::MngPadelInterclubEventInschrijving($pEditie, $persoon);


                $aantal++;

            }

            // -------------
            // Einde functie
            // -------------

            return $aantal;

         }

         // ========================================================================================
         // Function: Padel Interclub: Check of editie mag gewist worden
         //
         // In:	Interclub Editie
         //
         // Return: *OK of Boodschap
         //
         // ========================================================================================
         static function ChkDeletePadelInterclubEditie($pEditie){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $boodschap = '*OK';

             // ----------------------------
             // Niet indien reeds deelnemers
             // ----------------------------

             $sqlStat = "Select count(*) as aantal from padel_id_interclub_deelnemers where idEditie = '$pEditie'";
             $db->Query($sqlStat);

             if ($idRec = $db->Row())
                 if ($idRec->aantal >= 1)
                     $boodschap = "Er zijn reeds deelnemers ingegeven: wissen editie $pEditie niet toegestaan";


             // -------------
             // Einde functie
             // -------------

             return $boodschap;


         }

         // ========================================================================================
         // Function: Padel Interclub: Aanmaken/ wijzigen/wissen event voor bepaalde editie
         //
         // In:	Interclub Editie
         //     Actie (*ADD, *UPDATE, *DELETE)
         //
         // Return: Done? true/false
         //
         // ========================================================================================
         static function MngPadelInterclubEvent($pEditie, $pActie = null){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // ----------------------------
             // Ophalen nodige (header-)data
             // ----------------------------

             $event = self::GetPadelInterclubEvent();

             $sqlStat = "Select * from padel_ie_interclub_edities where ieEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $ieRec = $db->Row())
                 return false;

             // ------
             // Delete
             // ------

             if ($pActie == '*DELETE') {

                 $sqlStat = "Delete From events_ee_event_edities where eeEvent = '$event' and eeEditie = '$pEditie'";

                 $db->Query($sqlStat);

                 return true;


             }

             // --------------
             // Update of add
             // -------------

             $modus = '*ADD';

             $sqlStat = "Select count(*) as aantal from events_ee_event_edities where eeEvent = '$event' and eeEditie = '$pEditie'";

             $db->Query($sqlStat);

             if ($eeRec = $db->Row() and $eeRec->aantal >= 1)
                 $modus = '*UPDATE';

             $values = array();
             $where = array();

             $values["eeEvent"] = MySQL::SQLValue($event, MySQL::SQLVALUE_TEXT);
             $values["eeEditie"] = MySQL::SQLValue($pEditie, MySQL::SQLVALUE_TEXT);

             $values["eeNaam"] = MySQL::SQLValue($pEditie, MySQL::SQLVALUE_TEXT);

             $values["eeDatumVan"] = MySQL::SQLValue($ieRec->ieDatumVan, MySQL::SQLVALUE_DATE);
             $values["eeDatumTot"] = MySQL::SQLValue($ieRec->ieDatumTot, MySQL::SQLVALUE_DATE);

             $values["eeBedrag"] = MySQL::SQLValue($ieRec->iePrijs, MySQL::SQLVALUE_NUMBER);

             $values["eeUserCreatie"] = MySQL::SQLValue($ieRec->ieUserCreatie, MySQL::SQLVALUE_TEXT);
             $values["eeUserUpdate"] = MySQL::SQLValue($ieRec->ieUserUpdate, MySQL::SQLVALUE_TEXT);
             $values["eeDatumCreatie"] = MySQL::SQLValue($ieRec->ieDatumCreatie, MySQL::SQLVALUE_DATETIME);
             $values["eeDatumUpdate"] = MySQL::SQLValue($ieRec->ieDatumUpdate, MySQL::SQLVALUE_DATETIME);

             if ($modus == '*UPDATE') {
                 $where["eeEvent"] = MySQL::SQLValue($event, MySQL::SQLVALUE_TEXT);
                 $where["eeEditie"] = MySQL::SQLValue($pEditie, MySQL::SQLVALUE_TEXT);
                 $db->UpdateRows("events_ee_event_edities", $values, $where);
             }

             if ($modus == '*ADD')
                 $db->InsertRow("events_ee_event_edities", $values);

             // -------------
             // Einde functie
             // -------------

             return true;

         }

         // ========================================================================================
         // Function: Padel Interclub: Beheer Inschrijving-bestand
         //
         // In:	Interclub editie
         //     Deelnemer
         //     Actie (*ADD, *UPDATE, *DELETE)
         //
         // Return: Done? true/false
         //
         // ========================================================================================
         static function MngPadelInterclubEventInschrijving($pEditie, $pPersoon, $pActie = null){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             include_once(SX::GetClassPath("events.class"));
             include_once(SX::GetClassPath("efin.class"));

             // ------------------------------
             // Ophalen nodige (header-) info
             // -----------------------------

             $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";
             $db->Query($sqlStat);

             if (! $adRec = $db->Row())
                 return false;

             $sqlStat = "Select * from padel_id_interclub_deelnemers where idEditie = '$pEditie' and idPersoon = '$pPersoon'";
             $db->Query($sqlStat);

             if (! $idRec = $db->Row())
                 return false;

             $inschrijvingId = $idRec->idInschrijvingId;
             $teBetalen = $idRec->idTeBetalen;

             $event = self::GetPadelInterclubEvent();
             $eventFile = self::GetPadelInterclubEventFile();

             // ------
             // Delete
             // ------

             if ($pActie == '*DELETE') {

                 if ($inschrijvingId > 0)  {

                     $sqlStat = "Delete from $eventFile where id = $inschrijvingId";
                     $db->Query($sqlStat);

                     return true;

                 }
                 else {
                     return false;
                 }


            }

             // -------------
             // Add of update
             // -------------

             $modus = '*ADD';

             if ($inschrijvingId){

                 $sqlStat = "Select count(*) as aantal from $eventFile where id = $inschrijvingId";
                 $db->Query($sqlStat);

                 if ($eiRec = $db->Row())
                     if ($eiRec->aantal >= 1)
                         $modus = '*UPDATE';

             }


             $values = array();
             $where = array();

             $curDateTime = date('Y-m-d H:i:s');


             $values["event"] = MySQL::SQLValue($event, MySQL::SQLVALUE_TEXT);
             $values["editie"] = MySQL::SQLValue($pEditie, MySQL::SQLVALUE_TEXT);
             $values["persoon"] = MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);

             $values["naam"] = MySQL::SQLValue($adRec->adNaamVoornaam, MySQL::SQLVALUE_TEXT);
             $values["adres"] = MySQL::SQLValue($adRec->adAdres1, MySQL::SQLVALUE_TEXT);
             $values["postnr"] = MySQL::SQLValue($adRec->adPostnr, MySQL::SQLVALUE_TEXT);
             $values["gemeente"] = MySQL::SQLValue($adRec->adGemeente, MySQL::SQLVALUE_TEXT);
             $values["mail"] = MySQL::SQLValue($adRec->adMail, MySQL::SQLVALUE_TEXT);
             $values["tel"] = MySQL::SQLValue($adRec->adTel, MySQL::SQLVALUE_TEXT);

             $values["teBetalen"] = MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);


             if ($modus == '*UPDATE') {
                 $where["id"] = MySQL::SQLValue($inschrijvingId, MySQL::SQLVALUE_TEXT);
                 $db->UpdateRows($eventFile, $values, $where);
             }

             if ($modus == '*ADD') {

                 $values["datumInschrijving"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                 $GM = SSP_efin::GetNextGM($event);
                 $GMn = SSP_efin::CvtGmToNum($GM);

                 $values["GM"] = MySQL::SQLValue($GM, MySQL::SQLVALUE_TEXT);
                 $values["GMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

                 $inschrijvingId = $db->InsertRow($eventFile, $values);

             }

             // ----------------------------------------------------
             // Update inschrijving-id bij padel-interclub-deelnemer
             // ----------------------------------------------------

             $sqlStat = "Update padel_id_interclub_deelnemers set idInschrijvingId = $inschrijvingId where idEditie = '$pEditie' and idPersoon = '$pPersoon'";
             $db->Query($sqlStat);

             // ------------------------
             // Reg eventuele betalingen
             // ------------------------

             self::RegPadelInterclubBetaling($inschrijvingId);

             // -------------
             // Einde functie
             // -------------

             return true;


         }

         // ========================================================================================
         // Function: Padel Interclub: Registratie betaling
         //
         // In:	Event Inschrijving ID
         //
         // Return: None
         //
         // ========================================================================================
         static function RegPadelInterclubBetaling($pInschrijving){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // -----------------------------
             // Ophalen nodige (header-) Info
             // -----------------------------

             $event = self::GetPadelInterclubEvent();
             $file = self::GetPadelInterclubEventFile();

             $sqlStat = "Select * from padel_id_interclub_deelnemers where idInschrijvingId = $pInschrijving";
             $db->Query($sqlStat);

             if (! $idRec = $db->Row())
                 return;

             // --------------------------------------------------
             // Ophalen alle betalingen gekoppeld aan inschrijving
             // --------------------------------------------------


             $editie = $idRec->idEditie;

             $sqlStat = "Select sum(ebBedrag) as betaald from events_eb_event_betalingen where ebEvent = '$event' and ebEditie = '$editie' and ebEventInschrijving = $pInschrijving";

             $db->Query($sqlStat);

             if ($ebRec = $db->Row()){

                 $betaald = $ebRec->betaald;
                 if (! $betaald)
                     $betaald = 0;

                 $sqlStat = "Update padel_id_interclub_deelnemers set idBetaald = $betaald where idInschrijvingId = $pInschrijving";
                 $db->Query($sqlStat);

                 $sqlStat = "update $file set betaald = $betaald where id = $pInschrijving";

             }

             // -------------
             // Einde functie
             // -------------


         }


         // ========================================================================================
         // Function: Padel Interclub: Sturen mail met betaalverzoek
         //
         // In:	Editie
         //     Persoon
         //
         // Return: Mail verstuurd?
         //
         // ========================================================================================

         static function SndPadelInterclubBetaalMail($pEditie, $pPersoon){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));
             include_once(SX::GetClassPath("evim.class"));

             // ----------------------------
             // Ophalen nodige (header-)data
             // ----------------------------

             $adRec = SSP_db::Get_SSP_adRec($pPersoon);

             if (! $adRec)
                 return false;

             $sqlStat = "Select * from padel_ie_interclub_edities where ieEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $ieRec = $db->Row())
                 return false;

             $sqlStat = "Select * from padel_id_interclub_deelnemers where idEditie = '$pEditie' and idPersoon = '$pPersoon'";
             $db->Query($sqlStat);

             if (! $idRec = $db->Row())
                 return false;

             $inschrijvingId = $idRec->idInschrijvingId;

             $file = self::GetPadelInterclubEventFile();

             $sqlStat = "Select * from $file where id = $inschrijvingId";
             $db->Query($sqlStat);

             if (! $isRec = $db->Row())
                 return false;

             $GM = $isRec->GM;

             if (! $GM)
                 return false;


             // ---------------------
             // Mail naar inschrijver
             // ---------------------

             $mailSubject = "Schelle Sport Padel Interclub - Betaalverzoek";

             $arr_VARS = array();
             $arr_VALUES = array();

             $arr_VARS[] = "VOORNAAM_NAAM";
             $arr_VALUES[] = $adRec->adVoornaamNaam;

             $arr_VARS[] = "GM";
             $arr_VALUES[] = $GM;

             $arr_VARS[] = "EDITIE_NAAM";
             $arr_VALUES[] = $ieRec->ieNaam;

             $arr_VARS[] = "BEDRAG";
             $arr_VALUES[] = $idRec->idTeBetalen + 0;

             $mailBody = '<html><body>';
             $mailBody .= nl2br($ieRec->ieBetaalMailTemplate);
             $mailBody .= '</body></html>';

             // $mailBody = utf8_encode($mailBody);

             $mailBody = SSP_evim::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);

             $mailTo = $adRec->adMail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');

             // ------------------------------
             // Registratie in deelnemers-file
             // ------------------------------

             $sqlStat = "Update padel_id_interclub_deelnemers set idBetaalMailVerstuurd = 1, idBetaalMailVerstuurdOp = now() where idEditie = '$pEditie' and idPersoon = '$pPersoon'";
             $db->Query($sqlStat);

             // -------------
             // Einde functie
             // -------------

             return true;

         }

         // ========================================================================================
         // Functie: Padel Clubavond: Ophalen aantal inschrijvingen
         //
         // In: Editie
         //
         // Return: Aantal inschrijvingen
         // ========================================================================================

         static function GetClubavondAantalInschrijvingen($pEditie) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select count(*) as aantal from event_padel_clubavond where editie = '$pEditie' and status = 'A'";
             $db->Query($sqlStat);

             if (! $eventRec = $db->Row())
                 return 0;

             return $eventRec->aantal;

         }

         // ========================================================================================
         // Functie: Padel Clubavond: Ophalen aantal vrije plaatsen bepaalde editie
         //
         // In: Editie
         //
         // Return: Aantal plaatsen (0 = volzet, 999 = onbeperkt)
         // ========================================================================================

         static function GetClubavondAantalVrijePlaatsen($pEditie) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $sqlStat = "Select * from padel_ce_clubavond_edities where ceEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $ceRec = $db->Row())
                 return 999;

             $aantalPlaatsen = $ceRec->ceAantalToegestaan - $ceRec->ceAantalGereserveerd + 0;
             $aantalInschrijvingen = self::GetClubavondAantalInschrijvingen($pEditie);

             if ($aantalInschrijvingen == 0)
                 return $aantalPlaatsen;

             if ($aantalInschrijvingen < $aantalPlaatsen)
                 return ($aantalPlaatsen - $aantalInschrijvingen);
             else
                 return 0;
         }

         // ========================================================================================
         // Functie: Padel Clubavonden: Aanmaken HTML-snippet met aantal vrije plaatsen
          //
         // Return: HTML-snippet
         // ========================================================================================

         static function GetClubavondenAantalVrijePlaatsenHTML(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $html =  null;

             $sqlStat = "Select * from padel_ce_clubavond_edities where ceRecStatus = 'A' and ceDatum > current_date()";
             $db->Query($sqlStat);

             $htmlLines = array();

             while ($ceRec = $db->Row()){

                 $editie = $ceRec->ceEditie;
                 $editieNaam = $ceRec->ceNaam;
                 $afgesloten = $ceRec->ceInschrijvingAfgesloten;

                 $aantalOpen = self::GetClubavondAantalVrijePlaatsen($editie);

                 if ($aantalOpen > 0 and $afgesloten <> 1)
                     $htmlLines[] = "$editieNaam: Aantal vrije plaatsen = $aantalOpen";
                 else
                     $htmlLines[] = "$editieNaam: <span style='color:red; font-weight: bold'>VOLZET</span>";

             }

             foreach ($htmlLines as $htmlLine){

                 if (! $html)
                     $html = $htmlLine;
                 else
                     $html .= "<br/>$htmlLine";

             }

             if ($html){

                 $html = "<div class='jumbotron' style='padding: 10px; margin-bottom: 5px'>$html</div>";

             }


             // -------------
             // Einde functie
             // -------------

             return $html;

         }

         // ========================================================================================
         // Functie: Padel Clubavond: Update aantal inschrijvingen
         //
         // In: Editie
         //
         // Return: None...
         //
         // ========================================================================================

         static function UpdClubavondAantalInschrijvingen($pEditie){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from padel_ce_clubavond_edities where ceEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $ceRec = $db->Row())
                 return;

             $aantalInschrijvingen = self::GetClubavondAantalInschrijvingen($pEditie);

             $afgesloten = $ceRec->ceInschrijvingAfgesloten;
             $aantalVrijePlaatsen = self::GetClubavondAantalVrijePlaatsen($pEditie);
             if ($aantalVrijePlaatsen <= 0)
                 $afgesloten = 1;

             $sqlStat = "Update padel_ce_clubavond_edities set ceAantalIngeschreven = $aantalInschrijvingen, ceInschrijvingAfgesloten = $afgesloten where ceEditie = '$pEditie'";

             $db->Query($sqlStat);

         }
         // ========================================================================================
         // Functie: Padel Clubavond: Ophalen editie-naam
         //
         // In: Editie
         //
         // Return: Naam
         //
         // ========================================================================================

         static function GetClubavondEditienaam($pEditie){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select ceNaam from padel_ce_clubavond_edities where ceEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $ceRec = $db->Row())
                 return "xxx";

             return $ceRec->ceNaam;

         }

         // ========================================================================================
         // Functie: Padel Clubavond: Verwerken inschrijving (versturen bevestigingsmail)
         //
         // In: Inschrijving ID
         //
         // Return: Mail verstuurd?
         // ========================================================================================

         static function HdlClubavondInschrijving($pInschrijving){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             include_once(SX::GetSxClassPath("tools.class"));
             include_once(SX::GetClassPath("evim.class"));

             // ---------------------------------
             // Ophalen nodige (header-) gegevens
             // ---------------------------------

             $sqlStat = "Select * from event_padel_clubavond where id = $pInschrijving";
             $db->Query($sqlStat);

             if (! $eventRec = $db->Row())
                 return false;

             $editie = $eventRec->editie;

             $sqlStat = "Select * from padel_ce_clubavond_edities where ceEditie = '$editie'";
             $db->Query($sqlStat);

             if (! $ceRec = $db->Row())
                 return false;


             $editieNaam = $ceRec->ceNaam;

             // ---------------------
             // Mail naar inschrijver
             // ---------------------

             $mailSubject = "Schelle Sport Padel Clubavond - Bevestiging";

             $arr_VARS = array();
             $arr_VALUES = array();

             $arr_VARS[] = "NAAM";
             $arr_VALUES[] = $eventRec->naam;

             $arr_VARS[] = "NAAM2";
             $arr_VALUES[] = $eventRec->naam2;

             $arr_VARS[] = "GM";
             $arr_VALUES[] = $GM;

             $arr_VARS[] = "EDITIE_NAAM";
             $arr_VALUES[] = $editieNaam;

             $arr_VARS[] = "TEBETALEN";
             $arr_VALUES[] = $eventRec->teBetalen + 0;

             $mailBody = '<html><body>';
             $mailBody .= nl2br($ceRec->ceBevestigingsmailTemplate);
             $mailBody .= '</body></html>';

             // $mailBody = utf8_encode($mailBody);

             $mailBody = SSP_evim::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);

             $mailTo = $eventRec->mail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');

             // ---------------
             // Mail naar padel
             // ---------------

             $mailSubject = "Padel Clubavond Inschrijving voor $editieNaam";
             $mailTo= "padel@schellesport.be";
             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

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

             $mailBody .= "Beste Team Padel,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";


             $mailBody .= "Er was een inschrijving voor Padel Clubavond:  $editieNaam" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Deelnemer: " . $eventRec->naam;
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->naam2){

                 $mailBody .= "Deelnemer 2: " . $eventRec->naam2;
                 $mailBody .= "<br/><br/>". "\r\n";

             }

             $mailBody .= "Mail-adres: " . $eventRec->mail;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Tel: " . $eventRec->tel;
             $mailBody .= "<br/>" . "\r\n";

             if ($eventRec->opmerkingen)
                 $mailBody .= "<br/><b>Opmerkingen:</b><br/>" . nl2br(utf8_encode($eventRec->opmerkingen)) . "\r\n";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,". "\r\n";
             $mailBody .= "<br/><br/>Schelle Sport Secretariaat". "\r\n";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailBody = utf8_encode($mailBody);

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');

             // ----------------------------------------------------------------
             // Update aantal inschrijvingen & Zet op "afgesloten" indien volzet
             // ----------------------------------------------------------------

             self::UpdClubavondAantalInschrijvingen($editie);


         }

         // ========================================================================================
         // Function: Padel Lessen: Verwerk Inschrijving
         //
         // In:	Event Inschrijving ID
         //
         // Return: Volledig verwerkt? (true/false)
         //
         // ========================================================================================
         static function HdlPadelLesInschrijving($pInschrijving){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             include_once(SX::GetClassPath("events.class"));
             include_once(SX::GetClassPath("efin.class"));

             // ------------
             // ophalen info
             // ------------

             $sqlStat = "Select * from event_padel_les where id = $pInschrijving";
             $db->Query($sqlStat);

             if (! $eventRec = $db->Row())
                 return false;

             $lesmoment = $eventRec->lesmoment;
             $naam = utf8_encode($eventRec->naam);
             $deelnemer2 = utf8_encode($eventRec->deelnemer2);
             $deelnemer3 = utf8_encode($eventRec->deelnemer3);
             $deelnemer4 = utf8_encode($eventRec->deelnemer4);

             $sqlStat = "Select * from padel_lm_lesmomenten where lmId = $lesmoment";
             $db->Query($sqlStat);

             if (! $lmRec = $db->Row())
                 return false;

             $lesmomentNaam = $lmRec->lmNaam;

             // ----------------------
             // Ophalen GM (als nodig)
             // ----------------------

             $GM = $eventRec->GM;
             $GMn = $eventRec->GMn;

             if (! $GM) {

                 $GM = SSP_efin::GetNextGM('*PADEL_LES');
                 $GMn = SSP_efin::CvtGmToNum($GM);

             }

             if (! $GM)
                 return false;

             // --------------------
             // Ophalen "te betalen"
             // --------------------

             $teBetalen = 0;

             if ($eventRec->aantalDeelnemers == 2)
                $teBetalen = ($lmRec->lmPrijsPer2 * 2 * 6) + 0;
             if ($eventRec->aantalDeelnemers == 3)
                 $teBetalen = ($lmRec->lmPrijsPer3 * 3 * 6) + 0;
             if ($eventRec->aantalDeelnemers == 4)
                 $teBetalen = ($lmRec->lmPrijsPer4 * 4 * 6) + 0;

             if (! $teBetalen)
                 return false;

             // -----------------------------
             // Update inschrijvings-gegevens
             // -----------------------------

             $sqlStat = "Update  event_padel_les set teBetalen = $teBetalen, GM = '$GM', GMn = $GMn where id = $pInschrijving";
             $db->Query($sqlStat);

             // ---------------------------------------------------------------
             // Zet inschrijvings-moment als VOLZET (voorlopig eenvoudige regel)
             // ----------------------------------------------------------------

             $sqlStat = "Update padel_lm_lesmomenten set lmVolzet = 1 where lmId = $lesmoment";
             $db->Query($sqlStat);

             // ----------------------------------------------
             // Versturen bevestigingsmail naar de inschrijver
             // ----------------------------------------------

             $mailSubject = "Schelle Sport - Uw inschrijving voor padel-lessen";

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

             $mailBody .= "Beste $eventRec->naam,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "We noteerden jouw inschrijving voor onze padel lessen op: <b>$lesmomentNaam</b>";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Naam: " . $naam;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Mail-adres: " . $eventRec->mail;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Tel: " . $eventRec->tel;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Aantal deelnemers: " . $eventRec->aantalDeelnemers;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Deelnemer 2: " . $deelnemer2;
             $mailBody .= "<br/>" . "\r\n";

             if ($deelnemer3){
                 $mailBody .= "Deelnemer 3: " . $deelnemer3;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($deelnemer4){
                 $mailBody .= "Deelnemer 4: " . $deelnemer4;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->opmerkingen)
                 $mailBody .= "<br/><b>Uw opmerkingen:</b><br/>" . nl2br(utf8_encode($eventRec->opmerkingen)) . "\r\n";

             $GM = $eventRec->GM;
             $teBetalen = $eventRec->teBetalen + 0;
             $rekening = "BE56 0015 0154 9488";

             $mailBody .= "<br/><br/><b>Gelieve het deelname bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met gestructureerde mededeling: $GM</b>";


             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Alvast bedankt voor je inschrijving!";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Padel";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $eventRec->mail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "padel@schellesport.be";
             $fromName = "Schelle Sport - Padel";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor padel lessen";

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

             $mailBody .= "Beste Team Padel,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Er was een inschrijving voor de Padel Clubavond op$ $lesmomentNaam" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Mail-adres: " . $eventRec->mail;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Tel: " . $eventRec->tel;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Aantal deelnemers: " . $eventRec->aantalDeelnemers;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Deelnemer 2: " . $deelnemer2;
             $mailBody .= "<br/>" . "\r\n";

             if ($deelnemer3){
                 $mailBody .= "Deelnemer 3: " . $deelnemer3;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($deelnemer4){
                 $mailBody .= "Deelnemer 4: " . $deelnemer4;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->opmerkingen)
                 $mailBody .= "<br/><b>Opmerkingen:</b><br/>" . nl2br(utf8_encode($eventRec->opmerkingen)) . "\r\n";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,". "\r\n";
             $mailBody .= "<br/><br/>Schelle Sport Secretariaat". "\r\n";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $eventRec->mail;
             $mailBCC = "";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, "padel@schellesport.be", $mailBCC, $fromMail, $fromName,'','UTF-8');

             // -------------
             // Einde functie
             // -------------

             return true;

         }



          // -----------
         // EINDE CLASS
         // ----------


 	}      
?>