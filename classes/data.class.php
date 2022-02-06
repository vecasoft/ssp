<?php


class SSP_data
{ // define the class


    // ========================================================================================
    //  Ophalen data "In de kijker (Home-berichten)"
    //
    // In:	Niets
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_SSP_HB() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetSxClassPath("tools.class"));

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from ssp_hb where hbActief = 1 limit 10";
        $db->Query($sqlStat);

        $i = 0;

        while ($hbRec = $db->Row()) {

            $i++;

            if ($i > 10)
                break;


            $fotoPath = null;

            $fotos = json_decode($hbRec->hbFoto);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoPath = SX_tools::GetFilePath($foto->name);
                }
            }

            $tekst = $hbRec->hbTekstRechts . "<br/" . $hbRec->hbTekstOnder;

            $arrDATA['hbTitelBoven'] = utf8_encode($hbRec->hbTitelBoven);
            $arrDATA['hbTitel'] = utf8_encode($hbRec->hbTitel);
            $arrDATA['hbFoto'] = utf8_encode($fotoPath);
            $arrDATA['hbFotoGroot'] = utf8_encode($hbRec->hbFotoGroot);
            $arrDATA['hbTekstRechts'] = utf8_encode(nl2br($hbRec->hbTekstRechts));
            $arrDATA['hbTekstOnder'] = utf8_encode(nl2br($hbRec->hbTekstOnder));
            $arrDATA['hbTekst'] = utf8_encode(nl2br($tekst));
            $arrDATA['hbToonTitelBoven'] = utf8_encode($hbRec->hbToonTitelBoven);

            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;


    }


    // ========================================================================================
    //  Ophalen data "Homepage Blocks"
    //
    // In:	Niets
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_SX_HB() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from sx_hb_homepage_blocks where hbRecStatus = 'A' order by hbSort";
        $db->Query($sqlStat);

        $i = 0;

        while ($hbRec = $db->Row()) {

            $i++;

            // ----
            // FOTO
            // ----

            $fotoPath = null;

            $fotos = json_decode($hbRec->hhPhoto);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoPath = SX_tools::GetFilePath($foto->name);
                }
            }

            // ----------------
            // BACKGROUND-COLOR
            // ----------------

            $hbColorBackground = 'white';

            if ($hbRec->hbColorBackground){

                $taRec = SSP_db::Get_SX_taRec('SX_HB_COLOR', $hbRec->hbColorBackground);

                if ($taRec->taAlfaData)
                    $hbColorBackground = $taRec->taAlfaData;

            }


            // ----------
            // Build JSON
            // ----------

            $arrDATA['hbBlockType'] = utf8_encode($hbRec->hbBlockType);
            $arrDATA['hbTitle'] = utf8_encode($hbRec->hbTitle);
            $arrDATA['hbPhoto'] = utf8_encode($fotoPath);
            $arrDATA['hbTextHomepage'] = utf8_encode(nl2br($hbRec->hbTextHomepage));
            $arrDATA['hbTextExtented'] = utf8_encode(nl2br($hbRec->hbTextExtented));
            $arrDATA['hbColorBackground'] = utf8_encode($hbColorBackground);

            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;


    }
    // ========================================================================================
    //  Ophalen data "Artikel-detail" (paragrafen)
    //
    // In:	Artikel-ID
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_SX_AD($pArtikel) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("content.class"));

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from sx_ad_article_detail where adArticle = $pArtikel and adRecStatus = 'A' order by adSort, adId";
        $db->Query($sqlStat);

        $i = 0;

        while ($adRec = $db->Row()) {

            $i++;

            // ------
            // FOTO's
            // ------

            $col1Picture = null;
            $col2Picture = null;

            $fotos = json_decode($adRec->adCol1Picture);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $col1Picture = SX_tools::GetFilePath($foto->name);
                }
            }

            $fotos = json_decode($adRec->adCol2Picture);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $col2Picture = SX_tools::GetFilePath($foto->name);
                }
            }


            // ---------
            // Textblock
            // ---------

            $col1HTML = null;
            $col2HTML = null;

            if ($adRec->adCol1Textblock)
                $col1HTML = $adRec->adCol1Textblock;
            if ($adRec->adCol2Textblock)
                $col2HTML = $adRec->adCol2Textblock;

            // ------------
            // Code -> HTML
            // -------------

            if ($adRec->adCol1Code){

                $codeName = $adRec->adCol1Code;

                $sqlStat = "Select * from sx_co_code where coName = '$codeName'";
                $db->Query($sqlStat);

                if ($coRec = $db->Row())
                    $col1HTML = $coRec->coCode;

            }


            // -----
            // Show
            // -----

            $col1Show = '*NO';
            $col2Show = '*NO';

            if ($adRec->adCol1Width) {

                if ($col1HTML)
                    $col1Show = '*HTML';
                if ($col1Picture)
                    $col1Show = '*PICTURE';

            }

            if ($adRec->adCol2Width) {

                if ($col2HTML)
                    $col2Show = '*HTML';
                if ($col2Picture)
                    $col2Show = '*PICTURE';

            }
            // -----
            // Props
            // -----

            $col1Props = array();
            $col2Props = array();
  

            if ($adRec->adCol1Width) {
                $prop = "sm" . $adRec->adCol1Width;
                $col1Props["$prop"] = "true";
            }
            if ($adRec->adCol1WidthMobile){
                $prop = "xs" . $adRec->adCol2WidthMobile;
                $col2Props["$prop"] = "true";
            }


            if ($adRec->adCol2Width) {
                $prop = "sm" . $adRec->adCol2Width;
                $col2Props["$prop"] = "true";
            }
            if ($adRec->adCol1WidthMobile){
                $prop = "xs" . $adRec->adCol1WidthMobile;
                $col1Props["$prop"] = "true";
            }


            $classes = "mx-auto";

            if ($adRec->adMarginTop)
                $classes = "$classes mt-" . $adRec->adMarginTop;
            else
                $classes = "$classes my-auto";

            // ----------
            // Build JSON
            // ----------

            $arrDATA['classes'] = utf8_encode($classes);

            $arrDATA['col1Show'] = $col1Show;
            $arrDATA['col1Props'] =  $col1Props;
            $arrDATA['col1HTML'] = utf8_encode($col1HTML);
            $arrDATA['col1Picture'] = utf8_encode($col1Picture);
            $arrDATA['adCol1MarginLeft'] = utf8_encode($adRec->adCol1MarginLeft);
            $arrDATA['adCol1MarginRight'] = utf8_encode($adRec->adCol1MarginRight);

            $arrDATA['col2Show'] = $col2Show;
            $arrDATA['col2Props'] =  $col2Props;
            $arrDATA['col2HTML'] = utf8_encode($col2HTML);
            $arrDATA['col2Picture'] = utf8_encode($col2Picture);
            $arrDATA['col2Props'] =  $col2Props;
            $arrDATA['adCol2MarginLeft'] = utf8_encode($adRec->adCol2MarginLeft);
            $arrDATA['adCol2MarginRight'] = utf8_encode($adRec->adCol2MarginRight);

            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;

    }

    // ========================================================================================
    //  Ophalen artikels voor webshop
    //
    // In:	Niets
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_WEBSHOP_AR() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("menu.class"));
        include_once(SX::GetSxClassPath("sessions.class"));
        include_once(SX::GetClassPath("eba.class"));

        $userId = SX_sessions::GetSessionUserId();

        $arrDATA = array();
        $arrJSON = array();

        $arrDATA2 = array();
        $arrJSON2 = array();

        $arrJSONglobal = array();

        $rubrieken = array();

        // ----------------------
        // Pakketten (in lidgeld)
        // ----------------------

        $sqlStat = "Select * from eba_pk_pakketten inner join eba_ra_rubriek_artikels on raPakket = pkId Inner join eba_ru_rubrieken on ruId = raRubriek and ruRecStatus = 'A' where pkRecStatus = 'A' order by ruSort, ruId, raSort, raId ";

        $db->Query($sqlStat);

        while($pkRec = $db->Row()){

            $rubriek = $pkRec->ruId;


            if ( ! SSP_eba::ChkRubriekDoelgroep($rubriek, $userId))
                continue;

            $doelgroep = $pkRec->pkDoelgroep;
            if ($doelgroep and (! SSP_eba::ChkDoelgroep($userId, $doelgroep)))
                continue;

            $rubrieken[] = $pkRec->ruNaam;

            // ------
            // FOTO's
            // ------

            $fotoPath = null;
            $fotoGrootPath = null;

            $fotos = json_decode($pkRec->pkFoto);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoPath = SX_tools::GetFilePath($foto->name);
                }
            }

            $fotos = json_decode($pkRec->pkFotoGroot);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoGrootPath = SX_tools::GetFilePath($foto->name);
                }
            }

            // ----------
            // Build JSON
            // ----------

            $arrDATA['arId'] = 0;
            $arrDATA['pkId'] = $pkRec->pkId;
            $arrDATA['type'] = '*PAKKET';
            $arrDATA['naam'] = utf8_encode($pkRec->pkNaam);
            $arrDATA['omschrijving'] = utf8_encode(nl2br($pkRec->pkOmschrijving));

            $prijs = $pkRec->pkPrijs + 0;

            if (! $pkRec->pkPrijs)
                $arrDATA['prijsinfo'] =  "In lidgeld inbegrepen";
            else
                $arrDATA['prijsinfo'] =  "Prijs: $prijs ";

            $rubrieken[] = $pkRec->ruNaam;

            $arrDATA['foto'] = utf8_encode($fotoPath);
            $arrDATA['fotoGroot'] = utf8_encode($fotoGrootPath);

            $arrDATA['rubriek'] = utf8_encode($pkRec->ruNaam);

            // ----------------------
            // Onderliggende artikels
            // ----------------------

            $pakketId = $pkRec->pkId;

            $arrARTIKELS = array();

            $sqlStat = "Select * from eba_pa_pakket_artikels inner join eba_ar_artikels on arId = paArtikel where paPakket = $pakketId order by paSort, paId";
            $db2->Query($sqlStat);

            while ($paRec = $db2->Row()){

                $artikel = $paRec->paArtikel;;

                $arrARTIKEL = array();
                $arrARTIKEL['arId'] = $paRec->paArtikel;
                $arrARTIKEL['pkId'] = $paRec->paPakket;
                $arrARTIKEL['naam'] = $paRec->arNaam;

                $sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $artikel and amRecStatus = 'A' order by amSort, amMaat";
                $db3->Query($sqlStat);

                $maten = array();

                while ($amRec = $db3->Row())
                    $maten[] = $amRec->amMaat;

                $arrARTIKEL['maten'] = $maten;

                $arrARTIKELS[] = $arrARTIKEL;

            }

            $arrDATA['pakketArtikels'] = $arrARTIKELS;


            $arrJSON[] = $arrDATA;
        }

        // --------------------------
        // Artikels (bijbestellingen)
        // --------------------------

        $sqlStat = "Select * from eba_ar_artikels inner join eba_ra_rubriek_artikels on raArtikel = arId and raRecStatus = 'A' Inner join eba_ru_rubrieken on ruId = raRubriek and ruRecStatus = 'A' where arRecStatus = 'A' and arWebshop = 1 order by ruSort, ruId, raSort, raId ";

        $db->Query($sqlStat);

        while ($arRec = $db->Row()) {

            $rubriek = $arRec->ruId;

            if ( ! SSP_eba::ChkRubriekDoelgroep($rubriek, $userId))
                continue;

            $rubrieken[] = $arRec->ruNaam;

            // ------
            // FOTO's
            // ------

            $fotoPath = null;
            $fotoGrootPath = null;

            $fotos = json_decode($arRec->arFoto);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoPath = SX_tools::GetFilePath($foto->name);
                }
            }

            $fotos = json_decode($arRec->arFotoGroot);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoGrootPath = SX_tools::GetFilePath($foto->name);
                }
            }

            // ----------
            // Build JSON
            // ----------

            $arrDATA['arId'] = $arRec->arId;
            $arrDATA['pkId'] = 0;
            $arrDATA['type'] = '*ARTIKEL';
            $arrDATA['naam'] = utf8_encode($arRec->arNaam);
            $arrDATA['omschrijving'] = utf8_encode(nl2br($arRec->arOmschrijving));
            $arrDATA['prijsinfo'] =  utf8_encode('Prijs: ' . SSP_eba::GetArtikelPrijsInfo($arRec->arId));

            $arrDATA['foto'] = utf8_encode($fotoPath);
            $arrDATA['fotoGroot'] = utf8_encode($fotoGrootPath);

            $arrDATA['rubriek'] = utf8_encode($arRec->ruNaam);

            // Maten
            $maten = array();

            $artikel = $arRec->arId;
            $sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $artikel order by amSort";
            $db2->Query($sqlStat);
            while ($amRec = $db2->Row())
                $maten[] = $amRec->amMaat;

            $arrDATA['maten'] = $maten;

            $arrJSON[] = $arrDATA;

        }

        $rubrieken = array_unique($rubrieken);

        foreach ($rubrieken as $rubiek) {

            $arrDATA2['rubriek'] = utf8_encode($rubiek);
            $arrJSON2[] = $arrDATA2;

        }

        // ----------
        // Build JSON
        // ----------


        $arrJSONglobal[]= $arrJSON;
        $arrJSONglobal[]= $arrJSON2;

        $json = json_encode($arrJSONglobal);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }

    // ========================================================================================
    //  Ophalen overzicht bestellingen webshop
    //
    // In:	Niets
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_WEBSHOP_OVERVIEW() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("menu.class"));
        include_once(SX::GetSxClassPath("sessions.class"));
        include_once(SX::GetClassPath("eba.class"));

        $userId = SX_sessions::GetSessionUserId();
        $adRec = SSP_db::Get_SSP_adRec($userId);

        $arrORDER = array();

        $arrJSON = array();
        $arrJSONGlobal = array();

        $sqlStat = "Select * from eba_oh_order_headers left outer join eba_pk_pakketten on pkId = ohPakket where ohKlant ='$userId' and ohOrderDatum > DATE_SUB( CURDATE( ) ,INTERVAL 2 YEAR ) order by ohOrderDatum desc, ohOrdernummer desc";
        $db->Query($sqlStat);

        while ($ohRec = $db->Row()) {

            $ordernummer = $ohRec->ohOrdernummer;

            // -----------
            // STATUS-info
            // -----------

            $wachtOpBetaling = false;
            $magGewist = false;
            $kanAfgehaald = false;
            $afgehandeld = false;

            SSP_eba::GetOrderHeaderStatus($ordernummer, $wachtOpBetaling,$magGewist, $kanAfgehaald, $afgehandeld);

            $kledijbonBedrag = 0;
            if ($ohRec->ohBetaalwijze1 == 'KLEDIJBON')
                $kledijbonBedrag += $ohRec->ohBetaalBedrag1;
            if ($ohRec->ohBetaalwijze2 == 'KLEDIJBON')
                $kledijbonBedrag += $ohRec->ohBetaalBedrag2;

            If ($ohRec->ohBetaalStatus == '*NOK') {

                $restBedrag = $ohRec->ohTotaalPrijs -$ohRec->ohBetaalTotaal + 0;
                $orderStatus = "Wacht op betaling ($restBedrag EUR)";

            }

            If ($ohRec->ohBetaalStatus == '*LIDGELD')
                $orderStatus =  "Wacht op betaling lidgeld " . $adRec->adVoornaamNaam;

            If ($ohRec->ohBestelStatus == '*BESTELLEN')
                $orderStatus = "Wordt asap besteld bij leverancier";

            If ($ohRec->ohBestelStatus == '*BESTELD')
                $orderStatus = "In bestelling bij leverancier";

            If ($ohRec->ohBestelStatus == '*BACKORDER')
                $orderStatus = "In bestelling bij leverancier";

            If ($ohRec->ohBestelStatus == '*AFHALEN')
                $orderStatus = "In bestelling bij leverancier";

            if ($ohRec->ohLeverStatus == "*PART_KLAAR")
                $orderStatus = "Kan gedeeltelijk afgehaald worden";

            if ($ohRec->ohLeverStatus == "*KLAAR")
                $orderStatus = "Kan afgehaald worden";

            If ($ohRec->ohLeverStatus == '*PART_GELEVERD')
                $orderStatus = "Gedeeltelijk door u afgehaald";

            If ($ohRec->ohLeverStatus == '*GELEVERD')
                $orderStatus = "Volledig afgewerkt";

            // ----------
            // EXTRA-info
            // ----------

            $extraInfo = "Bijbestelling";

            if ($ohRec->ohPakket)
                $extraInfo = $ohRec->pkNaam;

            // ----------
            // PRIJS-info
            // ----------

            $prijsInfo = "";

            if ($ohRec->ohTotaalPrijs > 0) {

                $prijs = $ohRec->ohTotaalPrijs + 0;
                $prijsInfo = "Totaal bedrag: $prijs EUR";

                $reedsBetaald = "";

                if ($ohRec->ohBetaalTotaal) {

                    $reedsBetaald = $ohRec->ohBetaalTotaal + 0;

                }

                if ($reedsBetaald) {

                    $reedsBetaald = "betaald: $reedsBetaald EUR";

                    if ($kledijbonBedrag > 0 && $kledijbonBedrag < $reedsBetaald)
                        $reedsBetaald = "$reedsBetaald - Waarvan $reedsBetaald EUR via je webshoptegoed";
                    elseif ($kledijbonBedrag > 0 && $kledijbonBedrag >= $reedsBetaald)
                        $reedsBetaald = "$reedsBetaald EUR via je webshop-tegoed";

                    $prijsInfo = "$prijsInfo ($reedsBetaald)";

                }

            }  else {

                if ($ohRec->ohPakket and ($ohRec->pkInLidgeld == 1))
                    $prijsInfo = "In lidgeld inbegrepen";
                else
                    $prijsInfo = "Gratis";
            }

            // -------------------
            // ACTIE te ondernemen
            // -------------------

            $actieTeOndernemen = "";

            $restBedrag = $ohRec->ohTotaalPrijs - $ohRec->ohBetaalTotaal;

            if ($restBedrag < 0 )
                $restBedrag = 0;

            if ($ohRec->ohBetaalTotaal > 0)
                $bedragWoord = "rest-bedrag";
            else
                $bedragWoord =  "bedrag";

            if ($wachtOpBetaling == true && $ohRec->ohTotaalPrijs > 0)
                $actieTeOndernemen =  "Betaal het $bedragWoord ($restBedrag EUR) op: IBAN BE67 2930 0744 3187 van Schelle Sport met vermelding: $ohRec->ohGm";

            if ($wachtOpBetaling == true && $ohRec->ohTotaalPrijs == 0 && $ohRec->ohPakket > 0)
                $actieTeOndernemen = "Betaal uw lidgeld op: IBAN BE67 2930 0744 3187 van Schelle Sport met vermelding: $adRec->adGmLidgeldVB";

            if (! $actieTeOndernemen && $ohRec->ohLeverStatus == '*KLAAR')
                $actieTeOndernemen = "Klaar om af te afhalen (tijdens één van de afhaalmomenten)";

            if (! $actieTeOndernemen && $ohRec->ohLeverStatus != '*GELEVERD')
                $actieTeOndernemen = "Je wordt via mail verwittigd als de bestelling afgehaald kan worden.";


            $arrORDER['orderNummer'] = $ordernummer;
            $arrORDER['orderDatum'] = SX_tools::EdtDate($ohRec->ohOrderDatum, '%d %b %Y');
            $arrORDER['orderStatus'] = utf8_encode("Status: $orderStatus");
            $arrORDER['prijsInfo'] = utf8_encode($prijsInfo);
            $arrORDER['extraInfo'] = utf8_encode($extraInfo);
            $arrORDER['actieTeOndernemen'] = utf8_encode($actieTeOndernemen);


            $arrORDER['wachtOpBetaling'] =$wachtOpBetaling;
            $arrORDER['magGewist'] =$magGewist;
            $arrORDER['kanAfgehaald'] = $kanAfgehaald;
            $arrORDER['afgehandeld'] = $afgehandeld;
            
            // ------------
            // order-Detail
            // ------------


            $arrORDERlijnen = array();

            $sqlStat = "Select * from eba_od_order_detail inner join eba_ar_artikels on arId = odArtikel where odOrdernummer = $ordernummer order by odId";
            $db2->Query($sqlStat);
                
            while ($odRec = $db2->Row()){

                $prijs = $odRec->odEenheidsprijs + 0;
                $artikel = $odRec->arNaam;
                $maat = $odRec->odMaat;

                $arrORDERlijn = array();

                if ($maat)
                    $artikel = "$artikel [ $maat ]";

                if ($prijs)
                    $artikel = "$artikel - $prijs EUR";
                else
                    $artikel = "$artikel - pakket prijs";

                $orderlijnStatus = SSP_eba::GetOrderDetailStatus($odRec->odId);

                $arrORDERlijn['artikel'] = utf8_encode($artikel);
                $arrORDERlijn['status'] = utf8_encode($orderlijnStatus);

                $arrORDERlijnen[] = $arrORDERlijn;
  
            }
            
            $arrORDER['orderLijnen'] = $arrORDERlijnen;

            $arrJSON[] = $arrORDER;

        }


        $arrJSONGlobal[] = $arrJSON;


        // ----------
        // Klant-info
        // ----------

        $kledijbonInfo = "";

        $kledijBon = $adRec->adKledijbon + 0;
        $kledijbonBesteed = $adRec->adKledijbonBesteed + 0;

        if ($kledijBon){

            $kledijbonInfo = "Webshop tegoed: $kledijBon EUR";

            if ($kledijbonBesteed){

                $rest = $kledijBon - $kledijbonBesteed;
                if ($rest < 0)
                    $rest = 0;

                $kledijbonInfo  = "$kledijbonInfo - Reeds besteed: $kledijbonBesteed EUR - Rest: $rest EUR";

            }

        }

        $arrJSON = array();

        $arrJSON['naam'] = utf8_encode($adRec->adVoornaamNaam);
        $arrJSON['kledijbonInfo'] = utf8_encode($kledijbonInfo);

        $arrJSONGlobal[] = $arrJSON;

        // ----------
        // Build JSON
        // ----------

        $json = json_encode($arrJSONGlobal);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }
    // ========================================================================================
    //  Ophalen winkelwagen voor webshop
    //
    // In:	Niets
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_WEBSHOP_WW() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("menu.class"));
        include_once(SX::GetClassPath("eba.class"));

        $userId = SX_sessions::GetSessionUserId();
        $adRec = SSP_db::Get_SSP_adRec($userId);

        $arrDATA = array();
        $arrJSON = array();

        $arrJSONglobal = array();

        $sqlStat    = "Select * from eba_ww_winkelwagen inner join eba_ar_artikels on arId = eba_ww_winkelwagen.wwArtikel left outer join eba_pk_pakketten on pkId = wwPakket where wwUserId = '$userId' order by wwId";
        $db->Query($sqlStat);

        $totaalAantal = 0;
        $totaalBedrag = 0;

        while ($wwRec = $db->Row()) {

            $arrDATA['id'] = $wwRec->wwId;
            $arrDATA['naam'] = utf8_encode($wwRec->arNaam);
            $arrDATA['maat'] = utf8_encode($wwRec->wwMaat);

            $prijs = $wwRec->wwPrijs + 0;
            $arrDATA['prijs'] = $prijs;

            $winkelwagenLijn = $wwRec->arNaam;
            if ($wwRec->wwMaat)
                $winkelwagenLijn = "$winkelwagenLijn - $wwRec->wwMaat";

            if ($prijs)
             $winkelwagenLijn = "$winkelwagenLijn: $prijs EUR";
            else {

                if ($wwRec->pkPrijs)
                    $winkelwagenLijn = "$winkelwagenLijn: (pakket-prijs)";
                else
                    $winkelwagenLijn = "$winkelwagenLijn: (pakket)";

            }

            $arrDATA['winkelwagenLijn'] = utf8_encode($winkelwagenLijn);


            $arrJSON[] = $arrDATA;

            $totaalAantal++;
            $totaalBedrag += $wwRec->wwPrijs;

        }

        $arrJSONglobal[] = $arrJSON;

        // -------
        // Totalen
        // -------

        $sqlStat = "Select * from eba_ww_winkelwagen where wwUserId = '$userId' and wwArtikel = 0";
        $db->Query($sqlStat);

        if ($wwRec = $db->Row() and $wwRec->wwPrijs)
            $totaalBedrag = $wwRec->wwPrijs;

        $kledijbonInfo = "";

        $kledijBon = $adRec->adKledijbon + 0;
        $kledijbonBesteed = $adRec->adKledijbonBesteed + 0;

        if ($kledijBon){

            $kledijbonInfo = "Webshop tegoed: $kledijBon EUR";

            if ($kledijbonBesteed){

                $rest = $kledijBon - $kledijbonBesteed;
                if ($rest < 0)
                    $rest = 0;

                $kledijbonInfo  = "$kledijbonInfo - Reeds besteed: $kledijbonBesteed EUR - Rest: $rest EUR";

            }

        }

        $naam = $adRec->adVoornaamNaam;
        if ($adRec->adVoetbalCat){

            $cat = $adRec->adVoetbalCat;

            if ($cat == 'G')
                $cat = "GTEAM";

            if ($adRec->adVoetbalCatWebshop and $adRec->adVoetbalCatWebshop != $adRec->adVoetbalCat)
                $cat = "$cat -> $adRec->adVoetbalCatWebshop";

            $naam = "$naam ($cat)";

        }


        $arrJSON = array();
        $arrJSON['totaalAantal'] = $totaalAantal;
        $arrJSON['totaalBedrag'] = $totaalBedrag + 0;
        $arrJSON['totaalBedragMetCur'] = $totaalBedrag + 0 . ' EUR';

        $arrJSON['kledijbonInfo'] = utf8_encode($kledijbonInfo);
        $arrJSON['naam'] = utf8_encode($naam);


        $arrJSONglobal[] = $arrJSON;

        // ----------
        // Build JSON
        // ----------

        $json = json_encode($arrJSONglobal);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }
    // ========================================================================================
    //  Ophalen data TEST
    //
    // In:	Niets
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_TEST() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from ssp_ad";
        $db->Query($sqlStat);

        $i = 0;

         while ($adRec = $db->Row()) {

            $i++;

            if ($i > 10)
                break;


            $arrDATA['stNaam'] = utf8_encode($i. ':' . $adRec->adNaam . ' ' . $adRec->adVoornaam);
            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;


    }

    // ========================================================================================
    //  Ophalen data "app-groups"
    //
    // In:	Niets (based on session-id)
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_SX_AG() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("menu.class"));
        include_once(SX::GetSxClassPath("apps.class"));
        include_once(SX::GetSxClassPath("sessions.class"));
        include_once(SX::GetSxClassPath("auth.class"));

        $userId = SX_sessions::GetSessionUserId();

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from sx_ap_apps where apType = 'DATA_MANAGEMENT'  and apGroup <> '*OBSOLETE' and apGroup <> '*INLINE' order by apGroup, apSort";
        $db->Query($sqlStat);

        $i = 0;

        $appGroups = array();

        while ($apRec = $db->Row()) {

            $i++;

            // ---------------
            // Check authority
            // ---------------

            $auth = SX_auth::CheckUserAuth($userId, $apRec->apCode, '*ALL');

            if ($auth <> '*OK')
                continue;

            $appGroup = $apRec->apGroup;
            $taRec = SSP_db::Get_SX_taRec('SX_APP_GROUP', $appGroup);
            if ($taRec)
                $appGroup = $taRec->taName;


            $appGroups[] = $appGroup;

        }

        $appGroups = array_unique($appGroups);

        foreach ($appGroups as $appGroup){

            // ----------
            // Build JSON
            // ----------

            $seid = $_SESSION['SEID'];
            $path = "$apRec->apPath&seid=$seid";

            $arrDATA['agGroup'] = utf8_encode($appGroup);

            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;

    }


    // ========================================================================================
    //  Ophalen data "apps"
    //
    // In:	Niets (based on session-id)
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_SX_AP() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("menu.class"));
        include_once(SX::GetSxClassPath("apps.class"));
        include_once(SX::GetSxClassPath("sessions.class"));
        include_once(SX::GetSxClassPath("auth.class"));

        $userId = SX_sessions::GetSessionUserId();

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from sx_ap_apps where apType = 'DATA_MANAGEMENT'  and apGroup <> '*OBSOLETE' and apGroup <> '*INLINE' order by apGroup, apSort";
        $db->Query($sqlStat);

        $i = 0;

        while ($apRec = $db->Row()) {

            $i++;

            // ---------------
            // Check authority
            // ---------------

            $auth = SX_auth::CheckUserAuth($userId, $apRec->apCode, '*ALL');

            if ($auth <> '*OK')
                continue;

            // ----------
            // Build JSON
            // ----------

            $seid = $_SESSION['SEID'];
            $path = "$apRec->apPath&seid=$seid";

            $appGroup = $apRec->apGroup;
            $taRec = SSP_db::Get_SX_taRec('SX_APP_GROUP', $appGroup);
            if ($taRec)
                $appGroup = $taRec->taName;

            $arrDATA['apName'] = utf8_encode($apRec->apName);
            $arrDATA['apDescription'] = utf8_encode($apRec->apDescription);
            $arrDATA['apPath'] = utf8_encode($path);
            $arrDATA['apGroup'] = utf8_encode($appGroup);

            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;

    }

    // ========================================================================================
    //  Ophalen data "menu blocks"
    //
    // In:	Niets
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_SX_MB() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("menu.class"));

        $arrDATA = array();
        $arrJSON = array();

        $sqlStat = "Select * from sx_mb_menu_blocks where mbRecStatus = 'A' order by mbSort";
        $db->Query($sqlStat);

        $i = 0;

        while ($mbRec = $db->Row()) {

            $i++;

            // ----
            // FOTO
            // ----

            $fotoPath = null;

            $fotos = json_decode($mbRec->mbPhotoTop);
            if ($fotos) {
                foreach ($fotos as $foto) {
                    $fotoPath = SX_tools::GetFilePath($foto->name);
                }
            }

            // --------------------------
            // Replace menu-items in body
            // --------------------------

            $menuBody = $mbRec->mbMenuBody;
            $menuBody = SX_menu::UpdMenuTemplate($menuBody);

            // ----------
            // Build JSON
            // ----------

            $arrDATA['mbTitle'] = utf8_encode($mbRec->mbTitle);
            $arrDATA['mbShowTitle'] = $mbRec->mbShowTitle;
            $arrDATA['mbMdiIcon'] = utf8_encode($mbRec->mbMdiIcon);
            $arrDATA['mbMdiIconColor'] = utf8_encode($mbRec->mbMdiIconColor);
            $arrDATA['mbMenuBody'] = utf8_encode(nl2br($menuBody));
            $arrDATA['mbVueComponent'] = utf8_encode($mbRec->mbVueComponent);

            if ($mbRec->mbShowPhotoTop == 1)
                $arrDATA['mbPhotoTop'] = utf8_encode($fotoPath);
            else
                $arrDATA['mbPhotoTop'] = "";

            $arrDATA['mbShowPhotoTop'] = $mbRec->mbShowPhotoTop;

            $arrJSON[] = $arrDATA;

        }

        // -------------
        // Einde functie
        // -------------

        $json = json_encode($arrJSON);

        return $json;

    }


    // ========================================================================================
    //  Ophalen voetbal-team
    //
    // In:	Team ID
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_VB_TEAM($pTeam) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("sessions.class"));

        include_once(SX::GetClassPath("settings.class"));
        include_once(SX::GetClassPath("wedstrijden.class"));

        $userId = SX_sessions::GetSessionUserId();

        // ----------
        // Ploeginfo
        // ---------

        $arrTEAM = SSP_wedstrijden::GetTeamInfoArray($pTeam, true);

        $arrJSON = array();

        $arrJSON[] = $arrTEAM;

        $json = json_encode($arrJSON);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }

    // ========================================================================================
    //  Ophalen voetbal-teams
    //
    // In:	Niets
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_VB_TEAMS() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("sessions.class"));

        include_once(SX::GetClassPath("settings.class"));
        include_once(SX::GetClassPath("wedstrijden.class"));

        $userId = SX_sessions::GetSessionUserId();

        // --------------------
        // Teams actief seizoen
        // --------------------

        $arrTEAMS = array();
        $arrCATEGORIEN = array();

        $cats = array();

        $actiefSeizoen = SSP_settings::GetActiefSeizoen();

        $sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen'and vpJeugdSeniors in ('Jeugd', 'Seniors') order by vpSort desc";

        $db->Query($sqlStat);

        while($vpRec = $db->Row()) {

            $arrTEAM = SSP_wedstrijden::GetTeamInfoArray($vpRec->vpId, false);
            $arrTEAM['showExtraInfo'] = false;

            $cats[] =  $arrTEAM['categorie'];

            $arrTEAMS[] = $arrTEAM;


        }

        // -----------
        // Categorieën
        // -----------

        $cats = array_unique($cats);

        $categorieen = array();

        $categorieen[] = 'Alle';

        foreach ($cats as $cat)
            $categorieen[] = $cat;

        $arrCATEGORIEN['cats'] = $categorieen;

        // ----------
        // Build JSON
        // ----------

        $arrJSON = array();

        $arrJSON[] = $arrTEAMS;
        $arrJSON[] = $arrCATEGORIEN;


        $json = json_encode($arrJSON);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }
    // ========================================================================================
    //  Ophalen Personalia
    //
    // In:	Type - *BESTUUR_VB, ...
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_PERSONALIA($pType = '*BESTUUR_VB') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("sessions.class"));

        include_once(SX::GetClassPath("settings.class"));

        $userId = SX_sessions::GetSessionUserId();

        $arrPERSONEN = array();
        $arrCATS = array();

        // ---------------------------------
        // BESTUUR VOETBAL of SPORTIEVE STAF
        // ---------------------------------

        if ($pType == '*BESTUUR_VB' or $pType == '*SPORTIEVE_STAF'){

            if ($pType == '*BESTUUR_VB')
                $table = "PERSONALIA_BESTUUR_VB";

            if ($pType == '*SPORTIEVE_STAF')
                $table = "PERSONALIA_SPORTIEVE_STAF";


            $sqlStat = "Select * from sx_ta_tables where taTable = '$table' and taRecStatus = 'A'  order by taSort";
            $db->Query($sqlStat);

            while ($taRec = $db->Row()){

                $code = $taRec->taCode;
                $sqlStat = "Select * from ssp_ad where adCode = '$code'";

                $db2->Query($sqlStat);

                if ($adRec = $db2->Row()){

                    $arrPERSOON = self::GetJSON_PERSOON($adRec);
                    $arrPERSOON['functie'] = utf8_encode($taRec->taDescription);

                    if ($pType == '*SPORTIEVE_STAF' and $userId == '*NONE')
                        $arrPERSOON['tel'] = 'XXX';


                    $arrPERSONEN[] = $arrPERSOON;

                }

            }

        }

        // --------
        // TRAINERS
        // --------


        if ($pType == '*TRAINERS') {

            $sqlStat    = "Select * from ssp_ad "
                        . "Inner Join ssp_vp On vpTrainer = adCode or vpTrainer2 = adCode or vpTrainer3 = adCode or vpTrainer4 = adCode or vpTrainer5 = adCode "
                        . "Inner Join ssp_vs On vsCode = vpSeizoen and vsHuidigSeizoen = 1 "
                        . "Inner Join sx_ta_tables On taTable = 'VOETBAL_CAT' and taCode = vpVoetbalCat "
                        . "Where adRecStatus  = 'A' "
                        . "Order by vpSort Desc, adVoornaamNaam";

            $db->Query($sqlStat);

            while ($adRec = $db->Row()){

                $arrPERSOON = self::GetJSON_PERSOON($adRec);
                $arrPERSOON['ploeg'] = utf8_encode($adRec->vpNaam);


                if ($adRec->taName != 'Jeugd')
                    $arrPERSOON['cat'] = utf8_encode($adRec->taName);
                else
                    $arrPERSOON['cat'] = utf8_encode($adRec->vpNaam);

                $arrPERSOON['catPloeg'] = $arrPERSOON['cat'] . " - " . $arrPERSOON['ploeg'];

                if ($userId == '*NONE')
                    $arrPERSOON['tel'] = 'XXX';

                $arrPERSONEN[] = $arrPERSOON;

                $cats[] =  $arrPERSOON['cat'];

            }

        }

        // --------------
        // AFGEVAARDIGDEN
        // --------------

        if ($pType == '*AFGEVAARDIGDEN') {

            $sqlStat    = "Select * from ssp_ad "
                . "Inner Join ssp_vp On vpDelege = adCode or vpDelege2 = adCode or vpDelege3 = adCode "
                . "Inner Join ssp_vs On vsCode = vpSeizoen and vsHuidigSeizoen = 1 "
                . "Inner Join sx_ta_tables On taTable = 'VOETBAL_CAT' and taCode = vpVoetbalCat "
                . "Where adRecStatus  = 'A' "
                . "Order by vpSort Desc, adVoornaamNaam";

            $db->Query($sqlStat);

            while ($adRec = $db->Row()){

                $arrPERSOON = self::GetJSON_PERSOON($adRec);
                $arrPERSOON['ploeg'] = utf8_encode($adRec->vpNaam);


                if ($adRec->taName != 'Jeugd')
                    $arrPERSOON['cat'] = utf8_encode($adRec->taName);
                else
                    $arrPERSOON['cat'] = utf8_encode($adRec->vpNaam);

                $arrPERSOON['catPloeg'] = $arrPERSOON['cat'] . " - " . $arrPERSOON['ploeg'];

                if ($userId == '*NONE')
                    $arrPERSOON['tel'] = 'XXX';

                $arrPERSONEN[] = $arrPERSOON;

                $cats[] =  $arrPERSOON['cat'];

            }

        }





        // ----------
        // CATEGORIËN
        // ----------

        $cats = array_unique($cats);

        $categorieen = array();
        $categorie = array();

        $categorie['naam']  = 'Alle';
        $categorieen[] = $categorie;

        foreach ($cats as $cat) {

            $categorie['naam'] = utf8_encode($cat);

            $categorieen[] = $categorie;

        }

        $arrCATS['cats'] = $categorieen;

        // ----------
        // Build JSON
        // ----------

        $arrJSON = array();
        $arrJSON[] = $arrPERSONEN;
        $arrJSON[] = $categorieen;

        $json = json_encode($arrJSON);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }

    // ========================================================================================
    //  Ophalen array met persoon-info
    //
    // In:	persoon (adRec)
    //      Ttype = *ARRAY, *JSON
    //
    // Return: Array
    //
    // ========================================================================================

    static function GetJSON_PERSOON($pPersoon, $pType = '*ARRAY'){

        $arrPERSOON = array();

        $arrPERSOON['naam'] = utf8_encode($pPersoon->adNaam);
        $arrPERSOON['voornaam'] = utf8_encode($pPersoon->adVoornaam);
        $arrPERSOON['naamVoornaam'] = utf8_encode($pPersoon->adNaamVoornaam);
        $arrPERSOON['voornaamNaam'] = utf8_encode($pPersoon->adVoornaamNaam);
        $arrPERSOON['tel'] = $pPersoon->adTel;

        if ($pPersoon->adMailSchelleSport)
            $arrPERSOON['mail'] = utf8_encode($pPersoon->adMailSchelleSport);
        else
            $arrPERSOON['mail'] = utf8_encode($pPersoon->adMail);

        $fotoPath = null;

        $fotos = json_decode($pPersoon->adFoto);
        if ($fotos) {
            foreach ($fotos as $foto) {
                $fotoPath = SX_tools::GetFilePath($foto->name);
            }
        }

        if ($fotoPath)
            $arrPERSOON['foto'] = $fotoPath;
        else
            $arrPERSOON['foto'] = SX::GetSiteImgPath('nopicture.jpg');

        // -------------
        // Einde functie
        // -------------

        if ($pType == '*ARRAY')
            return $arrPERSOON;
        else
            return json_encode($arrPERSOON);


    }

    // ========================================================================================
    //  Ophalen data voor breadcrumb
    //
    // In:	App
    //      Id
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_BREADCRUMB($pApp, $pId = 0){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $basePHP = $_SESSION['SX_BASEPHP'];

        $arrITEMS = array();

        // -------------------------------
        // HOMEPAGE - always as first item
        // -------------------------------

        $arrITEMh = array();
        $arrITEMh['text'] = 'Home';
        $arrITEMh['disabled'] = false;
        $arrITEMh['href'] = $basePHP;

        // ------------
        // CURRENT page
        // ------------

        $inBetween = "";

        $arrITEMc = array();

        $itemHref = "$basePHP?app=$pApp";
        if ($pId)
            $itemHref = "$itemHref&id=$pId";

        $sqlStat = "Select * from sx_vc_vue_components where vcCode = '$pApp'";
        $db->Query($sqlStat);

        If (($vcRec = $db->Row()) && ($vcRec->vcBreadcrumbPHP > ' ')){
            eval($vcRec->vcBreadcrumbPHP);
        }

        if (! $itemText)
            $itemText = "xxx";

        $arrITEMc['text'] = $itemText;
        $arrITEMc['disabled'] = true;
        $arrITEMc['href'] = $itemHref;

        // ----------------
        // IUn between page
        // ----------------

        $arrITEMi = array();

        If ($inBetweenApp) {

            $sqlStat = "Select * from sx_vc_vue_components where vcCode = '$inBetweenApp'";
            $db->Query($sqlStat);

            If (($vcRec = $db->Row()) && ($vcRec->vcBreadcrumbPHP > ' ')){

                eval($vcRec->vcBreadcrumbPHP);

                $arrITEMi['text'] = $itemText;
                $arrITEMi['disabled'] = false;
                $arrITEMi['href'] = "$basePHP?app=$inBetweenApp";

            }


        }

        // ----------
        // Built JSON
        // ----------

        $arrITEMS[] = $arrITEMh;

        if ($arrITEMi['text'])
            $arrITEMS[] = $arrITEMi;

        $arrITEMS[] = $arrITEMc;

        $json = json_encode($arrITEMS);

        // -------------
        // Einde functie
        // -------------

        return $json;

    }


    // ========================================================================================
    //  Ophalen data voor documenten
    //
    // In:	App
    //      Id
    //
    // Return: JSON
    //
    // ========================================================================================

    static function GetJSON_DOCS()
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetSxClassPath("tools.class"));


        $id = 0;

        // -------
        // FOLDERS
        // -------

        $sqlStat = "Select * from doc_fd_folders order by fdSort";

        $db->Query($sqlStat);

        $arrFOLDERS = array();

        while ($fdRec = $db->Row()) {

            $arrFOLDER = array();

            $id++;
            $arrFOLDER['id'] = $id;

            $arrFOLDER['name'] = utf8_encode($fdRec->fdName);
            $arrFOLDER['nameButton'] = utf8_encode($fdRec->fdName);
            $arrFOLDER['type'] = utf8_encode('*FOLDER');


            // ----------
            // Subfolders
            // ----------

            $arrSUBFOLDERS = array();

            $folder = $fdRec->fdId;

            $sqlStat = "Select * from doc_sf_subfolders where sfFolder = $folder order by sfSort ";
            $db2->Query($sqlStat);

            while ($sfRec = $db2->Row()) {

                $arrSUBFOLDER = array();

                $id++;
                $arrSUBFOLDER['id'] = $id;

                $arrSUBFOLDER['name'] = utf8_encode($sfRec->sfName);
                $arrSUBFOLDER['nameButton'] = utf8_encode($sfRec->sfName);
                $arrSUBFOLDER['type'] = utf8_encode('*SUBFOLDER');

                // ----------
                // Documenten
                // ----------

                $arrDOCS = array();

                $subfolder = $sfRec->sfId;

                $sqlStat = "Select * from doc_do_documents where doSubfolder = $subfolder order by doName";
                $db3->Query($sqlStat);

                while ($doRec = $db3->Row()) {

                    $arrDOC = array();

                    $id++;
                    $arrDOC['id'] = utf8_encode($id);

                    $name = $doRec->doName;
                    $arrDOC['name'] = utf8_encode($name);

                    $arrDOC['type'] = utf8_encode('*DOC');
                    $arrDOC['url'] = utf8_encode($doRec->doURL);

                    $arrDOC['icon'] = utf8_encode('mdi-file-document-outline');


                    $arrDOCS[] = $arrDOC;

                }

                $arrSUBFOLDER['children'] = $arrDOCS;

                $arrSUBFOLDERS[] = $arrSUBFOLDER;


            }

            $arrFOLDER['children'] = $arrSUBFOLDERS;

            $arrFOLDERS[] = $arrFOLDER;

        }

        // ----------
        // Built JSON
        // ----------


        $json = json_encode($arrFOLDERS);

        // -------------
        // Einde functie
        // -------------


        return $json;

    }


    // -----------
    // Einde CLASS
    // -----------

}

?>