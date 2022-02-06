<?php
session_start();

// ******************************
// ELA - Registratie COORNA-keuze
// ******************************

// -----
// inits
// ------

include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';
include_once(Sx::GetSxClassPath("sessions.class"));	
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object
include(Sx::GetClassPath("ela.class"));
	
// -----------------------
// Get incoming parameters
// -----------------------

$keuze = $_POST['keuze'];

// ---------------------
// Get user from session
// ---------------------

$sessionId = $_SESSION["SEID"];

$userId = SX_sessions::GetSessionUserId($sessionId);

// ---------------
// Set kledijkeuze
// ---------------

SSP_ela::RegCoronaKeuze($keuze, $userId);

// ---------------
// Generate Answer
// ---------------

$arr['keuze'] = $keuze;
$arr['return'] = "*OK";	

$json = json_encode($arr);

echo $json;

?>
