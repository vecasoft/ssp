<?php

// -------
// Classes
// -------

include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object     
include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("content.class"));
include_once(SX::GetSxClassPath("fotoalbum.class"));

// -----------------
// Get "homebericht"
// -----------------

$query = "Select * from ssp_hb where hbActief = 1 and hbId = $parm1";
	
if (($db->Query($query)) && ($hbRec = $db->Row() )){ 

	// ---------------
	// Vervolgartikel?
	// ---------------

	$vervolgArtikel = '*NONE';
	
	if ($hbRec->hbArtikelId > 0) 
		$vervolgArtikel = SX_content::getArticleLink($hbRec->hbArtikelId, $hbRec->hbTekstLink);

	// --------------
	// omvattende div
	// --------------
	
	echo '<div style="float: left; width: 99%; padding-left: 0px; padding-bottom: 1px; margin-bottom: 5px">';
	
		
		// -----
		// titel
		// -----
		
		echo '<div class="frontpage_header">';
			echo '<h2 style="color: white; margin: 0px; padding-bottom: 3px; padding-top: 3px; padding-left: 3px">' . $hbRec->hbTitelBoven . '</h2>';
		echo '</div>';
		
		echo '<div class="frontpage_border">';
			
		// ----------
		// Foto-album
		// ----------
	
		if ($hbRec->hbFotoAlbum <> 0 ){
			
			$album = $hbRec->hbFotoAlbum;
			
			if ($hbRec->hbToonTitel == 1) 
				echo '<h2 style="padding: 5px; margin-top: 0px; margin-bottom: 5px">' . $hbRec->hbTitel . '</h2>';
			
			$sqlStat = "Select * from sx_fa_foto_albums where faId = $hbRec->hbFotoAlbum";
			$db->Query($sqlStat);
			$faRec = $db->Row();
			
			$albumDesc = nl2br($faRec->faDescription);
			echo "<div style=\"padding: 5px; text-align: center\">$albumDesc</div>";

			$sqlStat = "Select * from sx_ff_foto_album_fotos where ffAlbum = $hbRec->hbFotoAlbum order by ffSort, ffId";
			$db->Query($sqlStat);
			
			echo "<ul id='album_$album' style=\"display: none\">";
			
			$fotoPath1 = "";
			$fotoBigPath1 = "";
			$fotoCaption1 = "***";	
			
			$volgnummer = 0;
			
			while ($ffRec = $db->Row()) {
				
				$fotoPath = "";
				$fotoBigPath = "";
				$fotoCaption = "";
				
				$fotos = json_decode($ffRec->ffFoto);
				if ($fotos) {
					foreach ($fotos as $foto) {
						
						$fotoPath = SX_tools::GetFilePath($foto->name);
						
					}
				}
				
				$fotosBig = json_decode($ffRec->ffFotoBig);
				if ($fotosBig) {
					foreach ($fotosBig as $foto) {
						
						$fotoBigPath = SX_tools::GetFilePath($foto->name);

		
					}
				}	
				
				if ($fotoBigPath <=  " ")
					$fotoBigPath = $fotoPath;
				
				$fotoCaption = $ffRec->ffCaption;
			
				if ($fotoPath1 <= ' ') {
					
					$fotoPath1 = $fotoPath;
					$fotoBigPath1 = $fotoBigPath;
					$fotoCaption1 = $fotoCaption;	
			
				}
				
				$volgnummer++;
				
				echo "<li data-fotoNummer = $volgnummer title = \"$fotoCaption\" class='album_$album'><a  href=\"$fotoBigPath\" target=\"_blank\"><img src=\"$fotoPath\" title=\"$fotoCaption\"></a></li>";
				
			}
			
			echo "</ul>";

            echo "<div class='album_left_$album' style=\"cursor: pointer; float: left; margin-left: 10px;\"><span style='font-size: 200%; color: #0A529E;' class='glyphicon g glyphicon-circle-arrow-left'></span> </div>";
            echo "<div class='album_right_$album' style=\"cursor: pointer; float: right; padding-right: 10px\"><span style='font-size: 200%; color: #0A529E;' class='glyphicon g glyphicon-circle-arrow-right'></span></div>";
            
            echo "<br style='clear: both'>";

			echo "<div id='albumFoto_$album' style='cursor: pointer; zoom-in; text-align: center; width: 390px'>";
				echo"<a  style=\"cursor: zoom-in\" href=\"$fotoBigPath1\" target=\"_blank\"><img src=\"$fotoPath1\" title=\"$fotoCaption1\"></a>";
			echo "</div>";
			echo "<div id='albumFotoCaption_$album' style=\"width: 390px; text-align: center; font-weight: bold\">";
				echo $fotoCaption1;
			echo "</div>";
			
			echo "<div class='album_left_$album' style=\"cursor: pointer; float: left; margin-left: 10px;\"><span style='font-size: 200%; color: #0A529E;' class='glyphicon g glyphicon-circle-arrow-left'></span> </div>";
			
			$fotoQty = SX_fotoalbum::GetFotoQty($album);
			
			if ($fotoQty == 1)	
				$fotoQtyTxt = "$fotoQty foto's";
			else
				$fotoQtyTxt = "$fotoQty foto's";

			echo "<div class='album_left_$album' style=\"float: left; width: 310px; padding-top: 8px; text-align: center;  font-style: italic;\">Foto <span id=\"fotoNummer_$album\">1</span> / $fotoQty</span> </div>";
			
			echo "<div class='album_right_$album' style=\"cursor: pointer; float: right; padding-right: 10px\"><span style='font-size: 200%; color: #0A529E;' class='glyphicon g glyphicon-circle-arrow-right'></span></div>";

			echo "<br style='clear: both'>";
			
			
			
			
?>

<script>

<?php

echo "$(\"#album_$album > :first-child\").addClass('huidige_foto')";

?>

// -------------
// Volgende foto
// -------------

<?php

echo "$(\".album_right_$album\").click(function(){";

	echo "\$eerste_foto = $(\"#album_$album > :first-child\").html();";
	echo "\$eerste_foto_caption = $(\"#album_$album > :first-child\").attr('title');";
	echo "\$eerste_foto_nummer = $(\"#album_$album > :first-child\").attr('data-fotoNummer');";	
	echo "var listItems = $(\"#album_$album li\");";
?>
	
	$volgende_foto = '*';
	$volgende_foto_caption = '*';	
	
	listItems.each(function(idx, li) {
			
		if ($volgende_foto != "*") {
			$volgende_foto = $(li).html();
			$volgende_foto_caption = $(li).attr('title');
			$volgende_foto_nummer = $(li).attr('data-fotoNummer');
			$(li).addClass('huidige_foto');
			return false;
		}
		
		else {
		
			$huidige_foto = $(li).hasClass('huidige_foto');
		
			if ($huidige_foto) {
				$volgende_foto = "X";
				$(li).removeClass('huidige_foto');
			}
		
		}
		

	});

	if ($volgende_foto == 'X') {
		$volgende_foto = $eerste_foto;
		$volgende_foto_caption = $eerste_foto_caption;
		$volgende_foto_nummer = $eerste_foto_nummer;
<?php				
		echo "$(\"#album_$album > :first-child\").addClass('huidige_foto');";
?>
		
	}

<?php
	echo "$(\"#albumFoto_$album\").html(\$volgende_foto);";
	echo "$(\"#albumFotoCaption_$album\").html(\$volgende_foto_caption);";
	echo "$(\"#fotoNummer_$album\").html(\$volgende_foto_nummer);";
?>
	
})

