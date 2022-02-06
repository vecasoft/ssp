<?php

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;


class VECA_admin
{ // define the class


    // ========================================================================================
    //  Uitgaande Factuur - Ophalen volgende factuur-nummer
    //
    // In:	Factuurtype
    //      Factuurdatum
    //
    // Uit: Volgende factuurnummer
    // ========================================================================================

    static function GetVolgendeFactuurnummer() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $factuurnummer = 0;

        $sqlStat = "Select max(ufFactuurnummer) as hoogsteNummer from veca_uf_uitgaande_facturen";
        $db->Query($sqlStat);

        if ($ufRec = $db->Row()){

            if ($ufRec->hoogsteNummer)
                $factuurnummer = $ufRec->hoogsteNummer + 1;

        }

        // -------------
        // Einde functie
        // -------------

        return $factuurnummer;

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
        include_once(SX::GetClassPath("_veca_db.class"));

        $ufRec = VECA_db::Get_VECA_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return;

        // --------------------------
        // Automatische Factuurnummer
        // --------------------------

        $factuurnummer = $ufRec->ufFactuurnummer;

        if (! $factuurnummer){

            $factuurnummer = self::GetFactuurnummer($ufRec->ufFactuurtype, $ufRec->ufFactuurdatum );

        }

        // ---------------------------
        // Totalen op basis van detail
        // ---------------------------

        $maatstafTotaal = 0;
        $BTWTotaal = 0;
        $factuurTotaal = 0;

        $sqlStat = "Select * from veca_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur";
        $db->Query($sqlStat);

        while ($udRec = $db->Row()){

            $maatstafTotaal += $udRec->udBedragMaatstaf;
            $BTWTotaal += $udRec->udBTWBedrag;

        }

        $factuurTotaal = $maatstafTotaal + $BTWTotaal;

        // -------
        // Totalen
        // -------

        $betaalBedragTotaal = $ufRec->ufBetaalBedrag1 + $ufRec->ufBetaalBedrag2 + $ufRec->ufBetaalBedrag3;

        // Afronden op 2 decimale
        $factuurTotaal = round($factuurTotaal, 2);
        $betaalBedragTotaal = round($betaalBedragTotaal, 2);

        // --------------
        // Factuur-status
        // --------------

        $factuurStatus = "*WACHT";

        if ($factuurTotaal > 0)
            $factuurStatus = "*KLAAR";

        if ($ufRec->ufAantalMails)
            $factuurStatus = "*VERSTUURD";

        if ($betaalBedragTotaal > 0 and $betaalBedragTotaal < $factuurTotaal)
            $factuurStatus = "*PART_BETAALD";

        if ($factuurTotaal <= $betaalBedragTotaal)
            $factuurStatus = "*BETAALD";

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $values["ufFactuurnummer"] =  MySQL::SQLValue($factuurnummer, MySQL::SQLVALUE_NUMBER);

        $values["ufFactuurStatus"] =  MySQL::SQLValue($factuurStatus, MySQL::SQLVALUE_TEXT);

        $values["ufMaatstafTotaal"] =  MySQL::SQLValue($maatstafTotaal, MySQL::SQLVALUE_NUMBER);
        $values["ufBTWTotaal"] =  MySQL::SQLValue($BTWTotaal, MySQL::SQLVALUE_NUMBER);
        $values["ufFactuurTotaal"] =  MySQL::SQLValue($factuurTotaal, MySQL::SQLVALUE_NUMBER);

        $values["ufBetaalBedragTotaal"] =  MySQL::SQLValue($betaalBedragTotaal, MySQL::SQLVALUE_NUMBER);

