<?php
session_start();


if (! $_SESSION["SX_BASEPATH"]) {
    $rootDir = (substr($_SERVER["SCRIPT_FILENAME"], 0, (stripos($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"]) + 0)));
    $_SESSION["SX_BASEPATH"] = $rootDir . "\"";
}

// -----
// inits
// -----

include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';
include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

include_once(SX::GetClassPath("efin.class"));

$array = json_decode($_POST["myData"]);

$sort = 10;
$sequence = "0";

foreach($array as $data) {

    $analytischeRekening = $data->id;
    $moeder = $data->parent;

    if ($moeder == '#')
        $moeder = 0;

    $values = array();
    $where = array();

    $values["arSort"] =  MySQL::SQLValue($sort, MySQL::SQLVALUE_NUMBER);
    $values["arMoeder"] =  MySQL::SQLValue($moeder, MySQL::SQLVALUE_NUMBER);

    $where["arId"] =  MySQL::SQLValue($analytischeRekening, MySQL::SQLVALUE_NUMBER);

    $db->UpdateRows("efin_ar_analytische_rekeningen", $values, $where);

    $sort += 10;

}

SSP_efin::SetAnalytischeRekeningenLevel();
SSP_efin::SetAnalytischeRekeningenRoot();

$sqlStat = "Select * from efin_ar_analytische_rekeningen where arRecStatus = 'A' order by arSort";
$db->Query($sqlStat);

$sequence = "0";

while ($arRec = $db->Row()){

    $id = $arRec->arId;
    $level = $arRec->arLevel;

    $sequence = SSP_efin::GetNxtAnaSeq($sequence, $level);
    $values = array();
    $where = array();

    $values["arSequence"] =  MySQL::SQLValue($sequence, MySQL::SQLVALUE_TEXT);

    $where["arId"] =  MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);

    $db2->UpdateRows("efin_ar_analytische_rekeningen", $values, $where);

}


echo "Wijzigen opgeslagen";

?>
