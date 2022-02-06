<?php



include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
include_once(SX::GetClassPath("zomertornooi.class"));


$html = SSP_zomertornooi::GetAantalPlaatsenHTML();

echo $html;


?>