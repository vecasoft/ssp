<!DOCTYPE HTML>
<html>

<head>

<?php

// -------	
// Classes
// -------    
    
include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object	
include_once(SX::GetClassPath("personen.class"));

// --------
// Get Rows
// --------

$query = 'Select * from  ssp_nl where nlCat  = "'. $categorie . '" order by nlSort';

if (!$db->Query($query)) {
	echo 'ERROR: ' . $query;
	return;
}

?>


<script>


// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){

		
	var table = $('.datatable_<?php echo $categorie ?>').DataTable( {

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

	

	echo '<table class="display cell-border datatable_' . $categorie .'" cellspacing="0" width="100%">';
	
		echo '<thead style="text-align: left;">';

			echo '<tr>';
				echo '<th>Functie</th>';
				echo '<th>Naam</th>';
				echo '<th>Tel</th>';
				echo '<th>Mail</th>';
			echo '</tr>';

		echo '</thead>';

		echo '<tbody>';
				 
			while ($nlRec = $db->Row()) {
			
				$tel = "&nbsp;";
	
				if ($nlRec->nlHideTel != 1)
					$tel = SSP_personen::GetTel($nlRec->nlPersoon);

				$mail = "&nbsp;";

				if ($nlRec->nlHideMail != 1)
					$mail = SSP_personen::GetMail($nlRec->nlPersoon);

					 
				echo '<tr>';
					echo '<td>' . $nlRec->nlOmschrijving  . '</td>';	
					echo '<td>' . SSP_personen::GetNaam($nlRec->nlPersoon) . '</td>';
					echo '<td>' . $tel . '</td>';
					echo '<td>' . $mail . '</td>';
				echo '</tr>';
			
			}
	
	
		echo '</tbody>';
			
	echo '</table>';


?>

</body>
</html>