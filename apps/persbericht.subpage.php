<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));

// ----------------------
// Get persbericht-record
// ----------------------

$query = "Select * from ssp_pb where pbId = " . $parm1;

if (!$db->Query($query)) { 
	echo 'Unexpected error';
 	return;
} 

if(! $pbRec = $db->Row()) {
	echo 'Persbericht niet gevonden...';
 	return;
} 

// ----------
// Page-title
// ----------

echo "<script> document.title = 'Schelle Sport Persbericht: $pbRec->pbTitel' </script>";


// ----------------
// Afbeelden "bron"
// ----------------

$datumE = SX_tools::EdtDate($pbRec->pbDatum, '%a %d %b %Y');

$tekst = 'IN DE PERS - Bron: '
        . $pbRec->pbBron
        . ' ('
        . $datumE
        . ')';

echo '<div style="text-align: center; font-weight:bold; padding-top: 10px; padding-bottom: 7px;background-color: #F8F2B4; color: #1F59AA">';
	echo $tekst;
echo '</div>';

echo '<div style="margin-left: 10px">'; // omvattende div

// ---------------
// Afbeelden titel
// ---------------

echo  "<h1>" . $pbRec->pbTitel . "</h1>";

// ---------------
// Afbeelden tekst
// ---------------

echo '<div style="padding-bottom: 10px">';
	echo $pbRec->pbTekst;
echo '</div>';

// --------------------
// Afbeelden Bijlage(n)
// --------------------

if ($pbRec->pbBijlage) {

	$bijlages = json_decode($pbRec->pbBijlage);
	$teksten[0] = $pbRec->pbTextBijlage1;
	$teksten[1] = $pbRec->pbTextBijlage2;
	
	$i = 0; 
  
	foreach ($bijlages as $bijlage) {
		
	// -------
	// Picture
	// -------
  
	if (strpos($bijlage->type, "image") !== FALSE) {
		
		echo '<div style="width: 800px; border: 1px solid; margin-top: 5px; margin-bottom: 10px">';
		echo '<img src="' . $bijlage->name. '">';
		  
		  
		if ($teksten[$i]) {
			echo '<div style="background-color: #D2E3EA; padding: 5px; font-weight:bold">';
			echo  $teksten[$i];
			echo '</div>';
		}

		echo '</div>';
	
	}
													   
	// -------------------------------
	// Geen picture (normaal PDF, ...)
	// -------------------------------
	
	if (strpos($bijlage->type, "image") === FALSE) {
	 
		$tekst = $teksten[$i];
		if (! $tekst) {
			$tekst =  $bijlage->usrName;
		}

		echo '<div style="margin-top: 5px; margin-bottom: 10px">'; 
		echo SX::getDotBlue();  
		echo '&nbsp;'; 
		echo '<a href="' . $bijlage->name . '" target="_blank">';
		echo $tekst; 
		echo '</a>';
		echo '</div>';
 	
	}
	  
	$i++;
  
  }
  
}

// -------------------------
// Afbeelden link (embedded)
// -------------------------

if ($pbRec->pbURL > ' ' ) {
 
	if ($pbRec->pbUrlHeight > 0) 
		$height = $pbRec->pbUrlHeight;
	else
		$height = 500;
    
	echo '<div style="margin-top: 20px; margin-bottom: 20px">';
 
		echo '<iframe type="text/html" width="790" height="' . $height . '"';
		echo 'src="http://' . $pbRec->pbURL . '"';
		echo '>';
		echo '</iframe>';
 
	echo '</div>';

}
      
// -------------------------
// Afbeelden YouTube filmpje
// -------------------------

if ($pbRec->pbYouTubeCode) {

	echo '<div style="margin-top: 5px; margin-bottom: 10px">';
  
		echo '<iframe class="youtube-player" type="text/html" width="640" height="385"';
		echo 'src="http://www.youtube.com/embed/';
		echo $pbRec->pbYouTubeCode;
		echo '" allowfullscreen frameborder="0">';
		echo '</iframe>';
	  
	echo '</div>';

} 

echo '</div>';


?>