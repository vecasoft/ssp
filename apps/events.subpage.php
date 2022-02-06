<style TYPE="text/css">

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

<script>
document.title = 'Schelle Sport - Events';
</script>


<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));

// -----
// Inits
// -----

$foto = SX::GetSiteImg('events.subpage.jpg');

// ----------
// Get events
// ----------

$query = 'Select * from  ssp_ev  '     
	   . 'where evEinddatum >= current_date() '	
	   . 'order by evEinddatum ';

if (!$db->Query($query)) { 
  return;
} 

// --------------
// Foto & headers
// --------------

echo '<h1>Evenementen</h1>';

echo '<div style="float: left; padding: 0px; margin-left: 0px; margin-right: 10px">';
	echo $foto;
echo '</div>';

echo '<div style="float: left;">';

	echo '<div class="hoofding" style="width: 170px; font-weight: bold;float: left">Datum</div>';
	echo '<div class="hoofding" style="width: 75px; font-weight: bold;float: left">Tijd</div>';
	echo '<div class="hoofding" style="width: 340px; font-weight: bold;float: left">Omschrijving</div>';
	echo '<br style="clear: both">';

	$class = ' ';											

	// -------
	// Records
	// -------

	while ($evRec = $db->Row()) { 
			 
		$height = '16px';

		// datum aanduiding...
		$datumE = SX_tools::EdtDate($evRec->evEinddatum, '%a %d %b %Y');

		if ($evRec->evStartdatum > '0000-00-00') {

		$datumE = SX_tools::EdtDate($evRec->evStartdatum, '%a %d %b %Y') . ' t/m </br>' .
					  SX_tools::EdtDate($evRec->evEinddatum, '%a %d %b %Y');
			$height = '25px';

		}


		if ($evRec->evDatumText > ' '){
			$datumE = $evRec->evDatumText;

			$height = '16px';
		}


		// omschrijving...
		$omschrijving = $evRec->evOmschrijving;
		
		if ($evRec->evExtURL > ' ')
			$omschrijving = "<a target=_blank class='discretelink' href='$evRec->evExtURL'>$omschrijving</a>";
		
		if ($evRec->evArtikel > 0) 
			$omschrijving = SX_content::getArticleLink($evRec->evArtikel,$evRec->evOmschrijving,'class="discretelink"'); 


		// tijd aanduiding...
		if ($evRec->evTijdText > ' ') {
				$tijd = $evRec->evTijdText;
		}
		else {
				$tijd = '&nbsp';
		}

		if (strlen($evRec->evTijdText) > 9)
			$height = '25px';
			
		$class = 'detail1';

		echo '<div class="'. $class . '" style="width: 170px; float: left; height: ' . $height . '">' . $datumE . '</div>';
		echo '<div class="'. $class . '" style="width: 75px; float: left; height: ' . $height . '"">' . $evRec->evTijdText . '</div>';
		echo '<div class="'. $class . '" style="width: 340px; float: left; height: ' . $height . '"">' . $omschrijving . '</div>';

		echo '<br class="newline">';


	   
	}

echo '</div>';

echo '<div style="clear: both; height: 1px">&nbsp;</div>';



?>