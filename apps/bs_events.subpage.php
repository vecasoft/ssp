
<script>
document.title = 'Schelle Sport - Events';
</script>


<?php

// -------		
// Classes
// -------    
    
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	

include_once(SX::GetSxClassPath("tools.class"));

// -----
// Inits
// -----

$foto = SX::GetSiteImg('events.subpage.jpg');

// ----------
// Get events
// ----------

$query = 'Select * from  ssp_ev  '     
	   . 'where evEinddatum >= current_date()  '
	   . 'order by evEinddatum ';

if (!$db->Query($query)) { 
  return;
} 

// --------------
// Foto & headers
// --------------

echo "<div class='container'><div class='row' style='padding: 20px'>";

echo '<h1>Evenementen</h1>';

echo "</div>";

echo "<div class='row' style=''padding: 20px'>";

echo '<div class="col-2 d-none d-lg-block">';
	echo $foto;
echo '</div>';

echo '<div class="col-10">';

    echo " <table class=\"table table-bordered\">";

    echo "<thead>";
    echo "<tr>";
    echo "<th style='min-width: 150px'>Datum</th>";
    echo "<th>Tijd</th>";
    echo "<th>Omschrijving</th>";
    echo "</tr>";
    echo "</thead>";

    echo "<tbody>";

    while ($evRec = $db->Row()){

        $datumE = SX_tools::EdtDate($evRec->evEinddatum, '%a %d %b %Y');

        if ($evRec->evStartdatum > '0000-00-00') {
            $datumE = SX_tools::EdtDate($evRec->evStartdatum, '%a %d %b %Y') . ' t/m </br>' .
                SX_tools::EdtDate($evRec->evEinddatum, '%a %d %b %Y');
        }

        if ($evRec->evDatumText > ' '){
            $datumE = $evRec->evDatumText;
        }

        $tijd = $evRec->evTijdText;

        $omschrijving = $evRec->evOmschrijving;

        if ($evRec->evExtURL > ' ')
            $omschrijving = "<a target='_blank' href='$evRec->evExtURL'>$omschrijving</a>";

        if ($evRec->evArtikel > 0)
            $omschrijving = SX_content::getArticleLink($evRec->evArtikel,$evRec->evOmschrijving);


        echo "<tr>";

            echo "<td>$datumE</td>";
            echo "<td>$tijd</td>";
            echo "<td>$omschrijving</td>";

        echo "</tr>";

    }

    echo "</tbody>";
    echo "</table>";



echo '</div></div></div>';


?>