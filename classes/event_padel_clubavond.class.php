<?php 

     class SSP_event_padel_clubavond { // define the class

         // ========================================================================================
         // Function: Aanvullen event-gegevens
         //
         // In:	ID Inschrijving
         //
         // ========================================================================================

         static function UpdEvent($pID, $pUpdate = false){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetClassPath("events.class"));
             include_once(SX::GetClassPath("efin.class"));

             // --------------------------
             // Ophalen inschrijving event
             // --------------------------

             $sqlStat = "Select * from event_padel_clubavond where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             // ----------------------
             // Ophalen event & editie
             // ----------------------

             $event = '*PADEL_CLUBAVOND';

             if ($eventRec->editie)
                $editie = $eventRec->editie;
             else
                $editie = SSP_events::GetEventEditie($event);

             // ----------------------------
             // Ophalen aan te vullen velden
             // ----------------------------

             $sqlStat = "Select * from events_ee_event_edities where eeEvent = '$event' and eeEditie = '$editie'";
             $db->Query($sqlStat);

             $eeRec = $db->Row();

             if (! $eeRec)
                 return;

             $teBetalen = $eeRec->eeBedrag + 0;

             $curDateTime = date('Y-m-d H:i:s');

             if (! $pUpdate) {
                 $GM = SSP_efin::GetNextGM('*EVENT_PADEL_CLUBAVOND');
                 $GMn = SSP_efin::CvtGmToNum($GM);
             }

             // -----------------
             // Update event-file
             // -----------------

             $values = array();
             $where = array();

             $values["teBetalen"] =  MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);

             $values['event'] = MySQL::SQLValue($event, MySQL::SQLVALUE_TEXT);
             $values['editie'] = MySQL::SQLValue($editie, MySQL::SQLVALUE_TEXT);

             if (! $pUpdate) {
                 $values["GM"] = MySQL::SQLValue($GM);
                 $values["GMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

                 $values["datumInschrijving"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
             }

             $where["id"] =  MySQL::SQLValue($pID, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("event_padel_clubavond", $values, $where);


             // -------------
             // Einde functie
             // -------------


         }

         // ========================================================================================
         // Function: Versturen bevestigingsmails
         //
         // In:	ID Inschrijving
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function SndBevestigingsMails($pID){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $sqlStat = "Select * from event_padel_clubavond where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             $event = $eventRec->event;
             $editie = $eventRec->editie;

             $sqlStat = "Select * from events_ee_event_edities where eeEvent = '$event' and eeEditie = '$editie'";
             $db->Query($sqlStat);

             $eeRec = $db->Row();

             if (! $eeRec)
                 return;

             $datum = $eeRec->eeDatumVan;
             $datumE = sx_tools::EdtDate($datum);


             $naam = utf8_encode($eventRec->naam);

             // ----------------
             // BevestigingsMail
             // ----------------

             $mailSubject = "Schelle Sport - Padel Clubavond $datumE";

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

             $mailBody .= "We noteerden jouw inschrijving voor onze padel clubavond op $datumE.";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Naam: " . $naam;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Adres: " . $eventRec->adres;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Gemeente: " . $eventRec->gemeente;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Mail-adres: " . $eventRec->mail;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Tel: " . $eventRec->tel;
             $mailBody .= "<br/>" . "\r\n";

             if ($eventRec->opmerkingen)
                 $mailBody .= "<br/><b>Uw opmerkingen:</b><br/>" . nl2br(utf8_encode($eventRec->opmerkingen)) . "\r\n";

             $GM = $eventRec->GM;
             $teBetalen = $eventRec->teBetalen + 0;
             $rekening = "BE56 0015 0154 9488";

             if ($eventRec->status == 'W'){

                 $mailBody .= "<br/><br/>Deze clubavond is momenteel VOLZET - Je staat op de reservelijst.";
                 $mailBody .= "<br/><br/><b>Gelieve nog niets te betalen</b>";


             }else {


                 $mailBody .= "<br/><br/><b>Gelieve het deelname bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met gestructureerde mededeling: $GM</b>";

             }


             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Alvast bedankt voor je inschrijving!";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Bestuur";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $eventRec->mail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "padel@schellesport.be";
             $fromName = "Schelle Sport - Padel";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Padel Clubavond $datumE";

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

             $mailBody .= "Er was een inschrijving voor de Padel Clubavond op $datumE" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Adres: " . $eventRec->adres;
             $mailBody .= "<br/>" . "\r\n";

             $mailBody .= "Gemeente: " . $eventRec->gemeente;
             $mailBody .= "<br/>" . "\r\n";

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

         // ========================================================================================
         // Functie: Ophalen aantal vrije plaatsen bepaalde editie
         //
         // In: Editie
         //
         // Return: Aantal plaatsen (0 = volzet, 999 = onbeperkt)
         // ========================================================================================

         static function GetAantalVrijePlaatsen($pEditie) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $event = '*PADEL_CLUBAVOND';

             $sqlStat = "Select * from events_ee_event_edities where eeEvent = '$event' and eeEditie = '$pEditie'";
             $db->Query($sqlStat);

             if (! $eeRec = $db->Row())
                 return 999;

             if (! $eeRec->eeMaxAantalDeelnemers)
                 return 999;

             $aantalPlaatsen = $eeRec->eeMaxAantalDeelnemers;

             $sqlStat = "Select count(*) as aantal from event_padel_clubavond where editie = '$pEditie' and status = 'A'";
             $db->Query($sqlStat);

             if (! $eventRec = $db->Row())
                 return $aantalPlaatsen;

             $aantalInschrijvingen = $eventRec->aantal;

             if ($aantalInschrijvingen == 0)
                 return $aantalPlaatsen;

             if ($aantalInschrijvingen < $aantalPlaatsen)
                 return ($aantalPlaatsen - $aantalInschrijvingen);
             else
                return 0;


         }

         // ========================================================================================
         // Functie: Ophalen aantal vrije plaatsen bepaalde editie b(HTML snippet)
         //
         // In: Editie
         //
         // Return: HTML snippet
         // ========================================================================================

         static function GetAantalVrijePlaatsenHTML($pEditie){

             include_once(Sx::GetClassPath("settings.class"));

             $green = SSP_settings::GetBackgroundColor('green');
             $yellow = SSP_settings::GetBackgroundColor('yellow');
             $red = SSP_settings::GetBackgroundColor('red');
             $orange = SSP_settings::GetBackgroundColor('orange');
             $blue = SSP_settings::GetBackgroundColor('blue');
             $grey = SSP_settings::GetBackgroundColor('grey');

             // -----------------------------
             // Ophalen aantal vrije plaatsen
             // -----------------------------

             $aantalVrijePlaatsen = self::GetAantalVrijePlaatsen($pEditie);

             // -----------------------------------------
             // Geen beperking -> Geen HTML snippet nodig
             // -----------------------------------------

             if ($aantalVrijePlaatsen == 999)
                 return null;

             if ($aantalVrijePlaatsen == 0)
                 $html = "<div style='background-color: $red; padding: 10px; margin-bottom: 5px; font-weight: bold'>VOLZET >> Je inschrijving komt op een wachtlijst</div>";
             else
                 $html = "<div style='background-color: $green; padding: 10px; margin-bottom: 5px; font-weight: bold'>Vrije plaatsen: $aantalVrijePlaatsen</div>";

             return $html;










         }



         // -----------
         // EINDE CLASS
         // ----------


 	}      
?>