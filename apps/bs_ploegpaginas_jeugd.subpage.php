<style TYPE="text/css">


</style>

<script>
document.title = "Schelle Sport - Ploegpagina's Jeugd Overzicht";
</script>


<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("settings.class"));
include_once(Sx::GetClassPath("ploegen.class"));
include_once(Sx::GetClassPath("wedstrijden.class"));

$yellow = SSP_settings::GetBackgroundColor('yellow');

echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col">';

echo " <h1>Overzicht Jeugdploegen</h1>";
echo "<br/>";

echo "<p>Klik op een ploeg om naar de ploegpagina te gaan - met overzicht alle wedstrijden + eventuele verslagen.</p>";

// ---------------------------------------------------
// Table met alle (jeugd)ploegen + volgende wedstrijd
// ---------------------------------------------------

if (1==1){

$actiefSeizoen = SSP_settings::GetActiefSeizoen();

$sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and vpJeugdSeniors = 'Jeugd' and vpRecStatus = 'A' order by vpSort desc";

$db->Query($sqlStat);

echo "<input class=\"form-control\" id=\"tblJeugdPloegenZoeken\" type=\"text\" placeholder=\"Zoek...\">";
echo " <br>";

echo " <table style=\"margin-top: 20px;\" class=\"table table-bordered\">";

echo "<thead>";
echo "<tr>";

echo "<th>Ploeg</th>";
echo "<th class='d-none d-sm-table-cell'>Reeks</th>";
echo "<th>Volgende wedstrijd</th>";
echo "<th>Extra Info</th>";

echo "</tr>";
echo "</thead>";

echo "<tbody id=\"tblJeugdPloegen\">";

while ($vpRec = $db->Row()){

    echo "<tr>";

    echo "<td>";
    $ploeg = $vpRec->vpId;
    $ploegLink = "<a href='/index.php?app=ploegpagina_subpage&parm1=$ploeg&layout=full'>$vpRec->vpNaam</a>";
    echo $ploegLink;
    echo "</td>";

    echo "<td class='d-none d-sm-table-cell'>";
    echo $vpRec->vpReeks;
    echo "</td>";

    // ------------------
    // Volgende Wedstrijd
    // ------------------

    $ploeg = $vpRec->vpId;
    $volgendeWedstrijdId = SSP_ploegen::GetVolgendeWedstrijd($ploeg, 4, TRUE);
    $volgendeWedstrijd = SSP_wedstrijden::GetWedstrijdInfoString($volgendeWedstrijdId,2);

    echo "<td>";
    echo $volgendeWedstrijd;
    echo "</td>";

    // ---------
    // Extra Info
    // ----------

    $extraInfo = "";

    if ($volgendeWedstrijdId)
        $extraInfo = SSP_wedstrijden::GetWedstrijdExtraInfoTooltip($volgendeWedstrijdId, '*HTML');


    $backgroundColor = "";

    if ($extraInfo)
        $backgroundColor = "background-color: $yellow";

    echo "<td style=\"$backgroundColor\">";
    echo $extraInfo;
    echo "</td>";

    echo "</tr>";

}

echo "</tbody>";
echo "</table>";


echo "</div></div></div>";

}

?>


<script>
    $(document).ready(function(){
        $("#tblJeugdPloegenZoeken").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tblJeugdPloegen tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
