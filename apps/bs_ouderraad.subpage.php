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

<div class="container"><div class="row"><div class="col">

<h1>Ouderraad</h1>

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

	echo '<div class="jumbotron" style="margin-top: 10px; text-align: center; padding: 10px">';
	echo 'Contactformulier is enkel beschikbaar voor leden van Schelle Sport';
	echo '<br/>Gelieve aan te melden';
	echo '<br style="margin-bottom: 10px"/>';
    echo "<a href=\"#loginModal\" role=\"button\" class=\"btn btn-primary btn-lg\" data-toggle=\"modal\" data-backdrop=\"static\">Aanmelden</a>";
    echo "<br/><br/>Login of Wachtwoord vergeten? Klik <a href=\"/index.php?app=article_subpage&parm1=90&layout=full\">HIER</a><br/>";
	echo '</div>';

}
else {

    echo "<br/>";
    $path = "/ssp_co_ouderraad_contactformulier/ouderraad_contactformulier_add.php?seid=$sessionId";
    echo "<iframe src='$path' style='border: 1px grey; width: 100%; height: 2000px'></iframe>";

}
?>

</div></div></div>

</body>
</html>