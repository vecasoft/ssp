<?php

// We use SPOUT top read and create Excel files

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class SSP_efin_interface_epra
{ // define the class


    // ========================================================================================
    //  Ophalen uitbetaling-voorstel-detail op basis van rekening-detail
    //
    // In:	Rekening Detail-ID
    //
    // Uit: Persoon Code
    //      Ventilatie-rekening ID
    //
    // Return: uitbetaling-voorstel-detail (record)
    //
    //
    // ========================================================================================

    static function FindUitbetalingVoorstelDetail($pRekeningDetail, &$pPersoon, &$pVentilatie) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -------------------------
        // Init uitgaande parameters
        // -------------------------

        $pPersoon = null;
        $pVentilatie = null;

        // -----------------------
        // Ophalen nodige gegevens
        // -----------------------

        $rdRec = SSP_db::Get_EFIN_rdRec($pRekeningDetail);

        if (!$rdRec)
            return null;

        // ------------------------
        // Enkel negatieve bedragen
        // ------------------------

        if ($rdRec->rdBedrag >= 0)
            return null;

        // ------------------------------------------------------------
        // zoeken naar betaalvoorstel detail op basis van bedrag & iban
        // ------------------------------------------------------------

        $bedrag = abs($rdRec->rdBedrag);
        $bankrekening = $rdRec->rdIBAN;

        $sqlStat    = "Select * from epra_ud_uitbetaling_voorstel_detail "
                    . "Inner join epra_uv_uitbetaling_voorstel on uvId = udVoorstel and date(uvDatumCreatie) > DATE_SUB(CURDATE(), INTERVAL 29 DAY) "
                    . "where udBedragBetaald = $bedrag and REPLACE(udBankrekening, ' ', '') = REPLACE('$bankrekening',' ','') and (udEFINrd = 0 or udEFINrd = $pRekeningDetail) "
                    . "Order By udVoorstel Desc Limit 1";
;
        $db->Query($sqlStat);

        // echo $sqlStat;

        if ($udRec = $db->Row()) {

            $rekening = $udRec->udRekening;

            $rhRec = SSP_db::Get_EPRA_rhRec($rekening);
            $pPersoon = $rhRec->rhPersoon;

            //$sqlStat = "Select * from epra_ra_rekening_afspraken where raRekening = $rekening and raRecStatus = 'A' order by raDatumTot desc limit 1";
            //$db->Query($sqlStat);

            //if ($raRec = $db->Row())
            //    $pVentilatie = $raRec->raVentilatieRekening;

            $pVentilatie = $rhRec->rhVentilatieRekening;

            return $udRec;

        }

        // -------------
        // Einde functie
        // -------------

        return null;

    }

    // ========================================================================================
    // Registratie boeking in EFIN
    //
    // In:	Betaalvoorstel-detail
    //      EFIN Rekening detail

    // ========================================================================================

    static function RegBoekingEFIN($pVoorstelDetail, $pRekeningDetail){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // -----------------------------------
        // Registratie in betaalvoorsteldetail
        // -----------------------------------

        $sqlStat = "Update epra_ud_uitbetaling_voorstel_detail set udEFINrd = $pRekeningDetail where udId = $pVoorstelDetail";
        $db->Query($sqlStat);

        $sqlStat = "Update epra_rd_rekening_detail set rdEFINrd = $pRekeningDetail where rdVoorstelDetail = $pVoorstelDetail";
        $db->Query($sqlStat);

    }

    // -----------
    // Einde class
    // -----------

}

?>