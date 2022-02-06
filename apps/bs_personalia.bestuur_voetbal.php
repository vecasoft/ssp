<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Personalia - Bestuur Voetbal';
</script>

<?php

include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
include_once(SX::GetSxClassPath("sessions.class"));
include_once(SX::GetClassPath("personen.class"));

// -----------
// Get USER-id
// -----------

$sessionId = $_SESSION["SEID"];

$userId = SX_sessions::GetSessionUserId($sessionId);

?>

</head>

<body>

<?php

echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col" style="">';

echo " <h1>Personalia - Bestuur Voetbal</h1>";

// -------------
// Bestuursleden
// -------------

$htmlBestuur = SSP_personen::GetPersonaliaBestuurVoetbalHTML();

echo $htmlBestuur;


echo "</div></div></div>";

?>


</body>
</html>