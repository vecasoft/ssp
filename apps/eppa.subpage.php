<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Persoonlijke pagina';
</script>

<?php

echo '<link rel="stylesheet" href="' . $_SESSION["SX_BASEDIR"] . '/bootstrap/css/bootstrap_extract.css" />';

include_once(SX::GetSxClassPath("sessions.class"));

// -----------
// Get USER-id
// -----------

$sessionId = $_SESSION["SEID"];

$userId = SX_sessions::GetSessionUserId($sessionId);

?>

</head>

<body>

<?php

$path = "/eppa_overzicht/persoonlijke_pagina_list.php?seid=$sessionId	";

echo "<br/>";

echo "<iframe src='$path' style='border: 1px grey; width: 1000px; height: 2000px'></iframe>"; 

?>

</body>
</html>