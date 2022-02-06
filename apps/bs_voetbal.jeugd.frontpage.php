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

 $fotoPath = SX::GetSiteImgPath('bs.voetbal.frontpage.jpg');

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

    error_log($wedstrijdTeller);

    if ($wedstrijdTeller > 3)
        break;

    $id = "verslag1steploeg" . $wedstrijdTeller;
    $idCollapse = "verslag1steploegCollapse" . $wedstrijdTeller;

    $wedstrijdInfo = SSP_wedstrijden::GetWedstrijdInfoString($vwRec->vwId, 3);

    echo "<div class='row'>";
        echo "<div class='col'>";

?>

    <div id="<?php echo $id; ?>">

        <div class="card">
            <div class="card-header collapsed" data-toggle="collapse" href="#<?php echo $idCollapse; ?>">
                <a class="card-title">
                    <div style="float: left"> <?php echo $wedstrijdInfo ?></div>
                    <div style="float: right;"><i class="fas fa-plus"></i></div>
                </a>
            </div>
            <div id="<?php echo $idCollapse; ?>" class="collapse" aria-labelledby="headingOne" data-parent="#<?php echo $id; ?>">
                <div class="card-body">
                    <?php echo nl2br($vwRec->vwVerslagKort) ?>
                </div>
            </div>
        </div>

    </div>

    <script>

        $("#<?php echo $id; ?>").on("hide.bs.collapse show.bs.collapse", e => {
            $(e.target)
                .prev()
                .find("i:last-child")
                .toggleClass("fa-minus fa-plus");
        });


    </script>

<?php


        echo "</div>";
    echo "</div>";

}

 // ----------------
 // EINDE CONTAINER
 // ---------------

 echo "</div>"; // Container


?>