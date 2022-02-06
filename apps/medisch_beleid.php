<script>
document.title = 'Schelle Sport - Medisch Beleid';
</script>


<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	
$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("doc.class"));

$jsPath = $_SESSION["SX_BASEDIR"] . '/jquery/overlib_mini.js';
echo '<script type="text/javascript" src="' . $jsPath . '"> </script>';


$image1 = SX::GetSiteImgPath('jeugdopleiding_1.jpg');

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

function jsOpenPDF(docType) {

    $urlMedischBeleid = '<?php echo(SSP_doc::GetDocURL(173));?>';
    $urlBlessurePreventie = '<?php echo(SSP_doc::GetDocURL(175));?>';
    $urlWateTeDoen = '<?php echo(SSP_doc::GetDocURL(174));?>';
    $urlDocumentAangifte = '<?php echo(SSP_doc::GetDocURL(8));?>';
    $urlVerzekering = '<?php echo(SSP_doc::GetDocURL(390));?>';
    $urlDocumentAangifteGTEAM = 'https://www.s-sportrecreas.be/ledeninfo/sportverzekering';


    if (docType == 'MB')
        window.open($urlMedischBeleid, '_blank');
    if (docType == 'BP')
        window.open($urlBlessurePreventie, '_blank');
    if (docType == 'WT')
        window.open($urlWateTeDoen, '_blank');
    if (docType == 'DA')
        window.open($urlDocumentAangifte, '_blank');
    if (docType == 'DG')
        window.open($urlDocumentAangifteGTEAM, '_blank');
    if (docType == 'VZ')
        window.open($urlVerzekering, '_blank');

}


</script>


<div style="margin-left: 7px ">

    <h1>Medisch Beleid</h1>

    <div class="jumbotron"style="background-image: url(<?php echo $image1 ?>)">
        <div style="text-align: center">

            <button onclick="jsOpenPDF('MB');" class="btn btn-primary btn-lg" style="width: 90%">Medisch beleid</button>

            <button onclick="jsOpenPDF('BP');" class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Blessurepreventie</button>

            <button onclick="jsOpenPDF('WT');" class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Wat te doen bij een sportongeval</button>

            <button onclick="jsOpenPDF('DA');"  class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Document Aangifte Sportongeval</button>

            <button onclick="jsOpenPDF('DG');"  class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Document Aangifte Sportongeval G-Team & Auti-team</button>

            <button onclick="jsOpenPDF('VZ');"  class="btn btn-primary btn-lg" style="width: 90%; margin-top: 10px">Reglement Verzekering Voetbalbond</button>
        </div>
    </div>

</div>

