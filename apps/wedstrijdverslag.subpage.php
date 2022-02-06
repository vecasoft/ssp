<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));
include_once(Sx::GetClassPath("ploegen.class"));
include_once(Sx::GetClassPath("wedstrijden.class"));

// -----
// inits
// -----

$today1 = strtotime('-6 hours');
$today2 = strtotime('tomorrow');

$actiefSeizoen = SSP_ploegen::GetSeizoen();

// ----------------
// Teller bijwerken
// ----------------

$query = 'Update ssp_vw set vwLeesTeller = vwLeesTeller + 1 where vwId = ' . $parm1;
$db->Query($query);

// ---------------
// Get "wedstrijd"
// ---------------

$query = 'Select * from ssp_vw where vwId = ' . $parm1;

if (!$db->Query($query))
	return;
         
$vwRec = $db->Row(); 
     
// ---------------
// Afbeelden titel
// ---------------

$linkPloegpagina = SSP_ploegen::GetPloegPaginaLink($vwRec->vwPloeg, 'hier');
$ploegNaam = SSP_ploegen::GetNaam($vwRec->vwPloeg);

echo '<h1>Wedstrijdverslag: ' . $ploegNaam . '</h1>';

echo '<script>';
echo "document.title = 'Schelle Sport - Wedstrijdverslag: $ploegNaam'";
echo '</script>';

echo '<br style="clear: both">';

echo '<div style="width: 100%; background-color: #FFFCCC; padding-top: 7px; padding-bottom: 5px; padding-left: 5px; margin-bottom: 0px">';
	echo 'Wedstrijd: ' . SSP_wedstrijden::GetWedstrijdInfoString($parm1);
	echo '<br/>';
	echo 'Klik ' . $linkPloegpagina . ' voor de ploegpagina van de ' . $ploegNaam;
echo '</div>';

// -----------------
// Afbeelden verslag
// -----------------

echo '<div style="padding-top: 0px; margin-top: 0px; padding-left: 5px; padding-right: 5px; padding-bottom: 5px;">';
	echo '<h2 style="margin-top: 0px; margin-bottom: 5px; padding-bottom: 3px">' . $vwRec->vwVerslagTitel . '</h2>';
	echo nl2br($vwRec->vwVerslagLang);
echo '</div>';

// -------------------------
// Afbeelden YouTube filmpje
// -------------------------

if ($vwRec->vwYouTubeCode) {

  echo '<div style="margin-top: 5px; margin-bottom: 10px">';
  
	  echo '<iframe class="youtube-player" type="text/html" width="640" height="385"';
	  echo 'src="https://www.youtube.com/embed/';
	  echo $vwRec->vwYouTubeCode;
	  echo '" allowfullscreen frameborder="0">';
	  echo '</iframe>';
  
  echo '</div>';

} 

// ----------------
// Afbeelden foto's
// ----------------


if ($vwRec->vwFoto) {

  $bijlages = json_decode($vwRec->vwFoto);
  $onderschrift[0] = $vwRec->vwFotoText;
  $onderschrift[1] = $vwRec->vwFotoText2;
  $onderschrift[2] = $vwRec->vwFotoText3;
  $i = 0; 
  
  foreach ($bijlages as $bijlage) {
         
    // -------
    // Picture
    // -------
  
    if (strpos($bijlage->type, "image") !== false) {
        
      echo '<div style="padding: 0px; width: 800px; border: 1px solid; margin-top: 5px; margin-bottom: 10px">';
		  
		  echo '<img style="margin: 0px; padding: 0px;" src="' . $bijlage->name. '">';
		  		  
		  if ($onderschrift[$i]) {
			echo '<div style="background-color: #D2E3EA; padding: 5px; font-weight:bold">';
				echo $onderschrift[$i];
			echo '</div>';
		  }
      
 
      echo '</div>';
       
       
    }
    
    $i++;
 
  }
}                                 


?>