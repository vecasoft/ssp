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

// ------------------------------------
// Get "volgende wedstrijd" info string
// ------------------------------------

$volgendeWedstrijdId = SSP_ploegen::GetVolgendeWedstrijd($eersteElf, 4, true);

if ($volgendeWedstrijdId > 0) {
	$volgendeWedstrijd = SX::getDotBlue()
					   . ' <b> Volgende wedstrijd: </b>'
					   . SSP_wedstrijden::GetWedstrijdInfoString($volgendeWedstrijdId,4);
	$volgendeWedstrijdR = SX::getDotBlue()
					   . ' <b> Volgende wedstrijd: </b>'
					   . SSP_wedstrijden::GetWedstrijdInfoString($volgendeWedstrijdId,5);
					   
}

else {
 	$volgendeWedstrijd = SX::getDotBlue()
					   . ' <b> Volgende wedstrijd: </b>'
					   . 'Geen wedstrijd gepland';
	
	$volgendeWedstrijdR = $volgendeWedstrijd;

}	

// -----------------------------
// Get "statische links" seniors
// -----------------------------

$statischeLinks = SX::getDotBlue()
				. '&nbsp;'
				. SX_menu::GetMenuLINK('UITSLAGEN', 'Uitslagen en Stand' , 'class="discretelink"')
				. '&nbsp;-&nbsp;'
				. SX_menu::GetMenuLINK('SENIORS_COMPETITIE', 'Speelkalender' , 'class="discretelink"');

// --------------------
// begin omvattende div
// --------------------

