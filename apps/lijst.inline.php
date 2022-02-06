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

	}

	.detail1{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border-top: 1px solid #E5E5E5;
		border-left: 1px solid #E5E5E5;
		border-right: 1px solid #E5E5E5;
		margin: 0px:
		overflow: hidden;
	}

	.detail1_noline{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border-top: 1px solid #FFFFFF;
		border-left: 1px solid #E5E5E5;
		border-right: 1px solid #E5E5E5;
		margin: 0px:
		overflow: hidden;
	}

	.detail1_last{
		border-bottom: 1px solid #E5E5E5;
	}


	.detail2{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #E3EEF2;
		margin: 0px:
		overflow: hidden;
	}
	
</style>

<?php

// -----
// inits
// -----

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));

// ---------------
// Get header-info
// ---------------

$query = 'Select * from  ssp_lh_lijstheaders  '     
	   . ' where lhLijst  = "'. $lijstheader . '"';

if (!$db->Query($query)) {
	echo 'ERROR: ' . $query;
	return;
}

if (!$lhRec = $db->Row() ){  
	echo 'ERROR: ' . $query;
	return;
}

// -------
// headers
// -------

if ($lhRec->lhHeader0 > ' ') {

	echo '<div class="titel" style="text-align: center; width: ' .  $lhRec->lhBreedte0 . 'px; float: left">' . $lhRec->lhHeader0 . '</div>';

}

echo '<br style="clear: both">';


if ($lhRec->lhHeader1 > ' ' or $lhRec->lhHeader2 > ' ' or $lhRec->lhHeader3 > ' ') {


	if  ($lhRec->lhBreedte1 > 0) {
		echo '<div class="hoofding" style="width: ' .  $lhRec->lhBreedte1 . 'px; float: left;font-weight: bold;">' . $lhRec->lhHeader1 . '</div>';
	}

	if  ($lhRec->lhBreedte2 > 0) {
		echo '<div class="hoofding" style="width: ' .  $lhRec->lhBreedte2 . 'px; float: left;font-weight: bold;">' . $lhRec->lhHeader2 . '</div>';
	}

	if  ($lhRec->lhBreedte3 > 0) {
		echo '<div class="hoofding" style="width: ' .  $lhRec->lhBreedte3 . 'px; float: left;font-weight: bold;">' . $lhRec->lhHeader3 . '</div>';
	}

	if  ($lhRec->lhBreedte4 > 0) {
		echo '<div class="hoofding" style="width: ' .  $lhRec->lhBreedte4 . 'px; float: left;font-weight: bold;">' . $lhRec->lhHeader4	 . '</div>';
	}

	if  ($lhRec->lhBreedte5 > 0) {
		echo '<div class="hoofding" style="width: ' .  $lhRec->lhBreedte5 . 'px; float: left;font-weight: bold;">' . $lhRec->lhHeader5	 . '</div>';
	}

echo '<br style="clear: both">';

}


// ------
// detail
// ------

$query = 'Select * from  ssp_ld_lijstdetail '     
	   . ' where ldLijst  = "'. $lijstheader . '" '
	   . ' order by ldSort';

if (!$db->Query($query)) {
	echo 'ERROR: ' . $query;
	return;
}

// -------
// records
// -------

$recQty = $db->RowCount();
$i = 0;

while ($ldRec = $db->Row()) {
	 
	$class = 'detail1';

	if ($ldRec->ldLijnBoven == 0) 
		$class = 'detail1_noline';

	$i++;

	if ($i == $recQty)
		$class = $class . ' detail1_last';
	
	if  ($lhRec->lhBreedte1 > 0) {
	echo '<div class="' . $class . '" style="width: ' .  $lhRec->lhBreedte1 . 'px; float: left">' . $ldRec->ldDetail1 . '</div>';
	}
	
	if  ($lhRec->lhBreedte2 > 0) {
	echo '<div class="' . $class . '" style="width: ' .  $lhRec->lhBreedte2 . 'px; float: left">' . $ldRec->ldDetail2 . '</div>';
	}
	
	if  ($lhRec->lhBreedte3 > 0) {
	echo '<div class="' . $class . '" style="width: ' .  $lhRec->lhBreedte3. 'px; float: left">' . $ldRec->ldDetail3 . '</div>';
	}

		
	if  ($lhRec->lhBreedte4 > 0) {
	echo '<div class="' . $class . '" style="width: ' .  $lhRec->lhBreedte4. 'px; float: left">' . $ldRec->ldDetail4 . '</div>';
	}

	if  ($lhRec->lhBreedte5 > 0) {
	echo '<div class="' . $class . '" style="width: ' .  $lhRec->lhBreedte5. 'px; float: left">' . $ldRec->ldDetail5 . '</div>';
	}

	echo '<br style="clear: both">';

   
	}




echo '<br style="clear: both">';



?>