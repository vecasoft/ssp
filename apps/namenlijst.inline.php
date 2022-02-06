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
		border: 1px solid #E5E5E5;
		margin-top: 0px:
		margin-bottom: 0px;
		overflow: hidden;
	}

	.detail2{
		padding-left: 5px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		border: 1px solid #E5E5E5;
		background-color: #E3EEF2;
		margin-top: 0px:
		margin-bottom: 0px;
		overflow: hidden;
	}
	
	.newline{
		height: 0px;
		padding: 0px;
		margin: 0px;
		clear: both;
	}
  
</style>

<?php

// -----
// inits
// -----

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("personen.class"));

// ---------------
// Get header-info
// ---------------

$query = 'Select * from  ssp_nc where ncCode  = "'. $categorie . '"';

if (!$db->Query($query)) {
	echo 'ERROR: ' . $query;
	return;
}

if (!$ncRec = $db->Row() ){  
	echo 'ERROR: ' . $query;
	return;
}

// --------
// Get Rows
// --------

$query = 'Select * from  ssp_nl where nlCat  = "'. $categorie . '" order by nlSort';

if (!$db->Query($query)) {
	echo 'ERROR: ' . $query;
	return;
}

// -------
// Headers
// -------

if ($ncRec->ncHeader > ' ') {

	echo '<div class="titel" style="text-align: center; width: 743px; float: left">' . $ncRec->ncHeader . '</div>';
	echo '<br style="clear: both">';
}

echo '<div class="hoofding" style="width: 230px;font-weight: bold; float: left">Functie</div>';
echo '<div class="hoofding" style="width: 130px;font-weight: bold; float: left">Naam</div>';
echo '<div class="hoofding" style="width: 125px;font-weight: bold; float: left">Tel</div>';
echo '<div class="hoofding" style="width: 225px;font-weight: bold; float: left">Mail</div>';
echo '<br style="clear: both">';

$class = ' ';											

// -------
// Records
// -------

while($nlRec = $db->Row()) {

	$class = 'detail1';

	$tel = "&nbsp;";
	
	if ($nlRec->nlHideTel != 1)
		$tel = SSP_personen::GetTel($nlRec->nlPersoon);

	$mail = "&nbsp;";

	if ($nlRec->nlHideMail != 1)
		$mail = SSP_personen::GetMail($nlRec->nlPersoon);


	echo '<div class="'. $class . '" style="width: 230px; float: left">' . $nlRec->nlOmschrijving . '</div>';
	echo '<div class="'. $class . '" style="width: 130px; float: left">' . SSP_personen::GetNaam($nlRec->nlPersoon) . '</div>';
	echo '<div class="'. $class . '" style="width: 125px; float: left">' . $tel . '</div>';
	echo '<div class="'. $class . '" style="width: 225px; float: left">' . $mail . '</div>';

	echo '<br style="clear: both">';

}

?>