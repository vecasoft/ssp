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
include_once(Sx::GetClassPath("efin.class"));
include_once(SX::GetClassPath("_db.class"));

$json = SSP_efin::GetAnalytischeStructuurJSON();

// $json = "[{\"id\":1,\"text\":\"$naam\",\"children\":[{\"id\":2,\"text\":\"Child node 1\"},{\"id\":3,\"text\":\"Child node 2\"}]}]";


echo $json;

// echo '[{"id":1,"text":"Root node","children":[{"id":2,"text":"Child node 1"},{"id":3,"text":"Child node 2"}]}]';


?>
