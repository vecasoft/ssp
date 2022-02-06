<?php 

     class SSP_events { // define the class

         // ========================================================================================
         // Function: Get event scan event
         //
         // In:	Event-code (bv *MOSSELEN)
         //
         // Uit: GScan event ID
         //
         // ========================================================================================

         static function GetScanEvent($pEventCode){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_scanning_events where seEventCode = '$pEventCode' order by seDatumVan desc";
             $db->Query($sqlStat);

             if ($seRec = $db->Row())
                 return $seRec->seId;
             else
                 return 0;

         }

         // ========================================================================================
         // Function: Check korting code
         //
         // In:	Code (login ID)
         //     Event (bv. "*MOSSELEN")
         //
         // Uit: Geldig? *OK of foutboodschap
         //
         // ========================================================================================

         static function CheckKortingCode($pCode, $pEvent) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("scanning.class"));
             include_once(SX::GetClassPath("_db.class"));

             $return = "Korting-code '$pCode' is onbekend";

             // ---------
             // Get event
             // ---------

             $event = self::GetScanEvent($pEvent);

             if (! $event)
                 return "Onbekende fout (event niet gevonden)";

             // -----------------------------------------
             // USER -> In bezit van lidkaart met korting
             // -----------------------------------------

             $code = strtolower($pCode);

             $adRec = SSP_db::Get_SSP_adRec($code);

             if ($adRec) {

                $sqlStat = "Select * from ela_ka_kaarten where kaPersoon = '$code' and kaEetEvents = 1  and kaRecStatus = 'A' order by kaDatumCreatie desc limit 1";

                $db->Query($sqlStat);
                $kaRec = $db->Row();

                if ($kaRec){

                    $kaart = $kaRec->kaKaartCode;
                    $valid = SSP_scanning::HdlScanKaart($kaart, $event, $boodschap, $reedsGebruikt);

                    if ($valid)
                        $return = '*OK';
                    else
                        $return = "Kortingcode '$pCode' ongeldig: $boodschap";
                }

             }

             // -------------
             // Einde functie
             // -------------

             return $return;

         }

         // ========================================================================================
         // Function: Registratie  korting code in scanning event
         //
         // In:	Code (login ID)
         //     EventCode (bv. "*MOSSELEN")
         //
         // ========================================================================================

         static function RegKortingCode($pCode, $pEventCode){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("scanning.class"));
             include_once(SX::GetClassPath("_db.class"));

             // ---------
             // Get EVENT
             // ---------

             $event = self::GetScanEvent($pEventCode);

             // ---------
             // Get KAART
             // ---------

             $code = strtolower($pCode);

             $adRec = SSP_db::Get_SSP_adRec($code);

             if ($adRec) {

                 $sqlStat = "Select * from ela_ka_kaarten where kaPersoon = '$code' and kaEetEvents = 1  and kaRecStatus = 'A' order by kaDatumCreatie desc limit 1";


                 $db->Query($sqlStat);
                 $kaRec = $db->Row();

                 if ($kaRec) {
                     $kaart = $kaRec->kaKaartCode;
                 }

             }

             // --------------------
             // Registratie in event
             // --------------------

             $curDateTime = date('Y-m-d H:i:s');
             $curDate =  date('Y-m-d');

             $values = array();

             $values["scEvent"] = MySQL::SQLValue($event, MySQL::SQLVALUE_NUMBER);
             $values["scKaartCode"] = MySQL::SQLValue($kaart);
             $values["scScanTijdstip"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
             $values["scScanDatum"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
             $values["scScanError"] = MySQL::SQLValue('*OK', MySQL::SQLVALUE_TEXT);

             $id = $db->InsertRow("ssp_scanning_detail", $values);

         }


         // ========================================================================================
         // Function: Reistratie Betaling
         //
         // In:	Betaling
         //     Database actiecode (*ADD, *UPD, *DEL)
         //
         // ========================================================================================

         static function RegBetaling($pBetaling, $pDbAction = '*UPD'){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("scanning.class"));
             include_once(SX::GetClassPath("_db.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $sqlStat = "Select * from events_eb_event_betalingen where ebID = $pBetaling";
             $db->Query($sqlStat);

             if (! $ebRec = $db->Row())
                 return;

             $eventCode = $ebRec->ebEventCode;
             $event = $ebRec->ebEvent;
             $editie = $ebRec->ebEditie;

             if (! $event)
                 $event = $eventCode;

             $eventInschrijving = $ebRec->ebEventInschrijving;

             $sqlStat = "Select * from events_eh_event_headers where ehCode ='$event'";
             $db->Query($sqlStat);

             if (! $ehRec = $db->Row())
                 return;

             $file = $ehRec->ehFile;

             // ------------------------------------------------------
             // Som alle betalingen met voor zelfde event-inschrijving
             // ------------------------------------------------------

             $betaald = 0;

             $sqlStat = "Select * from events_eb_event_betalingen where (ebEventCode = '$event' or ebEvent = '$event') and (ebEditie is null or ebEditie = '$editie') and ebEventInschrijving = $eventInschrijving";
             $db->Query($sqlStat);

             while ($ebRec = $db->Row()) {

                 if (($pDbAction == '*DEL') and ($ebRec->ebID == $pBetaling))
                    continue;

                 $betaald += $ebRec->ebBedrag;

             }

             // --------------------------
             // Registratie betaald bedrag
             // --------------------------

             $sqlStat = "Update $file set betaald = $betaald where id = $eventInschrijving";

             error_log($sqlStat);

             $db->Query($sqlStat);

             // -------------
             // Einde functie
             // -------------
             

         }

         // ========================================================================================
         // Function: Ophalen korting bepaalde persoon
         //
         // In:	Persoon
         //     Type (*NORMAAL, *LIDGELDKEUZE)
         //
         // ========================================================================================

         static function GetKaartKorting($pPersoon, $pType = '*NORMAAL'){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             if ($pType == '*NORMAAL')
                 return 5;

             if ($pType == '*LIDGELDKEUZE') {

                 $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pPersoon' and lkKeuze = '*EETEVENTS'";
                 $db->Query($sqlStat);

                 if (! $lkRec = $db->Row())
                    return  0;

                 $korting = $lkRec->lkKortingOpLidgeld;

                 if (! $korting)
                     return 0;

                 $reedsToegekend = 0;

                 $sqlStat = "Select * from events_kk_kaart_korting where kkPersoon = '$pPersoon'";
                 $db->Query($sqlStat);

                 while ($kkRec = $db->Row())
                     $reedsToegekend += $kkRec->kkKortingExtra;

                 if ($reedsToegekend >= $korting)
                     return 0;
                 else
                     return $korting - $reedsToegekend;

             }

            // -------------
            // Einde functie
            // -------------

             return 0;

         }

         // ========================================================================================
         // Function: Ophalen (& registratie) korting volgens ingegeven korting-codes
         //
         // In:	Event (bv *MOSSELEN)
         //     Seizoen
         //     Te betalen bruto bedrag
         //     Codes = Array met korting codes
         //     Actie = *GET, *REG
         //
         // ========================================================================================

         static function GetRegKorting($pEvent, $pSeizoen, $pTeBetalen, $pCodes, $pActie = '*GET'){

             $teBetalen = $pTeBetalen;

             // --------------------------------------------
             // Eerst "normale" kortingen (5 EUR) verrekenen
             // --------------------------------------------

             $kortingTotaal = 0;

             foreach ($pCodes as $code) {

                 $korting = self::GetKaartKorting($code, '*NORMAAL');
                 $kortingTotaal += $korting;

                 if ($pActie == '*REG')
                     self::RegToegekendeKorting($pEvent, $pSeizoen, $code, $korting, '*NORMAAL');

             }

             if ($kortingTotaal >= $pTeBetalen)
                 return $pTeBetalen;

             $teBetalen -= $kortingTotaal;

             // ------------------------------------------------
             // Dan "extra" kortingen (Lidgeld keuze) verrekenen
             // ------------------------------------------------

             foreach ($pCodes as $code) {

                 $korting = self::GetKaartKorting($code, '*LIDGELDKEUZE');

                 if (($korting + $kortingTotaal) > $pTeBetalen) {

                     $korting = $pTeBetalen - $kortingTotaal;
                     $kortingTotaal += $korting;

                     if ($pActie == '*REG')
                         self::RegToegekendeKorting($pEvent, $pSeizoen, $code, $korting, '*LIDGELDKEUZE');

                     return $kortingTotaal;

                 } else {

                   $kortingTotaal += $korting;

                   if ($pActie == '*REG')
                       self::RegToegekendeKorting($pEvent, $pSeizoen, $code, $korting, '*LIDGELDKEUZE');

                 }

             }

             // -------------
             // Einde functie
             // -------------

             return $kortingTotaal;

         }

         // ========================================================================================
         // Function: Registratie toegekende korting
         //
         // In:	Event (bv *MOSSELEN)
         //     Seizoen
         //     Persoon
         //     Korting
         //     Kortingtype (*NOMAAL, *LIDGELDKEUZE)
         //
         // ========================================================================================

         static function RegToegekendeKorting($pEvent, $pSeizoen, $pPersoon, $pKorting, $pType = '*NORMAAL'){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $dbActie = '*ADD';

             $sqlStat = "Select * from events_kk_kaart_korting where kkPersoon = '$pPersoon' and kkEvent = '$pEvent' and kkSeizoen = '$pSeizoen'";
             $db->Query($sqlStat);

             if ($kkRec = $db->Row()) {
                 $dbActie = '*UPD';
                 $kkId = $kkRec->kkId;
             }

             $curDateTime = date('Y-m-d H:i:s');

             if ($dbActie == '*ADD') {

                 $values = array();

                 $values["kkPersoon"] = MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);
                 $values["kkEvent"] = MySQL::SQLValue($pEvent, MySQL::SQLVALUE_TEXT);
                 $values["kkSeizoen"] = MySQL::SQLValue($pSeizoen, MySQL::SQLVALUE_TEXT);

                 if ($pType == '*NORMAAL')
                     $values["kkKortingNormaal"] = MySQL::SQLValue($pKorting, MySQL::SQLVALUE_NUMBER);
                 if ($pType == '*LIDGELDKEUZE') {
                     $values["kkKortingExtra"] = MySQL::SQLValue($pKorting, MySQL::SQLVALUE_NUMBER);
                     $values["kkKortingExtraReden"] = MySQL::SQLValue('*LIDGELDKEUZE', MySQL::SQLVALUE_TEXT);
                 }

                 $values["kkDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["kkDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                 $values["kkUserCreatie"] = MySQL::SQLValue('*FORMULIER', MySQL::SQLVALUE_TEXT);
                 $values["kkUserUpdate"] = MySQL::SQLValue('*FORMULIER', MySQL::SQLVALUE_TEXT);

                 $id = $db->InsertRow("events_kk_kaart_korting", $values);


             }

             if ($dbActie == '*UPD') {

                 $values = array();
                 $where = array();

                 if ($pType == '*NORMAAL') {

                     $korting = $kkRec->kkKortingNormaal + $pKorting;
                     $values["kkKortingNormaal"] = MySQL::SQLValue($korting, MySQL::SQLVALUE_NUMBER);

                 }
                 if ($pType == '*LIDGELDKEUZE') {

                     $korting = $kkRec->kkKortingExtra + $pKorting;

                     $values["kkKortingExtra"] = MySQL::SQLValue($korting, MySQL::SQLVALUE_NUMBER);
                     $values["kkKortingExtraReden"] = MySQL::SQLValue('*LIDGELDKEUZE', MySQL::SQLVALUE_TEXT);

                 }

                 $values["kkDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["kkDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                 $where["kkId"] =  MySQL::SQLValue($kkRec->kkId, MySQL::SQLVALUE_NUMBER);

                 $db->UpdateRows("events_kk_kaart_korting", $values, $where);

             }


         }

         // ========================================================================================
         // Function: Update aantal korting
         //
         // In:	Event
         //     Inschrijving ID
         //
         // ========================================================================================

         static function UpdAantalKorting($pEvent, $pID){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             $sqlStat = "Select * from events_eh_event_headers where ehCode = '$pEvent'";
             $db->Query($sqlStat);

             if (! $ehRec = $db->Row())
                 return;

             $file = $ehRec->ehFile;

             $sqlStat = "Select * from $file where id = $pID";
             $db->Query($sqlStat);

             if ($eventRec = $db->Row()){

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

                 $aantalKorting = count($kortingCodes);

                 $sqlStat = "Update $file set aantalKorting = $aantalKorting where id = $pID";
                 $db2->Query($sqlStat);

             }

             // -------------
             // Einde functie
             // -------------

         }

         // ========================================================================================
         // Function: Aanmaken Tijdstippen
         //
         // In:	Event-shift
         //     User
         //
         // ========================================================================================

         static function CrtShiftTijdstippen($pShift, $pUser){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $aantal = 0;

             $tijdstippen = array();

             // $tijdstippen[] = '17:00';
             $tijdstippen[] = '17:30';
             $tijdstippen[] = '18:00';
             $tijdstippen[] = '18:30';
             $tijdstippen[] = '19:00';
             $tijdstippen[] = '19:30';
             $tijdstippen[] = '20:00';

             foreach ($tijdstippen as $tijdstip) {

                 $sqlStat = "Select * from event_et_eet_tijdstippen where etShift = $pShift and etTijdstip = '$tijdstip'";
                 $db->Query($sqlStat);

                 if (! $etRec = $db->Row()) {

                    $curDateTime = date('Y-m-d H:i:s');

                    $values = array();

                    $values["etShift"] = MySQL::SQLValue($pShift, MySQL::SQLVALUE_NUMBER);
                    $values["etTijdstip"] = MySQL::SQLValue($tijdstip, MySQL::SQLVALUE_TEXT);

                    $values["etUserCreatie"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
                    $values["etUserUpdate"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);

                    $values["etDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                    $values["etDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                    $id = $db->InsertRow("event_et_eet_tijdstippen", $values);

                    if ($id)
                        $aantal += 1;

                 }
             }

             // -------------
             // Einde functie
             // -------------

             return ($aantal > 0);

         }


         // ========================================================================================
         // Function: Ophalen editie
         //
         // In:	Event
         //
         // Return: Editie
         //
         // ========================================================================================

         static function GetEventEditie($pEvent){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from events_ee_event_edities where eeEvent = '$pEvent' and eeRecStatus = 'A' order by eeDatumVan desc limit 1";
             $db->Query($sqlStat);

             $editie = '*NONE';

             if ($eeRec = $db->Row())
                 $editie = $eeRec->eeEditie;

             // -------------
             // Einde functie
             // -------------

             return $editie;


         }



         // -----------
         // EINDE CLASS
         // ----------


 	}      
?>