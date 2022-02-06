<!DOCTYPE HTML>
<html>

<?php

// -------		
// Classes
// -------    
 
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("sessions.class"));	
include_once(SX::GetSxClassPath("tools.class"));
include_once(Sx::GetClassPath("ploegen.class"));
include_once(Sx::GetClassPath("personen.class"));

$sessionId = SX_sessions::GetSessionId();
$userId = SX_sessions::GetSessionUserId($sessionId);


?>


<head>

<script>
document.title = 'Schelle Sport - Mijn info';
</script>

</head>

<body>

<?php

// ----------------------
// Enkel indien aangemeld
// ----------------------

if ($userId == '*NONE') {

	echo "<div style='color: red; margin-top: 20px'>U bent niet (meer) ingelogd...</div>";
	return;
	
}

// ----------------
// Get adres-record
// ----------------

$query = "Select * from ssp_ad where adCode = '$userId'";

if (!$db->Query($query)) { 
	echo '<br/>Unexpected error (1)';
 	return;
} 

if(! $adRec = $db->Row()) {
	echo '<br/>Geen gegevens gekoppeld aan deze login';
 	return;
} 

$adres = "&nbsp;";
if ($adRec->adAdres1 > " ")
	$adres = $adRec->adAdres1;
if ($adRec->adAdres2 > " ")
	$adres .= "<br/>" . $adRec->adAdres2;
if (($adRec->adPostnr > " ") or ($adRec->adGemeente > " "))
	$adres .= ", " . $adRec->adPostnr . " " . $adRec->adGemeente;
if ($adres > " " and $adRec->adLand > " " and substr($adRec->adLand,0,4) <> 'Belg')
	$adres .= "<br/>" . $adRec->adLand;

$mail = "&nbsp;";	
if ($adRec->adMail > " ") {
	$mail = $adRec->adMail;
	if ($adRec->adMoederMailBasis == 1 and $adRec->adVaderMailBasis != 1)
		$mail .= " (moeder)";
	if ($adRec->adMoederMailBasis != 1 and $adRec->adVaderMailBasis == 1)
		$mail .= " (vader)";	
	if ($adRec->adMoederMailBasis == 1 and $adRec->adVaderMailBasis == 1)
		$mail .= " (ouders)";
}		

$tel = "&nbsp;";	
if ($adRec->adTel > " ") {
	$tel = $adRec->adTel;
	if ($adRec->adMoederTelBasis == 1 and $adRec->adVaderTelBasis != 1)
		$tel .= " (moeder)";
	if ($adRec->adMoederTelBasis != 1 and $adRec->adVaderTelBasis == 1)
		$tel .= " (vader)";	
	if ($adRec->adMoederTelBasis == 1 and $adRec->adVaderTelBasis == 1)
		$tel .= " (ouders)";
}


$extraGegevens = false;

if ($adRec->adSpelerMail > " " or $adRec->adSpelerTel > " " or $adRec->adVaderNaam > " " or $adRec->adMoederNaam > " ") 
	$extraGegevens = true;

$voetbalGegevens = false;	
if ($adRec->adFunctieVB > " ") 
	$voetbalGegevens = true;


$sspGegevens = false;	
if ($adRec->adFunctieSSP > " ") 
	$sspGegevens = true;
	
$vaderNaam = $adRec->adVaderVoornaam . ' ' . $adRec->adVaderNaam;
$moederNaam = $adRec->adMoederVoornaam . ' ' . $adRec->adMoederNaam;

// Functies voetbal
$functieVB = SSP_personen::GetOmschrijvingFunctieVB($adRec->adFunctieVB);

// Functies schelle Sport
$functieSSP = SSP_personen::GetOmschrijvingFunctieSSP($adRec->adFunctieSSP);

$medewerkerType = SSP_personen::GetOmschrijvingMW($adRec->adMedewerkerType);

$geboorteDatum = '&nbsp;';
if ($adRec->adGeboorteDatum > ' ') {
	$geboorteDatum = SX_Tools::EdtDate($adRec->adGeboorteDatum, '%d %B %Y');
	if ($adRec->adGeboortePlaats > ' ')
		$geboorteDatum .= ', ' . $adRec->adGeboortePlaats;
}
	

$aansluitingsDatum = '&nbsp;';
if ($adRec->adAanslDatum > ' ') 
	$aansluitingsDatum = '(' . SX_Tools::EdtDate($adRec->adAanslDatum, '%d %B %Y') . ')';

$voetbalCat = '&nbsp;';

if ($adRec->adVoetbalCat > ' ')
	$voetbalCat =  $adRec->adVoetbalCat;	
	
if ($voetbalCat == 'G')
	$voetbalCat = "G-Team";
	
if ($voetbalCat == 'SEN')
	$voetbalCat = "Seniors";	

$lidGeld = $adRec->adLidgeldVoldaanVB;

if ($lidGeld == 'DEEL')
	$lidGeld = 'Gedeeltelijk';

if ($lidGeld == 'PROEF')
	$lidGeld = 'Proefperiode';
	
if ($adRec->adLidgeldTotaal	>  0) 
	$lidGeld .= " (€ $adRec->adLidgeldTotaal)";

	
// ------
// Header
// ------

echo "<div style='padding-left: 10px; margin-bottom: 10px'>";

echo "<h1>Mijn Gegevens</h1>";

