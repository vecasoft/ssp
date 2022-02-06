<?php
// We use SPOUT top read and create Excel files

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Helper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Classes\Name as testnaam;


class SSP_efin_report
{ // define the class

    // ========================================================================================
    // TEST
    //
    // In:	Uploaded file
    //
    // Return: # lijnen opgeladen
    // ========================================================================================

    static function test(){

        include_once($_SESSION["SX_BASEPATH"] . '/vendor/autoload.php');

        $name = new testnaam;

        echo $name->get();


    }
    // ========================================================================================
    // Ophalen Analytische Structuur JSON voor Pivot
    //
    // In:
    //
    // Return: JSON string
    // ========================================================================================

    static function GetAnalytischeStructuurJSON(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ----------------------
        // Start JSON (Structuur)
        // ----------------------

        $json = "[{
            \"RekeningL1\": {
                type: \"level\",
                hierarchy: \"Analytische Rekening\"
            },
            \"RekeningL2\": {
                type: \"level\",
                hierarchy: \"Analytische Rekening\",
                level: \"Detail\",
                parent: \"RekeningL1\",
            },
            \"RekeningL3\": {
                type: \"level\",
                hierarchy: \"Analytische Rekening\",
                level: \"Detail level 2\",
                parent: \"Detail\"
            },
            \"bedrag_2022\": {
                type: \"number\",
                caption:\"Bedrag [2022]\"
            },
            \"budget_2022\": {
                type: \"number\",
                caption:\"Budget [2022]\"
            },
            \"bedrag_2021\": {
                type: \"number\",
                caption:\"Bedrag [2021]\"
            },
             \"bedrag_2020\": {
                type: \"number\",
                caption:\"Bedrag [2020]\"
            },          
        },";

        // ----
        // DATA
        // ----

        $struct = self::GetAnalytischeStructuurArray();

        foreach ($struct as $detail){

            $rekeningL1 = $detail['rekeningL1'];
            $rekeningL2 = $detail['rekeningL2'];
            $rekeningL3 = $detail['rekeningL3'];
            $bedrag2022 = $detail['bedrag_2022'];
            $budget2022 = $detail['budget_2022'];
            $bedrag2021 = $detail['bedrag_2021'];
            $bedrag2020 = $detail['bedrag_2020'];

            $json .= " {
                \"RekeningL1\": \"$rekeningL1\",
                \"RekeningL2\": \"$rekeningL2\",
                \"RekeningL3\": \"$rekeningL3\",
                \"bedrag_2022\": $bedrag2022,
                \"budget_2022\": $budget2022,
                \"bedrag_2021\": $bedrag2021,
                \"bedrag_2020\": $bedrag2020, 
            },";


        }

         // ----------
        // Einde JSON
        // ----------

        $json .= "]";


        // -------------
        // Einde functie
        // -------------

        return $json;

    }

    // ========================================================================================
    // Ophalen Analytische Structuur array
    //
    // In:
    //
    // Return: array
    // ========================================================================================

    static function GetAnalytischeStructuurArray(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $struct = array();

        $sqlStat = "Select * from efin_ar_analytische_rekeningen where arRecStatus = 'A' order by arSequence, arLevel";

        $db->Query($sqlStat);

        while ($arRec = $db->Row()) {

            $analytischeRekening = $arRec->arId;
            $levels = self::GetAnalytischeLevels($analytischeRekening);

            if (! $levels)
                continue;

            $bedrag2022 = 0;
            $bedrag2021 = 0;
            $bedrag2020 = 0;

            $budget2022 = 0;

            $sqlStat = "Select * from efin_as_analytische_rekening_saldi where asAnalytischeRekening = $analytischeRekening";

            $db2->Query($sqlStat);

            while ($asRec = $db2->Row()) {

                if ($asRec->asPeriode == '2022') {
                    $bedrag2022 += $asRec->asBedragPlusSpecifiek;
                    $bedrag2022 -= $asRec->asBedragMinSpecifiek;
                }
                if ($asRec->asPeriode == '2021') {
                    $bedrag2021 += $asRec->asBedragPlusSpecifiek;
                    $bedrag2021 -= $asRec->asBedragMinSpecifiek;
                }
                if ($asRec->asPeriode == '2020') {
                    $bedrag2020 += $asRec->asBedragPlusSpecifiek;
                    $bedrag2020 -= $asRec->asBedragMinSpecifiek;
                }
            }


            $detail = array();
            $detail['rekeningL1'] = $levels[0];
            $detail['rekeningL2'] = $levels[1];
            $detail['rekeningL3'] = $levels[2];

            // Bedragen
            $detail['bedrag_2022'] = $bedrag2022;
            $detail['bedrag_2021'] = $bedrag2021;
            $detail['bedrag_2020'] = $bedrag2020;


            // Budget (enkel huidig jaar)
            $sqlStat = "Select * from efin_ab_analytische_rekening_budgetten where abAnalytischeRekening = $analytischeRekening and abPeriode = '2022'";
            $db2->Query($sqlStat);

            if ($abRec = $db2->Row()){

                if ($abRec->abLinkType == '*SPECIFIEK')
                    $budget2022 = $abRec->abBudget;
            }

            $detail['budget_2022'] = $budget2022;

            $struct[] = $detail;


        }

        // -------------
        // Einde functie
        // -------------

        return $struct;

    }

    // ========================================================================================
    // Ophalen "analytsiche levels"
    //
    // In:	Analytische Rekening
    //
    // Return: Array met level 1,2,3
    // ========================================================================================

    static function GetAnalytischeLevels($pAnalytischeRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $levels = array();
        $levels[0] = null;
        $levels[1] = null;
        $levels[2] = null;

        $arRec = SSP_db::Get_EFIN_arRec($pAnalytischeRekening);

        $naam = utf8_encode($arRec->arNaam);

        if (! $arRec)
            return null;

        if ($arRec->arLevel == 0) {
            $levels[0] = $arRec->arSequence . " " . $naam;
            return $levels;
        }

        if ($arRec->arLevel == 1){

            $levels[1] = $arRec->arSequence . " " . $naam;

            $arRec = SSP_db::Get_EFIN_arRec($arRec->arMoeder);
            $naam = utf8_encode($arRec->arNaam);
            $levels[0] = $arRec->arSequence . " " . $naam;

            return $levels;

        }

        if ($arRec->arLevel == 2){

            $levels[2] = $arRec->arSequence . " " . $naam;

            $arRec = SSP_db::Get_EFIN_arRec($arRec->arMoeder);
            $naam = utf8_encode($arRec->arNaam);
            $levels[1] = $arRec->arSequence . " " . $naam;

            $arRec = SSP_db::Get_EFIN_arRec($arRec->arMoeder);
            $naam = utf8_encode($arRec->arNaam);
            $levels[0] = $arRec->arSequence . " " . $naam;

            return $levels;

        }

        // -------------
        // Einde functie
        // -------------

        return null;

    }

    // ========================================================================================
    // Aanmaken XLS - TEST
    //
    // In:	Uploaded file
    //
    // Return: Path created file
    // ========================================================================================

    static function TestXLS() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once($_SESSION["SX_BASEPATH"] . '/vendor/autoload.php');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Hello World !');
        $sheet->setCellValue('A2', 'Hello World 2!');
        //$writer = new Xlsx($spreadsheet);
        //$writer->save('hello world.xlsx');


        $fileXLS = self::CreateXLSX($spreadsheet, '*FILE', '*EFIN');


        // -------------
        // Einde functie
        // -------------

        return $fileXLS;

    }


    // ========================================================================================
    // Aanmaken Rapport TEST
    //
    // In:	Rapport ID
    //
    // Return: None
    // ========================================================================================

    static function RapportTEST($pRapport) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once($_SESSION["SX_BASEPATH"] . '/vendor/autoload.php');


        $raRec = SSP_db::Get_EFIN_raRec($pRapport);
        if (! $raRec)
            return;

        // -----------------------
        // Aanmaken Rapport (XLSX)
        // -----------------------

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $value1 = $raRec->raSelAlfa01;

        $sheet->setCellValue('A1', $value1);
        $sheet->setCellValue('A2', 'Hello World 2!');
        //$writer = new Xlsx($spreadsheet);
        //$writer->save('hello world.xlsx');

        $path = self::CreateXLSX($spreadsheet, '*FILE', '*EFIN');
        $url  = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);

        // ---------------------
        // Update RAPPORT-record
        // ---------------------

        $values = array();
        $where = array();

        $values["raPath"] =  MySQL::SQLValue($path);
        $values["raURL"] =  MySQL::SQLValue($url);

        $where["raId"] =  MySQL::SQLValue($pRapport, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("efin_ra_rapporten", $values, $where);

        // -------------
        // Einde functie
        // -------------

        return;

    }

        // ========================================================================================
        // Create XLSX
        //
        // In:	XLSX to be created
        //      Type = *DOWNLOAD, *FILE
        //      Applicatie = *EFIN, ...
        //
        //
        // ========================================================================================

        static function CreateXLSX($pSpreadsheet, $pType = '*DOWNLOAD', $pApp = '*EFIN') {



            include_once($_SESSION["SX_BASEPATH"] . '/vendor/autoload.php');


            if ($pType == '*DOWNLOAD') {

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="01simple.xlsx"');
                header('Cache-Control: max-age=0');
                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header('Pragma: public'); // HTTP/1.0


                // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer = new Xlsx($pSpreadsheet);
                $writer->save('php://output');
            }

            if ($pType == '*FILE') {

                $fileXLS = $_SERVER['DOCUMENT_ROOT'] . "/_files/efin/rapporten/testxls.xlsx";

                $writer = new Xlsx($pSpreadsheet);
                $writer->save($fileXLS);
            }

            // -------------
            // Einde functie
            // -------------

            return $fileXLS;


        }

    // -----------
    // Einde class
    // -----------

}

?>