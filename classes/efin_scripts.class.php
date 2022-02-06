<?php


class SSP_efin_scripts
{ // define the class

    // ========================================================================================
    //  Aanmaken MAPPING's Lidgeld Voetbal
    //
    // In:	Categorie: *JEUGD, *SENIORS, *GTEAM
    //      Ventilatie-rekening
    //
    // ========================================================================================

    static function CrtMappingsLidgeldVoetbal($pCategorie, $pVentilatieRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("efin.class"));

        $sqlStat = "Select * from ssp_ad where adFunctieVB like '%speler%' and adRecStatus = 'A' and adGmLidgeldVB > ' ' and adGmLidgeldVB not in (select amOGM from efin_am_analytische_mapping )";

        $curDateTime = date('Y-m-d H:i:s');

        $db->Query($sqlStat);

        while ($adRec = $db->Row()){

            if ($adRec->adVoetbalCatWebshop)
                $catVB = $adRec->adVoetbalCatWebshop;
            else
                $catVB = $adRec->adVoetbalCat;

            if (($pCategorie == '*GTEAM') and (substr($catVB,0,1)!= 'G') and (substr($catVB,0,1)!= 'A'))
                continue;
            if (($pCategorie == '*JEUGD') and (substr($catVB,0,1)!= 'U'))
                continue;
            if (($pCategorie == '*SENIORS') and (substr($catVB,0,1)!= 'S'))
                continue;
            if (($pCategorie == '*VETERANEN') and (substr($catVB,0,1)!= 'V'))
                continue;

            $values = array();

            $GM = $adRec->adGmLidgeldVB;
            $GMn = SSP_efin::CvtGmToNum($GM);

            // $values["amNaam"] = MySQL::SQLValue('Lidgeld Voetbal');

            $values["amOGM"] = MySQL::SQLValue($GM);
            $values["amOGMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

            $values["amVentilatieRekening"] = MySQL::SQLValue($pVentilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["amPersoon"] = MySQL::SQLValue($adRec->adCode);
            $values["amReferentie"] = MySQL::SQLValue($adRec->adCode);

            $values["amDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserCreatie"] = MySQL::SQLValue('*SCRIPT');
            $values["amDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserUpdate"] = MySQL::SQLValue('*SCRIPT');
            $values["amRecStatus"] = MySQL::SQLValue('A');

            $id = $db2->InsertRow("efin_am_analytische_mapping", $values);

        }


        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Aanmaken MAPPING's Webshop
    //
    // In:	Categorie: *JEUGD, *SENIORS, *GTEAM, *ALL
    //      Ventilatie-rekening
    //
    // ========================================================================================

    static function CrtMappingsWebshop($pCategorie, $pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.class"));

        $sqlStat = "Select * from eba_oh_order_headers where ohVolledigAfgewerkt <> 1 and ohTotaalPrijs > 0 and ohGm > ' ' and ohGm not in (select amOGM from efin_am_analytische_mapping )";

        $curDateTime = date('Y-m-d H:i:s');

        $db->Query($sqlStat);

        while ($ohRec = $db->Row()) {

            $persoon = $ohRec->ohKlant;

            if ($pCategorie != '*ALL'){

                $adRec = SSP_db::Get_SSP_adRec($persoon);

                if ($adRec->adVoetbalCatWebshop)
                    $catVB = $adRec->adVoetbalCatWebshop;
                else
                    $catVB = $adRec->adVoetbalCat;

                if (($pCategorie == '*GTEAM') and (substr($catVB,0,1)!= 'G'))
                    continue;
                if (($pCategorie == '*JEUGD') and (substr($catVB,0,1)!= 'U'))
                    continue;
                if (($pCategorie == '*SENIORS') and (substr($catVB,0,1)!= 'S'))
                    continue;

            }

            $values = array();

            $GM = $ohRec->ohGm;
            $GMn = SSP_efin::CvtGmToNum($GM);

            $referentie = strval ($ohRec->ohOrdernummer);

            // $values["amNaam"] = MySQL::SQLValue('Webshop');

            $values["amOGM"] = MySQL::SQLValue($GM);
            $values["amOGMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

            $values["amVentilatieRekening"] = MySQL::SQLValue($pVentilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["amPersoon"] = MySQL::SQLValue($persoon);
            $values["amReferentie"] = MySQL::SQLValue($referentie);

            $values["amDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserCreatie"] = MySQL::SQLValue('*SCRIPT');
            $values["amDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserUpdate"] = MySQL::SQLValue('*SCRIPT');
            $values["amRecStatus"] = MySQL::SQLValue('A');

            $id = $db2->InsertRow("efin_am_analytische_mapping", $values);

        }

        // -------------
        // Einde functie
        // -------------



    }

    // ========================================================================================
    //  Doorboeken lidgeld voetbal
    //
    // In:	RekeningDetail
    //      Ventilatie-rekening
    //
    //  Return: Boodschap (*OK indien volledig goed doorgeboekt)

    //
    // ========================================================================================

    static function BookLidgeldVB($pRekeningDetail, $pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("ela.class"));
        include_once(SX::GetClassPath("eba.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return "Onverwachte fout: Geen rekening-detail";

        $huidigSeizoen = SSP_eba::GetHuidigSeizoen();

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twVentilatieRekening = $pVentilatieRekening and twDoorgeboekt <> 1";

        $db->Query($sqlStat);

        while ($twRec = $db->Row()){

            $code = $twRec->twReferentie;

            $adRec = SSP_db::Get_SSP_adRec($code);
            $persoon = $adRec->adCode;

            $bedrag = $twRec->twBedrag;

            if ($adRec->adLidgeldVoldaanVB == 'JA' and $bedrag > 0)
                return "Lidgeld reeds volledig betaald";

            else {

                $gm = null;
                $positie = null;
                $specifiekeAfspraak = null;
                $boete = 0;
                $tariefCode = '';

                $teBetalen = SSP_ela::GetTebetalenLidgeldVoetbal($code, $reedsBetaald, $gm, $positie, $specifiekeAfspraak, $boete, $tariefCode);

                $betaalDatum = $rdRec->rdDatum;

                if ($bedrag > $teBetalen)
                    return "Bedrag te hoog";

                if ($teBetalen >= $bedrag){

                    $curDateTime = date('Y-m-d H:i:s');

                    $values = array();


                    $values["lbPersoon"] = MySQL::SQLValue($persoon, MySQL::SQLVALUE_TEXT);
                    $values["lbLidgeldVoor"] = MySQL::SQLValue('*VOETBAL', MySQL::SQLVALUE_TEXT);
                    $values["lbSeizoen"] = MySQL::SQLValue($huidigSeizoen, MySQL::SQLVALUE_TEXT);

                    $values["lbBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
                    $values["lbBetaalDatum"] = MySQL::SQLValue($betaalDatum, MySQL::SQLVALUE_DATE);

                    $values["lbBetaalWijze"] = MySQL::SQLValue('*OVERSCHRIJVING', MySQL::SQLVALUE_TEXT);
                    $values["lbEfinRD"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

                    $values["lbTariefCode"] = MySQL::SQLValue($tariefCode, MySQL::SQLVALUE_TEXT);
                    $values["lbBoete"] = MySQL::SQLValue($boete, MySQL::SQLVALUE_NUMBER);
                    $values["lbTeBetalen"] = MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);

                    $values["lbUserCreatie"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
                    $values["lbDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                    $values["lbUserUpdate"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
                    $values["lbDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                    $id = $db2->InsertRow("ela_lb_lidgeld_betalingen", $values);

                    if (! $id)
                        return "Onverwachte fout ela_lb_lidgeld_betalingen";

                    SSP_ela::ValBetalingLidgeldVoetbal($persoon, $huidigSeizoen);

                    // ---------------------------------------------
                    // Update status webshop kledijpakket in lidgeld
                    // ---------------------------------------------

                    SSP_eba::SetOrdersStat($persoon);

                }


            }


        }


        // -------------
        // Einde functie
        // -------------

        return '*OK';


    }

    // ========================================================================================
    //  Doorboeken webshop verkoop (bijbestellingen)
    //
    // In:	RekeningDetail
    //      Ventilatie-rekening
    //
    //  Return: Boodschap (*OK indien volledig goed doorgeboekt)

    //
    // ========================================================================================

    static function BookWebshop($pRekeningDetail, $pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("ela.class"));
        include_once(SX::GetClassPath("eba.class"));

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return "Onverwachte fout: Geen rekening-detail";


        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twVentilatieRekening = $pVentilatieRekening and twDoorgeboekt <> 1";

        $db->Query($sqlStat);


        while ($twRec = $db->Row()){

            $ordernummer = intval($twRec->twReferentie);

            if (! $ordernummer)
                return "Ongeldige referentie";

            $ohRec = SSP_db::Get_EBA_ohRec($ordernummer);

            if (! $ohRec)
                return "Ordernummer bestaat niet";

            if ($ohRec->ohBetaalTotaal == '*OK')
                return "Order was reeds betaald";

            $bedrag = $twRec->twBedrag;
            $teBetalen = $ohRec->ohTotaalPrijs - $ohRec->ohBetaalTotaal;

            if (! $teBetalen)
                return "Order reeds betaald of gratis...";

            if ($bedrag > $teBetalen)
                return "Bedrag te hoog";

            $ohBetaalBedrag1 = $ohRec->ohBetaalBedrag1 + 0;
            $ohBetaalDatum1 = $ohRec->ohBetaalDatum1;
            $ohBetaalwijze1 = $ohRec->ohBetaalwijze1;

            $ohBetaalBedrag2 = $ohRec->ohBetaalBedrag2 + 0;
            $ohBetaalDatum2 = $ohRec->ohBetaalDatum2;
            $ohBetaalwijze2 = $ohRec->ohBetaalwijze2;

            $geboekt = false;

            if (! $ohBetaalBedrag1 or $ohBetaalBedrag1 <= 0) {

                $ohBetaalBedrag1 = $bedrag;
                $ohBetaalDatum1 = $rdRec->rdDatum;
                $ohBetaalwijze1 = 'OVERSCHR';
                $geboekt = true;

            }
            elseif (! $ohBetaalBedrag2 or $ohBetaalBedrag2 <= 0){

                $ohBetaalBedrag2 = $bedrag;
                $ohBetaalDatum2 = $rdRec->rdDatum;
                $ohBetaalwijze2 = 'OVERSCHR';
                $geboekt = true;

            }

            if (! $geboekt)
                return "Kon niet geboekt worden $ohBetaalBedrag1 / $ohBetaalBedrag2";
            else {

                $ohBetaalTotaal = $ohBetaalBedrag1 + $ohBetaalBedrag2;

                if ($ohBetaalTotaal >= $teBetalen)
                    $ohBetaalStatus = '*OK';
                else
                    $ohBetaalStatus = '*NOK';

                $values = array();
                $where = array();


                if ($ohBetaalBedrag1) {
                    $values["ohBetaalBedrag1"] = MySQL::SQLValue($ohBetaalBedrag1, MySQL::SQLVALUE_NUMBER);
                    $values["ohBetaalwijze1"] = MySQL::SQLValue($ohBetaalwijze1);
                    $values["ohBetaalDatum1"] = MySQL::SQLValue($ohBetaalDatum1, MySQL::SQLVALUE_DATETIME);
                }

                if ($ohBetaalBedrag2) {
                    $values["ohBetaalBedrag2"] = MySQL::SQLValue($ohBetaalBedrag2, MySQL::SQLVALUE_NUMBER);
                    $values["ohBetaalwijze2"] = MySQL::SQLValue($ohBetaalwijze2);
                    $values["ohBetaalDatum2"] = MySQL::SQLValue($ohBetaalDatum2, MySQL::SQLVALUE_DATETIME);
                }

                $values["ohBetaalTotaal"] = MySQL::SQLValue($ohBetaalTotaal, MySQL::SQLVALUE_NUMBER);


                $values["ohBetaalStatus"] =MySQL::SQLValue($ohBetaalStatus);


                $where["ohOrdernummer"] =  MySQL::SQLValue($ordernummer, MySQL::SQLVALUE_NUMBER);

                $db->UpdateRows("eba_oh_order_headers", $values, $where);

                SSP_eba::ChkOrder($ordernummer);

            }

        }


        // -------------
        // Einde functie
        // -------------

        return '*OK';


    }


    // ========================================================================================
    //  Aanmaken MAPPING's event
    //
    // In:	Ventilatie Rekening
    //      event code
    //
    // ========================================================================================

    static function CrtMappingsEvent($pVentilatieRekening, $pEvent) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from events_eh_event_headers where ehCode ='$pEvent'";
        $db->Query($sqlStat);

        if (! $ehRec = $db->Row())
            return;

        $file = $ehRec->ehFile;

        $sqlStat = "Select * from $file where GM not in (select amOGM from efin_am_analytische_mapping)";
        $db->Query($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        while ($eventRec = $db->Row()){

            $values = array();

            $GM = $eventRec->GM;
            $GMn = $eventRec->GMn;

            $values["amNaam"] = MySQL::SQLValue($ehRec->ehNaam);

            $values["amOGM"] = MySQL::SQLValue($GM);
            $values["amOGMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

            $values["amVentilatieRekening"] = MySQL::SQLValue($pVentilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["amReferentie"] =  MySQL::SQLValue($eventRec->naam);

            $values["amDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserCreatie"] = MySQL::SQLValue('*SCRIPT');
            $values["amDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserUpdate"] = MySQL::SQLValue('*SCRIPT');
            $values["amRecStatus"] = MySQL::SQLValue('A');

            $id = $db2->InsertRow("efin_am_analytische_mapping", $values);

        }


        // -------------
        // Einde functie
        // -------------

    }


    // ========================================================================================
    //  Doorboeken event betaling
    //
    // In:	RekeningDetail
    //      Ventilatie-rekening
    //      Eventcode
    //
    //  Return: Boodschap (*OK indien volledig goed doorgeboekt)
    //
    // ========================================================================================

    static function BookEventBetaling($pRekeningDetail, $pVentilatieRekening, $pEvent){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.class"));
        include_once(SX::GetClassPath("events.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return "Onverwachte fout: Geen rekening-detail";

        $sqlStat = "Select * from events_eh_event_headers where ehCode ='$pEvent' and ehRecStatus = 'A'";
        $db->Query($sqlStat);

        if (! $ehRec = $db->Row())
            return "Onverwachte fout: Event bestaat niet of is in historiek";

        $file = $ehRec->ehFile;

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twVentilatieRekening = $pVentilatieRekening and twDoorgeboekt <> 1 limit 1";

        $db->Query($sqlStat);

        if (! $twRec = $db->Row())
            return "Onverwachte fout: geen openstaande toewijzing";

        $bedrag = $twRec->twBedrag;

        $GM = trim($rdRec->rdMededeling);
        $GM = substr($GM, 0, 12);

        $validGM = SSP_efin::CheckGM($GM);

        if (! $validGM)
            return "Onverwachte fout: Geen geldige GM";


        if ($validGM){

            $sqlStat = "Select * from $file where GMn = $GM";
            $db->Query($sqlStat);

            if (! $eventRec = $db->Row())
                return "Onverwachte fout: Event inschrijving niet gevonden op basis GM";

            $eventId = $eventRec->id;
            $editie = $eventRec->editie;

            // -------------------
            // Registreer betaling
            // -------------------

            $curDateTime = date('Y-m-d H:i:s');

            $values = array();

            $values["ebEventCode"] = MySQL::SQLValue($pEvent, MySQL::SQLVALUE_TEXT);
            $values["ebEvent"] = MySQL::SQLValue($pEvent, MySQL::SQLVALUE_TEXT);

            $values["ebEditie"] = MySQL::SQLValue($editie, MySQL::SQLVALUE_TEXT);

            $values["ebEventInschrijving"] = MySQL::SQLValue($eventId, MySQL::SQLVALUE_NUMBER);

            $values["ebBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
            $values["ebBetaalDatum"] = MySQL::SQLValue($rdRec->rdDatum, MySQL::SQLVALUE_DATE);

            $values["ebBetaalWijze"] = MySQL::SQLValue('*OVERSCHRIJVING', MySQL::SQLVALUE_TEXT);

            $values["ebEfinRD"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

            $values["ebUserCreatie"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
            $values["ebDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["ebUserUpdate"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
            $values["ebDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $betaalId = $db->InsertRow("events_eb_event_betalingen", $values);

            SSP_events::RegBetaling($betaalId, '*ADD');

        }

        // -------------
        // Einde functie
        // -------------

        return '*OK';


    }


    // ========================================================================================
    //  Aanmaken MAPPING's Stages
    //
    // In:	Ventilatie-rekening
    //
    // ========================================================================================

    static function CrtMappingsStages($pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from esa_si_stage_inschrijvingen where siStage in (select shId from esa_sh_stage_headers where shVentilatie = $pVentilatieRekening) and siGM not in (select amOGM from efin_am_analytische_mapping ) ";

        $curDateTime = date('Y-m-d H:i:s');

        $db->Query($sqlStat);

        while ($siRec = $db->Row()) {

            $stage = $siRec->siStage;
            $shRec = SSP_db::Get_ESA_shRec($stage);
            if (! $shRec)
                continue;

            $slRec = SSP_db::Get_ESA_slRec($siRec->siLichting);

            $values = array();

            $GM = $siRec->siGM;
            $GMn = $siRec->siGMn;

            $naam = $siRec->siNaam . ' ' . $siRec->siVoornaam . ' (' . $shRec->shNaamKort . ')';
            $referentie = $siRec->siNaam . ' ' . $siRec->siVoornaam . ' (' . $shRec->shNaamKort . ' - ' . $slRec->slCode . ')';

            $values["amNaam"] = MySQL::SQLValue($naam);

            $values["amOGM"] = MySQL::SQLValue($GM);
            $values["amOGMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

            $values["amVentilatieRekening"] = MySQL::SQLValue($pVentilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["amReferentie"] =  MySQL::SQLValue($referentie);

            $values["amDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserCreatie"] = MySQL::SQLValue('*SCRIPT');
            $values["amDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserUpdate"] = MySQL::SQLValue('*SCRIPT');
            $values["amRecStatus"] = MySQL::SQLValue('A');

            $id = $db2->InsertRow("efin_am_analytische_mapping", $values);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Doorboeken Stage betaling
    //
    // In:	RekeningDetail
    //      Ventilatie-rekening
    //
    //  Return: Boodschap (*OK indien volledig goed doorgeboekt)
    //
    // ========================================================================================

    static function BookStageBetaling($pRekeningDetail, $pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.class"));
        include_once(SX::GetClassPath("esa.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return "Onverwachte fout: Geen rekening-detail";


        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twVentilatieRekening = $pVentilatieRekening and twDoorgeboekt <> 1 limit 1";

        $db->Query($sqlStat);

        if (! $twRec = $db->Row())
            return "Onverwachte fout: geen openstaande toewijzing";

        $bedrag = $twRec->twBedrag;

        $GM = trim($rdRec->rdMededeling);
        $GM = substr($GM, 0, 12);

        $validGM = SSP_efin::CheckGM($GM);

        if (! $validGM)
            return "Onverwachte fout: Geen geldige GM";


        if ($validGM){

            $sqlStat = "Select * from esa_si_stage_inschrijvingen where siGMn = $GM";
            $db->Query($sqlStat);

            if (! $siRec = $db->Row())
                return "Onverwachte fout: Stage inschrijving niet gevonden op basis GM";

            $stage = $siRec->siStage;

            if ($siRec->siBetaald >= $siRec->siTeBetalen)
                return "Probleem: Stage reeds volledig betaald";


            $id = $siRec->siId;

            // -------------------
            // Registreer betaling
            // -------------------

            $curDateTime = date('Y-m-d H:i:s');

            $values = array();

            $values["sbStageInschrijving"] = MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);

            $values["sbBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
            $values["sbBetaalDatum"] = MySQL::SQLValue($rdRec->rdDatum, MySQL::SQLVALUE_DATE);

            $values["sbBetaalWijze"] = MySQL::SQLValue('*OVERSCHRIJVING', MySQL::SQLVALUE_TEXT);

            $values["sbEfinRD"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

            $values["sbUserCreatie"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
            $values["sbDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["sbUserUpdate"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
            $values["sbDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $betaalId = $db->InsertRow("esa_sb_stage_betalingen", $values);

            SSP_esa::RegBetaling($betaalId, '*ADD');

        }

        // -------------
        // Einde functie
        // -------------

        return '*OK';


    }

    // ========================================================================================
    //  Aanmaken MAPPING's Tennis Lidgelden
    //
    // In:	Ventilatie-rekening
    //
    // ========================================================================================

    static function CrtMappingsTennisLidgelden($pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaGM not in (select amOGM from efin_am_analytische_mapping ) ";

        error_log($sqlStat);

        $curDateTime = date('Y-m-d H:i:s');

        $db->Query($sqlStat);

        while ($aaRec = $db->Row()) {

            $values = array();

            $GM = $aaRec->aaGM;
            $GMn = $aaRec->aaGMn;

            $naam = $aaRec->aaNaam . ' ' . $aaRec->aaVoornaam . ' (' . $aaRec->aaSeizoen . ')';
            $referentie = $aaRec->aaNaam . ' ' . $aaRec->aaVoornaam . ' (' . $aaRec->aaSeizoen . ')';

            $values["amNaam"] = MySQL::SQLValue($naam);

            $values["amOGM"] = MySQL::SQLValue($GM);
            $values["amOGMn"] = MySQL::SQLValue($GMn, MySQL::SQLVALUE_NUMBER);

            $values["amVentilatieRekening"] = MySQL::SQLValue($pVentilatieRekening, MySQL::SQLVALUE_NUMBER);
            $values["amReferentie"] =  MySQL::SQLValue($referentie);

            $values["amDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserCreatie"] = MySQL::SQLValue('*SCRIPT');
            $values["amDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["amUserUpdate"] = MySQL::SQLValue('*SCRIPT');
            $values["amRecStatus"] = MySQL::SQLValue('A');

            $id = $db2->InsertRow("efin_am_analytische_mapping", $values);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Doorboeken Tennis Lidgeld betaling
    //
    // In:	RekeningDetail
    //      Ventilatie-rekening
    //
    //  Return: Boodschap (*OK indien volledig goed doorgeboekt)
    //
    // ========================================================================================

    static function BookTennisLidgeldBetaling($pRekeningDetail, $pVentilatieRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.class"));
        include_once(SX::GetClassPath("tennis.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (! $rdRec)
            return "Onverwachte fout: Geen rekening-detail";


        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twRekeningDetail = $pRekeningDetail and twVentilatieRekening = $pVentilatieRekening and twDoorgeboekt <> 1 limit 1";

        $db->Query($sqlStat);

        if (! $twRec = $db->Row())
            return "Onverwachte fout: geen openstaande toewijzing";

        $bedrag = $twRec->twBedrag;

        $GM = trim($rdRec->rdMededeling);
        $GM = substr($GM, 0, 12);

        $validGM = SSP_efin::CheckGM($GM);

        if (! $validGM)
            return "Onverwachte fout: Geen geldige GM";


        if ($validGM){

            $sqlStat = "Select * from tennis_aansluiting_aanvragen where aaGMn = $GM";
            $db->Query($sqlStat);

            if (! $aaRec = $db->Row())
                return "Onverwachte fout: Tennis inschrijving niet gevonden op basis GM";

            if ($aaRec->aaBetaald >= $aaRec->aaTebetalen)
                return "Probleem: Lidgeld reeds volledig betaald";

            // -------------------
            // Registreer betaling
            // -------------------

            $curDateTime = date('Y-m-d H:i:s');

            $values = array();

            $values["lbAanvraag"] = MySQL::SQLValue($aaRec->aaId, MySQL::SQLVALUE_NUMBER);

            $values["lbBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
            $values["lbBetaalDatum"] = MySQL::SQLValue($rdRec->rdDatum, MySQL::SQLVALUE_DATE);

            $values["lbBetaalWijze"] = MySQL::SQLValue('*OVERSCHRIJVING', MySQL::SQLVALUE_TEXT);

            $values["lbEfinRD"] = MySQL::SQLValue($pRekeningDetail, MySQL::SQLVALUE_NUMBER);

            $values["lbUserCreatie"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
            $values["lbDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["lbUserUpdate"] = MySQL::SQLValue('*EFIN', MySQL::SQLVALUE_TEXT);
            $values["lbDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $betaalId = $db->InsertRow("tennis_lb_lidgeld_betalingen", $values);

            SSP_tennis::CalcRegBetaald($aaRec->aaId);


        }

        // -------------
        // Einde functie
        // -------------

        return '*OK';


    }






    // -----------
    // Einde class
    // -----------


}

?>