        $where["ufId"] =  MySQL::SQLValue($pUitgaandeFactuur, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("veca_uf_uitgaande_facturen", $values, $where);

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
    //  Uitgaande Factuur - Bijwerken totalen en Status
    //
    // In:	Uitgaande Factuur Detail ID
    //      Modus (*ADD, *UPDATE)
    //
    // ========================================================================================

    static function UpdUitgaandeFactuurDetail($pUitgaandeFactuurDetail, $pModus = "*UPDATE") {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_veca_db.class"));
        include_once(SX::GetClassPath("_db.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $udRec = VECA_db::Get_VECA_udRec($pUitgaandeFactuurDetail);
        $ufRec = VECA_db::Get_VECA_ufRec($udRec->udFactuur);
        $klrec = VECA_db::Get_VECA_klRec($ufRec->ufKlant);

        // ------------
        // BTW-bedragen
        // ------------

        $BTWBedrag = null;
        $BTWCode = null;

        $BTWCodeDefault = $klrec->klBTWTarief;

        if (! $BTWCodeDefault)
            $BTWCodeDefault = "1"; // 21%

        $BTWCode = $udRec->udBTWCode;
        if ($BTWCode < "0" or $BTWCode > "9")
            $BTWCode = $BTWCodeDefault;

        $taRec = SSP_db::Get_SX_taRec("VECA_BTW_TARIEVEN", $BTWCode);
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

        $db->UpdateRows("veca_ud_uitgaande_factuur_detail", $values, $where);

        // ------------------
        // Update header info
        // ------------------

        self::UpdUitgaandeFactuur($ufRec->ufId);


        // -------------
        // Einde functie
        // -------------

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

        include_once(SX::GetClassPath("_veca_db.class"));
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("veca.uitgaande_factuur.class"));
        include_once(SX::GetSxClassPath("tools.class"));

        // ------------------------------
        // Ophalen uitgaande factuur info
        // ------------------------------

        $ufRec = VECA_db::Get_VECA_ufRec($pUitgaandeFactuur);

        if (! $ufRec)
            return "";

        $klRec = VECA_db::Get_VECA_klRec($ufRec->ufKlant);

        if (! $klRec)
            return "";

        // $ftRec = SSP_db::Get_EFIN_ftRec($ufRec->ufFactuurtype);

        //if (! $ftRec->ftDocumentType)
            $documentType = '*FACTUUR';
        //else
            //$documentType = $ftRec->ftDocumentType;

        // -------------------
        // Create PDF-document
        // -------------------

        $uitgaandeFactuur = new UitgaandeFactuur(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set margins
        $uitgaandeFactuur->SetMargins(PDF_MARGIN_LEFT, 42, PDF_MARGIN_RIGHT);
        $uitgaandeFactuur->SetHeaderMargin(5);
        $uitgaandeFactuur->SetFooterMargin(5);
        $uitgaandeFactuur->SetAuthor('VECA Software bv');
        $uitgaandeFactuur->SetTitle('Uitgaande Factuur - VECA Software');

        $uitgaandeFactuur->documentType = $documentType;

        $logoVECA = $_SESSION["SX_BASEPATH"] . sx::GetSiteImgPath('veca_logo.png');
        $uitgaandeFactuur->logoVECA = $logoVECA;

        if ($documentType == '*KOSTENNOTA') {
            $uitgaandeFactuur->documentTitel = "KOSTENNOTA";
            $uitgaandeFactuur->labelFactuurnummer = "Documentnr:";
            $uitgaandeFactuur->labelFactuurdatum = "Datum:";
        }
        else {
            $uitgaandeFactuur->documentTitel = "FACTUUR";
            $uitgaandeFactuur->labelFactuurnummer = "Factuurnummer:";
            $uitgaandeFactuur->labelFactuurdatum = "Factuurdatum:";
        }

        $uitgaandeFactuur->klantNaam = $klRec->klNaam;
        $uitgaandeFactuur->klantAdres = $klRec->klAdres;
        $uitgaandeFactuur->klantGemeente = $klRec->klPostnr . " " . $klRec->klGemeente;
        $uitgaandeFactuur->klantLand = $klRec->klLand;
        $uitgaandeFactuur->klantBTWnr = $klRec->klBTW;

        $factuurDatum = new DateTime($ufRec->ufFactuurdatum);
        $uitgaandeFactuur->factuurDatum = $factuurDatum->format('d/m/Y');

        $uitgaandeFactuur->factuurNummer = $ufRec->ufFactuurnummer;

        $uitgaandeFactuur->omschrijving = utf8_encode($ufRec->ufOmschrijving);
        // $uitgaandeFactuur->GM = $ufRec->ufBetaalMededelingGM;

        $uitgaandeFactuur->teBetalen = number_format($ufRec->ufFactuurTotaal,2,',','.') . " EUR";

        $maatstaffen = array();
        $btwPercentages = array();
        $btwBedragen = array();
        $omschrijvingen = array();
        $totalen = array();

        $totaalMaatstaf = 0;
        $totaalBTW = 0;
        $totaalTotaal = 0;

        $sqlStat = "Select * from veca_ud_uitgaande_factuur_detail where udFactuur = $pUitgaandeFactuur order by udSort";
        $db->Query($sqlStat);

        while ($udRec = $db->Row()) {

            $maatstaffen[] = "€ " . number_format($udRec->udBedragMaatstaf,2,',','.');
            $btwBedragen[] = "€ " . number_format($udRec->udBTWBedrag, 2,',','.');

            $taRec = SSP_db::Get_SX_taRec("VECA_BTW_TARIEVEN", $udRec->udBTWCode);
            $btwPercentages[] =  number_format($taRec->taNumData, 2,',','.');

            $totaal = $udRec->udBedragMaatstaf + $udRec->udBTWBedrag;
            $totalen[] =  "€ " . number_format($totaal, 2,',','.');

            $omschrijvingen[] = utf8_encode($udRec->udOmschrijving);

            $totaalMaatstaf += $udRec->udBedragMaatstaf;
            $totaalBTW +=  $udRec->udBTWBedrag;
            $totaalTotaal += $totaal;

        }

        $uitgaandeFactuur->maatstaffen = $maatstaffen;
        $uitgaandeFactuur->btwBedragen = $btwBedragen;
        $uitgaandeFactuur->btwPercentages = $btwPercentages;
        $uitgaandeFactuur->omschrijvingen = $omschrijvingen;
        $uitgaandeFactuur->totalen = $totalen;

        $uitgaandeFactuur->totaalMaatstaf = number_format($totaalMaatstaf, 2,',','.');
        $uitgaandeFactuur->totaalBTW = number_format($totaalBTW, 2,',','.');
        $uitgaandeFactuur->totaalTotaal = number_format($totaalTotaal, 2,',','.');

        $uitgaandeFactuur->AddPage();
        $uitgaandeFactuur->FactuurBody();

        // ------------------
        // Extra Omschrijving
        // ------------------

        $extraOmschrijving = utf8_encode(wordwrap($ufRec->ufExtraOmschrijving, 120) . "\n");

        $array = explode("\n", trim($extraOmschrijving));
        $lineQty = count($array);

        if (trim($ufRec->ufExtraOmschrijving)) {

            if ($lineQty > 14) {
                $uitgaandeFactuur->AddPage();
                $uitgaandeFactuur->ExtraOmschrijving($extraOmschrijving, 40);
            } else
                $uitgaandeFactuur->ExtraOmschrijving($extraOmschrijving);
        }

        $docNaam = "VECA Software - Factuur " . $ufRec->ufFactuurnummer . ".pdf";

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

        include_once(SX::GetClassPath("_veca_db.class"));

        $ufRec = VECA_db::Get_VECA_ufRec($pUitgaandeFactuur);

        $fileName = "";

        if ($ufRec) {

            $fileName = "factuur_" . $ufRec->ufFactuurnummer . "_" . $ufRec->ufFactuurdatum;

        }


        $rootDir = $_SESSION["SX_BASEPATH"];

        $filePath = $rootDir . '/_generated_files/veca/uitgaandeFacturen/' . $fileName . '.pdf';

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


    // -----------
    // Einde class
    // -----------

}

?>