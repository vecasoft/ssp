<?php
session_start();

// -----
// inits
// -----

include_once $_SESSION["SX_ROOTDIR"] . '/sx.class.php';
include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

$array = json_decode($_POST["myData"]);

$sort = 10;

foreach($array as $data) {

    $folder = $data->id;
    $mother = $data->parent;

    if ($mother == '#')
        $mother = 0;


    $values = array();
    $where = array();

    $values["fdSort"] =  MySQL::SQLValue($sort, MySQL::SQLVALUE_NUMBER);
    $values["fdMother"] =  MySQL::SQLValue($mother, MySQL::SQLVALUE_NUMBER);

    $where["fdId"] =  MySQL::SQLValue($folder, MySQL::SQLVALUE_NUMBER);

    $db->UpdateRows("doc_fd_folders", $values, $where);

    $sort += 10;

}

echo "Wijzigen opgeslagen";

?>
