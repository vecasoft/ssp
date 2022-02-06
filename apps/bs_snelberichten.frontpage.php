<?php

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("homeberichten.class"));


$snelberichten = SX_homeberichten::ChkSnelberichten();


if ($snelberichten) {

    // ---------------
    // START CONTAINER
    // --------------

    echo "<div class='container' style=''>";

    // -----
    // TITEL
    // -----

    echo "<div class='row'>";
    echo "<div class='col'>";
    echo "<h4 style='font-weight: bold'><i class=\"fas fa-bell\" style='color: red'></i> SNELBERICHTEN</h4>";
    echo "</div>";
    echo "</div>";

    // ------------------
    // Alle Snelberichten
    // ------------------

    $sqlStat = "Select * from ssp_sb where sbDatumTot >= CURRENT_DATE  and sbDoelgroep <> '*VACATURE' order by sbSort, sbId desc";
    $db->Query($sqlStat);

    while ($sbRec = $db->Row()) {

        $berichtTeller++;

        $id = "snelbericht" . $berichtTeller . "_$fp_kol";

        $title= $sbRec->sbTitel;
        $title = "<div style='color: darkred; font-weight: bold'>$title</div>";
        $html = nl2br($sbRec->sbTekst);

        echo SX_content::GetBs4Collapse($title, $html, $id);

    }

    // ----------------
    // EINDE CONTAINER
    // ---------------

    echo "</div>"; // Container

}


?>