// ---------------
// Voorgaande foto
// ---------------

<?php

echo "$(\".album_left_$album\").click(function(){";

	echo "\$laatste_foto = $(\"#album_$album > :last-child\").html();";
	echo "\$laatste_foto_caption = $(\"#album_$album > :last-child\").attr('title');";
	echo "\$laatste_foto_nummer = $(\"#album_$album > :last-child\").attr('data-fotoNummer');";	
?>

	$volgende_foto = "*";
	$volgende_foto_caption = '*';	
	
	jQuery.fn.reverse = function() {
		return this.pushStack(this.get().reverse(), arguments);
	};
	
<?php
		echo "var listItems = $(\"#album_$album li\").reverse();";
?>

	listItems.each(function(idx, li) {

			
		if ($volgende_foto != "*") {
			$volgende_foto = $(li).html();
			$(li).addClass('huidige_foto');
			$volgende_foto_caption = $(li).attr('title');
			$volgende_foto_nummer = $(li).attr('data-fotoNummer');
			return false;
		}
		
		else {
		
			$huidige_foto = $(li).hasClass('huidige_foto');
		
			if ($huidige_foto) {
				$volgende_foto = "X";
				$(li).removeClass('huidige_foto');
			}
		
		}
		

	});
	
	
	if ($volgende_foto == 'X') {
		$volgende_foto = $laatste_foto;
		$volgende_foto_caption = $laatste_foto_caption;
		$volgende_foto_nummer = $laatste_foto_nummer;
<?php				
		echo "$(\"#album_$album > :last-child\").addClass('huidige_foto');";
?>
		
	}

