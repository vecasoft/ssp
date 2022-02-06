<script>
    document.title = 'Schelle Sport - Lidgeld betalen';
</script>

<script>

// ---------------
// JQUERY-function
// ---------------

$(document).ready(function(){

    // Bewaar kledij-keuze
    // - - - - - - - - - -

    $('#butKeuze').click(function() {


        $keuze = $("input[name=inpKeuze]:checked").val();

        if ($keuze != '*STEUN' && $keuze != '*EETEVENTS' && $keuze != '*LIDGELD') {

            alert("Je dient een keuze te maken");
        }
        else {

            $('#butKeuze').prop('disabled', true);
            $("#wachten").css("display", "block");

            $.ajax({
                url: "/ssp/apps/ela_lidgeld_betalen_coronakeuze.ajx.php?",
                type: "POST",
                timeout: 3000,
                async: true,
                cache: true,
                data: {'keuze': $keuze},
                success:function(result){

                    // $(this).css("background-color","");
                    // alert(result);
                    json = $.parseJSON(result);

                    // alert(result);

                    $return = json.keuze;

                    alert("Uw keuze werd geregistreerd");
                    window.location.reload(true);

                },

                error:function(result){
                    alert('Onverwachte fout - Probeer later opnieuw');
                }

            });


        }

    })



});

</scripT>

<?php

// -------
// Classes
// -------

include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object
$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetClassPath("doc.class"));
include_once(SX::GetSxClassPath("sessions.class"));
include_once(SX::GetClassPath("_db.class"));
include_once(SX::GetClassPath("ela.class"));
require_once("vendor/autoload.php");

$persoon = SX_sessions::GetSessionUserId();

$fileName =  SSP_ela::CrtLidgeldVBSepaQR($persoon);

$qrPath = $_SESSION["SX_BASEDIR"] . '/_files/images_apps/qr_codes/' . $fileName;

$jsPath = $_SESSION["SX_BASEDIR"] . '/jquery/overlib_mini.js';
echo '<script type="text/javascript" src="' . $jsPath . '"> </script>';


$image1 = SX::GetSiteImgPath('jeugdopleiding_1.jpg');


$adRec = SSP_db::Get_SSP_adRec($persoon);

$isSpeler = false;

if ($adRec){

    $functieVB = $adRec->adFunctieVB;

    if (strpos($functieVB, 'speler') !== false)
        $isSpeler = true;
}


if (! $isSpeler) {

        echo "<h1>Betalen Lidgeld Voetbal Seizoen 2021-2022</h1>";

        echo '<div class="jumbotron" style="font-family: sans-serif; font-size: 14px; margin-top: 10px;  margin-left: 10px;text-align: center; padding: 10px">';
        echo '<br/><br/>Gelieve aan te melden met login speler';
        echo '<br/><br/>';
        echo '<button class="btn btn-success login"  href="./sx/apps/login.php"><span class="glyphicon glyphicon-log-in"></span> Aanmelden</button>';
        echo '<br/><br/>';
        echo '<a class="btn btn-warning" style="text-decoration:none"  href="index.php?app=article_subpage&parm1=90&layout=full"><span class="glyphicon glyphicon-exclamation-sign"></span> Login of Wachtwoord vergeten?</a>';

        echo '</div>';
        return;
}

if ($adRec->adClubVerlatenEindeSeizoen) {

    echo "<h1>Betalen Lidgeld Voetbal Seizoen 2021-2022</h1>";

    echo '<div class="jumbotron" style="font-family: sans-serif; font-size: 14px; margin-top: 10px;  margin-left: 10px;text-align: center; padding: 10px">';

        echo "Je verlaat de club => GEEN lidgeld te betalen";

    echo '</div>';
    return;
}
// ---------------------------------------
// Lidgeld keuze (enkel seizoen 2021-2022)
// ---------------------------------------

$coronaKeuze = SSP_ela::ChkCoronaKeuze($persoon);

$coronaKeuzeNodig = (substr($coronaKeuze,0,1) != '*');


