<?php 
     class SSP_hab { // define the class
	 
 		// ===================================================================================================
		// Functie: Ophalen bestelbon record
		//
		// In:	- Bestelbon
		//
		// Out: - Record
		//
		// ===================================================================================================
         
        static function GetBestelBonRec($pBestelbon) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_bb_bestelbonnen where bbId = $pBestelbon";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $bbRec = $db->Row())
				return null;
			
		
			return $bbRec;
				
		}
		
 		// ===================================================================================================
		// Functie: Ophalen artikel record
		//
		// In:	- Artikel
		//
		// Out: - Record
		//
		// ===================================================================================================
         
        static function GetArtikelRec($pArtikel) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_ha_artikels where haId = $pArtikel";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $haRec = $db->Row())
				return null;
			
		
			return $haRec;
				
		}	
		
 		// ===================================================================================================
		// Functie: Ophalen Leverancier record
		//
		// In:	- Leveranier
		//
		// Out: - Record
		//
		// ===================================================================================================
         
        static function GetLeverancierRec($pLeverancier) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_hl_leveranciers where hlId = $pLeverancier";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $hlRec = $db->Row())
				return null;
			
		
			return $hlRec;
				
		}	
 		// ===================================================================================================
		// Functie: Build Artikel Lange Naam
		//
		// In:	- Naam
		//		- Stukeenheid
		//		- Stukvolume
		//		- StukVolumeEenheid
		//
		// Out: - Lange naam
		//
		// ===================================================================================================
         
        static function BldHaNaamLang($pNaam,$pStukEenheid, $pStukVolume, $pStukVolumeEenheid ) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$naamLang = $pNaam;
			
			$sqlStat = "Select * from sx_ta_tables where taTable = 'HAB_STUKEENHEID' and taCode = '$pStukEenheid'";
			$db->Query($sqlStat);
			$taRec = $db->Row();
			
			$stukEenheid = $taRec->taName;
			
			if ($pStukEenheid == '*STUK')
				$stukEenheid = '';
		
			
			$sqlStat = "Select * from sx_ta_tables where taTable = 'HAB_VOLUME_EENHEID' and taCode = '$pStukVolumeEenheid'";			
			$db->Query($sqlStat);
			$taRec = $db->Row();
			
			$volumeEenheid = $taRec->taName;

			
			$volumeEenheid = '';
			$volume = 0;
			
			if ($pStukVolumeEenheid == 'L'){
				
				if ($pStukVolume < 1) {
					$volumeEenheid = 'cl';
					$volume = $pStukVolume * 100;
				} else {
					$volumeEenheid = 'L';
					$volume = $pStukVolume;				
				}

			}
			
			if ($pStukVolumeEenheid == 'KG'){
				
				if ($pStukVolume < 1) {
					$volumeEenheid = 'gr';
					$volume = $pStukVolume * 1000;
				} else {
					$volumeEenheid = 'Kg';
					$volume = $pStukVolume;
				}

			}
			
			if ($pStukVolumeEenheid == 'ST'){
				$volumeEenheid = 'St';
				$volume = $pStukVolume;
			}
			
			if ($pStukVolumeEenheid == 'ST' and $pStukVolume == 1)
					$naamLang = $pNaam;
			else {
				
				
				$naamLang = "$pNaam ($stukEenheid";

				
				if ($volumeEenheid)
					$naamLang = "$naamLang $volume $volumeEenheid)";	
				else
					$naamLang = "$naamLang)";
			}
			
			return $naamLang;

		}
		
		
 		// ===================================================================================================
		// Functie: Bereken Netto Eenheidsprijs
		//
		// In:	- AankoopEenheid
		//
		// Out: - Net. Eenheids prijs
		//
		// ===================================================================================================
         
        static function CalcNetEenhPrijs($pAankoopEenheid) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_ae_aankoop_eenheden where aeId = $pAankoopEenheid";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $aeRec = $db->Row())
				return null;
			
			$netPrijs = round(($aeRec->aeEenheidsPrijs - (($aeRec->aeEenheidsPrijs - $aeRec->aeTaks) * ($aeRec->aeKorting/100))),2);
			
			return $netPrijs;
	
		}	
		
 		// ===================================================================================================
		// Functie: Update AankoopEenheid Netto Eenheidsprijs
		//
		// In:	- AankoopEenheid
		//
		// Out: - Net. Eenheids prijs
		//
		// ===================================================================================================
         
        static function UpdAeNetEenhPrijs($pAankoopEenheid) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$netEenhPrijs = self::CalcNetEenhPrijs($pAankoopEenheid);
			
			$sqlStat = "Update hab_ae_aankoop_eenheden set aeNetEenhPrijs = $netEenhPrijs where aeId = $pAankoopEenheid";
			$db->Query($sqlStat);
			
			return $netEenhPrijs;
	
		}	
		
 		// ===================================================================================================
		// Functie: Update AankoopEenheid Naam
		//
		// In:	- AankoopEenheid
		//
		// Out: - Naam
		//
		// ===================================================================================================
         
        static function UpdAeNaam($pAankoopEenheid) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_ae_aankoop_eenheden where aeId = $pAankoopEenheid";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $aeRec = $db->Row())
				return null;			
			
			$eenheidType = $aeRec->aeEenheidType;
			
			
			$artikel = $aeRec->aeArtikel;
			$sqlStat = "Select * from hab_ha_artikels where haId = $artikel";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $haRec = $db->Row())
				return null;	
			
			$sqlStat = "Select * from sx_ta_tables where taTable = 'HAB_AANKOOPEENHEID_TYPE' and taCode = '$eenheidType'";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $taRec = $db->Row())
				return null;


			
			$inhoudEenheid = '';		
			
			if ($haRec->haStukVolumeEenheid == 'L') {
				
				$inhoudEenheid = 'CL';
				$inhoudperStuk = $haRec->haStukVolume * 100;
				
				if ($haRec->haStukVolume >= 1) {
					$inhoudEenheid = 'L';
					$inhoudperStuk = $haRec->haStukVolume * 1;					
				}
			}
			
			if ($haRec->haStukVolumeEenheid == 'KG') {
				
				$inhoudEenheid = 'gr';
				$inhoudperStuk = $haRec->haStukVolume * 1000;
				
				if ($haRec->haStukVolume >= 1) {
					$inhoudEenheid = 'Kg';
					$inhoudperStuk = $haRec->haStukVolume * 1;					
				}
			}	
			
			$naamExt = '';
			
			if ($aeRec->aeAantalStuks > 1)
				$naamExt = $aeRec->aeAantalStuks;
			
			if ($inhoudEenheid <= ' ' and  $aeRec->aeAantalStuks > 1)
				$naamExt = $naamExt . ' st.';
			else if ($aeRec->aeAantalStuks == 1)
				$naamExt = $naamExt . " $inhoudperStuk $inhoudEenheid";
			else if ($aeRec->aeAantalStuks > 1)
				$naamExt = $naamExt . " x $inhoudperStuk $inhoudEenheid";
			
			$naam = $taRec->taName;
			
			if ($naamExt and trim($naamExt) > ' ')
				$naam = "$naam ( $naamExt )";
			
			$volumePerEenheid = $haRec->haStukVolume * $aeRec->aeAantalStuks;
			$volumeEenheid = $haRec->haStukVolumeEenheid;
			
			
			$volumePerEenheidE = number_format($volumePerEenheid,3, ',', '.');
			$volumePerEenheidE = rtrim($volumePerEenheidE, '0');
			$volumePerEenheidE = rtrim($volumePerEenheidE, ',');
			
			$sqlStat = "Select * from sx_ta_tables where taTable = 'HAB_VOLUME_EENHEID' and taCode = '$volumeEenheid'";
			$db->Query($sqlStat);
			$taRec = $db->Row();
			$volumeEenheidName = $taRec->taName;
			
			$volumePerEenheidEdit = "$volumePerEenheidE $volumeEenheidName";
			
			$sqlStat = "Update hab_ae_aankoop_eenheden set aeNaam = '$naam', aeVolumePerEenheid = $volumePerEenheid, aeVolumeEenheid = '$volumeEenheid', aeVolumePerEenheidEdit = '$volumePerEenheidEdit' where aeId = $pAankoopEenheid";
			$db->Query($sqlStat);
			
			return $netEenhPrijs;
	
		}	
		
 		// ===================================================================================================
		// Functie: Opvullen alle prijsvelden van de bestellijn.
		//
		// In:	- Bestellijn
		//
		// Out: - None...
		//
		// ===================================================================================================
         
        static function FillBestellijnPrijsVelden($pBestellijn) {  
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Select * From hab_bl_bestellijnen where blId = $pBestellijn";
			$db->Query($sqlStat);
			$blRec = $db->Row();

			$artikel = $blRec->blArtikel;
			
			$sqlStat = "Select * From hab_ha_artikels where haId = $artikel";
			$db->Query($sqlStat);
			$haRec = $db->Row();
			
			$eenhPrijs = self::CalcNetEenhPrijs($blRec->blAankoopEenheid);
			
			$bedragExBtw = $eenhPrijs * $blRec->blAantal;
			$btwPerc = $haRec->haBtwPerc;
			$bedragBTW = round(($bedragExBtw / 100 )* $btwPerc,3);
			$bedragTotaal = $bedragExBtw + $bedragBTW;
			
			$sqlStat = "Update hab_bl_bestellijnen set blBedragExBtw = $bedragExBtw, blBtwPerc = $btwPerc, blBedragBtw = $bedragBTW, blBedragTotaal = $bedragTotaal where blId = $pBestellijn";
			$db->Query($sqlStat);	

			self::SetBestelbonBedragen($blRec->blBestelbon);
		
		}
 				
 		// ===================================================================================================
		// Functie: Bijwerken bedragen bestelbon
		//
		// In:	- Bestelbon

		// Out: Niets
		//
		// ===================================================================================================
         
        static function SetBestelbonBedragen($pBestelbon) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Select * from hab_bl_bestellijnen where blBestelbon = $pBestelbon";
			$db->Query($sqlStat);	
			
			$bedragExBtw = 0;
			$bedragBtw = 0;
			
			while ($blRec = $db->Row()) {
				
				$bedragExBtw += $blRec->blBedragExBtw;
				$bedragBtw += $blRec->blBedragBtw;				
				
				
			}
			
			$bedragTotaal = $bedragExBtw + $bedragBtw;
			
			$sqlStat = "Update hab_bb_bestelbonnen Set bbBedragExBtw = $bedragExBtw, bbBedragBtw = $bedragBtw, bbBedragTotaal = $bedragTotaal where bbId = $pBestelbon";

			$db->Query($sqlStat);			
					
		}			
 		// ===================================================================================================
		// Functie: Ophalen bestelbon record
		//
		// In:	- Bestelbon
		//
		// Out: - Record
		//
		// ===================================================================================================
         
        static function GetAankoopEenheidRec($pAankoopEenheid) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_ae_aankoop_eenheden where aeId = $pAankoopEenheid";
			
			if (! $db->Query($sqlStat))
				return null;
				
			if (! $aeRec = $db->Row())
				return null;
			
		
			return $aeRec;
				
		}	
			
 		// ===================================================================================================
		// Functie: Ophalen artikels van een leverancier
		//
		// In:	- Leverancier
		//		- Locatie
		//
		// Out: - Array van artikels
		//
		// ===================================================================================================
         
        static function GetLeverancierArtikels($pLeverancier, $pLocatie = 0) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from hab_ha_artikels where haLeverancier = $pLeverancier and haRecStatus = 'A'";
			
			$artikels = array();
			
			if (! $db->Query($sqlStat))
				return $artikels;
				
			while ($haRec = $db->Row()) {
				
				$chkArtikel = true;
				
				if ($pLocatie > 0)
					$chkArtikel = self::CheckArtikelLocatie($pLocatie, $haRec->haId);
				
				if ($chkArtikel == true)
					$artikels[] = $haRec->haId;

			}
		
			return $artikels;
				
		}
 		// ===================================================================================================
		// Functie: Check Artikel-Locatie relatie
		//
		// In:	- Locatie
		//		- Artikel-Locatie
		//
		// Out: - OK?
		//
		// ===================================================================================================
         
        static function CheckArtikelLocatie($pLocatie, $pArtikel) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			$sqlStat = "Select count(*) as aantal from hab_al_artikel_locaties where alArtikel = $pArtikel and alLocatie = $pLocatie";
			$db->Query($sqlStat);
			
			if ($alRec = $db->Row()){
			
				if ($alRec->aantal == 1)
					return true;
			
			}
			
			$sqlStat = "Select count(*) as aantal from hab_al_artikel_locaties where alArtikel = $pArtikel";			
			$db->Query($sqlStat);
			
			if ($alRec = $db->Row()){

			
				if ($alRec->aantal == 0)
					return true;
			
			}			

			
			return false;

		}
		
 		// ===================================================================================================
		// Functie: Mag bestelbon gewist worden?
		//
		// In:	- Bestelbon
		//
		// Out: - OK? false/true
		//
		// ===================================================================================================
         
        static function CheckBestelbonDeleteAllowed($pBestelbon) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			$sqlStat = "Select count(*) as aantal from hab_bl_bestellijnen where blBestelbon = $pBestelbon";
			$db->Query($sqlStat);
			
			if ($blRec = $db->Row()){
			
				if ($blRec->aantal > 0)
					return false;
			
			}
			
			
			return true;

		}		
		
 		// ===================================================================================================
		// Functie: Mag artikel gewist worden?
		//
		// In:	- Artikel
		//
		// Out: - OK? false/true
		//
		// ===================================================================================================
         
        static function CheckArtikelDeleteAllowed($pArtikel) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			$sqlStat = "Select count(*) as aantal from hab_ae_aankoop_eenheden where aeArtikel = $pArtikel";
			$db->Query($sqlStat);
			
			if ($aeRec = $db->Row()){
			
				if ($aeRec->aantal > 0)
					return false;
			
			}
			
			$sqlStat = "Select count(*) as aantal from hab_al_artikel_locaties where alArtikel = $pArtikel";
			$db->Query($sqlStat);
			
			if ($alRec = $db->Row()){
			
				if ($alRec->aantal > 0)
					return false;
			
			}
						
			$sqlStat = "Select count(*) as aantal from hab_bl_bestellijnen where blArtikel = $pArtikel";
			$db->Query($sqlStat);
			
			if ($blRec = $db->Row()){
			
				if ($blRec->aantal > 0)
					return false;
			
			}			
			
			return true;

		}			
 		// ===================================================================================================
		// Functie: Get file path Bestelbond
		//
		// In:	- Bestelbon
		//
		// Out: - OK?
		//
		// ===================================================================================================
         
        static function GetBestelbonPath($pBestelbon) {  		
			
			$rootDir = $_SESSION["SX_BASEPATH"];
			
			$filePath = $rootDir . '/_generated_files/hab/bestelbon_' . $pBestelbon . '.pdf';
			
			return $filePath;
		
		}
		
 		// ===================================================================================================
		// Functie: Zet status alle bestellijnen op basis status bestelbon
		//
		// In:	- Bestelbon
		//		- Status
		//
		// Out: Niets
		//
		// ===================================================================================================
         
        static function SetStatusBestellijnen($pBestelbon, $pBestelStatus) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Update hab_bl_bestellijnen set blBestelStatus = '$pBestelStatus' where blBestelbon = $pBestelbon";
			$db->Query($sqlStat);	
					
		}
		
 		// ===================================================================================================
		// Functie: Zet status bestelbon op basis status bestellijnen
		//
		// In:	- Bestelbon
		//
		// Out: - Bestelstatus bestelbon
		//
		// ===================================================================================================
         
        static function SetStatusBestelbon($pBestelbon) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Select * From hab_bl_bestellijnen where blBestelbon = $pBestelbon";
			$db->Query($sqlStat);
			
			$bestelStatus = '*NONE';
			
			while ($blRec = $db->Row()){
				
				if (($bestelStatus == '*NONE') or ($bestelStatus == $blRec->blBestelStatus))
					$bestelStatus = $blRec->blBestelStatus;
				else if (($bestelStatus == '*OPEN') and ($blRec->blBestelStatus == '*BESTELD'))
					$bestelStatus = '*PART_BESTELD';
				else if (($bestelStatus == '*BESTELD') and ($blRec->blBestelStatus == '*OPEN'))
					$bestelStatus = '*PART_BESTELD';				
				else if (($bestelStatus == '*GELEVERD') and ($blRec->blBestelStatus != '*GELEVERD'))
					$bestelStatus = '*PART_GELEVERD';			
				else if (($bestelStatus != '*GELEVERD') and ($blRec->blBestelStatus == '*GELEVERD'))
					$bestelStatus = '*PART_GELEVERD';				
			}
			
			$sqlStat = "update hab_bb_bestelbonnen set bbBestelStatus = '$bestelStatus' where bbId = $pBestelbon";
			$db->Query($sqlStat);			
			
			return $bestelStatus;
					
		}
 		// ===================================================================================================
		// Functie: Zet status bestellijn op "geleverd"
		//
		// In:	- Bestellijn
		//
		// Out: - Bestelstatus bestelbon
		//
		// ===================================================================================================
         
        static function SetBestellijnOpGeleverd($pBestellijn) {  
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Select * From hab_bl_bestellijnen where blId = $pBestellijn";
			$db->Query($sqlStat);

			$blRec = $db->Row();
			
			if ($blRec->blBestelStatus == '*BESTELD' or $blRec->blBestelStatus == '*PART_GELEVERD') {
	
				$sqlStat = "Update hab_bl_bestellijnen set blBestelStatus = '*GELEVERD', blAantalGeleverd = blAantal where blId = $pBestellijn";
	
				$db->Query($sqlStat);

			}
			
			$bestelStatus = self::SetStatusBestelbon($blRec->blBestelbon);
			
			return $bestelStatus;
		
		}
		
  		// ===================================================================================================
		// Functie: Aanpassen aantal geleverd
		//
		// In:	- Bestellijn
		//
		// Out: - Bestelstatus bestelbon
		//
		// ===================================================================================================
         
        static function SetAantalGeleverd($pBestellijn, $pAantalGeleverd) {  
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Select * From hab_bl_bestellijnen where blId = $pBestellijn";
			$db->Query($sqlStat);

			$blRec = $db->Row();
			
			if ($pAantalGeleverd >= $blRec->blAantal)
				$sqlStat = "Update hab_bl_bestellijnen set blBestelStatus = '*GELEVERD', blAantalGeleverd = $pAantalGeleverd where blId = $pBestellijn";
			else if ($pAantalGeleverd == 0)
				$sqlStat = "Update hab_bl_bestellijnen set blBestelStatus = '*BESTELD', blAantalGeleverd = $pAantalGeleverd where blId = $pBestellijn";
			else if ($pAantalGeleverd < $blRec->blAantal)
				$sqlStat = "Update hab_bl_bestellijnen set blBestelStatus = '*PART_GELEVERD', blAantalGeleverd = $pAantalGeleverd where blId = $pBestellijn";
		
			$db->Query($sqlStat);

			$bestelStatus = self::SetStatusBestelbon($blRec->blBestelbon);
			
			return $bestelStatus;
		
		}
	
		
 		// ===================================================================================================
		// Functie: Plaats Bestelling
		//
		// In:	- Bestelbon
		//		- USER-id
		//
		// Out: - OK?
		//
		// ===================================================================================================
         
        static function PutBestelling($pBestelbon, $pUserId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("tools.class"));
			
			// --------------------------
			// Enkel indien belstellijnen
			// --------------------------
			
			$sqlStat = "Select count(*) as aantal From hab_bl_bestellijnen where blBestelbon = $pBestelbon";
			$db->Query($sqlStat);
			
			$blRec = $db->Row();
			
			if ($blRec->aantal <= 0)
				return "Geen bestelling geplaatst - geen bestellijnen gevonden";

			// ----------------------
			// Ophalen bestelbon info
			// ----------------------

			$sqlStat = "Select * from hab_bb_bestelbonnen where bbId = $pBestelbon";
			$db->Query($sqlStat);
			$bbRec = $db->Row();

			
			// ----------------------------------
			// Zet status bestelbon op "*BESTELD"
			// ----------------------------------
			
			$sqlStat = "Update hab_bb_bestelbonnen set bbBestelStatus = '*BESTELD', bbDatumBestelling = now() where bbId = $pBestelbon";
			$db->Query($sqlStat);
			
			$sqlStat = "Update hab_bl_bestellijnen set blBestelStatus = '*BESTELD' where blBestelbon = $pBestelbon and blBestelStatus = '*OPEN'";
			$db->Query($sqlStat);	
			
			
			if ($bbRec->bbLeverancierMail <= " ")
				return "Status gewijzigd naar BESTELD - Geen mail gestuurd wegens geen mail-adres";
			
			// -------------------
			// Generated bestelbon
			// -------------------
			
			$_SESSION["bbid"] = $pBestelbon;
									
			$rootDir = $_SESSION["SX_BASEPATH"];
			$runPath = $rootDir . '/hab_bestelbon.php';

			require $runPath;
			
			$bestelbonPath = self::getBestelbonPath($pBestelbon);
					
					
			// --------------------------------
			// Zend bestelmail naar leverancier
			// ---------------------------------
		
			$adRec = null;
			
			if($bbRec->bbContactPersoon > ' ') {
				
				$sqlStat = "Select * from ssp_ad where adCode = '$bbRec->bbContactPersoon'";
				
				$db->Query($sqlStat);
				$adRec = $db->Row();
				
			}
			
			
			$mailTo = $bbRec->bbLeverancierMail;
			
			$fromMail = 'webmeester@schellesport.be';
			if ($adRec->adMail > ' ' )
				$fromMail = $adRec->adMail;
			
			$fromName = 'Schelle Sport';
			if ($bbRec->bbContactPersoon > ' ')
				$fromName = utf8_encode('Schelle Sport - ' . $adRec->adVoornaamNaam);
	
				
			$mailBody 	= 	'Geachte'
						.	'<br/><br/>'
						.	"Schelle Sport plaatste een nieuwe bestelling met bestelnummer: $pBestelbon (zie bijlage)"
						. 	'<br/>'
						.	'<br/>';
						
			if ($bbRec->bbInfo > ' ') {
				
				$mailBody 	.=	'Extra info:<br/><br/>'
							.	utf8_encode(nl2br($bbRec->bbInfo));
				
				
			}
			
			
			$mailBody	.=	'<br/><br/>'
						.	'Sportieve groet, '
						.	'<br/><br/>'
						.	'Schelle Sport'
						. 	'<br/>';
						
			if ($bbRec->bbContactPersoon > ' ') {
				
				$contactPersoon = utf8_encode($adRec->adVoornaamNaam);
				$mailBody .= "<br/>$contactPersoon";
										
			}
			
			if ($adRec->adTel > ' ') {
				
				$contactTel = $adRec->adTel;
				$mailBody .= "<br/>$contactTel";
										
			}		
			
			if ($adRec->adMail > ' ') {
					
				$bccMail = $adRec->adMail;
											
			}
				
			SX_tools::SendMail('Schelle Sport - Nieuwe Bestelbon', $mailBody, $mailTo, $bccMail, $fromMail, $fromName, $bestelbonPath, 'UTF-8');
	
			$sqlStat = "Update hab_bb_bestelbonnen set bbWijzigbaar = 0, bbGemaildNaar = '$mailTo', bbGemaildNaarBCC = '$bccMail' where bbId = $pBestelbon";
			$db->Query($sqlStat);					
				
			return "Status gewijzigd naar BESTELD - Mail met bestelbon gestuurd naar $mailTo";

		}
		
    }
       
?>