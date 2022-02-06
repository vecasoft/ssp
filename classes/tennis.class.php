<?php 

     class SSP_tennis { // define the class

         // ========================================================================================
         // Function: Ophalen huidig seizoen
         //
         // Uit: Seizoen (bv "2021")
         //
         // ========================================================================================
         static function GetSeizoen() {

            // -------------------------------------------
            // Is gewoon het huidige jaar (voorlopig toch)
            // ------------------------------ ------------

            $seizoen = 2022;

            // -------------
            // Einde functie
            // -------------

            return $seizoen;

         }

         // ========================================================================================
         // Function: Ophalen te betalen
         //
         // Uit: Persoon
         //      Tariefcode (optioneel,  default uit adRec)
         //
         // ========================================================================================
         static function GetTebetalen($pPersoon, $ptariefCode = null) {

             include_once(SX::GetClassPath("_db.class"));

             $adRec = SSP_db::Get_SSP_adRec($pPersoon);

             if ( ! $adRec)
                 return null;

             $tariefCode = $adRec->adTennisTariefCode;




             // -------------
             // Einde functie
             // -------------

             return $teBetalen;

         }


         // ========================================================================================
         // Function: Check code voetbal
         //
         // In:	Code (login ID)
         //
         // Uit: Geldig? *OK of foutboodschap
         //
         // ========================================================================================
         static function CheckVoetbalCode($pCode) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("scanning.class"));
             include_once(SX::GetClassPath("_db.class"));

             $return = "Login-code '$pCode' is onbekend";

             // -------------------------------------
             // USER -> In bezit van lidkaart VOETBAL
             // -------------------------------------

             $code = strtolower($pCode);

             $adRec = SSP_db::Get_SSP_adRec($code);

             if ($adRec) {

                $sqlStat = "Select * from ela_ka_kaarten where kaPersoon = '$code' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A' order by kaDatumCreatie desc limit 1";

                $db->Query($sqlStat);
                $kaRec = $db->Row();

                if ($kaRec)
                    $return = '*OK';
                else
                    $return = "Voor code '$pCode' bestaat geen actieve voetbal-lidkaart.";

             }

             // --------------------------
             // Slechts één maal gebruiken
             // --------------------------

             if ($return == '*OK'){

                 $seizoen = self::GetSeizoen();


                 $sqlStat = "Select count(*) as aantal from tennis_aansluitingen where taSeizoen = '$seizoen' and taCodeVoetbal = '$pCode'";
                 $db->Query($sqlStat);

                 if ($taRec = $db->Row())
                     if ($taRec->aantal > 0)
                         $return = "Code voetbal '$pCode' reeds gebruikt voor seizoen $seizoen";

             }


             // -------------
             // Einde functie
             // -------------

             return $return;

         }


         // ========================================================================================
         // Function: Registratie aansluiting aanvraag
         //
         // In:	Aanvraag ID
         //
         // ========================================================================================
         static function RegAansluitingAanvraag($pAanvraag) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             include_once(SX::GetClassPath("efin.class"));

             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaId = $pAanvraag and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             if (! $aaRec = $db->Row())
                return; // Kan in principe niet

             // --------------
             // Aanvullen data
             // --------------

             $GM = SSP_efin::GetNextGM('*LIDGELD_TENNIS');
             $GMn = SSP_efin::CvtGmToNum($GM);

             $values = array();
             $where = array();

             $values["aaGMn"] =  MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);
             $values["aaGM"] =  MySQL::SQLValue($GM);

             $where["aaId"] =  MySQL::SQLValue($pAanvraag, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

             // -------------------------------------
             // Versturen aanvraag bevestigings-mails
             // -------------------------------------
             
             self::SndAanvraagBevestigingsMails($pAanvraag);

             // ---------------------------
             // Link met ledenbestand (auto)
             // ----------------------------

             self::LinkMetLedenbestand($pAanvraag);

             // -------------
             // Einde functie
             // -------------


         }


         // ========================================================================================
         // Function: Bereken & Registratie bedrag betaald
         //
         // In:	Aanvraag ID
         //
         // Uit: Totaal betaald
         //
         // ========================================================================================
         static function CalcRegBetaald($pAanvraag) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $betaald = 0;

             $sqlStat = "Select sum(lbBedrag) as betaald from tennis_lb_lidgeld_betalingen where lbAanvraag = $pAanvraag";
             $db->Query($sqlStat);

             if ($lbRec = $db->Row())
                 $betaald = $lbRec->betaald;

             // -----------
             // Registratie
             // -----------

             $values = array();
             $where = array();

             $values["aaBetaald"] =  MySQL::SQLValue($betaald, MySQL::SQLVALUE_NUMBER);

             $where["aaId"] =  MySQL::SQLValue($pAanvraag, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

             // -------------
             // Einde functie
             // -------------

             return $betaald;


         }

         // ========================================================================================
         // Function: Auto link met ledenbestand
         //
         // In:	ID Aanvraag
         //
         // Return: Code ledenbestand
         //
         // ========================================================================================

         static function LinkMetLedenbestand($pAanvraag){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaId = $pAanvraag and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             if (! $aaRec = $db->Row())
                 return null;

             if ($aaRec->aaCode)
                 return $aaRec->aaCode; // reeds ingevuld

             // ------------------------------------------------
             // Opzoeken op basis naam, voornaam + geboortedatum
             // ------------------------------------------------

             $naam = $aaRec->aaNaam;
             $voornaam = $aaRec->aaVoornaam;
             $geboortedatum = $aaRec->aaGeboortedatum;

             $sqlStat = "Select * from ssp_ad where trim(upper(adNaam)) = trim(upper('$naam')) and trim(upper(adVoornaam)) = trim(upper('$voornaam')) and adGeboorteDatum = '$geboortedatum'";
             $db->Query($sqlStat);

             if ($adRec = $db->Row()){

                 $values = array();
                 $where = array();

                 $values["aaCode"] =  MySQL::SQLValue($adRec->adCode);

                 $where["aaId"] =  MySQL::SQLValue($pAanvraag);

                 $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

             }

             // -------------
             // Einde functie
             // -------------

             return $adRec->adCode;

         }

         // ========================================================================================
         // Function: Auto link met ledenbestand (alle)
         //
         // ========================================================================================

         static function LinkMetLedenbestandAlle(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaCode  is Null and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             while ($aaRec = $db->Row())
                 self::LinkMetLedenbestand($aaRec->aaId);


         }


         // ========================================================================================
         // Function: Synchrnoseren met ledenbestand
         //
         // In:	ID Aanvraag
         //
         // ========================================================================================

         static function SyncMetLedenbestand($pAanvraag){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaId = $pAanvraag and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             if (! $aaRec = $db->Row())
                 return;

             if (! $aaRec->aaCode)
                 return;

             $persoon = $aaRec->aaCode;

             $adRec = SSP_db::Get_SSP_adRec($persoon);

             if (! $adRec)
                 return;

             // ----------------------------------
             // Copy VTV nummer -> aanvraag-record
             // ----------------------------------

             if (! $aaRec->aaVTVnummer){
                 if ($adRec->adTennisLidnummer){


                     $values = array();
                     $where = array();

                     $values["aaVTVnummer"] =  MySQL::SQLValue($adRec->adTennisLidnummer);

                     $where["aaId"] =  MySQL::SQLValue($pAanvraag, MySQL::SQLVALUE_NUMBER);

                     $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

                 }
             }

             // --------------------------------------------------------------
             // Copy Contact-gegevens -> ledenbestand (niet alles voetballeden)
             // --------------------------------------------------------------

             $functieT = $adRec->adFunctieT;

             if (! $functieT)
                 $functieT = "speler";

             $relatieMet =  $adRec->adRelatieMet;
             if (! $relatieMet)
                 $relatieMet = 'T';

             $pos = strpos($relatieMet, 'T');
             if ($pos === false)
                 $relatieMet = $relatieMet . ',T';

             $values = array();
             $where = array();

             $curDateTime = date('Y-m-d H:i:s');

             if (! $aaRec->aaLidVoetbal) {

                 $values["adAdres1"] = MySQL::SQLValue($aaRec->aaStraat);
                 $values["adPostnr"] = MySQL::SQLValue($aaRec->aaPostcode);
                 $values["adGemeente"] = MySQL::SQLValue($aaRec->aaGemeente);
                 $values["adMail"] = MySQL::SQLValue($aaRec->aaMail);
                 $values["adTel"] = MySQL::SQLValue($aaRec->aaTel);
                 $values["adGeslacht"] = MySQL::SQLValue($aaRec->aaGeslacht);
             }

             $values["adRelatieMet"] = MySQL::SQLValue($relatieMet);

             if ($aaRec->aaVTVnummer)
                 $values["adTennisLidnummer"] =  MySQL::SQLValue($aaRec->aaVTVnummer);

             $values["adFunctieT"] =  MySQL::SQLValue($functieT);

             $values["adAansluitingVTV"] =  MySQL::SQLValue($aaRec->aaAansluitingVTV, MySQL::SQLVALUE_NUMBER);

             $values["adRecStatus"] =  MySQL::SQLValue('A');

             $values["adDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
             $values["adUserUpdate"] =  MySQL::SQLValue('*TENNIS');

             $where["adCode"] =  MySQL::SQLValue($persoon);

             $db->UpdateRows("ssp_ad", $values, $where);

             // -------------
             // Set SYNC-flag
             // -------------

             $values = array();
             $where = array();

             $values["aaSync"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);

             $where["aaId"] =  MySQL::SQLValue($pAanvraag, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

         }

         // ========================================================================================
         // Function: Registratie betaling(en) in ledenbestand
         //
         // In:	ID Aanvraag
         //
         // ========================================================================================

         static function RegBetalingInLedenbestand($pAanvraag){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             include_once(SX::GetClassPath("ela.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaId = $pAanvraag and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             if (! $aaRec = $db->Row())
                 return;

             if (! $aaRec->aaCode)
                 return;

             $persoon = $aaRec->aaCode;
             $seizoen = self::GetSeizoen();

             // -----------------------------
             // Enkel indien volledig betaald
             // -----------------------------

             if (! $aaRec->aaBetaald)
                 return;

             if ($aaRec->aaBetaald < $aaRec->aaTebetalen)
                 return;

             // ------------------------------------
             // Wis tennis-betalingen huidig seizoen
             // ------------------------------------

             $sqlStat = "Delete from ela_lb_lidgeld_betalingen where lbLidgeldVoor = '*TENNIS' and lbPersoon = '$persoon' and lbSeizoen = '$seizoen'";
             $db->Query($sqlStat);

             // ------------------
             // Ophalen betalingen
             // ------------------

             $tennisLidgeldBedrag1 = 0;
             $tennisLidgeldDatum1 = null;
             $tennisLidgeldBetaalwijze1 = null;

             $tennisLidgeldBedrag2 = 0;
             $tennisLidgeldDatum2 = null;
             $tennisLidgeldBetaalwijze2 = null;

             $i = 0;

             $sqlStat = "Select * from tennis_lb_lidgeld_betalingen where lbAanvraag = $pAanvraag";
             $db->Query($sqlStat);

             while ($lbRec = $db->Row()){

                $i++;

                if ($i ==1){

                    $tennisLidgeldBedrag1 = $lbRec->lbBedrag;
                    $tennisLidgeldDatum1 = $lbRec->lbBetaalDatum;
                    $tennisLidgeldBetaalwijze1 = $lbRec->lbBetaalWijze;

                }

                if ($i > 1){

                    $tennisLidgeldBedrag2 += $lbRec->lbBedrag;
                    $tennisLidgeldDatum2 = $lbRec->lbBetaalDatum;
                    $tennisLidgeldBetaalwijze2 = $lbRec->lbBetaalWijze;

                }

                SSP_ela::RegBetalingLidgeld($persoon, '*TENNIS', $seizoen, $lbRec->lbBedrag, $lbRec->lbBetaalDatum, $lbRec->lbBetaalWijze, '*TENNISADMIN', $lbRec->lbEfinRD);

             }

             //

             $tennisLidgeldTotaal = $tennisLidgeldBedrag1 + $tennisLidgeldBedrag2;

             if ($tennisLidgeldBetaalwijze1 == '*CASH')
                 $tennisLidgeldBetaalwijze1 = 'CASH';
             if ($tennisLidgeldBetaalwijze1 == '*OVERSCHRIJVING')
                 $tennisLidgeldBetaalwijze1 = 'OVERSCHR';
             
             if ($tennisLidgeldBetaalwijze2 == '*CASH')
                 $tennisLidgeldBetaalwijze2 = 'CASH';
             if ($tennisLidgeldBetaalwijze2 == '*OVERSCHRIJVING')
                 $tennisLidgeldBetaalwijze2 = 'OVERSCHR';

             // --------------------------------------
             // Registratie betalingen in ledenbestand
             // --------------------------------------

             $values = array();
             $where = array();

             $values["adTennisLidgeldBedrag1"] =  MySQL::SQLValue($tennisLidgeldBedrag1, MySQL::SQLVALUE_NUMBER);

             if ($tennisLidgeldDatum1)
                $values["adTennisLidgeldDatum1"] =  MySQL::SQLValue($tennisLidgeldDatum1, MySQL::SQLVALUE_DATE);

             if ($tennisLidgeldBetaalwijze1)
                 $values["adTennisLidgeldBetaalwijze1"] =  MySQL::SQLValue($tennisLidgeldBetaalwijze1, MySQL::SQLVALUE_TEXT);

             $values["adTennisLidgeldBedrag2"] =  MySQL::SQLValue($tennisLidgeldBedrag2, MySQL::SQLVALUE_NUMBER);

             if ($tennisLidgeldDatum2)
                 $values["adTennisLidgeldDatum2"] =  MySQL::SQLValue($tennisLidgeldDatum2, MySQL::SQLVALUE_DATE);

             if ($tennisLidgeldBetaalwijze2)
                 $values["adTennisLidgeldBetaalwijze2"] =  MySQL::SQLValue($tennisLidgeldBetaalwijze2, MySQL::SQLVALUE_TEXT);

             if ($aaRec->aaTariefCode == '*VOETBAL')
                 $values["adTennisTariefLidVoetbal"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
             else
                 $values["adTennisTariefLidVoetbal"] =  MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);

             $values["adTennisLidgeldTotaal"] =  MySQL::SQLValue($tennisLidgeldTotaal, MySQL::SQLVALUE_NUMBER);

             $values["adTennisTariefCode"] =  MySQL::SQLValue($aaRec->aaTariefCode, MySQL::SQLVALUE_TEXT);

             // $values["adTennisLidgeldVoldaan"] =  MySQL::SQLValue('JA', MySQL::SQLVALUE_TEXT);

             $where["adCode"] =  MySQL::SQLValue($aaRec->aaCode, MySQL::SQLVALUE_TEXT);

             $db->UpdateRows("ssp_ad", $values, $where);


             // ----------------------------------------------------
             // Set switch "betalingen geboekt" in aanvragen-bestand
             // ----------------------------------------------------

             $values = array();
             $where = array();

             $values["aaBetalingenGeboekt"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);

             $where["aaId"] =  MySQL::SQLValue($pAanvraag, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

             // -------------------------------
             // volledige sync met ledenbestand
             // -------------------------------

             self::SyncMetLedenbestand($pAanvraag);

             SSP_ela::ValBetalingLidgeldTennis($persoon);


             // -------------
             // Einde functie
             // -------------

         }

         // ========================================================================================
         // Function: Registratie aanslmuiting VTV
         //
         // In:	ID Aanvraag
         //
         // ========================================================================================

         static function RegAansluiting($pAanvraag){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // -------------------
             // Ophalen nodige data
             // -------------------


             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaId = $pAanvraag and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             if (! $aaRec = $db->Row())
                 return;

             if (! $aaRec->aaCode)
                 return;

             // -----------------------------
             // Enkel indien volledig betaald
             // -----------------------------

             if (($aaRec->aaTariefCode != '*GRATIS') and ($aaRec->aaBetaald < $aaRec->aaTebetalen))
                 return;

             // ----------------------------------------------------
             // Set switch "betalingen geboekt" in aanvragen-bestand
             // ----------------------------------------------------

             $curDate = date('Y-m-d');

             $values = array();
             $where = array();

             $values["aaAansluitingVTV"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
             $values["aaAansluitdatum"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);

             $where["aaId"] =  MySQL::SQLValue($pAanvraag, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("tennis_aansluiting_aanvragen", $values, $where);

             // ---------------------------------------
             // Registratie aansluiting in ledenbestand
             // ---------------------------------------

             self::SyncMetLedenbestand($pAanvraag);


             // -------------
             // Einde functie
             // -------------

         }

         // ========================================================================================
         // Function: Versturen bevestigingsmails bij aanvraag 
         //
         // In:	ID Aanvraag
         //
         // ========================================================================================

         static function SndAanvraagBevestigingsMails($pAanvraag){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaId = $pAanvraag and aaRecStatus = 'A'";
             $db->Query($sqlStat);

             if (! $aaRec = $db->Row())
                 return;

             $adres = "$aaRec->aaStraat, $aaRec->aaPostcode $aaRec->aaGemeente";

             $geboorteDatum = SX_tools::EdtDate($aaRec->aaGeboortedatum,'%d %B %Y');
             $naam = "$aaRec->aaVoornaam $aaRec->aaNaam";

             // ---------------------
             // Mail naar inschrijver
             // ---------------------

             $mailSubject = "Schelle Sport Tennis - Uw aanvraag lidmaatschap";

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

             $mailBody .= "Beste $aaRec->naam,". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Uw aanvraag 'lidmaatschap tennis' werd geregistreerd met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Seizoen: <b>$aaRec->aaSeizoen</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: <b>$naam</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Adres: <b>$adres</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Geboortedatum: <b>$geboorteDatum</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Toegepast tarief: <b>$aaRec->aaTarief</b>";
             $mailBody .= "<br/><br/>". "\r\n";

             $GM = $aaRec->aaGM;
             $teBetalen = $aaRec->aaTebetalen + 0;
             $rekening = "BE56 0015 0154 9488";

             $mailBody .= "<b>Gelieve het totaal bedrag - $teBetalen EUR - te betalen op rekening $rekening van Schelle Sport met gestructureerde mededeling: $GM</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Alvast bedankt voor je inschrijving!";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Bestuur";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = $aaRec->aaMail;
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');


             // ----------------
             // Mail naar tennis
             // ----------------

             $mailSubject = "Schelle Sport Tennis - aanvraag lidmaatschap";

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

             $mailBody .= "Aanvraag lidmaatschap Tennis met volgende gegevens:". "\r\n";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Seizoen: <b>$aaRec->aaSeizoen</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Naam: <b>$naam</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Adres: <b>$adres</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Mail: <b>$aaRec->aaMail</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Tel: <b>$aaRec->aaTel</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Geboortedatum: <b>$geboorteDatum</b>";
             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Toegepast tarief: <b>$aaRec->aaTarief</b>";

             If ($aaRec->aaOpmerkingen){

                 $mailBody .= "<br/><br/>Opmerkingen:<br/><br/>" . "\r\n";
                 $mailBody .= nl2br($aaRec->aaOpmerkingen);

             }

             $mailBody .= "<br/><br/><br/>". "\r\n";
             $mailBody .= "Sportieve groet,";

             $mailBody .= "<br/><br/>". "\r\n";
             $mailBody .= "Schelle Sport Secretariaat";

             $mailBody .= "</body><br/><br/>". "\r\n";

             $mailTo = "tennis@schellesport.be";
             $mailBCC = "gvh@vecasoftware.com";

             $fromMail = "secretariaat@schellesport.be";
             $fromName = "Schelle Sport - Secretariaat";

             SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,'','UTF-8');

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