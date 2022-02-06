<?php

use SepaQr\SepaQr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Helper;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SSP_ela
{ // define the class


    // ========================================================================================
    // Handle contactform
    //
    // In: Contactform ID
    //
    // ========================================================================================

    static function HdlContactForm($pContactForm) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));

        // ---------------
        // Get needed data
        // ---------------

        $sqlStat = "Select * from ssp_cf_contactformulier where cfId = $pContactForm";
        $db->Query($sqlStat);

        if (! $cfRec = $db->Row())
            return;

        $doelgroep = "xx";
        $taRec = SSP_db::Get_SX_taRec('ELA_CONTACT_DOELGROEP', $cfRec->cfDoelgroep);
        if ($taRec)
            $doelgroep = $taRec->taName;

        // ------------------------------------------
        // Versturen mail naar sporieve staf, bestuur
        // ------------------------------------------

        $datum = substr($cfRec->cfGeboortedatum,0,10);
        $timestamp = strtotime($datum);
        $geboorteDatum = date("d-m-Y", $timestamp);

        $msg = '<table>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Naam:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfNaam;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Voornaam:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfVoornaam;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Adres:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfStraat;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Postcode:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfPostnummer;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Gemeente:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfGemeente;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Tel:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfTel;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Mail:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfMail;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Geboortedatum';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $geboorteDatum;
        $msg .= '</td>';
        $msg .= '</tr>';
        $msg .= '<tr>';

        $msg .= '<td>';
        $msg .= 'Voor aansluiting bij:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $doelgroep;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Vorige / huidige club:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfVorigeClub;
        $msg .= '</td>';
        $msg .= '</tr>';

        $msg .= '<tr>';
        $msg .= '<td>';
        $msg .= 'Nog aangesloten:';
        $msg .= '</td>';
        $msg .= '<td>';
        $msg .= $cfRec->cfNogAangesloten;
        $msg .= '</td>';
        $msg .= '</tr>';
        $msg .= '</table>';

        if ($cfRec->cfOpmerkingen > ' ') {

            $msg .= '<br>';
            $msg .= '<span style="font-weight: bold; text-decoration:underline">';
            $msg .= 'Opmerkingen';
            $msg .= '</span>';
            $msg .= '<br><br>';
            $msg .= nl2br($cfRec->cfOpmerkingen);

        }

        $subject = 'Contactformulier Voetbal ingevuld';

        SX_tools::SendMail($subject, $msg, 'voetbal@schellesport.be; sportief@schellesport.be');


    }

    // ========================================================================================
    // Opvullen  GM Lidgeld VB in ledenbestand
    //
    // In:	Persoon (*ALLESPELERS = Alle spelers)
    //
    // ========================================================================================

    static function FillGmLidgeldVB($pPersoon = '*ALLESPELERS') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("efin.class"));

        if ($pPersoon == '*ALLESPELERS')
            $sqlStat = "Select * from ssp_ad where adFunctieVB like '%speler%' and adRecStatus = 'A'";
        else
            $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";

        $db->Query($sqlStat);

        while ($adRec = $db->Row()){

            if (! $adRec->adGmLidgeldVB){

                $gm = SSP_efin::GetNextGM('*LIDGELD_VB');
                $gmNum = preg_replace('/[^0-9]/', '', $gm);

                if ($gm){

                    $persoon = $adRec->adCode;

                    $sqlStat2 = "Update ssp_ad set adGmLidgeldVB = '$gm', adGmLidgeldVBn = $gmNum where adCode = '$persoon'";
                    $db2->Query($sqlStat2);


                }

            } elseif (! $adRec->adGmLidgeldVBn){

                $gmNum = preg_replace('/[^0-9]/', '', $adRec->adGmLidgeldVB);
                $persoon = $adRec->adCode;

                $sqlStat2 = "Update ssp_ad set adGmLidgeldVBn = $gmNum where adCode = '$persoon'";

                $db2->Query($sqlStat2);
            }

        }

    }

    // ========================================================================================
    // Bepalen gezins-positie (1ste, 2de, ...)
    //
    // In:	Persoon

    // Return: Gezins-positie
    // ========================================================================================

    static function GetGezinsPositie($pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (! $adRec)
            return 1;

        if (! $adRec->adFamilieVan)
            return 1;

        $familieCode = $adRec->adFamilieVan;

        $sqlStat = "Select * from ssp_ad where adFamilieVan = '$familieCode' and adFunctieVB like '%speler%' and adRecStatus = 'A' and adClubVerlatenEindeSeizoen <> 1 order by adGeboorteJaar, adGeboorteMaand, adCode";


        $db->Query($sqlStat);

        $positie = 1;

        while ($adRec = $db->Row()){

            if ($adRec->adCode == $pPersoon)
                break;

            $positie++;
        }

        // -------------
        // Einde functie
        // -------------

        return $positie;

    }

    // ========================================================================================
    // Ophalen gezinsleden
    //
    // In:	Persoon

    // Return: Gezins-leden array
    // ========================================================================================

    static function GetGezinsleden($pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (! $adRec)
            return null;

        $familieLeden = array();

        if (! $adRec->adFamilieVan)  {

            $familieLeden[] = $pPersoon;

            return $familieLeden;

        }

        $familieCode = $adRec->adFamilieVan;

        $sqlStat = "Select * from ssp_ad where adFamilieVan = '$familieCode' and adFunctieVB like '%speler%' and adRecStatus = 'A' order by adGeboorteJaar, adGeboorteMaand, adCode";

        $db->Query($sqlStat);

        while ($adRec = $db->Row())
            $familieLeden[] = $adRec->adCode;

        // -------------
        // Einde functie
        // -------------

        return $familieLeden;

    }

    // ========================================================================================
    // Get "Te betalen" lidgeld voetbal
    //
    // In:	Persoon
    //
    // Out: Bedrag reeds betaald
    //      GM
    //      gezinspositie
    //      Specifieke afspraak? (true/false)
    //      Boetebedrag
    //      Tariefcode
    //      Kortingbedrag (CORONA)
    //      Kortingtekst (CORONA)
    //
    // Return: Bedrag te betalen (-1 = niets te betalen)
    // ========================================================================================

    static function GetTebetalenLidgeldVoetbal($pPersoon, &$pReedsBetaald, &$pGM, &$pPositie, &$pSpecifiekeAfspraak, &$pBoeteBedrag, &$pTariefCode = null, &$pKortingBedrag = null,  &$pKortingTekst = null,  &$pBasisbedrag = null) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("eba.class"));

        include_once(SX::GetClassPath("settings.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (! $adRec)
            return -1;

        $huidigSeizoen = SSP_eba::GetHuidigSeizoen();

        // -------------------------
        // Init uitgaande parameters
        // -------------------------

        $pReedsBetaald = $adRec->adLidgeldTotaal;
        $pGM = $adRec->adGmLidgeldVB;
        $pSpecifiekeAfspraak = false;

        if (isset($pTariefCode))
            $pTariefCode = 'NVT';

        // -------------------
        // Specifieke afspraak
        // -------------------

        if ($adRec->adLidgeldSpecifiekBedrag) {

            $pSpecifiekeAfspraak = true;

            $bedrag = $adRec->adLidgeldSpecifiekBedrag;

            if ($adRec->adLidgeldBoete)
                $bedrag += $adRec->adLidgeldBoete;

            if (isset($pTariefCode))
                $pTariefCode = '*SPECIFIEK';

            if (isset($pBasisbedrag))
                $pBasisbedrag = $bedrag;

            return $bedrag;
        }

        $pBoeteBedrag = $adRec->adLidgeldBoete;

        // -------------------------
        // Bepalen voetbal-categorie
        // -------------------------

        $voetbalCat = $adRec->adVoetbalCat;

        if ($adRec->adVoetbalCatWebshop)
            $voetbalCat =$adRec->adVoetbalCatWebshop;

        if (! $voetbalCat)
            return 0;

        // -------
        // Gratis?
        // -------


        if ($adRec->adLidgeldGratis == 1 and $adRec->adLidgeldVoldaanVB == 'JA') {
            $pSpecifiekeAfspraak = true;
            if (isset($pTariefCode))
                $pTariefCode = '*GRATIS';
            return 0;
        }

        if ($adRec->adClubVerlatenEindeSeizoen == 1) {
            $pSpecifiekeAfspraak = false;
            if (isset($pTariefCode))
                $pTariefCode = '*NVT';
            return 0;
        }

        // ----------------------------------------------------
        // Bepalen prijs-categorie (1ste, 2de, 3de zelfde gezin)
        // ----------------------------------------------------

        $positie = self::GetGezinsPositie($pPersoon);
        $pPositie = $positie;

        // -----------
        // Tarief-code
        // -----------

        $tariefCode = $voetbalCat;

        // --------------
        // Ophalen tarief
        // --------------

        $sqlStat = "Select * from ela_lv_lidgelden_voetbal where lvSeizoen = '$huidigSeizoen' and lvCategorie = '$voetbalCat' and lvRecStatus = 'A'";

        $db->Query($sqlStat);

        if (! $lvRec = $db->Row())
            return -1;

        $bedrag = $lvRec->lvBedrag1;

        $tariefCodeBase = $tariefCode;

        if ($positie > 1 and $lvRec->lvBedrag2) {
            $bedrag = $lvRec->lvBedrag2;
            $tariefCode = $tariefCodeBase . "_2";
        }
        if ($positie > 2 and $lvRec->lvBedrag3) {
            $bedrag = $lvRec->lvBedrag3;
            $tariefCode = $tariefCodeBase . "_3";
        }

        if ($positie > 3 and $lvRec->lvBedrag4) {
            $bedrag = $lvRec->lvBedrag4;
            $tariefCode = $tariefCodeBase . "_4";
        }

        if ($adRec->adLidgeldBoete)
            $bedrag += $adRec->adLidgeldBoete;

        // --------------
        // CORONA-korting
        // --------------

        if (isset($pBasisbedrag))
            $pBasisbedrag = $bedrag;

        $kortingTekst = '';
        $kortingBedrag = 0;

        $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pPersoon'";
        $db->Query($sqlStat);

        if ($lkRec = $db->Row()){

            if ($lkRec->lkKortingGeenKledijpakket) {
                $kortingBedrag += $lkRec->lkKortingGeenKledijpakket;
                $kortingTekst = "Geen kledijpakket: $lkRec->lkKortingGeenKledijpakket EUR";
            }

            if (($lkRec->lkKeuze == '*LIDGELD') and ($lkRec->lkKortingOpLidgeld > 0)) {
                $kortingBedrag += $lkRec->lkKortingOpLidgeld;
                if ($kortingTekst)
                    $kortingTekst = "$kortingTekst,";
                $kortingTekst = "$kortingTekst Lidgeld-korting: $lkRec->lkKortingOpLidgeld EUR";
            }


            if ($kortingBedrag)
                $bedrag -=$kortingBedrag;

            if ($bedrag < 0)
               $bedrag = 0;

        }

        if (isset($pTariefCode))
            $pTariefCode = $tariefCode;

        if (isset($pKortingBedrag))
            $pKortingBedrag = $kortingBedrag;

        if (isset($pKortingTekst))
            $pKortingTekst = $kortingTekst;

        // -------------
        // Einde functie
        // -------------

        return $bedrag;

    }

    // ========================================================================================
    // Get "Te betalen" lidgeld voetbal HTML snippet
    //
    // In:	Persoon
    //
    // Return: HTML snippet
    // ========================================================================================

    static function GetLidgeldVoetbalHTML($pPersoon, $pShowQR = true, $pShowStatusKledijpakket = true) {

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.class"));
        include_once(SX::GetClassPath("eba.class"));
        include_once(SX::GetSxClassPath("sessions.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (! $adRec)
            return "ONBEKENDE FOUT";

        $kortingBedrag = 0;
        $kortingTekst = "";
        $basisbedrag = 0;
        $tariefCode = "";

        $teBetalen = self::GetTebetalenLidgeldVoetbal($pPersoon, $reedsBetaald, $GM, $positie, $specfiekeAfspraak, $boeteBedrag, $tariefCode, $kortingBedrag, $kortingTekst, $basisbedrag);


        $saldo = 0;

        if ($teBetalen > 0) {
            $saldo = $teBetalen - $reedsBetaald;
            $teBetalen .= " EUR";
        }
        else
            $teBetalen = "NVT";

        $naam = $adRec->adVoornaamNaam;

        if ($adRec->adVoetbalCatWebshop)
            $categorie = $adRec->adVoetbalCatWebshop;
        else
            $categorie = $adRec->adVoetbalCat;

        $positieGezin = "&nbsp;";

        if ($positie == 2 )
            $positieGezin = "&nbsp;(2de gezinslid)";
        if ($positie == 3 )
            $positieGezin = "&nbsp;(3de gezinslid)";
        if ($positie >= 4 )
            $positieGezin = "&nbsp;(4de gezinslid)";

        if ($specfiekeAfspraak == true)
            $positieGezin = "&nbsp;(Specifieke afspraak)";

        if ($boeteBedrag)
            $positieGezin .= "<br/>   (inclusief <b>boete</b> $boeteBedrag EUR)";

        $tarief = "";

        if ($tariefCode){

            $tarief = $tariefCode;
            $taRec = SSP_db::Get_SX_taRec('ELA_TARIEFCODES_VOETBAL', $tariefCode);

            if ($taRec)
                $tarief = $taRec->taName;

        }

        if (! $reedsBetaald)
            $reedsBetaald = "0";


        $block1 = "<b>$naam</b><br/><br/>Tarief: $tarief<br/><br/>"
                . "Lidgeld: $basisbedrag<br/>"
                . "$kortingTekst<br/>"
                . "Reeds betaald: $reedsBetaald EUR<br/><br/>"
                . "<b>Te betalen: $saldo EUR</b>";


        if ($saldo > 0) {

            $extraInstructie = "";

            if ($pShowQR)
                $extraInstructie = "<br/><br/>Je kan ook de QR code rechts gebruiken via je bank-app<br/>(Werkt o.a. bij Belfius, KBC, Fortis)";

            $block2 = "<b>Betaal-instructies</b><br/><br/>Stort het lidgeld op:<br/> <b>IBAN BE67 2930 0744 3187</b> van Schelle Sport<br/> met volgende mededeling: <b>$GM</b>$extraInstructie";
        }

        if ($saldo <= 0) {

            $block2 ="<b>Betaal-instructies</b><br/><br/><span style='font-size: 400%; color: green' class='glyphicon glyphicon-ok'></span><br/><br/>Volledig bedrag werd reeds betaald";


        }

        // QR-code betaalgegevens
        if ($pShowQR) {
            $fileName = self::CrtLidgeldVBSepaQR($pPersoon);
            $qrPath = $_SESSION["SX_BASEDIR"] . '/_files/images_apps/qr_codes/' . $fileName;
        }

        if (($saldo > 0) and $qrPath)
            $block3 = "<img  height=\"100\" width=\"100\" src=\"$qrPath\">";
        else
            $block3 = "&nbsp;";


        $html = "<div style='float: left; width: 250px'>$block1</div><div style='float: left; width: 400px'>$block2</div><div style='float: right;'>$block3</div>";

        // -----------------------
        // Toevoegen kledij-status
        // -----------------------

        $userId = SX_sessions::GetSessionUserId();

        $kledijStatusHTML = "";

        if ($pShowStatusKledijpakket) {

            $kledijStatus = SSP_eba::GetKledijStatus($pPersoon);

            If ($kledijStatus != '*BESTELD' and $kledijStatus != '*NVT' and $pPersoon == $userId) {
                $linkWebshop = "<a href='index.php?app=webshop&layout=full'>HIER</a>";

                $kledijStatusHTML = "Kledijpakket inbegrepen in lidgeld nog NIET besteld, klik $linkWebshop om het te bestellen";
                $colorHand = "red";
            };

            If ($kledijStatus != '*BESTELD' and $kledijStatus != '*NVT' and $pPersoon != $userId) {
                $kledijStatusHTML = "Kledijpakket nog NIET besteld (gelieve aan te melden met login \"$pPersoon\" om dit te doen)";
                $colorHand = "red";
            };

            If ($kledijStatus == '*BESTELD') {
                $kledijStatusHTML = "Kledijpakket inbegrepen in lidgeld reeds besteld.";
                $colorHand = "green";
            };

            If ($kledijStatus == '*NVT') {
                $kledijStatusHTML = "Kledijpakket niet voorzien";
                $colorHand = "green";
            };

            if ($kledijStatusHTML) {

                if ($colorHand != 'green')
                    $kledijStatusHTML = "<span style='color: $colorHand; font-size: 150%; font-weight: bold' class='glyphicon glyphicon-hand-right'></span>&nbsp;$kledijStatusHTML";
                else
                    $kledijStatusHTML = "<span style='color: $colorHand; font-size: 150%; font-weight: bold' class='glyphicon glyphicon-ok'></span>&nbsp;$kledijStatusHTML";
            }

        }

        $html .= "<div style='clear: both; padding-top: 10px'>$kledijStatusHTML</div>";


        // -------------
        // Einde functie
        // -------------

        return $html;


    }
    // ========================================================================================
    // Create SEPA QR-code
    //
    // In: Persoon
    //
    // Return: File-name QR-code
    // ========================================================================================

    static function CrtLidgeldVBSepaQR($pPersoon, $pType='*VOORSCHOT') {

        $sepaQr = new SepaQr();

        $reedsBetaald = 0;
        $teBetalen = self::GetTebetalenLidgeldVoetbal($pPersoon, $reedsBetaald, $GM, $positie, $specifiekeAfspraak, $boeteBedrag);

        $saldo = $teBetalen - $reedsBetaald;

        if ($saldo <= 0)
            $saldo = 0;

        $numberGM = preg_replace('/[^0-9]/', '', $GM);

        $sepaQr
            ->setName('Schelle Sport - Voetbal')
            ->setIban('BE67293007443187')
            ->setAmount($saldo)// The amount in Euro
            ->setRemittanceText($numberGM)
            ->setSize(300);


        $fileName = 'qr_lidgeld_' . $pPersoon . '.png';

        $qrPHPPath = $_SESSION["SX_BASEPATH"] . '/_files/images_apps/qr_codes/' . $fileName;
        $sepaQr->writeFile($qrPHPPath);

        // -------------
        // Einde functie
        // -------------

        return $fileName;


    }

    // ===================================================================================================
    // Functie: Opghalen "unieke" kaartcode (10 posities)
    //
    // In:	Niets
    //
    // Uit:	Unieke kaartcode
    //
    // ===================================================================================================

    Static function GetKaartCode() {

        include_once(SX::GetClassPath("_db.class"));
        $kaartCode = null;

        for ($i=0; $i < 10; $i++) {

            $kaartCode = substr(str_shuffle("0123456789abcdefghjkmnprstuvwxyzABCDEFGHIJKLMNOPQ"), 0, 7);

            $kaRec = SSP_db::Get_SSP_kaRec($kaartCode);

            if (!$kaRec)
                break;

        }

        // -------------
        // Einde functie
        // -------------

        return $kaartCode;

    }

    // ===================================================================================================
    // Functie: Aanmaken nieuwe kaart
    //
    // In:	USER-id
    //      Persoon
    //      Type (*LIDKAART_V, *LIDKAART_T, *ABONNEMENT_V, ...
    //      Subtype (*TRAINER, *BESTUUR_V, *BESTUUR_T, *RVB, ...)
    //      Seizoen
    //
    // Uit:	Unieke kaartcode
    //
    // ===================================================================================================

    Static function CrtKaart($pUserId, $pPersoon, $pType, $pSubtype, $pSeizoen, $pNaam = "", $pVoornaam = "") {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // --------------------
        // Ophalen subtybe data
        // --------------------

        $ksRec = SSP_db::Get_ELA_ksRec($pType, $pSubtype);
        if (! $ksRec)
            return null;

        // --------------------------
        // Ophalen "unieke" kaartcode
        // --------------------------

        $kaartCode = self::GetKaartCode();

        if (! $kaartCode)
            return null;

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();

        $seizoen = $pSeizoen;

        if ($pType == '*ABONNEMENT_VB' && $pSubtype == '*LEVENSLANG')
            $seizoen = 'Levenslang';

        $values["kaKaartCode"] = MySQL::SQLValue($kaartCode);

        $values["kaPersoon"] = MySQL::SQLValue($pPersoon);
        $values["kaType"] = MySQL::SQLValue($pType);
        $values["kaSubtype"] = MySQL::SQLValue($pSubtype);
        $values["kaSeizoen"] = MySQL::SQLValue($seizoen);
        $values["kaPrinted"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);

        $values["kaWedstrijdenJeugd"] = MySQL::SQLValue($ksRec->ksWedstrijdenJeugd, MySQL::SQLVALUE_NUMBER);
        $values["kaWedstrijdenSeniors"] = MySQL::SQLValue($ksRec->ksWedstrijdenSeniors, MySQL::SQLVALUE_NUMBER);
        $values["kaEetEvents"] = MySQL::SQLValue($ksRec->ksEetEvents, MySQL::SQLVALUE_NUMBER);

        $values["kaVoornaam"] =  MySQL::SQLValue($pVoornaam);
        $values["kaNaam"] =  MySQL::SQLValue($pNaam);

        $values["kaUserCreatie"] = MySQL::SQLValue($pUserId);
        $values["kaDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["kaUserUpdate"] = MySQL::SQLValue($pUserId);
        $values["kaDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $values["kaRecStatus"] = MySQL::SQLValue('A');

        $db->InsertRow("ela_ka_kaarten", $values);

        // -------------
        // Einde functie
        // -------------

        return $kaartCode;

    }

    // ===================================================================================================
    // Functie: Aanmaken nieuwe kaarten voor bepaald seizoen
    //
    // In:	USER-id
    //      Type (*LIDKAART_VB)
    //      Seizoen
    //      Subtype (optioneel)
    //      Persoon (optioneel)
    //
    // Uit:	# aangemaakte kaarten
    //
    // ===================================================================================================

    Static function CrtKaarten($pUserId, $pSeizoen, $pType, $pSubtype="", $pPersoon="", $pNaam = "", $pVoornaam = ""){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $aantal = 0;

        // ------------------------------------------
        // Voor specifieke persoon (sowieso aanmaken)
        // ------------------------------------------

        if ($pPersoon){

            $kaartCode = self::CrtKaart($pUserId, $pPersoon, $pType, $pSubtype, $pSeizoen, $pNaam, $pVoornaam);

            if ($kaartCode)
                return 1;
            else
                return 0;
        }

        // ------------------
        // Voetbal lidkaarten
        // ------------------

        if ($pType == '*LIDKAART_VB') {

            // -------
            // Spelers
            // -------

            if ((! $pSubtype) or ($pSubtype == '*SPELER')) {

                $subtype = '*SPELER';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieVB like '%speler%' and (adLidgeldVoldaanVB = 'JA' or adLidgeldVoldaanVB = 'DEEL' and adAfbetalingsplan = 1) and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;


                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // ---------
            // Trainers
            // ---------

            if ((! $pSubtype) or ($pSubtype == '*TRAINER')) {

                $subtype = '*TRAINER';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and (adFunctieVB like '%jeugd.tr%' or adFunctieVB like '%senior.tr%') and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // ---------
            // Ouderraad
            // ---------

            if ((! $pSubtype) or ($pSubtype == '*OUDERRAAD')) {

                $subtype = '*OUDERRAAD';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and (adFunctieVB like '%ouderraad%') and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // --------------
            // Scheidsrechter
            // --------------

            if ((! $pSubtype) or ($pSubtype == '*SCHEIDSRECHTER')) {

                $subtype = '*SCHEIDSRECHTER';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and (adFunctieVB like '%scheids%') and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // -------
            // Bestuur
            // -------

            if ((! $pSubtype) or ($pSubtype == '*BESTUUR')) {

                $subtype = '*BESTUUR';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and ( adFunctieVB like '%bestuur%' or adFunctieSSP like '%rvb%')  and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // -------------
            // Bestuur GTEAM
            // -------------

            if ((! $pSubtype) or ($pSubtype == '*BESTUUR_GTEAM')) {

                $subtype = '*BESTUUR_GTEAM';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and ( adFunctieVB like '%gteam.bstr%')  and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }
            // --------------
            // Sportieve Staf
            // --------------

            if ((! $pSubtype) or ($pSubtype == '*SPORTIEVE_STAF')) {

                $subtype = '*SPORTIEVE_STAF';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and ( adFunctieVB like '%sp.staf%')  and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // -------------
            // Afgevaardigde
            // -------------

            if ((! $pSubtype) or ($pSubtype == '*AFGEVAARDIGDE')) {

                $subtype = '*AFGEVAARDIGDE';

                $sqlStat = "Select distinct(adCode) as Persoon from ssp_ad inner join ssp_vp on vpSeizoen = '$pSeizoen' and (vpDelege = adCode or vpDelege2 = adCode or vpDelege3 = adCode)  where adRecStatus = 'A' and ( adFunctieVB like '%afgev%')  and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->Persoon;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

            // ----------
            // Medewerker
            // ----------

            if ((! $pSubtype) or ($pSubtype == '*MEDEWERKER')) {

                $subtype = '*MEDEWERKER';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and ( adFunctieSSP like '%MW%')  and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_VB' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;

                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }


        }


        // ------------
        // Abonnementen
        // -------------

        if ($pType == '*ABONNEMENT_VB') {

            $extraWhere = "1=1";

            if ($pSubtype == '*JEUGD')
                $extraWhere = "adAbonnement = 'JEUGD'";
            if ($pSubtype == '*SENIORS')
                $extraWhere = "adAbonnement = '1STE_ELF'";
            if ($pSubtype == '*LEVENSLANG')
                $extraWhere = "adAbonnementGratisReden = '*EEUWIG'";

            $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieVB like '%abonnee%' and ($extraWhere) and (adAbonnementBetaald = 1 or adAbonnementGratis = 1) and adCode not in (Select kaPersoon from ela_ka_kaarten where (kaSeizoen = '$pSeizoen' or kaSeizoen = 'Levenslang') and kaType = '*ABONNEMENT_VB' and kaRecStatus = 'A')";

            if ($pSubtype == '*SENIORS_ABO') {
                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieVB like '%speler%' and (adVoetbalCat = 'SEN' or adVoetbalCat = 'U21') and (adLidgeldVoldaanVB = 'JA' or adLidgeldVoldaanVB = 'DEEL' and adAfbetalingsplan = 1) and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*ABONNEMENT_VB' and kaSubtype = '*SENIORS_ABO' and kaRecStatus = 'A')";
            }

            $db->Query($sqlStat);

            while ($adRec = $db->Row()){

                $persoon = $adRec->adCode;
                $seizoen = $pSeizoen;

                if ($pSubtype != '*SENIORS_ABO' ) {

                    $abonnement = $adRec->adAbonnement;

                    if (!$abonnement)
                        continue;
                }


                if ($pSubtype == '*SENIORS_ABO') {
                    $abonnement = '*SENIORS';
                    $subtype = $pSubtype;
                }

                if ($pSubtype != '*SENIORS_ABO') {

                    $subtype = '*JEUGD_SENIORS';

                    if ($abonnement == 'JEUGD' or $abonnement == 'JEUGD_60')
                        $subtype = '*JEUGD';
                    if ($abonnement == '1STE_ELF' or $abonnement == '1STE_ELF_60')
                        $subtype = '*SENIORS';

                    if ($adRec->adAbonnementGratis == 1 and $adRec->adAbonnementGratisReden == '*EEUWIG') {
                        $subtype = '*LEVENSLANG';
                        $seizoen = 'Levenslang';
                    }
                }

                $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $seizoen);

                if ($kaartCode)
                    $aantal++;

            }
        }


        // ---------------
        // Lidkaart TENNIS
        // ---------------

        if ($pType == '*LIDKAART_T') {

            // -------
            // Spelers
            // -------

            if ((! $pSubtype) or ($pSubtype == '*SPELER')) {

                $subtype = '*SPELER';

                $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieT like '%speler%' and adTennisTariefLidVoetbal <> 1 and (adTennisLidgeldVoldaan = 'Ja' or adTennisLidgeldVoldaan = 'FAMILIE' or adTennisLidgeldVoldaan = 'NVT') and adCode not in (Select kaPersoon from ela_ka_kaarten where kaSeizoen = '$pSeizoen' and kaType = '*LIDKAART_T' and kaRecStatus = 'A')";

                $db->Query($sqlStat);

                while ($adRec = $db->Row()) {

                    $persoon = $adRec->adCode;


                    $kaartCode = self::CrtKaart($pUserId, $persoon, $pType, $subtype, $pSeizoen);

                    if ($kaartCode)
                        $aantal++;

                }

            }

        }


        // -------------
        // Einde functie
        // -------------

        return $aantal;

    }

    // ===================================================================================================
    // Functie: Zet lidkaart op "geprint"
    //
    // In:	kaart

    // Uit:	Niets
    //
    // ===================================================================================================

    Static function SetKaartGeprint($pKaart) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update ela_ka_kaarten set kaPrinted = 1, kaDatumPrinted = now() where kaKaartCode = '$pKaart'";

        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }

    // ===================================================================================================
    // Functie: Zet lidkaart op "historiek"
    //
    // In:	kaart

    // Uit:	Niets
    //
    // ===================================================================================================

    Static function SetKaartHistoriek($pKaart) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update ela_ka_kaarten set kaRecStatus = 'H' where kaKaartCode = '$pKaart'";

        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }
    // ===================================================================================================
    // Functie: Zet lidkaart op "ontvangen"
    //
    // In:	kaart

    // Uit:	Niets
    //
    // ===================================================================================================

    Static function SetKaartOntvangen($pKaart) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update ela_ka_kaarten set kaOntvangen = 1, kaDatumOntvangen = now() where kaKaartCode = '$pKaart' and kaPrinted = 1";

        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }
    // ===================================================================================================
    // Functie: Zet lidkaart op "niet ontvangen"
    //
    // In:	kaart

    // Uit:	Niets
    //
    // ===================================================================================================

    Static function SetKaartNietOntvangen($pKaart) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update ela_ka_kaarten set kaOntvangen = 0, kaDatumOntvangen =null where kaKaartCode = '$pKaart'";

        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }
    // ===================================================================================================
    // Functie: Ophalen "informatieve" naam contact/lid
    //
    // In: Persoon

    // Uit:	Naam (met extra info)
    //
    // ===================================================================================================

    Static function GetPersoonInfoNaam($pPersoon) {

        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (!$adRec)
            return "*ONBEKEND";

        $functieVB = "$adRec->adFunctieVB";
        $functieT = "$adRec->adFunctieT";
        $functieSSP = "$adRec->adFunctieSSP";

        $relatieMet = "$adRec->adRelatieMet";

        $geboorteJaar = $adRec->adGeboorteJaar;

        $infoNaam =  $adRec->adNaamVoornaam;

        if ((! $functieVB) and (! $functieT) and (! $functieSSP))
            return $infoNaam;

        if (strpos($functieVB, 'speler'))
            $infoNaam .= " (speler VB - $geboorteJaar )";

        elseif (strpos($functieT, 'speler'))
            $infoNaam .= " (speler T -  $geboorteJaar )";

        elseif (strpos($functieVB, 'trainer'))
            $infoNaam .= " (trainer VB)";

        elseif (strpos($relatieMet, 'T'))
            $infoNaam .= " (tennis)";


        // -------------
        // Einde functie
        // -------------

        return $infoNaam;


    }


    // ===================================================================================================
    // Functie: Ophalen Lidgeld voetbal betaal-status omschrijving (voor "raadplegen" toepassing)
    //
    // In: Persoon
    //
    // Uit: Kleur (groen, geel, rood)
    //
    // Return:	Betaalstatus
    //
    // ===================================================================================================

    Static function GetLidgeldvoldaanVBOmschrijving($pPersoon, &$pKleur){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetSxClassPath("tables.class"));

        $sqlStat = "Select * from ssp_ad where adFunctieVB like '%speler%' and adCode = '$pPersoon'";
        $db->Query($sqlStat);

        $pKleur = "";

        if (! $adRec = $db->Row())
            return '';

        if ($adRec->adClubVerlatenEindeSeizoen)
            return 'Verlaat club';

        $lidgeldVoldaan = $adRec->adLidgeldVoldaanVB;

        if (! $lidgeldVoldaan)
            return '';

        if ($lidgeldVoldaan == 'NVT')
            return '';



        if (($lidgeldVoldaan == 'DEEL') and ($adRec->adAfbetalingsplan == 1))
            $lidgeldVoldaan = 'JA';

        $lidgeldVoldaanOmschrijving = SX_tables::GetDesc('LEDEN_LIDGELDSTATUS',$lidgeldVoldaan);

        if ($lidgeldVoldaan == 'JA')
            $pKleur = 'groen';
        if ($lidgeldVoldaan == 'NEE')
            $pKleur = 'rood';
        if ($lidgeldVoldaan == 'DEEL')
            $pKleur = 'geel';

        // -------------
        // Einde functie
        // -------------

        return $lidgeldVoldaanOmschrijving;


    }

    // ===================================================================================================
    // Functie: Ophalen ploeg(en) trainer
    //
    // In: Persoon
    //
    // Return:	String met ploeg(en)
    // ===================================================================================================

    Static function GetTrainerPloegen($pPersoon, $pEnkelPloegen = false) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("settings.class"));

        $seizoen = SSP_settings::GetActiefSeizoen();

        $sqlStat = "Select * from ssp_vp where vpSeizoen = '$seizoen' and vpRecStatus = 'A' and (vpTrainer = '$pPersoon' or vpTrainer2 = '$pPersoon' or vpTrainer3 = '$pPersoon' or vpTrainer4 = '$pPersoon' or vpTrainer5 = '$pPersoon')";
        $db->Query($sqlStat);

        $ploegen = "";
        $aantalPloegen = 0;

        while ($vpRec = $db->Row()){

            $aantalPloegen++;

            if (! $ploegen)
                $ploegen = $vpRec->vpNaamKort;
            else
                $ploegen .= "," . $vpRec->vpNaamKort;
        }

        if ($aantalPloegen)
            if (! $pEnkelPloegen)
              $ploegen = "Trainer van: <b>$ploegen</b>";

        // -------------
        // Einde functie
        // -------------

        return $ploegen;
    
    }

    // ===================================================================================================
    // Functie: Ophalen alle mail-adressen van een persoon
    //
    // In: Persoon
    //
    // Return:	Array met mail adressen (unique)
    // ===================================================================================================

    Static function GetPersoonMails($pPersoon) {

        include_once(SX::GetClassPath("_db.class"));

        $mails = array();

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if ($adRec){

            $mailsUnique = array();
            $mailsUnique[] = $adRec->adMail;
            $mailsUnique[] = $adRec->adSpelerMail;
            $mailsUnique[] = $adRec->adVaderMail;
            $mailsUnique[] = $adRec->adMoederMail;

            $mailsUnique = array_unique($mailsUnique);

            foreach ($mailsUnique as $mail){

                if ($mail)
                    $mails[] = $mail;

            }

        }

        // -------------
        // Einde functie
        // -------------

        return $mails;

    }

    // ===================================================================================================
    // Functie: Ophalen ploeg(en) afgevaardigde
    //
    // In: Persoon
    //
    // Return:	String met ploeg(en)
    // ===================================================================================================

    Static function GetAfgevaardigdePloegen($pPersoon, $pEnkelPloegen = false) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("settings.class"));

        $seizoen = SSP_settings::GetActiefSeizoen();

        $sqlStat = "Select * from ssp_vp where vpSeizoen = '$seizoen' and vpRecStatus = 'A' and (vpDelege = '$pPersoon' or vpDelege2 = '$pPersoon' or vpDelege3 = '$pPersoon')";
        $db->Query($sqlStat);

        $ploegen = "";
        $aantalPloegen = 0;

        while ($vpRec = $db->Row()){

            $aantalPloegen++;

            if (! $ploegen)
                $ploegen = $vpRec->vpNaamKort;
            else
                $ploegen .= "," . $vpRec->vpNaamKort;
        }

        if ($aantalPloegen)
            if (! $pEnkelPloegen)
                $ploegen = "Afgevaardigde van: <b>$ploegen</b>";

        // -------------
        // Einde functie
        // -------------

        return $ploegen;

    }

    // ===================================================================================================
    // Functie: Ophalen ploeg-od speler
    //
    // In: Persoon
    //
    // Return:	Ploeg-id
    // ===================================================================================================

    Static function GetSpelerPloegId($pPersoon){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("settings.class"));

        $ploeg = null;

        $seizoen = SSP_settings::GetActiefSeizoen();

        $sqlStat = "Select * from ssp_vp_sp inner join ssp_vp on vpId = spPloeg where spPersoon = '$pPersoon' and spType = 'Speler' and vpSeizoen = '$seizoen' and vpVoetbalCat <> 'JEUGD' order by spDatumCreatie desc";
        $db->Query($sqlStat);

        if ($spRec = $db->Row())
            $ploeg = $spRec->vpId;

        // -------------
        // Einde functie
        // -------------

        return $ploeg;

    }

    // ===================================================================================================
    // Functie: Ophalen ploeg speler
    //
    // In: Persoon
    //
    // Return:	String met ploeg(en)
    // ===================================================================================================

    Static function GetSpelerPloeg($pPersoon, $pEnkelPloegen = false) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("settings.class"));

        $ploeg = null;

        $seizoen = SSP_settings::GetActiefSeizoen();

        $sqlStat = "Select * from ssp_vp_sp inner join ssp_vp on vpId = spPloeg where spPersoon = '$pPersoon' and spType = 'Speler' and vpSeizoen = '$seizoen' and vpVoetbalCat <> 'JEUGD' order by spDatumCreatie desc";
        $db->Query($sqlStat);

        if ($spRec = $db->Row())
            $ploeg = $spRec->vpNaamKort;

        // -------------
        // Einde functie
        // -------------

        if ($ploeg)
            if (! $pEnkelPloegen)
                $ploeg = "Speler van: <b>$ploeg</b>";

        return $ploeg;

    }

    // ===================================================================================================
    // Functie: Check functie VB
    //
    // In:  Functie VB
    //      Type (*SPELER, *TRAINER, *AFGEVAARDIGDE, *BESTUUR, *SPORTIEF
    //
    // Return:	true/false
    // ===================================================================================================

    Static function ChkFunctieVB($pfunctieVB, $pType) {

        $return = false;

        if (!$pfunctieVB)
            return false;

        if ($pType == '*SPELER')
            $return = (strpos($pfunctieVB, 'speler') !== false);

        if ($pType == '*DOELMAN')
            $return = (strpos($pfunctieVB, 'keeper') !== false);

        if ($pType == '*AFGEVAARDIGDE')
            $return = (strpos($pfunctieVB, 'afgev') !== false);

        if ($pType == '*TRAINER')
            $return = (strpos($pfunctieVB, 'trainer') !== false);

        if ($pType == '*BESTUUR')
            $return = (strpos($pfunctieVB, 'bestuur') !== false);

        if ($pType == '*SPORTIEF')
            $return = (strpos($pfunctieVB, 'sp.staf') !== false);

        if ($pType == '*ONDERBOUW')
            $return = (strpos($pfunctieVB, 'verantw.ob') !== false);

        if ($pType == '*MIDDENBOUW')
            $return = (strpos($pfunctieVB, 'verantw.mb') !== false);

        if ($pType == '*BOVENBOUW')
            $return = (strpos($pfunctieVB, 'verantw.bb') !== false);

        if ($pType == '*SENIORS')
            $return = (strpos($pfunctieVB, 'verantw.sen') !== false);

        if ($pType == '*DOELMANNEN')
            $return = (strpos($pfunctieVB, 'verantw.dm') !== false);

        if ($pType == '*GTEAM')
            $return = (strpos($pfunctieVB, 'verantw.gt') !== false);

        // -------------
        // Einde functie
        // -------------

        return $return;

    }
    // ===================================================================================================
    // Functie: Opvullen werkfile "personen per ploeg"
    //
    // In:  Geen
    //
    // Return:	true/false
    // ===================================================================================================

    Static function FillPersonenPerPloeg() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(Sx::GetClassPath("settings.class"));

        // -------------
        // Leegmaken file
        // --------------

        $sqlStat = "truncate ela_pp_personen_per_ploeg";
        $db->Query($sqlStat);

        // -----
        // Inits
        // -----

        $actiefSeizoen = SSP_settings::GetActiefSeizoen();

        // --------------------
        // OPvullen met spelers
        // --------------------

        $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieVB like '%speler%'";
        $db->Query($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        while ($adRec = $db->Row()){

            $persoon = $adRec->adCode;
            $ploeg = self::GetSpelerPloegId($persoon);

            if ($ploeg) {

                $values = array();

                $values["ppPersoon"] = MySQL::SQLValue($persoon);
                $values["ppPloeg"] = MySQL::SQLValue($ploeg, MySQL::SQLVALUE_NUMBER);
                $values["ppFunctie"] = MySQL::SQLValue('Speler');
                $values["ppSort"] = MySQL::SQLValue(90, MySQL::SQLVALUE_NUMBER);

                $values["ppDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db2->InsertRow("ela_pp_personen_per_ploeg", $values);

            }

        }

        // --------------------------------------
        // OPvullen met trainers & afgevaardigden
        // --------------------------------------

        $sqlStat = "Select * from ssp_vp where vpRecStatus = 'A' and vpSeizoen = '$actiefSeizoen'";
        $db->Query($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        while ($vpRec = $db->Row()){

            $ploeg = $vpRec->vpId;

            $trainers = array();
            if ($vpRec->vpTrainer)
                $trainers[] = $vpRec->vpTrainer;
            if ($vpRec->vpTrainer2)
                $trainers[] = $vpRec->vpTrainer2;
            if ($vpRec->vpTrainer3)
                $trainers[] = $vpRec->vpTrainer3;
            if ($vpRec->vpTrainer4)
                $trainers[] = $vpRec->vpTrainer4;
            if ($vpRec->vpTrainer5)
                $trainers[] = $vpRec->vpTrainer5;

            $afgevaardigden = array();
            if ($vpRec->vpDelege)
                $afgevaardigden[] = $vpRec->vpDelege;
            if ($vpRec->vpDelege2)
                $afgevaardigden[] = $vpRec->vpDelege2;
            if ($vpRec->vpDelege3)
                $afgevaardigden[] = $vpRec->vpDelege3;

            foreach ($trainers as $persoon){

                $values = array();

                $values["ppPersoon"] = MySQL::SQLValue($persoon);
                $values["ppPloeg"] = MySQL::SQLValue($ploeg, MySQL::SQLVALUE_NUMBER);
                $values["ppFunctie"] = MySQL::SQLValue('Trainer');
                $values["ppSort"] = MySQL::SQLValue(10, MySQL::SQLVALUE_NUMBER);

                $values["ppDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db2->InsertRow("ela_pp_personen_per_ploeg", $values);

            }

            foreach ($afgevaardigden as $persoon){

                $values = array();

                $values["ppPersoon"] = MySQL::SQLValue($persoon);
                $values["ppPloeg"] = MySQL::SQLValue($ploeg, MySQL::SQLVALUE_NUMBER);
                $values["ppFunctie"] = MySQL::SQLValue('Afgevaardigde');
                $values["ppSort"] = MySQL::SQLValue(20, MySQL::SQLVALUE_NUMBER);

                $values["ppDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db2->InsertRow("ela_pp_personen_per_ploeg", $values);

            }

        }

        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    // Aanmaken XLS met mail adressen
    //
    // In: Export Mailadres ID
    //
    // Return: None
    // ========================================================================================

    static function CrtMailXLS($pExportId) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once($_SESSION["SX_BASEPATH"] . '/vendor/autoload.php');

        $sqlStat = "Select * from ela_em_export_mailadressen where emId = $pExportId";
        $db->Query($sqlStat);

        if (! $emRec = $db->Row())
            return;

        $where = "";
        if ($emRec->emEnkelActief)
            $where = "adRecStatus = 'A' and (adClubVerlatenEindeSeizoen <> 1 or adRelatieMet like '%T%')";
        if ($emRec->emSelectie == '*VOETBAL')
            if (! $where)
                $where = "adRelatieMet like '%V%'";
            else
                $where .= " and (adRelatieMet like '%V%')";
        if ($emRec->emSelectie == '*TENNIS')
            if (! $where)
                $where = "adRelatieMet like '%T%'";
            else
                $where .= " and (adRelatieMet like '%T%')";

         if ($where)
             $where = "where $where";

        $sqlStat = "Select * from ssp_ad $where order by adNaamVoornaam";

        $db->Query($sqlStat);

        $mailAdressen = array();
        $namen = array();

        $db->Query($sqlStat);

        $aantal = 0;

        while ($adRec = $db->Row()){

            $aantal++;
            if ($aantal > 5000)
                break;

            $mailsUnique = array();
            $mailsUnique[] = $adRec->adMail;
            $mailsUnique[] = $adRec->adSpelerMail;
            $mailsUnique[] = $adRec->adVaderMail;
            $mailsUnique[] = $adRec->adMoederMail;

            $mailsUnique = array_unique($mailsUnique);

            $naam = trim(utf8_encode($adRec->adNaamVoornaam));

            $extraInfo = "";

            if ($adRec->adVoetbalCat) {

                $taRec = SSP_db::Get_SX_taRec('VOETBAL_CAT',$adRec->adVoetbalCat);

                if ($taRec)
                    $extraInfo = $taRec->taName;
                else
                    $extraInfo = $adRec->adVoetbalCat;

            }

            /// --------------
            // Functie VOETBAL
            // ---------------

            if ($adRec->adFunctieVB){

                $functies = explode(",", $adRec->adFunctieVB);

                foreach ($functies as $functie) {

                    if ($functie == 'trainer')
                        continue;
                    if ($functie == 'speler')
                        continue;

                    $fvRec = SSP_db::Get_SSP_fvRec($functie);

                    if ($fvRec)
                        if (! $extraInfo)
                            $extraInfo = $fvRec->fvNaam;
                        else
                            $extraInfo .= ", $fvRec->fvNaam";
                }

            }

            /// ----------
            // Functie SSP
            // ----------

            if ($adRec->adFunctieSSP){

                $functies = explode(",", $adRec->adFunctieSSP);

                foreach ($functies as $functie) {

                    $fsRec = SSP_db::Get_SSP_fsRec($functie);

                    if ($fsRec)
                        if (! $extraInfo)
                            $extraInfo = $fsRec->fvNaam;
                        else
                            $extraInfo .= ", $fsRec->fvNaam";
                }

            }

            // -------------
            // Functie TENNIS
            // --------------

            if ($adRec->adFunctieT)
                if (! $extraInfo)
                    $extraInfo = "Tennis";
                else
                    $extraInfo .= ", Tennis";

            if ($adRec->adRecStatus != 'A')
                if (! $extraInfo)
                    $extraInfo = "HISTORIEK";
                else
                    $extraInfo .= ", HISTORIEK";


            if ($extraInfo)
                $naam = "$naam ($extraInfo)";

            foreach ($mailsUnique as $mail){

                if ($mail) {

                    $index = array_search($mail, $mailAdressen);

                    if ($index === false) {
                        $mailAdressen[] = $mail;
                        $namen[] = $naam;
                     }
                    else {
                        $namen[$index] .= " + $naam";
                    }

                }

            }

        }

        // -----------------------
        // Aanmaken Rapport (XLSX)
        // -----------------------

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 0;

        foreach ($mailAdressen as $index=>$mailAdres) {

            $naam = $namen[$index];

            $row++;

            $cell = "A" . $row;
            $sheet->setCellValue($cell, $mailAdres);
            $cell = "B" . $row;
            $sheet->setCellValue($cell, $naam);

        }

        // -------------------
        // Autosize alle cells
        // -------------------

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {

            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));

            $sheet = $spreadsheet->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var PHPExcel_Cell $cell */
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }

        }

        // ----------
        // Create XLS
        // ----------

        $xlsNaam = "Mailadressen_" .time();

        $path = $_SERVER['DOCUMENT_ROOT'] . "/_files/ela/export_mail_adressen/$xlsNaam.xlsx";

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        // ------
        // Update
        // ------

        $url  = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
        $curDateTime = date('Y-m-d H:i:s');

        $values = array();
        $where = array();

        $values["emAantalRecords"] =  MySQL::SQLValue($row,MySQL::SQLVALUE_NUMBER );
        $values["emPath"] =  MySQL::SQLValue($path);
        $values["emURL"] =  MySQL::SQLValue($url);

        $values["emDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $where["emId"] =  MySQL::SQLValue($pExportId, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("ela_em_export_mailadressen", $values, $where);


        // -------------
        // Einde functie
        // -------------

        return;

    }

    // ========================================================================================
    // Wissen "oude" XLS met mail adressen (ouder dan 1 dag)
    //
    // In: None
    //
    // Return: None
    // ========================================================================================

    static function DelOldMailXLS() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from ela_em_export_mailadressen where date(emDatumCreatie) < current_date";
        $db->Query($sqlStat);

        while ($emRec = $db->Row()){

            $id = $emRec->emId;
            self::DelMailXLS($id);

        }

        $sqlStat = "Delete from ela_em_export_mailadressen where date(emDatumCreatie) < current_date";
        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }
    
    // ========================================================================================
    // Wissen XLS met mail adressen
    //
    // In: Export Mailadres ID
    //
    // Return: None
    // ========================================================================================

    static function DelMailXLS($pExportId){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from ela_em_export_mailadressen where emId = $pExportId";
        $db->Query($sqlStat);

        if (! $emRec = $db->Row())
            return;

        $path = $emRec->emPath;

        unlink($path);

        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    // Opvullen initialen
    //
    // In: Code (*ALL = Alle contacten)
    //
    // Return: None
    // ========================================================================================

    static function FillAdInitialen($pCode){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        if ($pCode == '*ALL')
            $sqlStat = "Select * from ssp_ad where adInitialen is null or adInitialen <= ' '";
        else
            $sqlStat = "Select * from ssp_ad where adCode = '$pCode' and (adInitialen is null or adInitialen <= ' ')";

        $db->Query($sqlStat);

        while ($adRec = $db->Row()){

            $code = $adRec->adCode;
            $initialen = strtoupper(substr($code ,0,2));

            $values = array();
            $where = array();

            $values["adInitialen"] =  MySQL::SQLValue($initialen);

            $where["adCode"] = MySQL::SQLValue($code);

            $db2->UpdateRows("ssp_ad", $values, $where);


        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Ophalen initialen
    //
    // In: Code
    //
    // Return: Initialen
    // ========================================================================================

    static function GetInitialen($pCode){

        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pCode);

        if ($adRec and $adRec->adInitialen)
            return $adRec->adInitialen;
        else
            return "??";

        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    // Ophalen initialen
    //
    // In: Code
    //     Type (*VOORNAAM-NAAM, *NAAM-VOORNAAM, *NAAM, *VOORNAAM)
    //
    // Return: Initialen
    // ========================================================================================

    static function GetNaam($pCode, $pType='*VOORNAAM-NAAM'){

        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pCode);

        if (! $adRec)
            return $pCode;

        $naam = $adRec->adVoornaamNaam;

        switch(strtoupper($pType)){

            case '*VOORNAAM-NAAM':
                $naam = $adRec->adVoornaamNaam;
                break;

            case '*NAAM-VOORNAAM':
                $naam = $adRec->adNaamVoornaam;
                break;

            case '*NAAM':
                $naam = $adRec->adNaam;
                break;

            case '*VOORNAAM':
                $naam = $adRec->adVoornaam;
                break;

        }

        // -------------
        // Einde functie
        // -------------

        return $naam;

    }

    // ========================================================================================
    // Convertie lidgeld betalingen VOETBAL
    //
    // In: Persoon
    //
    // ========================================================================================

    static function ConvBetalingLidgeldVoetbal($pPersoon){

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("eba.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if ($adRec && $adRec->adLidgeldTotaal) {

            $persoon = $adRec->adCode;

            $seizoen = SSP_eba::GetHuidigSeizoen();
            $lidgeldVoor = '*VOETBAL';

            if ($adRec->adLidgeldBedrag1)
                self::RegBetalingLidgeld($persoon, $lidgeldVoor, $seizoen, $adRec->adLidgeldBedrag1, $adRec->adLidgeldDatum1, $adRec->adLidgeldBetaalwijze1, '*CONVERTIE');

            if ($adRec->adLidgeldBedrag2)
                self::RegBetalingLidgeld($persoon, $lidgeldVoor, $seizoen, $adRec->adLidgeldBedrag2, $adRec->adLidgeldDatum2, $adRec->adLidgeldBetaalwijze2, '*CONVERTIE');

            if ($adRec->adLidgeldBedrag3)
                self::RegBetalingLidgeld($persoon, $lidgeldVoor, $seizoen, $adRec->adLidgeldBedrag3, $adRec->adLidgeldDatum3, $adRec->adLidgeldBetaalwijze3, '*CONVERTIE');

            if ($adRec->adLidgeldBedrag4)
                self::RegBetalingLidgeld($persoon, $lidgeldVoor, $seizoen, $adRec->adLidgeldBedrag4, $adRec->adLidgeldDatum4, $adRec->adLidgeldBetaalwijze4, '*CONVERTIE');


        }


        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Convertie lidgeld betalingen TENNIS
    //
    // In: Persoon
    //
    // ========================================================================================

    static function ConvBetalingLidgeldTennis($pPersoon){

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("tennis.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if ($adRec && $adRec->adTennisLidgeldTotaal) {

            $persoon = $adRec->adCode;
            $seizoen = SSP_tennis::GetSeizoen();

            if ($adRec->adTennisLidgeldBedrag1)
                self::RegBetalingLidgeld($persoon, '*TENNIS', $seizoen, $adRec->adTennisLidgeldBedrag1, $adRec->adTennisLidgeldDatum1, $adRec->adTennisLidgeldBetaalwijze1, '*CONVERTIE');

            if ($adRec->adTennisLidgeldBedrag2)
                self::RegBetalingLidgeld($persoon, '*TENNIS', $seizoen, $adRec->adTennisLidgeldBedrag2, $adRec->adTennisLidgeldDatum2, $adRec->adTennisLidgeldBetaalwijze2, '*CONVERTIE');

        }


        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Get persoon mail--adres(sen)
    //
    // In: Persoon
    //     Type (*BASIS, *ALLE)
    //
    // ========================================================================================

    static function GetPersoonMailString($pPersoon, $pType = '*BASIS')
    {

        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (!$adRec)
            return null;

        $mails = array();

        if ($adRec->adMail > ' ')
            $mails[] = $adRec->adMail;

        if ($pTtype = '*ALLE') {

            if ($adRec->adSpelerMail > ' ')
                $mails[] = $adRec->adSpelerMail;

            if ($adRec->adVaderMail > ' ')
                $mails[] = $adRec->adVaderMail;

            if ($adRec->adMoederMail > ' ')
                $mails[] = $adRec->adMoederMail;

        }

        // --------------------
        // Opbouwen mail string
        // --------------------

        $mailString = '';

        foreach ($mails as $mail){

            $mailAdres = trim($mail);

            if ($mailString) {

                if (! strpos($mailString, $mailAdres))
                    $mailString .= "; $mailAdres";
            }
            else
                $mailString = $mailAdres;



        }

        // -------------
        // Einde functie
        // -------------

        return $mailString;

    }

    // ========================================================================================
    // Validate lidgeld betalingen VOETBAL (enkel huidig seizoen)
    //
    // In: Persoon
    //     Seizoen
    //
    // ========================================================================================

    static function ValBetalingLidgeldVoetbal($pPersoon, $pSeizoen = null){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("eba.class"));

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (!$adRec)
            return;

        $huidigSeizoen = SSP_eba::GetHuidigSeizoen();

        if (! $pSeizoen)
            $pSeizoen = $huidigSeizoen;

        // -----------------------------------------------------------------------
        // Bereken totaal bedrag betaald (huidig seizoen) -> Registratie in ssp_ad
        // -----------------------------------------------------------------------

        $lidgeldBetaald = 0;
        $eersteDatum = null;
        $laatsteDatum = null;

        $sqlStat = "Select sum(lbBedrag) as betaald, Max(lbBetaalDatum) AS laatsteDatum, Min(lbBetaalDatum) AS eersteDatum from ela_lb_lidgeld_betalingen where lbPersoon = '$pPersoon' and lbSeizoen = '$huidigSeizoen' and lbLidgeldVoor = '*VOETBAL'";
        $db->Query($sqlStat);

        if ($lbRec = $db->Row())

            //if ($lbRec->betaald != $adRec->adLidgeldTotaal){

                $lidgeldBetaald = $lbRec->betaald;
                $eersteDatum = $lbRec->eersteDatum;
                $laatsteDatum = $lbRec->laatsteDatum;

                $values = array();
                $where = array();

                $values["adLidgeldTotaal"] =  MySQL::SQLValue($lidgeldBetaald, MySQL::SQLVALUE_NUMBER);
                $values["adLidgeldEersteBetaaldatum"] =  MySQL::SQLValue($eersteDatum, MySQL::SQLVALUE_DATE);
                $values["adLidgeldLaatsteBetaaldatum"] =  MySQL::SQLValue($laatsteDatum, MySQL::SQLVALUE_DATE);

                $where["adCode"] =  MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);

                $db->UpdateRows("ssp_ad", $values, $where);

                if (! $eersteDatum){

                    $sqlStat = "Update ssp_ad set adLidgeldEersteBetaaldatum = null, adLidgeldLaatsteBetaaldatum = null where adCode = '$pPersoon'";
                    $db->Query($sqlStat);


                }

            //}

        // -------------------------
        // Bepaal tarief, te betalen
        // -------------------------

        $teBetalen = 0;
        $tariefCode = '';
        $boeteBedrag = 0;
        $lidgeldKorting = 0;
        $lidgeldKortingTekst = '';

        if ($pSeizoen == $huidigSeizoen) {

            $teBetalen = self::GetTebetalenLidgeldVoetbal($pPersoon, $reedsBetaald, $GM, $positie, $specfiekeAfspraak, $boeteBedrag, $tariefCode, $lidgeldKorting, $lidgeldKortingTekst);

        }

        else {


            $sqlStat = "Select * from ela_lb_lidgeld_betalingen where lbPersoon = '$pPersoon' and lbSeizoen = '$pSeizoen' and lbLidgeldVoor = '*VOETBAL'";
            $db->Query($sqlStat);

            if ($lbRec = $db->Row()){

                $teBetalen = $lbRec->lbTeBetalen;
                $tariefCode = $lbRec->lbTariefCode;
                $boeteBedrag = $lbRec->lbBoete;

            }

        }

        $sqlStat = "Select * from ela_lb_lidgeld_betalingen where lbPersoon = '$pPersoon' and lbSeizoen = '$pSeizoen' and lbLidgeldVoor = '*VOETBAL' order by lbBetaalDatum, lbId";
        $db->Query($sqlStat);

        $lbSubtotaal = 0;

        while ($lbRec = $db->Row()){

            $lbSubtotaal += $lbRec->lbBedrag;

            $values = array();
            $where = array();

            $values["lbTariefCode"] =  MySQL::SQLValue($tariefCode, MySQL::SQLVALUE_TEXT);
            $values["lbBoete"] =  MySQL::SQLValue($boeteBedrag, MySQL::SQLVALUE_NUMBER);
            $values["lbTeBetalen"] =  MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);
            $values["lbSubtotaal"] =  MySQL::SQLValue($lbSubtotaal, MySQL::SQLVALUE_NUMBER);

            $where["lbId"] =  MySQL::SQLValue($lbRec->lbId, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("ela_lb_lidgeld_betalingen", $values, $where);



        }

        // ---------------------
        // Registratie in ssp_ad
        // ---------------------

        if ($pSeizoen == $huidigSeizoen){

            $adRec = SSP_db::Get_SSP_adRec($pPersoon);

            $proefperiode = $adRec->adProefperiode;
            $lidgeldVoldaanVB = $adRec->adLidgeldVoldaanVB;
            $lidgeldBetaald = $adRec->adLidgeldTotaal;
            $clubVerlatenEindeSeizoen = $adRec->adClubVerlatenEindeSeizoen;

            $lidgeldVoldaanVB = 'NEE';

            if ($proefperiode and ! $lidgeldBetaald)
                $lidgeldVoldaanVB = 'PROEF';

            if ($lidgeldBetaald)
                $proefperiode = 0;

            if ((! $lidgeldBetaald) and (! $proefperiode))
                $lidgeldVoldaanVB = 'NEE';
            if ($lidgeldBetaald and $lidgeldBetaald < $teBetalen)
                $lidgeldVoldaanVB = 'DEEL';
            if ($lidgeldBetaald >= $teBetalen)
                $lidgeldVoldaanVB = 'JA';

            if ($adRec->adLidgeldGratis)
                $lidgeldVoldaanVB = 'JA';

            if ($tariefCode == 'NVT')
                $lidgeldVoldaanVB = 'NVT';

            if ($clubVerlatenEindeSeizoen)
                $lidgeldVoldaanVB = 'NVT';

            $values = array();
            $where = array();

            $values["adVoetbalTariefCode"] =  MySQL::SQLValue($tariefCode, MySQL::SQLVALUE_TEXT);
            $values["adVoetbalTeBetalen"] =  MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);
            $values["adLidgeldVoldaanVB"] =  MySQL::SQLValue($lidgeldVoldaanVB, MySQL::SQLVALUE_TEXT);
            $values["adProefperiode"] =  MySQL::SQLValue($proefperiode, MySQL::SQLVALUE_NUMBER);

            $values["adLidgeldKorting"] =  MySQL::SQLValue($lidgeldKorting, MySQL::SQLVALUE_NUMBER);
            $values["adLidgeldKortingTekst"] =  MySQL::SQLValue($lidgeldKortingTekst, MySQL::SQLVALUE_TEXT);

            // Kledij mag besteld vanaf betaling EUR 70
            if (($lidgeldBetaald >= 70) or ($lidgeldVoldaanVB == 'JA'))
                $values["adKledijMagBesteld"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);

            $where["adCode"] =  MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);

            $db->UpdateRows("ssp_ad", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Validate lidgeld betalingen TENNIS
    //
    // In: Persoon
    //
    // ========================================================================================

    static function ValBetalingLidgeldTennis($pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("tennis.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (!$adRec)
            return;

        $huidigSeizoen = SSP_tennis::GetSeizoen();

        $aaRec = null;

        $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaSeizoen = '$huidigSeizoen' and aaCode = '$pPersoon'";
        $db->Query($sqlStat);

        $aaRec = $db->Row();

        // -----------------------------------------------------------------------
        // Bereken totaal bedrag betaald (huidig seizoen) -> Registratie in ssp_ad
        // -----------------------------------------------------------------------

        $lidgeldBetaald = $adRec->adTennisLidgeldTotaal;
        $tennisLidgeldDatum = null;

        $sqlStat = "Select sum(lbBedrag) as betaald, MAX(lbBetaalDatum) AS betaaldatum from ela_lb_lidgeld_betalingen where lbPersoon = '$pPersoon' and lbSeizoen = '$huidigSeizoen' and lbLidgeldVoor = '*TENNIS'";

        $db->Query($sqlStat);

        if ($lbRec = $db->Row()) {

            $lidgeldBetaald = $lbRec->betaald;
            $tennisLidgeldDatum = $lbRec->betaaldatum;

            if ($tennisLidgeldDatum < '2000-01-01') {
                $tennisLidgeldDatum = null;
            }

            //if ($lbRec->betaald != $adRec->adTennisLidgeldTotaal) {

                $values = array();
                $where = array();

                $values["adTennisLidgeldTotaal"] = MySQL::SQLValue($lidgeldBetaald, MySQL::SQLVALUE_NUMBER);
            $values["adTennisLidgeldLaatsteBetaaldatum"] = MySQL::SQLValue($tennisLidgeldDatum, MySQL::SQLVALUE_DATE);

                $where["adCode"] = MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);

                $db->UpdateRows("ssp_ad", $values, $where);

                if (!$tennisLidgeldDatum){

                    $sqlStat = "Update ssp_ad set adTennisLidgeldLaatsteBetaaldatum= null where adCode = '$pPersoon'";
                    $db->Query($sqlStat);

                }



            // }
        }

        // -------------------------
        // Bepaal tarief, te betalen
        // -------------------------

        $teBetalen = 0;

        $tariefCode = $adRec->adTennisTariefCode;

        if ($aaRec){

            $teBetalen = $aaRec->aaTebetalen;

            if (! $tariefCode)
                $tariefCode = $aaRec->aaTebetalen;

        } else {

            $taRec = SSP_db::Get_SX_taRec('TENNIS_TARIEVEN', $tariefCode);
            $teBetalen = $taRec->taNumData;

        }


        if ($adRec->adTennisSpecifiekBedrag)
            $teBetalen = $adRec->adTennisSpecifiekBedrag;

        $sqlStat = "Select * from ela_lb_lidgeld_betalingen where lbPersoon = '$pPersoon' and lbSeizoen = '$huidigSeizoen' and lbLidgeldVoor = '*TENNIS' order by lbBetaalDatum, lbId";
        $db->Query($sqlStat);

        $lbSubtotaal = 0;

        while ($lbRec = $db->Row()){

            $lbSubtotaal += $lbRec->lbBedrag;

            $values = array();
            $where = array();

            $values["lbTariefCode"] =  MySQL::SQLValue($tariefCode, MySQL::SQLVALUE_TEXT);
            $values["lbTeBetalen"] =  MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);
            $values["lbSubtotaal"] =  MySQL::SQLValue($lbSubtotaal, MySQL::SQLVALUE_NUMBER);

            $where["lbId"] =  MySQL::SQLValue($lbRec->lbId, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("ela_lb_lidgeld_betalingen", $values, $where);

        }

        // ----------------------------------------------
        // Registratie te betalen, betaalstatus in ssp_ad
        // ---------------------------------------------

        $tennisLidgeldVoldaan = 'NEE';

        if ($lidgeldBetaald and $lidgeldBetaald < $teBetalen)
            $tennisLidgeldVoldaan = 'DEEL';

        if ($lidgeldBetaald >= $teBetalen and $teBetalen > 0)
            $tennisLidgeldVoldaan = 'JA';

        if ($tariefCode == '*GRATIS')
            $tennisLidgeldVoldaan = 'JA';

        if ((! $tariefCode) and ($tennisLidgeldVoldaan == 'NEE'))
            $tennisLidgeldVoldaan = 'NVT';


        $values = array();
        $where = array();

        $values["adTennisTariefCode"] =  MySQL::SQLValue($tariefCode, MySQL::SQLVALUE_TEXT);
        $values["adTennisTeBetalen"] =  MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);
        $values["adTennisLidgeldVoldaan"] =  MySQL::SQLValue($tennisLidgeldVoldaan, MySQL::SQLVALUE_TEXT);

        $where["adCode"] =  MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);

        $db->UpdateRows("ssp_ad", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Registratie betaling lidgeld
    //
    // In: Persoon
    //     Lidgeld voor (*¨VOETBAL, *TENNIS)
    //     Seizoen
    //     Bedrag
    //     Datum (yyyy-mm-dd)
    //     User
    //     EFIN Rekeninig Detail ID (optioneel)
    //
    //
    // Return: ID (null indien betaling reeds geregistreerd)
    // ========================================================================================

    static function RegBetalingLidgeld($pPersoon, $pLidgeldVoor, $pSeizoen, $pBedrag, $pBetaalDatum, $pBetaalwijze , $pUser , $pEfinRd= null){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // -----------------------------------------------------------------
        // Convertie code betaalwijze (eigenlijk enkel nodig voor convertie)
        // -----------------------------------------------------------------

        $betaalWijze = $pBetaalwijze;

        if ($betaalWijze == 'OVERSCHR')
            $betaalWijze = '*OVERSCHRIJVING';

        if ($betaalWijze == 'CASH')
            $betaalWijze = '*CASH';

        // -------------------------------------
        // Check of betaling reeds geregistreerd
        // -------------------------------------

        $sqlStat = "Select count(*) as aantal from ela_lb_lidgeld_betalingen where lbPersoon = '$pPersoon' and lbLidgeldVoor = '$pLidgeldVoor' and lbSeizoen = '$pSeizoen' and lbBedrag = $pBedrag and lbBetaalDatum = '$pBetaalDatum' and lbBetaalWijze = '$betaalWijze'";

        $db->Query($sqlStat);

        if ($lbRec = $db->Row())
            if ($lbRec->aantal)
                return null;

        // -----------
        // Registratie
        // -----------

        $values = array();

        $curDateTime = date('Y-m-d H:i:s');

        $values["lbPersoon"] = MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);
        $values["lbLidgeldVoor"] = MySQL::SQLValue($pLidgeldVoor, MySQL::SQLVALUE_TEXT);
        $values["lbSeizoen"] = MySQL::SQLValue($pSeizoen, MySQL::SQLVALUE_TEXT);

        $values["lbBedrag"] = MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);
        $values["lbBetaalDatum"] = MySQL::SQLValue($pBetaalDatum, MySQL::SQLVALUE_DATE);
        $values["lbBetaalWijze"] = MySQL::SQLValue($betaalWijze, MySQL::SQLVALUE_TEXT);

        if ($pEfinRd)
            $values["lbEfinRD"] = MySQL::SQLValue($pEfinRd, MySQL::SQLVALUE_NUMBER);

        $values["lbUserCreatie"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
        $values["lbDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["lbUserUpdate"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
        $values["lbDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $id = $db->InsertRow("ela_lb_lidgeld_betalingen", $values);

        // -------------
        // Einde functie
        // -------------

        return $id;


    }

    // ========================================================================================
    // Check keuze-formulier Corona korting
    //
    // In: Persoon
    //
    // Return: String met korting info of keuzekode (*STEUN, *EETEVENTS, *LIDGELD) of *NVT
    //
    // ========================================================================================

    static function ChkCoronaKeuze($pPersoon){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $return = '*NVT';

        $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pPersoon' and lkKortingOpLidgeld >  0";
        $db->Query($sqlStat);

        if ($lkRec = $db->Row()) {

            if ($lkRec->lkKeuze <> '*OPEN')
                $return = $lkRec->lkKeuze;
            else {

                $korting = $lkRec->lkKortingOpLidgeld;

                $return = "Compensatie omwille van Corona: $korting EUR ";

            }

        }

        // -------------
        // Einde functie
        // -------------

        return $return;

    }



    // ========================================================================================
    // Registratie "Corona Keuze"
    //
    // In: Keuze
    //     Persoon
    //
    // Return: Registratie OK?
    //
    // ========================================================================================

    static function RegCoronaKeuze($pKeuze, $pPersoon){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $adRec = SSP_db::Get_SSP_adRec($pPersoon);

        if (! $adRec)
            return false;

        $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pPersoon'";
        $db->Query($sqlStat);

        if (! $lkRec = $db->Row())
            return false;

        // -----------------
        // Registratie keuze
        // -----------------

        $values = array();
        $where = array();

        $curDateTime = date('Y-m-d H:i:s');

        $values["lkKeuze"] =  MySQL::SQLValue($pKeuze, MySQL::SQLVALUE_TEXT);
        $values["lkDatumRegistratieKeuze"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $where["lkPersoon"] =  MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);

        $db->UpdateRows("ela_lk_lidgeld_keuze", $values, $where);

        // -------------------
        //  Herbereken lidgeld
        // -------------------

        self::ValBetalingLidgeldVoetbal($pPersoon);

        // ---------------
        // Bevestigingsmail
        // ----------------

        $taRec = $taRec = SSP_db::Get_SX_taRec('ELA_CORONA_KEUZE', $pKeuze);

        $keuzeNaam = $taRec->taDescription;

        $mailBody = '<html><body>';
        $mailBody .= 'Beste,<br/><br/>';
        $mailBody .= 'we noteerden volgende keuze ivm de Corona korting:<br/><br/>';
        $mailBody .= "Speler: $adRec->adVoornaamNaam<br/><br/>";
        $mailBody .= "Bedrag korting: $lkRec->lkKortingOpLidgeld EUR<br/><br/>";
        $mailBody .= "Keuze: $keuzeNaam<br/><br/>";
        $mailBody .= "Sportieve groet,<br/><br/>";
        $mailBody .= "Schelle Sport";
        $mailBody .= '</body></html>';

        $mailTo = self::GetPersoonMailString($pPersoon, '*ALLE');
        // $mailTo = "gvh@vecasoftware.com";
        $mailBCC = "gvh@vecasoftware.com";

        $fromMail = "secretariaat@schellesport.be";
        $fromName = "Schelle Sport - Secretariaat";

        $mailSubject = "Schelle Sport - Keuze Corona korting";

        SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName, null, 'UTF-8', '', null);

        // -------------
        // Einde functie
        // -------------

        return true;

    }
    // ========================================================================================
    // Set switch "Playtomic"
    //
    // In: Persoon
    //     Switch - true, false
    //
    // Return: None...
    //
    // ========================================================================================

    static function SetPlaytomic($pPersoon, $pSwitch){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        if ($pSwitch == true)
            $sqlStat = "Update ssp_ad set adPlaytomic = 1 where adCode = '$pPersoon'";
        else
            $sqlStat = "Update ssp_ad set adPlaytomic = 0 where adCode = '$pPersoon'";

        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Get extra korting Eetevents
    //
    // In: Persoon
    //
    // Return: Extra korting
    //
    // ========================================================================================

    static function GetExtraKortingEetevents($pPersoon){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $extraKorting = 0;

        $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pPersoon' and lkKeuze = '*EETEVENTS'";
        $db->Query($sqlStat);

        if ($lkRec = $db->Row())
            $extraKorting = $lkRec->lkKortingOpLidgeld;

        // -------------
        // Einde functie
        // -------------

        return $extraKorting;

    }

    // ========================================================================================
    // Create/ Update file "kledijverdeling"
    //
    // In: Persoon
    // ========================================================================================

    static function CrtAlleRecordsKledijverdeling(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adRelatieMet like '%V%' and adFunctieVB > ' ' and adFunctieVB <> 'abonnee'";
        $sqlStat .= " and adFunctieVB <> 'afgev' and adFunctieVB <> 'scheids' and adFunctieVB <> 'kinesist' ";
        $db->Query($sqlStat);

        while ($adRec = $db->Row())
            self::CrtRecKledijverdeling($adRec->adCode);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Create/ Update file "kledijverdeling"
    //
    // In: Persoon
    // ========================================================================================

    static function CrtRecKledijverdeling($pPersoon){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("settings.class"));
        include_once(SX::GetClassPath("eba.class"));

        // ------------------------
        // Ophalen "huidig seizoen"
        // ------------------------

        $huidigSeizoen = SSP_settings::GetActiefSeizoen();

        // ------------------------------------------------
        // Ophalen bestaande record (indien reeds bestaand)
        // -----------------------------------------------

        $sqlStat = "Select * from ela_kv_kledijverdeling where kvPersoon = '$pPersoon' and kvSeizoen = '$huidigSeizoen'";
        $db->Query($sqlStat);

        $kvRec = $db->Row();

        // ---------------
        // Status lidkaart
        // ---------------

        $lidkaartStatus = 'Niet Aangemaakt';

        $sqlStat = "Select * from ela_ka_kaarten where kaPersoon = '$pPersoon' and kaSeizoen = '$huidigSeizoen' and kaType = '*LIDKAART_VB' and kaPrinted = 1";
        $db->Query($sqlStat);

        while($kaRec = $db->Row()) {

            if (!$kaRec->kaOntvangen) {
                $lidkaartStatus = 'Te Ontvangen';
                $lidkaartCode = $kaRec->kaKaartCode;
                break;
            }

            if ($kaRec->kaOntvangen) {
                $lidkaartStatus = 'Reeds Ontvangen';
                $lidkaartCode = $kaRec->kaKaartCode;
            }

        }


        // -----------
        // Af te halen
        // -----------

        $afTeHalen= 'nada';

        if ($lidkaartStatus == 'Te Ontvangen')
            $afTeHalen = "<li>Lidkaart</li>";

        $sqlStat = "Select distinct(ohOrdernummer) as pakbon from eba_oh_order_headers";
        $sqlStat .= " inner join eba_od_order_detail on odOrderNummer = ohOrderNummer and odLeverStatus = '*KLAAR'";
        $sqlStat .= " where ohKlant = '$pPersoon' and ohVolledigAfgewerkt <> 1 and ohKlaarVoorAfleveren = 1";

        $db->Query($sqlStat);

        while ($ohRec = $db->Row()){

            $pakbon = $ohRec->pakbon;

            $enkelStock = SSP_eba::ChkPakbonEnkelStockTeLeveren($pakbon);

            if ($enkelStock)
                $pakbon = "<b>$pakbon (ENKEL STOCK)</b>";
            else
                $pakbon = "<b>$pakbon (PAKKET)</b>";

            if ($afTeHalen == 'nada')
                $afTeHalen = '';

            $afTeHalen .= "<li>Pakbon: $pakbon</li>";

        }

        if ($afTeHalen and $afTeHalen != 'nada')
            $afTeHalen = "<ul>$afTeHalen</ul>";

        // ----------
        // UPDATE/ADD
        // ----------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();
        $where = array();

        $values["kvPersoon"] =  MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);
        $values["kvSeizoen"] =  MySQL::SQLValue($huidigSeizoen, MySQL::SQLVALUE_TEXT);
        $values["kvLidkaartCode"] =  MySQL::SQLValue($lidkaartCode, MySQL::SQLVALUE_TEXT);
        $values["kvLidkaartStatus"] =  MySQL::SQLValue($lidkaartStatus, MySQL::SQLVALUE_TEXT);

        if ($kvRec->kvLidkaartStatus == 'Ontvangen')
            $values["kvLidkaartRedenNietAfgegeven"] =  null;

        $values["kvAfTeHalen"] =  MySQL::SQLValue($afTeHalen, MySQL::SQLVALUE_TEXT);

        if ($kvRec) {

            $where["kvId"] = MySQL::SQLValue($kvRec->kvId, MySQL::SQLVALUE_NUMBER);

            $db->UpdateRows("ela_kv_kledijverdeling", $values, $where);

        } else {

            $values["kvDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["kvUserUpdate"] =  MySQL::SQLValue('*SYS', MySQL::SQLVALUE_TEXT);
            $values["kvDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db->InsertRow("ela_kv_kledijverdeling", $values);
        }

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // -----------
    // Einde CLASS
    // -----------


}
?>