echo "Je vindt hier uw gegevens - zoals geregistreerd in onze leden-database";
echo "<br/>";
echo "<b>Eventuele aanvullingen en/of verbeteringen kunnen via onderstaand formulier gemeld worden.</b>";
echo "<br/>";

if ($adRec->adOnvolledig == 1) 
	echo "<h1 style='color: red'>Uw gegevens zijn niet volledig, gelieve langs het secretariaat te gaan</h1>";


echo "<fieldset style='border: 1px solid #D2E3EA; padding: 5px; margin-top: 5px; float:left; margin-right: 10px'>";
echo "<legend style='color: #3F5DAA'>Basis contact-gegevens</legend>";

echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Naam</div><div style='float:left; ; margin-top: 10px; width: 500px'>$adRec->adVoornaam $adRec->adNaam</div>";
echo "<br style='clear:both'>";
echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Adres</div><div style='float:left; margin-top: 10px; width: 500px'>$adres</div>";
echo "<br style='clear:both'>";
echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Mail / Tel.</div><div style='float:left; margin-top: 10px; width: 500px'>$mail &nbsp; &nbsp; $tel</div>";


echo "</fieldset>";

if ($extraGegevens == true) {

	echo "<br style='clear: both'>";
	
	echo "<fieldset style='border: 1px solid #D2E3EA; padding: 5px; margin-top: 5px; float:left; margin-right: 10px'>";
	echo "<legend style='color: #3F5DAA'>Extra contact-gegevens (minderjarigen)</legend>";
	
	echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Speler Mail / Tel.</div><div style='float:left; ; margin-top: 10px; width: 500px'>$adRec->adSpelerMail &nbsp; &nbsp; $adRec->adSpelerTel </div>";
	echo "<br style='clear:both'>";

	echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Vader Naam</div><div style='float:left; ; margin-top: 10px; width: 500px'>$vaderNaam</div>";
	echo "<br style='clear:both'>";
	echo "<div style='float:left; width: 150px; color: grey;'>Vader Mail / Tel.</div><div style='float:left; width: 500px'>$adRec->adVaderMail &nbsp; &nbsp; $adRec->adVaderTel</div>";
	echo "<br style='clear:both'>";
	
	echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Moeder Naam</div><div style='float:left; ; margin-top: 10px; width: 500px'>$moederNaam</div>";
	echo "<br style='clear:both'>";
	echo "<div style='float:left; width: 150px; color: grey;'>Moeder Mail / Tel.</div><div style='float:left; width: 500px'>$adRec->adMoederMail &nbsp; &nbsp; $adRec->adMoederTel</div>";
	echo "<br style='clear:both'>";
	
	echo "</fieldset>";
}

 if ($voetbalGegevens == true) {
 
	echo "<br style='clear: both'>";
		
 	echo "<fieldset style='border: 1px solid #D2E3EA; padding: 5px; margin-top: 5px; float:left; margin-right: 10px'>";
	echo "<legend style='color: #3F5DAA'>Voetbal-gegevens</legend>";

	echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Functie(s) voetbal</div><div style='float:left; ; margin-top: 10px; width: 500px'>$functieVB</div>";
	echo "<br style='clear:both'>";
	
	echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Geboortedatum</div><div style='float:left; ; margin-top: 10px; width: 500px'>$geboorteDatum</div>";
	echo "<br style='clear:both'>";	
	
	echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Bondsnummer</div><div style='float:left; ; margin-top: 10px; width: 500px'>$adRec->adBondsNr $aansluitingsDatum</div>";
	echo "<br style='clear:both'>";	
	
	if ($adRec->adVoetbalCat > " ") {
		echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Categorie</div><div style='float:left; ; margin-top: 10px; width: 500px'>$voetbalCat</div>";
		echo "<br style='clear:both'>";	
	}

	
	if ($lidGeld <> 'NVT') {
		echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Lidgeld voldaan</div><div style='float:left; ; margin-top: 10px; width: 500px'>$lidGeld</div>";
		echo "<br style='clear:both'>";	
	}
	
	echo "</fieldset>";
 }
 
  if ($sspGegevens == true) {
 
	echo "<br style='clear: both'>";
		
 	echo "<fieldset style='border: 1px solid #D2E3EA; padding: 5px; margin-top: 5px; float:left; margin-right: 10px'>";
	echo "<legend style='color: #3F5DAA'>Schelle Sport-gegevens</legend>";
	
		echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Functie(s)</div><div style='float:left; ; margin-top: 10px; width: 500px'>$functieSSP</div>";
		echo "<br style='clear:both'>";
	
	if ($adRec->adMedewerkerType > ' ') {
		echo "<div style='float:left; width: 150px; color: grey; margin-top: 10px'>Medewerker van</div><div style='float:left; ; margin-top: 10px; width: 500px'>$medewerkerType</div>";
		echo "<br style='clear:both'>";
	}
	
	echo "</fieldset>";
 }	

echo '<br style="clear: both">';
echo "</div>"; 


echo "<div style='padding-left: 10px; min-height: 300px'><iframe frameborder=0 src='/ssp_aanpassen_data_formulier/ssp_ca_contactformulier_aanpassen_data_add.php?x=y&seid=$sessionId' style='width: 100%; height: 600px'></iframe></div>";

?>

</body>
</html>