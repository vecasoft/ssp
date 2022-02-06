<?php

// We use SPOUT top read and create Excel files

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class SSP_efin_interface_horeca
{ // define the class


    // ========================================================================================
    //  Horeca Interface - Ophalen kasboek
    //
    // In:  GEEN
    // Uit: Rekening
    //
    // ========================================================================================

    static function GetHorecaKasboek(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $kasboek = null;

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*HORECA' and  xxCode = '*KAS-HORECA'";
        $db->Query($sqlStat);

        if ($xxRek = $db->Row())
            $kasboek = $xxRek->xxRekening;

        // -------------
        // Einde functie
        // -------------

        return $kasboek;

    }
    // ========================================================================================
    //  Horeca Interface - Omschrijving verplicht?
    //
    // In:  Ventilatie-rekening
    //
    // Uit: verplicht?
    //
    // ========================================================================================

    static function CheckOmschrijvingVerplicht($pVentilatie){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select count(*) as aantal from efin_vr_ventilatie_rekeningen where vrId = $pVentilatie and vrInterfaces like '%_OV%'";
        $db->Query($sqlStat);

        if ($vrRec = $db->Row())
            if ($vrRec->aantal > 0)
                return true;

        // -------------
        // Einde functie
        // -------------

        return false;

    }

    // ========================================================================================
    //  Horeca Interface - Bijwerken totalen en controle-status
    //
    // In:	Horeca Interface
    //
    // ========================================================================================

    static function UpdInterfaceHoreca($pInterfaceHoreca) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from efin_xh_interface_horeca_header where xhId = $pInterfaceHoreca";
        $db->Query($sqlStat);

        if (! $xhRec = $db->Row())
            return;

        // ----------------
        // Bedrag START-kas
        // ----------------

        $kasBedrag = ($xhRec->xhKasCent1 * (1 / 100))
            + ($xhRec->xhKasCent2 * (1 / 50))
            + ($xhRec->xhKasCent5 * (1 / 20))
            + ($xhRec->xhKasCent10 * (1 / 10))
            + ($xhRec->xhKasCent20 * (1 / 5))
            + ($xhRec->xhKasCent50 * (1 / 2))
            + ($xhRec->xhKasEur1)
            + ($xhRec->xhKasEur2 * 2)
            + ($xhRec->xhKasEur5 * 5)
            + ($xhRec->xhKasEur10 * 10)
            + ($xhRec->xhKasEur20 * 20)
            + ($xhRec->xhKasEur50 * 50)
            + ($xhRec->xhKasEur100 * 100)
            + ($xhRec->xhKasEur200 * 200);

        $kasBedrag = round($kasBedrag,2);

        // -------------
        // Verrichtingen
        // -------------

        $verrichtingenIn = 0;
        $verrichtingenUit    = 0;

        $sqlStat = "Select * from efin_xi_interface_horeca_detail where xiInterfaceHeader = $pInterfaceHoreca";
        $db->Query($sqlStat);

        $aantalVerrichtingen = 0;

        while ($xiRec = $db->Row()){

            $aantalVerrichtingen++;

            $verrichtingenIn += $xiRec->xiBedragIn;
            $verrichtingenUit += $xiRec->xiBedragUit;

        }


        $saldo = ($verrichtingenIn - $verrichtingenUit);
        $saldo = round($saldo,2);

        // -------------------------------
        // Ophalen aantal ingegeven shifts
        // -------------------------------

        $sqlStat = "Select * from efin_xp_interface_horeca_prestaties where xpInterfaceHeader = $pInterfaceHoreca";
        $db->Query($sqlStat);

        $aantalShifts = 0;

        while ($xpRec = $db->Row()){

            $aantalShifts += $xpRec->xpAantalShifts;
        }

        // -------------
        // Controle-code
        // -------------

        $controleCode = '*OK';

        if ($kasBedrag != $saldo)
            $controleCode = '*SALDO';

        if (! $kasBedrag and ! $aantalVerrichtingen and $aantalShifts <= 0)
            $controleCode = '*NIETS';

        if (($controleCode == '*OK') and ($aantalShifts <= 0) and ($xhRec->xhGeenShifts <> 1))
            $controleCode = '*SHIFTS';

        // -----------------------
        // Update Interface record
        // -----------------------

        $values = array();
        $where = array();

        $values["xhControleCode"] =  MySQL::SQLValue($controleCode, MySQL::SQLVALUE_TEXT);

        $values["xhKasBedrag"] =  MySQL::SQLValue($kasBedrag, MySQL::SQLVALUE_NUMBER);
        $values["xhVerrichtingenIn"] =  MySQL::SQLValue($verrichtingenIn, MySQL::SQLVALUE_NUMBER);
        $values["xhVerrichtingenUit"] =  MySQL::SQLValue($verrichtingenUit, MySQL::SQLVALUE_NUMBER);

        $values["xhAantalShifts"] =  MySQL::SQLValue($aantalShifts, MySQL::SQLVALUE_NUMBER);

        $where["xhId"] =  MySQL::SQLValue($pInterfaceHoreca, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_xh_interface_horeca_header", $values, $where);

        // -------------------------------------------------
        // Datum prestaties (gelijk zetten aan datum header)
        // -------------------------------------------------

        $datum = $xhRec->xhDatum;

        $sqlStat = "update efin_xp_interface_horeca_prestaties set xpDatum = '$datum' where xpInterfaceHeader = $pInterfaceHoreca";
        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

        return;

    }
    // ========================================================================================
    //  Horeca Interface - Doorboeken openstaanden
    //
    // ========================================================================================

    static function BoekInterfacesHoreca(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ------------------
        // Weekafrekening(en)
        // ------------------

        $sqlStat = "Select * from efin_xh_interface_horeca_header where xhBoekStatus <> '*GEBOEKT' and xhControleCode = '*OK'";
        $db->Query($sqlStat);

        while ($xhRec = $db->Row())
            self::BoekWeekafrekening($xhRec->xhId);

        // -------------
        // Kas TICKETING
        // -------------

        $sqlStat = "Select * from efin_xk_interface_kas_ticketing where xkBoekStatus <> '*GEBOEKT'";
        $db->Query($sqlStat);

        while ($xkRec = $db->Row())
            self::BoekKasTicketingTelling($xkRec->xkId);


        // ------------
        // Storting(en)
        // ------------

        $sqlStat = "Select * from efin_kb_kas_naar_bank where kbBoekStatus <> '*GEBOEKT'";
        $db->Query($sqlStat);

        while ($kbRec = $db->Row())
            self::BoekStorting($kbRec->kbId);


        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Horeca Weekafrekening - Doorboeken
    //
    // In:	Horeca Interface-record ID
    //
    // ========================================================================================

    static function BoekWeekafrekening($pInterfaceHoreca){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("efin.class"));
        include_once(Sx::GetSxClassPath("tools.class"));
        include(SX::GetClassPath("efin_interface_era.class"));

        // ---------------------
        // Ophalen basisgegevens
        // ---------------------

        $sqlStat = "Select * from efin_xh_interface_horeca_header where xhId = $pInterfaceHoreca";
        $db->Query($sqlStat);

        if (! $xhRec = $db->Row())
            return;

        $wachtkas = $xhRec->xhWachtKas;

        if ($xhRec->xhControleCode != '*OK')
            return;

        $kasboek = self::GetHorecaKasboek();

        if (! $kasboek)
            return;

        $rkRec = SSP_db::Get_EFIN_rkRec($kasboek);

        // ------------------
        // Boek verrichtingen
        // ------------------

        $sqlStat = "Select * from efin_xi_interface_horeca_detail where xiInterfaceHeader = $pInterfaceHoreca order by xiDatumCreatie, xiId";
        $db->Query($sqlStat);

        $valuta = $rkRec->rkValuta;
        $datum = $xhRec->xhDatum;
        $user = $xhRec->xhUserUpdate;

        $curDateTime = date('Y-m-d H:i:s');

        while ($xiRec = $db->Row()){

            $volgnummer = SSP_efin::GetVolgendKasVolgnr($kasboek, $datum);

            $bedrag = null;
            $ventilatie = null;
            $omschrijving = $xiRec->xiOmschrijving;

            if ($xiRec->xiBedragIn){
                $bedrag = $xiRec->xiBedragIn + 0;
                $ventilatie = $xiRec->xiVentilatieIn;
            } else {
                $bedrag = ($xiRec->xiBedragUit * -1) + 0;
                $ventilatie = $xiRec->xiVentilatieUit;
            }

            // Default omschrijving
            if (! $omschrijving){

                $vrRec = ssp_db::Get_EFIN_vrRec($ventilatie);

                $omschrijving = $vrRec->vrDefaultOmschrijving;

            }

            $values = array();

            $values["rdRekening"] = MySQL::SQLValue($kasboek, MySQL::SQLVALUE_NUMBER);
            $values["rdVolgnummer"] = MySQL::SQLValue($volgnummer, MySQL::SQLVALUE_TEXT);
            $values["rdDatum"] = MySQL::SQLValue($datum, MySQL::SQLVALUE_DATE);
            $values["rdRefDatum"] = MySQL::SQLValue($datum, MySQL::SQLVALUE_DATE);

            $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
            $values["rdValuta"] = MySQL::SQLValue($valuta, MySQL::SQLVALUE_TEXT);

            $values["rdMededeling"] = MySQL::SQLValue($omschrijving, MySQL::SQLVALUE_TEXT);
            $values["rdOorsprong"] = MySQL::SQLValue('*HORECA-WEEKAFREKENING', MySQL::SQLVALUE_TEXT);
            $values["rdLink"] = MySQL::SQLValue($xiRec->xiId, MySQL::SQLVALUE_NUMBER);

            $values["rdStatusDoorboeken"] = MySQL::SQLValue('*NVT', MySQL::SQLVALUE_TEXT);

            $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["rdUserCreatie"] = MySQL::SQLValue($user, MySQL::SQLVALUE_TEXT);
            $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["rdUserUpdate"] = MySQL::SQLValue($user, MySQL::SQLVALUE_TEXT);

            $values["rdRecStatus"] = MySQL::SQLValue('A', MySQL::SQLVALUE_TEXT);

            $rekeningDetail = $db2->InsertRow("efin_rd_rekening_details", $values);

            if ($rekeningDetail) {
                SSP_efin::CrtEenvoudigeVentilatie($user, $rekeningDetail, $ventilatie);
                SSP_efin::SetRdStatusToewijzen($rekeningDetail);
                SSP_efin::SetRdStatusDoorboeken($rekeningDetail);
            }
        }


        // --------------------------
        // Herberekenen saldi kasboek
        // --------------------------

        SSP_efin::FillRekeningDetailSaldi($kasboek);

        // -------------------------------------------------
        // Indien WACHTKAS opgegeven: Transfer naar deze kas
        // -------------------------------------------------

        if ($wachtkas and ($wachtkas <> $kasboek) and ($xhRec->xhKasBedrag)){

            $bedragTransfer = $xhRec->xhKasBedrag * -1;

            SSP_efin::CrtKasBoeking($user, $kasboek, $datum, $bedragTransfer, "transfer xxx", 0, $wachtkas, '*HORECA-WEEKAFREKENING', $xhRec->xhId);


        }

        // --------------------------
        // Herberekenen saldi kasboek
        // --------------------------

        SSP_efin::FillRekeningDetailSaldi($kasboek);

        // -------------------------------------------
        // Doorboeken prestaties -> diverse prestaties
        // -------------------------------------------

        $sqlStat = "Select * from efin_xp_interface_horeca_prestaties where xpInterfaceHeader = $pInterfaceHoreca";
        $db->Query($sqlStat);

        while ($xpRec = $db->Row()){

            $datum = $xpRec->xpDatum;
            $datumE = SX_tools::EdtDate($datum);

            $omschrijving = "Prestatie(s) in de week van: $datumE";

            $user = '*HORECA-INTERFACE';
            $persoon = $xpRec->xpPersoon;
            $aantalShifts = $xpRec->xpAantalShifts;

            SSP_efin_interface_era::CrtDiversePrestatie($persoon, $aantalShifts, '*HORECA', $datum, $omschrijving, $user);

        }


        // -------------------------------
        // PADEL verhuur/verkoop materiaal
        // -------------------------------

        if ($xhRec->xhPadelBallen or $xhRec->xhPadelRackets){

            $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*HORECA' and xxCode = '*PADEL'";
            $db->Query($sqlStat);
            $xxRec = $db->Row();

            $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*HORECA' and xxCode = '*PADEL-TEGENBOEKING'";
            $db->Query($sqlStat);
            $xxRecTb = $db->Row();

            $datum = $xhRec->xhDatum;
            $user = $xhRec->xhUserUpdate;

            if ($xhRec->xhPadelBallen){

                $prijsVerkoopBallen = self::GetPrijsVerkoopPadelBallen();

                $kasboek = $xxRec->xxRekening;
                $ventilatie = $xxRec->xxVentilatie;
                $mededeling = "Padel - Verkoop van ballen";
                $bedrag = $xhRec->xhPadelBallen * $prijsVerkoopBallen;

                SSP_efin::CrtKasBoeking($user, $kasboek, $datum, $bedrag, $mededeling, $ventilatie, 0, '*HORECA-WEEKAFREKENING', $xhRec->xhId);

                $bedrag = $bedrag * -1;
                $ventilatie = $xxRecTb->xxVentilatie;

                SSP_efin::CrtKasBoeking($user, $kasboek, $datum, $bedrag, $mededeling, $ventilatie, 0, '*HORECA-WEEKAFREKENING', $xhRec->xhId);

                SSP_efin::FillRekeningDetailSaldi($kasboek);

            }

            if ($xhRec->xhPadelRackets){

                $prijsVerhuurPallets = self::GetPrijsVerhuurPadelPaletten();

                $kasboek = $xxRec->xxRekening;
                $ventilatie = $xxRec->xxVentilatie;
                $mededeling = "Padel - Verhuur van rackets";
                $bedrag = $xhRec->xhPadelRackets * $prijsVerhuurPallets;

                SSP_efin::CrtKasBoeking($user, $kasboek, $datum, $bedrag, $mededeling, $ventilatie, 0, '*HORECA-WEEKAFREKENING', $xhRec->xhId);

                $bedrag = $bedrag * -1;
                $ventilatie = $xxRecTb->xxVentilatie;

                SSP_efin::CrtKasBoeking($user, $kasboek, $datum, $bedrag, $mededeling, $ventilatie, 0, '*HORECA-WEEKAFREKENING', $xhRec->xhId);

                SSP_efin::FillRekeningDetailSaldi($kasboek);

            }


        }


        // ---------------
        // Set Boek-status
        // ---------------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();
        $where = array();

        $values["xhBoekStatus"] =  MySQL::SQLValue('*GEBOEKT', MySQL::SQLVALUE_TEXT);
        $values["xhBoekDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $where["xhId"] =  MySQL::SQLValue($pInterfaceHoreca, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_xh_interface_horeca_header", $values, $where);

        // ---------------------------
        // Log datum "laatste boeking"
        // ---------------------------

        SSP_efin::SetInterfaceDatumLaatsteBoeking('*HORECA');

        // -------------
        // Einde functie
        // -------------


    }

    // ========================================================================================
    //  Kas TICKETING - Doorboeken
    //
    // In:	Kas Ticketing Interface ID
    //
    // ========================================================================================

    static function BoekKasTicketingTelling($pKasTicketingInterfaceId){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("efin.class"));

        $sqlStat = "Select * from efin_xk_interface_kas_ticketing where xkId = $pKasTicketingInterfaceId and xkBoekStatus <> '*GEBOEKT'";
        $db->Query($sqlStat);

        if (! $xkRec = $db->Row())
            return;

        $user  = $xkRec->xkUserCreatie;

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*TICKETING' and  xxCode = '*KASTELLING'";
        $db->Query($sqlStat);

        if ($xxRek = $db->Row()) {
            $kasTicketing = $xxRek->xxRekening;
            $ventilatie = $xxRek->xxVentilatie;
        }
        else
            return;

        $huidigSaldo = SSP_efin::GetRekeningSaldo($kasTicketing);

        // --------------------------------------------------------------
        // Boek verschil bedrag-geteld met huidige saldo in kas ticketing
        // --------------------------------------------------------------

        if ($huidigSaldo != $xkRec->xkBedragGeteld) {

            $verschil =  $xkRec->xkBedragGeteld - $huidigSaldo;
            $curDate = date('Y-m-d');

            $boekDatum = $xkRec->xkDatum;
            if (! $boekDatum)
                $boekDatum = $curDate;

            $naam = $xkRec->xkUserCreatie;
            $adRec = SSP_db::Get_SSP_adRec($xkRec->xkUserCreatie);

            if ($adRec)
                $naam = $adRec->adVoornaamNaam;

            $mededeling = "Kas-telling door $naam";

            SSP_efin::CrtKasBoeking($user,$kasTicketing, $boekDatum, $verschil, $mededeling,$ventilatie,0, '*KASTELLING');

        }

        // --------------------------------
        // Boek transfer naar wachtrekening
        // --------------------------------

        if (($xkRec->xkBedragNaarWachtkas > 0) and $xkRec->xkWachtKas){

            $wachtkas = $xkRec->xkWachtKas;
            $rkRecWachtkas = SSP_db::Get_EFIN_rkRec($wachtkas);

            $bedragTransfer = $xkRec->xkBedragNaarWachtkas * -1;
            $naamWachtkas = $rkRecWachtkas->rkNaam;

            $mededeling = "Transfer Kas Ticketing -> $naamWachtkas";

            SSP_efin::CrtKasBoeking($user,$kasTicketing, $curDate, $bedragTransfer, $mededeling,0,$wachtkas, '*KASTELLING');

        }


        // ---------------
        // Set Boek-status
        // ---------------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();
        $where = array();

        $values["xkBoekStatus"] =  MySQL::SQLValue('*GEBOEKT', MySQL::SQLVALUE_TEXT);
        $values["xkBoekDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $where["xkId"] =  MySQL::SQLValue($pKasTicketingInterfaceId, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_xk_interface_kas_ticketing", $values, $where);


        // ----------------------------------
        // Herbereken saldo kasboek ticketing
        // ----------------------------------

        SSP_efin::FillRekeningDetailSaldi($kasTicketing);



    }

    // ========================================================================================
    //  Horeca Storting - Doorboeken
    //
    // In:	Storting ID
    //
    // ========================================================================================

    static function BoekStorting($pStorting){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("efin.class"));

        // ---------------------
        // Ophalen basisgegevens
        // ---------------------

        $kbRec = SSP_db::Get_EFIN_kbRec($pStorting);
        if (! $kbRec)
            return;

        $kasboek = $kbRec->kbKas;

        $rkRek = SSP_db::Get_EFIN_rkRec($kasboek);
        if (! $rkRek)
            return;


        if ($kbRec->kbBankrekening)
            $zichtrekening = $kbRec->kbBankrekening;
        else
            $zichtrekening = self::GetZichtrekeningStortingen();

        // ---------------------------------------
        // Boek transfer wachtkas >> zichtrekening
        // ---------------------------------------

        $datum = $kbRec->kbDatum;
        $user = $kbRec->kbUserUpdate ;
        $bedrag = $kbRec->kbBedrag * -1;

        SSP_efin::CrtKasBoeking($user, $kasboek, $datum, $bedrag, "transfer xxx", 0, $zichtrekening, '*HORECA-STORTING', $pStorting);

        // ---------------
        // Set Boek-status
        // ---------------

        $curDateTime = date('Y-m-d H:i:s');

        $values = array();
        $where = array();

        $values["kbBoekStatus"] =  MySQL::SQLValue('*GEBOEKT', MySQL::SQLVALUE_TEXT);
        $values["kbBoekDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $where["kbId"] =  MySQL::SQLValue($pStorting, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_kb_kas_naar_bank", $values, $where);


        // --------------------------
        // Herberekenen saldi kasboek
        // --------------------------

        SSP_efin::FillRekeningDetailSaldi($kasboek);

        // ---------------------------
        // Log datum "laatste boeking"
        // ---------------------------

        SSP_efin::SetInterfaceDatumLaatsteBoeking('*HORECA');

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Horeca Interface - Wissen
    //
    // In:	Horeca Interface-record ID
    //
    // ========================================================================================

    static function DeleteInterfaceHoreca($pInterfaceHoreca) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Delete  from efin_xi_interface_horeca_detail where xiInterfaceHeader = $pInterfaceHoreca";
        $db->Query($sqlStat);

    }

    // ========================================================================================
    //  Ophalen saldo "wachtkas"
    //
    // In:  kas
    //      Huidige Storting ID (optionioneel)
    //
    // ========================================================================================

    static function GetWachtkasSaldo($pWachtkas, $pHuidigeStorting = 0){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(SX::GetClassPath("efin.class"));

        // ------------------------
        // Start met saldo uit EFIN
        // ------------------------

        $saldo = SSP_efin::GetRekeningSaldo($pWachtkas);

        // -------------------------------------------
        // Verhoog met openstaande week-afrekening(en)
        // -------------------------------------------

        $sqlStat = "Select * from efin_xh_interface_horeca_header where xhBoekStatus <> '*GEBOEKT' and xhControleCode = '*OK' and xhWachtKas = $pWachtkas";
        $db->Query($sqlStat);

        while ($xhRec = $db->Row())
            $saldo += $xhRec->xhKasBedrag;

        // ---------------------------------------------
        // Verhoog met openstaande ticketing tellingen
        // ---------------------------------------------

        $sqlStat = "Select * from efin_xk_interface_kas_ticketing where xkBoekStatus <> '*GEBOEKT' and xkWachtKas = $pWachtkas";
        $db->Query($sqlStat);

        while ($xkRec = $db->Row())
            $saldo += $xkRec->xkBedragNaarWachtkas;

        // --------------------------------------
        // Verminder met openstaande storting(en)
        // --------------------------------------

        $sqlStat = "Select * from efin_kb_kas_naar_bank where kbKas = $pWachtkas and kbBoekStatus <> '*GEBOEKT' and kbId <> $pHuidigeStorting";
        $db->Query($sqlStat);

        while ($kbRec = $db->Row())
            $saldo -= $kbRec->kbBedrag;

        // -------------
        // Einde functie
        // -------------

        return $saldo;



    }

    // ========================================================================================
    //  Ophalen zichtrekening voor de stortingen
    //
    // In: Geen
    // Uit: Rekening
    //
    // ========================================================================================

    static function GetZichtrekeningStortingen(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $rekening = 0;

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*HORECA' and xxCode = '*ZICHTREKENING'";
        $db->Query($sqlStat);

        if ($xxRec = $db->Row())
            $rekening = $xxRec->xxRekening;

        // -------------
        // Einde functie
        // -------------

        return $rekening;

    }
    // ========================================================================================
    //  Horeca Interface - Bijwerken storing "bedrag toegewezen"
    //
    // In:	Storting ID
    //
    // ========================================================================================

    static function UpdStortingBedragToegewezen($pStorting){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select sum(srBedrag) as bedragToegewezen from efin_sr_storting_rekening_detail where srStorting = $pStorting";
        $db->Query($sqlStat);

        $bedragToegewezen = 0;

        if ($srRec = $db->Row()) {

            if ($srRec->bedragToegewezen)
                $bedragToegewezen = $srRec->bedragToegewezen;

        }

        $sqlStat = "Update efin_kb_kas_naar_bank set kbBedragToegewezen = $bedragToegewezen where kbId = $pStorting";

        $db->Query($sqlStat);

        
        // -------------
        // Einde functie
        // -------------


    }

    // ========================================================================================
    //  Horeca Interface - Ophalen prijs verkoop padel ballen
    //
    // In:	    Geen
    //
    // Return:  Prijs
    //
    // ========================================================================================

    static function GetPrijsVerkoopPadelBallen(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*HORECA' and xxCode = '*PADEL_PRIJS_VERKOOP_BALLEN'";
        $db->Query($sqlStat);

        $prijs = 1;

        if ($xxRec = $db->Row())
            if ($xxRec->xxParameter)
                $prijs = $xxRec->xxParameter;

        // -------------
        // Einde functie
        // -------------

        return $prijs;

    }

    // ========================================================================================
    //  Horeca Interface - Ophalen prijs verhuur padel paletten
    //
    // In:	    Geen
    //
    // Return:  Prijs
    //
    // ========================================================================================

    static function GetPrijsVerhuurPadelPaletten(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*HORECA' and xxCode = '*PADEL_PRIJS_VERHUUR_PALLETS'";
        $db->Query($sqlStat);

        $prijs = 1;

        if ($xxRec = $db->Row())
            if ($xxRec->xxParameter)
                $prijs = $xxRec->xxParameter;

        // -------------
        // Einde functie
        // -------------

        return $prijs;

    }

    // ========================================================================================
    //  Horeca Interface - Check prestatie-persoon uniek
    //
    // In:	    Persoon
    //          Prestatie-header
    //
    // Return:  Prijs
    //
    // ========================================================================================

    static function ChkPrestatiePersoonUniek($pPersoon, $pInterfaceHoreca)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $isUniek = true;

        $sqlStat = "Select count(*) as aantal from efin_xp_interface_horeca_prestaties where xpInterfaceHeader = $pInterfaceHoreca and xpPersoon = '$pPersoon'";

        $db->Query($sqlStat);

        if ($xpRec = $db->Row())
            if ($xpRec->aantal >= 1)
                $isUniek = false;

        // -------------
        // Einde functie
        // -------------

        return $isUniek;

    }

    // -----------
    // Einde class
    // -----------

}

?>