if ($coronaKeuzeNodig) {

    ?>


    <div class="jumbotron" style="padding-left: 10px; padding-right:10px; margin-left: 5x; margin-top: 0px">
        <div style="text-align: center; float: left; margin-right: 20px">
            <span style="color: #1F63B9; font-size: 800%" class='glyphicon glyphicon-info-sign'></span>
        </div>
        <h3>Compensatie omwille CORONA voor bestaande leden seizoen 2020-2021</h3>
        Door de corona-maatregelen vielen heel wat trainingen en wedstrijden weg.<br/>Voor de jeugdspelers kon dit deels opgevangen worden door reeds in juli op te starten en tot eind mei door te trainen.<br/><br/>

        <b>We berekenden per categorie het lidgeld-aandeel van de weggevallen trainingen en wedstrijden in vergelijking met een normaal seizoen. Ook de korting op de bondsbijdragen werd mee in rekening gebracht.</b><br/><br/>


        <div style="border: 1px solid; padding: 10px">

            <div style="text-align: center; font-weight: bold; color: blue"><?php echo $coronaKeuze; ?></div><br/>

            <input type="radio" name="inpKeuze" id="STEUN" value="*STEUN"><label for="STEUN">&nbsp;<span style="margin-left: 10px; color: #C71F1F; font-weight: bold; font-size: 200%">Keuze 1: Steun SCHELLE SPORT</span><br/><br/><span style="margin-left: 25px;">U betaalt het volledige lidgeld en <b>geeft de club hierdoor een financieel steuntje in de rug</b></span><br/><br/></label>

            <input type="radio" name="inpKeuze" id="EETEVENTS" value="*EETEVENTS"><label  for="EETEVENTS">&nbsp;<span style="margin-left: 10px; color: #C71F1F; font-weight: bold; font-size: 200%">Keuze 2: Steun SCHELLE SPORT bij de EET-EVENTS</span><br/><br/><span style="margin-left: 25px;">U betaalt het volledige lidgeld en ontvangt de korting als <b>kassakorting</b> (via uw lidkaart) op de eet-events van Schelle Sport</span><br/><br/></label>

            <input type="radio" name="inpKeuze" id="LIDGELD" value="*LIDGELD"><label for="LIDGELD">&nbsp;<span style="margin-left: 10px; color: #C71F1F; font-weight: bold; font-size: 200%">Keuze 3: Lidgeld-korting</span><br/><br/><span style="margin-left: 25px;">De toegekende korting wordt verrekend met het lidgeld 2021-2022</span><br/><br/></label>


        <button style="padding-left: 5px" class="btn btn-success"  id="butKeuze">Klik hier om uw keuze te registreren</button>

        </div>

    </div>

    <?php
    return;

}


// -----------------------
// Ophalen betaal-gegevens
// -----------------------

$reedsBetaald = $adRec->adLidgeldTotaal + 0;
$lidgeldKortingTekst = $adRec->adLidgeldKortingTekst;


?>

<!-- ------ -->
<!-- JQUERY -->
<!-- ------ -->

<script>
    $(document).ready(function(){


    });

</script>

<div style="padding: 5px">

<h1>Betalen Lidgeld Voetbal Seizoen 2021-2022</h1>

Gelieve de betaalinstructies volledig op te volgen.<br/>
De mededeling (OGM) is voor elke speler anders.<br/>
Het minimale voorschot bedraagt 175 EUR (of het lidgeld indien lager) te betalen uiterlijk 15/5/2021<br/>
Het saldo dient uiterlijk op 1/7/2021 betaald te zijn. Je kan uiteraard ook meteen het volledige lidgeld betalen op 15/5.

<br/><br/>

<?php

    $gezinsLeden = SSP_ela::GetGezinsleden($persoon);


    foreach ($gezinsLeden as $gezinsLid){

        //  2021-2022: enkel van de persoon zelf...
        if ($gezinsLid != $persoon)
            continue;

        echo "<div class=\"jumbotron\"style=\"margin-left: 10px; height: 175px; padding: 10px\">";

            $html = SSP_ela::GetLidgeldVoetbalHTML($gezinsLid);
            echo $html;

        echo "</div>";

    }

?>

</div>