<?php
	echo "$(\"#albumFoto_$album\").html(\$volgende_foto);";
	echo "$(\"#albumFotoCaption_$album\").html(\$volgende_foto_caption);";
	echo "$(\"#fotoNummer_$album\").html(\$volgende_foto_nummer);";
?>
	
})
</script>

<?php		
			
			
		}
		
		else {	
		
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
			
			echo '<div style="float: left; padding: 0px; margin-top: 5px; margin-left: 3px; margin-right: 10px; margin-bottom: 0px; width: 150px">';
			
				if ($fotoPath != '*NONE' && $fotoGrootPath == '*NONE') {
					echo '<img style="width: 150px; margin-left: 0px; margin-top: 0px; padding-top: 0px display: block; " src="' . $fotoPath. '">';
				}
				
				if ($fotoPath != '*NONE' && $fotoGrootPath != '*NONE') {
					echo '<a href="' . $fotoGrootPath . '"  target="_blank"  title="Klik om volledige foto te zien">';
					echo '<img style="width: 150px; margin-left: 0px; margin-top: 0px; padding-top: 0px display: block; " src="' . $fotoPath. '">';
					echo '</a>';
				}
				
			
			echo '</div>'; // Foto-div
			
			// ------------------
			// Rechts van de foto
			// ------------------
			
			$titel = '';

			if ($hbRec->hbToonTitel == 1) 
				$titel = $hbRec->hbTitel;

			if ((!empty($hbRec->hbTekstRechts)) || ($hbRec->hbToonTitel == 1)) {
				
				echo '<div style="float: left; padding-top: 5px; width: 230px; margin-bottom: 0px">';
		
				if ($hbRec->hbToonTitel == 1) 
					echo '<h2 style="padding: 0px; margin-top: 0px; margin-bottom: 5px">' . $hbRec->hbTitel . '</h2>';
				
				echo $hbRec->hbTekstRechts;
				
				if (empty($hbRec->hbTekstOnder) && $vervolgArtikel != '*NONE') {
					echo '<div style="padding-top: 5px">'  . $vervolgArtikel . '</div>';
					$vervolgArtikel = '';
				}
							
				echo '</div>';
			
			}		
			
			// -----------------
			// onder van de foto
			// -----------------
		
			if (!empty($hbRec->hbTekstOnder)) {

				echo'<div style="clear:both; padding-top: 5px; padding-left: 5px">';
				echo $hbRec->hbTekstOnder;

				if ($vervolgArtikel != '*NONE') {
					echo '<div style="padding-top: 5px">'  . $vervolgArtikel . '</div>';
					$vervolgArtikel = '';
				}
				
				echo '</div>';

			}
		
		
			echo '<div style="clear: both; height:5px">&nbsp;</div>';	
		
		}
		
		echo '</div>';

	echo '</div>'; // omvattende div
		
}

?>