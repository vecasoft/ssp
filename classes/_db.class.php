<?php 

class SSP_db { // define the class

    // ========================================================================================
    // Check if MYSQL table file exist
    //
    // In:	Table
    //
    // Return: Exist? (
    // ========================================================================================

    static function ChkMysqlTABLE($pTable) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $return = $db->SelectTable($pTable);

        if ($return == '1')
            return true;
        else
            return false;

    }

    // ========================================================================================
    // Get DOC Folder
    //
    // In:	Folder
    //
    // Return: fdRec
    // ========================================================================================

    static function Get_DOC_fdRec($pFolder) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from doc_fd_folders where fdId = $pFolder";
        $db->Query($sqlStat);

        if ($fdRec = $db->Row())
            return $fdRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA Artikel
    //
    // In:	artikel
    //
    // Return: arRec
    // ========================================================================================

    static function Get_EBA_arRec($pArtikel) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_ar_artikels where arId = $pArtikel";
        $db->Query($sqlStat);

        if ($arRec = $db->Row())
            return $arRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA Artikel-stock
    //
    // In:	Artikel
    //      Maat
    //
    // Return: asRec
    // ========================================================================================

    static function Get_EBA_asRec($pArtikel, $pMaat) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_as_artikel_stock where asArtikel = $pArtikel and asMaat = '$pMaat'";
        $db->Query($sqlStat);

        if ($asRec = $db->Row())
            return $asRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA Bestel-header
    //
    // In:	Bestelbon nummer
    //
    // Return: ohRec
    // ========================================================================================

    static function Get_EBA_bhRec($pBestelbon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_bh_bestel_headers where bhId = $pBestelbon";
        $db->Query($sqlStat);

        if ($bhRec = $db->Row())
            return $bhRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA Leverancier
    //
    // In:	Leverancier-nr
    //
    // Return: leRec
    // ========================================================================================

    static function Get_EBA_leRec($pLeverancier) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_le_leveranciers where leId = $pLeverancier";
        $db->Query($sqlStat);

        if ($leRec = $db->Row())
            return $leRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA Order-header
    //
    // In:	Ordernummer
    //
    // Return: ohRec
    // ========================================================================================

    static function Get_EBA_ohRec($pOrdernummer) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrdernummer";
        $db->Query($sqlStat);

        if ($ohRec = $db->Row())
            return $ohRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA Order-detail
    //
    // In:	Orderlijn
    //
    // Return: ohRec
    // ========================================================================================

    static function Get_EBA_odRec($pOrderlijn) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_od_order_detail where odId = $pOrderlijn";
        $db->Query($sqlStat);

        if ($odRec = $db->Row())
            return $odRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EBA pakket-record
    //
    // In:	Pakket
    //
    // Return: pkRec
    // ========================================================================================

    static function Get_EBA_pkRec($pPakket) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_pk_pakketten where pkId = $pPakket";
        $db->Query($sqlStat);

        if ($pkRec = $db->Row())
            return $pkRec;
        else
            return null;

    }
    // ========================================================================================
    // Get EBA rubriek-record
    //
    // In:	Rubriek
    //
    // Return: ruRec
    // ========================================================================================

    static function Get_EBA_ruRec($pRubriek) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eba_ru_rubrieken where ruId = $pRubriek";
        $db->Query($sqlStat);

        if ($ruRec = $db->Row())
            return $ruRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Analytische Rekening
    //
    // In:	Analytische Rekening
    //
    // Return: bdRec
    // ========================================================================================

    static function Get_EFIN_arRec($pAnalytischeRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arId = $pAnalytischeRekening";
        $db->Query($sqlStat);

        if ($arRec = $db->Row())
            return $arRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Betaalvoorstel-detail
    //
    // In:	Betaalvoorstel-detail
    //
    // Return: bdRec
    // ========================================================================================

    static function Get_EFIN_bdRec($pBetaalvoorstelDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_bd_betaalvoorstel_detail where bdId = '$pBetaalvoorstelDetail'";
        $db->Query($sqlStat);

        if ($bdRec = $db->Row())
            return $bdRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Betaalvoorstel-header
    //
    // In:	Betaalvoorstel
    //
    // Return: bhRec
    // ========================================================================================

    static function Get_EFIN_bhRec($pBetaalvoorstel) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_bh_betaalvoorstel_header where bhId = '$pBetaalvoorstel'";
        $db->Query($sqlStat);

        if ($bhRec = $db->Row())
            return $bhRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Gestructureerde mededeling-record
    //
    // In:	Upload
    //
    // Return: ruRec
    // ========================================================================================

    static function Get_EFIN_gmRec($pCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_gm_gestructureerde_mededeling where gmCode = '$pCode'";
        $db->Query($sqlStat);

        if ($gmRec = $db->Row())
            return $gmRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Factuurtype Record
    //
    // In:	Factuurtype ID
    //
    // Return: ftRec
    // ========================================================================================

    static function Get_EFIN_ftRec($pFactuuurtype) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ft_factuur_type where ftId = $pFactuuurtype";
        $db->Query($sqlStat);

        if ($ftRec = $db->Row())
            return $ftRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Factuurnummer Record
    //
    // In:	Document type
    //      Jaar
    //
    // Return: fnRec
    // ========================================================================================

    static function Get_EFIN_fnRec($pDocumentType, $pJaar) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_fn_factuur_nummers where fnDocumentType = '$pDocumentType' and ( fnJaar = '$pJaar' or fnJaar = 'DOORLOPEND' ) ";
        $db->Query($sqlStat);

        if ($fnRec = $db->Row())
            return $fnRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Inkomende factuur-record
    //
    // In:	Inkomende factuur
    //
    // Return: ruRec
    // ========================================================================================

    static function Get_EFIN_ifRec($pInkomendeFactuur) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_if_inkomende_facturen where ifId = $pInkomendeFactuur";
        $db->Query($sqlStat);

        if ($ifRec = $db->Row())
            return $ifRec;
        else
            return null;

    }
    // ========================================================================================
    // Get EFIN Stoprting kas naar bank-record
    //
    // In:	Storting ID
    //
    // Return: kbRec
    // ========================================================================================

    static function Get_EFIN_kbRec($pStorting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_kb_kas_naar_bank where kbId = $pStorting";
        $db->Query($sqlStat);

        if ($kbRec = $db->Row())
            return $kbRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN KLANT-record
    //
    // In:	Klant-ID
    //
    // Return: lvRec
    // ========================================================================================

    static function Get_EFIN_klRec($pKlant) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_kl_klanten where klId = $pKlant";
        $db->Query($sqlStat);

        if ($klRec = $db->Row())
            return $klRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Leverancier-record
    //
    // In:	Leverancier
    //
    // Return: lvRec
    // ========================================================================================

    static function Get_EFIN_lvRec($pLeverancier) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_lv_leveranciers where lvId = $pLeverancier";
        $db->Query($sqlStat);

        if ($lvRec = $db->Row())
            return $lvRec;
        else
            return null;

    }
    // ========================================================================================
    // Get EFIN Rapport
    //
    // In:	Rapport ID
    //
    // Return: raRec
    // ========================================================================================

    static function Get_EFIN_raRec($pRapport) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ra_rapporten where raId = $pRapport";
        $db->Query($sqlStat);

        if ($raRec = $db->Row())
            return $raRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Rekening_detail
    //
    // In:	Rekening-detail
    //
    // Return: rdRec
    // ========================================================================================

    static function Get_EFIN_rdRec($pRekeningDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rd_rekening_details where rdId = $pRekeningDetail";
        $db->Query($sqlStat);

        if ($rdRec = $db->Row())
            return $rdRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Rekening_header
    //
    // In:	Rekening
    //
    // Return: rkRec
    // ========================================================================================

    static function Get_EFIN_rkRec($pRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_rk_rekeningen where rkId = $pRekening";
        $db->Query($sqlStat);

        if ($rkRec = $db->Row())
            return $rkRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Rekening_details_upload-record
    //
    // In:	Upload
    //
    // Return: ruRec
    // ========================================================================================

    static function Get_EFIN_ruRec($pUpload) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ru_rekening_details_upload where ruId = $pUpload";
        $db->Query($sqlStat);

        if ($ruRec = $db->Row())
            return $ruRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Sponsor Dossier
    //
    // In:	Sponsor Dossier ID
    //
    // Return: sdRec
    // ========================================================================================

    static function Get_EFIN_sdRec($pSponsorDossier) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_sd_sponsor_dossiers where sdId = $pSponsorDossier";
        $db->Query($sqlStat);

        if ($sdRec = $db->Row())
            return $sdRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Sponsor Facturatie Schema
    //
    // In:	Sponsor Facturatie Schema ID
    //
    // Return: sfRec
    // ========================================================================================

    static function Get_EFIN_sfRec($pSponsorFacturatieSchema) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_sf_sponsor_facturatie_schema where sfId = $pSponsorFacturatieSchema";
        $db->Query($sqlStat);

        if ($sfRec = $db->Row())
            return $sfRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Sponsor Tegenprestatie
    //
    // In:	Sponsor Tegenprestatie ID
    //
    // Return: stRec
    // ========================================================================================

    static function Get_EFIN_stRec($pSponsorTegenprestatie) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_st_sponsor_tegenprestaties where stId = $pSponsorTegenprestatie";
        $db->Query($sqlStat);

        if ($stRec = $db->Row())
            return $stRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Tegenprestatie
    //
    // In:	Tegenprestatie ID
    //
    // Return: tpRec
    // ========================================================================================

    static function Get_EFIN_tpRec($pTegenprestatie) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_tp_tegenprestaties where tpId = $pTegenprestatie";
        $db->Query($sqlStat);

        if ($tpRec = $db->Row())
            return $tpRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Toewijzing
    //
    // In:	Toewijzing
    //
    // Return: twRec
    // ========================================================================================

    static function Get_EFIN_twRec($pToewijzing) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_tw_rekening_detail_toewijzingen where twId = $pToewijzing";
        $db->Query($sqlStat);

        if ($twRec = $db->Row())
            return $twRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Uitgaande Factuur Detail
    //
    // In:	Uitgaande Factuur Detail ID
    //
    // Return: udRec
    // ========================================================================================

    static function Get_EFIN_udRec($pUitgaandeFactuurDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_ud_uitgaande_factuur_detail where udId = $pUitgaandeFactuurDetail";
        $db->Query($sqlStat);

        if ($udRec = $db->Row())
            return $udRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Uitgaande Factuur
    //
    // In:	Uitgaande Factuur ID
    //
    // Return: ufRec
    // ========================================================================================

    static function Get_EFIN_ufRec($pUitgaandeFactuur) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_uf_uitgaande_facturen where ufId = $pUitgaandeFactuur";
        $db->Query($sqlStat);

        if ($ufRec = $db->Row())
            return $ufRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Ventilatie-categorie
    //
    // In:	Ventilatie Categorie Code
    //
    // Return: vrRec
    // ========================================================================================

    static function Get_EFIN_vcRec($pVentilatieCategorieCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_vc_ventilatie_categorie where vcIdCode = '$pVentilatieCategorieCode'";
        $db->Query($sqlStat);


        if ($vcRec = $db->Row())
            return $vcRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Ventilatie-rekening
    //
    // In:	Ventilatie-rekening
    //
    // Return: vrRec
    // ========================================================================================

    static function Get_EFIN_vrRec($pVentilatieRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_vr_ventilatie_rekeningen where vrId = $pVentilatieRekening";
        $db->Query($sqlStat);

        if ($vrRec = $db->Row())
            return $vrRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Interface Horeca Header
    //
    // In:	Interface ID
    //
    // Return: xhRec
    // ========================================================================================

    static function Get_EFIN_xhRec($pInterface) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_xh_interface_horeca_header where xhId = $pInterface";
        $db->Query($sqlStat);

        if ($xhRec = $db->Row())
            return $xhRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EFIN Interface Parameters
    //
    // In:	Interface
    //  	Code
    //
    // Return: xxRec
    // ========================================================================================

    static function Get_EFIN_xxRec($pInterface, $pCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '$pInterface' and xxCode = '$pCode'";
        $db->Query($sqlStat);

        if ($xxRec = $db->Row())
            return $xxRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ELA Kaart-Record
    //
    // In:	Kaart
    //
    // Return: kaRec
    // ========================================================================================

    static function Get_ELA_kaRec($pKaart) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ela_ka_kaarten where kaKaartCode = '$pKaart'";

        $db->Query($sqlStat);

        if ($kaRec = $db->Row())
            return $kaRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ELA Kaart-subype Record
    //
    // In:	Type
    //      Subtype
    //
    // Return: ksRec
    // ========================================================================================

    static function Get_ELA_ksRec($pType, $pSubtype) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ela_ks_kaart_subtypes where ksType = '$pType' and ksSubtype = '$pSubtype'";

        $db->Query($sqlStat);

        if ($ksRec = $db->Row())
            return $ksRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA actiepunt-record
    //
    // In:	Actiepunt
    //
    // Return: taRec
    // ========================================================================================

    static function Get_EMA_acRec($pActiepunt) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_ac_actie_punten where acId = $pActiepunt";
        $db->Query($sqlStat);

        if ($acRec = $db->Row())
            return $acRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA groep-record
    //
    // In:	Groep-Code
    //
    // Return: taRec
    // ========================================================================================

    static function Get_EMA_grRec($pGroep) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_gr_groepen where grCode = '$pGroep'";
        $db->Query($sqlStat);

        if ($grRec = $db->Row())
            return $grRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA agendapunt-record
    //
    // In:	Agendapunt
    //
    // Return: maRec
    // ========================================================================================

    static function Get_EMA_maRec($pAgenda) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_ma_meeting_agendapunten where maId = $pAgenda";
        $db->Query($sqlStat);

        if ($maRec = $db->Row())
            return $maRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA bijlage-record
    //
    // In:	Bijlage
    //
    // Return: mbRec
    // ========================================================================================

    static function Get_EMA_mbRec($pBijlage) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_mb_meeting_bijlagen where mbId = $pBijlage";
        $db->Query($sqlStat);

        if ($mbRec = $db->Row())
            return $mbRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA deelnemer-record
    //
    // In:	Meeting
    //      Persoon
    //
    // Return: mbRec
    // ========================================================================================

    static function Get_EMA_mdRec($pMeeting, $pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_md_meeting_deelnemers where mdMeeting = $pMeeting and mdPersoon = '$pPersoon'";
        $db->Query($sqlStat);

        if ($mdRec = $db->Row())
            return $mdRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA meeting-record
    //
    // In:	Meeting ID
    //
    // Return: taRec
    // ========================================================================================

    static function Get_EMA_meRec($pMeeting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_me_meetings where meId = $pMeeting";
        $db->Query($sqlStat);

        if ($meRec = $db->Row())
            return $meRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA meetingtype-record
    //
    // In:	MeetingGroep
    //      MeetingType
    //
    // Return: mtRec
    // ========================================================================================

    static function Get_EMA_mtRec($pMeetingGroep, $pMeetingType) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ema_mt_meeting_types where mtGroep = '$pMeetingGroep' and mtId = $pMeetingType";
        $db->Query($sqlStat);

        if ($mtRec = $db->Row())
            return $mtRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMO Medisch Dossier Header
    //
    // In:	Medisch Dossier ID
    //
    // Return: mtRec
    // ========================================================================================

    static function Get_EMO_mhRec($pDossier) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from emo_mh_medisch_dossier_headers where mhId = $pDossier";
        $db->Query($sqlStat);

        if ($mhRec = $db->Row())
            return $mhRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMO Blessure
    //
    // In:	Blessure ID
    //
    // Return: blRec
    // ========================================================================================

    static function Get_EMO_blRec($pBlessure) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from emo_bl_blessures where blId = $pBlessure";
        $db->Query($sqlStat);

        if ($blRec = $db->Row())
            return $blRec;
        else
            return null;

    }


    // ========================================================================================
    // Get EMO Medisch Dossier Detail
    //
    // In:	Medisch Dossier Detail ID
    //
    // Return: mtRec
    // ========================================================================================

    static function Get_EMO_mdRec($pDossierDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from emo_md_medisch_dossier_detail where mdId = $pDossierDetail";
        $db->Query($sqlStat);

        if ($mdRec = $db->Row())
            return $mdRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EPPA Persoonlijke Pagina Item (Via alternatieve sleutel)
    //
    // In:	Persoon
    //      Type
    //
    // Return: taRec
    // ========================================================================================

    static function Get_EPPA_ppRec($pPersoon, $pType) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eppa_pp_persoonlijke_pagina where ppPersoon = '$pPersoon' and ppType = '$pType'";
        $db->Query($sqlStat);

        if ($ppRec = $db->Row())
            return $ppRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EPRA Rekening-header
    //
    // In:	Rekening ID
    //
    // Return: rhRec
    // ========================================================================================

    static function Get_EPRA_rhRec($pRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from epra_rh_rekening_headers where rhId = $pRekening";
        $db->Query($sqlStat);

        if ($rhRec = $db->Row())
            return $rhRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EMA meetingtype-record
    //
    // In:	Wedstrijd/Training-ID
    //
    // Return: twRec
    // ========================================================================================

    static function Get_ERA_twRec($pWedstrijdTraining) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_twbs_tw where twId = $pWedstrijdTraining";
        $db->Query($sqlStat);

        if ($twRec = $db->Row())
            return $twRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ERA Diverse Prestatie Types - Record
    //
    // In:	Prestatie type
    //
    // Return: twRec
    // ========================================================================================

    static function Get_ERA_dtRec($pPrestatieType) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from era_dt_diverse_prestatie_types where dtCode = '$pPrestatieType'";
        $db->Query($sqlStat);

        if ($dtRec = $db->Row())
            return $dtRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ESA stage header
    //
    // In:	Stage ID
    //
    // Return: shRec
    // ========================================================================================

    static function Get_ESA_shRec($pStage) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from esa_sh_stage_headers where shId = $pStage";
        $db->Query($sqlStat);

        if ($shRec = $db->Row())
            return $shRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ESA stage Inschrijving
    //
    // In:	Inschrijving ID
    //
    // Return: siRec
    // ========================================================================================

    static function Get_ESA_siRec($pInschrijving) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from esa_si_stage_inschrijvingen where siId = $pInschrijving";
        $db->Query($sqlStat);

        if ($siRec = $db->Row())
            return $siRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ESA stage Lichting
    //
    // In:	Lichting ID
    //
    // Return: slRec
    // ========================================================================================

    static function Get_ESA_slRec($pLichting) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from esa_sl_stage_lichtingen where slId = $pLichting";
        $db->Query($sqlStat);

        if ($slRec = $db->Row())
            return $slRec;
        else
            return null;

    }

    // ========================================================================================
    // Get ESA stage Gadget
    //
    // In:	Gadget ID
    //
    // Return: sgRec
    // ========================================================================================

    static function Get_ESA_sgRec($pGadget) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from esa_sg_stage_gadgets where sgId = $pGadget";
        $db->Query($sqlStat);

        if ($sgRec = $db->Row())
            return $sgRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EVA Evaluatie-header
    //
    // In:	$pEvaluatie
    //
    // Return: ehRec
    // ========================================================================================

    static function Get_EVA_ehRec($pEvaluatie) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from eva_eh_evaluatie_headers where ehId = $pEvaluatie";
        $db->Query($sqlStat);

        if ($ehRec = $db->Row())
            return $ehRec;
        else
            return null;

    }

    // ========================================================================================
    // Get EVIM Mail Template
    //
    // In:	$pMailTemplate
    //
    // Return: imRec
    // ========================================================================================

    static function Get_EVIM_imRec($pMailTemplate) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from evim_im_info_mail where imId = $pMailTemplate";
        $db->Query($sqlStat);

        if ($imRec = $db->Row())
            return $imRec;
        else
            return null;

    }

    // ========================================================================================
    // Get "contactgegevens" record
    //
    // In:	Persoon
    //
    // Return: adRec
    // ========================================================================================

    static function Get_SSP_adRec($pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";
        $db->Query($sqlStat);

        if ($adRec = $db->Row())
            return $adRec;
        else
            return null;

    }

    // ========================================================================================
    // Get "Functie VB" record
    //
    // In:	Functie-code
    //
    // Return: fvRec
    // ========================================================================================

    static function Get_SSP_fvRec($pFunctie) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_fv where fvCode = '$pFunctie'";
        $db->Query($sqlStat);

        if ($fvRec = $db->Row())
            return $fvRec;
        else
            return null;

    }
    // ========================================================================================
    // Get "Functie SSP" record
    //
    // In:	Functie-code
    //
    // Return: fsRec
    // ========================================================================================

    static function Get_SSP_fsRec($pFunctie) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_fs where fvCode = '$pFunctie'";
        $db->Query($sqlStat);

        if ($fsRec = $db->Row())
            return $fsRec;
        else
            return null;

    }
    // ========================================================================================
    // Get "KAART" record
    //
    // In:	Kaartcode
    //
    // Return: kaRec
    // ========================================================================================

    static function Get_SSP_kaRec($pKaartCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ka_kaarten where kaKaartCode = '$pKaartCode'";
        $db->Query($sqlStat);

        if ($kaRec = $db->Row())
            return $kaRec;
        else
            return null;

    }

    // ========================================================================================
    // Get MailGroep Record
    //
    // In:	Mailgroep
    //
    // Return: kaRec
    // ========================================================================================

    static function Get_SSP_mgRec($pMailGroep) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_mg_mail_groepen where mgMailGroep = '$pMailGroep'";
        $db->Query($sqlStat);

        if ($mgRec = $db->Row())
            return $mgRec;
        else
            return null;

    }

    // ========================================================================================
    // Get "voetbalploeg" record
    //
    // In:	ploeg
    //
    // Return: vpRec
    // ========================================================================================

    static function Get_SSP_vpRec($pPloeg) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_vp where vpId = $pPloeg";
        $db->Query($sqlStat);

        if ($vpRec = $db->Row())
            return $vpRec;
        else
            return null;

        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    // Get Seizoen record
    //
    // In:	Seizoen
    //
    // Return: vsRec
    // ========================================================================================

    static function Get_SSP_vsRec($pSeizoen) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_vs where vsCode = '$pSeizoen'";
        $db->Query($sqlStat);

        if ($vsRec = $db->Row())
            return $vsRec;
        else
            return null;

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Get "voetbalwedstrijd" record
    //
    // In:	Wedstrijd
    //
    // Return: vwRec
    // ========================================================================================

    static function Get_SSP_vwRec($pWedstrijd) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_vw where vwId = $pWedstrijd";
        $db->Query($sqlStat);

        if ($vwRec = $db->Row())
            return $vwRec;
        else
            return null;

        // -------------
        // Einde functie
        // -------------

    }

	// ========================================================================================
    // Get "Tables" record
    //
    // In:	Table
    //      Code
    //
    // Return: taRec
    // ========================================================================================

    static function Get_SX_taRec($pTable, $pCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from sx_ta_tables where taTable = '$pTable' and taCode = '$pCode'";
        $db->Query($sqlStat);


        if ($taRec = $db->Row()) {
            return $taRec;
        }
        else {
            return null;
        }

    }



    // -----------
    // Einde CLASS
    // -----------

}

?>