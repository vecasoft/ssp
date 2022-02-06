<?php

// -----
// Inits
// -----

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("settings.class"));

$titelBoven = 'IN DE PERS';
$fotoPath = SX::GetSiteImg('persberichten.frontpage.jpg');

// -----------------------------
// Get frontpage "persberichten"
// -----------------------------

$query = 'Select * from  ssp_pb  '     
	   . 'where pbActief = 1 and pbFrontPage = 1 and pbDatumFrontTot >= current_date() '	
	   . 'order by pbSort, pbDatum desc, pbId desc';

if (!$db->Query($query)) 
  return;


if ( $db->RowCount() <= 0)
	return;


// --------------------
// Begin omvattende div
// --------------------

echo '<div style="float: left; width: 99%; padding-left: 0px; padding-bottom: 1px; margin-bottom: 5px">';

	// -----
	// Titel
	// -----

	echo '<div class="frontpage_header">';
		echo '<h2 style="color: white; margin: 0px; padding-bottom: 3px; padding-top: 3px; padding-left: 3px">' . $titelBoven . '</h2>';
	echo '</div>';

	// -----
	// Border
	// -----
	
	
	echo '<div class="frontpage_border">';

		// ----
		// Foto 
		// ----

		echo '<div style="float: left; padding: 0px; margin-top: 5px; margin-left: 3px; margin-right: 10px; margin-bottom: 0px">';
			echo $fotoPath;
		echo '</div>';	

		// ------------------------------------------
		// Rechts van de foto: Eerste 3 persberichten
		// ------------------------------------------

		$i = 0;	
		
		echo '<div style="float: left; width: 230px; padding-top: 0px; margin-bottom: 0px;">';

			while ($pbRec = $db->Row()) {
			
				$i++;
  
				if ($i > 3)
					break;

				$URL = SSP_settings::GetLink('PERSBERICHT', $pbRec->pbTitel, 'class=discretelink', $pbRec->pbId );

				$datumE = SX_tools::EdtDate($pbRec->pbDatum,'%a %d %b %Y');
  
				echo '<div style="margin-top: 5px">';
					echo SX::getDotBlue();
					echo '&nbsp';
					echo $URL;
				echo '</div>';
  
			  echo '<div style="padding-left: 11px">';
				echo  $pbRec->pbBron;
				echo  ' - ';
				echo  $datumE;
			  echo '</div>';
			
			
			}
		
			// Link naar "Alle persberichten"
			
			if ($db->RowCount() <= 3)  {
			
				$URL = SSP_settings::GetLink('PERSBERICHTEN','MEER persberichten', 'class=discretelink');

				echo '<div style="margin-top: 5px">';
					echo SX::getDotBlue();
					echo '&nbsp';
					echo $URL;
				echo '</div>';
			
			}
			
			
		
		echo '</div>'; // Rechts naast foto
				
		// -----------------------------------
		// Overige persberichten onder de foto
		// -----------------------------------

		if ($db->RowCount() > 3) {
		
			echo '<div style="clear: both; padding-top: 10px; padding-left: 5px">';
			
				$db->Query($query);
				$i = 0;
				
				while ($pbRec = $db->Row()){
				
					$i++;
					
					if ($i <= 3) 
						continue;
			 
					$URL = SSP_settings::GetLink('PERSBERICHT', $pbRec->pbTitel, 'class=discretelink', $pbRec->pbId );
			  
					$datumE = SX_tools::EdtDate($pbRec->pbDatum,'%a %d %b %Y');
				
					echo '<div style="margin-top: 5px">';
						echo SX::getDotBlue();
						echo '&nbsp';
						echo $URL;
					echo '</div>';
				
					echo '<div style="padding-left: 11px">';
						echo  $pbRec->pbBron;
						echo  ' - ';
						echo  $datumE;
					echo '</div>';
				
				}


				// Link naar "Alle persberichten"
				
				if ($i >= 3)  {
				
					$URL = SSP_settings::GetLink('PERSBERICHTEN','MEER persberichten', 'class=discretelink');

					echo '<div style="margin-top: 5px">';
						echo SX::getDotBlue();
						echo '&nbsp';
						echo $URL;
					echo '</div>';
				
				} 


			echo '</div>'; // Onder de foto
		
		}
		
		echo '&nbsp;';

	echo '</div>'; // "End Border"
	
	
echo '</div>'; // End "Omvattende div"



?>