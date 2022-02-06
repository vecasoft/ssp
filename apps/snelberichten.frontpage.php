<?php

// =====
// inits
// =====

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));


$titelBoven = 'SNELBERICHTEN';
$fotoPath = SX::GetSiteImg('snelberichten.frontpage.jpg');

// ========================
// get open "snelberichten"
// ========================

if ($parm1 == '*ALL') 
	$query = 'Select * from ssp_sb ' 
		   . 'Where sbDatumTot >= CURRENT_DATE  and sbDoelgroep <> "Vacature" '
		   . 'order by sbSort, sbId desc';

if (!$db->Query($query)) { 
  return $query;
}

if ( $db->RowCount() > 0) {

	// ====================
	// begin omvattende div
	// ====================

	echo '<div style="float: left; width: 99%; padding-left: 0px; padding-bottom: 1px; margin-bottom: 5px">';
	
	// =====
	// titel
	// =====

	echo '<div class="frontpage_header">';
		echo '<h2 style="color: white; margin: 0px; padding-bottom: 3px; padding-top: 3px; padding-left: 3px">' . $titelBoven . '</h2>';
	echo '</div>';
	
	echo '<div class="frontpage_border">';
	
	// ====
	// foto 
	// ====

	echo '<div style="float: left; padding: 0px; margin-top: 5px; margin-left: 3px; margin-right: 10px; margin-bottom: 0px">';
		echo $fotoPath;
	echo '</div>';	
	
	// =================================
	// Rechts van de foto: snelberichten
	// =================================

	if ($parm1 == '*VACATURES') 
		echo '<div style="float: left; width: 230px; padding-top: 0px; margin-bottom: 0px;">';
	else
		echo '<div style="float: left; width: 300px; padding-top: 0px; margin-bottom: 0px;">';
		
	
		$i = 0;	
		
		while ($sbRec = $db->Row()) { 
	
			$i++;
			
			$titel = strip_tags($sbRec->sbTitel);
			$titel = stripslashes($titel);
			// $tekst = strip_tags($sbRec->sbTekst);
			// $tekst = stripslashes($tekst);
			$tekst = $sbRec->sbTekst;

			echo '<div style="margin-top: 5px">';
			
				echo SX::getDotBlue();
				echo '&nbsp';
				echo '<b>'. $titel . '</b>';
				echo '&nbsp;';
				echo SX_tools::CrtTooltip(nl2br($tekst), '', 'Meer info');
			
			echo '</div>';

			
			
			
		}
	
		
	echo '</div>';
		
	echo '<div style="clear: both; height:5px">&nbsp;</div>';	
	echo '</div>';
	echo '</div>'; // Einde omvattende div

}

?>