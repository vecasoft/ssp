<?php

// -------
// Classes
// -------

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("content.class"));

// --------------------
// begin omvattende div
// --------------------

echo '<div style="float: left; width: 99%; padding-left: 0px; padding-bottom: 1px; margin-bottom: 5px">';

	// -----
	// titel
	// -----
		
	echo '<div class="frontpage_header">';
		echo '<h2 style="color: white; margin: 0px; padding-bottom: 3px; padding-top: 3px; padding-left: 3px">' . 'LIDGELD VOETBAL BETALEN' . '</h2>';
	echo '</div>';
	
	echo '<div class="frontpage_border">';

// -------
// Content
// -------

echo "<div style=\"padding: 5px\">";

    echo SX::GetSiteImg('betaald.jpg');

    echo "Gelieve volgende data te respecteren voor betalen lidgelden voetbal:";
    echo "<ul style='margin-top: 10px'>";
    echo "<li>Uiterlijk 15 mei 2019: Voorschot (of volledig bedrag)</li>";
    echo "<li>Uiterlijk 15 juli 2019: Saldo</li>";
    echo "</ul>";

    echo '<a style="width: 200px; margin-top: 10px; margin-left: 75px; text-decoration: none" class="btn btn-success"  href="index.php?app=ela_lidgeld_betalen"><span class="glyphicon glyphicon-euro"></span> Lidgeld betalen</a>';

echo "</div>";
// --------------------
// Einde omvattende div
// --------------------

echo '<div style="clear: both; height:5px">&nbsp;</div>';	
echo '</div>';

echo '</div>'; // omvattende div

?>