<script>
    document.title = 'Schelle Sport - Extra trainingen in juni';
</script>


<?php

// -------
// Classes
// -------

include_once $_SERVER['DOCUMENT_ROOT'] . '/sx.class.php';

include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object
$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

include_once(SX::GetSxClassPath("sessions.class"));
include_once(SX::GetClassPath("_db.class"));

$persoon = SX_sessions::GetSessionUserId();

$adRec = SSP_db::Get_SSP_adRec($persoon);

$isSpeler = false;

if ($adRec){

    $functieVB = $adRec->adFunctieVB;

    if (strpos($functieVB, 'speler') !== false)
        $isSpeler = true;
}


if (! $isSpeler) {

    echo "<h1>Deelnemen aan extra trainingen juni 2020</h1>";

    echo '<div class="jumbotron" style="font-family: sans-serif; font-size: 14px; margin-top: 10px;  margin-left: 10px;text-align: center; padding: 10px">';
    echo '<br/><br/>Gelieve aan te melden met login speler';
    echo '<br/><br/>';
    echo '<button class="btn btn-success login"  href="./sx/apps/login.php"><span class="glyphicon glyphicon-log-in"></span> Aanmelden</button>';
    echo '<br/><br/>';
    echo '<a class="btn btn-warning" style="text-decoration:none"  href="index.php?app=article_subpage&parm1=90&layout=full"><span class="glyphicon glyphicon-exclamation-sign"></span> Login of Wachtwoord vergeten?</a>';

    echo '</div>';
    return;
}


echo "DIT IS EEN TEST";