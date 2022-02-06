<style TYPE="text/css">

	.titel{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #284D98;
		margin: 0px:
		text-align: center;
		color: white;

	}

	.hoofding{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #E3EEF2;
		margin: 0px:
		height: 14px;

	}

	.detail1{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		margin-top: 0px:
		margin-bottom: 0px;
		height: 14px;
		overflow: hidden;
	}

	.detail2{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #FFFCCC;
		margin-top: 0px:
		margin-bottom: 0px;
		height: 14px;
		overflow: hidden;

	}

	.afgelast{
		text-decoration: line-through;
	}
	

</style>

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
// Inits
// -----

$actiefSeizoen = SSP_settings::GetActiefSeizoen();	

// ---------
// Functions
// ---------

function dspWedstrijdInfo($ploeg1, $ploeg2, $doelp1, $doelp2, $tekst, $uitslagenIngegeven) {

	include_once(Sx::GetClassPath("wedstrijden.class"));
	include_once(SX::GetSxClassPath("tools.class"));

	$thuisploeg = SSP_clubs::GetSiteLink($ploeg1);
	$uitploeg = SSP_clubs::GetSiteLink($ploeg2);

	If ($uitslagenIngegeven == 1)
		$uitslag = $doelp1 . ' -  ' . $doelp2 ;
	else
		$uitslag = '&nbsp;';

	
	$class = 'detail1';
	if ($ploeg1 == 1 || $ploeg2 == 1) // Schelle Sport
		$class = 'detail2';

	$info = "&nbsp;";

	if ($tekst > " ")
		$info =  SX_tools::CrtTooltip($tekst, ' ' , 'info');


	echo '<div class="' . $class . '" style="width: 150px; float: left; overflow: hidden">' . $thuisploeg . '</div>';
	echo '<div class="' . $class . '" style="width: 20px; float: left; overflow: hidden;text-align: center">' . ' - ' . '</div>';
	echo '<div class="' . $class . '" style="width: 150px; float: left; overflow: hidden">' . $uitploeg . '</div>';
	echo '<div class="' . $class . '" style="width: 50px; float: left; overflow: hidden">' . $uitslag . ' ' . $info .  '</div>';
	
	echo '<br style="clear: both">';
}

// ------------------------------------------------------
// overzicht wedstrijden laatste "gepubliceerde" speeldag
// ------------------------------------------------------

$query = 'Select * from ssp_sd ' 
	   . 'Where sdSeizoen =  "' . $actiefSeizoen . '" '
	   . 'and sdPubliceren = 1 '
       . 'order by sdSpeeldag desc '
	   . 'limit 1';

if (! $db->Query($query))
		return '';

$class = 'detail1';

$i = 0;

while($sdRec = $db->Row()) {
   		
	// -----
	// Titel
	// -----

	$datumE = SX_tools::EdtDate($sdRec->sdDatum, '%a %d %b %Y');

	$titel = 'Speeldag: ' . $sdRec->sdSpeeldag . ' - '. $datumE ;

	echo '<br class="newline">';
	echo '<div class="titel" style="text-align: center; width: 405px; float: left">' . $titel . '</div>';

	// -------
	// Headers
	// -------

	echo '<br style="clear: both">';

	// -----------
	// Wedstrijden
	// -----------

	dspWedstrijdInfo($sdRec->sdW01P1, $sdRec->sdW01P2, $sdRec->sdW01D1, $sdRec->sdW01D2, $sdRec->sdW01TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW02P1, $sdRec->sdW02P2, $sdRec->sdW02D1, $sdRec->sdW02D2, $sdRec->sdW02TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW03P1, $sdRec->sdW03P2, $sdRec->sdW03D1, $sdRec->sdW03D2, $sdRec->sdW03TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW04P1, $sdRec->sdW04P2, $sdRec->sdW04D1, $sdRec->sdW04D2, $sdRec->sdW04TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW05P1, $sdRec->sdW05P2, $sdRec->sdW05D1, $sdRec->sdW05D2, $sdRec->sdW05TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW06P1, $sdRec->sdW06P2, $sdRec->sdW06D1, $sdRec->sdW06D2, $sdRec->sdW06TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW07P1, $sdRec->sdW07P2, $sdRec->sdW07D1, $sdRec->sdW07D2, $sdRec->sdW07TX, $sdRec->sdUitslagenIngegeven);

	dspWedstrijdInfo($sdRec->sdW08P1, $sdRec->sdW08P2, $sdRec->sdW08D1, $sdRec->sdW08D2, $sdRec->sdW08TX, $sdRec->sdUitslagenIngegeven);
}

	

?>