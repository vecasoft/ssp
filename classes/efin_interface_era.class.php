<?php

// We use SPOUT top read and create Excel files

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class SSP_efin_interface_era
{ // define the class


    // ========================================================================================
    // Aanmaken diverse prestatie
    //
    // In:  Persoon
    //      Prestatie type
    //      Datum
    //      Aantal eenheden
    //      Omschrijving
    //
    // Uit: Diverse prestatie ID (0 indien niet gelukt)
    //
    // ========================================================================================

    static function CrtDiversePrestatie($pPersoon, $pAantal, $pType, $pDatum, $pOmschrijving = null, $pUser = null){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ------------------------------
        // Bepalen eenheid & omschrijving
        // ------------------------------

        $sqlStat = "Select * from era_dt_diverse_prestatie_types where dtCode = '$pType'";
        $db->Query($sqlStat);

        if (! $dtRec = $db->Row())
            return 0;

        $eenheid = $dtRec->dtEenheid;

        if ($pOmschrijving)
            $omschrijving = $pOmschrijving;
        else
            $omschrijving = $dtRec->dtDefaultOmschrijving;

        // ----------------------------------
        // Creatie "diverse prestatie" record
        // ----------------------------------

        $values = array();

        $curDateTime = date('Y-m-d H:i:s');

        $user = $pUser;
        if (! $user)
            $user = $pPersoon;


        $values["dpPersoon"] = MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);
        $values["dpPrestatieType"] = MySQL::SQLValue($pType, MySQL::SQLVALUE_TEXT);
        $values["dpDatum"] = MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["dpAantalEenheden"] = MySQL::SQLValue($pAantal, MySQL::SQLVALUE_NUMBER);
        $values["dpEenheid"] = MySQL::SQLValue($eenheid, MySQL::SQLVALUE_TEXT);
        $values["dpOmschrijving"] = MySQL::SQLValue($omschrijving, MySQL::SQLVALUE_TEXT);

        $values["dpDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["dpUserCreatie"] = MySQL::SQLValue($user, MySQL::SQLVALUE_TEXT);
        $values["dpDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["dpUserUpdate"] = MySQL::SQLValue($user, MySQL::SQLVALUE_TEXT);

        $id = $db->InsertRow("era_dp_diverse_prestaties", $values);

        // -------------
        // Einde functie
        // -------------

        return $id;

    }



    // -----------
    // Einde class
    // -----------

}

?>