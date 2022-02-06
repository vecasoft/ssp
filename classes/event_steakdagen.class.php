<?php 

     class SSP_event_steakdagen { // define the class

         // ========================================================================================
         // Function: Ophalen event-naam scan-even (*STEAKDAGEN-202x)
         //
         // In:	Geen
         //
         // Uit: Scanevent ID
         //
         // ========================================================================================

         static function GetScanEvent(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_scanning_events where seEventCode like '*STEAKDAGEN%' order by seDatumVan desc";
             $db->Query($sqlStat);

             if ($seRec = $db->Row())
                 $scanEvent = $seRec->seId;

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

             $sqlStat = "Select * from event_steakdagen where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             $naam = $eventRec->naam;
             $shift = $eventRec->shift;

             $sqlStat = "Select * from event_es_eet_shifts where esId = $shift";
             $db->Query($sqlStat);

             $shiftRec = $db->Row();

             if (! $shiftRec)
                 return;

             $datum = $shiftRec->esNaam;
             $tijdstip = $eventRec->tijdstip;

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

             $taRec = SSP_db::Get_SX_taRec('EVENT_EET_FORMULE',$eventRec->formule);
             $formule = $taRec->taName;

             $mailSubject = "Schelle Sport - Uw Inschrijving voor de Steakdagen - $formule";

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
             $mailBody .= "Uw deelname aan de steakdagen werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Formule: <b>$formule</b>";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Datum: <b>$datum</b>";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Tijdstip: <b>$tijdstip</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal personen: " . $eventRec->aantalEters;
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalSteakS1B1) {
                 $mailBody .= "Steak Natuur - Bleu: " . $eventRec->aantalSteakS1B1 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS1B2) {
                 $mailBody .= "Steak Natuur - Saignant: " . $eventRec->aantalSteakS1B2 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS1B3) {
                 $mailBody .= "Steak Natuur - Bien cuit: " . $eventRec->aantalSteakS1B3 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS2B1) {
                 $mailBody .= "Steak Champignon - Bleu: " . $eventRec->aantalSteakS2B1 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS2B2) {
                 $mailBody .= "Steak Champignon - Saignant: " . $eventRec->aantalSteakS2B2 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS2B3) {
                 $mailBody .= "Steak Champignon - Bien cuit: " . $eventRec->aantalSteakS2B3 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS3B1) {
                 $mailBody .= "Steak Peper - Bleu: " . $eventRec->aantalSteakS3B1 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS3B2) {
                 $mailBody .= "Steak Peper - Saignant: " . $eventRec->aantalSteakS3B2 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }
             if ($eventRec->aantalSteakS3B3) {
                 $mailBody .= "Steak Peper - Bien cuit : " . $eventRec->aantalSteakS3B3 . " (aan 20 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVolauvent) {
                 $mailBody .= "Vol-au-vent: " . $eventRec->aantalVolauvent . " (aan 15 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalRagout) {
                 $mailBody .= "Wildragout: " . $eventRec->aantalRagout . " (aan 17 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalVolauventKids) {
                 $mailBody .= "Vol-au-vent (kids): " . $eventRec->aantalVolauventKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFishsticks) {
                 $mailBody .= "Fishsticks (kids): " . $eventRec->aantalFishsticks . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCurryworst) {
                 $mailBody .= "Curryworsten (kids): " . $eventRec->aantalCurryworst . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFrieten) {
                 $mailBody .= "Frieten: " . $eventRec->aantalFrieten . " (Inbegrepen)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalBrood) {
                 $mailBody .= "Brood: " . $eventRec->aantalBrood . " (Inbegrepen)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalChocoMousse) {
                 $mailBody .= "Potjes chocomousse: " . $eventRec->aantalChocoMousse . " (aan 3 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalRijstpap) {
                 $mailBody .= "Potjes rijstpap: " . $eventRec->aantalRijstpap . " (aan 3 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalIJsstracciatella) {
                 $mailBody .= "ijspotje Scoop stracciatella: " . $eventRec->aantalIJsstracciatella . " (aan 4 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalIJsVanille) {
                 $mailBody .= "ijspotje Scoop vanille: " . $eventRec->aantalIJsVanille . " (aan 4 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             $korting = $eventRec->bedragKorting + 0;

             if ($korting > 0) {
                 $mailBody .= "<br/>" . "\r\n";
                 $mailBody .= "Er werd in totaal <b>$korting</b> EUR korting toegekend";
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
             $mailBCC = "karel.foque@skynet.be; gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor Steakdagen";

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

             $mailBody .= "Er was een inschrijving voor de Steakdagen:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: $formule";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Tijdstip $tijdstip";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal: " . $eventRec->aantalEters;
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

             if ($aantalKortingbonnen){

                 foreach ($kortingCodes as $kortingCode){
                     SSP_events::RegKortingCode($kortingCode, '*STEAKDAGEN');
                 }

             }

             SSP_events::UpdAantalKorting('*STEAKDAGEN', $pID);

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