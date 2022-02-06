<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>

<?php

include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';
include_once(SX::GetSxClassPath("sessions.class"));
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object		

echo '<link rel="stylesheet" type="text/css" href="' . SX::GetCssPath() . '">';

echo '<script src="' . $_SESSION["SX_BASEDIR"] . '/jquery/jquery.js"> </script>';
echo '<script src="' . $_SESSION["SX_BASEDIR"] . '/jquery/jquery-ui.js"> </script>';

echo '<link rel="stylesheet" href="' . $_SESSION["SX_BASEDIR"] . '/jquery/jquery-ui.css" />';

// ----------------
// Get parameter(s)
// ----------------

if (isset($_GET['arId'])) 
	$arId = htmlspecialchars($_GET["arId"]); 
else
	die("Onverwachte fout");

?>

<style>

.ui-tabs .ui-tabs-nav {
	background: #D2E3EA;
}

.ui-tabs .ui-tabs-panel /* just in case you want to change the panel */ {
	/* background: blue; */
}

</style>


<?php

// -----
// Inits
// -----


?>

<script>

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){

	// ------------------------------------------
    // Background yellow for input-box with focus
	// ------------------------------------------
	
    $("input").focus(function(){
      $(this).css("background-color","#ffffc5");
    });
  
    $("input").blur(function(){
      $(this).css("background-color","#ffffff");
    });
	
	
});


</script>

</head>

<body>

<?php

$sessionId = SX_sessions::GetSessionId();
$userId = SX_sessions::GetSessionUserId($sessionId);
$userName = SX_sessions::GetUserName($userId);

// ----------------
// Get artikel-info
// ----------------

$sqlStat  = "Select * From eba_ar_artikels where arId = $arId";
             
if (!$db->Query($sqlStat)) { 
  return $sqlStat;
}
$arRec = $db->Row();

?>

<div style="margin-left: 15px;">

	<div style="float:left">
		<h1>Winkelwagen van <?php echo $userName; ?></h1>
	</div>
	
	<br style="clear: both">
	
<?php	
	if ($arId > 0) 
		echo "<div style='padding-left: 10px; min-height: 300px'><iframe frameborder=0 src='/eba_winkelwagen/eba_ww_winkelwagen_add.php?x=y&seid=$sessionId&arId=$arId' style='width: 100%; height: 600px'></iframe></div>";	
	else
		echo "<div style='padding-left: 10px; min-height: 300px'><iframe frameborder=0 src='/eba_winkelwagen/eba_ww_winkelwagen_list.php?x=y&seid=$sessionId&arId=$arId' style='width: 100%; height: 600px'></iframe></div>";	
	
?>
	
</div>
 
</body>
</html>