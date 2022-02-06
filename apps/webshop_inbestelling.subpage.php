<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Webshop - Overzicht uw Bestellingen';
$("meta[name='og:description']").attr('content', 'Schelle Sport - Webshop - Overzicht uw Bestellingen');
</script>

<script>

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){
	
   
	// -----------------------
	// Afbeelden "loading.gif"
	// -----------------------
	
	$(document).ajaxStart(function(){
				
		$(".butToevoegen").prop("disabled",true);
        $("#wachten").css("display", "block");
		
    });
	
    $(document).ajaxSuccess(function(){
		
		$(".butToevoegen").prop("disabled",false);	
        $("#wachten").css("display", "none");
   
	});
   
   
	// ------------------------------------
	// Enable button when text is filled in
	// ------------------------------------
	
	$('#feedback').keyup(function() {
        if($(this).val() != '') {
           $('#butFeedback').removeAttr('disabled');
        }
        if($(this).val() == '') {
           $('#butFeedback').attr('disabled','disabled');
        }
     });

	// -------------------------
	// AJAX - Versturen feedback
	// -------------------------
	
    $("#butFeedback").click(function(){

		$boodschap = $("#feedback").val();
		
		$.ajax({
			url: "webshop_inbestelling.ajax02.php?",
			type: "POST",
			timeout: 20000,
			cache: false,
			async: true,
			data: {'boodschap': $boodschap},
			success:function(result){

				// $(this).css("background-color",""); 
				// alert(result);				  
				json = $.parseJSON(result);
			
				if (json.runStatus == "*OK") {
					alert("Feedback werd verzonden. U zal spoedig antwoord krijgen");
					$("#feedback").val('');
					$('#butFeedback').attr('disabled','disabled');
				}
				else {
					// alert(json.runStatus);
					alert("ONVERWACHTE FOUT: Feedback werd NIET verzonden");
				}
				
			},           
        
			error:function(result){
				alert('Fout 02 - Probeer later opnieuw');
			}           
        
             
		});
      
    });
	

	// -----------------------------------
	// AJAX - Ophalen orders en klant-info
	// -----------------------------------
	
    $("#butGetOrderData").click(function(){

		// $(this).css("background-color","#ffffff");
		$('#orderBlock').html("Uw bestellingen worden opgehaald");

		$.ajax({
			url: "webshop_inbestelling.ajax01.php?",
			type: "POST",
			timeout: 20000,
			cache: true,
			async: true,
			data: {'actie': 'follow'},
			success:function(result){
			 
				// $(this).css("background-color",""); 
				// alert(result);				  
				json = $.parseJSON(result);
			
				$('#wwNaam').html(unescape(json.voornaamNaam));
				$('#wwMail').html(unescape(json.mail));
				$('#wwTel').html(json.tel);
				$('#wwLidgeldStatus').html(json.lidgeldStatus);

 				$('#kledijBon').html(json.kledijBon);
 				$('#kledijbonBesteed').html(json.kledijbonBesteed);
 				$('#kledijBonRest').html(json.kledijBonRest);	
				
				if (json.kledijBonRest != '0')
					$('#kledijBonRest2').html(' - wordt verrekend op uw eerstvolgende bestelling');
				else
					$('#kledijBonRest2').html('');

				if (json.kledijBon != "0") {
					$("#kledijBonDiv").show();
				}
				
				$('#wwAantal').html(json.aantal);
				$('#wwBedrag').html(json.bedrag);			
 				$('#orderBlock').html(unescape(json.orderBlock));
				
			},           
        
			error:function(result){
				alert('Fout 01 - Probeer later opnieuw');
			}           
        
             
		});
      
    });
	
	
	$('#butGetOrderData').click();
	
	$('input').focus();


	
})

// ------------
// Wissen order
// ------------

function jWisOrder($orderNummer) {
	
	$boodschap = "Ben je zeker het order met ref. " + $orderNummer + " te wissen?";
	
	var r = confirm($boodschap);

	if (r == true) {
		
		$.ajax({
			url: "webshop_inbestelling.ajax03.php?",
			type: "POST",
			timeout: 20000,
			cache: false,
			async: false,
			data: {'orderNummer': $orderNummer},
			success:function(result){
  			 
				// $(this).css("background-color",""); 
				// alert(result);				  
				json = $.parseJSON(result);
				
				$result = json.result;
				
				if ($result == "*OK") {
					$("#butGetOrderData").click();
				}
				if ($result != "*OK")
					alert('Wissen van uw bestelling was NIET mogelijk');
			
			
			},           
        
			error:function(result){
				alert('Fout 03 - Probeer later opnieuw');
			}           
        
             
		});
		
	}

		
}


</script>


<script>
function jsOpenHandleiding(){
	
	 window.open('/_files/handleidingen/webshop/handleiding_webshop.pdf');
	
}
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
include_once(Sx::GetSxClassPath("sessions.class"));


