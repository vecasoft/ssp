<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Personalia - Sportieve Staf';
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

echo " <h1>Personalia - Sportieve Staf</h1>";

if ($userId == 'GUEST' or $userId == '*NONE'){

    echo '<div class="jumbotron" style="margin-top: 10px; text-align: center; padding: 10px">';
    echo 'Volledige contactgegevens zijn enkel beschikbaar voor leden van Schelle Sport';
    echo '<br/><br/>Om alle gegevens te kunnen zien, gelieve aan te melden';
    echo '<br/><br/>';
    echo "<a href=\"#loginModal\" role=\"button\" class=\"btn btn-primary btn-lg\" data-toggle=\"modal\" data-backdrop=\"static\">Aanmelden</a>";
    echo '</div>';

}
else
    echo "<br/>";

// -------------
// Bestuursleden
// -------------

$dataLevel = "*VOLLEDIG";
if ($userId == 'GUEST' or $userId == '*NONE')
    $dataLevel = "*BEPERKT";

$html = SSP_personen::GetPersonaliaSportieveStafHTML($dataLevel);

echo $html;


echo "</div></div></div>";

?>


</body>
</html>