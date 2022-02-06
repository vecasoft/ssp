<?php

// -------
// Classes
// -------

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("content.class"));

// ----------
// Get events
// ----------

$query = 'Select * from  ssp_ev  '     
	   . 'where evEinddatum >= current_date() '	
	   . 'order by evEinddatum ';

if (!$db->Query($query))  
  return;

if ($db->RowCount() <= 0)
	return;


// --------------------
// begin omvattende div
// --------------------

echo '<div style="float: left; width: 99%; padding-left: 0px; padding-bottom: 1px; margin-bottom: 5px">';

	// -----
	// titel
	// -----
		
	echo '<div class="frontpage_header">';
		echo '<h2 style="color: white; margin: 0px; padding-bottom: 3px; padding-top: 3px; padding-left: 3px">' . 'EVENTS' . '</h2>';
	echo '</div>';
	
	echo '<div class="frontpage_border">';

	// ----
	// foto 
	// ---

	echo '<div style="float: left; padding: 0px; margin-top: 5px; margin-left: 3px; margin-right: 10px; margin-bottom: 0px">';
		echo SX::GetSiteImg('events.frontpage.jpg');
	echo '</div>';

	// ------------------------------------
	// Rechts van de foto: komende 3 events
	// ------------------------------------

	$query = 'Select * from  ssp_ev  '     
		   . 'where evEinddatum >= current_date() '	
		   . 'order by evEinddatum ';

	if (!$db->Query($query))  
	  return;

	if ($db->RowCount() <= 0)
		return;
		
	echo '<div style="float: left; width: 230px; padding-top: 0px; margin-bottom: 0px;">';

	$i = 0;

	while ($evRec = $db->Row()) { 
		
		$i++;

		if ($i > 3)
			break;

		// datum aanduiding...
		$datumE = SX_tools::EdtDate($evRec->evEinddatum, '%a %d %b %Y');
		
		if ($evRec->evStartdatum > '0000-00-00 00:00:00') {

			$datumE = SX_tools::EdtDate($evRec->evStartdatum, '%a %d %b %Y') . ' t/m ' .
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



		echo '<div style="margin-top: 5px">';
		echo SX::getDotBlue();
		echo '&nbsp';
		echo '<b>' . $omschrijving . '</b>';
		echo '<br/>';
		echo $datumE . '&nbsp;' . $tijd;
		echo '</div>';

	}

	echo '<br/>';
	echo 'Klik <a target="_top" class="discretelink" href="index.php?app=events_subpage">hier</a>
	 voor een overzicht van ALLE toekomstige evenementen.';


	echo '</div>';

// --------------------
// Einde omvattende div
// --------------------

echo '<div style="clear: both; height:5px">&nbsp;</div>';	
echo '</div>';
echo '</div>'; // omvattende div



?>