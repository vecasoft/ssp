<?php 

     class SSP_event_mosselen { // define the class

         // ========================================================================================
         // Function: Ophalen event-naam scan-even (*HERFST_EETDAGEN-202x)
         //
         // In:	Geen
         //
         // Uit: Scan event
         //
         // ========================================================================================

         static function GetScanEvent(){

            $year = date("Y");
            $scanEvent = "*HERFST_EETDAGEN-$year";

            return $scanEvent;

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

             $sqlStat = "Select * from event_herfst_eetdagen where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             $naam = $eventRec->naam;
             $shift = $eventRec->shift;

             $formuleCode = $eventRec->eventFormule;

             $taRec = SSP_db::Get_SX_taRec('EVENT_EET_FORMULE',$formuleCode);

             if ($taRec)
                 $formuleNaam = $taRec->taName;
             else
                 $formuleNaam = $formuleCode;

             $sqlStat = "Select * from event_herfst_eetdagen_shifts where id = $shift";
             $db->Query($sqlStat);

             $shiftRec = $db->Row();

             if (! $shiftRec)
                 return;

             $datum = $shiftRec->naam;

             // ------------
             // Kortingcodes
             // ------------

             $kortingCodes = array();

             if ($eventRec->korting1)
                 $kortingCodes[] = $eventRec->korting1;
             if ($eventRec->korting2)
                 $kortingCodes[] = $eventRec->korting2;
             if ($eventRec->korting3)
                 $kortingCodes[] = $eventRec->korting3;
             if ($eventRec->korting4)
                 $kortingCodes[] = $eventRec->korting4;
             if ($eventRec->korting5)
                 $kortingCodes[] = $eventRec->korting5;

             $aantalKortingbonnen = count($kortingCodes);

             // ----------------
             // BevestigingsMail
             // ----------------

             $mailSubject = "Schelle Sport - Uw Inschrijving voor herfst-eetdagen - $formuleNaam";

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
             $mailBody .= "Uw deelname aan de herfst-eetdagen werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Formule: $formuleNaam";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalStoofpotjeVwWarm) {
                 $mailBody .= "Stoofpotje (warm - frieten): " . $eventRec->aantalStoofpotjeVwWarm . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalStoofpotjeVwKoudFrieten) {
                 $mailBody .= "Stoofpotje (koud - frieten): " . $eventRec->aantalStoofpotjeVwKoudFrieten . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalStoofpotjeVwKoudKroketten) {
                 $mailBody .= "Stoofpotje (koud - kroketten): " . $eventRec->aantalStoofpotjeVwKoudKroketten . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVideeVwWarm) {
                 $mailBody .= "Videe (warm - frieten): " . $eventRec->aantalVideeVwWarm . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVideeVwKoudFrieten) {
                 $mailBody .= "Videe (koud - frieten): " . $eventRec->aantalVideeVwKoudFrieten . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVideeVwKoudKroketten) {
                 $mailBody .= "Videe (koud - kroketten): " . $eventRec->aantalVideeVwKoudKroketten . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalStoofpotjeKidsWarm) {
                 $mailBody .= "Stoofpotje KIDS (warm - kroketten): " . $eventRec->aantalStoofpotjeKidsWarm . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalStoofpotjeKidsKoudFrieten) {
                 $mailBody .= "Stoofpotje KIDS (koud - frieten): " . $eventRec->aantalStoofpotjeKidsKoudFrieten . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalStoofpotjeKidsKoudKroketten) {
                 $mailBody .= "Stoofpotje KIDS (koud - kroketten): " . $eventRec->aantalStoofpotjeKidsKoudKroketten . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVideeKidsWarm) {
                 $mailBody .= "Videe KIDS (warm - frieten): " . $eventRec->aantalVideeKidsWarm . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVideeKidsKoudFrieten) {
                 $mailBody .= "Videe KIDS (koud - frieten): " . $eventRec->aantalVideeKidsKoudFrieten . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVideeKidsKoudKroketten) {
                 $mailBody .= "Videe KIDS (koud - kroketten): " . $eventRec->aantalVideeKidsKoudKroketten . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalKaaskroketjes) {
                 $mailBody .= "Kaastkroketten: " . $eventRec->aantalKaaskroketjes . " (aan 6 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalKaaskroketjes) {
                 $mailBody .= "Garnaalkroketten: " . $eventRec->aantalKaaskroketjes . " (aan 10 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }


             if ($eventRec->aantalRijstpap) {
                 $mailBody .= "Potjes rijstpap: " . $eventRec->aantalRijstpap . " (aan 3 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalChocoMousse) {
                 $mailBody .= "Potjes chocomousse: " . $eventRec->aantalChocoMousse . " (aan 3 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($aantalKortingbonnen) {
                 $mailBody .= "<br/>" . "\r\n";
                 $mailBody .= "Er werd $aantalKortingbonnen X 5 EUR korting toegekend";
                 $mailBody .= "<br/>" . "\r\n";
             }

             $GM = $eventRec->GM;
             $teBetalen = $eventRec->teBetalen + 0;
             $rekening = "BE56 0015 0154 9488";

             $mailBody .= "<br><b>Gelieve het totaal bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met gestructureerde mededeling: $GM</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Alvast bedankt voor je deelname!";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Bestuur";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $eventRec->mail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor herfst-eetdagen - $formuleNaam";

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

             $mailBody .= "Er was een inschrijving voor de herfst-eetdagen:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: $formuleNaam";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal hoofdgerechten: " . $eventRec->aantalEters;
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->opmerkingen)
                 $mailBody .= "<b>Opmerkingen:</b><br/>" . nl2br($eventRec->opmerkingen) . "\r\n";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,". "\r\n";
             $mailBody .= "<br/><br/>Schelle Sport Secretariaat". "\r\n";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $eventRec->mail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, "horeca@schellesport.be", $mailBCC, $fromMail, $fromName,'','UTF-8');

             // ------------------------
             // Registratie kortingcodes
             // ------------------------

             $scanEvent = self::GetScanEvent();

             if ($aantalKortingbonnen){

                 foreach ($kortingCodes as $kortingCode){
                     SSP_events::RegKortingCode($kortingCode, $scanEvent);
                 }

             }

             SSP_events::UpdAantalKorting('*HERFST_EETDAGEN', $pID);

             // -------------
             // Einde functie
             // -------------

             return;

         }


         // -----------
         // EINDE CLASS
         // ----------


 	}      
?>