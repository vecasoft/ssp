<?php

include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';
include_once(Sx::GetSxClassPath("sessions.class"));	
include_once(Sx::GetSxClassPath("tools.class"));	
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object

$sqlStat = "Select * from sx_ch_calendar_headers where chCalendar = '$calendar'";
$db->Query($sqlStat);
$chRec = $db->Row();



?>
<!DOCTYPE HTML>
<html>


<script>
document.title = 'Kalender - <?php echo $chRec->chName ?>';
$("meta[name='og:description']").attr('content', 'Kalender - <?php echo $chRec->chName ?>');
</script>

<?php
echo '<link rel="stylesheet" href="' . $_SESSION["SX_BASEDIR"] . '/jquery/fullcalendar/fullcalendar.min.css" />';
echo '<script type="text/javascript" src="' . $_SESSION["SX_BASEDIR"] . '/jquery/moment/moment.min.js"> </script>';
echo '<script type="text/javascript" src="' . $_SESSION["SX_BASEDIR"] . '/jquery/fullcalendar/fullcalendar.min.js"> </script>';
echo '<script type="text/javascript" src="' . $_SESSION["SX_BASEDIR"] . '/jquery/fullcalendar/lang/nl.js"> </script>';
?>

<script>

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){
	
	$iBoxEvent = $( "#iBoxEvent" ).dialog({
	  resizable: true,
	  modal: false,
	  minWidth: 400,
	  title: '<?php echo "Kalender: $chRec->chName";?>',
	  autoOpen: false,
	  open: function(event, ui) { jQuery('.ui-dialog-titlebar-close').hide(); }

	});
	
	
	// -------------
	// FULL-CALENDAR
	// -------------
	
	$('#calendar').fullCalendar({
		
		customButtons: {
			myCustomButton: {
				text: 'custom!',
				click: function() {
					alert('clicked the custom button!');
				}
			}
		},
		
		firstDay:1,
		
		timeFormat: 'H(:mm)',
		
		lang: 'nl',
		
		monthNames: ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli','Augustus','September','Oktober', 'November','December'],

		dayNames: ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'],

		dayNamesShort: ['Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za'],

		buttonText: {
						today:    'Vandaag',
						month:    'Maand',
						week:     'week',
						day:      'Dag'
					},

		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		
		 eventMouseover: function(calEvent, jsEvent, view) {

			$("#iBoxEvent").html(calEvent.omschrijving1);
			$iBoxEvent.dialog('open');


		},

		 eventMouseout: function(calEvent, jsEvent, view) {

			$("#iBoxEvent").html(calEvent.omschrijving1);
			$iBoxEvent.dialog('close');


		},
		
		 eventClick: function(calEvent, jsEvent, view) {

		 
			if (calEvent.omschrijving2 > ' ')
				alert(calEvent.omschrijving2);


		},
		displayEventTime: false,
		eventSources: [

			// your event source
			{
				url: '/calendarFeed.php?calendar=<?php echo $calendar ?>', // use the `url` property

			}

			// any other sources...

		]
		
	
	
});

	
})

</script>

</head>
<body>

<?php
	echo "<h1>Kalender - $chRec->chName</h1>";
	
	if ($chRec->chDescription > " ") 
		echo "	$chRec->chDescription <br/><br/>";		

?>


<div style="width: 750px;">
<div id='calendar'></div>
</div>

<div id="iBoxEvent" title="" style="display: none; width: 500px"></div>

</body>
</html>