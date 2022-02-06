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
        echo "<h4 style='font-weight: bold'>Wedstrijdkalender 7 dagen</h4>";
     echo "</div>";
 echo "</div>";

 // ----
 // FOTO
 // ----

 $fotoPath = SX::GetSiteImgPath('bs.voetbal.wedstrijdkalender.frontpage.jpg');

 echo "<div class='row'>";
     echo "<div class='col'>";
        echo "<img class=\"img-fluid\" style='width: 525px' src='$fotoPath'>";
     echo "</div>";
 echo "</div>";

 // -----------------
 // THUIS-wedstrijden
 // -----------------

 $html = "Geen wedstrijden gepland";

 $dayNbr = date('N');

 $fromDate = 'CURRENT_DATE';
 $toDate = 'CURRENT_DATE + INTERVAL 7 DAY';

 if ($dayNbr == 6) {
     $fromDate = 'CURRENT_DATE';
     $toDate = 'CURRENT_DATE + INTERVAL 6 DAY';
 }

 if ($dayNbr == 7) {
     $fromDate = 'CURRENT_DATE - INTERVAL 1 DAY';
     $toDate = 'CURRENT_DATE + INTERVAL 5 DAY';
 }


 $sqlStat = "Select * from ssp_vw where date(vwDatumTijd) >= $fromDate and date(vwDatumTijd) <= $toDate and vwUitThuis = 'T' and (vwstatus = 'TS' or vwStatus = 'GS') order by vwDatumTijd";

 $db->Query($sqlStat);

 $i = 0;
 $wedstrijdLijst = false;

 while ($vwRec = $db->Row()){

     $i++;

     if ($i == 1){

         $html = "<div class=\"list-group\">";
         $wedstrijdLijst = true;
     }


     $ploegNaam = SSP_ploegen::GetNaam($vwRec->vwPloeg , '*NAAMKORT');
     $kleurCodeBox = SSP_ploegen::GetKleurCodeBox($vwRec->vwPloeg);
     $datumWedstrijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d/%m - %H:%M');
     $linkPloegPagina = "index.php?app=ploegpagina_subpage&parm1=$vwRec->vwPloeg&layout=full";

     $wedstrijd = SSP_wedstrijden::GetWedstrijdInfoString($vwRec->vwId, 'F');

     $info = SSP_wedstrijden::GetWedstrijdExtraInfoTooltip($vwRec->vwId, '*HTML');
     $extraInfo = null;

     if ($info) {
        $extraInfo = "<div class=\"d-flex flex-column\"><div class=\"p-2 bg-warning\">$info</div></div>";
     }

     $html .= "<a style='padding-op: 2px; padding-bottom: 2px' href='$linkPloegPagina' class=\"list-group-item list-group-item-action\">$wedstrijd $extraInfo</a>";


 }

 if ($wedstrijdLijst)
      $html .= "</div>";

 // Build collapse...
 $id = "kalender_thuiswedstrijden_$fp_kol";
 $title = "Thuiswedstrijden";

 echo SX_content::GetBs4Collapse($title, $html, $id);

// ---------------
// UIT-wedstrijden
// ---------------

$html = "Geen wedstrijden gepland";

$dayNbr = date('N');

$fromDate = 'CURRENT_DATE';
$toDate = 'CURRENT_DATE + INTERVAL 7 DAY';

if ($dayNbr == 6) {
    $fromDate = 'CURRENT_DATE';
    $toDate = 'CURRENT_DATE + INTERVAL 6 DAY';
}

if ($dayNbr == 7) {
    $fromDate = 'CURRENT_DATE - INTERVAL 1 DAY';
    $toDate = 'CURRENT_DATE + INTERVAL 5 DAY';
}


$sqlStat = "Select * from ssp_vw where date(vwDatumTijd) >= $fromDate and date(vwDatumTijd) <= $toDate and vwUitThuis = 'U' and (vwstatus = 'TS' or vwStatus = 'GS') order by vwDatumTijd";

$db->Query($sqlStat);

$i = 0;
$wedstrijdLijst = false;

