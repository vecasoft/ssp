 <?php

// -----
// inits
// -----

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("content.class"));
 
include_once(SX::GetClassPath("ploegen.class"));		
include_once(SX::GetClassPath("settings.class"));	
include_once(SX::GetClassPath("wedstrijden.class"));
include_once(SX::GetClassPath("clubs.class"));	

$eersteElf = SSP_settings::GetEerstePloegId();

$rechtsFilled = false;
$volgendeWedstrijdDisplayed = false;
$statischeLinksDisplayed = false;

 // ---------------
 // START CONTAINER
 // --------------

 echo "<div class='container' style=''>";

 // -----
 // TITEL
 // -----

 echo "<div class='row'>";
     echo "<div class='col'>";
        echo "<h4 style='font-weight: bold'>VOETBAL Wedstrijden 1ste Ploeg</h4>";
     echo "</div>";
 echo "</div>";

 // ----
 // FOTO
 // ----

 $fotoPath = SX::GetSiteImgPath('bs.voetbal.seniors.frontpage.jpg');

 echo "<div class='row'>";
     echo "<div class='col'>";
        echo "<img class=\"img-fluid\" style='width: 525px' src='$fotoPath'>";
     echo "</div>";
 echo "</div>";

 // ------------------------------------
 // Laatste wedstrijdvesrslag 1ste ploeg
 // ------------------------------------

$sqlStat = "Select * from ssp_vw where vwPloeg = $eersteElf and date(vwDatumTijd) >= CURRENT_DATE - INTERVAL 4 MONTH and date(vwDatumTijd) <= CURRENT_DATE + INTERVAL 1 DAY and vwStatus = 'GS' and vwVerslagKort > ' ' order by vwDatumTijd desc";

$db->Query($sqlStat);
$wedstrijdTeller = 0;

while ($vwRec = $db->Row()) {

    $wedstrijdTeller++;

    if ($wedstrijdTeller > 3)
        break;

    $id = "verslag1steploeg" . $wedstrijdTeller;

    $title = SSP_wedstrijden::GetWedstrijdInfoString($vwRec->vwId, 3);
    $html = nl2br($vwRec->vwVerslagKort);

    echo SX_content::GetBs4Collapse($title, $html, $id);

}

 // ----------------
 // EINDE CONTAINER
 // ---------------

 echo "</div>"; // Container


?>