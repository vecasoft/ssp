<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

<style>

    @media (min-width: 576px) {

        .card-columns {column-count: 2; }
    }

</style>

<?php

// -------		
// Classes
// -------    
    
include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("bs4.class"));
include_once(SX::GetClassPath("ploegen.class"));
include_once(SX::GetClassPath("wedstrijden.class"));
include_once(SX::GetClassPath("clubs.class"));
include_once(SX::GetClassPath("personen.class"));
include_once(SX::GetClassPath("settings.class"));

// -----
// inits
// -----

$today1 = strtotime('-6 hours');
$today2 = strtotime('tomorrow');
$eerstvolgend = FALSE;

$logoFB = SX::GetSiteImg('facebook.gif');

$nextWedstrijdId = SSP_ploegen::GetVolgendeWedstrijd($parm1, 4, TRUE);

// --------------------
// Afbeelden ploeg-info
// --------------------


$ploeg = $parm1;
$query = "Select * from ssp_vp where vpId = $ploeg";

if (!$db->Query($query)) { 
  return $query;
}

if (! $vpRec = $db->Row()) {
    echo '<div class="container">';
    echo '<div class="rol">';
    echo '<div class="col">';
    echo "<br/>";
	echo "<div class='alert alert-warning' role=\"alert\" style='margin-bottom: 20px; text-align: center'>Onverwachte fout</div>";
	echo "&nbsp;";
	echo "</div></div></div>";
	return;
}

// ------------------
// Set document title
// ------------------

$docTitle = "Schelle Sport - Ploegpagina $vpRec->vpNaam";
echo ' <script> ';
echo "$(document).attr('title', '$docTitle');";
echo ' </script> ';

// ---------------------------------------
// Create HTML Button "Andere Ploegpagina"
// --------------------------------------

$actiefSeizoen = SSP_settings::GetActiefSeizoen();

$sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and (vpJeugdSeniors = 'Jeugd' or vpJeugdSeniors = 'Seniors')  and vpRecStatus = 'A' order by vpSort desc";
$db->Query($sqlStat);

$html = "<div class=\"dropdown\">";

$html .= "<button class=\"btn btn-secondary dropdown-toggle\" type=\"button\" id=\"dropDownAnderePloegpagina\" data-display=\"static\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">";
$html .= "Ga naar andere ploegpagina";
$html .= "</button>";

$html .= "<div class=\"dropdown-menu\" aria-labelledby=\"dropDownAnderePloegpagina\">";

while ($vpRec2 = $db->Row()) {
    $ploegId = $vpRec2->vpId;
    $html .= "<a class=\"dropdown-item\" href=\"/index.php?app=ploegpagina_subpage&parm1=$ploegId&layout=full'\">$vpRec2->vpNaam</a>";
}

$html .= " </div>";

$html .= "</div>";

$butAnderePloegpagina = $html;

// -----------
// Create Page
// -----------

