<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));
include_once(Sx::GetClassPath("ploegen.class"));
include_once(Sx::GetClassPath("wedstrijden.class"));
include_once(Sx::GetClassPath("clubs.class"));

// -----
// inits
// -----

$today1 = strtotime('-6 hours');
$today2 = strtotime('tomorrow');

$actiefSeizoen = SSP_ploegen::GetSeizoen();

// -----------------
// Overzicht ploegen
// -----------------

$height = "1250px";

if ($JeugdSeniors == 'Seniors')
	$height = "250px";	

$path = "/ssp_ploegpaginas/ssp_vp_list.php?type=$JeugdSeniors&seizoen=$actiefSeizoen";
echo "<iframe src='$path' style='border: 1px grey; width: 1000px; height: $height'></iframe>"; 


?>