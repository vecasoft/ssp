<script>
document.title = 'Schelle Sport - Persberichten';

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){
	
	var table = $('.datatable_persberichten').DataTable( {

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

<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));

// -----
// Inits
// -----

$headerFoto = SX::GetSiteImg('persberichten.subpage.jpg');

// ----------
// Get events
// ----------

$query = 'Select * from  ssp_pb  '     
	   . 'where pbActief = 1 and pbDatumTot >= current_date() '	
	   . 'order by pbSort, pbDatum desc, pbId desc ';

if (!$db->Query($query)) { 
  return;
} 

// ------
// Header 
// ------

echo '<h1>Schelle Sport In De Pers</h1>';

echo '<div style="float: top; padding: 0px; margin-top: 5px; margin-left: 3px; margin-right: 10px; margin-bottom: 5px">';
	echo $headerFoto;
echo '</div>';

// ------------------
// Hoofding overzicht
// ------------------

echo '<table class="display cell-border datatable_persberichten" cellspacing="0" width="100%">';

	echo '<thead style="text-align: left;">';

		echo '<tr>';
			echo '<th>Titel</th>';
			echo '<th>Bron</th>';
			echo '<th>Datum</th>';
		echo '</tr>';

	echo '</thead>';


	// ------
	// Detail 
	// ------

	while($pbRec = $db->Row()) {

		$url = 'index.php?app=persbericht_subpage&parm1=' . $pbRec->pbId;

		$datumE = SX_tools::EdtDate($pbRec->pbDatum,'%a %d %b %Y');

		echo '<tr>';

			echo '<td>';
				echo '<a class="discretelink" href="' . $url . '">';
				echo  $pbRec->pbTitel;
				echo '</a>';
			echo '</td>';
	  
			echo '<td>';
				echo  $pbRec->pbBron;
			echo '</td>';
			
			echo '<td>';
				echo $datumE;
			echo '</td>';
		  
		echo '</tr>';

	}

echo '</table>';

?>