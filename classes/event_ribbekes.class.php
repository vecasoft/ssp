<?php 

     class SSP_event_ribbekes { // define the class

         // ========================================================================================
         // Function: Ophalen event-naam scan-even (*RIBBEKES-202x)
         //
         // In:	Geen
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function GetScanEvent(){

            $year = date("Y");
            $scanEvent = "*RIBBEKES-$year";

            return $scanEvent;

         }

         // ========================================================================================
         // Function: Registratie inschrijving
         //
         // In:	ID Inschrijving
         //
         // Uit: Mail verstuurd? true/false
         //
         // ========================================================================================

         static function RegInschrijving($pInschrijving){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));
             include_once(SX::GetClassPath("efin.class"));

             $sqlStat = "Select * from event_ribbekes where id= $pInschrijving";
             $db->Query($sqlStat);

             if (! $irRec = $db->Row())
                 return;

             // ------------------
             // Aanvullen gegevens
             // ------------------

             $curDateTime = date('Y-m-d H:i:s');

             $GM = SSP_efin::GetNextGM('*EVENT_RIBBEKES');
             $GMn = SSP_efin::CvtGmToNum($GM);

             $values = array();
             $where = array();

             $values["GMn"] =  MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);
             $values["GM"] =  MySQL::SQLValue($GM);

             $values["datumInschrijving"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

             $where["id"] =  MySQL::SQLValue($pInschrijving, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("event_ribbekes", $values, $where);

             // ---------------
             // Versturen mails
             // ---------------

             self::SndBevestigingsMails($pInschrijving);

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

         static function SndBevestigingsMails($pInschrijving){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             // ------------
             // Inschrijving
             // ------------

             $sqlStat = "Select * from event_ribbekes where id= $pInschrijving";
             $db->Query($sqlStat);

             if (! $irRec = $db->Row())
                 return;

             $naam = $irRec->naam;

             // -------------
             // Shift (datum)
             // ------------

             $shift = $irRec->shift;

             $sqlStat = "Select * from event_ribbekes_shifts where id = $shift";
             $db->Query($sqlStat);

             $esRec = $db->Row();

             if (! $esRec)
                 return;

             $datum = $esRec->naam;

             // -------
             // Formule
             // -------

             $taRec = SSP_db::Get_SX_taRec('EVENT_EET_FORMULE', $irRec->eventFormule);

             if (! $taRec)
                 return;

             $formule = $taRec->taName;

             // ------------
             // Kortingcodes
             // ------------

             $kortingCodes = array();

             if ($irRec->korting1)
                 $kortingCodes[] = $irRec->korting1;
             if ($irRec->korting2)
                 $kortingCodes[] = $irRec->korting2;
             if ($irRec->korting3)
                 $kortingCodes[] = $irRec->korting3;
             if ($irRec->korting4)
                 $kortingCodes[] = $irRec->korting4;
             if ($irRec->korting5)
                 $kortingCodes[] = $irRec->korting5;

             $aantalKortingbonnen = count($kortingCodes);

             // ---------------------
             // Mail naar inschrijver
             // ---------------------

             $mailSubject = "Schelle Sport - Uw Inschrijving Ribbekes-weekend";

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

             $mailBody .= "Beste $irRec->naam,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Uw deelname aan het ribbekes-weekend werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
;
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Tijdstip: $irRec->tijdstip";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "Formule: $formule";
             $mailBody .= "<br/>". "\r\n";
             $mailBody .= "<br/>". "\r\n";

             $mailBody .= "Je deed volgende bestelling:";
             $mailBody .= "<br/><br/>". "\r\n";

             if ($irRec->aantalRibbekes) {
                 $mailBody .= "Ribbekes: " . $irRec->aantalRibbekes . " (aan 18 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalStoofpotje) {
                 $mailBody .= "Stoofpotje: " . $irRec->aantalStoofpotje . " (aan 14 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalStoofpotjeKids) {
                 $mailBody .= "Stoofpotje (kids): " . $irRec->aantalStoofpotjeKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalCurryworstenKids) {
                 $mailBody .= "Curryworst (kids): " . $irRec->aantalCurryworstenKids . " (aan 8 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->keuzeFrietjes) {
                 $mailBody .= "Keuze frietjes: " . $irRec->keuzeFrietjes;
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalRijstpap) {
                 $mailBody .= "Potjes rijstpap: " . $irRec->aantalRijstpap . " (aan 3 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalChocoMousse) {
                 $mailBody .= "Potjes chocomousse: " . $irRec->aantalChocoMousse . " (aan 3 EUR)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalWijnWit) {
                 $mailBody .= "Wijn wit: " . $irRec->aantalWijnWit . " (aan 10 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalWijnRood) {
                 $mailBody .= "Wijn rood: " . $irRec->aantalWijnRood . " (aan 10 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalWijnRose) {
                 $mailBody .= "Wijn rosé: " . $irRec->aantalWijnRose . " (aan 10 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($irRec->aantalCava) {
                 $mailBody .= "Cava: " . $irRec->aantalCava . " (aan 12 EUR / fles)";
                 $mailBody .= "<br/>" . "\r\n";
             }

             if ($aantalKortingbonnen) {
                 $mailBody .= "<br/>" . "\r\n";
                 $mailBody .= "Er werd $aantalKortingbonnen X 5 EUR korting toegekend";
                 $mailBody .= "<br/>" . "\r\n";
             }

             $GM = $irRec->GM;
             $teBetalen = $irRec->teBetalen + 0;
             $rekening = "BE56 0015 0154 9488";

             $mailBody .= "<br><b>Gelieve het totaal bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met gestructureerde mededeling: $GM</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Alvast bedankt voor je deelname!";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Bestuur";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $irRec->mail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // -----------------------
             // Mail naar Schelle Sport
             // -----------------------

             $mailSubject = "Schelle Sport - Inschrijving voor Ribbekes-weekend";

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

             $mailBody .= "Er was een inschrijving voor het Ribbekes-weekend:" . "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Datum: $datum";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Tijdstip: $irRec->tijdstip";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Formule: $formule";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: $naam";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Aantal hoofdschotels: $irRec->aantalHoofdschotels";

             $mailBody .= "<br/><br/>". "\r\n";
             if ($irRec->opmerkingen)
                 $mailBody .= "<b>Opmerkingen:</b><br/>" . nl2br($irRec->opmerkingen) . "\r\n";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,". "\r\n";
             $mailBody .= "<br/><br/>Schelle Sport Secretariaat". "\r\n";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $irRec->mail;
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


                 // Registratie aantal kortingbonnen...

                 $values = array();
                 $where = array();

                 $values["aantalKorting"] =  MySQL::SQLValue($aantalKortingbonnen, MySQL::SQLVALUE_NUMBER);

                 $where["id"] =  MySQL::SQLValue($pInschrijving, MySQL::SQLVALUE_NUMBER);

                 $db->UpdateRows("event_ribbekes", $values, $where);


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