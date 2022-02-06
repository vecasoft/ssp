<!DOCTYPE HTML>
<html>

<head>

<script>
    document.title = 'Schelle Sport - Ouderraad';
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

<h1>Ouderraad - Contactformulier</h1>

Binnen onze club willen we als <a target="_blank" href="https://schellesport.be/doc_openfile.php?code=u8eCH9a">ouderraad</a> een luisterend oor bieden en onze bijdrage leveren om mee te werken aan een positieve ervaring voor iedereen.
<br/>We adviseren hierin het bestuur en de betrokken afdelingen.
<br/><br/>
Wat wij echter <b>niet</b> behandelen zijn:<br/><br/>
<ul>
    <li>Vragen rond sportief beleid: daarvoor moet u persoonlijk contact opnemen met de trainers, co&#246;rdinators of TVJO van Schelle Sport.</li>
    <li>Vragen over de ledenwerking (inschrijvingen, aansluiting KBVB, enz): contacteer daarvoor het secretariaat van de club</li>
</ul><br/>
Met andere opmerkingen/vragen dan deze zo net vermeld, gaan we graag aan de slag. Deze worden besproken tijdens onze overlegmomenten.<br/>
Hierna zullen we als Ouderraad een antwoord terugsturen.
<br/><br/>
Het is uiteraard ook altijd mogelijk om de  <a target="_blank" href="https://schellesport.be/doc_openfile.php?code=u8eCH9a">leden</a> van de ouderraad persoonlijk aan te spreken op de club.


<?php

if ($userId == 'GUEST' or $userId == '*NONE'){

	echo '<div class="jumbotron" style="font-family: sans-serif; font-size: 14px; margin-top: 10px; text-align: center; width: 920px; margin-left: 30px; padding: 10px">';
	echo 'Contactformulier is enkel beschikbaar voor leden van Schelle Sport';
	echo '<br/><br/>Gelieve aan te melden';
	echo '<br/><br/>';
	echo '<button class="btn btn-success login"  href="./sx/apps/login.php"><span class="glyphicon glyphicon-log-in"></span> Aanmelden</button>';
    echo "<br/><br/>Login of Wachtwoord vergeten? Klik <a href=\"/index.php?app=article_subpage&parm1=90&layout=full\">HIER</a><br/>";
	echo '</div>';

}
else {

    echo "<br/>";
    $path = "/ssp_co_ouderraad_contactformulier/ouderraad_contactformulier_add.php?seid=$sessionId";
    echo "<iframe src='$path' style='border: 1px grey; width: 1000px; height: 2000px'></iframe>";

}
?>

</body>
</html>