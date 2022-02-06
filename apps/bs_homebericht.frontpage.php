<?php

// -------
// Classes
// -------

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
include_once(SX::GetClassPath("_db.class"));

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("content.class"));
include_once(SX::GetSxClassPath("fotoalbum.class"));

// -----------------
// Get "homebericht"
// -----------------


$sqlStat = "Select * from ssp_hb where hbActief = 1 and hbId = $parm1";
	
if (($db->Query($sqlStat)) && ($hbRec = $db->Row() )) {

    // ----
    // Foto
    // ----

    $fotoPath = '*NONE';
    $fotoGrootPath = '*NONE';

    $fotos = json_decode($hbRec->hbFoto);
    if ($fotos) {
        foreach ($fotos as $foto) {
            $fotoPath = SX_tools::GetFilePath($foto->name);
        }
    }

    $fotosGroot = json_decode($hbRec->hbFotoGroot);
    if ($fotosGroot) {
        foreach ($fotosGroot as $fotoGroot) {
            $fotoGrootPath = SX_tools::GetFilePath($fotoGroot->name);
        }
    }


    // -----
    // TITEL
    // -----

    $titelBoven = $hbRec->hbTitelBoven;

    $htmlRechts = $hbRec->hbTekstRechts;
    $htmlOnder = $hbRec->hbTekstOnder;

    if ($hbRec->hbTitel && $htmlRechts && ($hbRec->hbToonTitel == 1)) {
        $titel = $hbRec->hbTitel;
        $htmlRechts = "<h4 style='color: blue'>$titel</h4>$htmlRechts";
    }

    if ($hbRec->hbTitel && ! $htmlRechts && ($hbRec->hbToonTitel == 1)) {
        $titel = $hbRec->hbTitel;
        $htmlOnder = "<h4 style='color: blue'>$titel</h4>$htmlOnder";
    }

    if ($hbRec->hbFotoBreedte == '*VOLLEDIG' && $htmlRechts) {
        
        $htmlOnder = "$htmlRechts <br/> $htmlOnder";
        $htmlRechts = null;

    }

    $backgroundColor = "white";

    if ($hbRec->hbKleurAchtergrond && ($hbRec->hbKleurAchtergrond != '*WHITE')){

        $taRec = SSP_db::Get_SX_taRec('HB_ACHTERGROND_KLEUR', $hbRec->hbKleurAchtergrond );

        $backgroundColor = $taRec->taAlfaData;

    }

    echo "<div class='container' style='background-color: $backgroundColor'>";

        echo "<div class='row'>";
            echo "<h4 style='font-weight: bold'>$titelBoven</h4>";
        echo "</div>";

        // ---------------------------------
        // Foto (Met eventueel tekst rechts)
        // ---------------------------------

        echo "<div class='row'>";

            if ($hbRec->hbFotoBreedte != '*VOLLEDIG') {

                echo "<div class='col-5'>";
                    echo "<img class=\"img-fluid\" src='$fotoPath'>";
                echo "</div>";

                echo "<div class='col-7'>";
                    echo $htmlRechts;
                echo "</div>";


            }

            if ($hbRec->hbFotoBreedte == '*VOLLEDIG') {

                echo "<div class='col-12'>";
                    echo "<img class=\"img-fluid\" style='width: 525px' src='$fotoPath'>";
                echo "</div>";

            }

        echo "</div>";


        // -----------
        // Tekst onder
        // -----------

        echo "<div class='row' style='padding-top: 5px'>";
            echo "<div class='col-12'>";
                echo $htmlOnder;
            echo "</div>";
        echo "</div>";


    echo "</div>";




}

?>