while ($vwRec = $db->Row()){

    $i++;

    if ($i == 1){

        $html = "<div class=\"list-group\">";
        $wedstrijdLijst = true;
    }


    $ploegNaam = SSP_ploegen::GetNaam($vwRec->vwPloeg , '*NAAMKORT');
    $kleurCodeBox = SSP_ploegen::GetKleurCodeBox($vwRec->vwPloeg);
    $datumWedstrijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d/%m - %H:%M');
    $linkPloegPagina = "index.php?app=ploegpagina_subpage&parm1=$vwRec->vwPloeg&layout=full";

    $wedstrijd = SSP_wedstrijden::GetWedstrijdInfoString($vwRec->vwId, 'F');

    $info = SSP_wedstrijden::GetWedstrijdExtraInfoTooltip($vwRec->vwId, '*HTML');
    $extraInfo = null;

    if ($info) {
        $extraInfo = "<div class=\"d-flex flex-column\"><div class=\"p-2 bg-warning\">$info</div></div>";
    }

    $html .= "<a style='padding-op: 2px; padding-bottom: 2px' href='$linkPloegPagina' class=\"list-group-item list-group-item-action\">$wedstrijd $extraInfo</a>";


}

if ($wedstrijdLijst)
    $html .= "</div>";

 // Build collapse...
 $id = "kalender_uitwedstrijden_$fp_kol";
 $title = "Uitwedstrijden";

 echo SX_content::GetBs4Collapse($title, $html, $id);

 // ---------
 // TORNOOIEN
 // ---------

 $sqlStat = "Select * From ssp_cl_et Inner Join sx_ta_tables on taTable = 'VOETBAL_CAT' and taCode = etVoetbalCat where date(etDatum)>= CURRENT_DATE - INTERVAL 1 DAY  and date(etDatum)<= CURRENT_DATE + INTERVAL 31 DAY and etStatus <> 'AFGELAST' and  etStatus <> 'FORFAIT' Order By etDatum, taSort";

$db->Query($sqlStat);

$tornooiLijst = false;
$html = "Geen tornooien gepland";
$i = 0;

 while ($etRec = $db->Row()){

     $i++;

     if ($i == 1){

         $html = "<div class=\"list-group\">";
         $tornooiLijst = true;
     }


     $datumTornooi = SX_tools::EdtDate($etRec->etDatum, '%a %d/%m');

     $cat = $etRec->etVoetbalCat;

     if ($cat ==  'G')
         $cat = 'G-Team';

     $club = SSP_clubs::GetNaam($etRec->etClub, true, 'class=discretelink');

     $tornooi = "$datumTornooi <b>$cat</b> op: $club";

     $tornooiInfo = $etRec->etTornooiInfo;

     $documenten = $etRec->etDocumenten;

     $docs = json_decode($documenten);
     $d = 0;

     if ($docs) {

         foreach ($docs as $doc) {

             if ($d > 0 || $tornooiInfo > ' ')
                 $tornooiInfo .= '<br/>';

             if ($d == 0)
                 $tornooiInfo .= '    <b>Documenten:</b><br/>';

             $tornooiInfo .= "<a href='$doc->name' target='_blank'>$doc->usrName</a>";

             $d++;

         }

     }

     if ($tornooiInfo) {
         $extraInfo = "<div class=\"d-flex flex-column\"><div class=\"p-2 bg-warning\">$tornooiInfo</div></div>";
     }

     $tornooiHTML = "$tornooi $extraInfo";

     $idTornooi = "Tornooi_" . $etRec->etId . $fp_kol;
     $tornooiHTML = SX_content::GetBs4Collapse($tornooi, $extraInfo, $idTornooi);


     $html .= "<div style='padding-top: 2px; padding-bottom: 4px' class=\"list-group-item list-group-item-action\">$tornooiHTML</div>";


 }

 if ($tornooiLijst)
     $html .= "</div>";

 // Build collapse...
 $idTornooilijst = "kalender_tornooien_$fp_kol";
 $title = "Tornooien";

 echo SX_content::GetBs4Collapse($title, $html, $idTornooilijst);


 // ----------------
 // EINDE CONTAINER
 // ---------------

 echo "</div>"; // Container


?>