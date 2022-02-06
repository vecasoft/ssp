<?php
// We use SPOUT top read and create Excel files

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Helper;
use PhpOffice\PhpSpreadsheet\IOFactory;


class SSP_efin_rapporten
{ // define the class

    // ========================================================================================
    // Create XLSX
    //
    // In:	XLSX to be created
    //      Type = *DOWNLOAD, *FILE
    //      Applicatie = *EFIN, ...
    //
    //
    // ========================================================================================

    static function CreateXLSX($pSpreadsheet, $pType = '*DOWNLOAD', $pNaam) {



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

            $fileXLS = $_SERVER['DOCUMENT_ROOT'] . "/_files/efin/rapporten/$pNaam.xlsx";

            $writer = new Xlsx($pSpreadsheet);
            $writer->save($fileXLS);
        }

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

        error_log("111111");

        $raRec = SSP_db::Get_EFIN_raRec($pRapport);
        if (! $raRec)
            return;

        // -----------------------
        // Aanmaken Rapport (XLSX)
        // -----------------------

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $value1 = $raRec->raSelAlfa01;

        $sheet->setCellValue('A1', 'aaaaaaaaaaa');
        $sheet->setCellValue('A2', 'Hello World 2!');
        //$writer = new Xlsx($spreadsheet);
        //$writer->save('hello world.xlsx');

        $path = self::CreateXLSX($spreadsheet, '*FILE', 'test');
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
    // Aanmaken Rapport BTW1
    //
    // In:	Rapport ID
    //
    // Return: None
    // ========================================================================================

    static function RapportBTW1($pRapport) {

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

        $sqlStat    = "Select * from efin_ra_rapporten "
                    . "inner join efin_if_inkomende_facturen on ifFactuurdatum >=  raSelDate01 and ifFactuurdatum <=  raSelDate02 "
                    . "where raId = $pRapport";
        $db->Query($sqlStat);


        $cell = "A1";
        $sheet->setCellValue($cell, 'Leverancier');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "B1";
        $sheet->setCellValue($cell, 'Factuurnummer');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "C1";
        $sheet->setCellValue($cell, 'Factuurdatum');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "D1";
        $sheet->setCellValue($cell, 'BTW Incl.');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "E1";
        $sheet->setCellValue($cell, 'BTW Bedrag');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "F1";
        $sheet->setCellValue($cell, 'Maatstaf 1');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "G1";
        $sheet->setCellValue($cell, 'BTW Code 1');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $cell = "H1";
        $sheet->setCellValue($cell, 'BTW Bedrag 1');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $i = 1;

        while ($raRec = $db->Row()) {

            $i++;

            $lvRec = SSP_db::Get_EFIN_lvRec($raRec->ifLeverancier);

            $cell = "A" . $i;
            $sheet->setCellValue($cell, $lvRec->lvNaam);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            $cell = "B" . $i;
            $sheet->setCellValue($cell, $raRec->ifFactuurnummer);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            $cell = "C" . $i;
            $dateTime = DateTime::createFromFormat('Y-m-d', $raRec->ifFactuurdatum);
            $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateTime );
            $sheet->setCellValue($cell, $excelDateValue);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);

            $cell = "D" . $i;
            $sheet->setCellValue($cell, $raRec->ifBedrag);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

            $cell = "E" . $i;
            $sheet->setCellValue($cell, $raRec->ifBTW);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

            $cell = "F" . $i;
            $sheet->setCellValue($cell, $raRec->ifMaatstaf1);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

            $cell = "G" . $i;
            $sheet->setCellValue($cell, $raRec->ifBTWCode1);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            $cell = "H" . $i;
            $sheet->setCellValue($cell, $raRec->ifBTWBedrag1);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        }

        //$writer = new Xlsx($spreadsheet);
        //$writer->save('hello world.xlsx');

        $naam = "BTW1_$pRapport";

        $path = self::CreateXLSX($spreadsheet, '*FILE', $naam);
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




    // -----------
    // Einde class
    // -----------

}

?>