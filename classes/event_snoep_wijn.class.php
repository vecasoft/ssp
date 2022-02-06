<?php 

     class SSP_event_snoep_wijn { // define the class

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


             $sqlStat = "Select * from event_snoep_wijn where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             // ----------------------
             // Ophalen event & editie
             // ----------------------

             $event = '*SNOEP_WIJN';

             if ($eventRec->editie)
                 $editie = $eventRec->editie;
             else
                 $editie = SSP_events::GetEventEditie($event);

             // ----------------------------
             // Ophalen aan te vullen velden
             // ----------------------------

             $teBetalen = 0;

             if ($eventRec->aantalSnoep)
                $teBetalen += ($eventRec->aantalSnoep * 5);
             if ($eventRec->aantalPralines)
                 $teBetalen += ($eventRec->aantalPralines * 10);
             if ($eventRec->aantalWijnRood)
                 $teBetalen += ($eventRec->aantalWijnRood * 8);
             if ($eventRec->aantalWijnWit)
                 $teBetalen += ($eventRec->aantalWijnWit * 8);
             if ($eventRec->aantalWijnWit2)
                 $teBetalen += ($eventRec->aantalWijnWit2 * 8);
             if ($eventRec->aantalWijnRose)
                 $teBetalen += ($eventRec->aantalWijnRose * 8);
             if ($eventRec->aantalCava)
                 $teBetalen += ($eventRec->aantalCava * 11);

             $teBetalen += 0;

             $curDateTime = date('Y-m-d H:i:s');

             if ($eventRec->GM) {

                 $GM = $eventRec->GM;
                 $GMn = $eventRec->GMn;

             } else {

                 $GM = SSP_efin::GetNextGM('*EVENT_SNOEP_WIJN');
                 $GMn = SSP_efin::CvtGmToNum($GM);
             }

             // -----------------
             // Update event-file
             // -----------------

             $values = array();
             $where = array();

             $values['event'] = MySQL::SQLValue($event, MySQL::SQLVALUE_TEXT);
             $values['editie'] = MySQL::SQLValue($editie, MySQL::SQLVALUE_TEXT);

             $values["teBetalen"] =  MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);

             $values["GM"] =  MySQL::SQLValue($GM);
             $values["GMn"] =  MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

             if (! $pUpdate)
                $values["datumInschrijving"] =  MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

             $where["id"] =  MySQL::SQLValue($pID, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("event_snoep_wijn", $values, $where);


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

             $sqlStat = "Select * from event_snoep_wijn where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             $naam = utf8_encode($eventRec->naam);

             // ----------------
             // BevestigingsMail
             // ----------------


             $mailSubject = "Schelle Sport - Snoep & Wijn actie";

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

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalSnoep) {
                 $mailBody .= "Snoep: " . $eventRec->aantalSnoep . " (aan 5 EUR / pakje)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalPralines) {
                 $mailBody .= "Pralines: " . $eventRec->aantalPralines . " (aan 10 EUR / pakje)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnRood) {
                 $mailBody .= "Wijn Rood: " . $eventRec->aantalWijnRood . " (aan 8 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnWit) {
                 $mailBody .= "Wijn Wit (Sauvignon Blanc): " . $eventRec->aantalWijnWit . " (aan 8 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnWit2) {
                 $mailBody .= "Wijn Wit (Chardonnay): " . $eventRec->aantalWijnWit2 . " (aan 8 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnRose) {
                 $mailBody .= "Wijn Rosé: " . $eventRec->aantalWijnRose . " (aan 8 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCava) {
                 $mailBody .= "Cava: " . $eventRec->aantalCava . " (aan 11 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->opmerkingen)
                 $mailBody .= "<br/><b>Uw opmerkingen:</b><br/>" . nl2br(utf8_encode($eventRec->opmerkingen)) . "\r\n";

             $GM = $eventRec->GM;
             $teBetalen = $eventRec->teBetalen + 0;
             $rekening = "BE56 0015 0154 9488";

             $mailBody .= "<br/><br/><b>Gelieve het totaal bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met gestructureerde mededeling: $GM</b>";
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

             $mailSubject = "Schelle Sport - Inschrijving voor Snoep & Wijn";

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

             $mailBody .= "Er was een inschrijving voor Snoep & Wijn verkoop:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";


             $mailBody .= "Bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalSnoep) {
                 $mailBody .= "Snoep: " . $eventRec->aantalSnoep;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalPralines) {
                 $mailBody .= "Palines: " . $eventRec->aantalPralines;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnRood) {
                 $mailBody .= "Wijn Rood: " . $eventRec->aantalWijnRood;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnWit) {
                 $mailBody .= "Wijn Wit (Sauvignon Blanc): " . $eventRec->aantalWijnWit;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnWit2) {
                 $mailBody .= "Wijn Wit (Chardonnay): " . $eventRec->aantalWijnWit2;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalWijnRose) {
                 $mailBody .= "Wijn Rosé: " . $eventRec->aantalWijnRose;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCava) {
                 $mailBody .= "Cava: " . $eventRec->aantalCava;
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

             SX_tools::SendMail($mailSubject, $mailBody, "voetbal@schellesport.be", $mailBCC, $fromMail, $fromName,'','UTF-8');

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