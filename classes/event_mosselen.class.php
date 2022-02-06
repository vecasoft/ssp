<?php 

     class SSP_event_mosselen { // define the class

         // ========================================================================================
         // Function: Ophalen event-naam scan-even (*MOSSELWEEK-202x)
         //
         // In:	Geen
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function GetMosselenScanEvent(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_scanning_events where seEventCode like '*MOSSELEN%' order by seDatumVan desc";
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

             $sqlStat = "Select * from event_mosselen where id = $pID";
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

             $mailSubject = "Schelle Sport - Uw Inschrijving voor Mosselweek - $formule";

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
             $mailBody .= "Uw deelname aan de mosselweek werd geregistreerd met volgende gegevens:". "\r\n";
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

             if ($eventRec->aantalMosselenVw) {
                 $mailBody .= "Mosselen: " . $eventRec->aantalMosselenVw . " (aan 22 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashVw) {
                 $mailBody .= "Goulash: " . $eventRec->aantalGoulashVw . " (aan 14 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalMosselenKids) {
                 $mailBody .= "Mosselen (kids): " . $eventRec->aantalMosselenKids . " (aan 12 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashKids) {
                 $mailBody .= "Goulash (kids): " . $eventRec->aantalGoulashKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCurryworst) {
                 $mailBody .= "Curryworst (kids): " . $eventRec->aantalCurryworst . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFishsticks) {
                 $mailBody .= "aantalFishsticks (kids): " . $eventRec->aantalFishsticks . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFrieten) {
                 $mailBody .= "Porties friet: " . $eventRec->aantalFrieten . " (Inbegrepen)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalBrood) {
                 $mailBody .= "Porties brood: " . $eventRec->aantalBrood . " (Inbegrepen)";
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

             $mailSubject = "Schelle Sport - Inschrijving voor Mosselweek";

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

             $mailBody .= "Er was een inschrijving voor de Mosselweek:" . "\r\n";
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
                     SSP_events::RegKortingCode($kortingCode, '*MOSSELEN');
                 }

             }

             SSP_events::UpdAantalKorting('*MOSSELEN', $pID);

             // -------------
             // Einde functie
             // -------------

             return;

         }


         // ========================================================================================
         // Function: Mosselen "op de club" - Versturen bevestigingsmails
         //
         // In:	ID Inschrijving
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function Mosselen_op_de_club_SndBevestigingsMails($pID){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $sqlStat = "Select * from event_mosselen_op_de_club where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             $naam = $eventRec->naam;
             $shift = $eventRec->shift;

             $sqlStat = "Select * from event_mosselen_shifts_op_de_club where id = $shift";
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

             $mailSubject = "Schelle Sport - Uw Inschrijving voor Mosselweek - eten op de club";

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
             $mailBody .= "Uw deelname aan de mosselweek werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Formule: Eten op de club";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Plaats: Sportcafé";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal personen: " . $eventRec->aantalEters;
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalMosselenVw) {
                 $mailBody .= "Mosselen: " . $eventRec->aantalMosselenVw . " (aan 22 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashVw) {
                 $mailBody .= "Goulash: " . $eventRec->aantalGoulashVw . " (aan 14 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalMosselenKids) {
                 $mailBody .= "Mosselen (kids): " . $eventRec->aantalMosselenKids . " (aan 12 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashKids) {
                 $mailBody .= "Goulash (kids): " . $eventRec->aantalGoulashKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCurryworst) {
                 $mailBody .= "Curryworst (kids): " . $eventRec->aantalCurryworst . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFrieten) {
                 $mailBody .= "Porties friet: " . $eventRec->aantalFrieten . " (Inbegrepen)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalBrood) {
                 $mailBody .= "Porties brood: " . $eventRec->aantalBrood . " (Inbegrepen)";
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
             $mailBCC = "karel.foque@skynet.be";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor Mosselweek";

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

             $mailBody .= "Er was een inschrijving voor de Mosselweek:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: Eten op de club";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Datum: $datum";
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

             $scanEvent = self::GetMosselenScanEvent();

             if ($aantalKortingbonnen){

                 foreach ($kortingCodes as $kortingCode){
                     SSP_events::RegKortingCode($kortingCode, '*MOSSELEN');
                 }

             }

             SSP_events::UpdAantalKorting('*MOSSELEN1', $pID);

             // -------------
             // Einde functie
             // -------------

             return;

         }

         // ========================================================================================
         // Function: Mosselen "Take Away" - Versturen bevestigingsmails
         //
         // In:	ID Inschrijving
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function Mosselen_take_away_SndBevestigingsMails($pID){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------


             $sqlStat = "Select * from event_mosselen where id = $pID";
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
             $mailSubject = "Schelle Sport - Uw Inschrijving voor Mosselweek - $formule";

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

             $mailBody .= "Beste $naam,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Uw deelname aan de mosselweek werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Formule: $formule";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Datum: $datum";
               $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal personen: " . $eventRec->aantalEters;
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalMosselenVw) {
                 $mailBody .= "Mosselen: " . $eventRec->aantalMosselenVw . " (aan 22 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashVw) {
                 $mailBody .= "Goulash: " . $eventRec->aantalGoulashVw . " (aan 14 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalMosselenKids) {
                 $mailBody .= "Mosselen (kids): " . $eventRec->aantalMosselenKids . " (aan 12 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashKids) {
                 $mailBody .= "Goulash (kids): " . $eventRec->aantalGoulashKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCurryworst) {
                 $mailBody .= "Curryworst (kids): " . $eventRec->aantalCurryworst . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFishsticks) {
                 $mailBody .= "Curryworst (kids): " . $eventRec->aantalFishsticks . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFrieten) {
                 $mailBody .= "Porties friet: " . $eventRec->aantalFrieten . " (Inbegrepen)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalBrood) {
                 $mailBody .= "Porties brood: " . $eventRec->aantalBrood . " (Inbegrepen)";
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
             $mailBCC = "karel.foque@skynet.be";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor Mosselweek";

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

             $mailBody .= "Er was een inschrijving voor de Mosselweek:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: $formule";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: " . $naam;
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Datum: $datum";
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
                     SSP_events::RegKortingCode($kortingCode, '*MOSSELEN');
                 }

             }

             SSP_events::UpdAantalKorting('*MOSSELEN', $pID);

             // -------------
             // Einde functie
             // -------------


         }

         // ========================================================================================
         // Function: Mosselen "Leveren" - Versturen bevestigingsmails
         //
         // In:	ID Inschrijving
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function Mosselen_leveren_SndBevestigingsMails($pID){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));
             include_once(SX::GetClassPath("events.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $sqlStat = "Select * from event_mosselen_leveren where id = $pID";
             $db->Query($sqlStat);

             $eventRec = $db->Row();

             if (! $eventRec)
                 return;

             $naam = $eventRec->naam;
             $shift = $eventRec->shift;

             $sqlStat = "Select * from event_mosselen_shifts_leveren where id = $shift";
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


             $mailSubject = "Schelle Sport - Uw Inschrijving voor Mosselweek - Leveren aan huis";

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

             $mailBody .= "Beste $naam,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Uw deelname aan de mosselweek werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Formule: Leveren aan huis";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal personen: " . $eventRec->aantalEters;
             $mailBody .= "<br/><br/>". "\r\n";

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($eventRec->aantalMosselenVw) {
                 $mailBody .= "Mosselen: " . $eventRec->aantalMosselenVw . " (aan 22 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashVw) {
                 $mailBody .= "Goulash: " . $eventRec->aantalGoulashVw . " (aan 14 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalMosselenKids) {
                 $mailBody .= "Mosselen (kids): " . $eventRec->aantalMosselenKids . " (aan 12 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalGoulashKids) {
                 $mailBody .= "Goulash (kids): " . $eventRec->aantalGoulashKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalCurryworst) {
                 $mailBody .= "Curryworst (kids): " . $eventRec->aantalCurryworst . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalFrieten) {
                 $mailBody .= "Porties friet: " . $eventRec->aantalFrieten . " (Inbegrepen)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($eventRec->aantalBrood) {
                 $mailBody .= "Porties brood: " . $eventRec->aantalBrood . " (Inbegrepen)";
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
             $mailBCC = "karel.foque@skynet.be";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor Mosselweek";

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

             $mailBody .= "Er was een inschrijving voor de Mosselweek:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: Leveren aan huis";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: " . $naam;
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Datum: $datum";
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
             $mailBCC = "";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, "horeca@schellesport.be", $mailBCC, $fromMail, $fromName,'','UTF-8');

             // ---------------------------
             // Registratie kortingcode(s)
             // ------------------------------

             $scanEvent = self::GetMosselenScanEvent();

             if ($aantalKortingbonnen){

                foreach ($kortingCodes as $kortingCode){
                    SSP_events::RegKortingCode($kortingCode, $scanEvent);
                }

             }


             SSP_events::UpdAantalKorting('*MOSSELEN3', $pID);

             // -------------
             // Einde functie
             // -------------

         }

         // ========================================================================================
         // Function: Berekenen aantal tafels
         //
         // In:	Aantal personen
         //
         // Uit: Aantal tafels
         //
         // ========================================================================================

         static function CalcAantalTafels($pAantalPersonen){

             return ceil($pAantalPersonen / 5);

         }

         // ========================================================================================
         // Function: Update aantal tafels voor "eten op de club"
         //
         // In:	Inschrijving

         // ========================================================================================

         static function UpdAantalTafels($pID){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from event_mosselen_op_de_club where id = $pID";
             $db->Query($sqlStat);

             if ($eventRec = $db->Row()){

                 $aantalTafels = self::CalcAantalTafels($eventRec->aantalEters);

                 if ($aantalTafels){

                     $sqlStat = "update event_mosselen_op_de_club set aantalTafels = $aantalTafels where id = $pID";
                     $db->Query($sqlStat);

                 }

             }


             self::UpdAantalTafelsPerShift();

             // -------------
             // Einde functie
             // -------------

             return;

         }

         // ========================================================================================
         // Function: Update aantal tafels voor "eten op de club" per shift
         //
         // In:	Inschrijving

         // ========================================================================================

         static function UpdAantalTafelsPerShift(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             $sqlStat = "Select * from event_mosselen_shifts_op_de_club";
             $db->Query($sqlStat);

             while ($shiftRec = $db->Row()) {

                 $shift = $shiftRec->id;
                 $aantalTafels = 0;

                 $sqlStat = "Select * from event_mosselen_op_de_club where shift = $shift";
                 $db2->Query($sqlStat);

                 while ($eventRec = $db2->Row())
                     $aantalTafels += $eventRec->aantalTafels;

                 $values = array();
                 $where = array();

                 $values["aantalTafelsGereserveerd"] =  MySQL::SQLValue($aantalTafels, MySQL::SQLVALUE_NUMBER);
                 $where["id"] =  MySQL::SQLValue($shift, MySQL::SQLVALUE_NUMBER);

                 $db2->UpdateRows("event_mosselen_shifts_op_de_club", $values, $where);

             }

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