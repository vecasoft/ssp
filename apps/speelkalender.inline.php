<style TYPE="text/css">

	.titel{
		font-style: Helvetica, Arial, sans-serif;
		font-size: 18px;
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #2DA3E7;
		margin: 0px:
		text-align: center;
		color: white;

	}

	.hoofding{
		font-style: Helvetica, Arial, sans-serif;
		font-size: 14px;
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
		font-style: Helvetica, Arial, sans-serif;
		font-size: 14px;
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		margin-top: 0px:
		margin-bottom: 0px;
		overflow: hidden;
	}

	.detail2{
		font-style: Helvetica, Arial, sans-serif;
		font-size: 14px;
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #FFFCCC;
		margin-top: 0px:
		margin-bottom: 0px;
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

$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

// -------
// Headers
// -------

if ($titel == NULL)	
	$titel = 'Speelkalender';

echo '<div class="titel" style="text-align: center; width: 781px; float: left">' .  $titel . '</div>';
echo '<br style="clear: both">';

echo '<div class="hoofding" style="width: 125px; float: left;font-weight: bold;">Heen</div>';
echo '<div class="hoofding" style="width: 75px; float: left;font-weight: bold;">Aanvang</div>';
echo '<div class="hoofding" style="width: 150px; float: left">&nbsp;</div>';
echo '<div class="hoofding" style="width: 10px; float: left">&nbsp</div>';
echo '<div class="hoofding" style="width: 150px; float: left">&nbsp</div>';
echo '<div class="hoofding" style="width: 125px; float: left;font-weight: bold;">Terug</div>';
echo '<div class="hoofding" style="width: 75px; float: left;font-weight: bold;">Aanvang</div>';

echo '<br style="clear: both">';
	
// ---------------------
// Overzicht wedstrijden
// ---------------------

$query = 'Select * from ssp_vw '  
       . 'where vwPloeg = ' . $ploeg . ' and vwType = "CW" and vwHeenTerug = "H" '
       . 'order by vwDatumTijd';

if (!$db->Query($query))	
	return;
	

while($vwRec = $db->Row()){

	// ----------------------
	// Terugwedstrijd ophalen
	// ----------------------
	
	$query2  = 'Select * from ssp_vw '  
			. 'where vwPloeg = ' . $ploeg . ' and vwType = "CW" and vwHeenTerug = "T" '
			. 'and vwClub = ' . $vwRec->vwClub . ' '
			. 'order by vwDatumTijd';
			
	$db2->Query($query2);
	
 
	$terugRec = $db2->Row();   


	if ($terugRec == NULL) {
		echo 'Query not successful...';
		echo '<br/>';
		echo $query2;
	}


	$datumTijd = strtotime( $vwRec->vwDatumTijd ); 
	$datumE = SX_tools::EdtDate($vwRec->vwDatumTijd,'%a %d %b %Y');
	$tijdE = SX_tools::EdtDate($vwRec->vwDatumTijd,'%H:%M');

	$datumTijd2 = strtotime( $terugRec->vwDatumTijd ); 
	$datumE2 = SX_tools::EdtDate($terugRec->vwDatumTijd, '%a %d %b %Y');
	$tijdE2 = SX_tools::EdtDate($terugRec->vwDatumTijd, '%H:%M');

	$tegenstander = $vwRec->vwTegenstander;

	if ($vwRec->vwClub > 0) {
			
		$tegenstander2 = SSP_clubs::GetSiteLink($vwRec->vwClub, $tegenstander);
		
		if ($tegenstander2 > ' ')
			$tegenstander = $tegenstander2;
		
	}

	$ploeg1 = 'Schelle';
	$ploeg2 = $tegenstander;

	if ($vwRec->vwUitThuis == 'U') {

		$ploeg2 = 'Schelle';
		$ploeg1 = $tegenstander;
	
	}

	$class = 'detail1';
	$class2 = $class;

	echo '<div class="' . $class2 . '" style="width: 125px; float: left">'. $datumE. '</div>';
	echo '<div class="' . $class2 . '" style="width: 75px; float: left">'. $tijdE . '</div>';
	echo '<div class="' . $class2 . '" style="width: 150px; float: left; text-align: center">'. $ploeg1 . '</div>';
	echo '<div class="' . $class2 . '" style="width: 10px; float: left; text-align: center">'. ' - '. '</div>';
	echo '<div class="' . $class2 . '" style="width: 150px; float: left; text-align: center">'. $ploeg2 . '</div>';
	echo '<div class="' . $class2 . '" style="width: 125px; float: left">'. $datumE2 . '</div>';
	echo '<div class="' . $class2 . '" style="width: 75px; float: left">'. $tijdE2 . '</div>';

	echo '<br style="clear: both">';
		
}
	

?>