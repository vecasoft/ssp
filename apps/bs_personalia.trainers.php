<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Personalia - Trainers en Afgevaardigden';
</script>

<?php

include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
include_once(SX::GetSxClassPath("sessions.class"));
include_once(SX::GetClassPath("personen.class"));
include_once(SX::GetSxClassPath("bs4.class"));

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

echo " <h1>Personalia - Trainers en Afgevaardigden</h1>";

if ($userId == 'GUEST' or $userId == '*NONE'){

    echo '<div class="jumbotron" style="margin-top: 10px; text-align: center; padding: 10px">';
    echo 'Deze gegevens zijn enkel beschikbaar voor leden van Schelle Sport';
    echo '<br/><br/>';
    echo "<a href=\"#loginModal\" role=\"button\" class=\"btn btn-primary btn-lg\" data-toggle=\"modal\" data-backdrop=\"static\">Aanmelden</a>";
    echo '</div>';

}
else
    echo "<br/>";

// -------------
// Trainers
// -------------

if ($userId != 'GUEST' and $userId != '*NONE') {

    $htmlTrainers = SSP_personen::GetPersonaliaTrainersHTML();
    $htmlAfgevaardigden = SSP_personen::GetPersonaliaAfgevaardigdenHTML();

    $tabHeaders = array();
    $tabHeaders[] = 'Trainers';
    $tabHeaders[] = 'Afgevaardigden';

    $tabContents = array();
    $tabContents[] = $htmlTrainers;
    $tabContents[] = $htmlAfgevaardigden;

    echo SX_bs4::GetTabHtml('trainers_afgevaardigden',$tabHeaders, $tabContents, '');

}


// echo $htmlTrainers;


echo "</div></div></div>";

?>


</body>
</html>