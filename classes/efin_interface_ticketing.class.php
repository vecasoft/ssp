<?php

// We use SPOUT top read and create Excel files

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class SSP_efin_interface_ticketing
{ // define the class


    // ========================================================================================
    //  Opvullen diverse bedragen ticketing bepaalde ticketing groepering
    //
    // In:  Ticketing Groepering ID
    //
    // Uit: Uitgevoerd? (true/false)
    //
    // ========================================================================================

    static function UpdBedragenTicketingGroepering($pTicketingGroepering){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from efin_tg_ticketing_groepering where tgId = $pTicketingGroepering";
        $db->Query($sqlStat);

        if (! $tgRec = $db->Row())
            return false;

        $datumVan = $tgRec->tgDatumVan;
        $datumTot = $tgRec->tgDatumTot;

        // -----------------------------
        // Ophalen ventilatie-rekeningen
        // -----------------------------

        $ventsTicketingOmzet = array();

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*TICKETING' and xxCode like '*TICKETS%'" ;
        $db->Query($sqlStat);

        while($xxRec = $db->Row())
            $ventsTicketingOmzet[] = $xxRec->xxVentilatie;

        $sqlStat = "Select * from efin_vr_ventilatie_rekeningen where vrInterfaces like '%OMZET-TICKETING%'" ;
        $db->Query($sqlStat);

        while($vrRec = $db->Row())
            $ventsTicketingOmzet[] = $vrRec->vrId;

        $ventsTicketingOmzet = array_unique($ventsTicketingOmzet);

        $ventsVergoedingen = array();

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*TICKETING' and xxCode like '*VERGOEDING%'" ;
        $db->Query($sqlStat);

        while($xxRec = $db->Row())
            $ventsVergoedingen[] = $xxRec->xxVentilatie;

        $ventsVergoedingen = array_unique($ventsVergoedingen);

        $ventsKastelling = array();

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*TICKETING' and xxCode like '*KASTELLING%'" ;
        $db->Query($sqlStat);

        while($xxRec = $db->Row())
            $ventsKastelling[] = $xxRec->xxVentilatie;

        $ventsKastelling = array_unique($ventsKastelling);

        $ventsBankkaartOntvangsten = array();

        $sqlStat = "Select * from efin_xx_interface_parameters where xxInterface = '*TICKETING' and xxCode like '*BETAALTERMINAL-BETALINGEN%'" ;
        $db->Query($sqlStat);

        while($xxRec = $db->Row())
            $ventsBankkaartOntvangsten[] = $xxRec->xxVentilatie;

        $ventsBankkaartOntvangsten = array_unique($ventsBankkaartOntvangsten);

        // --------------------------------
        // Ophalen bedrag verkochte tickets
        // --------------------------------

        $bedragTicketing = 0;

        foreach ($ventsTicketingOmzet as $vent){

            $sqlStat    = "Select sum(twBedrag) as bedrag from efin_tw_rekening_detail_toewijzingen  "
                        . "inner join efin_rd_rekening_details on rdId = twRekeningDetail "
                        . "where twVentilatieRekening = $vent and rdRefDatum >= '$datumVan' and rdRefDatum <= '$datumTot'";



           $db->Query($sqlStat);

           if ($twRec = $db->Row())
               if ($twRec->bedrag)
                   $bedragTicketing += $twRec->bedrag;

        }

        // ---------------------------
        // Ophalen bedrag vergoedingen
        // --------------------------

        $bedragVergoedingen = 0;

        foreach ($ventsVergoedingen as $vent){


            $sqlStat    = "Select sum(twBedrag) as bedrag from efin_tw_rekening_detail_toewijzingen  "
                . "inner join efin_rd_rekening_details on rdId = twRekeningDetail "
                . "where twVentilatieRekening = $vent and rdRefDatum >= '$datumVan' and rdRefDatum <= '$datumTot'";

            $db->Query($sqlStat);

            if ($twRec = $db->Row())
                if ($twRec->bedrag)
                    $bedragVergoedingen += abs($twRec->bedrag);

        }

        // ------------------
        // Ophalen bedrag kas
        // ------------------

        $bedragKas = 0.00;

        foreach ($ventsKastelling as $vent){

            $sqlStat    = "Select sum(twBedrag) as bedrag from efin_tw_rekening_detail_toewijzingen  "
                . "inner join efin_rd_rekening_details on rdId = twRekeningDetail "
                . "where twVentilatieRekening = $vent and rdRefDatum >= '$datumVan' and DATE_SUB(rdRefDatum, INTERVAL 5 DAY) <= '$datumTot'";


            $db->Query($sqlStat);

            if ($twRec = $db->Row())
                if ($twRec->bedrag)
                    $bedragKas += $twRec->bedrag;

        }

        // ------------------------------------
        // Ophalen bedrag bankkaart-ontvangsten
        // ------------------------------------

        $bedragBankkaart = 0;

        foreach ($ventsBankkaartOntvangsten as $vent){

            $sqlStat    = "Select sum(twBedrag) as bedrag from efin_tw_rekening_detail_toewijzingen  "
                . "inner join efin_rd_rekening_details on rdId = twRekeningDetail "
                . "where twVentilatieRekening = $vent and rdRefDatum >= '$datumVan' and rdRefDatum <= '$datumTot'";

            $db->Query($sqlStat);

            if ($twRec = $db->Row())
                if ($twRec->bedrag)
                    $bedragBankkaart += $twRec->bedrag;

        }

        // ------------------
        // Bepaal kasverschil
        // ------------------

        $bedragAndereUitgaven = 0;

        if ($tgRec->tgBedragAndereUitgaven)
            $bedragAndereUitgaven = $tgRec->tgBedragAndereUitgaven;

        $bedragKasverschil = $bedragKas + $bedragBankkaart + $bedragVergoedingen + $bedragAndereUitgaven - $bedragTicketing;

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $berekendOp = date('Y-m-d H:i:s');

        $values["tgBedragTicketing"] =  MySQL::SQLValue($bedragTicketing, MySQL::SQLVALUE_NUMBER);
        $values["tgBedragVergoedingen"] =  MySQL::SQLValue($bedragVergoedingen, MySQL::SQLVALUE_NUMBER);
        $values["tgBedragKas"] =  MySQL::SQLValue($bedragKas, MySQL::SQLVALUE_NUMBER);
        $values["tgBedragBankkaart"] =  MySQL::SQLValue($bedragBankkaart, MySQL::SQLVALUE_NUMBER);
        $values["tgBedragKasverschil"] =  MySQL::SQLValue($bedragKasverschil, MySQL::SQLVALUE_NUMBER);

        $values["tgBerekendOp"] =  MySQL::SQLValue($berekendOp, MySQL::SQLVALUE_DATETIME);

        $where["tgId"] =  MySQL::SQLValue($pTicketingGroepering, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_tg_ticketing_groepering", $values, $where);

        // -------------
        // Einde functie
        // -------------

        return true;

    }



    // ========================================================================================
    //  Boek kasverschil
    //
    // In:  Ticketing Groepering ID
    //
    // Return: Succesvol?
    //
    // ========================================================================================

    static function BoekKasverschil($pTicketingGroepering)
    {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("efin.class"));

        $sqlStat = "Select *, date_add(tgDatumTot, INTERVAL 5 DAY) as boekDatum, DATE_FORMAT(tgDatumVan, '%d/%m/%Y')  as datumVan, DATE_FORMAT(tgDatumTot, '%d/%m/%Y')  as datumTot from efin_tg_ticketing_groepering where tgId = $pTicketingGroepering";
        $db->Query($sqlStat);

        if (!$tgRec = $db->Row())
            return false;

        // -------------------------------------------
        // Niet als reeds geboekt of geen kastverschil
        // -------------------------------------------

        if (! $tgRec->tgBedragKasverschil)
            return false;

        if ($tgRec->tgBedragKasverschil == 0)
            return false;

        if ($tgRec->tgGeboektAutomatisch == 1)
            return false;

        if ($tgRec->tgGeboektManueel == 1)
            return false;

        // ---------------------------------------
        // Geen boeking indien te hoog kasverschil
        // ---------------------------------------

        if (! $tgRec->tgHoogKasverschilToestaan) {

            $hoogKasverschil = self::ChkHoogKasverschil($pTicketingGroepering);

            if ($hoogKasverschil)
                return false;

        }

        // -------------------------------
        // Boekdatum (datum tot + 5 dagen)
        // ------------------------------

        $datumBoeking = $tgRec->boekDatum;

        if (! $datumBoeking)
            return false;


        // ----------------------
        // ophalen  kas ticketing
        // ----------------------

        $kasTicketing = SSP_efin::GetTicketingKasboek();

        if (! $kasTicketing)
            return false;

        // -----------------------------------------------------
        // Ophalen ventilatierekening kasverschil & tegenboeking
        // -----------------------------------------------------

        $xxRec = SSP_db::Get_EFIN_xxRec('*TICKETING','*KASVERSCHIL');
        $ventKasverschil = $xxRec->xxVentilatie;

        if (! $ventKasverschil)
            return false;

        $xxRec = SSP_db::Get_EFIN_xxRec('*TICKETING','*TEGENBOEKING');
        $ventTegenboeking = $xxRec->xxVentilatie;

        if (! $ventTegenboeking)
            return false;

        // ----------------------------
        // Aanmaken boeking kasverschil
        // ----------------------------

        $bedrag = $tgRec->tgBedragKasverschil;
        $mededeling = "Kasverschil Ticketing - Periode "
                    . $tgRec->datumVan
                    . " - "
                    . $tgRec->datumTot;

        SSP_efin::CrtKasBoeking('*AUTO', $kasTicketing, $datumBoeking, $bedrag, $mededeling, $ventKasverschil,0, '*TICKETING-GROEPERING', $pTicketingGroepering );

        // ---------------------------------
        // Aanmaken tegenboeking kasverschil
        // --------------------------------

        $bedrag = $tgRec->tgBedragKasverschil * -1;
        $mededeling = "Kasverschil - Tegenboeking";

        SSP_efin::CrtKasBoeking('*AUTO', $kasTicketing, $datumBoeking, $bedrag, $mededeling, $ventTegenboeking,0, '*TICKETING-GROEPERING');

        // ----------------
        // Herbereken saldi
        // ----------------

        SSP_efin::FillRekeningDetailSaldi($kasTicketing);

        // -----------------------
        // Ophalen rekening-detail
        // -----------------------

        $sqlStat = "Select * from efin_rd_rekening_details where rdLink = $pTicketingGroepering and rdOorsprong = '*TICKETING-GROEPERING'";
        $db->Query($sqlStat);

        $rdId = 0;

        if ($rdRec = $db->Row())
            $rdId = $rdRec->rdId;

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $values["tgGeboektAutomatisch"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
        $values["tgRekeningDetail"] =  MySQL::SQLValue($rdId, MySQL::SQLVALUE_NUMBER);

        $where["tgId"] =  MySQL::SQLValue($pTicketingGroepering, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_tg_ticketing_groepering", $values, $where);


    }


    // ========================================================================================
    //  Test of kasverschil te hoog (meer dan 5 % omzet tickets)
    //
    // In:  Ticketing Groepering ID
    //
    // Return: Te hoog?
    //
    // ========================================================================================

    static function ChkHoogKasverschil($pTicketingGroepering){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(Sx::GetClassPath("efin.class"));

        $sqlStat = "Select * from efin_tg_ticketing_groepering where tgId = $pTicketingGroepering";
        $db->Query($sqlStat);

        if (!$tgRec = $db->Row())
            return false;

        $kasVerschil = abs($tgRec->tgBedragKasverschil);

        if ($kasVerschil <= 5)
            return false;

        // -----------------
        // Max. 2 % van omzet
        // ------------------

        $maxKasverschil = ($tgRec->tgBedragTicketing / 50);

        if ($kasVerschil > $maxKasverschil)
            return true;
        else
            return false;


    }

    // -----------
    // Einde class
    // -----------

}

?>