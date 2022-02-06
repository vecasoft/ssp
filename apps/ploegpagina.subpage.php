
<?php


// -------		
// Classes
// -------    
    
include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("ploegen.class"));
include_once(SX::GetClassPath("wedstrijden.class"));
include_once(SX::GetClassPath("clubs.class"));
include_once(SX::GetClassPath("personen.class"));
include_once(SX::GetClassPath("settings.class"));


?>

<style>
h1, h1 a, h1 a:link, h1 a:visited, h1 a:hover
{
  margin: 0.67em 0;
  font-size: 20px;
  color: #1F59AA;
  font-weight: bold;
}
</style>

<?php

// -----
// inits
// -----

$today1 = strtotime('-6 hours');
$today2 = strtotime('tomorrow');
$eerstvolgend = FALSE;

$logoFB = SX::GetSiteImg('facebook.gif');

$nextWedstrijdId = SSP_ploegen::GetVolgendeWedstrijd($parm1, 4, TRUE);

// --------------
// Omvattende div
// --------------

echo '<div style="margin-left: 10px">';

// --------------------
// Afbeelden ploeg-info
// --------------------

$query = 'Select * from ssp_vp where vpId = ' . $parm1;

if (!$db->Query($query)) { 
  return $query;
}


if (! $vpRec = $db->Row()) {
	echo '*** ONVERWACHTE FOUT ***';
	return;
}

// ------------------
// Set document title
// ------------------

$docTitle = "Schelle Sport - Ploegpagina $vpRec->vpNaam";
echo ' <script> ';
echo "$(document).attr('title', '$docTitle');";
echo ' </script> ';

echo "<div style='float: left'>";
echo "<h1>$vpRec->vpNaam (Seizoen $vpRec->vpSeizoen)</h1>";
echo "</div>";

echo "<div style='float: right; padding-top: 10px; padding-right: 40px'>";
?>
  <div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" style='background-color: #2DA3E7' type="button" data-toggle="dropdown">Ga naar andere ploegpagina
    <span class="caret"></span></button>
    <ul class="dropdown-menu">
<?php

$seizoen = $vpRec->vpSeizoen;

$sqlStat = "Select * from ssp_vp where vpSeizoen = '$seizoen' and (vpJeugdSeniors = 'Jeugd' or vpJeugdSeniors = 'Seniors') order by vpSort Desc";
if ($db->Query($sqlStat)) { 

	while ($vpRec2 = $db->Row()){
		
		$url = "index.php?app=ploegpagina_subpage&parm1=$vpRec2->vpId&layout=full";
		echo "<li><a href='$url'>$vpRec2->vpNaam</a></li>";
			
		
	}


}

?>
    </ul>
  </div>
  
<?php
echo "</div>";

echo "<br style='clear: both'/>";

// -------------------------------
// Ophalen ploegfoto (klein/groot)
// -------------------------------

$fotoPath = '';
$fotoGrootPath = '';

$fotos = json_decode($vpRec->vpFoto);

if ($fotos) {
	foreach ($fotos as $foto) {

	  if (strpos($foto->type, "image") !== false)
		$fotoPath = $foto->name;

	}
}

$fotosGroot = json_decode($vpRec->vpFotoGroot);

if ($fotosGroot) {
	foreach ($fotosGroot as $fotoGroot) {

	  if (strpos($fotoGroot->type, "image") !== false)
		$fotoGrootPath = $fotoGroot->name;

	}
}

// -----------------------
// Versie ZONDER ploegfoto
// -----------------------

