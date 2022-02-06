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

// =======================
// overzicht rangschikking
// =======================

$query = 'Select * from ssp_sr limit 1';

if (! $db->Query($query))
	return '';

$class = 'detail1';

$i = 0;

while($srRec = $db->Row()) {
   	
	// -----
	// Titel
	// -----

	$datumE = SX_tools::EdtDate($srRec->srDatum, '%a %d %b %Y');
	$tabelTitel = 'Rangschikking na: ' . $datumE;


	echo '<br style="clear: both">';
	echo '<div class="titel" style="text-align: center; width: 355px; float: left">' . $tabelTitel . '</div>';

	
	// =======
	// headers
	// =======

	echo '<br style="clear: both">';

	echo '<div class="hoofding" style="width: 20px; float: left; font-weight: bold; text-align: left">&nbsp;</div>';
	echo '<div class="hoofding" style="width: 200px; float: left; font-weight: bold; text-align: left">Ploeg</div>';
	echo '<div class="hoofding" style="width: 50px; float: left; font-weight: bold;text-align: center">Wedstr.</div>';
	echo '<div class="hoofding" style="width: 50px; float: left; font-weight: bold;text-align: center">Punten</div>';

	echo '<br style="clear: both">';
	
	$volgnr = 0;

	$ploeg01 = SSP_clubs::GetSiteLink($srRec->srPL01);
	$aantal01 = $srRec->srAW01;
	$punten01 = $srRec->srPT01;
	$class01 = 'detail1';
	if ($srRec->srPL01 == 1) {	
		$class01 = 'detail2';
		$ploeg01 = SSP_clubs::GetNaam($srRec->srPL01);
	}

	$ploeg02 = SSP_clubs::GetSiteLink($srRec->srPL02);
	$aantal02 = $srRec->srAW02;
	$punten02 = $srRec->srPT02;
	$class02 = 'detail1';
	if ($srRec->srPL02 == 1) {	
		$class02 = 'detail2';
		$ploeg02 = SSP_clubs::GetNaam($srRec->srPL02);
	}

	$ploeg03 = SSP_clubs::GetSiteLink($srRec->srPL03);
	$aantal03 = $srRec->srAW03;
	$punten03 = $srRec->srPT03;
	$class03 = 'detail1';
	if ($srRec->srPL03 == 1) {	
		$class03 = 'detail2';
		$ploeg03 = SSP_clubs::GetNaam($srRec->srPL03);
	}

	$ploeg04 = SSP_clubs::GetSiteLink($srRec->srPL04);
	$aantal04 = $srRec->srAW04;
	$punten04 = $srRec->srPT04;
	$class04 = 'detail1';
	if ($srRec->srPL04 == 1) {	
		$class04 = 'detail2';
		$ploeg04 = SSP_clubs::GetNaam($srRec->srPL04);
	}

	$ploeg05 = SSP_clubs::GetSiteLink($srRec->srPL05);
	$aantal05 = $srRec->srAW05;
	$punten05 = $srRec->srPT05;
	$class05 = 'detail1';
	if ($srRec->srPL05 == 1) {	
		$class05 = 'detail2';
		$ploeg05 = SSP_clubs::GetNaam($srRec->srPL05);
	}

	$ploeg06 = SSP_clubs::GetSiteLink($srRec->srPL06);
	$aantal06 = $srRec->srAW06;
	$punten06 = $srRec->srPT06;
	$class06 = 'detail1';
	if ($srRec->srPL06 == 1) {	
		$class06 = 'detail2';
		$ploeg06 = SSP_clubs::GetNaam($srRec->srPL06);
	}


	$ploeg07 = SSP_clubs::GetSiteLink($srRec->srPL07);
	$aantal07 = $srRec->srAW07;
	$punten07 = $srRec->srPT07;
	$class07 = 'detail1';
	if ($srRec->srPL07 == 1) {	
		$class07 = 'detail2';
		$ploeg07 = SSP_clubs::GetNaam($srRec->srPL07);
	}

	$ploeg08 = SSP_clubs::GetSiteLink($srRec->srPL08);
	$aantal08 = $srRec->srAW08;
	$punten08 = $srRec->srPT08;
	$class08 = 'detail1';
	if ($srRec->srPL08 == 1) {	
		$class08 = 'detail2';
		$ploeg08 = SSP_clubs::GetNaam($srRec->srPL08);
	}

	$ploeg09 = SSP_clubs::GetSiteLink($srRec->srPL09);
	$aantal09 = $srRec->srAW09;
	$punten09 = $srRec->srPT09;
	$class09 = 'detail1';
	if ($srRec->srPL09 == 1) {	
		$class09 = 'detail2';
		$ploeg09 = SSP_clubs::GetNaam($srRec->srPL09);
	}

	$ploeg10 = SSP_clubs::GetSiteLink($srRec->srPL10);
	$aantal10 = $srRec->srAW10;
	$punten10 = $srRec->srPT10;
	$class10 = 'detail1';
	if ($srRec->srPL10 == 1) {	
		$class10 = 'detail2';
		$ploeg10 = SSP_clubs::GetNaam($srRec->srPL10);
	}

	$ploeg11 = SSP_clubs::GetSiteLink($srRec->srPL11);
	$aantal11 = $srRec->srAW11;
	$punten11 = $srRec->srPT11;
	$class11 = 'detail1';
	if ($srRec->srPL11 == 1) {	
		$class11 = 'detail2';
		$ploeg11 = SSP_clubs::GetNaam($srRec->srPL11);
	}

	$ploeg12 = SSP_clubs::GetSiteLink($srRec->srPL12);
	$aantal12 = $srRec->srAW12;
	$punten12 = $srRec->srPT12;
	$class12 = 'detail1';
	if ($srRec->srPL12 == 1) {	
		$class12 = 'detail2';
		$ploeg12 = SSP_clubs::GetNaam($srRec->srPL12);
	}

	$ploeg13 = SSP_clubs::GetSiteLink($srRec->srPL13);
	$aantal13 = $srRec->srAW13;
	$punten13 = $srRec->srPT13;
	$class13 = 'detail1';
	if ($srRec->srPL13 == 1) {	
		$class13 = 'detail2';
		$ploeg13 = SSP_clubs::GetNaam($srRec->srPL13);
	}

	$ploeg14 = SSP_clubs::GetSiteLink($srRec->srPL14);
	$aantal14 = $srRec->srAW14;
	$punten14 = $srRec->srPT14;
	$class14 = 'detail1';
	if ($srRec->srPL14 == 1) {	
		$class14 = 'detail2';
		$ploeg14 = SSP_clubs::GetNaam($srRec->srPL14);
	}

	$ploeg15 = SSP_clubs::GetSiteLink($srRec->srPL15);
	$aantal15 = $srRec->srAW15;
	$punten15 = $srRec->srPT15;
	$class15 = 'detail1';
	if ($srRec->srPL15 == 1) {	
		$class15 = 'detail2';
		$ploeg15 = SSP_clubs::GetNaam($srRec->srPL15);
	}

	$ploeg16 = SSP_clubs::GetSiteLink($srRec->srPL16);
	$aantal16 = $srRec->srAW16;
	$punten16 = $srRec->srPT16;
	$class16 = 'detail1';
	if ($srRec->srPL16 == 1) {	
		$class16 = 'detail2';
		$ploeg16 = SSP_clubs::GetNaam($srRec->srPL16);
	}

	$ploeg17 = SSP_clubs::GetSiteLink($srRec->srPL17);
	$aantal17 = $srRec->srAW17;
	$punten17 = $srRec->srPT17;
	$class17 = 'detail1';
	if ($srRec->srPL17 == 1) {	
		$class17 = 'detail2';
		$ploeg17 = SSP_clubs::GetNaam($srRec->srPL17);
	}
	
	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class01 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg01 . '</div>';
	echo '<div class="' . $class01 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal01 . '</div>';
	echo '<div class="' . $class01 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten01 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class02 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg02 . '</div>';
	echo '<div class="' . $class02 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal02 . '</div>';
	echo '<div class="' . $class02 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten02 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class03 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg03 . '</div>';
	echo '<div class="' . $class03 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal03 . '</div>';
	echo '<div class="' . $class03 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten03 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class04 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg04 . '</div>';
	echo '<div class="' . $class04 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal04 . '</div>';
	echo '<div class="' . $class04 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten04 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class05 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg05 . '</div>';
	echo '<div class="' . $class05 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal05 . '</div>';
	echo '<div class="' . $class05 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten05 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class06 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg06 . '</div>';
	echo '<div class="' . $class06 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal06 . '</div>';
	echo '<div class="' . $class06 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten06 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class07 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg07 . '</div>';
	echo '<div class="' . $class07 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal07 . '</div>';
	echo '<div class="' . $class07 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten07 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class08 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg08 . '</div>';
	echo '<div class="' . $class08 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal08 . '</div>';
	echo '<div class="' . $class08 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten08 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class09 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg09 . '</div>';
	echo '<div class="' . $class09 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal09 . '</div>';
	echo '<div class="' . $class09 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten09 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class10 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg10 . '</div>';
	echo '<div class="' . $class10 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal10 . '</div>';
	echo '<div class="' . $class10 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten10 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class11 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg11 . '</div>';
	echo '<div class="' . $class11 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal11 . '</div>';
	echo '<div class="' . $class11 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten11 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class12 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg12 . '</div>';
	echo '<div class="' . $class12 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal12 . '</div>';
	echo '<div class="' . $class12 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten12 . '</div>';
	echo '<br style="clear: both">';
	
	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . 
	$volgnr . '</div>';
	echo '<div class="' . $class13 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg13 . '</div>';
	echo '<div class="' . $class13 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal13 . '</div>';
	echo '<div class="' . $class13 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten13 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class14 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg14 . '</div>';
	echo '<div class="' . $class14 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal14 . '</div>';
	echo '<div class="' . $class14 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten14 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class15 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg15 . '</div>';
	echo '<div class="' . $class15 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal15 . '</div>';
	echo '<div class="' . $class15 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten15 . '</div>';
	echo '<br style="clear: both">';

	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class16 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg16 . '</div>';
	echo '<div class="' . $class16 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal16 . '</div>';
	echo '<div class="' . $class16 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten16 . '</div>';
	echo '<br style="clear: both">';
	
	$volgnr++;
	echo '<div class="' . $class01 . '" style="width: 20px; float: left; overflow: hidden">' . $volgnr . '</div>';
	echo '<div class="' . $class17 . '" style="width: 200px; float: left; overflow: hidden">' . $ploeg17 . '</div>';
	echo '<div class="' . $class17 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $aantal17 . '</div>';
	echo '<div class="' . $class17 . '" style="width: 50px; float: left; overflow: hidden;text-align: center">' . $punten17 . '</div>';
	echo '<br style="clear: both">';
}

?>