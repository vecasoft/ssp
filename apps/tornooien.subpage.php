<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Overzicht Tornooien';
$("meta[name='og:description']").attr('content', 'my new title');
</script>

<script>

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){
	
	var table = $('.datatable_tornooien').DataTable( {

	"order": [[ 1, 'asc' ]],
        "displayLength": 25,
		"sort": false,
		"paging": false,
		"stateSave": true,
		
	
		"language": {
               
			    "sProcessing": "Bezig...",
				"sLengthMenu": "_MENU_ resultaten weergeven",
				"sZeroRecords": "Geen resultaten gevonden",
				"sInfo": "_START_ tot _END_ van _TOTAL_ resultaten",
				"sInfoEmpty": "Geen resultaten om weer te geven",
				"sInfoFiltered": " (gefilterd uit _MAX_ resultaten)",
				"sInfoPostFix": "",
				"sSearch": "Zoeken:",
				"sEmptyTable": "Geen documenten gevonden",
				"sInfoThousands": ".",
				"sLoadingRecords": "Een moment geduld aub - bezig met laden...",
				"oPaginate": {
					"sFirst": "Eerste",
					"sLast": "Laatste",
					"sNext": "Volgende",
					"sPrevious": "Vorige"
						   
						}
			
	}

	} );
	
	$('input').focus();


	
})

</script>

</head>

<body>

<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));
include_once(Sx::GetClassPath("ploegen.class"));
include_once(Sx::GetClassPath("clubs.class"));

// ------
// Header
// ------

echo "<h1>Geplande Tornooien Jeugdploegen</h1>";

echo SX::GetSiteImg('tornooien.subpage.jpg');

// ----------------------------
// Get "Toekomstige" tornooien"
// ----------------------------

$sqlStat  = "SELECT * FROM ssp_cl_et "
          . "INNER JOIN sx_ta_tables ON taTable = 'VOETBAL_CAT' AND taCode = etVoetbalCat "
          . "WHERE date(etDatum)>= CURRENT_DATE - INTERVAL 7 DAY "
          . " and etStatus <> 'AFGELAST' and  etStatus <> 'FORFAIT' "
          . "ORDER BY taSort, etDatum";

             
if (!$db->Query($sqlStat)) { 
  return $sqlStat;
}
      
elseif ($db->RowCount() < 1)
  echo "Geen tornooien gepland"; 

else {


	echo '<br/>';

    // ------------------
    // Hoofding overzicht
    // ------------------
    
	echo '<table class="display cell-border datatable_tornooien" cellspacing="0" width="100%">';
   
		echo '<thead style="text-align: left;">';

			echo '<tr>';
				echo '<th>Cat.</th>';
				echo '<th>Ploeg</th>';
				echo '<th>Datum</th>';
				echo '<th>Club</th>';
				echo '<th>Extra Info</th>';
				echo '<th>Documenten</th>';
			echo '</tr>';

		echo '</thead>';
		
		while ($etRec = $db->Row()) { 
    
			echo '<tr>';
			
				// ---------
				// Categorie
				// ---------
				
				echo "<td>"; 
					if($etRec->etVoetbalCat <> 'G')
						echo $etRec->etVoetbalCat;
					else
						echo 'G-Team';
				echo "</td>"; 
				
				// -----
				// Ploeg
				// -----
         
				echo "<td>"; 
					if ($etRec->etPloeg == 0)
						echo "&nbsp;";      
					else 
						echo SSP_ploegen::GetNaam($etRec->etPloeg, '*NAAMKORT');
				echo "</td>"; 
				
				// -----
				// Datum
				// -----

				echo '<td style="width:50px">'; 
					echo SX_tools::EdtDate($etRec->etDatum, '%a %d/%m');
				echo "</td>";
				
				// ----
				// Club
				// ----
  
				echo "<td>"; 
					echo SSP_clubs::GetNaam($etRec->etClub, true);
				echo "</td>"; 
				
				// ----------
				// Extra info
				// ----------
	
				echo "<td>"; 
					echo $etRec->etTornooiInfo;
				echo "</td>";   
				
				// ----------
				// Documenten
				// ----------

				echo "<td>";
		  
					$documenten = $etRec->etDocumenten;
		  
					$docs = json_decode($documenten);
					$i = 0;
		  
					if ($docs) {
					
						foreach ($docs as $doc) {
			  
							if ($i > 0)
								echo '<br/>';
		
							echo '<a href="' . $doc->name . '"  target="_blank">' . $doc->usrName . '</a>';
			  
							$i++;
	  
						}
		  
					}	

				echo "</td>";
          
			echo "</tr>";
   
		}
  
	echo "</table>";
      
}       
 
$db->Close();  
                   
?>

</body>
</html>