echo '<div class="container">';

    // -----
    // TITEL
    // -----

    if (1==1) {

        echo "<div class=\"row\">";

        echo '<div class="ml-3">';
        echo "<h1>$vpRec->vpNaam (Seizoen $vpRec->vpSeizoen)</h1>";
        echo "</div>";

        echo '<div class="ml-auto mr-3 mt-2">';
        echo "$butAnderePloegpagina";
        echo "</div>";

        echo "</div>";
    }

    // ------------------------
    // Alg. Ploeg-info (+ foto)
    // ------------------------

    echo "<div class=\"row\">";
    echo "\n\n";
    echo "<div class=\"col\">";
    echo "\n\n";

        echo "<div class=\"jumbotron\" style='padding: 20px'>";
        echo "\n\n";

            echo "Trainer/ afgevraadigde: Zie <a href=\"index.php?app=personalia_subpage&layout=full\">Personalia</a><br/>";
            echo "\n\n";

            if ($vpRec->vpLinkStand > ' ') {
                echo 'Klik <a href="' . $vpRec->vpLinkStand . '" target="_blank">hier</a> voor de stand in hun reeks ' . $vpRec->vpReeks ;
                echo "\n\n";
            }

        echo "</div>";
        echo "\n\n";

    echo "</div>";
    echo "\n\n";
    echo "</div>";
    echo "\n\n";

    // -----------------------
    // Wedstrijden + uitslagen
    // -----------------------

    // Toekomstige wedstrijden...
    $sqlStat = "Select *, case when DATE(DATE_ADD(vwDatum, INTERVAL + 1 DAY)) > now() then 'Toekomst' else 'Verleden' end as toekomstVerleden from ssp_vw where vwPloeg = $ploeg order by vwDatumTijd";
    $db->Query($sqlStat);

    $tellerToekomst = 0;
    $tellerAlle = 0;

    $toekomstigeWedstrijden = "<div class='container d-none d-md-block'>";
    $toekomstigeWedstrijden2 = "<div class='container d-block d-md-none'>";
    $alleWedstrijden = "<div class='container d-none d-md-block'>";
    $alleWedstrijden2 = "<div class='container d-block d-md-none'>";

    While ($vwRec = $db->Row()){

        $wedstrijdCard = SSP_wedstrijden::GetWedstrijdCard($vwRec->vwId);

        $tellerAlle++;

        $alleWedstrijden2 .= "<div class='row' style=\"margin-bottom: 10px;\"><div class='col-12'>";
        $alleWedstrijden2 .= $wedstrijdCard;
        $alleWedstrijden2 .= "</div></div>";

        if ($tellerAlle == 1)
           $alleWedstrijden .= "<div class='row' style=\"margin-bottom: 10px;\"><div class='col-6'>";
        if ($tellerAlle == 2)
           $alleWedstrijden .= "<div class='col-6'>";

       $alleWedstrijden .= $wedstrijdCard;

        if ($tellerAlle == 1)
           $alleWedstrijden .= "</div>";
        if ($tellerAlle == 2)
           $alleWedstrijden .= "</div></div>";

        if ($tellerAlle == 2)
            $tellerAlle = 0;

        if ($vwRec->toekomstVerleden == 'Toekomst') {

            $tellerToekomst++;
            
            $toekomstigeWedstrijden2 .= "<div class='row' style=\"margin-bottom: 10px;\"><div class='col-12'>";
            $toekomstigeWedstrijden2 .= $wedstrijdCard;
            $toekomstigeWedstrijden2 .= "</div></div>";


            if ($tellerToekomst == 1)
                $toekomstigeWedstrijden .= "<div class='row' style=\"margin-bottom: 10px;\"><div class='col-6'>";
            if ($tellerToekomst == 2)
                $toekomstigeWedstrijden .= "<div class='col-6'>";

            $toekomstigeWedstrijden .= $wedstrijdCard;

            if ($tellerToekomst == 1)
                $toekomstigeWedstrijden .= "</div>";
            if ($tellerToekomst == 2)
                $toekomstigeWedstrijden .= "</div></div>";

            if ($tellerToekomst == 2)
                $tellerToekomst = 0;

        }
    }

    if ($tellerToekomst == 1)
        $toekomstigeWedstrijden .= "<div class='col-6'><div style='width: 450px'>&nbsp;</div></div></div>";
    if ($tellerAlle == 1)
        $alleWedstrijden .= "<div class='col-6'><div style='width: 450px'>&nbsp;</div></div></div>";

    $toekomstigeWedstrijden .= "</div>";
    $toekomstigeWedstrijden2 .= "</div>";

    $alleWedstrijden .= "</div>";
    $alleWedstrijden2 .= "</div>";

    $tabHeaders = array();
    $tabHeaders[] = 'Toekomstige';
    $tabHeaders[] = 'Alle';

    $tabContents = array();
    $tabContents[] = $toekomstigeWedstrijden . $toekomstigeWedstrijden2;
    $tabContents[] = $alleWedstrijden . $alleWedstrijden2;

    echo SX_bs4::GetTabHtml('wedstrijden',$tabHeaders, $tabContents, 'Wedstrijd-kalender');

    // echo $toekomstigeWedstrijden;

   // -----------------
// Einde "container"
// -----------------

echo "</div>";


?>