echo '<link rel="stylesheet" href="' . $_SESSION["SX_BASEDIR"] . '/bootstrap/css/bootstrap_extract.css" />';

// -----------
// Get USER-id
// -----------

$sessionId = $_SESSION["SEID"];


$userId = SX_sessions::GetSessionUserId($sessionId);

// ------
// Header
// ------

echo "<div style='padding-left: 5px; padding-right: 5px;'>";

echo "<h1>Webshop - Overzicht uw bestellingen</h1>";

// -------------------
// Only when logged in
// -------------------

if ($userId == '*NONE') {
	
?>
	<div class="jumbotron" style="text-align: center">
		<div style="float:center; height: 50px"><span style="color: #1F63B9; font-size: 500%" class="glyphicon glyphicon-shopping-cart"></span></div>
		<div><h2 style="color: green">De webshop is exclusief voor leden van Schelle Sport.<br/>	Gelieve aan te melden.</h2><span style='color: green; font-size: 100%' class='glyphicon glyphicon-hand-right'></span>&nbsp;&nbsp;je vindt je gebruikers-id onderaan rechts op je lidkaart</div>
		<br style="clear:both">
		<button class="btn btn-success login"  href="./sx/apps/login.php"><span class="glyphicon glyphicon-log-in"></span> Aanmelden</button>
		<br style="clear:both">
		<br style="clear:both">
		<span style='color: green; font-size: 100%' class='glyphicon glyphicon-hand-right'></span>&nbsp;&nbsp;Wachtwoord vergeten? Klik <a href="/index.php?app=article_subpage&parm1=90&layout=full">HIER</a><br/>
		<br style="clear:both">
		<button class="btn btn-default"  onclick="jsOpenHandleiding()" href="./sx/apps/login.php"><span style="color: blue; font-size: 125%" class="glyphicon glyphicon-info-sign"></span> Handleiding</button>
	</div>

</div>


<?php

	return;
	
}

// ------------
// Header-block
// ------------

?>


<div class="panel panel-info">
	<div class="panel-heading">
		<div style="float: left">
			<span style="color: #1F63B9;" class='glyphicon glyphicon-user'></span>
		</div>
		<div style="float:left; margin-left: 10px">
			<strong>Uw Contactgegevens</strong>
		</div>
		<br style="clear: both">
	</div>		
	<div class="panel-body">
		<span id='wwNaam'>&nbsp;</span>
		&nbsp;- Mail-adres: <span id='wwMail'>&nbsp;</span>
		&nbsp;- Tel: <span id='wwTel'>&nbsp;</span>
		&nbsp;- Status lidgeld: <span id='wwLidgeldStatus'>&nbsp;</span>
		<div id="kledijBonDiv" style="margin-top: 5px; display: none"><span style="color: #1F63B9; font-size: 120%" class='glyphicon glyphicon-hand-right'></span> <b>Uw webshop-tegoed:</b> <span id="kledijBon">xx</span> EUR - Reeds besteed: <span id="kledijbonBesteed">yy</span> EUR - <b>Nog te besteden: <span id="kledijBonRest">zz</span> EUR</b><span id = "kledijBonRest2"></span></div>
	</div>
</div>

<div class="panel panel-info">
	<div class="panel-heading">
		<div style="float: left">
			<span style="color: #1F63B9; font-size: 120%" class='glyphicon glyphicon-volume-up'></span>
		</div>
		<div style="float:left; margin-left: 10px">
			<strong>Contacteer Schelle Sport Webshop</strong>
		</div>
		<br style="clear: both">
	</div>
	<div class="panel-body">
		<div style="float: left;">
			<textarea style="width: 550px; height: 60px" id='feedback' placeholder='Geef hier uw eventuele vragen/opmerkingen over uw bestelling(en) in. klik dan op "Versturen"'></textarea>
		</div>
		<div style="float:left; margin-left: 10px">
			<button class='btn'  id='butFeedback'  disabled href='./sx/apps/login.php'><span class='glyphicon glyphicon-envelope'></span> Versturen</button>
		</div>
		<br style="clear: both">
	</div>
</div>

<div style="margin-bottom:7px"><span style='color: blue; font-size: 100%; margin-left: 5px' class='glyphicon glyphicon-hand-right'></span>&nbsp;&nbsp;Klik <a href="/index.php?app=webshop&layout=full">HIER</a> om bijkomende producten te bestellen</div>


<?php

// ------------
// Detail Block
// ------------

?>


<div id="orderBlock" style="clear: both;">Uw bestellingen worden opgehaald...</div>

<div style="display: none;">
	<button id="butGetOrderData">Ophalen klant-info en orders</button>
	<button id="butDelOrder" data="abc">Wis order</button>
</div>

</div>

<div id="wachten" style="display:none"></div>

<style>
#wachten
{
	background: url(loading.gif) no-repeat center center;
	height: 100px;
	width: 100px;
	position: fixed;
	z-index: 1000;
	left: 50%;
	top: 50%;
	margin: -25px 0 0 -25px;
}
</style>



</body>
</html>