if (! $fotoPath) {

		$trainers = SSP_personen::GetNaam($vpRec->vpTrainer);
		
		if ($vpRec->vpTrainer2)
			$trainers .= ', ' . SSP_personen::GetNaam($vpRec->vpTrainer2);
		
		if ($vpRec->vpTrainer3)
			$trainers .= ', ' . SSP_personen::GetNaam($vpRec->vpTrainer3);
		
		if ($vpRec->vpTrainer4)
			$trainers .= ', ' . SSP_personen::GetNaam($vpRec->vpTrainer4);
		
		if ($vpRec->vpTrainer5)
			$trainers .= ', ' . SSP_personen::GetNaam($vpRec->vpTrainer5);

		$trainers = "Zie personalia (onder menu 'Voetbal')";
		   
		$afgevaardigden = SSP_personen::GetNaam($vpRec->vpDelege);		
		if ($vpRec->vpDelege2)
			$afgevaardigden .= ', ' . SSP_personen::GetNaam($vpRec->vpDelege2);

	
		echo '<div class="jumbotron" style="font-size: 14px; width: 915px; margin-left: 30px; margin-bottom: 5px; padding: 5px; padding-top: 0px; padding-left: 20px; padding-bottom: 10px">';

			echo "<br/><div style='float: left; width: 135px'><b>Trainer(s):</b></div>$trainers";
			echo "<br/><div style='float: left; width: 135px'><b>Afgevaardigde(n):</b></div>$afgevaardigden";
									
			if ($vpRec->vpInfo > ' '){
				echo '<br/><br/>';
				echo nl2br($vpRec->vpInfo);
			}

			if ($vpRec->vpLinkStand > ' ') {
				echo '<br/><br/>';
				echo 'Klik <a href="' . $vpRec->vpLinkStand . '" target="_blank">hier</a> voor de stand in hun reeks ' . $vpRec->vpReeks ;
			}
			
		echo '</div>';
	
}
		
// --------------------
// Versie MET ploegfoto
// --------------------

if ($fotoPath) {

	echo '<div style="float:left; padding-left: 28px; padding-right: 10px">';
		echo "<a href='$fotoGrootPath' target='_blank'><img style='width: 330px; border-radius: 6px;' src='$fotoPath'></a>";
	echo '</div>';

	echo "<div class='jumbotron' style='font-size: 14px; float: left; width: 580px; height: 229px; padding: 10px'>";
		
			echo '<div style="float: left; width: 70px">Trainer(s):</div>';
					
			echo SSP_personen::GetNaam($vpRec->vpTrainer);
	    	   
			if ($vpRec->vpTrainer2) {
				echo '<br/>';
				echo '<div style="float: left; width: 70px">&nbsp;</div>';
				echo  SSP_personen::GetNaam($vpRec->vpTrainer2);
			}
  	   
			if ($vpRec->vpTrainer3) {
				echo '<br/>';
				echo '<div style="float: left; width: 70px">&nbsp;</div>';
				echo  SSP_personen::GetNaam($vpRec->vpTrainer3);
			}
   	   
			if ($vpRec->vpTrainer4) {
				echo '<br/>';
				echo '<div style="float: left; width: 70px">&nbsp;</div>';
				echo  SSP_personen::GetNaam($vpRec->vpTrainer4);
			}
   	   
			if ($vpRec->vpTrainer5) {
				echo '<br/>';
				echo '<div style="float: left; width: 70px">&nbsp;</div>';
				echo  SSP_personen::GetNaam($vpRec->vpTrainer5);
			}
					
    		echo '<br/><br/>';
	
			echo '<div style="float: left; width: 135px">Afgevaardigden(n):</div>' . SSP_personen::GetNaam($vpRec->vpDelege);
 
  
			if ($vpRec->vpDelege2) {
				echo '<br/>';
				echo '<div style="float: left; width: 135px">&nbsp;</div>';
				echo SSP_personen::GetNaam($vpRec->vpDelege2);
			}
			
			if ($vpRec->vpInfo > ' '){
				echo '<br/><br/>';
				echo nl2br($vpRec->vpInfo);
			}

			if ($vpRec->vpLinkStand > ' ') {

				echo '<br/><br/>';
	
				echo 'Klik <a href="' . $vpRec->vpLinkStand . '" target="_blank">hier</a> voor de stand in hun reeks ' . $vpRec->vpReeks ;
	
			}
			
			
			if ($vpRec->vpLinkFB > " ") {
				echo '<br/><br/>';
				echo '<a href="' . $vpRec->vpLinkFB . '" target="_blank">' . $logoFB. '</a>';
			}

	echo '</div>';
}

// ---------------------
// Overzicht wedstrijden
// ---------------------
echo "<br/>";
$path = "/ssp_ploegpagina_wedstrijden/ssp_vw_list.php?ploeg=$parm1";
echo "<iframe src='$path' style='border: 1px grey; width: 1000px; height: 2000px'></iframe>"; 
  

// --------------------
// Einde omvattende div
// --------------------

echo '<br style="clear: both">';
echo '</div>';
  

?>
