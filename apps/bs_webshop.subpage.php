<!DOCTYPE HTML>
<html>

<script>
document.title = 'Schelle Sport - Webshop';
$("meta[name='og:description']").attr('content', 'Schelle Sport - Webshop');
</script>

<style>
    /* Center the loader */
    #loader {
        position: absolute;
        left: 50%;
        top: 150px;
        z-index: 1;
        width: 150px;
        height: 150px;
        margin: -75px 0 0 -75px;
        border: 16px solid #f3f3f3;
        border-radius: 50%;
        border-top: 16px solid #3498db;
        width: 120px;
        height: 120px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Add animation to "page content" */
    .animate-bottom {
        position: relative;
        -webkit-animation-name: animatebottom;
        -webkit-animation-duration: 1s;
        animation-name: animatebottom;
        animation-duration: 1s
    }

    @-webkit-keyframes animatebottom {
        from { bottom:-100px; opacity:0 }
        to { bottom:0px; opacity:1 }
    }

    @keyframes animatebottom {
        from{ bottom:-100px; opacity:0 }
        to{ bottom:0; opacity:1 }
    }

    .ribbon {
        position: absolute;
        right: -5px; top: -5px;
        z-index: 1;
        overflow: hidden;
        width: 75px; height: 75px;
        text-align: right;
    }
    .ribbon span {
        font-size: 10px;
        color: #fff;
        text-transform: uppercase;
        text-align: center;
        font-weight: bold; line-height: 20px;
        transform: rotate(45deg);
        width: 100px; display: block;
        background: #79A70A;
        background: linear-gradient(#9BC90D 0%, #79A70A 100%);
        box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
        position: absolute;
        top: 19px; right: -21px;
    }
    .ribbon span::before {
        content: '';
        position: absolute;
        left: 0px; top: 100%;
        z-index: -1;
        border-left: 3px solid #79A70A;
        border-right: 3px solid transparent;
        border-bottom: 3px solid transparent;
        border-top: 3px solid #79A70A;
    }
    .ribbon span::after {
        content: '';
        position: absolute;
        right: 0%; top: 100%;
        z-index: -1;
        border-right: 3px solid #79A70A;
        border-left: 3px solid transparent;
        border-bottom: 3px solid transparent;
        border-top: 3px solid #79A70A;
    }
    .red span {background: linear-gradient(#F70505 0%, #8F0808 100%);}
    .red span::before {border-left-color: #8F0808; border-top-color: #8F0808;}
    .red span::after {border-right-color: #8F0808; border-top-color: #8F0808;}

    .blue span {background: linear-gradient(#2989d8 0%, #1e5799 100%);}
    .blue span::before {border-left-color: #1e5799; border-top-color: #1e5799;}
    .blue span::after {border-right-color: #1e5799; border-top-color: #1e5799;}

</style>


<script>

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){

    // document.getElementById("loader").style.display = "block";

	// -----------------------
	// Afbeelden "loading.gif"
	// -----------------------

	$(document).ajaxStart(function(){

		$(".butToevoegen").prop("disabled",true);
        document.getElementById("loader").style.display = "block";

    });

    $(document).ajaxSuccess(function(){

		$(".butToevoegen").prop("disabled",false);
        // $("#wachten").css("display", "none");
        document.getElementById("loader").style.display = "none";
	});

    // - - - - - - - - - - - - - - - -
    // Button "Wissen winkelwagen-lijn"
    // - - - - - - - - - - - - - - - -

    $('#butDelWwData').click(function() {

        $wisId = $(this).attr('data');

        $.ajax({
            url: "webshop.ajax04.php?",
            type: "POST",
            timeout: 30000,
            cache: false,
            async: true,
            data: {'wwId': $wisId},
            success:function(result){

                // $(this).css("background-color","");
                // alert(result);
                json = $.parseJSON(result);

                $("#butGetWwData").click();

            },

            error:function(result){

                alert('Fout 04 - Probeer later opnieuw');
            }


        });

    });

    // - - - - - - - - - - - - -
    // Button "Plaats bestelling"
    // - - - - - - - - - - - - -

    $('#butPlaatsBestelling').click(function() {

        $.ajax({
            url: "webshop.ajax05.php?",
            type: "POST",
            timeout: 20000,
            cache: false,
            async: true,
            success:function(result){

                // $(this).css("background-color","");
                // alert(result);
                json = $.parseJSON(result);

                $info = json.info;

                // alert($info);

                $("#bevestigBestellingBody").html($info);

                $("#butGetWwData").click();

                // $bevestigBestelling.dialog('open');
                $('#bevestigBestelling').modal('show');


            },

            error:function(result){
                alert('Fout 05 - Probeer later opnieuw');
            }


        });

    });

    // ---------------------------------------
    // AJAX - Ophalen klant-info + winkelwagen
    // ---------------------------------------

    $("#butGetWwData").click(function(){

        $.ajax({
            url: "webshop.ajax01.php?",
            type: "POST",
            timeout: 30000,
            cache: true,
            async: true,
            data: {'actie': 'follow'},
            success:function(result){

                json = $.parseJSON(result);

                $('#wwNaam').html('<b>' + unescape(json.voornaamNaam) + '</b>');
                $('#wwMail').html(unescape(json.mail));
                $('#wwTel').html(json.tel);
                $('#wwLidgeldStatus').html(json.lidgeldStatus);
                $('#kledijKeuzeTekst').html(json.kledijKeuzeTekst);
                $('#wwAantal').html(json.aantal);
                $('#wwBedrag').html(json.bedrag);
                $('#winkelwagen').html(json.winkelwagen);
                $('#kledijBon').html(json.kledijBon);
                $('#kledijbonBesteed').html(json.kledijbonBesteed);
                $('#kledijBonRest').html(json.kledijBonRest);

                if (json.kledijBonRest != '0')
                    $('#kledijBonRest2').html(' - wordt verrekend op uw eerstvolgende bestelling');
                else
                    $('#kledijBonRest2').html('');


                if (json.aantal > 0) {
                    $('#butPlaatsBestelling').show();
                    $('#butPlaatsBestelling').removeAttr("disabled");
                }
                else {
                    $('#butPlaatsBestelling').hide();
                    $('#butPlaatsBestelling').attr("disabled", "disabled");
                }

                if (json.kledijBon != "0")
                    $("#kledijBonDiv").show();
                if (json.kledijKeuzeTekst > " ")
                    $("#kledijKeuzeDiv").show();

            },

            error:function(result){
                alert('Fout 01 - Probeer later opnieuw');
            }

        });

    });

    // - - - - - - - - - - - -
    // TOEVOEGEN - Toon popup
    // - - - - - - - - - - - -

    $('.butToevoegen').click(function() {

        $parm_id = $(this).attr('id');
        $parm_type = $(this).attr('data-type');

        // - - - - - - - - - - - - - - -
        // Toon modaal scherm "toevoegen"
        // - - - - - - - - - - - - - - -

        $.ajax({
            url: "bs_webshop.ajax02.php?",
            type: "POST",
            timeout: 30000,
            async: true,
            cache: true,
            data: {'parm_id': $parm_id, 'parm_type': $parm_type},
            success:function(result){

                // $(this).css("background-color","");
                // alert(result);
                json = $.parseJSON(result);

                // alert(result);

                $html = json.htmlcode;
                $runStatus = json.runStatus;
                $('#toevoegenBody').html($html);
                //$('#toevoegen').attr('runStatus', $runStatus);

                $('#toevoegen').modal('show');

            },

            error:function(result){
                alert('Fout 02 - Probeer later opnieuw');
            }

        });




    });

    // ----------------------
    // TOEVOEGEN - Verwerking
    // ----------------------

    $("#btnToevoegen").click(function () {

        var $set = $('.maten');
        var len = $set.length;

        $('.maten').each(function(i, obj) {

            $maat = $(obj).val();
            $arId = $(obj).attr('data-arId');
            $paId = $(obj).attr('data-paId');
            $pkId = $(obj).attr('data-pkId');

            // - - - - - - - - - - - - -
            // Create "winkelwagen-lijn"
            // - - - - - - - - - - - - -

            $.ajax({
                url: "webshop.ajax03.php?",
                type: "POST",
                timeout: 10000,
                cache: false,
                async: true,
                data: {'arId': $arId, 'maat': $maat, 'pkId': $pkId},
                success:function(result){

                    // $(this).css("background-color","");
                    // alert(result);
                    json = $.parseJSON(result);

                    if (i == len - 1) {
                        $("#butGetWwData").click();
                        window.scrollTo(0,0);

                    }

                },

                error:function(result){
                    alert('Unexpected Error 03');
                }

            });


        });


        $('#toevoegen').modal('hide');

    });

    // Override top

    $('.nav-link').click(function(){
        divId = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(divId).offset().top - 75
        }, 100);
    });

    // --------------------------------
    // Ophalen klant & winkelwagen info
    // --------------------------------

    $("#butGetWwData").click();

})

</script>


</head>

<body>


<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	
$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
$db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
$db4 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

include_once(SX::GetSxClassPath("tools.class"));
include_once(Sx::GetClassPath("eba.class"));
include_once(Sx::GetSxClassPath("sessions.class"));

// -----------
// Get USER-id
// -----------

$sessionId = $_SESSION["SEID"];
$userId = SX_sessions::GetSessionUserId($sessionId);

// -----------------------------
// Get image "vergrootglas-zoom"
// -----------------------------

$img_vergrootglas_zoom = SX::GetSiteImgPath('vergrootglas_zoom.gif');

// ------
// Header
// ------

echo "<div class='container'><div class='row'><div class='col'>";

echo "<h1>Webshop</h1>";

// -------------------------------------------
// Booschap - "Enkel voor leden Schelle Sport"
// -------------------------------------------

if ($userId == '*NONE') {

    echo '<div class="jumbotron" style="margin-top: 10px; text-align: center; padding: 10px">';
    echo 'De webshop is enkel beschikbaar voor leden van Schelle Sport';
    echo '<br/>Gelieve aan te melden';
    echo '<br style="margin-bottom: 10px"/>';
    echo "<a href=\"#loginModal\" role=\"button\" class=\"btn btn-primary btn-lg\" data-toggle=\"modal\" data-backdrop=\"static\">Aanmelden</a>";
    echo "<br/><br/>Login of Wachtwoord vergeten? Klik <a href=\"/index.php?app=article_subpage&parm1=90&layout=full\">HIER</a><br/>";
    echo '</div>';

	return;

}


?>

<!--Klantgegevens-->
<div id="loader"></div>

<div class="card shadow-lg">

    <div class="card-header">
        <h4><i class="fas fa-user" style="color:#0A529E"></i><div class="badge badge-info" style="margin-left: 10px; color:#FFEB10; background-color: #0A529E">Uw Gegevens</div></h4>
    </div>

    <div class="card-body">
        <div class="container">
            <div class="row">
                 <div class="col-12">
                    Naam: <span id="wwNaam"></span>
                 </div>
                <div class="col-12">
                    Status Lidgeld: <span id="wwLidgeldStatus"></span>
                </div>
                <div class="col-12">
                    Webshop-tegoed: <span id="kledijBonRest"></span>
                </div>
            </div>
        </div>
    </div>

</div>

<!--Winkelwagen-->

<div class="card shadow-lg" style="margin-top: 10px;">

    <div class="card-header">
        <h4><i class="fas fa-shopping-cart" style="color:#0A529E"></i><div class="badge badge-info" style="margin-left: 10px; color:#FFEB10; background-color: #0A529E">Uw Winkelwagen</div></h4>
    </div>

    <div class="card-body">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-xs-12">
                    <div id="winkelwagen"></div>
                </div>
                <div class="row-md-2 col-xs-12">
                    <button id="butPlaatsBestelling" type="button" class="btn btn-success  btn-lg">Plaats bestelling</button>
                </div>
            </div>
        </div>
    </div>

</div>

<?php

    echo SSP_eba::GetRubriekenNavBar($userId);

    $sqlStat = "Select * from eba_ru_rubrieken where ruRecStatus = 'A' order by ruSort";
    $db->Query($sqlStat);

    while ($ruRec = $db->Row()){

        $rubriek = $ruRec->ruId;

        if ( ! SSP_eba::ChkRubriekDoelgroep($rubriek, $userId))
            continue;

        $id = "rubriek$rubriek";

        echo "<section id=\"$id\" class=\"container-fluid\" style='padding-top: 50px'>";
        echo "<h4><div class=\"badge badge-info\" >$ruRec->ruNaam</div></h4>";

        // Extra info
        if ($ruRec->ruOmschrijving){
            echo "<div class='alert alert-info' style='padding-top: 10px; padding-bottom: 10px'>";
            echo $ruRec->ruOmschrijving;
            echo "</div>";

        }

        // --------
        // Paketten
        // --------

        $sqlStat = "select * from eba_pk_pakketten inner join eba_ra_rubriek_artikels on raRubriek = $ruRec->ruId and raPakket = pkId order by raSort";
        $db2->Query($sqlStat);

        while ($pkRec = $db2->Row()) {

            $doelgroep = $pkRec->pkDoelgroep;
            if ($doelgroep and (! SSP_eba::ChkDoelgroep($userId, $doelgroep)))
                continue;

            echo SSP_eba::GetPakketCard($pkRec->pkId);

        }

        // --------
        // Artikels
        // --------

        $sqlStat = "Select * From eba_ar_artikels inner join eba_ra_rubriek_artikels on raRubriek = $ruRec->ruId and raArtikel = arId order by raSort ";
        $db2->Query($sqlStat);

        while ($arRec = $db2->Row())
          echo SSP_eba::GetArtikelCard($arRec->arId);

        echo "</section>";


    }

?>

<div style="display: none;">
    <a  href="javascript: return false"  id="butGetWwData">Get data</a>
    <a  href="javascript: return false"   id="butDelWwData" data="abc">Wis winkelwagen lijn</a>

    <button id="butModalToevoegen" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#toevoegen">
        Launch demo modal
    </button>

</div>

<div id="toevoegen" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Toevoegen aan winkelwagen</h3>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            </div>
            <div class="modal-body">

                    <div id="toevoegenBody">
                    </div>

                    <div class="form-group py-4">
                        <button class="btn btn-outline-secondary btn-lg" data-dismiss="modal" aria-hidden="true">Afsluiten</button>
                        <button type="button" class="btn btn-success btn-lg float-right " id="btnToevoegen">Toevoegen</button>
                    </div>


            </div>
        </div>
    </div>
</div>

<div id="bevestigBestelling" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Uw bestelling werd geregistreerd</h3>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            </div>
            <div class="modal-body">

                <div id="bevestigBestellingBody">
                </div>

                <div class="form-group py-4">
                    <button class="btn btn-outline-secondary btn-lg float-right" data-dismiss="modal" aria-hidden="true">Afsluiten</button>
                </div>


            </div>
        </div>
    </div>
</div>


</div></div></div>

</body>
</html>