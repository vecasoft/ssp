<!DOCTYPE HTML>
<html>

<head>

<script>
document.title = 'Schelle Sport - Personalia';
</script>

<?php

include_once(SX::GetSxClassPath("sessions.class"));
include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

// -----------
// Get USER-id
// -----------

$sessionId = $_SESSION["SEID"];

$userId = SX_sessions::GetSessionUserId($sessionId);

?>

</head>

<body>

<?php

echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col" style="">';

echo " <h1>Personalia - Bestuur Voetbal</h1>";

if ($userId == 'GUEST' or $userId == '*NONE'){
	
	echo '<div class="jumbotron" style="margin-top: 10px; text-align: center; padding: 10px">';
	echo 'Info trainers & afgevaardigen is enkel beschikbaar voor leden van Schelle Sport';
	echo '<br/><br/>Om alle gegevens te kunnen zien, gelieve aan te melden';
	echo '<br/><br/>';
	echo '<button class="btn btn-success login"  href="./sx/apps/login.php"><span class="glyphicon glyphicon-log-in"></span> Aanmelden</button>';
	echo '</div>';

}
else
	echo "<br/>";

// -------------
// Bestuursleden
// -------------

$sqlStat = "Select * from ssp_ad inner join sx_ta_tables on taTable = 'BESTUUR' and taCode = adCode where adFunctieVB like '%bestuur%' and adRecStatus = 'A' ORDER BY taSort";

$db->Query($sqlStat);
$htmlBestuur = "";

$htmlBestuur .= "<input class=\"form-control\" id=\"tblBestuurZoeken\" type=\"text\" placeholder=\"Zoek...\">";
$htmlBestuur .= "<br/>";

$htmlBestuur .= "<table style=\"margin-top: 20px;\"  class=\"table table-bordered\">";
$htmlBestuur .=  "<thead>";
$htmlBestuur .= "<tr>";
$htmlBestuur .= "<th>Naam</th>";
$htmlBestuur .= "<th class='d-lg-table-cell d-none'>Functie</th>";
$htmlBestuur .= "<th class='d-lg-none'>Functie / Contact</th>";
$htmlBestuur .= "<th class='d-lg-table-cell d-none'>Contactgegevens</th>";
$htmlBestuur .= "</tr>";
$htmlBestuur .=  "</thead>";

$htmlBestuur .= "<tbody id=\"tblBestuur\">";

while ($adRec = $db->Row()){

    $mail = $adRec->adMail;

    if ($adRec->taAlfaData)
        $mail = $adRec->taAlfaData;

    $fotoPath = "";

    if ($adRec->adFoto){

        $fotos = json_decode($adRec->adFoto);

        if ($fotos) {
            foreach ($fotos as $foto) {

                if (strpos($foto->type, "image") !== false)
                    $fotoPath = $foto->thumbnail;

            }
        }

    }

    $htmlBestuur .= "<tr>";

    $htmlBestuur .= "<td>";
    $htmlBestuur .= $adRec->adVoornaamNaam;

    if ($fotoPath){

        $htmlBestuur .= "<br/>";
        $htmlBestuur .= "<img class=\"img-fluid\" src='$fotoPath'>";

    }

    $htmlBestuur .= "</td>";
    $htmlBestuur .= "<td>$adRec->taDescription<div class='d-lg-none' style='padding-top: 10px'><a href='mailto:$adRec->adMail'>$mail</a><br/>$adRec->adTel</div></td>";
    $htmlBestuur .= "<td  class='d-lg-table-cell d-none'><a href='mailto:$adRec->adMail'>$mail</a><br/>$adRec->adTel</td>";

    $htmlBestuur .= "</tr>";

}

$htmlBestuur .= "</tbody>";
$htmlBestuur .= "</table>";

$htmlBestuur .= "<script>";
$htmlBestuur .= "$(document).ready(function(){";
$htmlBestuur .= "$(\"#tblBestuurZoeken\").on(\"keyup\", function() {";
$htmlBestuur .= "var value = $(this).val().toLowerCase();";
$htmlBestuur .= "$(\"#tblBestuur tr\").filter(function() {";
$htmlBestuur .= "$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)";
$htmlBestuur .= "});";
$htmlBestuur .= "});";
$htmlBestuur .= "});";
$htmlBestuur .= "</script>";

echo $htmlBestuur;


echo "</div></div></div>";

?>


</body>
</html>