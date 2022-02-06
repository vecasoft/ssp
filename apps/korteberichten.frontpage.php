<?php

// -------
// Classes
// -------

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));


$titelBoven = 'KORT NIEUWS';
$fotoPath = SX::GetSiteImg('korteberichten.frontpage.jpg');

// ---------------------
// Get "korte berichten"
// ---------------------

$query = 'Select * from ssp_kb ' 
	   . 'Where kbDatumTot >= CURRENT_DATE and kbActief = 1 '
       . 'order by kbSort, kbId desc';


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


		// ----------------------------------
		// Rechts van de foto: korteberichten
		// ----------------------------------



		$i = 0;

		while ($kbRec = $db->Row()) {

			$i++;

			if ($i > 1) 
				echo '<div style="Clear: both; padding-left: 5px; padding-top: 5px">';
			else
				echo '<div style="float: left; ; width: 227px; padding-top: 0px; margin-bottom: 0px;">';

				$titel = $kbRec->kbTitel;
				$titel = stripslashes($titel);

				$tekst = $kbRec->kbTekst;

				echo '<div style="margin-top: 5px">';

					echo '<h2 style="padding: 0px; margin-top: 0px; margin-bottom: 5px">' . $titel . '</h2>';
					
					echo $tekst;

					if ($kbRec->kbTekstOnder > ' ' && $i > 1)	
						echo $kbRec->kbTekstOnder;

				
				echo '</div>';

			echo '</div>';


			// Vervolgtekst 1ste bericht...
			if ($i == 1 && $kbRec->kbTekstOnder > ' ') {
				
				echo '<div style="Clear: both; padding-left: 5px; padding-top: 5px">';
				echo $kbRec->kbTekstOnder;
				echo '</div>';	
							

			}


		}

	echo '</div>'; // "End Border"
	
	
echo '</div>'; // End "Omvattende div"


?>