echo '<div style="float: left; width: 99%; padding-left: 0px; padding-bottom: 1px; margin-bottom: 5px">';


	// -----
	// Title
	// -----
	
	echo '<div class="frontpage_header">';
		echo '<h2 style="color: white; margin: 0px; padding-bottom: 3px; padding-top: 3px; padding-left: 3px">' . 'VOETBAL' . '</h2>';
	echo '</div>';
	
	echo '<div class="frontpage_border">';
	
	// -----
	// Image 
	// -----

	echo '<div style="float: left; padding: 0px; margin-top: 5px; margin-left: 3px; margin-right: 10px; margin-bottom: 0px">';
		echo SX::GetSiteImg('voetbal.frontpage.jpg');
	echo '</div>';	
	
	// -------------------------
	// block right next to image
	// -------------------------
	
	echo '<div style="float: left; padding-top: 0px; margin-bottom: 0px; width: 230px;">';
	
		echo '<h2 style="margin-top: 5px; margin-bottom: 0px; padding: 0px">Seniors</h2>';
	
		// -----------------------------------
		// 1) Laatste miniverslag eerste ploeg
		// -----------------------------------
		
		// ophalen laatste wedstrijd met verslag (wedstrijd minder dan 1 maand geleden...
		$query 	= 'Select * from ssp_vw ' 
				. 'Where vwPloeg = ' . $eersteElf . ' '
				. 'and date(vwDatumTijd) >= CURRENT_DATE - INTERVAL 2 MONTH '
				. 'and date(vwDatumTijd) <= CURRENT_DATE + INTERVAL 1 DAY '
				. 'and vwStatus = "GS" '
				. 'and vwVerslagKort > " " '
				. 'order by vwDatumTijd desc';
		
		if ( ($db->Query($query)) && ($vwRec = $db->Row()) ) { 
			echo '<div style="margin-top: 0px; padding-top: 5px">';
				echo SX::getDotBlue();
				echo '&nbsp;<b>';
				echo SSP_wedstrijden::GetWedstrijdInfoString($vwRec->vwId, 3);
				echo '</b>';
				echo '<div style="padding-top: 5px">'. nl2br($vwRec->vwVerslagKort) . '</div>';
			echo '</div>';
			
			$rechtsFilled = true;

		} 
	
		if ($rechtsFilled != true) {
			echo '<div style="margin-top: 5px">';
			echo $volgendeWedstrijdR;
			echo '</div>';
			$volgendeWedstrijdDisplayed = true;
		}
		
		if ($rechtsFilled != true) {
			echo '<div style="margin-top: 5px">';
			echo $statischeLinks;
			echo '</div>';
			$statischeLinksDisplayed = true;
		}

	
	
	echo '</div>';
	
	// ------------------------------------------
	// Display "volgende wedstrijd" onder de foto
	// ------------------------------------------
	
	if ($volgendeWedstrijdDisplayed == false) {
		echo '<div style="clear: both; margin-left: 5px; padding-top: 5px">';
		echo $volgendeWedstrijd;
		echo '</div>';
	}
	
	// ----------------------------------------
	// Display "statische links " onder de foto
	// ----------------------------------------
	
	if ($statischeLinksDisplayed == false) {
		echo '<div style="clear: both; margin-left: 5px; margin-top: 5px">';
		echo $statischeLinks;
		echo '</div>';
	}
	
	// =========
	// J E U G D
	// ==========
	
	echo '<div style="padding-left: 5px; clear: both; margin: 0px; padding-bottom: 5px; padding-top: 8px"><h2 style="margin: 0px; padding: 0px">Jeugd</h2></div>';
	
	// ----------------------
	// Artikels (Nieuws jeugd)
	// ----------------------
	
	$query = "Select * from sx_ar_articles where arCat = '*VOETBAL_NIEUWS_JEUGD' and arActive = 1 order by arSort, arId desc";
	
	if (! $db->Query($query))
		echo $query;
	else;
		while ($arRec = $db->Row()) {
		
			$artikelLink = SX::getDotBlue()
						. '&nbsp;'
						. SX_content::getArticleLink($arRec->arId, $arRec->arTitle, 'class=discretelink');
		
			echo '<div style="clear: both; padding-top: 5px; padding-left: 5px">';
			echo $artikelLink;
			echo '</div>';
				
		}
	// ------------------------------
	// Link Openingsuren secretariaat
	// ------------------------------
	
	$linkTekst = "Openingsuren secretariaat";
  
	$statischeLinks = SX::getDotBlue()
					. '&nbsp;'
					. SX_menu::GetMenuLINK('KAL_SECRETARIAAT', $linkTekst , 'class="discretelink"');
		  
	echo '<div style="clear: both; padding-top: 5px; padding-left: 5px">';
	echo $statischeLinks;
	echo '</div>';
	
	// --------------
	// Link Tornooien
	// --------------

	$query	= "SELECT * FROM ssp_cl_et "
			. "WHERE date(etDatum)>= CURRENT_DATE - INTERVAL 7 DAY ";
	  
	if ( ($db->Query($query)) && ($etRec = $db->Row()) ) { 
	
		$linkTekst = "Overzicht geplande Tornooien";
	  
		$statischeLinks = SX::getDotBlue()
						. '&nbsp;'
						. SX_menu::GetMenuLINK('TORNOOIEN', $linkTekst , 'class="discretelink"');
			  
		echo '<div style="clear: both; padding-top: 5px; padding-left: 5px">';
		echo $statischeLinks;
		echo '</div>';
	
	}
	

	// --------------------------
	// Wedstrijdverslagen (jeugd)
	// --------------------------

	echo '<div style="clear: both; padding-top: 5px; padding-left: 5px">';
	echo SX::getDotBlue() . '&nbsp;' . 'Recente wedstrijdverslagen';
	echo '</div>';


	$query = 'Select * from ssp_vw ' 
		   . 'Where vwPloeg <> ' . $eersteElf . ' '
		   . 'and date(vwDatumTijd) >= CURRENT_DATE - INTERVAL 31 DAY ' // SHOULD BE 31
		   . 'and date(vwDatumTijd) <= CURRENT_DATE + INTERVAL 1 DAY '
		   . 'and vwIsVerslag = 1 '
		   . 'and vwVerslagStatus = "OK" '
		   . 'order by vwDatumTijd desc ' 
		   . 'limit 12';
		   
	if ($db->Query($query)) {
		
		$i = 0;
			
		while ($vwRec = $db->Row()) { 
		
			$ploegNaam = SSP_ploegen::GetNaam($vwRec->vwPloeg , '*NAAMKORT');
			$datumWedstrijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%d/%m');
			$colorBox = SSP_ploegen::GetKleurCodeBox($vwRec->vwPloeg);
			
			$verslagLink = SSP_settings::GetLink('VOETBALVERSLAG', $ploegNaam . $colorBox .' (' . $datumWedstrijd . ') : ' . $vwRec->vwVerslagTitel , 'class=discretelink', $vwRec->vwId );
		
			echo '<div style="padding-top: 5px; padding-left: 15px; font-weight: bold;">' . $verslagLink . '</div>';
		
		}
		
	}
	
	// ------------------
	// Link Ploegpagina's
	// ------------------

	$linkTekst = "Ploegpagina's";

	$statischeLinks = SX::getDotBlue()
					. '&nbsp;'
					. SX_menu::GetMenuLINK('PLOEGPAGINASJEUGD', $linkTekst , 'class="discretelink"');

	echo '<div style="clear: both; padding-top: 5px; padding-left: 5px">';
	echo $statischeLinks;
	echo '</div>';
	
	// -----------------------
	// Link seizoens-overzicht
	// -----------------------

	$linkTekst = "Wedstrijden: Seizoens-overzicht (Met export)";

	$statischeLinks = SX::getDotBlue()
					. '&nbsp;'
					. SX_menu::GetMenuLINK('WEDSTRIJDKALENDER', $linkTekst , 'class="discretelink"');
			
	echo '<div style="clear: both; padding-top: 5px; padding-left: 5px">';
	echo $statischeLinks;
	echo '</div>';

	// ================================================
	// W E D S T R I J D K A L E N D E R ( 7 D A A G S)
	//=================================================
	
	echo '<div style="padding-left: 5px; clear: both; margin: 0px; padding-bottom: 5px; padding-top: 8px"><h2 style="margin: 0px; padding: 0px">Wedstrijdkalender (7 dagen)</h2></div>';

	echo '<div style="padding-left: 5px">'; // wedstrijdkalender...
	
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

	echo SX::getDotBlue() . '&nbsp;' . 'Thuis-wedstrijden';

	echo '<div style="clear: both; padding-left: 15px; padding-top: 0px; margin-top: 0px; ">'; // THUIS-wedstrijden

	$query = 'Select * from ssp_vw ' 
		   . 'Where '
		   . 'date(vwDatumTijd) >= ' . $fromDate . ' '
		   . 'and date(vwDatumTijd) <= ' . $toDate . ' '
		   . 'and ( vwstatus = "TS" or vwStatus = "GS") ' 
		   . 'and vwUitThuis = "T" '
		   . 'order by vwDatumTijd'; 

	if ($db->Query($query)) {
		
		$i = 0;
			
		while ($vwRec = $db->Row()) { 

			$i++;

			echo '<div style="clear: both; padding-top: 2px; padding-bottom: 2px">';

				$toolTip = SSP_wedstrijden::GetWedstrijdExtraInfoTooltip($vwRec->vwId);
		
				$vwType = '';
				if ($vwRec->vwType != 'CW')
					$vwType = '(' . SSP_wedstrijden::GetWedstrijdTypeOmschrijving($vwRec->vwType, 'K') . ')';


				$ploegNaam2 = SSP_ploegen::GetNaam($vwRec->vwPloeg , '*NAAMKORT');
				$ploegNaam = SSP_ploegen::GetPloegPaginaLink($vwRec->vwPloeg, $ploegNaam2, 'class="discretelink"');
				
				$kleurCodeBox = SSP_ploegen::GetKleurCodeBox($vwRec->vwPloeg);
				
				$datumWedstrijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d/%m - %H:%M');
		
				echo '<div style="float: left">' . $datumWedstrijd . ' ' . $ploegNaam . $kleurCodeBox . ' - ' . $vwRec->vwTegenstander . ' ' . $vwType . '</div>' . '<div style="float: left; margin-left: 5px">' . $toolTip . '</div>';
			
			echo '</div>';
			
		}
		
	}

	echo '</div>'; // THUIS-wedstrijden
	
	echo "<br style='clear: both' />";
	echo SX::getDotBlue() . '&nbsp;' . 'Uit-wedstrijden';

	echo '<div style="clear: both; padding-left: 15px; padding-top: 0px; margin-top: 0px; ">'; // UIT-wedstrijden

	$query = 'Select * from ssp_vw ' 
		   . 'Where '
		   . 'date(vwDatumTijd) >= ' . $fromDate . ' '
		   . 'and date(vwDatumTijd) <= ' . $toDate . ' '
		   . 'and ( vwstatus = "TS" or vwStatus = "GS") ' 
		   . 'and vwUitThuis = "U" '
		   . 'order by vwDatumTijd'; 

	if ($db->Query($query)) {
		
		$i = 0;
			
		while ($vwRec = $db->Row()) { 

			$i++;

			echo '<div style="clear: both; padding-top: 2px; padding-bottom: 2px">';
			
				$toolTip = SSP_wedstrijden::GetWedstrijdExtraInfoTooltip($vwRec->vwId);
		
				$vwType = '';
				if ($vwRec->vwType != 'CW')
					$vwType = '(' . SSP_wedstrijden::GetWedstrijdTypeOmschrijving($vwRec->vwType, 'K') . ')';


				$ploegNaam2 = SSP_ploegen::GetNaam($vwRec->vwPloeg , '*NAAMKORT');
				$ploegNaam = SSP_ploegen::GetPloegPaginaLink($vwRec->vwPloeg, $ploegNaam2, 'class="discretelink"');
				
				$kleurCodeBox = SSP_ploegen::GetKleurCodeBox($vwRec->vwPloeg);
				
				$datumWedstrijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d/%m - %H:%M');
		
				echo '<div style="float: left">' . $datumWedstrijd . ' ' . $vwRec->vwTegenstander . ' - ' . $ploegNaam . $kleurCodeBox . ' ' . $vwType . '</div>' . '<div style="float: left; margin-left: 5px">' . $toolTip . '</div>';
			
			
			echo '</div>';
		}
		
	}

	echo '</div>'; // UIT-wedstrijden	

	
	// ---------
	// Tornooien
	// ---------

	$query	= "Select * FROM ssp_cl_et "
	        . "INNER JOIN sx_ta_tables ON taTable = 'VOETBAL_CAT' AND taCode = etVoetbalCat "
			. "Where date(etDatum)>= CURRENT_DATE - INTERVAL 1 DAY "
			. "  and date(etDatum)<= CURRENT_DATE + INTERVAL 7 DAY "
			. "  and etStatus <> 'AFGELAST' and  etStatus <> 'FORFAIT' "
			. "Order By etDatum, taSort";
			
	if ($db->Query($query) && $db->RowCount() >= 1) {
		echo "<br style='clear: both' />";
		echo SX::getDotBlue() . '&nbsp;' . 'Tornooien';
		
		echo '<div style="clear: both; padding-left: 15px; padding-top: 0px; margin-top: 0px; ">'; // TORNOOIEN
		
			while ($etRec = $db->Row()) { 
			
				echo '<div style="padding-top: 2px; padding-bottom: 2px">';

							
					$datumTornooi = SX_tools::EdtDate($etRec->etDatum, '%a %d/%m');
					
					$toolTip = '';
					$tornooiInfo = $etRec->etTornooiInfo;
					
					$documenten = $etRec->etDocumenten;
		  
					$docs = json_decode($documenten);
					$i = 0;
		  
					if ($docs) {
					
						foreach ($docs as $doc) {
			  
							if ($i > 0 || $tornooiInfo > ' ')
								$tornooiInfo .= '<br/>';
								
							if ($i == 0)
								$tornooiInfo .= '<br/><b>Documenten:</b><br/>';
		
							$tornooiInfo .= "<a href='$doc->name' target='_blank'>$doc->usrName</a>";
			  
							$i++;
	  
						}
		  
					}	
			
					
					if ($tornooiInfo > ' ') 					
						$toolTip = SX_tools::CrtTooltip($tornooiInfo, '', 'Info');
			
					$cat = $etRec->etVoetbalCat;
					
					if ($cat ==  'G')
						$cat = 'G-Team';
					
			
					echo $datumTornooi . " " . $cat . " op: " . SSP_clubs::GetNaam($etRec->etClub, true, 'class=discretelink') . " " . $toolTip;
				
				echo '</div>';		
		
			
			}
		echo '</div>';
	
	
	}
			
	
	echo '</div>'; // wedstrijdkalender...
	
// --------------------
// Einde omvattende div
// --------------------

echo '<div style="clear: both; height:5px">&nbsp;</div>';	
echo '</div>';
echo '</div>'; // omvattende div

?>