<script>
document.title = 'Schelle Sport - Jeugdopleiding';
</script>


<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	
$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("fanactie.class"));
include_once(SX::GetClassPath("doc.class"));

$jsPath = $_SESSION["SX_BASEDIR"] . '/jquery/overlib_mini.js';
echo '<script type="text/javascript" src="' . $jsPath . '"> </script>';


$image1 = SX::GetSiteImgPath('jeugdopleiding_1.jpg');
$image2 = SX::GetSiteImgPath('jeugdopleiding_2.jpg');

?>

<!-- ------ -->
<!-- JQUERY -->
<!-- ------ -->

<script>
$(document).ready(function(){

		
});

</script>

<!-- -------- -->
<!-- Open PDF -->
<!-- -------- -->

<script>

function jsOpenPDF(docType){

    $urlBenaderingJeugd = '<?php echo(SSP_doc::GetDocURL(166));?>';
    $urlSamenstellingKernen = '<?php echo(SSP_doc::GetDocURL(164));?>';
    $urlSpeelgelegenheid = '<?php echo(SSP_doc::GetDocURL(165));?>';
    $urlStrategischPlan = '<?php echo(SSP_doc::GetDocURL(167));?>';

    $urlSpelregels2V2 = '<?php echo(SSP_doc::GetDocURL(168));?>';
    $urlSpelregels3V3 = '<?php echo(SSP_doc::GetDocURL(169));?>';
    $urlSpelregels5V5 = '<?php echo(SSP_doc::GetDocURL(170));?>';
    $urlSpelregels8V8 = '<?php echo(SSP_doc::GetDocURL(171));?>';
    $urlSpelregels11V11 = '<?php echo(SSP_doc::GetDocURL(172));?>';

    if (docType == 'BJ')
        window.open($urlBenaderingJeugd, '_blank');
    if (docType == 'SK')
        window.open($urlSamenstellingKernen, '_blank');
    if (docType == 'SG')
        window.open($urlSpeelgelegenheid, '_blank');
    if (docType == 'SP')
        window.open($urlStrategischPlan, '_blank');
    if (docType == 'SR2V2')
        window.open($urlSpelregels2V2, '_blank');
    if (docType == 'SR3V3')
        window.open($urlSpelregels3V3, '_blank');
    if (docType == 'SR5V5')
        window.open($urlSpelregels5V5, '_blank');
    if (docType == 'SR8V8')
        window.open($urlSpelregels8V8, '_blank');
    if (docType == 'SR11V11')
        window.open($urlSpelregels11V11, '_blank');
}

</script>


<div style="margin-left: 7px ">

<h1>Jeugdopleiding</h1>

<div class="jumbotron"style="background-image: url(<?php echo $image1 ?>)">
    <div style="text-align: center">
        <button onclick="jsOpenPDF('BJ');" class="btn btn-primary btn-lg" style="width: 90%">Benadering Jeugdwerking</button>
        <button onclick="jsOpenPDF('SK');" class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Samenstelling Kernen</button>
        <button onclick="jsOpenPDF('SG');"  class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Speelgelegenheid</button>
        <button onclick="jsOpenPDF('SP');"  class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Strategisch plan 2018-2022</button>
    </div>
</div>

<h1>Spelregels</h1>
<div class="jumbotron" style="background-image: url(<?php echo $image2 ?>)">
    <div style="text-align: center">
        <button onclick="jsOpenPDF('SR2V2');" class="btn btn-success btn-lg" style="width: 90%">Spelregels U6 (2vs2)</button>
        <button onclick="jsOpenPDF('SR3V3');" class="btn btn-success btn-lg" style="width: 90%; margin-top: 10px">Spelregels U7 (3vs3)</button>
        <button onclick="jsOpenPDF('SR5V5');"  class="btn btn-success btn-lg" style="width: 90%; margin-top: 10px">Spelregels U8 & U9 (5vs5)</button>
        <button onclick="jsOpenPDF('SR8V8');"  class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Spelregels U10 t/m U13 (8vs8)</button>
        <button onclick="jsOpenPDF('SR11V11');"  class="btn btn-danger btn-lg" style="width: 90%; margin-top: 10px">Spelregels U14+ (11vs11)</button>
    </div>
</div>

</div>


