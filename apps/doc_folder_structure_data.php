<?php
session_start();


// -----
// inits
// -----

include_once $_SESSION["SX_ROOTDIR"] . '/sx.class.php';
include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
include_once(Sx::GetClassPath("doc.class"));
include_once(SX::GetClassPath("_db.class"));

$json = SSP_doc::GetFoldersJSON();

error_log($json);

// $json = "[{\"id\":1,\"text\":\"$naam\",\"children\":[{\"id\":2,\"text\":\"Child node 1\"},{\"id\":3,\"text\":\"Child node 2\"}]}]";


echo $json;

// echo '[{"id":1,"text":"Root node","children":[{"id":2,"text":"Child node 1"},{"id":3,"text":"Child node 2"}]}]';


?>
