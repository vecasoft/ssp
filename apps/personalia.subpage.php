<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Personalia';
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

if ($userId == 'GUEST' or $userId == '*NONE'){
	
	echo '<div class="jumbotron" style="font-family: sans-serif; font-size: 14px; margin-top: 10px; text-align: center; width: 920px; margin-left: 30px; padding: 10px">';
	echo 'Info trainers & afgevaardigen is enkel beschikbaar voor leden van Schelle Sport';
	echo '<br/><br/>Om alle gegevens te kunnen zien, gelieve aan te melden';
	echo '<br/><br/>';
	echo '<button class="btn btn-success login"  href="./sx/apps/login.php"><span class="glyphicon glyphicon-log-in"></span> Aanmelden</button>';
	echo '</div>';

}
else
	echo "<br/>";

$path = "/ssp_ploegpagina_wedstrijden/ssp_vw_list.php?ploeg=$parm1";
$path = "/ssp_personalia/menu.php?seid=$sessionId	";


echo "<iframe src='$path' style='border: 1px grey; width: 1000px; height: 2500px'></iframe>"; 

?>

</body>
</html>