<?php

// We use SPOUT top read and create Excel files

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class SSP_efin
{ // define the class

    // ========================================================================================
    // Create rekening detail
    //
    // In:	Rekening
    //      Volgnummer (must be unique if given)
    //      IBAN tegenpartij
    //      Omschrijving
    //      Datum
    //      Bedrag
    //      Valuta
    //      Oorsprong
    //
    // Return: rekeningDetail ID
    // ========================================================================================

    static function CrtRekeningDetail($pRekening, $pVolgnummer, $pIBAN, $pOmschrijving, $pDatum, $pBedrag, $pValuta = 'EUR', $pOorsprong = '*UITTREKSEL', $pRefDatum = null) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // -------------------------------------------
        // Als volgnummer opgegeven -> Moet uniek zijn
        // -------------------------------------------

        if ($pVolgnummer) {

            $sqlStat = "Select count(*) as aantal from efin_rd_rekening_details where rdRekening = $pRekening and rdVolgnummer = '$pVolgnummer'";

            $db->Query($sqlStat);

            if ($rdRec = $db->Row()) {

                if ($rdRec->aantal > 0)
                    return false;

            }

        }

        // ----------------------------------------------------
        // Opsplitsen omschrijving tussen Vanwege en mededeling
        // ----------------------------------------------------

        $posMEDEDELING = strpos($pOmschrijving, "MEDEDELING :");
        $posBANKREFERENTIE = strpos($pOmschrijving, "BANKREFERENTIE :");

        $posIBAN = strpos($pOmschrijving, substr($pIBAN, 0, 4));

        $omschrijving = $pOmschrijving;

        if (!($posBANKREFERENTIE === false))
            $omschrijving = substr($pOmschrijving, 0, $posBANKREFERENTIE);

        $mededeling = "*GEEN";

        if (!($posMEDEDELING === false))
            $mededeling = substr($omschrijving, $posMEDEDELING + 13);

        $posBIC = strpos($pOmschrijving, "BIC ");
        if ($posBIC > 0)
            $posBIC += 13;
        else
            $posBIC = 0;

        $posVIA_EB_BUSINESS = strpos($pOmschrijving, "VIA EB BUSINESS");
        if ($posVIA_EB_BUSINESS > 0)
            $posVIA_EB_BUSINESS += 16;
        else
            $posVIA_EB_BUSINESS = 0;

        $posVIA_WEB_BANKING = strpos($pOmschrijving, "VIA WEB BANKING");
        if ($posVIA_WEB_BANKING > 0)
            $posVIA_WEB_BANKING += 16;
        else
            $posVIA_WEB_BANKING = 0;

        $posXXX = strpos($pOmschrijving, "XXX");
        if ($posXXX > 0)
            $posXXX += 4;
        else
            $posXXX = 0;

        $tegenpartij = "";

        $posTEGENPARTIJ = $posBIC;

        if ($posVIA_EB_BUSINESS > $posTEGENPARTIJ )
            $posTEGENPARTIJ = $posVIA_EB_BUSINESS;
        if ($posVIA_WEB_BANKING > $posTEGENPARTIJ )
            $posTEGENPARTIJ = $posVIA_WEB_BANKING;
        if ($posXXX > $posTEGENPARTIJ )
            $posTEGENPARTIJ = $posXXX;

        if ($posIBAN > $posTEGENPARTIJ)
            $tegenpartij = substr($pOmschrijving, $posTEGENPARTIJ, $posIBAN - $posTEGENPARTIJ);
        elseif ($posMEDEDELING > $posTEGENPARTIJ)
            $tegenpartij = substr($pOmschrijving, $posTEGENPARTIJ, $posMEDEDELING - $posTEGENPARTIJ);
        elseif ($posBANKREFERENTIE > $posTEGENPARTIJ)
            $tegenpartij = substr($pOmschrijving, $posTEGENPARTIJ, $posBANKREFERENTIE - $posTEGENPARTIJ);


        $posUITGEVOERDOP = strpos($mededeling, "UITGEVOERD OP");
        if (! ($posUITGEVOERDOP === false))
            $mededeling = substr($mededeling, 0, $posUITGEVOERDOP);

        if (! $mededeling )
            $mededeling = $pOmschrijving;
        if ($mededeling == '*GEEN' )
            $mededeling = $pOmschrijving;

        // -----------------------------------
        // Remove "valutadatum" van mededeling
        // -----------------------------------

        $posBANKREFERENTIE = strpos($mededeling, "BANKREFERENTIE :");
        if (! ($posBANKREFERENTIE === false))
            $mededeling = substr($mededeling, 0, $posBANKREFERENTIE);

        $posValutaDatum = strpos($mededeling, "VALUTADATUM²");

        if (! ($posValutaDatum === false))
            $mededeling = substr($mededeling, 0, $posValutaDatum);

        // Referentie-datum

        If ($pRefDatum)
            $refDatum = $pRefDatum;
        else
            $refDatum = $pDatum;

        // -------------------------------
        // Aanmaken rekening-detail record
        // -------------------------------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();

        $values["rdRekening"] = MySQL::SQLValue($pRekening, MySQL::SQLVALUE_NUMBER);
        $values["rdVolgnummer"] = MySQL::SQLValue($pVolgnummer);
        $values["rdDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdRefDatum"] = MySQL::SQLValue($refDatum, MySQL::SQLVALUE_DATE);
        $values["rdBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
        $values["rdValuta"] = MySQL::SQLValue($pValuta);
        $values["rdIBAN"] = MySQL::SQLValue($pIBAN);
        $values["rdMededeling"] = MySQL::SQLValue($mededeling);
        $values["rdTegenpartij"] = MySQL::SQLValue($tegenpartij);

        $values['rdStatusToewijzen'] = MySQL::SQLValue('*NIET');
        $values['rdStatusDoorboeken'] = MySQL::SQLValue('*NIET');

        $values["rdOorsprong"] = MySQL::SQLValue($pOorsprong);

        $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserCreatie"] = MySQL::SQLValue('*UPLOAD');
        $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserUpdate"] = MySQL::SQLValue('*UPLOAD');
        $values["rdRecStatus"] = MySQL::SQLValue('A');

        $id = $db->InsertRow("efin_rd_rekening_details", $values);

        // -------------
        // Einde functie
        // -------------

        return $id;


    }

    // ========================================================================================
    // Opladen rekening-details van XLS (export FORTIS)
    //
    // In:	Uploaded file
    //
    // Return: # lijnen opgeladen
    // ========================================================================================

    static function LoadRekeningDetailsFromXLS_FORTIS($pUpload)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once($_SESSION["SX_BASEPATH"] . '/vendor/autoload.php');

        // ----------------------
        // Get info uploaded file
        // ----------------------

        $ruRec = SSP_db::Get_EFIN_ruRec($pUpload);

        if (!$ruRec)
            return 0;

        $aantalCreated = 0;

        $rekening = $ruRec->ruRekening;

        // ----------------------------------
        // Hoogste reeds opgeladen Volgnummer
        // ----------------------------------

        $maxVolgnummer = "";

        $sqlStat = "Select max(rdVolgnummer) as maxVolgnummer from efin_rd_rekening_details where rdRekening = $rekening";
        $db->Query($sqlStat);

        if ($rdRec = $db->Row())
            $maxVolgnummer = $rdRec->maxVolgnummer;

        $fileArray = my_json_decode($ruRec->ruFile);

        $uploadedFile = $_SESSION["SX_BASEPATH"] . '/_files/efin/opladen/' . basename($fileArray[0]["name"]);

        $reader = ReaderFactory::create(Type::XLSX);

        $reader->open($uploadedFile);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {

                $volgnummer = $row[0];

                // -----------------
                // Enkel lengte == 9
                // -----------------

                $lengte = strlen($volgnummer);
                if ($lengte < 9)
                    continue;

                if ($volgnummer == 'Volgnummer')
                    continue;

                if ($maxVolgnummer and ($volgnummer <= $maxVolgnummer) and ($ruRec->ruEnkelNieuwe == 1))
                    continue;

                $datum = $row[1]->format("Y-m-d");

                $bedrag = $row[3];

                $IBAN = $row[5];

                $omschrijving = $row[6];

                // --------------------------------------------
                // Ref. datum (enkel bij TERMINAL GLOBALISATIE)
                // --------------------------------------------

                $refDatum = null;

                $omschrijvingUpper = strtoupper($omschrijving);
                $ibanUpper = strtoupper($IBAN);

                if (strpos($omschrijvingUpper, "TERMINAL") !== false){

                    if (strpos($ibanUpper, "GLOBALISATIE") !== false) {

                        $pos = strpos($omschrijvingUpper, "DATUM");

                        if ($pos !== false){

                            $year = substr($omschrijvingUpper,$pos+14,4);
                            $month = substr($omschrijvingUpper,$pos+11,2);
                            $day = substr($omschrijvingUpper,$pos+8,2);

                            if ($year >= 2019 and $year <= 2050 and $month >= 01 and $month <= 12 and $day >= 1 and $day <= 31)
                                $refDatum = $year . "-" . $month . "-" . $day;

                        }

                    }

                }

                $rekeningDetail = self::CrtRekeningDetail($rekening, $volgnummer, $IBAN, $omschrijving, $datum, $bedrag, 'EUR', '*UITTREKSEL', $refDatum);

                if ($rekeningDetail) {

                    $aantalCreated++;
                    self::CrtAutoToewijzing($rekeningDetail);
                    self::SetRdStatusToewijzen($rekeningDetail);

                    self::BookRD($rekeningDetail, $boodschap);
                    self::SetRdStatusDoorboeken($rekeningDetail);


                }


            }
        }

        if ($aantalCreated){

            $sqlStat = "update efin_ru_rekening_details_upload set ruStatus = '*VERWERKT', ruAantal = $aantalCreated where ruId = $pUpload";
            $db->Query($sqlStat);
            
            
        }

        // ------------------
        // Herberekenen SALDI
        // ------------------

        self::FillRekeningDetailSaldi($rekening);
        
        // -------------
        // Einde functie
        // -------------

        return $aantalCreated;


    }

    // ========================================================================================
    //  Test GM
    //
    // In:	GM
    //
    // Return: Geldig ?
    // ========================================================================================

    static function CheckGM($pGM){

        $number = self::CvtGmToNum($pGM);

        $alfa = strval($number);

        $numberBase = floatval(substr($alfa, 0, 10));
        $numberCheck = floatval(substr($alfa, -2, 2));

        $checkDigit = $numberBase - (floor($numberBase / 97) * 97);
        if ($checkDigit == 0)
            $checkDigit = 97;

        // -------------
        // Einde functie
        // -------------

        return ($numberCheck == $checkDigit);

    }


    // ========================================================================================
    //  Check BTW bedragen IF
    //
    // In:	Maatstaf
    //      BTW Code
    //      BTW Bedrag (optioneel)
    //
    // Return: BTW Bedrag (null indien ongeldig (afwijking >  0,01)
    // ========================================================================================

    static function ChkIfBtwBedrag($pMaatstaf, $pBTWcode, $pBTWbedrag){

        include_once(SX::GetClassPath("_db.class"));

        if (! $pMaatstaf)
            return 0;

        $taRec = SSP_db::Get_SX_taRec('EFIN_BTW_TARIEVEN', $pBTWcode);

        if (! $taRec)
            return null;

        $BTWperc = $taRec->taNumData / 100;

          // -----------
        // BTW % *ZERO
        // -----------

        if (! $BTWperc and $pBTWbedrag)
            return null;

        // ---------------------------------------
        // Bereken BTW bedrag indien niet ingevuld
        // ---------------------------------------

        $BTWbedrag = round(($pMaatstaf * $BTWperc), 2 ) + 0;

        if (! $pBTWbedrag)
            return $BTWbedrag;

        $verschil = round(Abs(Abs($pBTWbedrag) - Abs($BTWbedrag)),2);

        if ($verschil <= 0.19)
            return $pBTWbedrag;
        else
            return null;


    }

    // ========================================================================================
    //  Convert OGM to Numeric
    //
    // In:	GM
    //
    // Return: Nummerieke waarde (12/0)
    // ========================================================================================

    static function CvtGmToNum($pGM) {

        $number = preg_replace('/[^0-9]/', '', $pGM);

        return $number;

    }

    // ========================================================================================
    //  Convert OGM to Alfa
    //
    // In:	GM (nummeriek)
    //
    // Return: Alfa waarde (+++123/1234/12345+++)
    // ========================================================================================

    static function CvtGmToAlfa($pGMn) {

        $Gm = strval($pGMn);

        $GMa = '+++' . substr($Gm, 0, 3) . '/' . substr($Gm, 3, 4) . '/' . substr($Gm, 7, 5) . '+++';

        return $GMa;

    }

    // ========================================================================================
    //  Get "next" GM
    //
    // In:	Code (bv. *LIDGELD_VB)
    //
    // Return: GM
    // ========================================================================================

    static function GetNextGM($pCode)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $gmRec = SSP_db::Get_EFIN_gmRec($pCode);

        if (!$gmRec)
            return null;

        $stringBase = $gmRec->gmPrefix
            . str_pad($gmRec->gmVolgnummer, 7, '0', STR_PAD_LEFT);

        $numberBase = number_format($stringBase, 0, '', '');

        $checkDigit = $numberBase - (floor($numberBase / 97) * 97);
        if ($checkDigit == 0)
            $checkDigit = 97;

        $GM = $stringBase . str_pad($checkDigit, 2, '0', STR_PAD_LEFT);

        $GM = '+++' . substr($GM, 0, 3) . '/' . substr($GM, 3, 4) . '/' . substr($GM, 7, 5) . '+++';

        $sqlStat = "update efin_gm_gestructureerde_mededeling set gmVolgnummer = gmVolgnummer + 1, gmLaatsteGM = '$GM', gmDatumUpdate = now() where gmCode = '$pCode'";
        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

        return $GM;

    }


    // ========================================================================================
    //  OPhalen toewijzing rekening detail (voor in overzicht)
    //
    // In:	Rekening Detail
    //
    // Return: HTML snippet
    // ========================================================================================

    static function GetToewijzingHTML($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("ela.class"));

        // -----------------------
        // Ophalen rekening-detail
        // -----------------------

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return "&nbsp;";

        $valuta = $rdRec->rdValuta;

        // ------------------
        // Ophalen toewijzing
        // ------------------

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen left outer join efin_vr_ventilatie_rekeningen on vrId = twVentilatieRekening where twRekeningDetail = $pRekeningDetail and twVentilatieRekening > 0" ;

        $db->Query($sqlStat);

        $html = "";

        $toegewezenBedrag = 0;

        $geenPersoon = false;

        while($twRec = $db->Row()){

            $toegewezenBedrag += $twRec->twBedrag;

            $bedrag = $twRec->twBedrag + 0;
            $ventilatieRekening = $twRec->vrNaam;

            $toewijzingHTML = "$bedrag $valuta: $ventilatieRekening";

            if ($twRec->twPersoon) {

                $naam = SSP_ela::GetPersoonInfoNaam($twRec->twPersoon)  ;
                $toewijzingHTML .= " [$naam]";
            }
            elseif ($twRec->vrPersoonVerplicht == 1)
                $toewijzingHTML .= " [*** GEEN PERSOON ***]";

            if ($twRec->twReferentie)
                $toewijzingHTML .= " - Ref. $twRec->twReferentie";

            if ($html)
                $html .= "<hr style='clear: both; margin-top: 0px; margin-bottom: 0px'/>";

            $html .= $toewijzingHTML;

        }

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ========================================================================================
    //  Automatisch toewijzen rekening detail
    //
    // In:	Rekening Detail
    //      Ook indien reeds toegewezen?
    //
    // Return: Status (*ERROR, *REEDS_TOEGEWEZEN, *TOEGEWEZEN, *WACHT)
    // ========================================================================================

    static function CrtAutoToewijzing($pRekeningDetail, $pOokAlsReedsToegewezen = false) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));
        include_once(Sx::GetClassPath("efin_interface_epra.class"));

        $ventilatieRekening = 0;
        $persoon = null;
        $referentie = null;

        $inkomendeFactuur = false;
        $uitgaandeFactuur = false;
        $epra = false;

        // ------------------------
        // Ophalen "rekening-detail
        // ------------------------

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return '*ERROR';

        $rekening = $rdRec->rdRekening;

        // --------------------------------
        // Niet indien reeds een toewijzing
        // --------------------------------

        if (! $pOokAlsReedsToegewezen) {

            $sqlStat = "Select count(*) as aantal from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twVentilatieRekening > 0";

            $db->Query($sqlStat);

            $twRec = $db->Row();

            if ($twRec->aantal > 0)
                return "*REEDS_TOEGEWEZEN";

        }

        // --------------
        // Betaalterminal
        // --------------

        if (self::CrtTwBetaalterminal($rdRec))
            return '*AANGEMAAKT';

        // ------------------
        // Inkomende factuur?
        // ------------------

        $inkomendeFactuur = self::FindRdIf($pRekeningDetail);
                
        // ------------------
        // Uitgaande Factuur?
        // ------------------
        
        if (! $inkomendeFactuur and ! $uitgaandeFactuur)
            $uitgaandeFactuur = self::FindRdUf($pRekeningDetail);

        // ---------------------
        // Betaling vanuit EPRA?
        // ---------------------

        if (! $inkomendeFactuur and ! $uitgaandeFactuur) {
            $udRec = SSP_efin_interface_epra::FindUitbetalingVoorstelDetail($pRekeningDetail, $persoon, $ventilatieRekening);

            if ($udRec and $ventilatieRekening) {

                $epra = true;
                $referentie = "EPRA";

                SSP_efin_interface_epra::RegBoekingEFIN($udRec->udId, $pRekeningDetail);

            }
        }


        // ----------------------------------------
        // Bepalen ventilatie-rekening adhv mapping
        // ----------------------------------------


        // ---------------
        // Op basis van GM
        // ---------------

        if ((! $inkomendeFactuur) and (! $uitgaandeFactuur) and (! $epra)) {

            $mededeling = trim($rdRec->rdMededeling);
            $mededeling = substr($mededeling, 0, 12);
            $mededeling = trim($mededeling);
            $validGM = false;
            $GMn = null;

            $length = strlen($mededeling);

            if ($length == 12) {

                $validGM = self::CheckGM($mededeling);

                if ($validGM) {

                    $GMn = self::CvtGmToNum($mededeling);

                    $sqlStat = "Select * from efin_am_analytische_mapping where amOGMn = $GMn and amRecStatus = 'A'";

                    $db->Query($sqlStat);

                    if ($amRec = $db->Row()) {
                        $ventilatieRekening = $amRec->amVentilatieRekening;
                        $persoon = $amRec->amPersoon;
                        $referentie = $amRec->amReferentie;
                    }

                }


            }
        }

        // ---------------------------------
        // Op basis van mededeling (mapping)
        // ---------------------------------

        if ((! $inkomendeFactuur) and (! $uitgaandeFactuur) and (! $epra)) {

            $IBAN = $rdRec->rdIBAN;

            $mededeling = strtoupper(trim($rdRec->rdMededeling));
            $bedrag = $rdRec->rdBedrag;

            if (!$ventilatieRekening) {

                $sqlStat = "Select * from efin_am_analytische_mapping where amOGM <= ' ' and (amSeek1 > ' '  or amIBAN = '$IBAN') and amRecStatus = 'A'";

                $db->Query($sqlStat);

                while ($amRec = $db->Row()) {

                    if ($amRec->amRekening and ($rekening != $amRec->amRekening))
                        continue;

                    $seek1 = trim(strtoupper($amRec->amSeek1));
                    $seek2 = trim(strtoupper($amRec->amSeek2));
                    $seek3 = trim(strtoupper($amRec->amSeek3));
                    $seek4 = trim(strtoupper($amRec->amSeek4));
                    $specifiekBedrag = $amRec->amBedrag;
                    $posNeg = $amRec->amPositiefNegatief;


                    if ($seek1 and strpos($mededeling, $seek1) === false)
                        continue;
                    if ($seek2 and strpos($mededeling, $seek2) === false)
                        continue;
                    if ($seek3 and strpos($mededeling, $seek3) === false)
                        continue;
                    if ($seek4 and strpos($mededeling, $seek4) === false)
                        continue;

                    if ($specifiekBedrag and ($specifiekBedrag != $bedrag))
                        continue;
                    if (($posNeg == '*POS') and ($bedrag < 0))
                        continue;
                    if (($posNeg == '*NEG') and ($bedrag > 0))
                        continue;

                    $ventilatieRekening = $amRec->amVentilatieRekening;
                    $persoon = $amRec->amPersoon;
                    $referentie = $amRec->amReferentie;
                    break;

                }

            }
        }


        // --------------------------------
        // Wis bestaande (wacht) toewijzing
        // --------------------------------

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail ";
        $db->Query($sqlStat);

        while ($twRec = $db->Row()){

            self::CrtOudeToewijzing($twRec->twId);
        }

        $sqlStat = "Delete from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail";
        $db->Query($sqlStat);

        self::HdlOudeToewijzingen();

        // -------------------
        // Aanmaken toewijzing
        // -------------------

        $curDateTime = date('Y-m-d H:i:s');

        if ((! $inkomendeFactuur) and (! $uitgaandeFactuur)) {

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($rdRec->rdBedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["twPersoon"] = MySQL::SQLValue($persoon);
            $values["twReferentie"] = MySQL::SQLValue($referentie);

            $values["twUserCreatie"] = MySQL::SQLValue("*AUTO");
            $values["twUserUpdate"] = MySQL::SQLValue("*AUTO");

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::UpdAnalytischeSaldiOpBasisToewijzing($id);

            if ($ventilatieRekening == 0)
                return "*WACHT";
            else
                return "*AANGEMAAKT";

        }
        
        // ------------------------------------
        // VENTILATIE op basis INKOMENDE FACTUUR
        // -------------------------------------
        
        if ($inkomendeFactuur){
            
            self::CrtTwOpBasisInkomdeFactuur($pRekeningDetail, $inkomendeFactuur, "*AUTO");

            return "*AANGEMAAKT";

        }

        // ------------------------------------
        // VENTILATIE op basis UITGAANDE FACTUUR
        // -------------------------------------

        if ($uitgaandeFactuur){

            self::CrtTwOpBasisUitgaandeFactuur($pRekeningDetail, $uitgaandeFactuur, "*AUTO");

            return "*AANGEMAAKT";

        }


    }

    // ========================================================================================
    //  Aanmaken Rekening Detail Toewijzing(en) op basis INKOMENDE FACTUUR
    //
    // In:	Rekening Detail ID
    //      Inkomende Factuur ID
    //      User ID
    //      Bedrag (optioneel)
    //
    // ========================================================================================

    static function CrtTwOpBasisInkomdeFactuur($pRekeningDetail, $pInkomendeFactuur, $pUserId = '*AUTO', $pBedrag = 0){
    
        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);
        $lvRec = SSP_db::Get_EFIN_lvRec($ifRec->ifLeverancier);

        $referentie = "IF: $lvRec->lvNaam / $ifRec->ifFactuurnummer / $ifRec->ifFactuurdatum";

        if ($ifRec->ifOmschrijving)
            $referentie = "$referentie / $ifRec->ifOmschrijving";

        $curDateTime = date('Y-m-d H:i:s');

        $bedrag = abs($pBedrag);

        // ------------
        // Ventilatie 1
        // ------------

        if ($ifRec->ifVentilatieRekening1) {

            $ventBedrag = $ifRec->ifVentilatieBedrag1 * -1;
            $ventRekening = $ifRec->ifVentilatieRekening1;

            if (abs($pBedrag) >  0) {

                if (abs($ventBedrag) > $bedrag) {

                    if ($ventBedrag < 0)
                        $ventBedrag = $bedrag * -1;
                    else
                        $ventBedrag = $bedrag;

                    $bedrag = 0;
                }
                else
                    $bedrag -= abs($ventBedrag);


            }

            if (abs($ventBedrag) > 0) {

                $values = array();

                $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
                $values["twBedrag"] = MySQL::SQLValue($ventBedrag, MySQL::SQLVALUE_NUMBER);
                $values["twVentilatieRekening"] = MySQL::SQLValue($ventRekening, MySQL::SQLVALUE_NUMBER);
                $values["twReferentie"] = MySQL::SQLValue($referentie);

                $values['twInkomendeFactuur'] = MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

                $values["twUserCreatie"] = MySQL::SQLValue($pUserId);
                $values["twUserUpdate"] = MySQL::SQLValue($pUserId);

                $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

                self::UpdAnalytischeSaldiOpBasisToewijzing($id);

            }

        }

        // ------------
        // Ventilatie 2
        // ------------

        if ($ifRec->ifVentilatieRekening2) {

            $ventBedrag = $ifRec->ifVentilatieBedrag2 * -1;
            $ventRekening = $ifRec->ifVentilatieRekening2;

            if (abs($pBedrag) >  0) {

                if (abs($ventBedrag) > $bedrag) {

                    if ($ventBedrag < 0)
                        $ventBedrag = $bedrag * -1;
                    else
                        $ventBedrag = $bedrag;

                    $bedrag = 0;
                }
                else
                    $bedrag -= abs($ventBedrag);


            }

            if (abs($ventBedrag) > 0) {

                $values = array();

                $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
                $values["twBedrag"] = MySQL::SQLValue($ventBedrag, MySQL::SQLVALUE_NUMBER);
                $values["twVentilatieRekening"] = MySQL::SQLValue($ventRekening, MySQL::SQLVALUE_NUMBER);
                $values["twReferentie"] = MySQL::SQLValue($referentie);

                $values['twInkomendeFactuur'] = MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

                $values["twUserCreatie"] = MySQL::SQLValue($pUserId);
                $values["twUserUpdate"] = MySQL::SQLValue($pUserId);

                $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

                self::UpdAnalytischeSaldiOpBasisToewijzing($id);

            }

        }

        // ------------
        // Ventilatie 3
        // ------------

        if ($ifRec->ifVentilatieRekening3) {

            $ventBedrag = $ifRec->ifVentilatieBedrag3 * -1;
            $ventRekening = $ifRec->ifVentilatieRekening3;

            if (abs($pBedrag) >  0) {

                if (abs($ventBedrag) > $bedrag) {

                    if ($ventBedrag < 0)
                        $ventBedrag = $bedrag * -1;
                    else
                        $ventBedrag = $bedrag;

                    $bedrag = 0;
                }
                else
                    $bedrag -= abs($ventBedrag);


            }

            if (abs($ventBedrag) > 0) {

                $values = array();

                $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
                $values["twBedrag"] = MySQL::SQLValue($ventBedrag, MySQL::SQLVALUE_NUMBER);
                $values["twVentilatieRekening"] = MySQL::SQLValue($ventRekening, MySQL::SQLVALUE_NUMBER);
                $values["twReferentie"] = MySQL::SQLValue($referentie);

                $values['twInkomendeFactuur'] = MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

                $values["twUserCreatie"] = MySQL::SQLValue($pUserId);
                $values["twUserUpdate"] = MySQL::SQLValue($pUserId);

                $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

                self::UpdAnalytischeSaldiOpBasisToewijzing($id);

            }

        }
        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Aanmaken Rekening Detail Toewijzing(en) op basis BETAALTERMINAL
    //
    // In:	Rekening Detail-record
    //
    // Out: Toewwijzing gelukt?
    //
    // ========================================================================================

    static function CrtTwBetaalterminal($pRdRec)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // --------------------------------------
        // IBAN tegenpartij moet BE97001853432449
        // --------------------------------------

        if ($pRdRec->rdIBAN != 'BE97001853432449')
            return false;

        // ----------------
        // Ophalen settings
        // ----------------

        $xxRec1 = SSP_db::Get_EFIN_xxRec('*TICKETING', '*BETAALTERMINAL-BETALINGEN');

        if (!$xxRec1)
            return false;

        $xxRec2 = SSP_db::Get_EFIN_xxRec('*TICKETING', '*BETAALTERMINAL-KOSTEN');

        if (!$xxRec2)
            return false;

        // --------------------------------
        // Haal diverse info uit mededeling
        // --------------------------------

        $tid = null;
        $refDatum = null;
        $brutoBedrag = null;
        $btNaam = null;

        $mededeling = strtoupper(trim($pRdRec->rdMededeling));

        // Terminal ID
        $posTID = strpos($mededeling, " TID ");
        if ($posTID !== false)
            $tid = substr($mededeling, $posTID + 5, 8);

        // Referentie datum
        $posRefDatum = strpos($mededeling, " DATE ");
        if ($posRefDatum !== false)
            $refDatum = substr($mededeling, $posRefDatum + 6, 10);

        // Bruto bedrag
        $posBrutoBedrag = strpos($mededeling, " BRUT ");
        if ($posBrutoBedrag !== false)
            $brutoBedrag = substr($mededeling, $posBrutoBedrag + 6, 10);

        if (!$tid or !$refDatum or !$brutoBedrag)
            return false;


        $nettoBedrag = $pRdRec->rdBedrag;

        $kostBedrag = $brutoBedrag - $nettoBedrag;
        $kostBedrag = $kostBedrag * -1;

        // Ophalen betaalterminal naam
        $sqlStat = "Select * from efin_bt_betaalterminals where btTID = '$tid'";
        $db->Query($sqlStat);
        if ($btRec = $db->Row())
            $btNaam = $btRec->btNaam;

        // ----------------
        // Update ref-datum
        // ----------------

        if ($refDatum) {

            $values = array();
            $where = array();

            $values["rdRefDatum"] = MySQL::SQLValue($refDatum, MySQL::SQLVALUE_DATE);

            $where["rdId"] = MySQL::SQLValue($pRdRec->rdId, MySQL::SQLVALUE_NUMBER);

            $db->UpdateRows("efin_rd_rekening_details", $values, $where);
         }

        // ---------------------------
        // Toewijzingen bruto betaling
        // ---------------------------

        $referentie = "$btNaam - Datum: $refDatum";
        $curDateTime = date('Y-m-d H:i:s');

        $ventilatie =  $xxRec1->xxVentilatie;

        $values = array();

        $values["twRekeningDetail"] = MySQL::SQLValue($pRdRec->rdId, MySQL::SQLVALUE_NUMBER);
        $values["twBedrag"] = MySQL::SQLValue($brutoBedrag, MySQL::SQLVALUE_NUMBER);
        $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatie, MySQL::SQLVALUE_NUMBER);
        $values["twReferentie"] = MySQL::SQLValue($referentie);

        $values["twUserCreatie"] = MySQL::SQLValue('*AUTO');
        $values["twUserUpdate"] = MySQL::SQLValue('*AUTO');

        $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

        // -------------------
        // Toewijzingen Kosten
        // -------------------

        $referentie = "$btNaam - Datum: $refDatum";
        $curDateTime = date('Y-m-d H:i:s');

        $ventilatie =  $xxRec2->xxVentilatie;

        $values = array();

        $values["twRekeningDetail"] = MySQL::SQLValue($pRdRec->rdId, MySQL::SQLVALUE_NUMBER);
        $values["twBedrag"] = MySQL::SQLValue($kostBedrag, MySQL::SQLVALUE_NUMBER);
        $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatie, MySQL::SQLVALUE_NUMBER);
        $values["twReferentie"] = MySQL::SQLValue($referentie);

        $values["twUserCreatie"] = MySQL::SQLValue('*AUTO');
        $values["twUserUpdate"] = MySQL::SQLValue('*AUTO');

        $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

        // -------------
        // Einde functie
        // -------------

        return true;

    }


    // ========================================================================================
    //  Aanmaken Rekening Detail Toewijzing(en) op basis UITGAANDE FACTUUR
    //
    // In:	Rekening Detail ID
    //      Uitgaande Factuur ID
    //      User ID
    //
    // ========================================================================================

    static function CrtTwOpBasisUitgaandeFactuur($pRekeningDetail, $pUitgaandeFactuur, $pUserId = '*AUTO'){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $curDateTime = date('Y-m-d H:i:s');

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);
        $klRec = SSP_db::Get_EFIN_klRec($ufRec->ufKlant);

        $referentie = "UF: $klRec->klNaam / $ufRec->ufFactuurnummer / $ufRec->ufFactuurdatum";

        $sqlStat = "Select * from efin_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur order by udSort";
        $db->Query($sqlStat);

        $ventBedragen = array();
        $ventRekeningen = array();

        $i = 0;

        while ($udRec = $db->Row()){

            $i += 1;

            $ventBedrag = $udRec->udBedragMaatstaf + $udRec->udBTWBedrag;
            $ventRekening = $udRec->udVentilatieRekening;

            if ($i == 1){
                $ventBedragen[] = $ventBedrag;
                $ventRekeningen[] = $ventRekening;

            }

            if ($i > 1){

                $key = array_search($ventRekening, $ventRekeningen);

                if ($key === false) {
                    $ventBedragen[] = $ventBedrag;
                    $ventRekeningen[] = $ventRekening;
                }
               else {
                    $ventBedragen[$key] += $ventBedrag;
                }

            }

        }

        foreach ($ventRekeningen as $key => $ventRekening) {

            $ventBedrag =  $ventBedragen[$key];
            // $ventRekening = $udRec->udVentilatieRekening;

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($ventBedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventRekening, MySQL::SQLVALUE_NUMBER);
            $values["twReferentie"] = MySQL::SQLValue($referentie);

            $values['twUitgaandeFactuur'] = MySQL::SQLValue($ufRec->ufId, MySQL::SQLVALUE_NUMBER);

            $values["twUserCreatie"] = MySQL::SQLValue($pUserId);
            $values["twUserUpdate"] = MySQL::SQLValue($pUserId);

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db2->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::UpdAnalytischeSaldiOpBasisToewijzing($id);

        }

        // -------------------------
        // Reeds betaald (voorschot)
        // -------------------------

        if ($ufRec->ufReedsBetaald){

            $ventBedrag =$ufRec->ufReedsBetaald * -1;
            $ventRekening = $ufRec->ufReedsBetaaldVentilatie;

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($ventBedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventRekening, MySQL::SQLVALUE_NUMBER);
            $values["twReferentie"] = MySQL::SQLValue($referentie);

            $values['twUitgaandeFactuur'] = MySQL::SQLValue($ufRec->ufId, MySQL::SQLVALUE_NUMBER);

            $values["twUserCreatie"] = MySQL::SQLValue($pUserId);
            $values["twUserUpdate"] = MySQL::SQLValue($pUserId);

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db2->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::UpdAnalytischeSaldiOpBasisToewijzing($id);


        }

        // -------------
        // Einde functie
        // -------------

    }

       // ========================================================================================
    //  Bepalen inkomende factuur op basis rekening detail
    //
    // In:	Rekening Detail
    // Return: Inkomende factuur (null indien niets gevonden)
    //
    // ========================================================================================

    static function FindRdIf($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (!$rdRec)
            return null;

        $bedrag = abs($rdRec->rdBedrag);
        $rdIBAN = str_replace(' ', '', $rdRec->rdIBAN);

        $inkomendeFactuur = null;

        // ---------------------------------------------
        // Zoek inkomende factuur op basis "GM" & bedrag
        // ---------------------------------------------

        $mededeling = trim($rdRec->rdMededeling);
        $mededeling = substr($mededeling, 0, 12);
        $validGM = false;
        $GMn = null;

        $length = strlen($mededeling);

        if ($length == 12) {

            $validGM = self::CheckGM($mededeling);

            if ($validGM)
                $GMn = self::CvtGmToNum($mededeling);
        }


        if ($validGM){

            $sqlStat = "Select * from efin_if_inkomende_facturen where ifFactuurstatus <> '*BETAALD' and ifControle = '*OK' and ifBetaalMededelingGM > ' ' and ifVentilatieRekening1 > 0 and ifBedrag = $bedrag";

            $db->Query($sqlStat);

            while ($ifRec = $db->Row()){

                $ifGMn = self::CvtGmToNum($ifRec->ifBetaalMededelingGM);

                if (! $ifGMn)
                    continue;

                if ($ifGMn == $GMn) {

                    $inkomendeFactuur = $ifRec->ifId;
                    break;

                }

            }

        }

        // ----------------------------------------------------------------------
        // Zoek inkomende factuur op basis "exacte omschrijving" & bedrag & IBAN
        // ---------------------------------------------------------------------

        if (! $inkomendeFactuur){

            $mededeling = strtoupper(trim($rdRec->rdMededeling));

            $sqlStat = "Select * from efin_if_inkomende_facturen where ifFactuurstatus <> '*BETAALD' and ifControle = '*OK' and ifBetaalMededelingVrij > ' ' and ifVentilatieRekening1 > 0 and ifBedrag = $bedrag";

            $db->Query($sqlStat);

            while ($ifRec = $db->Row()){

                // Check IBAN
                $ifIBAN = str_replace(' ', '', $ifRec->ifBetaalOpRekening);

                if ($rdIBAN != $ifIBAN)
                    continue;

                // Check "exacte mededeling"
                $betaalMededeling = strtoupper($ifRec->ifBetaalMededelingVrij);

                if ($mededeling == $betaalMededeling) {

                    $inkomendeFactuur = $ifRec->ifId;
                    break;

                }
            }

        }

        // --------------------------------------------------------------------
        // Zoek inkomende factuur op basis "fuzzy omschrijving" & bedrag & IBAN
        // -------------------------------------------------------------------

        if (! $inkomendeFactuur){

            $mededeling = strtoupper(trim($rdRec->rdMededeling));

            $sqlStat = "Select * from efin_if_inkomende_facturen where ifFactuurstatus <> '*BETAALD' and ifControle = '*OK' and ifBetaalMededelingVrij > ' ' and ifVentilatieRekening1 > 0 and ifBedrag = $bedrag";

            $db->Query($sqlStat);

            while ($ifRec = $db->Row()){

                // Check IBAN
                $ifIBAN = str_replace(' ', '', $ifRec->ifBetaalOpRekening);

                if ($rdIBAN != $ifIBAN)
                    continue;

                // Check "fuzzy mededeling"
                $betaalMededeling = strtoupper($ifRec->ifBetaalMededelingVrij);

                similar_text($betaalMededeling, $mededeling, $perc);

                if ($perc > 80 ) {

                    $inkomendeFactuur = $ifRec->ifId;
                    break;

                }
            }

        }

        // -------------
        // Einde functie
        // -------------

        return $inkomendeFactuur;

    }

    // ========================================================================================
    //  Bepalen uitgaande factuur op basis rekening detail
    //
    // In:	Rekening Detail
    // Return: Inkomende factuur (null indien niets gevonden)
    //
    // ========================================================================================

    static function FindRdUf($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (!$rdRec)
            return null;

        $uitgaandeFactuur = null;

        // ------------------------------------
        // Zoek uitgaande factuur op basis "GM"
        // ------------------------------------

        $mededeling = trim($rdRec->rdMededeling);
        $mededeling = substr($mededeling, 0, 12);
        $validGM = false;
        $GMn = null;

        $length = strlen($mededeling);

        if ($length == 12) {

            $validGM = self::CheckGM($mededeling);

            if ($validGM)
                $GMn = self::CvtGmToNum($mededeling);
        }

        if ($validGM){

            $sqlStat = "Select * from efin_uf_uitgaande_facturen where substr(ufFactuurStatus,1,8) <> '*BETAALD' and ufBetaalMededelingGMnum = $GMn";

            $db->Query($sqlStat);

            while ($ufRec = $db->Row()){

               $uitgaandeFactuur = $ufRec->ufId;
               break;

            }

        }

        // -------------
        // Einde functie
        // -------------

        return $uitgaandeFactuur;

    }

    // ========================================================================================
    //  Zet status inkomende facturen na factuurcontrole
    //
    // In:	referentie-Type (*HORECAA)
    //
    // ========================================================================================

    static function SetIfStatusNaControle($pReferentieType = '*HORECA') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_if_inkomende_facturen where ifFactuurstatus = '*WACHTEN' and ifReferentieType = '$pReferentieType'";
        $db->Query($sqlStat);

        while ($ifRec = $db->Row())
            self::SetIfControle($ifRec ->ifId);

    }

    // ========================================================================================
    //  Opvullen rekening-detail status "toewijzen"
    //
    // In:	Rekening Detail
    //
    // ========================================================================================

    static function SetRdStatusToewijzen($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (!$rdRec)
            return;

        if (abs($rdRec->rdBedrag) == 0)
            return;

        $ventilatieRekeningen = "";

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen left outer join efin_vr_ventilatie_rekeningen on vrId = twVentilatieRekening where twRekeningDetail = $pRekeningDetail and twVentilatieRekening > 0" ;

        $db->Query($sqlStat);

        $statusToewijzen = '*NIET';
        $bedragToegewezen = 0;
        $persoonOntbreekt = false;

        while ($twRec = $db->Row()) {

            $vrIdCode = str_pad($twRec->twVentilatieRekening, 5, '0', STR_PAD_LEFT);

            if ($ventilatieRekeningen)
                $ventilatieRekeningen .= ',';

            $ventilatieRekeningen .= $vrIdCode;

            $bedragToegewezen += $twRec->twBedrag;

            if (($twRec->vrPersoonVerplicht == 1) and (! $twRec->twPersoon))
                $persoonOntbreekt = true;

        }

        $rest = abs($bedragToegewezen) - abs($rdRec->rdBedrag);
        $rest = round($rest,2);

        if (!$bedragToegewezen)
            $statusToewijzen = '*NIET';

        elseif ($rest > 0 and $rdRec->rdBedrag > 0)
            $statusToewijzen = '*TEVEEL';

        elseif ($rest < 0 and $rdRec->rdBedrag < 0)
            $statusToewijzen = '*TEVEEL';

        elseif ($rest == 0)
            $statusToewijzen = '*VOLLEDIG';

        else
            $statusToewijzen = '*DEEL';

        if ($persoonOntbreekt)
            $statusToewijzen = '*ONVOLLEDIG';

        $toewijzingHTML = self::GetToewijzingHTML($pRekeningDetail);


        // -------------
        // Update status
        // -------------

        $values = array();
        $where = array();

        $values["rdStatusToewijzen"] =MySQL::SQLValue($statusToewijzen);
        $values["rdVentilatieRekeningen"] =MySQL::SQLValue($ventilatieRekeningen);
        $values["rdToewijzingHTML"] =MySQL::SQLValue($toewijzingHTML);

        $where["rdId"] =  MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_rd_rekening_details", $values, $where);

        // ----------------------------------
        // Opvullen veld "extra omschrijving"
        // ----------------------------------

        self::FillRdExtraInfo($pRekeningDetail);

        // -------------------------
        // Bereken bedrag toegewezen
        // -------------------------

        self::RegRdBedragToegewezen($pRekeningDetail);

        // -------------
        // Einde functie
        // -------------

        return;

    }

    // ========================================================================================
    //  Wijzingen alle ToewijzingHTML voor bepaalde ventilatie rekening
    //
    // In:	Ventilatie-rekening
    //
    // ========================================================================================

    static function UpdVRToewijzingHTML($pVentilatieRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twVentilatieRekening = $pVentilatieRekening";
        $db->Query($sqlStat);

        while ($twRec = $db->Row()){

            $rekeningDetail = $twRec->twRekeningDetail;

            $toewijzingHTML = self::GetToewijzingHTML($rekeningDetail);

           // -------------
            // Update status
            // -------------

            $values = array();
            $where = array();

            $values["rdToewijzingHTML"] =MySQL::SQLValue($toewijzingHTML);

            $where["rdId"] =  MySQL::SQLValue($rekeningDetail, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("efin_rd_rekening_details", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Opvullen rekening-detail status "doorboeken"
    //
    // In:	Rekening Detail
    //
    // ========================================================================================

    static function SetRdStatusDoorboeken($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (!$rdRec)
            return;

        $statusDoorboeken = "*NIET";

        // -------------------------
        // Check of "van toepassing"
        // -------------------------

        $statusDoorboeken = "";
        $bedragDoorTeBoeken = 0;

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen inner join efin_vr_ventilatie_rekeningen on vrId = twVentilatieRekening and (vrDoorboeken = 1 and (twInkomendeFactuur <= 0 or twInkomendeFactuur is null) and (twUitgaandeFactuur <= 0 or twUitgaandeFactuur is null) ) where twRekeningDetail = $pRekeningDetail" ;
        $db->Query($sqlStat);

        while ($twRec = $db->Row())
            $bedragDoorTeBoeken += $twRec->twBedrag;

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and (twInkomendeFactuur > 0 or twUitgaandeFactuur > 0)";
        $db->Query($sqlStat);

        while ($twRec = $db->Row())
            $bedragDoorTeBoeken += $twRec->twBedrag;


        if ($bedragDoorTeBoeken == 0)
            $statusDoorboeken = "*NVT";

        // -------------------------------------------
        // Bepalen of reeds (gedeeltelijk) doorgeboekt
        // -------------------------------------------

        if ($statusDoorboeken != '*NVT') {


            $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen left outer join efin_vr_ventilatie_rekeningen on vrId = twVentilatieRekening where twRekeningDetail = $pRekeningDetail and twVentilatieRekening > 0";

            $db->Query($sqlStat);

            $statusDoorboeken = '*NIET';
            $bedragDoorgeboekt = 0;
            $warning = false;

            while ($twRec = $db->Row()) {

                if ($twRec->twDoorgeboekt == 1)
                    $bedragDoorgeboekt += $twRec->twBedrag;

                if (($twRec->twDoorgeboekt != 1) and ($twRec->twDoorboekBoodschap) and ($twRec->twDoorboekBoodschap != '*OK'))
                    $warning = true;

            }

            if (!$bedragDoorgeboekt)
                $statusDoorboeken = '*NIET';

            elseif (round($bedragDoorgeboekt,2) >= round($bedragDoorTeBoeken,2))
                $statusDoorboeken = '*VOLLEDIG';

            else if (round($bedragDoorTeBoeken,2) != round($rdRec->rdBedrag,2))
                $statusDoorboeken = "*DEEL";

            if ($warning)
                $statusDoorboeken = '*PROBLEEM';

        }

        // -------------
        // Update status
        // -------------

        $sqlStat = "Update efin_rd_rekening_details set rdStatusDoorboeken = '$statusDoorboeken' where rdId = $pRekeningDetail";


        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

        return;
    }

    // ========================================================================================
    //  Check Link RD-toewijzing manueel met Inkomende Factuur
    //
    // In:  Rekening Detail Toewijzing
    //      Inkomende Factuur ID
    //
    // Uit: Boodschap (*OK indien ok)
    // ========================================================================================

    static function ChkLnkRDMetIF($pToewijzing, $pInkomendeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if (! $twRec)
            return "Onbekende fout...";

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        if (! $ifRec)
            return "Factuur bestaat niet (meer)";

        if ($ifRec->ifFactuurstatus == '*BETAALD')
            return "Factuur is reeds betaald";

        if ($ifRec->ifControle != '*OK')
            return "Factuur is niet volledig ingegeven (controle <> OK)";

        $openBedrag = round($ifRec->ifBedrag - $ifRec->ifBetaald, 2);
        $openBedrag *= -1;

        if ($openBedrag != $twRec->twBedrag)
            return "Openstaand bedrag ($openBedrag) van de factuur komt niet overeen";


        // -------------
        // Einde functie
        // -------------

        return "*OK";

    }

    // ========================================================================================
    //  Check Link RD-toewijzing manueel met Uitgaande Factuur
    //
    // In:  Rekening Detail Toewijzing
    //      Uitgaande Factuur ID
    //
    // Uit: Boodschap (*OK indien ok)
    // ========================================================================================

    static function ChkLnkRDMetUF($pToewijzing, $pUitgaandeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if (! $twRec)
            return "Onbekende fout...";

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return "Factuur/kostennota bestaat niet (meer)";

        if (substr($ufRec->ufFactuurStatus,0,8) == '*BETAALD')
            return "Factuur/kostennota is reeds betaald";

        if ($ufRec->ufControle != '*OK')
            return "Factuur/kostennota niet volledig ingegeven (controle <> OK)";

        $openBedrag = $ufRec->ufFactuurTotaal - $ufRec->ufBetaalBedragTotaal;

        if ($openBedrag != $twRec->twBedrag)
            return "Openstaand bedrag ($openBedrag) van de factuur/kostennota komt niet overeen";

        // -------------
        // Einde functie
        // -------------

        return "*OK";

    }
    // ========================================================================================
    //  Get URL rekening detail documenten (bv Inkomende Factuur)
    //
    // In:  Rekening Detail
    //
    // Uit: HTML Snippet
    // ========================================================================================

    static function GetRdDocumenten($pRekeningDetail){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $html = "";

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if ($rdRec->rdBijlage){

            $fileArray = my_json_decode($rdRec->rdBijlage);

            $fileName = basename($fileArray[0]["name"]);

            $origName = $fileArray[0]["usrName"];
            $bijlagePath = '/_files/efin/rekening_detail_bijlagen/' . $fileName;

            if ($html)
                $html .= "<br/>";

            $html .= "<a href='$bijlagePath' target='_blank'>Bijlage</a>";


        }

        $ifId = null;
        $ufId = null;

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail";
        $db->Query($sqlStat);

        while ($twRec = $db->Row()){

            // -----------------
            // Uitgaande factuur
            // -----------------

            if ($twRec->twUitgaandeFactuur){

                $ufRec = SSP_db::Get_EFIN_ufRec($twRec->twUitgaandeFactuur);

                if (!$ufRec)
                    continue;

                if ( $ufId == $ufRec->ufId)
                    continue;

                $ufId = $ufRec->ufId;
                $seid = $_SESSION["SEID"];
                if (!$seid)
                    $seid = "NVT";

                $bijlagePath = "/efin_uitgaande_factuur.php?seid=$seid&ufid=$ufId";
                $html .= "<a href='$bijlagePath' target='_blank'>Factuur</a>";
            }

            // -----------------
            // Inkomende factuur
            // -----------------

            if ($twRec->twInkomendeFactuur){

                $ifRec = SSP_db::Get_EFIN_ifRec($twRec->twInkomendeFactuur);

                if ($ifId == $ifRec->ifId)
                    continue;

                $docNaam = 'Factuur';
                if ($ifRec->ifBedrag < 0)
                    $docNaam = 'CN';

                $ifId = $ifRec->ifId;

                if ($ifRec and $ifRec->ifDocument){

                    $fileArray = my_json_decode($ifRec->ifDocument);

                    $fileName = basename($fileArray[0]["name"]);

                    $origName = $fileArray[0]["usrName"];
                    $bijlagePath = '/_files/efin/inkomende_facturen/' . $fileName;

                    if ($html)
                        $html .= "<br/>";

                    $html .= "<a href='$bijlagePath' target='_blank'>$docNaam</a>";

                }

            }


        }

        // -------------
        // Einde functie
        // -------------

        return $html;

    }
    // ========================================================================================
    //  Get Extra Info Rekening-detail (bv factuur-omschrijving)
    //
    // In:  Rekening Detail
    //
    // Uit: Extra info
    // ========================================================================================

    static function GetRdExtraInfo($pRekeningDetail){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $extraInfo = "";


        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail";
        $db->Query($sqlStat);

        while ($twRec = $db->Row()) {

            if ($twRec->twInkomendeFactuur) {

                $ifRec = SSP_db::Get_EFIN_ifRec($twRec->twInkomendeFactuur);

                if ($extraInfo)
                    $extraInfo .= "\n";

                $extraInfo .= $ifRec->ifOmschrijving;

                if ($ifRec->ifReferentieType == '*EBA') {

                    if ($extraInfo)
                        $extraInfo .= "\n";
                    $extraInfo .= "EBA-bestelbon:$ifRec->ifReferentie";

                }

            }


        }

        // -------------
        // Einde functie
        // -------------

        return $extraInfo;

    }

    // ========================================================================================
    //  Fill Extra Info Rekening-detail (bv factuur-omschrijving)
    //
    // In:  Rekening Detail
    //
    // ========================================================================================

    static function FillRdExtraInfo($pRekeningDetail){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $extraInfo = self::GetRdExtraInfo($pRekeningDetail);

        if ($extraInfo) {

            $values = array();
            $where = array();

            $values["rdExtraInfo"] =  MySQL::SQLValue($extraInfo);

            $where["rdId"] =  MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

            $db->UpdateRows("efin_rd_rekening_details", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Fill Extra Info Rekening-detail (voor alle RD-records)
    // ========================================================================================

    static function FillAllRdExtraInfo(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rd_rekening_details";
        $db->Query($sqlStat);

        while ($rdRec = $db->Row()){

            self::FillRdExtraInfo($rdRec->rdId);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Link RD-toewijzing manueel met Inkomende Factuur
    //
    // In:  Rekening Detail Toewijzing ID
    //      Inkomende Factuur ID
    //      User ID
    //
    // ========================================================================================

    static function LnkRDMetIF($pToewijzing, $pInkomendeFactuur, $pUserId) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ----------------
        // Check parameters
        // ----------------

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        if (! $ifRec)
            return;

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if (! $twRec)
            return;

        $rekeningDetail = $twRec->twRekeningDetail;

        // -----------------------
        // Wissen huidig TW-record
        // -----------------------
        
        $sqlStat = "Delete from efin_tw_rekening_detail_toewijzingen where twId = $pToewijzing";
        $db->Query($sqlStat);
        
        // --------------------------
        // Aanmaken Nieuwe TW-records
        // --------------------------
    
        self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $pInkomendeFactuur, $pUserId);

        // ------------------------------------------------
        // Indien gekoppeld met CN -> Toewijzingen aanmaken
        // ------------------------------------------------


        if ($ifRec->ifBedragGekoppeldeFactCn >  0){

            $sqlStat = "Select * from efin_cf_creditnota_factuur where cfFactuur = $pInkomendeFactuur";
            $db->Query($sqlStat);

            while ($cfRec = $db->Row())
                self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $cfRec->cfCreditnota, $pUserId, $cfRec->cfBedrag);

        }

        //if ($ifRec->ifCN1)
        //    self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $ifRec->ifCN1, $pUserId);
        //if ($ifRec->ifCN2)
        //    self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $ifRec->ifCN2, $pUserId);
        //if ($ifRec->ifCN3)
        //    self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $ifRec->ifCN3, $pUserId);
        //if ($ifRec->ifCN4)
        //    self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $ifRec->ifCN4, $pUserId);
        //if ($ifRec->ifCN5)
        //    self::CrtTwOpBasisInkomdeFactuur($rekeningDetail, $ifRec->ifCN5, $pUserId);

        // ----------------------------
        // Aanpassen divcerse statussen
        // ----------------------------

        self::SetRdStatusToewijzen($rekeningDetail);

        self::BookRD($rekeningDetail, $boodschap);
        self::SetRdStatusDoorboeken($rekeningDetail);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Link RD-toewijzing manueel met Uigaande Factuur
    //
    // In:  Rekening Detail Toewijzing ID
    //      Uitgaande Factuur ID
    //      User ID
    //
    // ========================================================================================

    static function LnkRDMetUF($pToewijzing, $pUitgaandeFactuur, $pUserId) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ----------------
        // Check parameters
        // ----------------

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return;

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if (! $twRec)
            return;

        $rekeningDetail = $twRec->twRekeningDetail;

        // -----------------------
        // Wissen huidig TW-record
        // -----------------------

        $sqlStat = "Delete from efin_tw_rekening_detail_toewijzingen where twId = $pToewijzing";
        $db->Query($sqlStat);

        // --------------------------
        // Aanmaken Nieuwe TW-records
        // --------------------------

        self::CrtTwOpBasisUitgaandeFactuur($rekeningDetail, $pUitgaandeFactuur, $pUserId);

        // --------------------------------------------------------------
        // Aanpassen diverse statussen & doorboeken indien nodig/mogelijk
        // --------------------------------------------------------------

        self::SetRdStatusToewijzen($rekeningDetail);

        self::BookRD($rekeningDetail, $boodschap);
        self::SetRdStatusDoorboeken($rekeningDetail);

        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    //  Opvullen inkomende factuur controle
    //
    // In:	Inkomende factuur
    //
    // ========================================================================================

    static function SetIfControle($pInkomendeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $controle = '*OK';

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);
        $lvRec = SSP_db::Get_EFIN_lvRec($ifRec->ifLeverancier);

        $factuurstatus = $ifRec->ifFactuurstatus;
        $factuurControleStatus = $ifRec->ifFactuurControleStatus;

        if ( ( ! $lvRec->lvFactuurControle ) and ($factuurControleStatus == "*TE-CONTROLEREN"))
            $factuurControleStatus = '*OK';

        // -------------------------------------------------
        // Minstelns 1 ventilatie-rekening(en) moet ingevuld
        // -------------------------------------------------

        if (! $ifRec->ifVentilatieRekening1)
            $controle = '*VENTILATIE_GEEN';

        // ------------------------------------------
        // Bedrag ventilatie-rekening(en) moet kloppen
        // ------------------------------------------

        $bedragVentilatie = 0;

        if ($ifRec->ifVentilatieRekening1 and $ifRec->ifVentilatieBedrag1)
            $bedragVentilatie += $ifRec->ifVentilatieBedrag1;
        if ($ifRec->ifVentilatieRekening2 and $ifRec->ifVentilatieBedrag2)
            $bedragVentilatie += $ifRec->ifVentilatieBedrag2;
        if ($ifRec->ifVentilatieRekening3 and $ifRec->ifVentilatieBedrag3)
            $bedragVentilatie += $ifRec->ifVentilatieBedrag3;

        $bedragFactuur = $ifRec->ifBedrag;

        $verschil = round($bedragVentilatie - $bedragFactuur, 2);

        if ($verschil)
            $controle = "*VENTILATIE_VERSCHIL";

        // --------------------------------
        // Aanvullen bepaalde andere velden
        // --------------------------------

        $betaald = round($ifRec->ifBetaalBedrag1,2) + round($ifRec->ifBetaalBedrag2,2) + round($ifRec->ifBetaalBedrag3,2);

        if ($ifRec->ifVoorschot)
            $betaald += $ifRec->ifVoorschot;

        // ----------------------------
        // Regisratie Link CN / Factuur
        // ----------------------------

        $bedragGekoppeldeFactCn = 0;
        $betaalMededelingVrij = $ifRec->ifBetaalMededelingVrij;

        if ($betaalMededelingVrij) {

            $pos = strpos($betaalMededelingVrij, '+ CN');

            if ($pos > 2){

                $betaalMededelingVrij = substr($betaalMededelingVrij, 0, $pos - 1);

            }

        }



        if ($ifRec->ifBedrag < 0) {

            $sqlStat = "Select sum(cfBedrag) as bedrag from efin_cf_creditnota_factuur where cfCreditnota = $pInkomendeFactuur";
            $db->Query($sqlStat);

            if ($cfRec = $db->Row())
                $bedragGekoppeldeFactCn = ($cfRec->bedrag) * -1;
        }

        if ($ifRec->ifBedrag >= 0) {

            $sqlStat = "Select * from efin_cf_creditnota_factuur where cfFactuur = $pInkomendeFactuur";
            $db->Query($sqlStat);

            while ($cfRec = $db->Row()){

                $bedragGekoppeldeFactCn += $cfRec->cfBedrag;

                $cnRec = SSP_db::Get_EFIN_ifRec($cfRec->cfCreditnota);
                $creditnota = $cnRec->ifFactuurnummer;

                if ($betaalMededelingVrij)
                    $betaalMededelingVrij = "$betaalMededelingVrij + CN $creditnota";

            }


        }
        $betaald += round($bedragGekoppeldeFactCn,2);

        // --------------------------------------
        // Indien gekoppelde CN -> bedrag ophalen
        // --------------------------------------

        $bedragFactuur = round($ifRec->ifBedrag,2);

        if (round(abs($betaald),2) >= round(abs($bedragFactuur),2))
            $factuurstatus = '*BETAALD';

        elseif ($bedragFactuur <  0)
            $factuurstatus = '*TEONTVANGEN';

        else
            $factuurstatus = '*OPEN';

        if (! $ifRec->ifBetaalMededelingGM and ! $ifRec->ifBetaalMededelingVrij) {

            $betaalMededelingVrij = 'Factuur ' . $ifRec->ifFactuurnummer;

            if ($ifRec->ifReferentie)
                $betaalMededelingVrij .= ' / Onze Ref: ' . $ifRec->ifReferentie;

        }

        // ------------------------------------------------
        // EBA Bestelbon -> Status mag niet '*CONTROLE' zijn
        // ------------------------------------------------

        if ($ifRec->ifReferentieType == '*EBA'){

            $bestelbon = $ifRec->ifReferentie;

            $bhRec = SSP_db::Get_EBA_bhRec($bestelbon);

            if ($bhRec){

                if (($bhRec->bhBetaalStatus == '*CONTROLE') or ($bhRec->bhBetaalStatus == '*HOLD')){

                    $controle = '*BESTELBON_CONTROLE';
                    $factuurControleStatus = "*TE-CONTROLEREN";

                } else {
                    $factuurControleStatus = "*OK";
                }
            }

        }

        // Factuur status ifv factuur-controle
        if (($factuurstatus == '*OPEN' or $factuurstatus == '*TEONTVANGEN'  or !$factuurstatus) and ($factuurControleStatus == '*TE-CONTROLEREN'))
            $factuurstatus = '*WACHTEN';

        if (($factuurstatus == '*WACHTEN') and ($factuurControleStatus == '*OK'))
            $factuurstatus = '*OPEN';

        if (($factuurstatus == '*WACHTEN' or $factuurstatus == '*OPEN' ) and ($factuurControleStatus == '*NOK'))
            $factuurstatus = '*CONTROLE-NOK';

        if (($factuurstatus == '*OPEN') and ($ifRec->ifBetaalwijze == "*BANKKAART"))
            $factuurstatus = '*BANKKAART';

        if (($factuurstatus == '*OPEN') and ($ifRec->ifBetaalwijze == "*CASH"))
            $factuurstatus = '*CASH';

        if (($factuurstatus == '*OPEN') and ($ifRec->ifBetaalwijze == "*DOMICIL"))
            $factuurstatus = '*DOMICIL';

        if (($factuurstatus == '*OPEN') and ($ifRec->ifBetaalwijze == "*OVERSCHRIJVING_DATUM"))
            $factuurstatus = '*MEMODATUM';

        if (($factuurstatus == '*OPEN') and ($ifRec->ifBetaalvoorstelBetaald > 0))
            $factuurstatus = '*BETALING_ING';

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $values["ifControle"] =MySQL::SQLValue($controle);
        $values["ifBetaald"] =MySQL::SQLValue($betaald, MySQL::SQLVALUE_NUMBER);
        $values["ifBedragGekoppeldeFactCn"] =MySQL::SQLValue($bedragGekoppeldeFactCn, MySQL::SQLVALUE_NUMBER);

        if ($factuurstatus)
            $values["ifFactuurstatus"] =MySQL::SQLValue($factuurstatus);

        if ($betaalMededelingVrij)
            $values["ifBetaalMededelingVrij"] =MySQL::SQLValue($betaalMededelingVrij);

        if ($factuurControleStatus)
            $values["ifFactuurControleStatus"] =MySQL::SQLValue($factuurControleStatus);

        $where["ifId"] =  MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_if_inkomende_facturen", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Link Inkomende CN met Inkomende Factuur
    //
    // In:	Inkomende CN ID
    //      Inkomende Factuur ID
    //
    // ========================================================================================

    static function LinkInkomendeCNmetInkomendeFactuur($pInkomendeCN, $pInkomendeFactuur) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $values = array();
        $where = array();

        $values["ifGekoppeldMetFactuur"] =  MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

        $where["ifId"] =  MySQL::SQLValue($pInkomendeCN, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_if_inkomende_facturen", $values, $where);

    }

    // ========================================================================================
    //  Update Inkomende factuur
    //
    // In:	Inkomende factuur ID
    //
    // ========================================================================================

    static function UpdIF($pInkomendeFactuur) {

        // -------------------
        // Andere aanvullingen
        // -------------------

        self::SetIfVervaldatum($pInkomendeFactuur);
        self::FillIfVentilatie($pInkomendeFactuur);

        self::BookFactuurNaarEBA($pInkomendeFactuur);

        self::SetIfControle($pInkomendeFactuur);

    }

    // ========================================================================================
    //  Sey inkomende factuur vervaldatum
    //
    // In:	Inkomende factuur
    //
    // ========================================================================================

    static function SetIfVervaldatum($pInkomendeFactuur) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        // Reeds bepaald...
        if ($ifRec->ifVervaldatum)
            return;


        // Voorlopig steeds + 1 maand

        $sqlStat = "Update efin_if_inkomende_facturen set ifVervaldatum = DATE_ADD(ifFactuurdatum, INTERVAL 1 MONTH) where ifId = $pInkomendeFactuur";
        $db->Query($sqlStat);


    }


    // ========================================================================================
    //  Doorboeken rekening-detail
    //
    // In:	Rekening Detail
    // Out: Boodschap
    //
    // Return: Successfull?
    //
    // ========================================================================================

    static function BookRD($pRekeningDetail, &$pBoodschap){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        $pBoodschap = 'Niets door te boeken';

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twDoorgeboekt <> 1";
        $db->Query($sqlStat);

        while ($twRec = $db->Row()){

            $ventilatieRekening = $twRec->twVentilatieRekening;

            $vrRec = SSP_db::Get_EFIN_vrRec($ventilatieRekening);

            // ---------------------------------
            // Doorboeken naar inkomende factuur
            // ---------------------------------

            if ($twRec->twInkomendeFactuur){

                $inkomendeFactuur = $twRec->twInkomendeFactuur;
                $betaaldatum = $rdRec->rdDatum;

                $bedrag = $twRec->twBedrag * -1;

                $rekening = $rdRec->rdRekening;

                $returnDoorboeken =  self::BookBetalingIF($inkomendeFactuur, $bedrag,$rekening,$betaaldatum);


            }
            // ---------------------------------
            // Doorboeken naar uitgaande factuur
            // ---------------------------------

            if ($twRec->twUitgaandeFactuur){

                $uitgaandeFactuur = $twRec->twUitgaandeFactuur;
                $betaaldatum = $rdRec->rdDatum;

                $bedrag = $twRec->twBedrag;

                $rekening = $rdRec->rdRekening;

                $returnDoorboeken =  self::BookBetalingUF($uitgaandeFactuur, $bedrag,$rekening,$betaaldatum);

            }

            // --------------------------------------------------------
            // Doorboeken op basis specifiek ventilatie-rekening-script
            // --------------------------------------------------------

            if (($vrRec->vrDoorboeken == 1) and ($vrRec->vrScriptDoorboeken)){

                $rdId = $pRekeningDetail;
                $vrId = $vrRec->vrId;
                $returnDoorboeken = "";

                eval($vrRec->vrScriptDoorboeken);
            }

            // ----------
            // Set status
            // -----------

            $pBoodschap = $returnDoorboeken;

            if ($returnDoorboeken == '*OK') {

                $twId = $twRec->twId;

                $sqlStat = "Update efin_tw_rekening_detail_toewijzingen set twDoorgeboekt = 1, twDoorboekDatum = now(), twDoorboekBoodschap = '*OK'  where twId = $twId";
                $db2->Query($sqlStat);
            }

            else {

                $twId = $twRec->twId;

                $sqlStat = "Update efin_tw_rekening_detail_toewijzingen set twDoorgeboekt = 0, twDoorboekDatum = now(), twDoorboekBoodschap = '$returnDoorboeken' where twId = $twId";
                $db2->Query($sqlStat);


            }

        }

        // -------------
        // Einde functie
        // -------------

        return ($pBoodschap == '*OK');

    }

    // ========================================================================================
    //  Uitvoeren "mapping-script" ventilatie rekening
    //
    // In:	Ventilatie-rekening
    //
    // ========================================================================================

    static function RunVrMapScript($pVentilatieRekening){

        include_once(SX::GetClassPath("_db.class"));

        $vrRec = SSP_db::Get_EFIN_vrRec($pVentilatieRekening);

        if ($vrRec->vrScriptMapping) {

            $vrId = $pVentilatieRekening;

            eval($vrRec->vrScriptMapping);

        }


    }

    // ========================================================================================
    //  Uitvoeren "mapping-script" ventilatie rekeningen
    //
    // ========================================================================================

    static function AutoRunVrMapScripts(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_vr_ventilatie_rekeningen where vrRecStatus = 'A' and vrAutoRunScriptMapping = 1 ";

        $db->Query($sqlStat);

        while ($vrRec = $db->Row())
            self::RunVrMapScript($vrRec->vrId);

    }


    // ========================================================================================
    //  Aftesten Referentie Inkomende Factuur
    //
    //
    // In:	Referentie-type
    //      Referentie
    //      Factuurnummer
    //
    // Return: *OK of Foutboodschap
    //
    // ========================================================================================

    static function ChkIfReferentie($pReferentieType, $pReferentie, $pFactuurnummer)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        if ($pReferentieType == '*ANDERE')
            return "*OK";

        if ($pReferentieType == '*EBA') {

            $bhRec = SSP_db::Get_EBA_bhRec($pReferentie);

            if (!$bhRec)
                return "Geen EBA bestelbon met nummer $pReferentie";

            if (($bhRec->bhFactuurNr > " ") and ($pFactuurnummer > " ") and (trim($bhRec->bhFactuurNr) != trim($pFactuurnummer)))
                return "Bestelbon gekoppeld aan andere factuurnummer";

            return '*OK';

        }
    }
    // ========================================================================================
    //  Opvullen id-code van de Factuurtype (ftREc)
    //
    // In:	Factuur Type ID
    //
    // ========================================================================================

    static function FillFtIdCode($pFactuurType){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $idCode = str_pad($pFactuurType, 5, '0', STR_PAD_LEFT);

        $values = array();
        $where = array();

        $values["ftIdCode"] =MySQL::SQLValue($idCode);

        $where["ftId"] =  MySQL::SQLValue($pFactuurType, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_ft_factuur_type", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Opvullen id-code van de sponsorcontract-tegenprestatie (stRec)
    //
    // In:	Sponsorcontract Tegenprestatie
    //
    // ========================================================================================

    static function FillStIdCode($pSponsorcontractTegenprestatie){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $idCode = str_pad($pSponsorcontractTegenprestatie, 5, '0', STR_PAD_LEFT);

        $values = array();
        $where = array();

        $values["stIdCode"] =MySQL::SQLValue($idCode);

        $where["stId"] =  MySQL::SQLValue($pSponsorcontractTegenprestatie, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_st_sponsorcontract_tegenprestaties", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Opvullen id-code van de ventielatie-categorie (vcRec)
    //
    // In:	Ventilatie Categorie
    //
    // ========================================================================================

    static function FillVcIdCode($pVentilatieCategorie){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $idCode = str_pad($pVentilatieCategorie, 5, '0', STR_PAD_LEFT);

        $values = array();
        $where = array();

        $values["vcIdCode"] =MySQL::SQLValue($idCode);

        $where["vcId"] =  MySQL::SQLValue($pVentilatieCategorie, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_vc_ventilatie_categorie", $values, $where);

    }

    // ========================================================================================
    //  Opvullen id-code van de ventilatierekening
    //
    // In:	Ventilatie Rekening
    //
    // ========================================================================================

    static function FillVrIdCode($pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $vrRec = SSP_db::Get_EFIN_vrRec($pVentilatieRekening);

        if (! $vrRec)
            return;

        $idCode = $vrRec->vrId;
        $idCode = str_pad($idCode, 5, '0', STR_PAD_LEFT);

        $values = array();
        $where = array();

        $values["vrIdCode"] =MySQL::SQLValue($idCode);

        $where["vrId"] =  MySQL::SQLValue($pVentilatieRekening, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_vr_ventilatie_rekeningen", $values, $where);


    }

    // ========================================================================================
    //  Opvullen ventilatie-rekenening inkomende factuur
    //
    //
    // In:	Inkomende factuur
    //
    // ========================================================================================

    static function FillIfVentilatie($pInkomendeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        if (!$ifRec)
            return;

        // -------------------------------
        // Reeds ingevuld -> Einde functie
        // -------------------------------

        if ($ifRec->ifVentilatieBedrag1 and $ifRec->ifVentilatieRekening1)
            return;

        // -----------------------------------------------------------
        // Opvullen ventilatie-rekening/bedrag op basis EBA referentie
        // -----------------------------------------------------------

        $referentieType = $ifRec->ifReferentieType;
        $referentie = $ifRec->ifReferentie;

        $ventilatieRekening = null;
        $ventilatieBedrag = $ifRec->ifBedrag;

        if ($referentieType == '*EBA' and $referentie){

            $bhRec = SSP_db::Get_EBA_bhRec($referentie);

            if ($bhRec)
                $ventilatieRekening = $bhRec->bhVentilatieRekening;

        }

        // ---------------------------------
        // Opvullen op basis van Leverancier
        // ---------------------------------

        if (! $ventilatieRekening){

            $leverancier = $ifRec->ifLeverancier;

            $lvRec = SSP_db::Get_EFIN_lvRec($leverancier);

            if ($lvRec and ($lvRec->lvVentilatieRekening)){

                $ventilatieRekening = $lvRec->lvVentilatieRekening;
            }
        }

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $values["ifVentilatieBedrag1"] = MySQL::SQLValue($ventilatieBedrag, MySQL::SQLVALUE_NUMBER);
        $values["ifVentilatieRekening1"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

        $where["ifId"] =  MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_if_inkomende_facturen", $values, $where);

        // -------------
        // Einde functie
        // -------------

        return;

    }

    // ========================================================================================
    //  Aanmaken betaalvoorstel-detail
    //
    // In:	Betaalvoorstel
    //
    // ========================================================================================

    static function CrtBvDetail($pBetaalvoorstel) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        $bhRec = SSP_db::Get_EFIN_bhRec($pBetaalvoorstel);

        if (! $bhRec)
            return;

        $userId = $bhRec->bhUserCreatie;


        $sqlStat = "Select * from efin_if_inkomende_facturen where ifFactuurstatus = '*OPEN' and ifControle = '*OK'";

        $db->Query($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        $values["kaDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        while ($ifRec = $db->Row()){

            // ---------------
            // Extra selecties
            // ---------------

            if ($bhRec->bhLeverancier and ($bhRec->bhLeverancier != $ifRec->ifLeverancier))
                continue;
            if ($bhRec->bhBetaalVia and ($bhRec->bhBetaalVia != $ifRec->ifBetaalVia))
                continue;
            if ($bhRec->bhVervaldatum and ($bhRec->bhVervaldatum < $ifRec->ifVervaldatum))
                continue;

            $teBetalen = $ifRec->ifBedrag - $ifRec->ifBetaald;

            if ($teBetalen > 0) {

                $values = array();

                $values["bdVoorstel"] = MySQL::SQLValue($pBetaalvoorstel, MySQL::SQLVALUE_NUMBER);
                $values["bdFactuur"] = MySQL::SQLValue($ifRec->ifId);

                $values["bdTeBetalen"] = MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);
                $values["bdStatus"] = MySQL::SQLValue('*OPEN');

                $values["bdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["bdUserCreatie"] = MySQL::SQLValue($userId);
                $values["bdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["bdUserUpdate"] = MySQL::SQLValue($userId);

                $id = $db2->InsertRow("efin_bd_betaalvoorstel_detail", $values);

            }

        }

    }

    // ========================================================================================
    //  Verwerken betaalvoorstel-detail
    //
    // In:	Betaalvoorstel-detail
    //
    // ========================================================================================

    static function HdlBetaalvoorstelDetail($pBetaalvoorstelDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $bdRec = SSP_db::Get_EFIN_bdRec($pBetaalvoorstelDetail);

        if (! $bdRec)
            return;

        if ($bdRec->bdStatus != '*OPEN')
            return;

        $bhRec = SSP_db::Get_EFIN_bhRec($bdRec->bdVoorstel);

        if (! $bhRec)
            return;

        $betaalVoorstel = $bdRec->bdVoorstel;
        $inkomendeFactuur = $bdRec->bdFactuur;
        $betaald = $bdRec->bdTeBetalen;
        $betaalDatum = $bhRec->bhDatum;

        // ------------------------
        // Update Inkomende factuur
        // ------------------------

        $values = array();
        $where = array();

        $values['ifBetaalvoorstelBetaald'] =  MySQL::SQLValue($betaald, MySQL::SQLVALUE_NUMBER);
        $values['ifFactuurstatus'] =  MySQL::SQLValue('*BETALING_ING');

        $where["ifId"] =  MySQL::SQLValue($inkomendeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_if_inkomende_facturen", $values, $where);

        // ----------------------------
        // Update Betaalvoorstel-detail
        // ----------------------------

        $values = array();
        $where = array();

        $values["bdBetaald"] =  MySQL::SQLValue($betaald, MySQL::SQLVALUE_NUMBER);
        $values["bdStatus"] =  MySQL::SQLValue('*BETAALD');

        $where["bdId"] =  MySQL::SQLValue($pBetaalvoorstelDetail, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_bd_betaalvoorstel_detail", $values, $where);

        // -------------
        // Einde functie
        // -------------

        return;

    }

    // ========================================================================================
    //  Zet betaalvoorstel status
    //
    // In:	Betaalvoorstel
    //
    // ========================================================================================

    static function SetBetaalvoorstelStatus($pBetaalvoorstel) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from efin_bd_betaalvoorstel_detail where bdVoorstel = $pBetaalvoorstel";
        $db->Query($sqlStat);

        $bhStatus = '*OPEN';

        while ($bdRec = $db->Row()){

            $bdStatus = $bdRec->bdStatus;

            if ($bdStatus == '*PARTIEEL_BETAALD') {
                $bhStatus = '*PARTIEEL_BETAALD';
                break;
            }

            if ($bdStatus == '*BETAALD' and $bhStatus == '*OPEN')
                $bhStatus = '*BETAALD';

            if ($bdStatus == '*OPEN' and $bhStatus == '*BETAALD')
                $bhStatus = '*PARTIEEL_BETAALD';

        }


        // ----------------------------
        // Update betaalvoorstel-status
        // ----------------------------

        $values = array();
        $where = array();

        $values["bhStatus"] =  MySQL::SQLValue($bhStatus);

        $where["bhId"] =  MySQL::SQLValue($pBetaalvoorstel, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_bh_betaalvoorstel_header", $values, $where);

    }

    // ========================================================================================
    //  Boek betaling Inkomende Factuur
    //
    // In:	Inkomende factuur
    //      Bedrag
    //      Rekening
    //      BEtaaldatum (MYSQL formaat)
    //
    // Return: Boodschap (*OK)
    //
    // ========================================================================================

    static function BookBetalingIF($pInkomendeFactuur, $pBedrag, $pRekening, $pBetaaldatum){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        if (! $ifRec)
            return "Inkomende factuur niet gevonden";

        // --------------------------
        // Niet voor "gekoppelde CN"
        // --------------------------

        if (($ifRec->ifBedrag < 0) and (abs($ifRec->ifBedragGekoppeldeFactCn) > 0))
            return '*OK';

        $bedrag = $pBedrag;

        // Bedrag niet hoger dan openstaand
        if ($ifRec->ifBedrag > 0) {
            if (($ifRec->ifBetaald + $bedrag) > $ifRec->ifBedrag)
                $bedrag = $ifRec->ifBedrag - $ifRec->ifBetaald;
        }

        $teBetalen = $ifRec->ifBedrag - $ifRec->ifBetaald;

        if ($teBetalen <= 0 and $ifRec->ifBedrag > 0)
            return "Inkomende factuur reeds volledig betaald";

        if ($teBetalen >= 0 and $ifRec->ifBedrag < 0)
            return "Inkomende factuur reeds volledig betaald";

        $ifBetaalBedrag1 = $ifRec->ifBetaalBedrag1;
        $ifBetaalDatum1 = $ifRec->ifBetaalDatum1;
        $ifViaRekening1 = $ifRec->ifViaRekening1;

        $ifBetaalBedrag2 = $ifRec->ifBetaalBedrag2;
        $ifBetaalDatum2 = $ifRec->ifBetaalDatum2;
        $ifViaRekening2 = $ifRec->ifViaRekening2;

        $ifBetaalBedrag3 = $ifRec->ifBetaalBedrag3;
        $ifBetaalDatum3 = $ifRec->ifBetaalDatum3;
        $ifViaRekening3 = $ifRec->ifViaRekening3;

        if ($ifBetaalBedrag1 and $ifBetaalBedrag2 and $ifBetaalBedrag3)
            return "Bedrag kan niet geboekt worden";

        if (! $ifBetaalBedrag1) {
            $ifBetaalBedrag1 = $bedrag;
            $ifBetaalDatum1 = $pBetaaldatum;
            $ifViaRekening1 = $pRekening;
        }

        elseif (! $ifBetaalBedrag2) {
            $ifBetaalBedrag2 = $bedrag;
            $ifBetaalDatum2 = $pBetaaldatum;
            $ifViaRekening2 = $pRekening;
        }

        elseif (! $ifBetaalBedrag3) {
            $ifBetaalBedrag3 = $bedrag;
            $ifBetaalDatum3 = $pBetaaldatum;
            $ifViaRekening3 = $pRekening;
        }

        // ------------------------
        // Update inkomende factuur
        // ------------------------

        $values = array();
        $where = array();

        $values["ifBetaalBedrag1"] = MySQL::SQLValue($ifBetaalBedrag1, MySQL::SQLVALUE_NUMBER);
        $values["ifBetaalDatum1"] = MySQL::SQLValue($ifBetaalDatum1, MySQL::SQLVALUE_DATE);
        $values["ifViaRekening1"] = MySQL::SQLValue($ifViaRekening1, MySQL::SQLVALUE_NUMBER);

        $values["ifBetaalBedrag2"] = MySQL::SQLValue($ifBetaalBedrag2, MySQL::SQLVALUE_NUMBER);
        $values["ifBetaalDatum2"] = MySQL::SQLValue($ifBetaalDatum2, MySQL::SQLVALUE_DATE);
        $values["ifViaRekening2"] = MySQL::SQLValue($ifViaRekening2, MySQL::SQLVALUE_NUMBER);

        $values["ifBetaalBedrag3"] = MySQL::SQLValue($ifBetaalBedrag3, MySQL::SQLVALUE_NUMBER);
        $values["ifBetaalDatum3"] = MySQL::SQLValue($ifBetaalDatum3, MySQL::SQLVALUE_DATE);
        $values["ifViaRekening3"] = MySQL::SQLValue($ifViaRekening3, MySQL::SQLVALUE_NUMBER);

        $where["ifId"] =  MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_if_inkomende_facturen", $values, $where);

        self::SetIfControle($pInkomendeFactuur);

        // -------------
        // EBA bestelbon
        // -------------

        if ($ifRec->ifReferentie and ($ifRec->ifReferentieType == '*EBA')){

            $bhRec = SSP_db::Get_EBA_bhRec($ifRec->ifReferentie);

            if ($bhRec){

                $bestelbon = $bhRec->bhId;
                $factuurnummer = $ifRec->ifFactuurnummer;
                $factuurBedrag = $ifRec->ifBedrag;
                $factuurdatum = $ifRec->ifFactuurdatum;

                $values = array();
                $where = array();

                $values["bhBetaalStatus"] =  MySQL::SQLValue('*BETAALD');
                $values["bhBetaalDatum"] = MySQL::SQLValue($pBetaaldatum, MySQL::SQLVALUE_DATE);
                $values["bhFactuurNr"] = MySQL::SQLValue($factuurnummer);
                $values["bhFactuurDatum"] = MySQL::SQLValue($factuurdatum, MySQL::SQLVALUE_DATE);
                $values["bhFactuurBedrag"] = MySQL::SQLValue($factuurBedrag, MySQL::SQLVALUE_NUMBER);
                $values["bhFactuurEFIN"] = MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

                $where["bhId"] =  MySQL::SQLValue($bestelbon, MySQL::SQLVALUE_NUMBER);

                $db->UpdateRows("eba_bh_bestel_headers", $values, $where);

            }

       }

       // -------------
        // Einde functie
        // -------------

        return "*OK";

    }
    // ========================================================================================
    //  Boek betaling Uitgaande Factuur
    //
    // In:	Uitgaande factuur ID
    //      Bedrag
    //      Rekening ID
    //      Betaaldatum (MYSQL formaat)
    //
    // Return: Boodschap (*OK)
    //
    // ========================================================================================

    static function BookBetalingUF($pUitgaandeFactuur, $pBedrag, $pRekening, $pBetaaldatum){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return "Uitgaande factuur/kostennota niet gevonden";

        $teBetalen = $ufRec->ufFactuurTotaal - $ufRec->ufBetaalBedrag1 - $ufRec->ufBetaalBedrag2 - $ufRec->ufBetaalBedrag3 - $ufRec->ufBetaalBedragFC;
        $voorschot = $ufRec->ufReedsBetaald;

        if (($teBetalen + $voorschot) <= 0 and $pBedrag > 0)
            return "Inkomende/kotennota factuur reeds volledig betaald";

        $ufBetaalBedrag1 = $ufRec->ufBetaalBedrag1;
        $ufBetaalDatum1 = $ufRec->ufBetaalDatum1;
        $ufViaRekening1 = $ufRec->ufViaRekening1;

        $ufBetaalBedrag2 = $ufRec->ufBetaalBedrag2;
        $ufBetaalDatum2 = $ufRec->ufBetaalDatum2;
        $ufViaRekening2 = $ufRec->ufViaRekening2;

        $ufBetaalBedrag3 = $ufRec->ufBetaalBedrag3;
        $ufBetaalDatum3 = $ufRec->ufBetaalDatum3;
        $ufViaRekening3 = $ufRec->ufViaRekening3;

        if (! $ufBetaalBedrag1) {
            $ufBetaalBedrag1 = $pBedrag;
            $ufBetaalDatum1 = $pBetaaldatum;
            $ufViaRekening1 = $pRekening;
        }

        elseif ($ufBetaalDatum1 == $pBetaaldatum ) {
            $ufBetaalBedrag1 += $pBedrag;
        }

        elseif (! $ufBetaalBedrag2) {
            $ufBetaalBedrag2 = $pBedrag;
            $ufBetaalDatum2 = $pBetaaldatum;
            $ufViaRekening2 = $pRekening;
        }

        elseif ($ufBetaalDatum2 == $pBetaaldatum ) {
            $ufBetaalBedrag2 += $pBedrag;
        }

        elseif (! $ufBetaalBedrag3) {
            $ufBetaalBedrag3 = $pBedrag;
            $ufBetaalDatum3 = $pBetaaldatum;
            $ufViaRekening3 = $pRekening;
        }

        elseif ($ufBetaalDatum3 == $pBetaaldatum ) {
            $ufBetaalBedrag3 += $pBedrag;
        }
        else
            return "Bedrag kan niet geboekt worden";

        // ------------------------
        // Update inkomende factuur
        // ------------------------

        $values = array();
        $where = array();

        $values["ufBetaalBedrag1"] = MySQL::SQLValue($ufBetaalBedrag1, MySQL::SQLVALUE_NUMBER);
        $values["ufBetaalDatum1"] = MySQL::SQLValue($ufBetaalDatum1, MySQL::SQLVALUE_DATE);
        $values["ufViaRekening1"] = MySQL::SQLValue($ufViaRekening1, MySQL::SQLVALUE_NUMBER);

        $values["ufBetaalBedrag2"] = MySQL::SQLValue($ufBetaalBedrag2, MySQL::SQLVALUE_NUMBER);
        $values["ufBetaalDatum2"] = MySQL::SQLValue($ufBetaalDatum2, MySQL::SQLVALUE_DATE);
        $values["ufViaRekening2"] = MySQL::SQLValue($ufViaRekening2, MySQL::SQLVALUE_NUMBER);

        $values["ufBetaalBedrag3"] = MySQL::SQLValue($ufBetaalBedrag3, MySQL::SQLVALUE_NUMBER);
        $values["ufBetaalDatum3"] = MySQL::SQLValue($ufBetaalDatum3, MySQL::SQLVALUE_DATE);
        $values["ufViaRekening3"] = MySQL::SQLValue($ufViaRekening3, MySQL::SQLVALUE_NUMBER);

        $where["ufId"] =  MySQL::SQLValue($pUitgaandeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_uf_uitgaande_facturen", $values, $where);

        // -------------------------
        // Set status, controle, etc
        // -------------------------

        self::UpdUitgaandeFactuur($pUitgaandeFactuur, '*UPDATE');

        // -------------
        // Einde functie
        // -------------

        return "*OK";

    }
     // ========================================================================================
    // Bijwerken analytische Rekening Saldi voor diverse periodes
    //
    // In:	Analytische Rekening
    //
    // ========================================================================================

    static function UpdAnalytischeSaldi($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from efin_pe_periodes where peRecStatus = 'A' order by peCode desc ";

        $db->Query($sqlStat);

        while ($peRec = $db->Row()){

            $periode = $peRec->peCode;
            $datumVan = $peRec->peDatumVan;
            $datumTot = $peRec->peDatumTot;
            $bedragMin = 0;
            $bedragPlus = 0;
            $bedragMinSpecifiek = 0;
            $bedragPlusSpecifiek = 0;

            $values = array();

            $values["asAnalytischeRekening"] = MySQL::SQLValue($pAnalytischeRekening, MySQL::SQLVALUE_NUMBER);
            $values["asPeriode"] = MySQL::SQLValue($periode);
            $values["asBedragMin"] = MySQL::SQLValue($bedragMin, MySQL::SQLVALUE_NUMBER);
            $values["asBedragPlus"] = MySQL::SQLValue($bedragPlus, MySQL::SQLVALUE_NUMBER);
            $values["asBedragMinSpecifiek"] = MySQL::SQLValue($bedragMinSpecifiek, MySQL::SQLVALUE_NUMBER);
            $values["asBedragPlusSpecifiek"] = MySQL::SQLValue($bedragPlusSpecifiek, MySQL::SQLVALUE_NUMBER);

            $id = $db2->InsertRow("efin_as_analytische_rekening_saldi", $values);

            $sqlStat = "SELECT
	twRekeningDetail,
	vdAnalytischeRekening,
	arNaam as AnalytischeRekening,
	twBedrag,
	round(twBedrag * (vdPercentage / 100),2) as ToegewezenBedrag,
	rkNaam as RekeningGeboekt,
	rdDatum as Datum,
    vdLinkType as LinkType
FROM efin_tw_rekening_detail_toewijzingen
Left outer join efin_vr_ventilatie_rekeningen on vrId = twVentilatieRekening
left outer join efin_vd_ventilatie_detail on vdVentilatieRekening = twVentilatieRekening
left outer join efin_rd_rekening_details on rdId = twRekeningDetail
left outer join efin_rk_rekeningen on rkId = rdRekening
left outer join efin_ar_analytische_rekeningen on arId = vdAnalytischeRekening
where vdAnalytischeRekening = $pAnalytischeRekening and rdDatum >= '$datumVan' and rdDatum <= '$datumTot' ";

            $db2->Query($sqlStat);

            while ($twRec = $db2->Row()){

                if ($twRec->ToegewezenBedrag < 0)
                    $bedragMin += abs($twRec->ToegewezenBedrag);

                if ($twRec->ToegewezenBedrag > 0)
                    $bedragPlus += $twRec->ToegewezenBedrag;

                if ($twRec->LinkType == '*SPECIFIEK'){

                    if ($twRec->ToegewezenBedrag < 0)
                        $bedragMinSpecifiek += abs($twRec->ToegewezenBedrag);

                    if ($twRec->ToegewezenBedrag > 0)
                        $bedragPlusSpecifiek += $twRec->ToegewezenBedrag;

                }

            }

            $values = array();
            $where = array();

            $curDateTime = date('Y-m-d H:i:s');

            $values["asBedragMin"] = MySQL::SQLValue($bedragMin, MySQL::SQLVALUE_NUMBER);
            $values["asBedragPlus"] = MySQL::SQLValue($bedragPlus, MySQL::SQLVALUE_NUMBER);
            $values["asBedragMinSpecifiek"] = MySQL::SQLValue($bedragMinSpecifiek, MySQL::SQLVALUE_NUMBER);
            $values["asBedragPlusSpecifiek"] = MySQL::SQLValue($bedragPlusSpecifiek, MySQL::SQLVALUE_NUMBER);

            $values["asDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $where["asAnalytischeRekening"] = MySQL::SQLValue($pAnalytischeRekening, MySQL::SQLVALUE_NUMBER);
            $where["asPeriode"] = MySQL::SQLValue($periode);

            $db2->UpdateRows("efin_as_analytische_rekening_saldi", $values, $where);


        }

        // --------------------
        // Bewaren HTML in file
        // --------------------

        $saldiHTML = self::GetAnalytischeSaldiHTML($pAnalytischeRekening);

        $values = array();
        $where = array();

        $values["arSaldi"] =  MySQL::SQLValue($saldiHTML);

        $where["arId"] =  MySQL::SQLValue($pAnalytischeRekening, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_ar_analytische_rekeningen", $values, $where);


        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Totale herberekening saldi analytische Rekeningen
    //
    // In:	Niets
    //
    // ========================================================================================

    static function CalcAlleAnalytischeRekeningen() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ar_analytische_rekeningen";
        $db->Query($sqlStat);

        while ($arRec = $db->Row()){

            $analytischeRekening = $arRec->arId;

            self::UpdAnalytischeSaldi($analytischeRekening);
            self::RegAnalytischeVentilatieHTML($analytischeRekening);

        }


    }

    // ========================================================================================
    // Bereken RD bedrag toegewezen
    //
    // In:	Rekening Detail ID
    //
    // ========================================================================================

    static function RegRdBedragToegewezen($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $bedragToegewezen = 0;

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail";
        $db->Query($sqlStat);


        while ($twRec = $db->Row())
            $bedragToegewezen += $twRec->twBedrag;

        $values = array();
        $where = array();

        $values["rdBedragToegewezen"] =  MySQL::SQLValue($bedragToegewezen, MySQL::SQLVALUE_NUMBER);
        $where["rdId"] =  MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_rd_rekening_details", $values, $where);

    }

    // ========================================================================================
    // Bereken RD bedrag toegewezen ALLE RD
    //
    // In:	Geen
    //
    // ========================================================================================

    static function RegRdBedragToegewezenAlle(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rd_rekening_details";
        $db->Query($sqlStat);

        while($rdRec = $db->Row())
                self::RegRdBedragToegewezen($rdRec->rdId);


    }


    // ========================================================================================
    // Herberekening saldi analytische Rekeningen op basis van toewijzing
    //
    // In:	Toewijzing
    //
    // ========================================================================================

    static function UpdAnalytischeSaldiOpBasisToewijzing($pToewijzing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if (! $twRec)
            return;

        $ventilatieRekening = $twRec->twVentilatieRekening;

        self::UpdAnalytischeSaldiOpBasisVentilatieRekening($ventilatieRekening);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Herberekening saldi analytische Rekeningen op basis van ventilatie Rekening
    //
    // In:	Ventilatie rekening
    //
    // ========================================================================================

    static function UpdAnalytischeSaldiOpBasisVentilatieRekening($pVentilatieRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_vd_ventilatie_detail where vdVentilatieRekening = $pVentilatieRekening";
        $db->Query($sqlStat);

        while ($vdRec = $db->Row()){

            $analytischeRekening = $vdRec->vdAnalytischeRekening;

            self::UpdAnalytischeSaldi($analytischeRekening);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Ophalen analytische Rekening Saldi HTML
    //
    // In:	Analytische Rekening
    //
    // ========================================================================================

    static function GetAnalytischeSaldiHTML($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_as_analytische_rekening_saldi where asAnalytischeRekening = $pAnalytischeRekening order by asPeriode desc";
        $db->Query($sqlStat);

        $html = "";

        while ($asRec = $db->Row()){

            $periode = $asRec->asPeriode;

            $bedragMin = $asRec->asBedragMin + 0;
            $bedragPlus = $asRec->asBedragPlus + 0;
            $saldo = $bedragPlus - $bedragMin;

            if ($saldo or $bedragMin or $bedragPlus) {

                $saldo = number_format($saldo, 2, ',', '.');
                $bedragMin = number_format($bedragMin, 2, ',', '.');
                $bedragPlus = number_format($bedragPlus, 2, ',', '.');


                if ($html)
                    $html .= "<br style='clear: both'/>";

                $html .= "<div style='margin-right: 10px; float: left'>$periode:</div><div style='float: left'></div> <b>$saldo</b>&nbsp;&nbsp;(in: $bedragPlus - uit: $bedragMin)</div>";

            }
        }

        // -------------
        // Einde functie
        // -------------

        return $html;


    }
    // ========================================================================================
    // Ophalen analytische Rekening Budgetten HTML
    //
    // In:	Analytische Rekening
    //
    // ========================================================================================

    static function GetAnalytischeBudgettenHTML($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ab_analytische_rekening_budgetten where abAnalytischeRekening = $pAnalytischeRekening order by abPeriode desc";
        $db->Query($sqlStat);

        $html = "";

        while ($abRec = $db->Row()){

            $periode = $abRec->abPeriode;
            $budget = $abRec->abBudget + 0;

            if ($abRec->abLinkType == '*GROEP')
                $groepCode = '&nbsp;<b>[G]</b>';
            else
                $groepCode = "";

            if ($budget) {

                $budget = number_format($budget, 2, ',', '.');
                if ($html)
                    $html .= "<br style='clear: both'/>";

                $html .= "<div style='margin-right: 10px; float: left'>$periode:</div><div style='float: left'></div> <b>$budget</b>$groepCode</div>";

            }
        }

        // -------------
        // Einde functie
        // -------------

        return $html;

    }  
    
    // ========================================================================================
    // Ophalen analytische Rekening Ventilatie HTML
    //
    // In:	Analytische Rekening
    //
    // ========================================================================================

    static function GetAnalytischeVentilatieHTML($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from efin_vd_ventilatie_detail where vdAnalytischeRekening = $pAnalytischeRekening";
        $db->Query($sqlStat);

        $html = "";

        while ($vdRec = $db->Row()){

            $ventilatieRekening = $vdRec->vdVentilatieRekening;

            $vrRec = SSP_db::Get_EFIN_vrRec($ventilatieRekening);

            $vrNaam = $vrRec->vrNaam;

            if ($vdRec->vdLinkType == '*GROEP')
                $vrNaam .= ' <b>[G]</b>';

            $percentage = $vdRec->vdPercentage + 0;

            if ($html)
                $html .= "<br/>";

            if ($percentage != 100)
                $html .= "$vrNaam - $percentage %";
            else
                $html .= $vrNaam;
        }

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ========================================================================================
    // Ophalen Ventilatie Detail HTML
    //
    // In:	Ventilatie Rekening
    //
    // ========================================================================================

    static function GetVentilatieDetailHTML($pVentilatieRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $vrRec = SSP_db::Get_EFIN_vrRec($pVentilatieRekening);

        $sqlStat = "Select * from efin_vd_ventilatie_detail inner join efin_ar_analytische_rekeningen on arId = vdAnalytischeRekening where vdVentilatieRekening = $pVentilatieRekening order by arLevel desc";
        $db->Query($sqlStat);

        $html = "";

        while ($vdRec = $db->Row()){

            $analytischeRekening = $vdRec->vdAnalytischeRekening;

            $arRec = SSP_db::Get_EFIN_arRec($analytischeRekening);

            $arNaam = utf8_encode($arRec->arNaam);
            $percentage = $vdRec->vdPercentage + 0;

            if ($html)
                $html .= "<hr style='clear: both; margin-top: 0px; margin-bottom: 0px'/>";

            if ($vdRec->vdPercentage == 100)
                $html .= $arNaam;
            else
                $html .= "$arNaam - $percentage %";

            if ($vdRec->vdLinkType == '*GROEP')
                $html .= " <b>[G]</b>";

        }

        if ($vrRec->vrNietAnalytisch == 1)
            $html = "NVT";

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ========================================================================================
    // Opslagen analytische Rekening Ventilatie HTML
    //
    // In:	Analytische Rekening
    //
    // ========================================================================================

    static function RegAnalytischeVentilatieHTML($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $html = self::GetAnalytischeVentilatieHTML($pAnalytischeRekening);

        $values = array();
        $where = array();

        $values["arVentilatie"] =  MySQL::SQLValue($html);

        $where["arId"] =  MySQL::SQLValue($pAnalytischeRekening, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_ar_analytische_rekeningen", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Aanmaken "oude toewijzing" record
    //
    // In:	Toewijzing
    //
    // ========================================================================================

    static function CrtOudeToewijzing($pToewijzing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if (! $twRec)
            return;

        $ventilatieRekening = $twRec->twVentilatieRekening;

        $values = array();

        $values["toVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);
        $values["toStatus"] = MySQL::SQLValue('A');

        $id = $db->InsertRow("efin_to_rekening_detail_oude_toewijzingen", $values);

        // -------------
        // Einde functie
        // -------------


    }

    // ========================================================================================
    //  Verwerken alle  "oude toewijzing" record(s)
    // ========================================================================================

    static function HdlOudeToewijzingen() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from efin_to_rekening_detail_oude_toewijzingen where toStatus = 'A'";
        $db->Query($sqlStat);

        while ($toRec = $db->Row()){

            $venilatieRekening = $toRec->toVentilatieRekening;
            self::UpdAnalytischeSaldiOpBasisVentilatieRekening($venilatieRekening);

            $values = array();
            $where = array();

            $values["toStatus"] =  MySQL::SQLValue('H');

            $where["toId"] =  MySQL::SQLValue($toRec->toId, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("efin_to_rekening_detail_oude_toewijzingen", $values, $where);


        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Delete Rekening toegestaan?
    //
    // In:	Rekening
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteRekening($pRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ------------------
        // Niet indien detail
        // ------------------

        $sqlStat = "Select count(*) as aantal from efin_rd_rekening_details where rdRekening = $pRekening";
        $db->Query($sqlStat);

        if ($rkRec = $db->Row())
            if ($rkRec->aantal)
                return "Wissen niet mogelijk: er is detail";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Delete Rekening-detail toegestaan?
    //
    // In:	Rekening-detail
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteRekeningDetail($pRekeningDetail){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ----------------------
        // Niet indien toewijzing
        // ----------------------

        $sqlStat = "Select count(*) as aantal from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twDoorgeboekt = 1";
        $db->Query($sqlStat);

        if ($twRec = $db->Row())
            if ($twRec->aantal)
                return "Wissen niet mogelijk: er is een doorgeboekte toewijzing gekoppeld";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Delete Rekening-detail?
    //
    // In:	Rekening-detail
    //
    // ========================================================================================

    static function DelRekeningDetail($pRekeningDetail){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Delete From efin_rd_rekening_details where rdId = $pRekeningDetail";
        $db->Query($sqlStat);

        $sqlStat = "Delete From efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail";
        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Delete Tegenprestatiue toegestaan?
    //
    // In:	Tegenprestatie ID
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteTegenprestatie($pRegenprestatie){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -----------------------
        // Ophalen nodige gegevens
        // -----------------------

        $tpRec = SSP_db::Get_EFIN_tpRec($pRegenprestatie);

        if (! $tpRec)
            return "Onverwachte fout...";

        $naam = $tpRec->tpNaam;

        // -------------------------------------
        // Niet indien gekoppeld aan een dossier
        // -------------------------------------

        $sqlStat = "Select count(*) as aantal from efin_st_sponsor_tegenprestaties where stTegenprestatie = $pRegenprestatie";
        $db->Query($sqlStat);

        if ($stRec = $db->Row())
            if ($stRec->aantal > 0)
                return "Wissen [$naam] niet toegestaan - gekoppeld aan sponsor dossier(s)";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Delete Rekening-detail-toewijzing toegestaan?
    //
    // In:	Toewijzing
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteToewijzing($pToewijzing){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -----------------------
        // Niet indien doorgeboekt
        // -----------------------

        $twRec = SSP_db::Get_EFIN_twRec($pToewijzing);

        if ($twRec->twDoorgeboekt == 1) {

            return "Toewijzing wissen niet mogelijk: werd reeds doorgeboekt";

        }

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Delete analytische rekening toegestaan?
    //
    // In:	Analytische rekening
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteAnalytischeRekening($pAnalytischeRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ----------------------------
        // Niet indien gekoppeld budget
        // ----------------------------

        $sqlStat = "Select count(*) as aantal from efin_ab_analytische_rekening_budgetten where abAnalytischeRekening = $pAnalytischeRekening";
        $db->Query($sqlStat);

        if ($abRec = $db->Row())
            if ($abRec->aantal)
                return "Wissen niet mogelijk: er zijn nog gekoppelde bugetten";

        // ---------------------------------
        // Niet indien gekoppelde ventilatie
        // ---------------------------------

        $sqlStat = "Select  count(*) as aantal from efin_vd_ventilatie_detail where vdAnalytischeRekening = $pAnalytischeRekening";
        $db->Query($sqlStat);

        if ($vdRec = $db->Row())
            if ($vdRec->aantal)
                return "Wissen niet mogelijk: er zijn ventilatie-rekeningen gekoppeld";


        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Delete Ventilatie rekening toegestaan?
    //
    // In:	Ventilatie rekening
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteVentilatieRekening($pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // -----------------------------------
        // Niet indien gekoppelde toewijzingen
        // -----------------------------------

        $sqlStat = "Select count(*) as aantal from efin_tw_rekening_detail_toewijzingen where twVentilatieRekening = $pVentilatieRekening";               $db->Query($sqlStat);

        if ($twRec = $db->Row())
            if ($twRec->aantal) {
                $aantal = $twRec->aantal;
                return "Wissen niet mogelijk: er zijn $aantal gekoppelde rekening-detail bedragen";
            }

        // ---------------------------------------------
        // Niet indien gekoppelde analytische rekeningen
        // ---------------------------------------------

        $sqlStat = "Select count(*) as aantal from efin_vd_ventilatie_detail where vdVentilatieRekening = $pVentilatieRekening";
        $db->Query($sqlStat);

        if ($vdRec = $db->Row())
            if ($vdRec->aantal)
                return "Wissen niet mogelijk: er zijn nog gekoppelde analytische Rekeningen";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Delete inkomende factuur toegestaan?
    //
    // In:	Inkomende factuur
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteIF($pInkomendeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));


        // ---------------------------------------
        // Enkel indien geen gekoppelde toewijzing
        // ---------------------------------------

        $sqlStat = "Select count(*) as aantal from efin_tw_rekening_detail_toewijzingen where twInkomendeFactuur = $pInkomendeFactuur";
        $db->Query($sqlStat);

        if ($twRec = $db->Row())
            if ($twRec->aantal > 0)
                return "Wissen niet toegestaan: Er is een gekoppelde rekening-detail toewijzing";

        // ------------------------
        // Enkel indien status OPEN
        // -------------------------

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        if ($ifRec->ifFactuurstatus == '*BETAALD' or $ifRec->ifFactuurstatus == '*BETALING_ING'  )
            return "Wissen niet toegestaan: status 'betaald'";

        $bedragGekoppeldeFactCn = $ifRec->ifBedragGekoppeldeFactCn + 0;

        if ($bedragGekoppeldeFactCn)
            return "Wissen niet toegestaan: Gekoppeld met één of meerdere CN of Facturen";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }


    // ========================================================================================
    //  Delete Leverancier Toegestaan?
    //
    // In:	Leverancioer
    //
    // Return: Booschap (*OK indien mag gewist worden)
    //
    // ========================================================================================

    static function ChkDeleteLeverancier($pLeverancier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // --------------------------
        // Enkel indien geen facturen
        // --------------------------

        $sqlStat = "Select count(*) as aantal from efin_if_inkomende_facturen where ifLeverancier = $pLeverancier";
        $db->Query($sqlStat);

        if ($ifRec = $db->Row())
            if ($ifRec->aantal > 0) {
                $aantal = $ifRec->aantal;
                return "Wissen niet toegestaan: Er zijn $aantal inkomende facturen voor deze leverancier";
            }
        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }


    // ========================================================================================
    //  Ophalen "wachtkas" bepaalde persoon
    //
    // In: Persoon
    //
    // Return: Rekening ID
    //
    // ========================================================================================

    static function GetPersoonWachtkas($pPersoon){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $rekening = 0;

        $persoon = $pPersoon;

        if ($persoon == 'webmaster')
            $persoon = 'gverhelst';

        $sqlStat = "Select * from efin_rk_rekeningen where rkPersoon = '$persoon' order by rkId desc";

        $db->Query($sqlStat);

        if ($rkRek = $db->Row())
            $rekening = $rkRek->rkId;

        // -------------
        // Einde functie
        // -------------

        return $rekening;

    }

    // ========================================================================================
    //  Get Analytische Structuur JSON( Als input voor JSTREE)
    //
    // In:	Geen
    //
    // Return: JSON-string
    //
    // ========================================================================================

    static function GetAnalytischeStructuurJSON(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where (arMoeder <= 0  or arMoeder is null) order by arSort, arId";

        $db->Query($sqlStat);

        $json = "";

        while ($arRec = $db->Row()){

            $id = $arRec->arId;
            $jsonAr = self::GetAnalytischeRekeningJSON($id);

            if (! $json)
                $json = $jsonAr;
            else
                $json .= ",$jsonAr";

        }

        $json = "[" . $json . "]";

        // -------------
        // Einde functie
        // -------------

        return $json;


    }

    // ========================================================================================
    //  Get Analytische Rekening JSON (Als input voor JSTREE)
    //
    // In:	Analytische Rekening
    //
    // Return: JSON-string
    //
    // ========================================================================================

    static function GetAnalytischeRekeningJSON($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $arRec = SSP_db::Get_EFIN_arRec($pAnalytischeRekening);

        $id = $arRec->arId;
        $text = utf8_encode($arRec->arNaam);

        // ------------------
        // Ophalen "children"
        // ------------------

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arMoeder = $id order by arSort, arId";
        $db->Query($sqlStat);

        $children = array();
        $chkChildren = false;
        $childrenJSON = "";

        while ($arRec = $db->Row()){

            $jsonChild = self::GetAnalytischeRekeningJSON($arRec->arId);

            $children[] = $jsonChild;
            $chkChildren = true;
        }

        if ($chkChildren){

            $teller = 1;

            foreach ($children as $child){

                if ($teller == 1)
                    $childrenJSON = "\"children\": [";

                if ($teller > 1)
                    $childrenJSON .= ",";

                $childrenJSON .= $child;

                $teller++;


            }

            $childrenJSON .= "]";

        }

        if (!$childrenJSON)
            $json = "{\"id\":$id,\"text\":\"$text\"}";
        else
            $json = "{\"id\":$id,\"text\":\"$text\" , $childrenJSON}";

        // -------------
        // Einde functie
        // -------------

        return $json;

    }

    // ========================================================================================
    //  Groepeer ventilatie
    //
    // In:	Analytische Rekening
    //
    // Return: Iets gewijzigd? true/false
    //
    // ========================================================================================

    static function GroupAnalytischeVentilatie($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        include_once(SX::GetClassPath("_db.class"));

        $arRec = SSP_db::Get_EFIN_arRec($pAnalytischeRekening);

        if (! $arRec)
            return false;

        if ($arRec->arVentilatieGroeperen != 1)
            return false;

        $ventilatieRekeningen = array();
        $percentages = array();

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arMoeder = $pAnalytischeRekening";
        $db->Query($sqlStat);

        while ($arRec = $db->Row()){

            $analytischeRekening = $arRec->arId;

            $sqlStat = "Select * from efin_vd_ventilatie_detail where vdAnalytischeRekening = $analytischeRekening";
            $db2->Query($sqlStat);

            while ($vdRec = $db2->Row()){

                $ventilatieRekening = $vdRec->vdVentilatieRekening;

                $key = array_search($ventilatieRekening, $ventilatieRekeningen);

                if (false === $key) {
                    $ventilatieRekeningen[] = $vdRec->vdVentilatieRekening;
                    $percentages[] = $vdRec->vdPercentage;
                }
                else {

                    $percentages[$key] += $vdRec->vdPercentage;
                }

            }

        }

        // ------
        // update
        // ------

        if (! $ventilatieRekeningen)
            return false;

        $sqlStat = "Delete from efin_vd_ventilatie_detail where vdAnalytischeRekening = $pAnalytischeRekening and (vdUserCreatie = '*GROEPEREN' or vdLinkType = '*GROEP')";
        $db->Query($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        foreach ($ventilatieRekeningen as $key => $ventilatieRekening) {

            $percentage = $percentages[$key];

            $values = array();

            $values["vdVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["vdAnalytischeRekening"] = MySQL::SQLValue($pAnalytischeRekening, MySQL::SQLVALUE_NUMBER);
            $values["vdPercentage"] = MySQL::SQLValue($percentage, MySQL::SQLVALUE_NUMBER);
            $values["vdLinkType"] = MySQL::SQLValue("*GROEP");

            $values["vdUserCreatie"] = MySQL::SQLValue("*GROEPEREN");
            $values["vdUserUpdate"] = MySQL::SQLValue("*GROEPEREN");
            $values["vdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["vdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db->InsertRow("efin_vd_ventilatie_detail", $values);

        }

        self::RegAnalytischeVentilatieHTML($pAnalytischeRekening);

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    //  Get analytische Rekening Level
    //
    // In:	Analytische Rekening
    //
    // Return: Level (0,1,2)
    //
    // ========================================================================================

    static function GetAnalytischeRekeningLevel($pAnalytischeRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $arRec = SSP_db::Get_EFIN_arRec($pAnalytischeRekening);

        if (! $arRec->arMoeder)
            return 0;

        $moeder = $arRec->arMoeder;

        $moederLevel = self::GetAnalytischeRekeningLevel($moeder);

        if (! $moederLevel)
            return 1;
        else
            return 2;


        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    //  Get analytische Rekening Root
    //
    // In:	Analytische Rekening
    //
    // Return: Level (0,1,2)
    //
    // ========================================================================================

    static function GetAnalytischeRekeningRoot($pAnalytischeRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $arRec = SSP_db::Get_EFIN_arRec($pAnalytischeRekening);

        $level = self::GetAnalytischeRekeningLevel($pAnalytischeRekening);

        if ($level == 0)
            return $pAnalytischeRekening;

        if ($level == 1)
            return $arRec->arMoeder;

        if ($level == 2) {

            $arRec = SSP_db::Get_EFIN_arRec($arRec->arMoeder);
            return $arRec->arMoeder;


        }

        // -------------
        // Einde functie
        // -------------

        return -1;

    }

    // ========================================================================================
    //  Set analytische Rekeningen Level
    // ========================================================================================

    static function SetAnalytischeRekeningenLevel(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arRecStatus = 'A'";
        $db->Query($sqlStat);

        while ($arRec = $db->Row()){

            $analytischeRekening = $arRec->arId;

            $level = self::GetAnalytischeRekeningLevel($analytischeRekening);

            $values = array();
            $where = array();

            $values["arLevel"] =  MySQL::SQLValue($level, MySQL::SQLVALUE_NUMBER);

            $where["arId"] =  MySQL::SQLValue($analytischeRekening, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("efin_ar_analytische_rekeningen", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Set analytische Rekeningen Root
    // ========================================================================================

    static function SetAnalytischeRekeningenRoot(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arRecStatus = 'A'";
        $db->Query($sqlStat);

        while ($arRec = $db->Row()){

            $analytischeRekening = $arRec->arId;

            $root = self::GetAnalytischeRekeningRoot($analytischeRekening);

            $values = array();
            $where = array();

            $values["arRoot"] =  MySQL::SQLValue($root, MySQL::SQLVALUE_NUMBER);

            $where["arId"] =  MySQL::SQLValue($analytischeRekening, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("efin_ar_analytische_rekeningen", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    //  Groepeer alle ventilaties
    //
    // In:	Niets
    //
    // Return: Iets gewijzigd? true/false
    //
    // ========================================================================================

    static function GroupAlleAnalytischeVentilaties() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arRecStatus = 'A'";

        $db->Query($sqlStat);

        while ($arRec = $db->Row()) {

            $analytischeRekening = $arRec->arId;
            self::GroupAnalytischeVentilatie($analytischeRekening);

        }

        // ------------------------
        // 2 X - Max. 2 levels diep
        // ------------------------

        $db->Query($sqlStat);

        while ($arRec = $db->Row()) {

            $analytischeRekening = $arRec->arId;
            self::GroupAnalytischeVentilatie($analytischeRekening);

        }

    }

    // ========================================================================================
    //  Groepeer Budgetten
    //
    // In:	Analytische Rekening
    //
    // Return: Iets gewijzigd? true/false
    //
    // ========================================================================================

    static function GroupAnalytischeBudgetten($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        include_once(SX::GetClassPath("_db.class"));

        $arRec = SSP_db::Get_EFIN_arRec($pAnalytischeRekening);

        if (! $arRec)
            return false;

        if ($arRec->arBudgetGroeperen != 1)
            return false;

        $periodes = array();
        $budgetten = array();

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arMoeder = $pAnalytischeRekening";
        $db->Query($sqlStat);

        while ($arRec = $db->Row()){

            $analytischeRekening = $arRec->arId;

            $sqlStat = "Select * from efin_ab_analytische_rekening_budgetten where abAnalytischeRekening = $analytischeRekening";
            $db2->Query($sqlStat);

            while ($abRec = $db2->Row()){

                $periode = $abRec->abPeriode;
                $budget = $abRec->abBudget;

                $key = array_search($periode, $periodes);

                if (false === $key) {
                    $periodes[] = $periode;
                    $budgetten[] = $budget;
                }
                else {

                    $budgetten[$key] += $budget;
                }

            }

        }

        // ------
        // update
        // ------

        if (! $budgetten)
            return false;

        $sqlStat = "Delete from efin_ab_analytische_rekening_budgetten where abAnalytischeRekening = $pAnalytischeRekening and (abLinkType = '*GROEP' or abUserCreatie = '*GROEPEREN')";
        $db->Query($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        foreach ($periodes as $key => $periode) {

            $budget = $budgetten[$key];

            $values = array();

            $values["abAnalytischeRekening"] = MySQL::SQLValue($pAnalytischeRekening, MySQL::SQLVALUE_NUMBER);
            $values["abPeriode"] = MySQL::SQLValue($periode);
            $values["abBudget"] = MySQL::SQLValue($budget, MySQL::SQLVALUE_NUMBER);

            $values["abLinkType"] = MySQL::SQLValue('*GROEP');

            $values["abUserCreatie"] = MySQL::SQLValue("*GROEPEREN");
            $values["abUserUpdate"] = MySQL::SQLValue("*GROEPEREN");
            $values["abDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["abDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db->InsertRow("efin_ab_analytische_rekening_budgetten", $values);

        }

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    //  Groepeer alle budgetten
    //
    // In:	Niets
    //
    // Return: Iets gewijzigd? true/false
    //
    // ========================================================================================

    static function GroupAlleAnalytischeBudgetten() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arRecStatus = 'A'";

        $db->Query($sqlStat);

        while ($arRec = $db->Row()) {

            $analytischeRekening = $arRec->arId;
            self::GroupAnalytischeBudgetten($analytischeRekening);

        }

        // ------------------------
        // 2 X - Max. 2 levels diep
        // ------------------------

        $db->Query($sqlStat);

        while ($arRec = $db->Row()) {

            $analytischeRekening = $arRec->arId;
            self::GroupAnalytischeBudgetten($analytischeRekening);

        }

    }

    // ========================================================================================
    //  Controleer analytische ventilatie
    //
    // In:	Analytische Rekening
    //
    // Return: OK? (Niet OK indien ventilatie > 100 %)
    //
    // ========================================================================================

    static function CheckAnalytischeVentilatie($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_vd_ventilatie_detail where vdAnalytischeRekening = $pAnalytischeRekening";
        $db->Query($sqlStat);

        while ($vdRec = $db->Row()){

            if ($vdRec->vdPercentage > 100)
                return false;

        }

        // -------------
        // Einde functie
        // -------------

        return true;


    }

    // ========================================================================================
    //  Opvullen Rekening Detail Saldi
    //
    // In:	Rekening
    //
    // ========================================================================================

    static function FillRekeningDetailSaldi($pRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        $rkRec = SSP_db::Get_EFIN_rkRec($pRekening);

        if (! $rkRec)
            return;

        $saldo = $rkRec->rkStartSaldo;
        $startDatum = $rkRec->rkDatumStartSaldo;

        $values = array();
        $where = array();

        $sqlStat = "Select * from efin_rd_rekening_details where rdRekening = $pRekening and rdDatum > '$startDatum' order by rdVolgnummer, rdId;";
        $db->Query($sqlStat);

        while ($rdRec = $db->Row()){

            $rekeningDetail = $rdRec->rdId;

            $saldo  += $rdRec->rdBedrag;

            $values["rdSaldo"] =  MySQL::SQLValue($saldo, MySQL::SQLVALUE_NUMBER);
            $where["rdId"] =  MySQL::SQLValue($rekeningDetail, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("efin_rd_rekening_details", $values, $where);



        }


        // -------------
        // Einde functie
        // -------------

        return;

    }

    // ========================================================================================
    //  Ophalen rekening saldo
    //
    // In:	Rekening
    //
    // Return: SALDO
    // ========================================================================================

    static function GetRekeningSaldo($pRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rd_rekening_details where rdRekening = $pRekening order by rdVolgnummer desc";
        $db->Query($sqlStat);

        $saldo = 0;

        if ($rdRec = $db->Row())
            $saldo = $rdRec->rdSaldo;

        // -------------
        // Einde functie
        // -------------

        return $saldo;


    }

    // ========================================================================================
    //  Doorboeken inkomende factuur naar EBA bestelbon
    //
    // In:	Inkomende factuur
    //
    // Return: SALDO
    // ========================================================================================

    static function BookFactuurNaarEBA($pInkomendeFactuur) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ifRec = SSP_db::Get_EFIN_ifRec($pInkomendeFactuur);

        if (! $ifRec)
            return;

        if ($ifRec->ifReferentieType != '*EBA')
            return;
        if (! $ifRec->ifReferentie)
            return;

        $bestelbon =  $ifRec->ifReferentie;

        $bhRec = SSP_db::Get_EBA_bhRec($bestelbon);

        if (! $bhRec)
            return;

        $factuurnummer = $ifRec->ifFactuurnummer;
        $factuurdatum = $ifRec->ifFactuurdatum;
        $factuurBedrag = 0;

        if ($ifRec->ifVentilatieBedrag1 > 0)
            $factuurBedrag = $ifRec->ifVentilatieBedrag1;
        if ($ifRec->ifVentilatieBedrag2 > 0)
            $factuurBedrag += $ifRec->ifVentilatieBedrag2;
        if ($ifRec->ifVentilatieBedrag3 > 0)
            $factuurBedrag += $ifRec->ifVentilatieBedrag3;

        $values = array();
        $where = array();

        if (! $bhRec->bhBetaalStatus)
            $values["bhBetaalStatus"] =  MySQL::SQLValue('*CONTROLE');

        $values["bhFactuurNr"] = MySQL::SQLValue($factuurnummer);
        $values["bhFactuurDatum"] = MySQL::SQLValue($factuurdatum, MySQL::SQLVALUE_DATE);
        $values["bhFactuurBedrag"] = MySQL::SQLValue($factuurBedrag, MySQL::SQLVALUE_NUMBER);
        $values["bhFactuurEFIN"] = MySQL::SQLValue($pInkomendeFactuur, MySQL::SQLVALUE_NUMBER);

        $where["bhId"] =  MySQL::SQLValue($bestelbon, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("eba_bh_bestel_headers", $values, $where);

    }


    // ========================================================================================
    //  Ophalen volgnummer KAS-boeking
    //
    // In:	Rekening
    //      Datum
    //
    // Return: Volgnummer (datum_xx)
    // ========================================================================================

    static function GetVolgendKasVolgnr($pRekening, $pDatum){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $date = date_create($pDatum);
        $volgnr_base = date_format($date, 'ymd');

        $volgnr_base .= "_";

        for ($i = 1; $i <= 99; $i++) {

            $sequence = str_pad($i, 2, '0', STR_PAD_LEFT);
            $volgnr = $volgnr_base . $sequence;

            $sqlStat = "select count(*) as aantal from efin_rd_rekening_details where rdRekening = $pRekening and rdVolgnummer = '$volgnr'";
            $db->Query($sqlStat);

            $rdRec = $db->Row();

            if ($rdRec->aantal == 0)
                break;

        }

        // -------------
        // Einde functie
        // -------------

        return $volgnr;

    }

    // ========================================================================================
    //  Ticketing Interface - Bijwerken totalen en controle-status
    //
    // In:	Ticketing Interface
    //
    // ========================================================================================

    static function UpdInterfaceTicketing($pInterfaceTicketing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from efin_xt_interface_ticketing where xtId = $pInterfaceTicketing";
        $db->Query($sqlStat);

        if (! $xtRec = $db->Row())
            return;

        // ----------------
        // Bedrag START-kas
        // ----------------

        if (1==2) {

            $startKasBedrag = ($xtRec->xtStartKasCent1 * (1 / 100))
                + ($xtRec->xtStartKasCent2 * (1 / 50))
                + ($xtRec->xtStartKasCent5 * (1 / 20))
                + ($xtRec->xtStartKasCent10 * (1 / 10))
                + ($xtRec->xtStartKasCent20 * (1 / 5))
                + ($xtRec->xtStartKasCent50 * (1 / 2))
                + ($xtRec->xtStartKasEur1)
                + ($xtRec->xtStartKasEur2 * 2)
                + ($xtRec->xtStartKasEur5 * 5)
                + ($xtRec->xtStartKasEur10 * 10)
                + ($xtRec->xtStartKasEur20 * 20)
                + ($xtRec->xtStartKasEur50 * 50)
                + ($xtRec->xtStartKasEur100 * 100);

        }

        // ----------------
        // Bedrag EIND-kas
        // ----------------

        if (1==2) {

            $eindKasBedrag = ($xtRec->xtEindKasCent1 * (1 / 100))
                + ($xtRec->xtEindKasCent2 * (1 / 50))
                + ($xtRec->xtEindKasCent5 * (1 / 20))
                + ($xtRec->xtEindKasCent10 * (1 / 10))
                + ($xtRec->xtEindKasCent20 * (1 / 5))
                + ($xtRec->xtEindKasCent50 * (1 / 2))
                + ($xtRec->xtEindKasEur1)
                + ($xtRec->xtEindKasEur2 * 2)
                + ($xtRec->xtEindKasEur5 * 5)
                + ($xtRec->xtEindKasEur10 * 10)
                + ($xtRec->xtEindKasEur20 * 20)
                + ($xtRec->xtEindKasEur50 * 50)
                + ($xtRec->xtEindKasEur100 * 100);
        }

        // ------------------------
        // Waarde verkochte tickets
        // ------------------------

        $ticketTarief1 = null;
        $ticketTarief2 = null;
        $ticketTarief3 = null;
        $ticketsBedrag = 0;

        if ($xtRec->xtTicketTariefCode1 and $xtRec->xtTicketEerste1 and $xtRec->xtTicketLaatste1){

            $taRec = SSP_db::Get_SX_taRec('EFIN_TICKET_TARIEF', $xtRec->xtTicketTariefCode1);

            if ($taRec->taNumData){

                $ticketTarief1 = $taRec->taNumData;
                $ticketAantal1 = ($xtRec->xtTicketLaatste1 - $xtRec->xtTicketEerste1 + 1);
                if ($ticketAantal1)
                    $ticketsBedrag += ($ticketAantal1 * $ticketTarief1);
            }
        }
        
        if ($xtRec->xtTicketTariefCode2 and $xtRec->xtTicketEerste2 and $xtRec->xtTicketLaatste2){

            $taRec = SSP_db::Get_SX_taRec('EFIN_TICKET_TARIEF', $xtRec->xtTicketTariefCode2);

            if ($taRec->taNumData){

                $ticketTarief2 = $taRec->taNumData;
                $ticketAantal2 = ($xtRec->xtTicketLaatste2 - $xtRec->xtTicketEerste2 + 1);
                if ($ticketAantal2)
                    $ticketsBedrag += ($ticketAantal2 * $ticketTarief2);
            }
        }
        
        if ($xtRec->xtTicketTariefCode3 and $xtRec->xtTicketEerste3 and $xtRec->xtTicketLaatste3){

            $taRec = SSP_db::Get_SX_taRec('EFIN_TICKET_TARIEF', $xtRec->xtTicketTariefCode3);

            if ($taRec->taNumData){

                $ticketTarief3 = $taRec->taNumData;
                $ticketAantal3 = ($xtRec->xtTicketLaatste3 - $xtRec->xtTicketEerste3 + 1);
                if ($ticketAantal3)
                    $ticketsBedrag += ($ticketAantal3 * $ticketTarief3);
            }
        }
        
        if ($xtRec->xtTicketTariefCode4 and $xtRec->xtTicketEerste4 and $xtRec->xtTicketLaatste4){

            $taRec = SSP_db::Get_SX_taRec('EFIN_TICKET_TARIEF', $xtRec->xtTicketTariefCode4);

            if ($taRec->taNumData){

                $ticketTarief4 = $taRec->taNumData;
                $ticketAantal4 = ($xtRec->xtTicketLaatste4 - $xtRec->xtTicketEerste4 + 1);
                if ($ticketAantal4)
                    $ticketsBedrag += ($ticketAantal4 * $ticketTarief4);
            }
        }
        
        // -------------
        // Controle-code
        // -------------

        $controleCode = '*OK';

        // START-kas ingegeven?
        if (($controleCode == '*OK') and
            ($xtRec->xtStartKasCent1 == null) and
            ($xtRec->xtStartKasCent2 == null) and
            ($xtRec->xtStartKasCent5 == null) and
            ($xtRec->xtStartKasCent10 == null) and
            ($xtRec->xtStartKasCent20 == null) and
            ($xtRec->xtStartKasCent50 == null) and
            ($xtRec->xtStartKasEur1 == null) and
            ($xtRec->xtStartKasEur2 == null) and
            ($xtRec->xtStartKasEur5 == null) and
            ($xtRec->xtStartKasEur10 == null) and
            ($xtRec->xtStartKasEur20 == null) and
            ($xtRec->xtStartKasEur50 == null) and
            ($xtRec->xtStartKasEur100 == null)) {

            // $controleCode = '*STARTKAS';

        }
        
        // EIND-kas ingegeven?
        if (1==2) {
            if (($controleCode == '*OK') and
                ($xtRec->xtEindKasCent1 == null) and
                ($xtRec->xtEindKasCent2 == null) and
                ($xtRec->xtEindKasCent5 == null) and
                ($xtRec->xtEindKasCent10 == null) and
                ($xtRec->xtEindKasCent20 == null) and
                ($xtRec->xtEindKasCent50 == null) and
                ($xtRec->xtEindKasEur1 == null) and
                ($xtRec->xtEindKasEur2 == null) and
                ($xtRec->xtEindKasEur5 == null) and
                ($xtRec->xtEindKasEur10 == null) and
                ($xtRec->xtEindKasEur20 == null) and
                ($xtRec->xtEindKasEur50 == null) and
                ($xtRec->xtEindKasEur100 == null)) {

                $controleCode = '*EINDKAS';

            }
        }
        // Eind kas mag niet lager dan start-kas (NIET MEER NODIG - > TEST op SALDO volstaat)
        //If ($controleCode == '*OK' and $startKasBedrag > $eindKasBedrag)
        //    $controleCode = '*LAGE-EIND-KAS';

        // Vergoeding ingegeven
        if ($controleCode == '*OK' and ($xtRec->xtMedewerker1) and ($xtRec->xtUitbetalingMedewerker1 == null))
            $controleCode = '*VERGOEDINGEN';
        if ($controleCode == '*OK' and ($xtRec->xtMedewerker2) and ($xtRec->xtUitbetalingMedewerker2 == null))
            $controleCode = '*VERGOEDINGEN';
        if ($controleCode == '*OK' and ($xtRec->xtMedewerker3) and ($xtRec->xtUitbetalingMedewerker3 == null))
            $controleCode = '*VERGOEDINGEN';

        if ($controleCode == '*OK' and $ticketsBedrag <= 0)
            $controleCode = '*TICKETS';

        // ----------------
        // Saldo-berekening
        // ----------------

        $uitBetalingen = $xtRec->xtUitbetalingMedewerker1 + $xtRec->xtUitbetalingMedewerker2 + $xtRec->xtUitbetalingMedewerker3;

        $saldoBerekening= "Eindkas: <b>$eindKasBedrag</b> EUR - Startkas: <b>$startKasBedrag</b> EUR - Verkochte tickets: <b>$ticketsBedrag</b> EUR + uitbetalingen: <b>$uitBetalingen</b> EUR";
        $saldoBedrag = $eindKasBedrag - $startKasBedrag - $ticketsBedrag + $uitBetalingen;

        //if ($controleCode == '*OK' and $saldoBedrag and (! $xtRec->xtSaldoVerschilReden))
        //    $controleCode = '*SALDO';

        // -----------------------
        // Update Interface record
        // -----------------------

        $values = array();
        $where = array();

        $values["xtControleCode"] =  MySQL::SQLValue($controleCode, MySQL::SQLVALUE_TEXT);

        $values["xtStartKasBedrag"] =  MySQL::SQLValue($startKasBedrag, MySQL::SQLVALUE_NUMBER);
        $values["xtEindKasBedrag"] =  MySQL::SQLValue($eindKasBedrag, MySQL::SQLVALUE_NUMBER);

        $values["xtTicketTarief1"] =  MySQL::SQLValue($ticketTarief1, MySQL::SQLVALUE_NUMBER);
        $values["xtTicketAantal1"] =  MySQL::SQLValue($ticketAantal1, MySQL::SQLVALUE_NUMBER);

        $values["xtTicketTarief2"] =  MySQL::SQLValue($ticketTarief2, MySQL::SQLVALUE_NUMBER);
        $values["xtTicketAantal2"] =  MySQL::SQLValue($ticketAantal2, MySQL::SQLVALUE_NUMBER);

        $values["xtTicketTarief3"] =  MySQL::SQLValue($ticketTarief3, MySQL::SQLVALUE_NUMBER);
        $values["xtTicketAantal3"] =  MySQL::SQLValue($ticketAantal3, MySQL::SQLVALUE_NUMBER);

        $values["xtTicketTarief4"] =  MySQL::SQLValue($ticketTarief4, MySQL::SQLVALUE_NUMBER);
        $values["xtTicketAantal4"] =  MySQL::SQLValue($ticketAantal4, MySQL::SQLVALUE_NUMBER);
        
        $values["xtTicketsBedrag"] =  MySQL::SQLValue($ticketsBedrag, MySQL::SQLVALUE_NUMBER);

        $values["xtSaldoBerekening"] =  MySQL::SQLValue($saldoBerekening, MySQL::SQLVALUE_TEXT);
        $values["xtSaldoBedrag"] =  MySQL::SQLValue($saldoBedrag, MySQL::SQLVALUE_NUMBER);

        $where["xtId"] =  MySQL::SQLValue($pInterfaceTicketing, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_xt_interface_ticketing", $values, $where);


        // -------------
        // Einde functie
        // -------------

        return;

    }
    // ========================================================================================
    //  Ticketing Interface -Check of delete toegestaan
    //
    // In:	Ticketing Interface-record ID
    //
    // ========================================================================================

    static function ChkDeleteInterfaceTicketing($pInterfaceTicketing){


        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...


        $allowDelete = true;

        $sqlStat = "Select * from efin_xt_interface_ticketing where xtId = $pInterfaceTicketing";
        $db->Query($sqlStat);


        if ($xtRec = $db->Row())
            if ($xtRec->xtBoekStatus == '*GEBOEKT')
                $allowDelete = false;

        // -------------
        // Einde functie
        // -------------

        return $allowDelete;

    }

    // ========================================================================================
    //  Ticketing Interface - Doorboeken openstaanden
    //
    // ========================================================================================

    static function BoekInterfacesTicketing(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_xt_interface_ticketing where xtBoekStatus <> '*GEBOEKT' and xtControleCode = '*OK'";
        $db->Query($sqlStat);

        while ($xtRec = $db->Row())
            self::BoekInterfaceTicketing($xtRec->xtId);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Ticketing Interface - Doorboeken
    //
    // In:	Ticketing Interface-record ID
    //
    // ========================================================================================

    static function BoekInterfaceTicketing($pInterfaceTicketing){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include(SX::GetClassPath("efin_interface_era.class"));  // Create DB-object...
        include_once(Sx::GetSxClassPath("tools.class"));


        // ---------------------
        // Ophalen basisgegevens
        // ---------------------

        $sqlStat = "Select * from efin_xt_interface_ticketing where xtId = $pInterfaceTicketing";
        $db->Query($sqlStat);

        if (! $xtRec = $db->Row())
            return;

        if ($xtRec->xtControleCode != '*OK')
            return;

        $kasboek = self::GetTicketingKasboek();

        if (! $kasboek)
            return;

        // --------------------
        // Boek verkoop tickets
        // --------------------

        $bedrag = 0;
        $bedragSaldo = 0;

        // Groepeer per tariefcode...
        $tariefCodes = array();
        $tarieven = array();
        $aantallen = array();

        if ($xtRec->xtTicketTariefCode1 and $xtRec->xtTicketAantal1) {

            $tariefCodes[] = $xtRec->xtTicketTariefCode1;
            $aantallen[] = $xtRec->xtTicketAantal1;
            $tarieven[] = $xtRec->xtTicketTarief1;
            
        }

        if ($xtRec->xtTicketTariefCode2 and $xtRec->xtTicketAantal2) {

            $key = array_search($xtRec->xtTicketTariefCode2, $tariefCodes);

            if ($key === false) {
                $tariefCodes[] = $xtRec->xtTicketTariefCode2;
                $aantallen[] = $xtRec->xtTicketAantal2;
                $tarieven[] = $xtRec->xtTicketTarief2;
            } else 
                $aantallen[$key] += $xtRec->xtTicketAantal2;              

        }
        
        if ($xtRec->xtTicketTariefCode3 and $xtRec->xtTicketAantal3) {

            $key = array_search($xtRec->xtTicketTariefCode3, $tariefCodes);

            if ($key === false) {
                $tariefCodes[] = $xtRec->xtTicketTariefCode3;
                $aantallen[] = $xtRec->xtTicketAantal3;
                $tarieven[] = $xtRec->xtTicketTarief3;
            } else
                $aantallen[$key] += $xtRec->xtTicketAantal3;

        }

        if ($xtRec->xtTicketTariefCode4 and $xtRec->xtTicketAantal4) {

            $key = array_search($xtRec->xtTicketTariefCode4, $tariefCodes);

            if ($key === false) {
                $tariefCodes[] = $xtRec->xtTicketTariefCode4;
                $tarieven[] = $xtRec->xtTicketTarief4;
                $aantallen[] = $xtRec->xtTicketAantal4;
            } else
                $aantallen[$key] += $xtRec->xtTicketAantal4;

        }

        foreach ($tariefCodes as $key=>$tariefCode) {

            $ticketTarief = $tarieven[$key];
            $aantal = $aantallen[$key];

            $bedrag = self::BoekTicketingTicketverkoop($kasboek, $xtRec->xtDatum, $tariefCode, $ticketTarief, $aantal, $xtRec->xtUserCreatie, $pInterfaceTicketing);

            $bedragSaldo += $bedrag;

        }

        // ---------------------------
        // Boek vergoeding medewerkers
        // ---------------------------

        if (1==2) {

            if ($xtRec->xtMedewerker1 and ($xtRec->xtUitbetalingMedewerker1 > 0)) {

                self::BoekTicketingVergoeding($kasboek, $xtRec->xtDatum, $xtRec->xtMedewerker1, $xtRec->xtUitbetalingMedewerker1, $xtRec->xtUserCreatie, $pInterfaceTicketing);

                $bedragSaldo -= $xtRec->xtUitbetalingMedewerker1;
            }

            if ($xtRec->xtMedewerker2 and ($xtRec->xtUitbetalingMedewerker2 > 0)) {
                self::BoekTicketingVergoeding($kasboek, $xtRec->xtDatum, $xtRec->xtMedewerker2, $xtRec->xtUitbetalingMedewerker2, $xtRec->xtUserCreatie, $pInterfaceTicketing);
                $bedragSaldo -= $xtRec->xtUitbetalingMedewerker2;
            }

            if ($xtRec->xtMedewerker3 and ($xtRec->xtUitbetalingMedewerker3 > 0)) {
                self::BoekTicketingVergoeding($kasboek, $xtRec->xtDatum, $xtRec->xtMedewerker3, $xtRec->xtUitbetalingMedewerker3, $xtRec->xtUserCreatie, $pInterfaceTicketing);
                $bedragSaldo -= $xtRec->xtUitbetalingMedewerker3;
            }

        }


        // ---------------------------------------------------------------------
        // Doorboeken vergoeding ticketing medewerkers naar "diverse prestaties"
        // ---------------------------------------------------------------------

        $datum = $xtRec->xtDatum;
        $datumE = SX_tools::EdtDate($datum);

        $omschrijving = "Ticketing $datumE";

        $user = '*TICKETING-INTERFACE';

        if ($xtRec->xtMedewerker1 and ($xtRec->xtUitbetalingMedewerker1 > 0)) {

            $uren =  round($xtRec->xtUitbetalingMedewerker1 / 5);
            $persoon = $xtRec->xtMedewerker1;


            if ($uren > 0 and $uren <= 8)
                SSP_efin_interface_era::CrtDiversePrestatie($persoon, $uren, '*TICKETING', $datum, $omschrijving, $user);
            
        }

        if ($xtRec->xtMedewerker2 and ($xtRec->xtUitbetalingMedewerker2 > 0)) {

            $uren =  round($xtRec->xtUitbetalingMedewerker2 / 5);
            $persoon = $xtRec->xtMedewerker2;

            if ($uren > 0 and $uren <= 8)
                SSP_efin_interface_era::CrtDiversePrestatie($persoon, $uren, '*TICKETING', $datum, $omschrijving, $user);

        }

        if ($xtRec->xtMedewerker3 and ($xtRec->xtUitbetalingMedewerker3 > 0)) {

            $uren =  round($xtRec->xtUitbetalingMedewerker3 / 5);
            $persoon = $xtRec->xtMedewerker3;

            if ($uren > 0 and $uren <= 8)
                SSP_efin_interface_era::CrtDiversePrestatie($persoon, $uren, '*TICKETING', $datum, $omschrijving, $user);

        }
        
        // --------------------------------------------------------------------
        // Boek eventueel kasverschil (OBSOLETE -> nu via ticketing groepering)
        // --------------------------------------------------------------------

        if (1==2)
         if ($xtRec->xtSaldoBedrag != 0 and $xtRec->xtSaldoBedrag != null )
            self::BoekTicketingKasverschil($kasboek, $xtRec->xtDatum,$xtRec->xtSaldoBedrag, $xtRec->xtSaldoVerschilReden, $xtRec->xtUserCreatie, $pInterfaceTicketing);

         // ----------
        // Tegnboeking
        // -----------

        if ($bedragSaldo) {

            self::BoekTicketingTegenboeking($kasboek, $xtRec->xtDatum,$bedragSaldo * -1, $xtRec->xtUserCreatie, $pInterfaceTicketing);


        }


        // ------------------
        // Herberekenen saldi
        // ------------------

        self::FillRekeningDetailSaldi($kasboek);

        // ---------------
        // Set Boek-status
        // ---------------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();
        $where = array();

        $values["xtBoekStatus"] =  MySQL::SQLValue('*GEBOEKT', MySQL::SQLVALUE_TEXT);
        $values["xtBoekDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $where["xtId"] =  MySQL::SQLValue($pInterfaceTicketing, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_xt_interface_ticketing", $values, $where);

        // -------------------------
        // Log datum laatste boeking
        // -------------------------

        self::SetInterfaceDatumLaatsteBoeking('*TICKETING');

        // -------------
        // Einde functie
        // -------------


    }

    // ========================================================================================
    //  Ticketing Interface - Boek verkoop Tickets
    //
    // In:	Kasboek ID
    //      Datum
    //      Ticketcode
    //      Ticket tarief
    //      Aantal
    //      user-id Ingave
    //      Interface Ticketing ID

    //  Uit: Geboekt bedrag
    //
    // ========================================================================================

    static function BoekTicketingTicketverkoop($pKasboek, $pDatum, $pTicketCode, $pTicketTarief, $pAantal, $pUserIngave, $pInterfaceTicketing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rkRek = SSP_db::Get_EFIN_rkRec($pKasboek);

        $curDateTime = date('Y-m-d H:i:s');


        // ------------------------
        // Aanmaken rekening-detail
        // ------------------------

        $volgnummer = self::GetVolgendKasVolgnr($pKasboek, $pDatum);
        $bedrag = $pTicketTarief * $pAantal;
        $valuta = $rkRek->rkValuta;

        $taRec = SSP_db::Get_SX_taRec('EFIN_TICKET_TARIEF', $pTicketCode);

        $interfaceCode = $taRec->taAlfaData;

        $ticketNaam = $taRec->taName;
        $mededeling = "Ticket verkoop $ticketNaam";

        $values = array();

        $values["rdRekening"] = MySQL::SQLValue($pKasboek, MySQL::SQLVALUE_NUMBER);
        $values["rdVolgnummer"] = MySQL::SQLValue($volgnummer, MySQL::SQLVALUE_TEXT);
        $values["rdDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdRefDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);

        $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
        $values["rdValuta"] = MySQL::SQLValue($valuta, MySQL::SQLVALUE_TEXT);

        $values["rdMededeling"] = MySQL::SQLValue($mededeling, MySQL::SQLVALUE_TEXT);
        $values["rdOorsprong"] = MySQL::SQLValue('*TICKETING', MySQL::SQLVALUE_TEXT);
        $values["rdLink"] = MySQL::SQLValue($pInterfaceTicketing, MySQL::SQLVALUE_NUMBER);

        $values["rdStatusDoorboeken"] = MySQL::SQLValue('*NVT', MySQL::SQLVALUE_TEXT);

        $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
        $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

        $values["rdRecStatus"] = MySQL::SQLValue('A', MySQL::SQLVALUE_TEXT);

        $rekeningDetail = $db->InsertRow("efin_rd_rekening_details", $values);

        // ----------
        // Ventilatie
        // ----------

        // $taRec = SSP_db::Get_SX_taRec('EFIN_INTERFACE_CODES', $interfaceCode);

        $xxRec = SSP_db::Get_EFIN_xxRec('*TICKETING',$interfaceCode);

        $ventilatieRekening = null;

        if ($xxRec)
            $ventilatieRekening = $xxRec->xxVentilatie;

        if ($ventilatieRekening){

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($rekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

            $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::SetRdStatusToewijzen($rekeningDetail);

        }

        // -------------
        // Einde functie
        // -------------

        return $bedrag;

    }

    // ========================================================================================
    //  Ticketing Interface - Boek vergoeding medewerkers
    //
    // In:	Kasboek ID
    //      Datum
    //      Medewerker
    //      Bedrag
    //      User-id Ingave
    //      Interface Ticketing ID
    //
    // ========================================================================================

    static function BoekTicketingVergoeding($pKasboek, $pDatum, $pMedewerker, $pBedrag, $pUserIngave, $pInterfaceTicketing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rkRek = SSP_db::Get_EFIN_rkRec($pKasboek);

        $adRec = SSP_db::Get_SSP_adRec($pMedewerker);

        $curDateTime = date('Y-m-d H:i:s');

        $bedrag = $pBedrag * -1;

        // ------------------------
        // Aanmaken rekening-detail
        // ------------------------

        $volgnummer = self::GetVolgendKasVolgnr($pKasboek, $pDatum);
        $valuta = $rkRek->rkValuta;

        $naam = $adRec->adVoornaamNaam;
        $mededeling = "Vergoeding ticket medewerker $naam";

        $values = array();

        $values["rdRekening"] = MySQL::SQLValue($pKasboek, MySQL::SQLVALUE_NUMBER);
        $values["rdVolgnummer"] = MySQL::SQLValue($volgnummer, MySQL::SQLVALUE_TEXT);
        $values["rdDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdRefDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);

        $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
        $values["rdValuta"] = MySQL::SQLValue($valuta, MySQL::SQLVALUE_TEXT);

        $values["rdMededeling"] = MySQL::SQLValue($mededeling, MySQL::SQLVALUE_TEXT);
        $values["rdOorsprong"] = MySQL::SQLValue('*TICKETING', MySQL::SQLVALUE_TEXT);
        $values["rdLink"] = MySQL::SQLValue($pInterfaceTicketing, MySQL::SQLVALUE_NUMBER);

        $values["rdStatusDoorboeken"] = MySQL::SQLValue('*NVT', MySQL::SQLVALUE_TEXT);

        $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
        $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

        $values["rdRecStatus"] = MySQL::SQLValue('A', MySQL::SQLVALUE_TEXT);

        $rekeningDetail = $db->InsertRow("efin_rd_rekening_details", $values);

        // ----------
        // Ventilatie
        // ----------

        $xxRec = SSP_db::Get_EFIN_xxRec('*TICKETING','*VERGOEDING');

        $ventilatieRekening = null;

        if ($xxRec)
            $ventilatieRekening = $xxRec->xxVentilatie;

        if ($ventilatieRekening){

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($rekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

            $values["twPersoon"] = MySQL::SQLValue($pMedewerker, MySQL::SQLVALUE_TEXT);

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

            $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::SetRdStatusToewijzen($rekeningDetail);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Ticketing Interface - Boek KASVERSCHIL  **** OBSOLETE ****
    //
    // In:	Kasboek ID
    //      Datum
    //      Bedrag
    //      Reden
    //      User-id Ingave
    //      Interface Ticketing ID
    //
    // ========================================================================================

    static function BoekTicketingKasverschil($pKasboek, $pDatum, $pBedrag, $pReden, $pUserIngave, $pInterfaceTicketing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rkRek = SSP_db::Get_EFIN_rkRec($pKasboek);

        $curDateTime = date('Y-m-d H:i:s');

        // ------------------------
        // Aanmaken rekening-detail
        // ------------------------

        $volgnummer = self::GetVolgendKasVolgnr($pKasboek, $pDatum);
        $valuta = $rkRek->rkValuta;

        $mededeling = "Ticketing KASVERSCHIL (Reden: $pReden)";

        $values = array();

        $values["rdRekening"] = MySQL::SQLValue($pKasboek, MySQL::SQLVALUE_NUMBER);
        $values["rdVolgnummer"] = MySQL::SQLValue($volgnummer, MySQL::SQLVALUE_TEXT);
        $values["rdDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdRefDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);

        $values["rdBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
        $values["rdValuta"] = MySQL::SQLValue($valuta, MySQL::SQLVALUE_TEXT);

        $values["rdMededeling"] = MySQL::SQLValue($mededeling, MySQL::SQLVALUE_TEXT);
        $values["rdOorsprong"] = MySQL::SQLValue('*TICKETING', MySQL::SQLVALUE_TEXT);
        $values["rdLink"] = MySQL::SQLValue($pInterfaceTicketing, MySQL::SQLVALUE_NUMBER);

        $values["rdStatusDoorboeken"] = MySQL::SQLValue('*NVT', MySQL::SQLVALUE_TEXT);

        $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
        $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

        $values["rdRecStatus"] = MySQL::SQLValue('A', MySQL::SQLVALUE_TEXT);

        $rekeningDetail = $db->InsertRow("efin_rd_rekening_details", $values);

        // ----------
        // Ventilatie
        // ----------

        // $taRec = SSP_db::Get_SX_taRec('EFIN_INTERFACE_CODES', '*TICKETING-KASVERSCHIL');

        $xxRec = SSP_db::Get_EFIN_xxRec('*TICKETING','*KASVERSCHIL');

        $ventilatieRekening = null;

        if ($xxRec)
            $ventilatieRekening = $xxRec->xxVentilatie;

        if ($ventilatieRekening){

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($rekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

            $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::SetRdStatusToewijzen($rekeningDetail);

        }

        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    //  Ticketing Interface - Boek Tegenboeking
    //
    // In:	Kasboek ID
    //      Datum
    //      Bedrag
    //      User-id Ingave
    //      Interface Ticketing ID
    //
    // ========================================================================================

    static function BoekTicketingTegenboeking($pKasboek, $pDatum, $pBedrag, $pUserIngave, $pInterfaceTicketing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rkRek = SSP_db::Get_EFIN_rkRec($pKasboek);

        $curDateTime = date('Y-m-d H:i:s');

        // ------------------------
        // Aanmaken rekening-detail
        // ------------------------

        $volgnummer = self::GetVolgendKasVolgnr($pKasboek, $pDatum);
        $valuta = $rkRek->rkValuta;

        $mededeling = "Ticketing - Tegenboeking";

        $values = array();

        $values["rdRekening"] = MySQL::SQLValue($pKasboek, MySQL::SQLVALUE_NUMBER);
        $values["rdVolgnummer"] = MySQL::SQLValue($volgnummer, MySQL::SQLVALUE_TEXT);
        $values["rdDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdRefDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);

        $values["rdBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
        $values["rdValuta"] = MySQL::SQLValue($valuta, MySQL::SQLVALUE_TEXT);

        $values["rdMededeling"] = MySQL::SQLValue($mededeling, MySQL::SQLVALUE_TEXT);
        $values["rdOorsprong"] = MySQL::SQLValue('*TICKETING', MySQL::SQLVALUE_TEXT);
        $values["rdLink"] = MySQL::SQLValue($pInterfaceTicketing, MySQL::SQLVALUE_NUMBER);

        $values["rdStatusDoorboeken"] = MySQL::SQLValue('*NVT', MySQL::SQLVALUE_TEXT);

        $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
        $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

        $values["rdRecStatus"] = MySQL::SQLValue('A', MySQL::SQLVALUE_TEXT);

        $rekeningDetail = $db->InsertRow("efin_rd_rekening_details", $values);

        // ----------
        // Ventilatie
        // ----------

        // $taRec = SSP_db::Get_SX_taRec('EFIN_INTERFACE_CODES', '*TICKETING-KASVERSCHIL');

        $xxRec = SSP_db::Get_EFIN_xxRec('*TICKETING','*TEGENBOEKING');

        $ventilatieRekening = null;

        if ($xxRec)
            $ventilatieRekening = $xxRec->xxVentilatie;

        if ($ventilatieRekening){

            $values = array();

            $values["twRekeningDetail"] = MySQL::SQLValue($rekeningDetail, MySQL::SQLVALUE_NUMBER);
            $values["twBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
            $values["twVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

            $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserCreatie"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);
            $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["twUserUpdate"] = MySQL::SQLValue($pUserIngave, MySQL::SQLVALUE_TEXT);

            $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

            self::SetRdStatusToewijzen($rekeningDetail);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Ticketing Interface - Ophalen ticketing kasboek
    //
    // In:  GEEN
    // Uit: Rekening
    //
    // ========================================================================================

    static function GetTicketingKasboek(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $kasboek = 0;

        $sqlStat = "Select * from efin_xx_interface_parameters where xxCode = '*TICKETS-JEUGD'";
        $db->Query($sqlStat);

        if ($xxRek = $db->Row())
            $kasboek = $xxRek->xxRekening;

        // -------------
        // Einde functie
        // -------------

        return $kasboek;

    }


    // ========================================================================================
    //  Aanmaken "Eenvoudige" ventilatie  (volledig bedrag, zonder doorboeking)
    //
    // In:	User-ID
    //  	Rekening-detail
    //      Ventilatie
    //
    // ========================================================================================

    static function CrtEenvoudigeVentilatie($pUserId, $pRekeningDetail, $pVentilatie){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);
        $vrRec = SSP_db::Get_EFIN_vrRec($pVentilatie);

        // ----------
        // Ventilatie
        // ----------

        $bedrag = $rdRec->rdBedrag;
        $curDateTime = date('Y-m-d H:i:s');

        $values = array();

        $values["twRekeningDetail"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);
        $values["twBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
        $values["twVentilatieRekening"] = MySQL::SQLValue($pVentilatie, MySQL::SQLVALUE_NUMBER);

        $values["twDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["twUserCreatie"] = MySQL::SQLValue($pUserId, MySQL::SQLVALUE_TEXT);
        $values["twDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["twUserUpdate"] = MySQL::SQLValue($pUserId, MySQL::SQLVALUE_TEXT);

        $id = $db->InsertRow("efin_tw_rekening_detail_toewijzingen", $values);

        self::SetRdStatusToewijzen($pRekeningDetail);


        if (! $vrRec->vrDoorboeken) {

            $values = array();
            $where = array();

            $values["rdStatusDoorboeken"] =  MySQL::SQLValue('*NVT');

            $where["rdId"] =  MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

            $db->UpdateRows("efin_rd_rekening_details", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    //  Mail indien "goed te keuren" facturen
    //
    // In:	Type (bv *HORECA)
    //  	Rekening-detail
    //      Ventilatie
    //
    // ========================================================================================

    static function MailGoedTeKeurenFacturen($pType = '*HORECA', $pMailAdres = "horeca@schellesport.be"){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetSxClassPath("tools.class"));

        $sqlStat = "Select * from efin_if_inkomende_facturen where ifFactuurstatus = '*WACHTEN' and ifReferentieType = '*HORECA'";
        $db->Query($sqlStat);

        $aantal = 0;
        $leveranciers = array();

        while ($ifRec = $db->Row()){

            $leverancier = $ifRec->ifLeverancier;

            $aantal++;

            if ($aantal == 1)
                $leveranciers[] = $leverancier;
            else {

                $key = array_search($leverancier, $leveranciers);

                if (false === $key)
                    $leveranciers[] = $leverancier;

            }

        }

        // ----------------------------
        // Geen goed te keuren facturen
        // ----------------------------

        if (! $aantal)
            return;

        // ----
        // MAIL
        // ----

        $mailBody = "<!DOCTYPE html>";
        $mailBody .= "<html>";
        $mailBody .= "<head>";

        $mailBody .= "<style>";
        $mailBody .= "table, th, td { ";
        $mailBody .= " border: 1px solid black; ";
        $mailBody .= " border-collapse: collapse;";
        $mailBody .= "} ";
        $mailBody .= "th, td { ";
        $mailBody .= "  padding: 5px; ";
        $mailBody .= "  text-align: left;";
        $mailBody .= " } ";
        $mailBody .= "</style>";

        $mailBody .= "</head>";

        $mailBody .= "<body>" . "\r\n";

        $mailBody .= "Beste Horeca verantwoordelijke,<br/><br/>" . "\r\n";

        if ($aantal == 1)
            $mailBody .= "Er is één <b>goed te keuren</b> inkomende factuur." . "\r\n";
        else
        $mailBody .= "Er zijn $aantal <b>goed te keuren</b> facturen." . "\r\n";

        $mailBody .= "<br/><br/>";
        $mailBody .= "Van volgende leverancier(s):" . "\r\n";
        $mailBody .= "<ul>" . "\r\n";

        foreach ($leveranciers as $leverancier){

            $lvRec = SSP_db::Get_EFIN_lvRec($leverancier);

            if ($lvRec) {
                $naam = $lvRec->lvNaam;
                $mailBody .= "<li>$naam</li>" . "\r\n";
            }

        }

        $mailBody .= "</ul>" . "\r\n";
        $mailBody .= "<br/>Sportieve groet," . "\r\n";
        $mailBody .= "<br/><br/>Schelle Sport EFIN" . "\r\n";
        $mailBody .= "</body></html>"  . "\r\n";

        SX_tools::SendMail('Schelle Sport - Goed te keuren facturen ', $mailBody, 'horeca@schellesport.be', '', 'secretariaat@schellesport.be', 'Schelle Sport - Secretariaat', '', 'UTF-8', 'financieel@schellesport.be');

    }

    // ========================================================================================
    //  Uitgaande Factuur - Ophalen volgende factuur-nummer
    //
    // In:	Factuurtype
    //      Factuurdatum
    //
    // Uit: Volgende factuurnummer
    // ========================================================================================

    static function GetFactuurnummer($pFactuurType, $pFactuurDatum) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ftRec = SSP_db::Get_EFIN_ftRec($pFactuurType);

        if (! $ftRec)
            return null;

        $documentType = $ftRec->ftDocumentType;

        $time = strtotime($pFactuurDatum);
        $jaar = date('Y', $time);

        $fnRec = SSP_db::Get_EFIN_fnRec($documentType, $jaar);

        if (! $fnRec)
            return null;

        $factuurNummer = $fnRec->fnVolgendeNummer;

        if ($factuurNummer){

            $volgendeNummer = $factuurNummer + 1;
            $jaar = $fnRec->fnJaar;

            $sqlStat = "update efin_fn_factuur_nummers set fnVolgendeNummer = $volgendeNummer where fnDocumentType = '$documentType' and fnJaar = '$jaar' ";
            $db->Query($sqlStat);

        }

        // --------------------
        // Mag nog niet bestaan
        // --------------------

        $sqlStat = "Select count(*) as aantal from efin_uf_uitgaande_facturen where ufFactuurnummer = $factuurNummer";
        $db->Query($sqlStat);

        if ($ufRec = $db->Row() and $ufRec->aantal > 0)
            $factuurNummer = self::GetFactuurnummer($pFactuurType, $pFactuurDatum);


        // -------------
        // Einde functie
        // -------------

        return $factuurNummer;

    }

    // ========================================================================================
    //  Uitgaande Factuur - Bijwerken totalen en Status
    //
    // In:	Uitgaande Factuur
    //      Modus (*ADD, *UPDATE)
    //
    // ========================================================================================

    static function UpdUitgaandeFactuur($pUitgaandeFactuur, $pModus = "*UPDATE"){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return;

        $klRec = SSP_db::Get_EFIN_klRec($ufRec->ufKlant);

        if (! $klRec)
            return;

        // -----------
        // Factuurtype
        // -----------

        $factuurtype = $ufRec->ufFactuurtype;

        if (! $factuurtype)
            $factuurtype = $klRec->klFactuurtype;

        // --------------------------
        // Automatische Factuurnummer
        // --------------------------

        $factuurnummer = $ufRec->ufFactuurnummer;

        if (! $factuurnummer){

            $factuurnummer = self::GetFactuurnummer($ufRec->ufFactuurtype, $ufRec->ufFactuurdatum );

        }

        // --------------------------
        // Gestructureerde mededeling
        // --------------------------

        if (! $ufRec->ufBetaalMededelingGM) {

            $GM = self::GetNextGM('*UITGAANDE_FACUREN');
            $GMnum = self::CvtGmToNum($GM);

        }

        // ---------------------------
        // Totalen op basis van detail
        // ---------------------------

        $maatstafTotaal = 0;
        $BTWTotaal = 0;
        $factuurTotaal = 0;
        $ventilatie = "";

        $sqlStat = "Select * from efin_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur";
        $db->Query($sqlStat);

        while ($udRec = $db->Row()){

            $maatstafTotaal += $udRec->udBedragMaatstaf;
            $BTWTotaal += $udRec->udBTWBedrag;

        }

        $factuurTotaal = $maatstafTotaal + $BTWTotaal;

        // -----------
        // Ventilaties
        // -----------

        $sqlStat = "Select distinct(udVentilatieRekening) as ventilatie from efin_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur";
        $db->Query($sqlStat);

        $i = 0;

        while ($udRec = $db->Row()){

            $i += 1;

            $vrRec = SSP_db::Get_EFIN_vrRec($udRec->ventilatie);

            if ($i == 1)
                $ventilatie = $vrRec->vrNaam;

            if ($i == 2)
                $ventilatie = '- ' . $ventilatie;

            if ($i >= 2)
                $ventilatie .= '\n\r' . '- ' . $vrRec->vrNaam;

        }

        // -------
        // Totalen
        // -------

        $factuurTotaal -= $ufRec->ufReedsBetaald;

        $betaalBedragTotaal = $ufRec->ufBetaalBedrag1 + $ufRec->ufBetaalBedrag2 + $ufRec->ufBetaalBedrag3 + $ufRec->ufBetaalBedragFC;

        // Afronden op 2 decimale
        $factuurTotaal = round($factuurTotaal, 2);
        $betaalBedragTotaal = round($betaalBedragTotaal, 2);

        // --------
        // Controle
        // --------

        $controle = "*OK";

        //if (($controle == "*OK") and (! $ventilatieBedragTotaal))
        //    $controle = "*VENTILATIE_GEEN";

        //if (($controle == "*OK") and ($ventilatieBedragTotaal != $factuurTotaal))
        //    $controle = "*VENTILATIE_VERSCHIL";
;

        // --------------
        // Factuur-status
        // --------------

        $factuurStatus = "*WACHT";

        if ($controle == '*OK')
            $factuurStatus = "*KLAAR";

        if ($ufRec->ufAantalMails)
            $factuurStatus = "*VERSTUURD";

        if (abs($betaalBedragTotaal) > 0 and abs($betaalBedragTotaal) < abs($factuurTotaal))
            $factuurStatus = "*PART_BETAALD";

        if (abs($factuurTotaal) <= abs($betaalBedragTotaal))
            $factuurStatus = "*BETAALD";

        if (($factuurStatus == '*BETAALD') and ($ufRec->ufBetaalBedragFC) and (! $ufRec->ufIsCreditnota))
            $factuurStatus = "*BETAALD_VC";
        if (($factuurStatus == '*BETAALD') and ($ufRec->ufBetaalBedragFC) and ($ufRec->ufIsCreditnota) )
            $factuurStatus = "*BETAALD_VF";

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $values["ufFactuurnummer"] =  MySQL::SQLValue($factuurnummer, MySQL::SQLVALUE_NUMBER);
        $values["ufFactuurtype"] =  MySQL::SQLValue($factuurtype, MySQL::SQLVALUE_TEXT);

        if (! $ufRec->ufBetaalMededelingGM) {
            $values["ufBetaalMededelingGM"] =  MySQL::SQLValue($GM, MySQL::SQLVALUE_TEXT);
            $values["ufBetaalMededelingGMnum"] =  MySQL::SQLValue($GMnum, MySQL::SQLVALUE_NUMBER);
        }

        $values["ufControle"] =  MySQL::SQLValue($controle, MySQL::SQLVALUE_TEXT);
        $values["ufFactuurStatus"] =  MySQL::SQLValue($factuurStatus, MySQL::SQLVALUE_TEXT);

        $values["ufVentilatie"] =  MySQL::SQLValue($ventilatie, MySQL::SQLVALUE_TEXT);

        $values["ufMaatstafTotaal"] =  MySQL::SQLValue($maatstafTotaal, MySQL::SQLVALUE_NUMBER);
        $values["ufBTWTotaal"] =  MySQL::SQLValue($BTWTotaal, MySQL::SQLVALUE_NUMBER);
        $values["ufFactuurTotaal"] =  MySQL::SQLValue($factuurTotaal, MySQL::SQLVALUE_NUMBER);

        $values["ufBetaalBedragTotaal"] =  MySQL::SQLValue($betaalBedragTotaal, MySQL::SQLVALUE_NUMBER);

        $where["ufId"] =  MySQL::SQLValue($pUitgaandeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_uf_uitgaande_facturen", $values, $where);

        // ---------------------
        // MAIL onderwerp & body
        // ---------------------

        if ($pModus == '*ADD')
            self::FillUitgaandeFactuurMailInfo($pUitgaandeFactuur);

        // -------------
        // Einde functie
        // -------------

        return;

    }

    // ========================================================================================
    //  Uitgaande Factuur - Wissen onderliggende records
    //
    // In:	Uitgaande Factuur
    //
    // Uit: *OK of Reden niet gewist
    //
    // ========================================================================================

    static function ChkDeleteUitgaandeFactuur($pUitgaandeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ufRec =  SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        $factuurnummer =  $ufRec->ufFactuurnummer;

        // -------------
        // Wissen detail
        // -------------

        $sqlStat = "select count(*) as aantal from efin_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur";
        $db->Query($sqlStat);

        if ($udRec = $db->Row())
            if ($udRec->aantal > 0)
                return "Factuur $factuurnummer kan niet gewist worden, er zijn nog detail lijnen";

        // -------------
        // Einde functie
        // -------------

        return "*OK";

    }

    // ========================================================================================
    //  Uitgaande Factuur - Bijwerken totalen en Status
    //
    // In:	Uitgaande Factuur Detail ID
    //      Modus (*ADD, *UPDATE)
    //
    // ========================================================================================

    static function UpdUitgaandeFactuurDetail($pUitgaandeFactuurDetail, $pModus = "*UPDATE") {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $udRec = SSP_db::Get_EFIN_udRec($pUitgaandeFactuurDetail);
        $ufRec = SSP_db::Get_EFIN_ufRec($udRec->udFactuur);
        $klrec = SSP_db::Get_EFIN_klRec($ufRec->ufKlant);

        // ------
        // Wissen
        // ------

        if ($udRec->udDelete){

            $sqlStat = "Delete From efin_ud_uitgaande_factuur_detail where udId = $pUitgaandeFactuurDetail";
            $db->Query($sqlStat);

            self::UpdUitgaandeFactuur($ufRec->ufId);

            return;

        }

        // ------------
        // BTW-bedragen
        // ------------

        $BTWBedrag = null;
        $BTWCode = null;

        $BTWCodeDefault = $klrec->klBTWTarief;

        if (! $BTWCodeDefault)
            $BTWCodeDefault = "3"; // 21%

        $BTWCode = $udRec->udBTWCode;
        if ($BTWCode < "0" or $BTWCode > "9")
            $BTWCode = $BTWCodeDefault;

        $taRec = SSP_db::Get_SX_taRec("EFIN_BTW_TARIEVEN", $BTWCode);
        $percentage = $taRec->taNumData;

        $BTWBedrag = $udRec->udBedragMaatstaf * ($percentage / 100);
        $BTWBedrag = round($BTWBedrag , 2);
        $BTWCode = $BTWCode;

        // ------
        // UPDATE
        // ------

        $values = array();
        $where = array();

        $values["udBTWCode"] =  MySQL::SQLValue($BTWCode, MySQL::SQLVALUE_TEXT);
        $values["udBTWBedrag"] =  MySQL::SQLValue($BTWBedrag, MySQL::SQLVALUE_NUMBER);

        $where["udId"] =  MySQL::SQLValue($pUitgaandeFactuurDetail, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_ud_uitgaande_factuur_detail", $values, $where);

        // ------------------
        // Update header info
        // ------------------

        self::UpdUitgaandeFactuur($ufRec->ufId);


        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Check of rapport-code toegewezen is aan minstens één ventilatie-rekening
    //
    // In:	Rapport-code
    //
    // Out: Toegewezen? true/false
    //
    // ========================================================================================

    static function ChkRapportCodeToegewezen($pCode){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select count(*) as aantal from efin_vr_ventilatie_rekeningen where vrCode = '$pCode'";
        $db->Query($sqlStat);

        if ($vrRec = $db->Row() and $vrRec->aantal >= 1)
            return true;
        else
            return false;

    }

    // ========================================================================================
    //  Check of ventilatie-categorie toegewezen is aan minstens één ventilatie-rekening
    //
    // In:	Ventilatie Categorie (Code)
    //
    // Out: Toegewezen? true/false
    //
    // ========================================================================================

    static function ChkVentilatieCategorieToegewezen($pVentilatieCategorieCode){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select count(*) as aantal from efin_vr_ventilatie_rekeningen where vrCategorie = '$pVentilatieCategorieCode'";
        $db->Query($sqlStat);

        if ($vrRec = $db->Row() and $vrRec->aantal >= 1)
            return true;
        else
            return false;

    }

    // ===================================================================================================
    // Functie: Create Uitgaande Factuur PDF
    //
    // In:	Uitgaande Factuur ID
    //      Modus (file, display)
    //
    // Out: Path (als modus = 'file')
    //
    // ===================================================================================================

    static function CrtUitgaandeFactuurPDF($pUitgaandeFactuur, $pModus = 'file') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.uitgaande_factuur.class"));
        include_once(SX::GetSxClassPath("tools.class"));

        // ------------------------------
        // Ophalen uitgaande factuur info
        // ------------------------------

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return "";

        $klRec = SSP_db::Get_EFIN_klRec($ufRec->ufKlant);

        if (! $klRec)
            return "";

        $ftRec = SSP_db::Get_EFIN_ftRec($ufRec->ufFactuurtype);

        if (! $ftRec->ftDocumentType)
            $documentType = '*FACTUUR';
        else
            $documentType = $ftRec->ftDocumentType;

        // -------------------
        // Create PDF-document
        // -------------------

        $uitgaandeFactuur = new UitgaandeFactuur(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set margins
        $uitgaandeFactuur->SetMargins(PDF_MARGIN_LEFT, 42, PDF_MARGIN_RIGHT);
        $uitgaandeFactuur->SetHeaderMargin(5);
        $uitgaandeFactuur->SetFooterMargin(5);
        $uitgaandeFactuur->SetAuthor('Schelle Sport');
        $uitgaandeFactuur->SetTitle('Uitgaande Factuur - Schelle Sport');

        $uitgaandeFactuur->documentType = $documentType;

        $logoVoetbal = $_SESSION["SX_BASEPATH"] . sx::GetSiteImgPath('logo_voetbal_klein.jpg');
        $logoTennis = $_SESSION["SX_BASEPATH"] . sx::GetSiteImgPath('logo_tennis_klein.jpg');
        $uitgaandeFactuur->logoVoetbal = $logoVoetbal;
        $uitgaandeFactuur->logoTennis = $logoTennis;

        if ($documentType == '*KOSTENNOTA') {
            $uitgaandeFactuur->documentTitel = "KOSTENNOTA";
            $uitgaandeFactuur->labelFactuurnummer = "Documentnr:";
            $uitgaandeFactuur->labelFactuurdatum = "Datum:";
        }
        else {
            $uitgaandeFactuur->documentTitel = "FACTUUR";
            if ($ufRec->ufIsCreditnota)
                $uitgaandeFactuur->documentTitel = "CREDITNOTA";

            $uitgaandeFactuur->labelFactuurnummer = "Factuurnummer:";
            if ($ufRec->ufIsCreditnota)
                $uitgaandeFactuur->labelFactuurnummer = "Creditnota:";

            $uitgaandeFactuur->labelFactuurdatum = "Factuurdatum:";
            if ($ufRec->ufIsCreditnota)
                $uitgaandeFactuur->labelFactuurdatum = "Datum:";

        }

        $uitgaandeFactuur->klantNaam = utf8_encode($klRec->klNaam);
        $uitgaandeFactuur->klantAdres = utf8_encode($klRec->klAdres);
        $uitgaandeFactuur->klantGemeente = $klRec->klPostnr . " " . utf8_encode($klRec->klGemeente);
        $uitgaandeFactuur->klantLand = utf8_encode($klRec->klLand);
        $uitgaandeFactuur->klantBTWnr = $klRec->klBTW;

        $factuurDatum = new DateTime($ufRec->ufFactuurdatum);
        $uitgaandeFactuur->factuurDatum = $factuurDatum->format('d/m/Y');

        $uitgaandeFactuur->factuurNummer = $ufRec->ufFactuurnummer;

        $uitgaandeFactuur->omschrijving = utf8_encode($ufRec->ufOmschrijving);

        $uitgaandeFactuur->GM = $ufRec->ufBetaalMededelingGM;
        $uitgaandeFactuur->isCreditnota = $ufRec->ufIsCreditnota;

        if ($ufRec->ufBetaalOpRekening){

            $rkRec = SSP_db::Get_EFIN_rkRec($ufRec->ufBetaalOpRekening);
            $uitgaandeFactuur->IBAN = $rkRec->rkIBAN;

        }

        if ($ufRec->ufVervalDatum){

            $vervalDatum = new DateTime($ufRec->ufVervalDatum);
            $uitgaandeFactuur->vervalDatum = $vervalDatum->format('d/m/Y');

        } else
            $uitgaandeFactuur->vervalDatum = "Contante betaling";

        if ($ufRec->ufReedsBetaald)
            $uitgaandeFactuur->reedsBetaald = number_format($ufRec->ufReedsBetaald,2,',','.'). " EUR";
        else
            $uitgaandeFactuur->reedsBetaald = null;

        $uitgaandeFactuur->teBetalen = number_format($ufRec->ufFactuurTotaal,2,',','.') . " EUR";

        $maatstaffen = array();
        $btwPercentages = array();
        $btwBedragen = array();
        $mededelingen = array();
        $totalen = array();

        $totaalMaatstaf = 0;
        $totaalBTW = 0;
        $totaalTotaal = 0;

        $sqlStat = "Select * from efin_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur order by udSort";
        $db->Query($sqlStat);


        while ($udRec = $db->Row()) {

            $maatstaffen[] = "€ " . number_format($udRec->udBedragMaatstaf,2,',','.');
            $btwBedragen[] =  "€ " .number_format($udRec->udBTWBedrag, 2,',','.');

            $taRec = SSP_db::Get_SX_taRec("EFIN_BTW_TARIEVEN", $udRec->udBTWCode);
            $btwPercentages[] =  number_format($taRec->taNumData, 2,',','.');

            $totaal = $udRec->udBedragMaatstaf + $udRec->udBTWBedrag;
            $totalen[] =   "€ " . number_format($totaal, 2,',','.');

            $mededelingen[] = utf8_encode($udRec->udMededeling);

            $totaalMaatstaf += $udRec->udBedragMaatstaf;
            $totaalBTW +=  $udRec->udBTWBedrag;
            $totaalTotaal += $totaal;

        }

        $uitgaandeFactuur->maatstaffen = $maatstaffen;
        $uitgaandeFactuur->btwBedragen = $btwBedragen;
        $uitgaandeFactuur->btwPercentages = $btwPercentages;
        $uitgaandeFactuur->mededelingen = $mededelingen;
        $uitgaandeFactuur->totalen = $totalen;

        $uitgaandeFactuur->totaalMaatstaf =  "€ " . number_format($totaalMaatstaf, 2,',','.');
        $uitgaandeFactuur->totaalBTW =  "€ " . number_format($totaalBTW, 2,',','.');
        $uitgaandeFactuur->totaalTotaal =  "€ " . number_format($totaalTotaal, 2,',','.');

        $uitgaandeFactuur->AddPage();
        $uitgaandeFactuur->FactuurBody();

        // ------------------
        // Extra Omschrijving
        // ------------------

        $extraOmschrijving = utf8_encode(wordwrap($ufRec->ufExtraOmschrijving,120) . "\n");

        $array= explode("\n", trim($extraOmschrijving));
        $lineQty = count($array);

        if (trim($ufRec->ufExtraOmschrijving)) {
            
            if ($lineQty > 14) {
                $uitgaandeFactuur->AddPage();
                $uitgaandeFactuur->ExtraOmschrijving($extraOmschrijving, 40);
            } else
                $uitgaandeFactuur->ExtraOmschrijving($extraOmschrijving);
        }

        $docNaam = "Factuur_" . $ufRec->ufFactuurnummer . ".pdf";

        if ($pModus == 'display')
            $uitgaandeFactuur->Output($docNaam);

        if ($pModus == 'file') {

            $filePath = self::GetUitgaandeFactuurPath($pUitgaandeFactuur);
            $uitgaandeFactuur->Output($filePath, 'F');

        }


        // -------------
        // Einde functie
        // -------------

        if ($pModus == 'file')
            return $filePath;
        else
            return "";

    }


    // ===================================================================================================
    // Functie: Get file path Uitgaande Factuur
    //
    // In:	- Uitgaande Factuur ID
    //
    // Out: - OK?
    //
    // ===================================================================================================

    static function GetUitgaandeFactuurPath($pUitgaandeFactuur) {

        include_once(SX::GetClassPath("_db.class"));

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        $fileName = "";

        if ($ufRec) {

            $fileName = "factuur_" . $ufRec->ufFactuurnummer . "_" . $ufRec->ufFactuurdatum;

        }


        $rootDir = $_SESSION["SX_BASEPATH"];

        $filePath = $rootDir . '/_generated_files/efin/uitgaandeFacturen/' . $fileName . '.pdf';

        return $filePath;

    }

    // ===================================================================================================
    // Functie: Uitgaande Factuur - Opvullen default MAIL-body & onderwerp
    //
    // In:	Uitgaande Factuur ID
    //
    // ===================================================================================================

    static function FillUitgaandeFactuurMailInfo($pUitgaandeFactuur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);
        $klRec = SSP_db::Get_EFIN_klRec($ufRec->ufKlant);
        $ftRec = SSP_db::Get_EFIN_ftRec($ufRec->ufFactuurtype);

        // --------------
        // MAIL-onderwerp
        // --------------
        $factuurnummer = $ufRec->ufFactuurnummer;
        $onderwerp = "Uw Schelle Sport Factuur: $factuurnummer";

        // ----------
        // MAIL-$body
        // ----------

        $br = "\r\n";

        // Aanhef
        $aanHef = "Beste,";

        if ($klRec->klFactuurMailAanhef)
            $aanHef = $klRec->klFactuurMailAanhef;

        $body =  $aanHef;
        $body .= $br;
        $body .= $br;
        $body .= "In bijlage van deze e-mail vindt u uw factuur in PDF formaat.";
        $body .= $br;
        $body .= $br;
        $body .= "Wij hebben gekozen voor de elektronische facturatie omdat deze betrouwbaar, economisch en milieuvriendelijk is, zowel voor ons als voor u, de ontvanger.";
        $body .= $br;
        $body .= "Sinds 1 januari 2013 wordt een elektronische factuur gelijkgesteld aan een factuur op papier.";
        $body .= $br;
        $body .= $br;
        $body .= "Aarzel niet ons te contacteren als u meer informatie wenst over deze factuur.";
        $body .= $br;
        $body .= $br;
        $body .= "Sportieve Groet,";
        $body .= $br;
        $body .= $br;
        $body .= "Schelle Sport";

        $values = array();
        $where = array();

        // --------
        // MAil naar
        // ---------

        $mailNaar = $ufRec->ufMailNaar;
        if (! $mailNaar)
            $mailNaar = $klRec->klFactuurMail;

        // -------
        // Mail CC
        // -------

        $mailNaarCC1 = $ufRec->ufMailNaarCC1;
        $mailNaarCC2 = $ufRec->ufMailNaarCC2;
        $mailNaarCC3 = $ufRec->ufMailNaarCC3;

        if ($ftRec->ftMailCC) {

            if (($ftRec->ftMailCC != $mailNaarCC1) and ($ftRec->ftMailCC != $mailNaarCC2) and ($ftRec->ftMailCC != $mailNaarCC3) ){


                if (! $mailNaarCC1)
                    $mailNaarCC1 = $ftRec->ftMailCC;
                elseif (! $mailNaarCC2 )
                    $mailNaarCC2 = $ftRec->ftMailCC;
                elseif (! $mailNaarCC3 )
                    $mailNaarCC3 = $ftRec->ftMailCC;

            }

        }

        // -----
        // UPDATE
        // ------

        $values["ufMailNaar"] =  MySQL::SQLValue($mailNaar);

        $values["ufMailNaarCC1"] =  MySQL::SQLValue($mailNaarCC1);
        $values["ufMailNaarCC2"] =  MySQL::SQLValue($mailNaarCC2);
        $values["ufMailNaarCC3"] =  MySQL::SQLValue($mailNaarCC3);

        if (! $ufRec->ufMailOnderwerp)
            $values["ufMailOnderwerp"] =  MySQL::SQLValue($onderwerp);


        if (! $ufRec->ufMailBody)
        $values["ufMailBody"] =  MySQL::SQLValue($body);

        $where["ufId"] =  MySQL::SQLValue($pUitgaandeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_uf_uitgaande_facturen", $values, $where);

    }

    // ===================================================================================================
    // Functie: MAIL Uitgaande Factuur
    //
    // In:	Uitgaande Factuur ID
    //      User-id
    //
    // ===================================================================================================

    static function MailUitgaandeFactuur($pUitgaandeFactuur, $pUserId){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetSxClassPath("tools.class"));

        $ufRec = SSP_db::Get_EFIN_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return;

        // --------------------
        // Aanmaken factuur PDF
        // --------------------

        $factuurPath = self::CrtUitgaandeFactuurPDF($pUitgaandeFactuur, 'file');

        if (! $factuurPath)
            return;

        // ----------------------------
        // Eventuele bijlages meesturen
        // ----------------------------

        $bijlageNaam1 = "";
        $bijlageNaam2 = "";
        $bijlageNaam3 = "";

        $i = 0;

        if ($ufRec->ufBijlagenVoorKlant){

            $attachments = array();
            $attachmentNamen = array();

            $fileArray = json_decode($ufRec->ufBijlagenVoorKlant, true);

            $bijlage = $_SESSION["SX_BASEPATH"]. '/_files/efin/uitgaande_facturen/extern/' . basename($fileArray[0]["name"]);
            $bijlageNaam =  basename($fileArray[0]["usrName"]);

            $attachments[] = $bijlage;
            $attachmentNamen[] = $bijlageNaam;

            $i++;

            if ($i ==1)
                $bijlageNaam1 = $bijlageNaam;
            if ($i ==2)
                $bijlageNaam2 = $bijlageNaam;
            if ($i ==3)
                $bijlageNaam3 = $bijlageNaam;

        } else {
            $attachments = null;
            $attachmentNamen = null;
        }

        // -------------
        // Mail-adressen
        // -------------

        $mailTO = $ufRec->ufMailNaar;

        $mailCC = "";
        if ($ufRec->ufMailNaarCC1)
            $mailCC = $ufRec->ufMailNaarCC1;
        if ($ufRec->ufMailNaarCC2)
            $mailCC .= ";" . $ufRec->ufMailNaarCC2;
        if ($ufRec->ufMailNaarCC3)
            $mailCC .= ";" . $ufRec->ufMailNaarCC3;

        $mailBCC = "gvh@vecasoftware.com";

        $mailFROM = "secretariaat@schellesport.be";
        $MailFromNAME = "Schelle Sport";

        $mailONDERWERP = $ufRec->ufMailOnderwerp;

        $mailBODY = "<html><body>";
        $mailBODY .= nl2br($ufRec->ufMailBody);
        $mailBODY .= "</body></html>";

        $mailBODY = utf8_encode($mailBODY);

        SX_tools::SendMail($mailONDERWERP, $mailBODY, $mailTO, $mailBCC, $mailFROM, $MailFromNAME, $factuurPath, 'UTF-8', $mailCC , '', $attachments, $attachmentNamen);

        // ---
        // LOG
        // ---

        $factuurNaam = basename($factuurPath);
        $curDateTime = date('Y-m-d H:i:s');

        $values = array();

        $values["fmFactuur"] = MySQL::SQLValue($pUitgaandeFactuur, MySQL::SQLVALUE_NUMBER);

        $values["fmMailNaar"] = MySQL::SQLValue($mailTO);
        $values["fmMailCC"] = MySQL::SQLValue($mailCC);
        $values["fmMailBCC"] = MySQL::SQLValue($mailBCC);

        $values["fmMailOnderwerp"] = MySQL::SQLValue($mailONDERWERP);
        $values["fmMailBody"] = MySQL::SQLValue($mailBODY);

        $values["fmMailBijlage1"] = MySQL::SQLValue($factuurNaam);
        $values["fmMailBijlage2"] = MySQL::SQLValue($bijlageNaam1);
        $values["fmMailBijlage3"] = MySQL::SQLValue($bijlageNaam2);
        $values["fmMailBijlage4"] = MySQL::SQLValue($bijlageNaam3);

        $values["fmDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["fmUserCreatie"] = MySQL::SQLValue($pUserId);

        $id = $db->InsertRow("efin_fm_factuur_mail_log", $values);

        // -------------------------------
        // Update "aantal mails verstuurd"
        // -------------------------------

        $sqlStat = "Select count(*) as aantal from efin_fm_factuur_mail_log where fmFactuur = $pUitgaandeFactuur";
        $db->Query($sqlStat);
        $fmRec = $db->Row();
        $aantalMails = $fmRec->aantal;

        $sqlStat = "Update efin_uf_uitgaande_facturen set ufMailOpnieuwSturen = 0, ufAantalMails = $aantalMails where ufId = $pUitgaandeFactuur";
        $db->Query($sqlStat);


    }

    // ===================================================================================================
    // Functie: Ophalen rekening transfer-ventilatie
    //
    // In:  Rekening
    //
    // Uit: Ventilatie-rekening "transfer"
    //
    // ===================================================================================================

    static function GetRekeningTransferVentilatie($pRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rv_rekening_ventilatierekeningen where rvRekening = $pRekening and rvType = '*TRANSFER'";

        $db->Query($sqlStat);

        if ($rvRec = $db->Row())
            return $rvRec->rvVentilatieRekening;

        return 0;

    }

    // ===================================================================================================
    // Functie: Aanmaken KAS boeking
    //
    // In:	User-ID
    //      Rekening
    //      Datum
    //      Bedrag
    //      Medeling
    //      Ventilatie
    //      Kas-rekening (transfer)
    //      Oorsprong
    //      Link
    //
    //
    // ===================================================================================================

    static function CrtKasBoeking($pUserId, $pRekening, $pDatum, $pBedrag, $pMedeling, $pVentilatie = 0, $pKasTransfer = 0, $pOorsprong = '*MANUEEL', $pLink = 0){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rkRec = SSP_db::Get_EFIN_rkRec($pRekening);

        $mededeling = $pMedeling;
        $isTransfer = false;

        if ($pKasTransfer and ($pKasTransfer != $pRekening)){

            $rkRec2 = SSP_db::Get_EFIN_rkRec($pKasTransfer);

            $kasVan = $rkRec->rkNaam;
            $kasNaar = $rkRec2->rkNaam;

            $mededeling = "Transfer $kasVan >> $kasNaar ";

            $isTransfer = true;

        }

        // ------------------------
        // Aanmaken rekening-detail
        // ------------------------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();

        $volgnummer = self::GetVolgendKasVolgnr($pRekening, $pDatum);
        $valuta = $rkRec->rkValuta;

        $values["rdRekening"] = MySQL::SQLValue($pRekening, MySQL::SQLVALUE_NUMBER);
        $values["rdVolgnummer"] = MySQL::SQLValue($volgnummer);
        $values["rdDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdRefDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
        $values["rdValuta"] = MySQL::SQLValue($valuta);
        $values["rdMededeling"] = MySQL::SQLValue($mededeling);

        $values['rdStatusToewijzen'] = MySQL::SQLValue('*NIET');
        $values['rdStatusDoorboeken'] = MySQL::SQLValue('*NIET');

        $values["rdOorsprong"] = MySQL::SQLValue($pOorsprong);

        if ($pLink)
            $values["rdLink"] = MySQL::SQLValue($pLink, MySQL::SQLVALUE_NUMBER);

        $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserCreatie"] = MySQL::SQLValue($pUserId);
        $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["rdUserUpdate"] = MySQL::SQLValue($pUserId);
        $values["rdRecStatus"] = MySQL::SQLValue('A');

        $rekeningDetail = $db->InsertRow("efin_rd_rekening_details", $values);

        // --------------------------------
        // Aanmaken "eenvoudige" ventilatie
        // --------------------------------

        if ($pVentilatie and (! $isTransfer))
            self::CrtEenvoudigeVentilatie($pUserId, $rekeningDetail, $pVentilatie);

        // ----------
        // Doorboeken
        // ----------

        if ($pKasTransfer and ($pKasTransfer != $pRekening)){

            $ventilateTransfer = self::GetRekeningTransferVentilatie($pRekening);

            if ($ventilateTransfer){

                self::CrtEenvoudigeVentilatie($pUserId, $rekeningDetail, $ventilateTransfer);

                // -------------------------------
                // Tegenboeking (enkel indien KAS)
                // -------------------------------

                $rkRec = SSP_db::Get_EFIN_rkRec($pKasTransfer);

                if ($rkRec->rkRekeningType == '*KAS') {

                    $bedrag = $pBedrag * -1;
                    self::CrtKasBoeking($pUserId, $pKasTransfer, $pDatum, $bedrag, $mededeling, $ventilateTransfer,0,$pOorsprong,$pLink);

                    self::FillRekeningDetailSaldi($pKasTransfer);

                }
            }

        }

        // -------------
        // Einde functie
        // -------------

        return;

    }


    // ========================================================================================
    //  Sponsor Facturatie Schema - Bijwerken totalen
    //
    // In:	Sponsor Facturatie Schema ID
    //
    // ========================================================================================

    static function UpdSponsorFacturatieSchema($pSponsorFacturatieSchema) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $sfRec = SSP_db::Get_EFIN_sfRec($pSponsorFacturatieSchema);

        $sponsorDossier = $sfRec->sfDossier;
        $sdRec = SSP_db::Get_EFIN_sdRec($sponsorDossier);

        $omschrijving =  $sfRec->sfOmschrijving;
        if (! $omschrijving)
            $omschrijving = $sdRec->sdFactuurOmschrijving;

        $extraOmschrijving =  $sfRec->sfExtraOmschrijving;
        if (! $extraOmschrijving)
            $extraOmschrijving = $sdRec->sdFactuurExtraOmschrijving;

        // ----------
        // BTW-bedrag
        // ----------

        $BTWCode = $sfRec->sfBTWCode;

        $taRec = SSP_db::Get_SX_taRec("EFIN_BTW_TARIEVEN", $BTWCode);
        $percentage = $taRec->taNumData;

        $BTWBedrag = $sfRec->sfBedragMaatstaf * ($percentage / 100);
        $BTWBedrag = round($BTWBedrag , 2);

        // ---------------------------------------
        // Status op "gefactureerd" indien factuur
        // ---------------------------------------

        $status = $sfRec->sfStatus;

        if ($sfRec->sfUitgaandeFactuur)
            $status = '*GEFACTUREERD';

        // ------
        // UPDATE
        // ------

        $values = array();
        $where = array();

        $values["sfBTWBedrag"] =  MySQL::SQLValue($BTWBedrag, MySQL::SQLVALUE_NUMBER);

        $values["sfOmschrijving"] =  MySQL::SQLValue($omschrijving, MySQL::SQLVALUE_TEXT);
        $values["sfExtraOmschrijving"] =  MySQL::SQLValue($extraOmschrijving, MySQL::SQLVALUE_TEXT);

        $values["sfStatus"] =  MySQL::SQLValue($status, MySQL::SQLVALUE_TEXT);

        $where["sfId"] =  MySQL::SQLValue($pSponsorFacturatieSchema, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_sf_sponsor_facturatie_schema", $values, $where);

        // --------------
        // Update dossier
        // --------------

        self::UpdSponsorDossier($sponsorDossier);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Sponsor Dossier - Bijwerken
    //
    // In:	Sponsor Dossier ID
    //
    // ========================================================================================

    static function UpdSponsorDossier($pSponsorDossier) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetSxClassPath("tools.class"));
        include_once(Sx::GetClassPath("settings.class"));

        $green = SSP_settings::GetBackgroundColor('green');
        $yellow = SSP_settings::GetBackgroundColor('yellow');
        $red = SSP_settings::GetBackgroundColor('red');
        $orange = SSP_settings::GetBackgroundColor('orange');
        $blue = SSP_settings::GetBackgroundColor('blue');


        $sdRec = SSP_db::Get_EFIN_sdRec($pSponsorDossier);

        if (! $sdRec)
            return;

        $seid = $_SESSION["SEID"];
        if (!$seid)
            $seid = "NVT";

        // --------------------
        // HTML Tegenprestaties
        // --------------------

        $tegenprestatiesHTML = null;

        $sqlStat = "Select * from efin_st_sponsor_tegenprestaties where stDossier = $pSponsorDossier order by stSort, stId";
        $db->Query($sqlStat);

        while ($stRec = $db->Row()){

            if (! $tegenprestatiesHTML)
                $tegenprestatiesHTML = "<ul style='padding-left: 10px'>";

            $tegenprestatieId = $stRec->stTegenprestatie;

            $tpRec = SSP_db::Get_EFIN_tpRec($tegenprestatieId);
            if (! $tpRec)
                continue;

            $tegenprestatie = $tpRec->tpNaam;

            if ($stRec->stInfo) {
                $info = $stRec->stInfo;
                $tegenprestatie .= "&nbsp;[$info]";
            }
            if ($stRec->stExtraInfo) {
                $extraInfo = $stRec->stExtraInfo;
                $tegenprestatie .= "&nbsp;<span title='$extraInfo' style='color: red'>extra Info</span>";
            }
            $tegenprestatiesHTML .= "<li>$tegenprestatie</li>";

        }

        if ($tegenprestatiesHTML)
            $tegenprestatiesHTML .= "</ul>";

        // ----------------------
        // HTML Facturatie-schema
        // ----------------------

        $facturatieSchemaHTML = null;

        $sqlStat = "Select *, case when sfStreefDatum <= curdate() then '*VERLEDEN' else '*TOEKOMST' end as verledenToekomst from efin_sf_sponsor_facturatie_schema where sfDossier = $pSponsorDossier order by sfStreefDatum";
        $db->Query($sqlStat);

        while ($sfRec = $db->Row()){

            if (! $facturatieSchemaHTML)
                $facturatieSchemaHTML = "<ul style='padding-left: 10px'>";

            $datum = SX_tools::EdtDate($sfRec->sfStreefDatum, '%d/%m/%y');
            $bedragExBTW = floatval($sfRec->sfBedragMaatstaf );
            $bedragInclBTW = floatval($sfRec->sfBedragMaatstaf +  $sfRec->sfBTWBedrag);

            if ($sfRec->verledenToekomst == "*VERLEDEN")
                $inVerleden = true;
            else
                $inVerleden = false;

            $euro =  chr(128);

            $statusCode = $sfRec->sfStatus;
            $taRec = SSP_db::Get_SX_taRec('EFIN_SPONSOR_FACTSTATUS', $statusCode);
            $factuurStatus = $taRec->taName;

            $facturatieSchema = "$datum: $euro $bedragExBTW - $factuurStatus";

            if ($statusCode == '*GEFACTUREERD'){

                if ($sfRec->sfUitgaandeFactuur){

                    $factuurId = $sfRec->sfUitgaandeFactuur;
                    $ufRec = SSP_db::Get_EFIN_ufRec($factuurId);

                    if ($ufRec) {

                        $ufId = $ufRec->ufId;
                        $factuurnummer = $ufRec->ufFactuurnummer;

                        if ($ufRec->ufFactuurStatus == '*BETAALD')
                            $factuurnummer .= ' (BETAALD)';
                        if ($ufRec->ufFactuurStatus == '*BETAALD_VC')
                            $factuurnummer .= ' (GECREDITEERD)';
                        if ($ufRec->ufFactuurStatus == '*BETAALD_VF')
                            $factuurnummer .= ' (BETAALD - via FACTUUR)';

                        $factuurPath = "/efin_uitgaande_factuur.php?seid=NVT&ufid=$ufId";
                        $htmlUF = "<a href='$factuurPath' target='_blank'>$factuurnummer</a>";


                        $facturatieSchema .= " - $htmlUF";
                    }

                }


            }

            $backgroundColor = "";

            if ($statusCode == '*GEFACTUREERD')
                $backgroundColor = $green;
            if ($statusCode == '*OPEN')
                $backgroundColor = $blue;

            if (($statusCode == "*OPEN") and $inVerleden and $sfRec->sfBedragMaatstaf > 0)
                $backgroundColor = $yellow;

            $facturatieSchema = "<div style='background-color: $backgroundColor; padding-left: 5px'>$facturatieSchema</div>";

            $facturatieSchemaHTML .= "<li>$facturatieSchema</li>";

        }

        if ($facturatieSchemaHTML)
            $facturatieSchemaHTML .= "</ul>";

        if ((! $facturatieSchema) and ($sdRec->sdGratis == 1)) {
            $facturatieSchemaHTML = "<b>GRATIS</b>";

            if ($sdRec->sdGratisReden)
                $facturatieSchemaHTML = $facturatieSchemaHTML . "<br/>" . nl2br($sdRec->sdGratisReden);

        }


        if (! $facturatieSchemaHTML)
            $facturatieSchemaHTML = "<div style='background-color: $orange; padding-left: 5px; padding-top: 10px; padding-bottom: 10px; font-weight: bold'>NOG IN TE GEVEN</div>";

        // --------------
        // Dossier "naam"
        // --------------

        $naam = "Dossier X";

        $klRec = SSP_db::Get_EFIN_klRec($sdRec->sdKlant);

        if ($klRec){

            $naam = $klRec->klNaam;

            $datumVan = SX_tools::EdtDate($sdRec->sdStart, "%d/%m/%y");
            $datumTot = SX_tools::EdtDate($sdRec->sdEinde, "%d/%m/%y");
            $naam = $naam . " ($datumVan - $datumTot)";

        }

        // ------------------
        // Facturatie nodig ?
        // ------------------

        $facturatieNodig = 0;

        $sqlStat = "Select count(*) as aantal from efin_sf_sponsor_facturatie_schema where sfDossier = $pSponsorDossier and sfStatus = '*OPEN' and sfStreefDatum <= current_date() and sfBedragMaatstaf > 0";
        $db->Query($sqlStat);
        $sfRec = $db->Row();

        if ($sfRec->aantal >= 1)
            $facturatieNodig = 1;

        // ------
        // UPDATE
        // ------

        $values = array();
        $where = array();

        $values["sdTegenprestatiesHTML"] =  MySQL::SQLValue($tegenprestatiesHTML,  MySQL::SQLVALUE_TEXT);
        $values["sdFacturatieSchemaHTML"] =  MySQL::SQLValue($facturatieSchemaHTML,  MySQL::SQLVALUE_TEXT);
        $values["sdNaam"] =  MySQL::SQLValue($naam,  MySQL::SQLVALUE_TEXT);
        $values["sdFacturatieNodig"] =  MySQL::SQLValue($facturatieNodig,  MySQL::SQLVALUE_NUMBER);

        $where["sdId"] =  MySQL::SQLValue($pSponsorDossier, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_sd_sponsor_dossiers", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Sponsor Facturatatie-schema; Ophalen HTML snippet uitgaande factuur
    //
    // In:	ID Facturatue Schema
    //
    // ========================================================================================

    static function GetSponsorFacturatieSchemaFacfuurHTML($pFacturatieSchema){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from efin_sf_sponsor_facturatie_schema where sfId = $pFacturatieSchema";
        $db->Query($sqlStat);

        $sfRec = $db->Row();

        if (! $sfRec)
            return "ERROR $pFacturatieSchema" ;

        if (! $sfRec->sfUitgaandeFactuur)
            return "&nbsp;";

        $uitgaandeFactuur = $sfRec->sfUitgaandeFactuur;
        $ufRec = SSP_db::Get_EFIN_ufRec($uitgaandeFactuur);

        if (! $ufRec)
            return "FACTUUR GEWIST";

        $factuurnummer = $ufRec->ufFactuurnummer;

        $seid = $_SESSION["SEID"];
        if (!$seid)
            $seid = "NVT";

        if ($ufRec->ufFactuurStatus == '*BETAALD')
            $factuurnummer .= ' (BETAALD)';
        if ($ufRec->ufFactuurStatus == '*BETAALD_VC')
            $factuurnummer .= ' (GECREDITEERD)';
        if ($ufRec->ufFactuurStatus == '*BETAALD_VF')
            $factuurnummer .= ' (BETAALD - VIA FACTUUR)';

        $factuurPath = "/efin_uitgaande_factuur.php?seid=$seid&ufid=$uitgaandeFactuur";
        $html = "<a href='$factuurPath' target='_blank'>$factuurnummer</a>";

        // -------------
        // Einde functie
        // -------------

        return $html;

    }


    // ========================================================================================
    //  Sponsor Dossier - Bijwerken alle dossiers
    //
    // ========================================================================================

    static function UpdAlleSponsorDossiers() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_sd_sponsor_dossiers";
        $db->Query($sqlStat);

        while ($sdRec = $db->Row())
            self::UpdSponsorDossier($sdRec->sdId);


        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    //  Klant - Mag gewist worden?
    //
    // In:	Klant ID
    //
    // uit: *OK of foutbericht
    //
    // ========================================================================================

    static function ChkDeleteKlant($pKlant){


        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ---------------------------
        // Niet als uitgaande facturen
        // ---------------------------

        $sqlStat = "Select count(*) as aantal from efin_uf_uitgaande_facturen where ufKlant = $pKlant";
        $db->Query($sqlStat);

        if ($ufRec = $db->Row())
            if ($ufRec->aantal > 0)
                return "Wissen niet mogelijk - er bestaat minstens 1 uitgaande factuur voor deze klant";

        // ------------------------
        // Niet als sponsor dossier
        // ------------------------

        $sqlStat = "Select count(*) as aantal from efin_sd_sponsor_dossiers where sdKlant = $pKlant";
        $db->Query($sqlStat);

        if ($klRec = $db->Row())
            if ($klRec->aantal > 0)
                return "Wissen niet mogelijk - er bestaan minstens 1 sponsor-dossiers voor deze klant";

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    //  Sponsor Dossier - Mag gewist worden?
    //
    // In:	Sponsor Dossier ID
    //
    // uit: *OK of foutbericht
    //
    // ========================================================================================

    static function ChkDeleteSponsorDossier($pSponsorDossier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ---------------------
        // Geen  tegenprestaties
        // ---------------------

        $sqlStat = "Select count(*) as aantal from efin_st_sponsor_tegenprestaties where stDossier = $pSponsorDossier";
        $db->Query($sqlStat);

        if ($stRec = $db->Row())
            if ($stRec->aantal > 0)
                return "Wissen niet mogelijk - er zijn tegenprestaties";

        // ----------------------
        // Geen facturatie-schema
        // -----------------------

        $sqlStat = "Select count(*) as aantal from efin_sf_sponsor_facturatie_schema where sfDossier = $pSponsorDossier";
        $db->Query($sqlStat);

        if ($sfRec = $db->Row())
            if ($sfRec->aantal > 0)
                return "Wissen niet mogelijk - er is een facturatie-schema";

        // -----------------------
        // Geen uitgaande facturen
        // -----------------------

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }


    // ========================================================================================
    //  Aanmaken Sponsor Facturen
    //
    //  In:     Dossier
    //          User-id
    //
    //
    //  Uit:    # aangemaakte facturen
    // ========================================================================================

    static function CrtSponsorFacturen($pDossier, $pUserId) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from efin_sf_sponsor_facturatie_schema where sfStatus = '*OPEN' and sfDossier = $pDossier and sfStreefDatum <= current_date()";

        $db->Query($sqlStat);

        $aantal = 0;

        while ($sfRec = $db->Row()){

            $created = self::CrtSponsorFactuur($sfRec->sfId, $pUserId);

            if ($created)
                $aantal += 1;

        }

        self::UpdSponsorDossier($pDossier);

        // -------------
        // Einde functie
        // -------------

        return $aantal;

    }

    // ========================================================================================
    //  Aanmaken Sponsor Factuur
    //
    //  In: Sponsor Facturatie Schema ID
    //
    //  Uit: Created? true/false
    // ========================================================================================

    static function CrtSponsorFactuur($pSponsorFacturatieSchema, $pUserId = '*AUTO'){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $sfRec = SSP_db::Get_EFIN_sfRec($pSponsorFacturatieSchema);

        if (! $sfRec)
            return false;

        if ($sfRec->sfStatus != '*OPEN')
            return false;

        $dossier = $sfRec->sfDossier;

        $sdRec = SSP_db::Get_EFIN_sdRec($dossier);

        if (! $sdRec)
            return false;

        $klant = $sdRec->sdKlant;

        $klRec = SSP_db::Get_EFIN_klRec($klant);

        if (! $klRec)
            return false;

        // -----------------------
        // Aanmaken factuur-header
        // -----------------------


        $omschrijving = $sfRec->sfOmschrijving;
        $extraOmschrijving = $sfRec->sfExtraOmschrijving;

        if (! $omschrijving)
            $omschrijving = $sdRec->sdFactuurOmschrijving;

        if (! $extraOmschrijving )
            $extraOmschrijving = $sdRec->sdFactuurExtraOmschrijving;

        $betaalOpRekening = $klRec->klBetaalVia;

        if (! $betaalOpRekening)
            $betaalOpRekening = 9; // TENNIS

        $curDate = date('Y-m-d');
        $curDateTime = date('Y-m-d H:i:s');

        $values = array();

        $values["ufKlant"] = MySQL::SQLValue($klant, MySQL::SQLVALUE_NUMBER);
        $values["ufSponsorDossier"] = MySQL::SQLValue($dossier, MySQL::SQLVALUE_NUMBER);
        $values["ufFactuurtype"] = MySQL::SQLValue(2, MySQL::SQLVALUE_NUMBER);
        $values["ufFactuurdatum"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);

        $values["ufOmschrijving"] = MySQL::SQLValue($omschrijving, MySQL::SQLVALUE_TEXT);
        $values["ufExtraOmschrijving"] = MySQL::SQLValue($extraOmschrijving, MySQL::SQLVALUE_TEXT);

        $values["ufBetaalOpRekening"] = MySQL::SQLValue($betaalOpRekening, MySQL::SQLVALUE_TEXT);

        $values["ufDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["ufDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["ufUserCreatie"] = MySQL::SQLValue($pUserId, MySQL::SQLVALUE_TEXT);
        $values["ufUserUpdate"] = MySQL::SQLValue($pUserId, MySQL::SQLVALUE_TEXT);

        $factuur = $db->InsertRow("efin_uf_uitgaande_facturen", $values);

        // ----------------------
        // Aanmaken factuur-detail
        // ----------------------

        $values = array();

        $bedragMaatstaf = $sfRec->sfBedragMaatstaf;
        $btwCode = $sfRec->sfBTWCode;
        $ventilatieRekening = $sdRec->sdVentilatieRekening;

        $values["udFactuur"] = MySQL::SQLValue($factuur, MySQL::SQLVALUE_NUMBER);
        $values["udMededeling"] = MySQL::SQLValue("Sponsoring", MySQL::SQLVALUE_TEXT);
        $values["udBedragMaatstaf"] = MySQL::SQLValue($bedragMaatstaf, MySQL::SQLVALUE_NUMBER);
        $values["udBTWCode"] = MySQL::SQLValue($btwCode, MySQL::SQLVALUE_TEXT);
        $values["udVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);
        $values["udSort"] = MySQL::SQLValue(10, MySQL::SQLVALUE_NUMBER);

        $values["udDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["udDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["udUserCreatie"] = MySQL::SQLValue($pUserId, MySQL::SQLVALUE_TEXT);
        $values["udUserUpdate"] = MySQL::SQLValue($pUserId, MySQL::SQLVALUE_TEXT);

        $detail = $db->InsertRow("efin_ud_uitgaande_factuur_detail", $values);

        self::UpdUitgaandeFactuurDetail($detail, '*ADD');

        self::UpdUitgaandeFactuur($factuur, '*ADD');

        // -------------------------
        // Set Vervaldatum (+ 1 maand)
        // ---------------------------

        $sqlStat = "Update efin_uf_uitgaande_facturen set ufVervalDatum = DATE_ADD(ufFactuurdatum, INTERVAL 1 MONTH) where ufId = $factuur";
        $db->Query($sqlStat);

        // ----------------------
        // Update Sponsor-dossier
        // ---------------------

        $sqlStat = "Update efin_sf_sponsor_facturatie_schema set sfStatus = '*GEFACTUREERD', sfUitgaandeFactuur = $factuur where sfId = $pSponsorFacturatieSchema";
        $db->Query($sqlStat);

        self::UpdSponsorFacturatieSchema($pSponsorFacturatieSchema);
        self::UpdSponsorDossier($dossier);

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    //  Ophalen sponsordossiers "te factureren"
    //
    //  Uit: array met sponsordossiers (null indien geen dossiers)
    // ========================================================================================

    static function GetSponsorDossiersKlaarTeFactureren() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select distinct(sfDossier) as dossier from efin_sf_sponsor_facturatie_schema where sfStatus = '*OPEN' and sfStreefDatum <= current_date() order by sfDossier";
        $db->Query($sqlStat);

        $dossiers = array();
        $aantal = 0;

        while ($sfRec = $db->Row()) {
            $dossiers[] = $sfRec->dossier;
            $aantal++;
        }

        if ($aantal == 0)
            $dossiers = null;

        // -------------
        // Einde functie
        // -------------

        return $dossiers;

    }


    // ========================================================================================
    //  Ophalen volgende analytische sequence
    //
    //  In: sequence (bv . 2.1.1)
    //      Level (0,1,2)
    //
    //  Uit: Volgende sequence
    // ========================================================================================

    static function GetNxtAnaSeq($pSequence, $pLevel){

        $sequences = explode('.', $pSequence);

        if ($pLevel == 0){

            $sequenceDeel = $sequences[0];
            $sequenceDeel = self::GetNxtAnaSeqDeel($sequenceDeel);
            return $sequenceDeel;

        }

        if ($pLevel == 1){

            $sequenceDeel = $sequences[1];
            $sequenceDeel = self::GetNxtAnaSeqDeel($sequenceDeel);
            return $sequences[0] . ".$sequenceDeel";
        }

        if ($pLevel == 2){

            $sequenceDeel = $sequences[2];
            $sequenceDeel = self::GetNxtAnaSeqDeel($sequenceDeel);
            return $sequences[0] . ".$sequences[1]" . ".$sequenceDeel";
        }

        // -------------
        // Einde functie
        // -------------


    }

    // ========================================================================================
    //  Ophalen volgende sequence deel (1 -> 2, 2 -> 3, 9 -> A, A - >B etc)
    //
    //  In: sequence (bv . 2.1.1)
    //      Level (0,1,2)
    //
    //  Uit: Volgende sequence
    // ========================================================================================

    static function GetNxtAnaSeqDeel($pSequenceDeel){

        $fromAr = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y");
        $toAr = array("2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

        $index = array_search($pSequenceDeel,$fromAr,true);

        if ($index !== false)
            $sequenceDeel = $toAr[$index];
        else
            $sequenceDeel = "1";

        // -------------
        // Einde functie
        // -------------

        return $sequenceDeel;

    }

    // ========================================================================================
    //  Zet datum laatste boeking van een bepaalde interface
    //
    //  In: Interface Code (bv. *TICKETING)
    //
    // ========================================================================================

    static function SetInterfaceDatumLaatsteBoeking($pInterfaceCode){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "update efin_xz_interfaces set efin_xz_interfaces.xzLaatsteBoeking = now() where xzCode = '$pInterfaceCode'";
        $db->Query($sqlStat);


    }


    // ========================================================================================
    //  Corrigeer toewijzing alle rekening-detail
     //
    // ========================================================================================

    static function BatchCorrectieToewijzingRD() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rd_rekening_details where rdDatum BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH) AND CURRENT_DATE";
        $db->Query($sqlStat);

        while ($rdRec = $db->Row()){

            self::SetRdStatusToewijzen($rdRec->rdId);
            self::SetRdStatusDoorboeken($rdRec->rdId);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ===================================================================================================
    // Functie: Sturen mail naar "factuur-afhandelaars"
    //
    // In:  Mailserver
    //      Mailbox
    //      Username
    //      Pass
    //      Folder (Opt, Dft= INBOX)
    //
    // Return: # Mails in mailbox (inbox)
    //
    // ===================================================================================================

    Static function SndMailNaarVerwerkersInkomendeFacturen($pMail1, $pMail2 = ""){

        include_once(SX::GetSxClassPath("tools.class"));

        $aantal = SX_tools::CheckMailbox("imap.mailprotect.be", "facturen@schellesport.be", "club49club49");

        if (! $aantal)
            return;

        $mailBody  =    "Beste,<br/><br/>";
        $mailBody .=    "*** DIT IS EEN AUTOMATISCHE MAIL *** <br/><br/>";
        $mailBody .=    "Er staan $aantal mails in de INBOX van facturen@schellesport.be";

        $mailBody .=    "<br/><br/>";
        $mailBody .=    "Sportieve groet,<br/><br/>Webmaster<br/>Schelle Sport";

        SX_tools::SendMail("Schelle Sport - Mogelijk inkomende facturen", $mailBody, $pMail1, 'gvh@vecasoftware.com', "webmaster@schellesport.be", "Schelle Sport Webmaster", '', 'UTF-8', '');


    }
    // ===================================================================================================
    // Functie: Sturen mail naar "factuur-afhandelaars"
    //
    // In:  Mail-adres
    //
    // ===================================================================================================

    Static function SndMailTeFacturerenSponsorDossiers($pMail1){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetSxClassPath("tools.class"));

        $sqlStat = "Select count(*) as aantal from efin_sd_sponsor_dossiers where sdFacturatieNodig = 1 and sdRecStatus = 'A'";
        $db->Query($sqlStat);

        $sdRec = $db->Row();

        if ($sdRec->aantal <= 0)
            return;

        $aantal = $sdRec->aantal;

        $mailBody  =    "Beste,<br/><br/>";
        $mailBody .=    "*** DIT IS EEN AUTOMATISCHE MAIL *** <br/><br/>";

        if ($aantal > 1)
            $mailBody .= "Er zijn $aantal sponsordossiers klaar om te factureren";
        else
            $mailBody .= "Er is een sponsordossier klaar om te factureren";

        $mailBody .=    "<br/><br/>";
        $mailBody .=    "Sportieve groet,<br/><br/>Secretariaat<br/>Schelle Sport";

        SX_tools::SendMail("Schelle Sport - Sponsordossier(s) te factureren", $mailBody, $pMail1, 'gvh@vecasoftware.com', "secretariaat@schellesport.be", "Schelle Sport Secretariaat", '', 'UTF-8', '');

    }

    // ===================================================================================================
    // Functie: Opvullen "periode omzet" Horeca
    //
    // Input: Type (*HORECA-WEEK, *HORECA-MAAND, *TICKETING-WEEK, *TICKETING-MAAND)
    //
    // ===================================================================================================

    Static function FillPO($pType = '*HORECA-WEEK'){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        // --------
        // per week
        // --------

        $sqlStat = "Delete from efin_po_periodieke_omzet where poType = '$pType'";
        $db->Query($sqlStat);

        $periodeType = '*WEEK';
        if ($pType == '*HORECA-MAAND' or $pType == '*TICKETING-MAAND')
            $periodeType = '*MONTH';

        $sqlStat = "Select * from sx_pe_periods where peType = '$periodeType' and peDateFrom < current_date order by peDateFrom";
        $db->Query($sqlStat);

        while ($peRec = $db->Row()){

            $periode = $peRec->peYear . '-' .  sprintf("%02d", $peRec->pePeriod);

            $dateFrom = $peRec->peDateFrom;
            $dateTo = $peRec->peDateTo;

            $bedrag1 = 0;
            $bedrag2 = 0;

            $sqlStat = null;

            if ($pType == '*HORECA-MAAND' or $pType == '*HORECA-WEEK')
                $sqlStat = "Select * from efin_vr_ventilatie_rekeningen where vrInterfaces like '%*OMZET-HORECA%'";

            if ($pType == '*TICKETING-MAAND' or $pType == '*TICKETING-WEEK')
                $sqlStat = "Select * from efin_vr_ventilatie_rekeningen where vrInterfaces like '%*OMZET-TICKETING%'";

            $db2->Query($sqlStat);

            while ($vrRec = $db2->Row()) {

                $ventilatieRekening = $vrRec->vrId;

                $interfaces = $vrRec->vrInterfaces;


                $isBedrag2 = false;
                if (strpos($interfaces, 'CASH') !== false)
                    $isBedrag2 = true;
                if (strpos($interfaces, 'SENIORS') !== false)
                    $isBedrag2 = true;

                $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen inner join efin_rd_rekening_details on rdId = twRekeningDetail where twVentilatieRekening = $ventilatieRekening and rdRefDatum >= '$dateFrom' and rdRefDatum <=  '$dateTo'";


                $db3->Query($sqlStat);

                while ($twRec = $db3->Row()) {

                    if ($isBedrag2)
                        $bedrag2 += $twRec->twBedrag;
                    else
                        $bedrag1 += $twRec->twBedrag;

                }


            }

            if ($bedrag1 or $bedrag2) {

                $curDateTime = date('Y-m-d H:i:s');

                $values = array();

                $values["poType"] = MySQL::SQLValue($pType);
                $values["poPeriode"] = MySQL::SQLValue($periode);
                $values["poDatumVan"] = MySQL::SQLValue($dateFrom, MySQL::SQLVALUE_DATE);
                $values["poDatumTot"] = MySQL::SQLValue($dateTo, MySQL::SQLVALUE_DATE);

                $bedragTotaal = $bedrag1 + $bedrag2;
                $values["poBedragTotaal"] = MySQL::SQLValue($bedragTotaal, MySQL::SQLVALUE_NUMBER);
                $values["poBedrag1"] = MySQL::SQLValue($bedrag1, MySQL::SQLVALUE_NUMBER);
                $values["poBedrag2"] = MySQL::SQLValue($bedrag2, MySQL::SQLVALUE_NUMBER);

                $values["poDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $db3->InsertRow("efin_po_periodieke_omzet", $values);

            }
        }


        // -------------
        // Einde functie
        // -------------

    }

    // ===================================================================================================
    // Functie: Check Link Inkomende CN met Inkomende Factuur
    //
    // Input:   Creditnota
    //          Factuur
    //          Bedrag
    //
    // Return: *OK of fout-boodschap
    //
    // ===================================================================================================

    Static function ChkInkCnFactuurLink($pCN, $pFactuur, &$pBedrag){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...


        // --------------------
        // Ophalen nodige data
        // -------------------

        $sqlStat = "Select * from efin_if_inkomende_facturen where ifId = $pCN";
        $db->Query($sqlStat);

        if (! $cnRec = $db->Row())
            return "Onverwachte fout";

        $sqlStat = "Select * from efin_if_inkomende_facturen where ifId = $pFactuur";
        $db->Query($sqlStat);

        if (! $ifRec = $db->Row())
            return "Onverwachte fout";

        // -----------------------
        // Moet zelfde leverancier
        // -----------------------

        if ($ifRec->ifLeverancier != $cnRec->ifLeverancier)
            return "Leverancier factuur moet zelfde als leverancier CN";

        // ------------------------
        // Enkel positieve bedragen
        // ------------------------

        if (! $pBedrag){

            $pBedrag = $ifRec->ifBedrag - $ifRec->ifBetaald;

            $openBedrag = abs($cnRec->ifBedrag - $cnRec->ifBetaald);

            if ($openBedrag < $pBedrag)
                $pBedrag = $openBedrag;

        }

        if ($pBedrag <= 0)
            return "Bedrag verplicht (en steeds positief)";

        // ------------------------------------
        // Bedrag <= te betalen/ open bedrag CN
        // ------------------------------------

        $teBetalen = $ifRec->ifBedrag - $ifRec->ifBetaald;

        if ($pBedrag > $teBetalen)
            return "Bedrag > Te betalen ($teBetalen)";

        $openBedrag = abs($cnRec->ifBedrag - $cnRec->ifBetaald);

        if ($pBedrag > $openBedrag)
            return "Bedrag > Open bedrag deze CN ($openBedrag)";


        // -------------
        // Einde functie
        // -------------

        Return "*OK";

    }


    // ===================================================================================================
    // Functie: Registratie Link Inkomende CN met Inkomende Factuur
    //
    // Input:   Creditnota
    //          Factuur
    //
    // Return: *OK of fout-boodschap
    //
    // ===================================================================================================

    Static function RegInkCnFactuurLink($pCN, $pFactuur){

        self::SetIfControle($pCN);
        self::SetIfControle($pFactuur);

    }

    // ===================================================================================================
    // Functie: Copy budgetten periode -> periode
    //
    // Input:   Periode van
    //          Periode naar
    //
    // Return: Gebeurd? true/false
    //
    // ===================================================================================================

    Static function CpyBudgetten($pPeriodeVan, $pPeriodeNaar){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from efin_ab_analytische_rekening_budgetten where abPeriode = '$pPeriodeVan' and abLinkType = '*SPECIFIEK'";

        $db->Query($sqlStat);

        while ($abRec = $db->Row()){

            $analytischeRekening = $abRec->abAnalytischeRekening;
            $budget = $abRec->abBudget;
            $linkType = $abRec->abLinkType;

            $sqlStat = "Select count(*) as aantal from efin_ab_analytische_rekening_budgetten where abAnalytischeRekening = $analytischeRekening and and abLinkType = '*SPECIFIEK' and abPeriode = '$pPeriodeNaar'" ;

            $db2->Query($sqlStat);

            if ($ab2Rec = $db2->Row())
                if ($ab2Rec->aantal <= 0)
                    continue;

            $curDateTime = date('Y-m-d H:i:s');

            $values = array();

            $values["abAnalytischeRekening"] = MySQL::SQLValue($analytischeRekening, MySQL::SQLVALUE_NUMBER);
            $values["abPeriode"] = MySQL::SQLValue($pPeriodeNaar, MySQL::SQLVALUE_TEXT);

            $values["abBudget"] = MySQL::SQLValue($budget, MySQL::SQLVALUE_NUMBER);
            $values["abLinkType"] = MySQL::SQLValue($linkType, MySQL::SQLVALUE_TEXT);

            $values["abUserCreatie"] = MySQL::SQLValue('*COPY', MySQL::SQLVALUE_TEXT);
            $values["abDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $values["abUserUpdate"] = MySQL::SQLValue('*COPY', MySQL::SQLVALUE_TEXT);
            $values["abDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $db2->InsertRow("efin_ab_analytische_rekening_budgetten", $values);

        }

        // -------------
        // Einde functie
        // -------------

        return  true;

    }




    // -----------
    // Einde class
    // -----------

}

?>