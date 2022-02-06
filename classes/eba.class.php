<?php 
     class SSP_eba { // define the class
	 
		// ===================================================================================================
		// Functie: Ophalen "adRec"
		//
		// In:	- persoonID
		//
		// Uit:	adRec (or null)
		//
		// ===================================================================================================
         
        Static function db_adRec($pPersoonId) {
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pPersoonId'";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return null;
			}
				
			if (! $adRec = $db->Row()) {
				$db->close();			
				return null;
			}
			else {
				$db->close();			
				return $adRec;				
			}
				 
		}
	 
		// ===================================================================================================
		// Functie: Ophalen "adRec"
		//
		// In:	- artikel ID
		//
		// Uit:	adRec (or null)
		//
		// ===================================================================================================
         
        Static function db_arRec($pArtikelId) {
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return null;
			}
				
			if (! $arRec = $db->Row()) {
				$db->close();			
				return null;
			}
			else {
				$db->close();			
				return $arRec;				
			}
				 
		}	
		
		// ===================================================================================================
		// Functie: Huidig Webshop Seizoen
		//
		// In:	- Geen
		//
		// Uit:	Return-value: Seizoen (vb. 2017-2018)
		//
		// ===================================================================================================
         
        Static function GetHuidigSeizoen() {
			
			// ----------------------
			// Get value from session
			// ----------------------
			
			//$huidigSeizoen = $_SESSION["huidigSeizoen"];
			//if ($huidigSeizoen > ' ')
			//	return $huidigSeizoen;
			
			// --------------------
			// Get value from table
			// --------------------
						
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from sx_ta_tables where taTable = 'EBA_SEIZOENEN' and taAlfaData = '*HUIDIG'";		
									
			if (! $db->Query($sqlStat)) {
				$db->close();
				return '*ERROR';
			}
			
			if ($taRec = $db->Row()) {	
				$_SESSION["huidigSeizoen"] = $taRec->taCode;
				$db->close();
				return $taRec->taCode;
			}
				
			$db->close();
			return '*ERROR';
						
		}	
		
	 
	  	// ===================================================================================================
		// Functie: Maat verplicht 
		//
		// In:	- artikelId = artikel ID
		//
		// Return:  false/true
		//
		// ===================================================================================================
         
        Static function ChkMaatVerplicht($pArtikelId) {  
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return false;
			}
				
			if ($amRec = $db->Row()){
				$db->close();
				return true;
			}
			
			$db->close();
			return false;
		
		
		}
		
	  	// ===================================================================================================
		// Functie: Ophalen artikel-prijs -> Prijs
		//
		// In:	- artikel ID
		//		- Maat
         //     - Type (*WEBSHOP, *CATALOOG, *AANKOOP
		//
		// ===================================================================================================
         
        Static function GetArtikelPrijs($pArtikelId, $pMaat, $pType = '*WEBSHOP') {
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$prijs = 0;
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}
			
			if ($arRec = $db->Row()) {

                $prijs = $arRec->arPrijs;

                if ($pType == '*CATALOOG')
                    $prijs = $arRec->arCataloogPrijs;

                if ($pType == '*WEBSHOP')
                    $prijs = $arRec->arPrijs;

                if ($pType == '*AANKOOP')
                    $prijs = ($arRec->arCataloogPrijs * (1 - ($arRec->arKortingPerc2 / 100))) + $arRec->arKostPrintLogo;


			}


			// ---------------
			// Prijs per maat?
			// ---------------
			
			if ($arRec->arPrijsPerMaat == 1) {
			
				$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikelId and amMaat = '$pMaat'";
				
				if ($db->Query($sqlStat)) {
				
					$amRec = $db->Row();

                    if ($pType == '*WEBSHOP')
						$prijs = $amRec->amPrijs;

                    if ($pType == '*CATALOOG')
                        $prijs = $amRec->amCataloogPrijs;

                    if ($pType == '*AANKOOP')
                        $prijs = ($amRec->amCataloogPrijs * (1 - ($arRec->arKortingPerc2 / 100)))+ $arRec->arKostPrintLogo;
				
				}
				
			
			}

			$db->close();
			return $prijs;
			
			
		}
		
	  	// ===================================================================================================
		// Functie: Ophalen artikel naam
		//
		// In:	- artikel ID
		//
		// ===================================================================================================
         
        Static function GetArtikelNaam($pArtikelId) {  
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
			
			$naam = '???';
			
			if (! $db->Query($sqlStat)) {
				$db->close();
				return '???';
			}
				
			if ($arRec = $db->Row())	
				$naam = $arRec->arNaam;
			
			$db->close();
			return $naam;
		
		}		

	  	// ===================================================================================================
		// Functie: Ophalen lidgeld status -> Lidgeld status  (JA, NEE, NVT, ...)
		//
		// In:	- Klant-id
		//  	- Datum (optioneel)
		//
		// ===================================================================================================
         
        Static function GetLidgeldStatus($pKlantId, $pSeizoen = '') {  
								 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$lidgeldSeizoen = $pSeizoen;
			$huidigSeizoen = self::GetHuidigSeizoen();

			if ($lidgeldSeizoen <= ' ')
				$lidgeldSeizoen = $huidigSeizoen;

			if ($lidgeldSeizoen == $huidigSeizoen) {

				$sqlStat = "Select * from ssp_ad where adCode = '$pKlantId'";
							
				if (! $db->Query($sqlStat)) {
					$db->close();
					return 'NEE';
				}
					
				if ($adRec = $db->Row()) {	
					
					if ($adRec->adKledijMagBesteld == 1) {
						$db->close();
						return "OK";
					}
					if ($adRec->adLidgeldVoldaanVB == 'JA') {
						$db->close();
						return "OK";
					}
					
					$db->close();	
					return $adRec->adLidgeldVoldaanVB;
				}
	
				$db->close();
				return 'NEE';
	
			}
            if ($lidgeldSeizoen < $huidigSeizoen and $lidgeldSeizoen >= '2020-2021') {

                $sqlStat = "Select sum(lbBedrag) as bedrag from ela_lb_lidgeld_betalingen where lbPersoon = '$pKlantId' and lbSeizoen = '$lidgeldSeizoen' ";
                $db->Query($sqlStat);

                if ($lbRec = $db->Row()){

                    if ($lbRec->bedrag > 75)
                        return 'OK';
                    else
                        return 'NEE';

                }

            }

            if ($lidgeldSeizoen < $huidigSeizoen and $lidgeldSeizoen <= '2019-2020') {

				$sqlStat = "Select * from ssp_lv_lidgeldvorigseizoen where lvAdCode = '$pKlantId' and lvSeizoen = '$lidgeldSeizoen' ";
							
				if (! $db->Query($sqlStat)) {
					$db->close();
					return 'NEE';
				}
					
				if ($lvRec = $db->Row()) {	
					
					if ($lvRec->lvKledijMagBesteld == 1) {
						$db->close();
						return "OK";
					}
					if ($lvRec->lvLidgeldStatus == 'JA') {
						$db->close();
						return "OK";
					}
					
					$db->close();					
					return $lvRec->lvLidgeldStatus;
				}
	
				$db->close();
				return 'NEE';
	
			}

            return 'NEE';
			
		}
		
		// ===================================================================================================
		// Functie: Default Leverancier -> Defaul leverancier ID
		//
		// In:	- artikel-ID
		//
		// ===================================================================================================
         
        Static function GetDftLeverancier($pArtikelId) {  
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_al_artikel_leveranciers where alArtikel = $pArtikelId and alDefault = 1";
						
			$Leverancier = 0;			
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}

			while($alRec = $db->Row()) {	
			
				if ($alRec->alDefault == 1) 
					$Leverancier = $alRec->alLeverancier;
					
			}
			
			$db->close();
			return $Leverancier;
	
		
		} 
		// ===================================================================================================
		// Functie: Test of leverancier geldig is voor bepaald artikel
		//
		// In:	- Artikel
		//		- Leveranceir
		//
		// ===================================================================================================
         
        Static function ChkArtLev($pArtikel, $pLeverancier) {  
		
		 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select count(*) as aantal from eba_al_artikel_leveranciers where alArtikel = $pArtikel and alLeverancier = $pLeverancier";

			if (! $db->Query($sqlStat)) {
				$db->close();
				return false;
			}

			$returnVal = false;
			
			if ($alRec = $db->Row()) {	
			
				if ($alRec->aantal > 0)
					$returnVal = true;
					
			}
			
			$db->close();
			return $returnVal;
	
		
		} 	
		
 		// ===================================================================================================
		// Functie: Aanmaken order-header vanaf Webshop
		//
		// In	- UserId 
		//		- Pakket (optioneel)
		//
		// Out	- OrderId
		//
		// ===================================================================================================
         
        Static function crtOrderHeaderFromWebshop($pUserId, $pPakket = 0) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
            include_once(SX::GetClassPath("_db.class"));

            include_once(SX::GetClassPath("efin.class"));

            // ------------------------
            // Ophalen algemene waarden
            // ------------------------

            $curDateTime = date('Y-m-d H:i:s');
            $curDate = date('Y-m-d');
            $seizoen = self::GetHuidigSeizoen();

            // -----------
            // Pakketprijs
            // -----------

            $prijs = 0;

            if ($pPakket){

                $pkRec = SSP_db::Get_EBA_pkRec($pPakket);

                if ($pkRec)
                    $prijs = $pkRec->pkPrijs;

            }


            // ---------------------
            // Aanmaken order-header
            // ---------------------

            $values = array();

            $gm = SSP_efin::GetNextGM('*WEBSHOP');

			$values["ohKlant"] = MySQL::SQLValue($pUserId);
			$values["ohOrderType"] = MySQL::SQLValue("*KLANT");
			$values["ohUserCreatie"] = MySQL::SQLValue($pUserId);				
			$values["ohUserUpdate"] = MySQL::SQLValue($pUserId);
			
			$values["ohOorsprong"] = MySQL::SQLValue("*WEBSHOP");
			
			$values["ohPakket"] = MySQL::SQLValue($pPakket, MySQL::SQLVALUE_NUMBER );
			
			$values["ohTotaalPrijs"] = MySQL::SQLValue($prijs, MySQL::SQLVALUE_NUMBER );

			$values["ohInfo"] = MySQL::SQLValue("");

            $values["ohGm"] = MySQL::SQLValue($gm);

			$values["ohBetaalBedrag1"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );
			$values["ohBetaalBedrag2"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );
			$values["ohBetaalTotaal"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );

			$values["ohSeizoen"] = MySQL::SQLValue($seizoen);

			$values["ohOrderDatum"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);

            $values["ohDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
            $values["ohDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $orderNummer = $db->InsertRow("eba_oh_order_headers", $values);

			if ($orderNummer > 0)
				self::CrtLogEntry($pUserId,$orderNummer,0,'*ADD-HEADER','*WEBSHOP');

			// -------------
			// Einde functie
			// -------------

			$db->close();
			return $orderNummer;
		
		}
		
 		// ===================================================================================================
		// Functie: Aanmaken order-line vanaf Webshop
		//
		// In	- orderNummer
		//		- artikelId
		//		- maat
		//		- Prijs
		//
		// Out	- orderlineId
		//
		// ===================================================================================================
         
        Static function crtOrderLineFromWebshop($pOrderNummer,	$pArtikelId, $pMaat, $pPrijs) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			
			// ----------------
			// Get order-header
			// ----------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			if (! $ohRec = $db->Row()) {
				$db->close();
				return 0;
			}
						
			// ----------------
			// Create orderlijn
			// ----------------
						
			$odId = 0;
						
			$values["odOrdernummer"] = MySQL::SQLValue($pOrderNummer, MySQL::SQLVALUE_NUMBER);
			$values["odArtikel"] = MySQL::SQLValue($pArtikelId, MySQL::SQLVALUE_NUMBER );
			$values["odMaat"] = MySQL::SQLValue($pMaat);	
			$values["odAantal"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER );
			$values["odEenheidsprijs"] = MySQL::SQLValue($pPrijs, MySQL::SQLVALUE_NUMBER );
			$values["odPakket"] = MySQL::SQLValue($ohRec->ohPakket, MySQL::SQLVALUE_NUMBER );
			$values["odBestelStatus"] = MySQL::SQLValue('*WACHT');
			$values["odUserCreatie"] = MySQL::SQLValue($ohRec->ohUserCreatie);				
			$values["odUserUpdate"] = MySQL::SQLValue($ohRec->ohUserCreatie);
			$values["odLeverStatus"] = MySQL::SQLValue(' ');
			
			// - - - - - - 
			// Leverancier
			// - - - - - - 
			
			$leverancier = self::GetDftLeverancier($pArtikelId);
			$values["odLeverancier"] = MySQL::SQLValue($leverancier, MySQL::SQLVALUE_NUMBER );
			

				
			$odId = $db2->InsertRow("eba_od_order_detail", $values); 
			
			$sqlStat = "Update eba_od_order_detail set odLeverMailGestuurd = 0 , odTijdCreatie = now(), odDatumCreatie = now(), odTijdUpdate = now(), odDatumUpdate = now() where odId = $odId";
			$db->Query($sqlStat);
			
			if ($odId > 0)
				self::CrtLogEntry($ohRec->ohUserCreatie,$pOrderNummer,$odId,'*ADD-DETAIL','*WEBSHOP');
			
			$db->close();
			return $odId;
		
		}
 		// ===================================================================================================
		// Functie: Aanmaken Log-entry
		// 
		// In	- UserId 
		//		- OrderNummer
		//		- OrderLijn
		//		- Actiecode (*ADD, *DELETE)
		//		- Oorsprong 
		//
		// Out	- LogId
		//
		// ===================================================================================================
         
        Static function CrtLogEntry($pUserId, $pOrderNummer, $pOrderLijn, $pActieCode, $pOorsprong = " ") {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
			
			// ----------------
			// Get order-header
			// ----------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			if (! $ohRec = $db->Row()){
				$db->close();
				return 0;
			}
			
			if ($pOorsprong > " ")
				$oorsprong = $pOorsprong;
			else
				$oorsprong = $ohRec->ohOorsprong;
			
			
			// ----------------
			// Get order-detail
			// ----------------
			
			$artikel = 0;
			$maat = " ";
			
			if ($pOrderLijn > 0) {
				
				$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
				$db->Query($sqlStat);
				
				if (! $odRec = $db->Row()) {
					$db->close();
					return 0;	
				}

				$artikel = $odRec->odArtikel;
				$maat = $odRec->odMaat;
				
			}

			$values["loKlant"] = MySQL::SQLValue($ohRec->ohKlant);
			$values["loOrdernummer"] = MySQL::SQLValue($pOrderNummer, MySQL::SQLVALUE_NUMBER );
			$values["loOrderlijn"] = MySQL::SQLValue($pOrderLijn, MySQL::SQLVALUE_NUMBER );
			$values["loActiecode"] = MySQL::SQLValue($pActieCode);		
			$values["loOorsprong"] = MySQL::SQLValue($oorsprong);
			
			$values["loPakket"] = MySQL::SQLValue($ohRec->ohPakket, MySQL::SQLVALUE_NUMBER );

			$values["loArtikel"] = MySQL::SQLValue($artikel, MySQL::SQLVALUE_NUMBER );
			$values["loMaat"] = MySQL::SQLValue($maat);	
			
			$values["loUserCreatie"] = MySQL::SQLValue($pUserId);				
			
			$logId = $db->InsertRow("eba_lo_logging", $values); 
					
			$sqlStat = "Update eba_lo_logging set loDatumCreatie = now() where loId = $logId";
			$db->Query($sqlStat);
			
			$db->close();
			return $logId;
		
		}
		
 		// ===================================================================================================
		// Functie: Aanmaken eba_od_order_detail Records in geval van een "pakket" + zetten prijs in order-header
		//
		// In:	- Ordernummer 
		// 		- Pakket-ID
		// 		- UserId 
		//
		// ===================================================================================================
         
        Static function crtPakketOrder($pOrderNummer, $pPakketId, $pUserId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
					
			// --------------
			// Only if pakket
			// --------------
			
			if ($pPakketId <= 0)
				return;
			
			
			// --------------------------------------------
			// Copieer alle pakket-artikels naar bestelling
			// --------------------------------------------

			$sqlStat = "Select * from eba_pa_pakket_artikels where paPakket = $pPakketId order by paSort";
		

			if (! $db->Query($sqlStat)){
				$db->close();
				return;
			}
	
			while($paRec = $db->Row()) {
				
				$leverancier = self::GetDftLeverancier($paRec->paArtikel);
			
				$values["odOrdernummer"] = MySQL::SQLValue($pOrderNummer, MySQL::SQLVALUE_NUMBER);
				$values["odArtikel"] = MySQL::SQLValue($paRec->paArtikel, MySQL::SQLVALUE_NUMBER );
				$values["odAantal"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER );
				$values["odEenheidsprijs"] = 0;
				$values["odBestelBon"] = 0;
				$values["odLeverMailGestuurd"] = 0;
				$values["odLeverancier"] = MySQL::SQLValue($leverancier, MySQL::SQLVALUE_NUMBER);
				$values["odPakket"] = MySQL::SQLValue($pPakketId, MySQL::SQLVALUE_NUMBER );
				$values["odBestelStatus"] = MySQL::SQLValue('*WACHT');
				$values["odLeverStatus"] = MySQL::SQLValue(' ');
				$values["odTijdCreatie"] = MySQL::SQLValue(now(),MySQL::SQLVALUE_DATETIME );		
				$values["odTijdUpdate"] = MySQL::SQLValue(now(),MySQL::SQLVALUE_DATETIME );
				$values["odDatumCreatie"] = MySQL::SQLValue(now(),MySQL::SQLVALUE_DATETIME );		
				$values["odDatumUpdate"] = MySQL::SQLValue(now(),MySQL::SQLVALUE_DATETIME );								
				$values["odUserCreatie"] = MySQL::SQLValue($pUserId);				
				$values["odUserUpdate"] = MySQL::SQLValue($pUserId);
				
				$values["odOpmerkingKlant"] = MySQL::SQLValue(' ');
				
				$odId = $db2->InsertRow("eba_od_order_detail", $values); 
			
			
			}
			
			// --------------------------------
			// Zet pakket-prijs in order-header
			// --------------------------------

			$sqlStat = "Select * from eba_pk_pakketten where pkId = $pPakketId";
			
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
				
			if ($pkRec = $db->Row()) {
			
				$sqlStat = "Update eba_oh_order_headers set ohTotaalPrijs = $pkRec->pkPrijs where ohOrdernummer = $pOrderNummer";
				$db2->Query($sqlStat);
				
			}
			
			$db->close();
							
		}

 		// ===================================================================================================
		// Functie: Is "pakket order" ? -> TRUE/FALSE
		//
		// In:	- ordernummer 
		//
		// Out: - pakket-Id
		//
		// ===================================================================================================
         
        Static function IsPakketOrder($pOrderNummer, &$pPakketId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
			$sqlStat = "Select * From eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
					
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
	
			If ($ohRec = $db->Row()) {
			
				If ($ohRec->ohPakket > 0) {
					$pPakketId = $ohRec->ohPakket;
					$db->close();
					return true;
				}
				else {
					$db->close();
					return false;
				}
			}
				
			Else {
				$db->close();
				return false;
			}
				
		}
		
		// ===================================================================================================
		// Functie: Ophalen "pakket naam"
		//
		// In:	- Pakket-id 
		//
		// ===================================================================================================
         
        Static function GetPakketNaam($pPakketId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
			$sqlStat = "Select * From eba_pk_pakketten where pkId = $pPakketId";
				
			if (! $db->Query($sqlStat)) {
				$db->close();
				return '???';
			}
	
			ElseIf ($pkRec = $db->Row()) {
				$db->close();
				return $pkRec->pkNaam;
			}
				
			Else { 
				$db->close();
				return '???';
			}
				
		}
		
 		// ===================================================================================================
		// Functie: Ophalen "pakket-prijs" ? -> Prijs
		//
		// In:	- Pakket-id 
		//
		// ===================================================================================================
         
        Static function GetPakketPrijs($pPakketId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
			$sqlStat = "Select * From eba_pk_pakketten where pkId = $pPakketId";
				
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}
	
			ElseIf ($pkRec = $db->Row()){
				$db->close();
				return $pkRec->pkPrijs;
			}
					
			Else {
				$db->close();
				return false;
			}
				
		}
		
 		// ===================================================================================================
		// Functie: Is pakket inbegrepen in het lidgeld? -> TRUE/FALSE
		//
		// In:	- Pakket-id 
		//
		// ===================================================================================================
         
        Static function IsLidgeldPakket($pPakketId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
			$sqlStat = "Select * From eba_pk_pakketten where pkId = $pPakketId";
				
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
	
			ElseIf ($pkRec = $db->Row()) {
				$db->close();
				return ($pkRec->pkInLidgeld == 1);
			}
				
			Else {
				$db->close();
				return false;
			}
				
		}
		
 		// ===================================================================================================
		// Functie: Testen order (set Statussen, ohTotaalPrijs, ...)
		//
		// In:	- Ordernummer 
		//
		// ===================================================================================================
         
        Static function ChkOrder($pOrderNummer, $pUserId = '*SYSTEM') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			self::SetOhControle($pOrderNummer, '*OK');

			// ----------------------
			// Get Order-header
			// ----------------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			if (! $db->Query($sqlStat)){
				$db->close();
				return;
			}

			if (! $ohRec = $db->Row()){
				$db->close();
				return;
			}			
			
			// ----------------------
			// Check alle orderlijnen
			// ----------------------

			$totaal = 0;
			
			$sqlStat = "Select * from eba_od_order_detail where odOrdernummer = $pOrderNummer";
					
			if (! $db->Query($sqlStat)){
				$db->close();
				return;
			}
	
			$SetHeaderCode = false;
	
			while($odRec = $db->Row()) {	
				
			
				// ----------------------------------------
				// Aanpassen stock indien stock-leverancier
				// ----------------------------------------
				
				$isStockLeverancier = self::IsStockLeverancier($odRec->odLeverancier);
				
				if ($isStockLeverancier == true) {

					// ---------
					// Uit stock
					// ---------
					
					if (($odRec->odBestelStatus == '*ONTVANGEN') and $odRec->odUitStock != 1)
							self::GetOrderLineFromStock($odRec->odId, $pUserId);

					// ----------------
					// (Terug) IN stock
					// -----------------
					
					if (($odRec->odBestelStatus != '*ONTVANGEN') and $odRec->odUitStock == 1)
							self::PutOrderLineInStock($odRec->odId, $pUserId);		
					
				}

				// -------------------------------
				// Bijhouden "gereserveerde stock"
				// -------------------------------

				self::CalcGereserveerdeStock($odRec->odArtikel, $odRec->odMaat);

				// ------------------
				// Overige verwerking
				// ------------------
				
				$totaal += $odRec->odEenheidsprijs;
			
				self::SetOdControle($odRec->odId, '*OK');
			
				// Maat verplicht?
				if ($odRec->odMaat <= ' ') {
				
					if (self::ChkMaatVerplicht($odRec->odArtikel)) {
						
						self::SetOdControle($odRec->odId, '*MAAT');
						
						if ($SetHeaderCode == false) {
							self::SetOhControle($pOrderNummer, '*MAAT');
							$SetHeaderCode = true;
						}
							
						
					}
								
				}
							
			}

			// ----------------------------------------
			// Voor pakket-order: haal prijs van pakket
			// ----------------------------------------
		
			$isPakketOrder =  self::IsPakketOrder($pOrderNummer, $pakketId);
			
			if ($isPakketOrder == true)
				$totaal = self::GetPakketPrijs($pakketId);
		
			// --------------------
			// Update totaal-bedrag
			// --------------------
			
			$sqlStat = "Update eba_oh_order_headers set ohTotaalPrijs = $totaal where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			// ------------------------------------
			// Set Status orderlijnen & orderheader
			// ------------------------------------
			
			self::SetOrderStatus($pOrderNummer);
			
			// --------------------------
			// Bijhouden bedrag kledijbon
			// --------------------------
			
			$klantId = $ohRec->ohKlant;
			
			self::KeepKledijbon($klantId);
			
			$db->close();
			
		}
	
 		// ===================================================================================================
		// Functie: Mag orderlijn gewist worden?
		//
		// In:	- Orderlijn
		//
		// ===================================================================================================
         
        Static function MagOrderlijnGewist($pOrderLijn) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  


			$sqlStat = "Select * from eba_od_order_detail inner join eba_le_leveranciers on leId = odLeverancier where odId = $pOrderLijn";

			if (! $db->Query($sqlStat)) {
				$db->close();
				return true;
			}
			
			$returnVal = true;
			
			if ($odRec = $db->Row()){
				
				if ($odRec->odBestelStatus != '*WACHT' && $odRec->odBestelStatus != '*BESTELLEN' )
					$returnVal = false;	
				
				if ($odRec->leLevType == '*STOCK' && $odRec->odBestelStatus != '*ONTVANGEN')
					$returnVal = true;					
			}
			
			$db->close();
			return $returnVal;
		
		}
		
 		// ===================================================================================================
		// Functie: Is order volledig afgewerkt? 
		//
		// In:	- Ordernummer
		//
		// ===================================================================================================
         
        Static function IsVolledigAfgewerkt($pOrderNummer) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";


			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
			
			$returnVal = false;
			
			if ($ohRec = $db->Row()){
				
				if ($ohRec->ohVolledigAfgewerkt == 1)
					$returnVal = true;	
				
			}
			
			$db->close();
			return $returnVal;
		
		}	
		
 		// ===================================================================================================
		// Functie: Mag orderlijn naar status "volledig afgewerkt?"
		//
		// In:	- Ordernummer
		//
		// Out: - Message
		//
		// ===================================================================================================
         
        Static function MagNaarVolledigAfgewerkt($pOrderNummer, &$pMessage, $pCheckDate = false) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// ------------------------
			// Init outgoing parameters
			// ------------------------
			
			$pMessage = '';
			
			// ------------
			// Check status
			// ------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";


			if (! $db->Query($sqlStat)) {
				$db->close();
				return false;
			}
			
			$returnVal = false;
			
			if ($ohRec = $db->Row()){
				
				$returnVal = true;		
				
				if ($ohRec->ohBestelStatus <> '*ONTVANGEN') {
					$returnVal = false;	
					$pMessage = 'Volledig afgewerkt ongeldig: Bestelstatus is verschillend van "Ontvangen"';
				}
				else if ($ohRec->ohLeverStatus <> '*GELEVERD' && $ohRec->ohLeverStatus <> '*STOCK') {
					$returnVal = false;	
					$pMessage = 'Volledig afgewerkt ongeldig: Leverstatus is verschillend van "Geleverd" of "Stock"';
				}
			}
			
			
			// ----------------
			// Extra test datum
			// ----------------

			if ($returnVal == true and $pCheckDate == true) {
				
				$sqlStat = "Select * from eba_od_order_detail where odOrdernummer = $pOrderNummer and odAfgeleverdOp >= NOW() - INTERVAL 1 DAY ";
				$db->Query($sqlStat);
				
				If ($odRec = $db->Row()){
					$returnVal = false;	
				}
				
			}
			
			$db->close();
			return $returnVal;
					
			
		}	
		
 		// ===================================================================================================
		// Functie: Pak afgeleverd aan klant
		//
		// In:	- Ordernummer
		//		- User
		//
		// ===================================================================================================
         
        Static function SetPakGeleverdAanKlant($pOrderNummer, $pUserId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_od_order_detail where odOrdernummer = $pOrderNummer";
			if (! $db->Query($sqlStat)){
				$db->close();
				return;
			}
			
			while ($odRec = $db->Row()){
				
				if ($odRec->odLeverStatus == '*KLAAR')
					self::SetLeverStatus($odRec->odId, '*GELEVERD', $pUserId);
								
			}
			
			
			$db->close();
			return;
			
		}			
	
 		// ===================================================================================================
		// Functie: Iets af te leveren van pak?
		//
		// In:	- Ordernummer
		//
		// ===================================================================================================
         
        Static function IetsAfTeLeverenVanPak($pOrderNummer) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pOrderNummer and odLeverStatus = '*KLAAR'";
			
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
			
			$returnVal = false;
			
			if ($odRec = $db->Row()){
				
				if ($odRec->aantal > 0)
					$returnVal = true;
								
			}
			
			
			$db->close();
			return $returnVal;
			
		}		
 		// ===================================================================================================
		// Functie: Zet order op status "volledig afgewerkt?"
		//
		// In:	- Ordernummer
		//
		// ===================================================================================================
         
        Static function SetVolledigAfgewerkt($pOrderNummer) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
			$sqlStat = "update eba_oh_order_headers set ohVolledigAfgewerkt = 1 where ohOrdernummer = $pOrderNummer";


			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
			
			$db->close();
			return true;
			
		}			

		// ===================================================================================================
		// Functie: Delete complete order
		//
		// In:	- Ordernummer 
		//		- User (used for logging)
		//		- Oorsprong (*WEBSHOP/*MANUEEL)
		//
		// ===================================================================================================
         
        Static function DelOrder($pOrderNummer, $pUserId = "", $pOorsprong = '*WEBSHOP') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$klantId = self::GetOrderKlant($pOrderNummer);
			
			// -------------
			// Delete detail
			// -------------
			
			self::DelOrderDetail($pOrderNummer);
			
			// -------------
			// Delete header
			// -------------
			
			self::CrtLogEntry($pUserId,$pOrderNummer,0,'*DEL-HEADER','*WEBSHOP');
			
			$sqlStat = "Delete From eba_oh_order_headers Where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			// --------------
			// Keep kledijbon
			// --------------
			
			self::KeepKledijbon($klantId);

		}
	 	
		// ===================================================================================================
		// Functie: Delete alle orderlijnen (to be called before delete order-header)
		//
		// In:	- Ordernummer 
		//
		// ===================================================================================================
         
        Static function DelOrderDetail($pOrderNummer) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
					
			$arr_odId = array();
			$arr_artikel = array();
			$arr_maat = array();
			
			$sqlStat = "Select * From eba_od_order_detail Where odOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			while ($odRec = $db->Row()) {
				$arr_odId[] = $odRec->odId;
				$arr_artikel[] = $odRec->odArtikel;
				$arr_maat[] = $odRec->odMaat;
			}
				
			for ($i = 0; $i < count($arr_odId); $i++) {
				
				$odId = $arr_odId[$i];
				
				self::CrtLogEntry($pUserId,$pOrderNummer,$odId,'*DEL-DETAIL','*WEBSHOP');	
				
				$sqlStat = "Delete From eba_od_order_detail where odId = $odId";
				$db->Query($sqlStat);	

				$artikel = $arr_artikel[$i];
				$maat = $arr_maat[$i];				
				
				self::CalcGereserveerdeStock($artikel, $maat);
				
		
			}
			
		}
		
		
 		// ===================================================================================================
		// Functie: Set order-detail Controle Code
		//
		// In:	- order-detail ID
		//  	- Controle Error Code
		//
		// ===================================================================================================
         
        Static function SetOdControle($pOrderLijn, $pControleCode) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Update eba_od_order_detail set odControle = '$pControleCode' where odId = $pOrderLijn";
			
			$db->Query($sqlStat);
			
		}
		
		// ===================================================================================================
		// Functie: Check of er orderlijnen-zijn
		//
		// In:	- OrderNummer
		//
		// ===================================================================================================
         
        Static function ChkOrderDetail($pOrderNummer) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select count(*) as aantalLijnen from eba_od_order_detail where odOrdernummer = $pOrderNummer";
			
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
			
			$aantalLijnen = 0;
			
			if ($odRec = $db->Row())
				$aantalLijnen = $odRec->aantalLijnen;
			
			$db->close();
			return ($aantalLijnen > 0);

			
		}	
 		// ===================================================================================================
		// Functie: Set status bestellijn
		//
		// In:	- order-detail ID
		// 		- Status
		//		- UserID indien status "ontvangen"
		//		- Ontvang FoutCode
		//
		// ===================================================================================================
         
        Static function SetStatBestellijn($pOrderLijn, $pStatus, $pUserId = '', $pOntvangFoutCode = '') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
            include_once(SX::GetClassPath("_db.class"));

            $odRec = SSP_db::Get_EBA_odRec($pOrderLijn);

			if ($pStatus == '*ONTVANGEN') {
				
				$userId = $pUserId;
						
				$sqlStat = "Update eba_od_order_detail set odBestelStatus = '$pStatus', odOntvangFoutCode = '', odOntvangenOp = now(), odOntvangenDoor = '$userId' where odId = $pOrderLijn";
				$db->Query($sqlStat);
			}
			else {
				$userId = "";
						
				if ($pOntvangFoutCode > ' ' and $pStatus == '*CONTROLEFOUT')
					$sqlStat = "Update eba_od_order_detail set odBestelStatus = '$pStatus', odOntvangFoutCode = '$pOntvangFoutCode', odOntvangenOp = null, odOntvangenDoor = '' where odId = $pOrderLijn";
				
				elseif ($pStatus == '*CONTROLEFOUT')
					$sqlStat = "Update eba_od_order_detail set odBestelStatus = '$pStatus', odOntvangenOp = null, odOntvangenDoor = '' where odId = $pOrderLijn";
					
				else	
					$sqlStat = "Update eba_od_order_detail set odBestelStatus = '$pStatus', odOntvangFoutCode = '' , odOntvangenOp = null, odOntvangenDoor = '' where odId = $pOrderLijn";				
				
				$db->Query($sqlStat);
			}
			
			
			if ($pStatus <> '*BACKORDER') {
				$sqlStat = "Update eba_od_order_detail set odBackorderDatum = null where odId = $pOrderLijn";
				$db->Query($sqlStat);
			}
		
			// ------------------
			// verdere verwerking
			// ------------------
			
			$sqlStat = "Select * from  eba_od_order_detail where odId = $pOrderLijn";
			$db->Query($sqlStat);
			$odRec = $db->Row();
			
			// ----------------------------
			// Set status "bestel-hoofding"
			// ---------------------------			
			
			$bhId = $odRec->odBestelBon;
			
			if ($bhId > 0 ) {
			
				$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odBestelBon = $bhId and odBestelStatus <> '*ONTVANGEN'";
				
				$db->Query($sqlStat);
				$odRec = $db->Row();
				
				if ($odRec->aantal == 0) {
					
					$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*ONTVANGEN' where bhId = $bhId";
					$db->Query($sqlStat);
				
				}
				else {
					
					$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odBestelBon = $bhId and odBestelStatus = '*ONTVANGEN'";
					$db->Query($sqlStat);
					$odRec = $db->Row();
					
					if ($odRec->aantal > 0) {
						
						$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*PART_ONTVANGEN' where bhId = $bhId";
						$db->Query($sqlStat);
						
						
					}

				}
			
			}
			
			// -----------------------
			// Set status order header
			// -----------------------

            self::ChkOrder($odRec->odOrdernummer, $pUserId);

        }
					
 		// ===================================================================================================
		// Functie: Set leverstatus orderlijn
		//
		// In:	- order-detail ID
		// 		- Status
		//		- UserID
		//
		// ===================================================================================================
         
        Static function SetLeverStatus($pOrderLijn, $pStatus, $pUserId = '*SYSTEM') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			// --------------------
			// Get orderlijn-record
			// --------------------
			
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";			
			$db->Query($sqlStat);
	
			$odRec = $db->Row();

			// ----------------
			// Update OrderLijn
			// ----------------
			
			if (($pStatus != '*GELEVERD') and ($pStatus != '*STOCK'))
				$sqlStat = "Update eba_od_order_detail set odLeverStatus = '$pStatus' where odId = $pOrderLijn";
			else
				$sqlStat = "Update eba_od_order_detail set odLeverStatus = '$pStatus', odAfgeleverdDoor = '$pUserId', odAfgeleverdOp = now() where odId = $pOrderLijn";				
			

			$db->Query($sqlStat);
			
			// --------------
			// In/Out Stock ?
			// --------------
		
			if (($odRec->odLeverStatus != '*STOCK') && ($pStatus == '*STOCK')) {
				self::PutKlantOrderLijnInStock('*IN', $pOrderLijn, $pUserId);
			}
			
			
			if (($odRec->odLeverStatus == '*STOCK') && ($pStatus != '*STOCK'))
				self::PutKlantOrderLijnInStock('*OUT', $pOrderLijn, $pUserId);		
		
			// -----------------------
			// Set status order-header
			// -----------------------
			
			self::ChkOrder($odRec->odOrdernummer, $pUserId);

			
		}
		
 		// ===================================================================================================
		// Functie: Orderlijn -> Stock
		//
		// In:	- *IN/*OUT
		// 		- orderlijnID
		// 		- userID
		//
		// ===================================================================================================
         
        Static function PutKlantOrderLijnInStock($pInOut, $pOrderLijn, $pUserId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// --------------------
			// Get orderlijn-record
			// --------------------
			
			$sqlStat = "Select * from eba_od_order_detail inner join eba_oh_order_headers on ohOrdernummer = odOrdernummer where odId = $pOrderLijn";			
			$db->Query($sqlStat);
	
			$odRec = $db->Row();
			
			// -----------------------
			// Enkel voor klant-orders
			// -----------------------

			if ($odRec->ohOrderType != '*KLANT')
				return;
		
			// ----------------
			// Registreer Stock
			// ----------------		
			
			$stock = self::GetStock($odRec->odArtikel, $odRec->odMaat);
			
			if ($pInOut == '*IN') {
					
				$nieuweStock = $stock + 1;
				self::RegStockWijziging($odRec->odArtikel, $odRec->odMaat, $nieuweStock,'*TERUGNAME', '' , $pUserId, $pOrderLijn);
			
			}
			
			if ($pInOut == '*OUT') {			
				
				$nieuweStock = $stock - 1;
				
				if ($nieuweStock >= 0)
					self::RegStockWijziging($odRec->odArtikel, $odRec->odMaat, $nieuweStock,'*TERUGNAME', '' , $pUserId, $pOrderLijn);

			}

				
		}		
		
		// ===================================================================================================
		// Functie: Maak Bestelling
		//
		// In:	- Bestelbon
		//		- Leverancier
		//
		// ===================================================================================================
         
        Static function CrtBestelling($pBestelbon, $pLeverancier, $pUserId = '*SYSTEM') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			include_once(SX::GetClassPath("_db.class"));
			
			// ---------------------
			// Ophalen bestel-header
			// ---------------------
			
			$sqlStat = "Select * from eba_bh_bestel_headers where bhId = $pBestelbon";
			$db->Query($sqlStat);
			$bhRec = $db->Row();
			
			// ---------------------------------------------------------------------------------
			// Toevoegen alle "te bestellen" orderlijnen van betreffende leverancier & doelgroep
			// ---------------------------------------------------------------------------------
			
			$sqlStat = "Select * from eba_od_order_detail inner join eba_oh_order_headers on ohOrdernummer = odOrdernummer where odBestelStatus = '*BESTELLEN' and odLeverStatus <> '*KLAAR' and odLeverStatus <> '*GELEVERD' and  odLeverancier = $pLeverancier";
			
			$db->Query($sqlStat);	

			while ($odRec = $db->Row()) {
				
				$odId = $odRec->odId;
				

				// ---------------
				// Check doelgroep
				// ---------------
				
				if ($bhRec->bhDoelgroep != 0) {
					
					$inDoelgroep = self::ChkDoelgroep($odRec->ohKlant, $bhRec->bhDoelgroep);
					
					if ($inDoelgroep == false)
						continue;
					
				}

				// ------------------
				// Check enkel pakket
				// ------------------
				
				if ($bhRec->bhEnkelPakket == 1) {
					
					if ($odRec->ohPakket == 0)
						continue;
					
				}

				// -------------------------
				// Check enkel bijbestelling
				// -------------------------
				
				if ($bhRec->bhEnkelBijbestelling == 1) {
					
					if ($odRec->ohPakket > 0)
						continue;
					
				}
								
				// ------------
				// Check pakket
				// ------------
				
				if ($bhRec->bhPakket) {
					
					if ($odRec->ohPakket != $bhRec->bhPakket)
						continue;
					
				}

                // ----------------------
                // Check pakket-categorie
                // ----------------------

                if ($bhRec->bhPakketCategorie) {

                    if (!$odRec->ohPakket )
                        continue;

                    $pakket = $odRec->ohPakket;

                    $pkRec = SSP_db::Get_EBA_pkRec($pakket);

                    if ($pkRec->pkCategorie != $bhRec->bhPakketCategorie)
                        continue;

                }

                // ------------
				// Check order
				// ------------
				
				if ($bhRec->bhOrdernummer) {
					
					if ($odRec->odOrdernummer != $bhRec->bhOrdernummer)
						continue;
					
				}
				
				// -------------
				// Check artikel
				// -------------
				
				if ($bhRec->bhArtikel) {
					
					if ($odRec->odArtikel != $bhRec->bhArtikel)
						continue;
					
				}
				
				// -----------------
				// Update order-lijn
				// -----------------
					
				$sqlStat = "Update eba_od_order_detail set odBestelBon = $pBestelbon, odBestelStatus = '*BESTELD' where odId = $odId";
				
				$db2->Query($sqlStat);
				
				
			}
			
			// -------------------------------------
			// Keep "cataloogprijs" van de bestelbon
			// -------------------------------------
			
			self::SetBestelbonCatalogPrijs($pBestelbon);
			
				
			// ------------------------------
			// Set status all affected orders
			// ------------------------------
			
			$sqlStat = "Select * from eba_od_order_detail where odBestelBon = $pBestelbon";
		
			$db->Query($sqlStat);

			while($odRec = $db->Row()) {
				self::ChkOrder($odRec->odOrdernummer, $pUserId);
			}
	
		}	
		
		// ===================================================================================================
		// Functie: Plaats Bestelling
		//
		// In:	- Bestelbon
		//
		// ===================================================================================================
         
        Static function PutBestelling($pBestelbon, $pUserId = '*SYSTEM') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(SX::GetSxClassPath("tools.class"));
		
			// ----------------------------------
			// Zet status bestelbon op "*BESTELD"
			// ----------------------------------
			
			$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*BESTELD' where bhId = $pBestelbon";
			$db->Query($sqlStat);
			
			// --------------------------
			// Zend mail naar leverancier
			// --------------------------
			
			$sqlStat = "Select *from eba_bh_bestel_headers where bhId = $pBestelbon";
			$db->Query($sqlStat);
			$bhRec = $db->Row();
			
			if ($bhRec->bhLeverancier > 0) {
				
				$leverancier = $bhRec->bhLeverancier;
				
				$sqlStat = "Select leMail from eba_le_leveranciers where leId = $leverancier";
				
				$db->Query($sqlStat);
				$leRec = $db->Row();
				
				if ($leRec->leMail > ' ') {
					
					$mailTo = $leRec->leMail;
					$bccMail = $bhRec->bhContactMail;
					
					$fromMail = 'webshop@schellesport.be';
					if ($bhRec->bhContactMail > ' ' )
						$fromMail = $bhRec->bhContactMail;
					
					$fromName = 'Schelle Sport';
					if ($bhRec->bhContactPersoon > ' ')
						$fromName = "Schelle Sport - $bhRec->bhContactPersoon";
			
						
					$mailBody 	= 	'Beste,'
								.	'<br/><br/>'
								.	'Schelle Sport plaatste een nieuwe bestelling met bestelnummer: ' .$pBestelbon
								. 	'<br/>'
								.	'<br/>';
								
					if ($bhRec->bhInfo > ' ') {
						
						$mailBody 	.=	'Extra info:<br/><br/>'
									.	nl2br($bhRec->bhInfo);
						
						
					}
					
					$mailBody	.=	'<br/><br/>'
								.	'Sportieve groet, '
								.	'<br/><br/>'
								.	'Schelle Sport';
								
					if ($bhRec->bhContactPersoon > ' ') {
						
						$contactPersoon = $bhRec->bhContactPersoon;
						$mailBody .= "<br/>$contactPersoon";
												
					}
					
					if ($bhRec->bhContactTel > ' ') {
						
						$contactTel = $bhRec->bhContactTel;
						$mailBody .= "<br/>$contactTel";
												
					}							

					SX_tools::SendMail('Schelle Sport - Nieuwe Bestelbon', $mailBody, $mailTo, $bccMail, $fromMail, $fromName);
					
						
				
				}
									
			}
			
						
		}		
		
		// ===================================================================================================
		// Functie: Check of er Bestellijnen_zijn
		//
		// In:	- Bestelbon
		//
		// ===================================================================================================
         
        Static function ChkBestelDetail($pBestelbon) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select count(*) as aantalLijnen from eba_od_order_detail where odBestelBon = $pBestelbon";
			
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
			
			$aantalLijnen = 0;
			
			if ($odRec = $db->Row())
				$aantalLijnen = $odRec->aantalLijnen;
			
			$db->close();			
			return ($aantalLijnen > 0);

			
		}	
		
		// ===================================================================================================
		// Functie: Haal orderlijn uit bestelbon
		//
		// In:	- order-detail ID
		//
		// ===================================================================================================
         
        Static function RmvBestelLijn($pOrderLijn, $pUserId = '*SYSTEM') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			
			$sqlStat = "Update eba_od_order_detail set odBestelStatus = '*BESTELLEN', odBestelBon = 0 where odId = $pOrderLijn";
			$db->Query($sqlStat);
			
			// Set status all affected orders...
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
		
			$db->Query($sqlStat);

			while($odRec = $db->Row()) {
				self::ChkOrder($odRec->odOrdernummer, $pUserId);
			}
			
		
		}	
		
		// ===================================================================================================
		// Functie: Verwijder alle lijnen uit bestelbon
		//
		// In:	- bestelbon
		//
		// ===================================================================================================
         
        Static function RmvBestelBon($pBestelbon, $pUserId = '*SYSTEM') {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
		
			$sqlStat = "Select * from eba_od_order_detail where odBestelBon = $pBestelbon";
		
			$db->Query($sqlStat);

			while($odRec = $db->Row()) {
				self::RmvBestelLijn($odRec->odId, $pUserId);
			}
			
			self::SetBestelbonCatalogPrijs($pBestelbon);
			
		
		}
		
 		// ===================================================================================================
		// Functie: Set order-header Controle Code
		//
		// In:	- orderNummer
		//  	- Controle Error Code
		//
		// ===================================================================================================
         
        Static function SetOhControle($pOrderNummer, $pControleCode) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Update eba_oh_order_headers set ohControle = '$pControleCode' where ohOrdernummer = $pOrderNummer";
			
			$db->Query($sqlStat);
			
		}
		
 		// ===================================================================================================
		// Functie: Ophalen rangschikkking van een status -> statusRang
		//
		// In:	- Table
		// 		- Status
		//
		// ===================================================================================================
         
        Static function GetStatusRang($pTable, $pStatus) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from sx_ta_tables where taTable = '$pTable' and taCode = '$pStatus'";
			
			if (! $db->Query($sqlStat)){
				$db->close();
				return 0;
			}
			
			if ($taRec = $db->Row()) {
				$db->close();
				return $taRec->taSort;
			}
			else {
				$db->close();
				return 0;
			}
						
		}	

 		// ===================================================================================================
		// Functie: Ophalen LAAGSTE status -> Status
		//
		// In:	- Table
		//		- 2 statussen
		//
		// ===================================================================================================
         
        Static function GetLaagsteStatus($pTable, $pBestelStatus1, $pBestelStatus2) {  
 
			$rang1 = self::GetStatusRang($pTable, $pBestelStatus1);
			$rang2 = self::GetStatusRang($pTable, $pBestelStatus2);

			if ($rang1 < $rang2)
				$laagsteStatus = $pBestelStatus1;
			else
				$laagsteStatus = $pBestelStatus2;
 			
			return $laagsteStatus;
			
		}	
		
 		// ===================================================================================================
		// Functie: Ophalen HOOGSTE status -> Status
		//
		// In:	- Table
		//		- 2 statussen
		//
		// ===================================================================================================
         
        Static function GetHoogsteStatus($pTable, $pBestelStatus1, $pBestelStatus2) {  
 
			$rang1 = self::GetStatusRang($pTable, $pBestelStatus1);
			$rang2 = self::GetStatusRang($pTable, $pBestelStatus2);

			if ($rang1 > $rang2)
				$laagsteStatus = $pBestelStatus1;
			else
				$laagsteStatus = $pBestelStatus2;
 			
			return $laagsteStatus;
			
		}	
		
 		// ===================================================================================================
		// Functie: Set Order-header STATUS
		//
		// In:	- OrderNummer
		//
		// ===================================================================================================
         
        Static function SetOrderStatus($pOrderNummer, $pCheckBackorder = true) {
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
						
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			
			$db->Query($sqlStat);
			
			$ohRec = $db->Row();
	
			// --------------------
			// Init 3 status-velden
			// --------------------
			
			$betaalStatus = '*OK';
			$bestelStatus = '*';
			$leverStatus = '*';
			
			// ------------
			// Betaalstatus
			// ------------
			
			if ($ohRec->ohTotaalPrijs > $ohRec->ohBetaalTotaal)
				$betaalStatus = '*NOK';
				
			// -----------------	
			// Lidgeld voldaan ?
			// -----------------
			
			if ($betaalStatus == '*OK') {
			
				$isLidgeldPakket = self::IsLidgeldPakket($ohRec->ohPakket);
				
				if ($isLidgeldPakket) {
				
					$lidgeldStatus = self::GetLidgeldStatus($ohRec->ohKlant, $ohRec->ohSeizoen);

					if ($lidgeldStatus != 'OK')
						$betaalStatus = '*LIDGELD';
				}
				
			}
					
				
			// ----------------------------
			// Update bestelStatus in detail
			// ----------------------------			
			
			$sqlStat = "Select * from eba_od_order_detail where odOrdernummer = $pOrderNummer";
			
			if ($betaalStatus == '*OK' && $ohRec->ohControle == "*OK") {
				$bestelStatus = '*BESTELLEN';
			}
			
			else
				$bestelStatus = '*WACHT';
			
			
			$headerBestelStatus = '*';
			$headerLeverStatus = '*';

            $volledigOntvangen = 1;

			$db->Query($sqlStat);


			while($odRec = $db->Row()) {

				$isStockLeverancier = self::IsStockLeverancier($odRec->odLeverancier);

				if ($odRec->odBestelStatus != '*ONTVANGEN' and $odRec->odBestelStatus != '*AFHALEN')
                    $volledigOntvangen = 0;

				// Indien "hoger:gelijk" aan "BESTELD" -> Niets veranderen
				
				$huidigeBestelStatus = $odRec->odBestelStatus;

				if ($odRec->odBestelStatus == '*AFHALEN' and odBestelBon <= 0 and $isStockLeverancier == TRUE)
					$huidigeBestelStatus = '*BESTELLEN';
				
				$checkStatus = self::GetLaagsteStatus('EBA_BESTELSTATUS', '*BESTELD', $huidigeBestelStatus);
				
				if ($checkStatus != '*BESTELD')
					$detailBestelStatus = $bestelStatus;
				else 
					$detailBestelStatus = $odRec->odBestelStatus;

				if (($detailBestelStatus == '*BESTELLEN') and ($isStockLeverancier == TRUE)) {
                    $detailBestelStatus = '*AFHALEN';

				}
				
				if ($detailBestelStatus != $odRec->odBestelStatus) {
					
					$sqlStat = "Update eba_od_order_detail set odBestelStatus = '$detailBestelStatus' where odId = $odRec->odId";
				
					$db2->Query($sqlStat);

				}

				if ($isStockLeverancier and $pCheckBackorder and $detailBestelStatus = '*AFHALEN' ){

				    self::SetBestelstatusStockItem($odRec->odArtikel, $odRec->odMaat);
                    $odRec = SSP_db::Get_EBA_odRec($odRec->odId);
                    $detailBestelStatus = $odRec->odBestelStatus;

                }

				// - - - - - - - - - - 
				// Header Bestel-status
				// - - - - - - - - - - 
				
				 if ($headerBestelStatus == '*')
					$headerBestelStatus = $detailBestelStatus;
				 elseif ($headerBestelStatus == '*CONTROLEFOUT') // may not change...
					$headerBestelStatus = '*CONTROLEFOUT';
				 elseif ($headerBestelStatus == '*BESTELD' and $detailBestelStatus == '*BESTELLEN')
					$headerBestelStatus = '*PART_BESTELD';
				 elseif ($headerBestelStatus == '*AFHALEN' and $detailBestelStatus == '*BESTELD')
					$headerBestelStatus = '*BESTELD';
				 elseif ($headerBestelStatus == '*AFHALEN' and $detailBestelStatus == '*BESTELLEN')
					$headerBestelStatus = '*BESTELLEN';
				 elseif ($headerBestelStatus == '*BESTELLEN' and $detailBestelStatus == '*BESTELD')
					$headerBestelStatus = '*PART_BESTELD';				
				 elseif ($headerBestelStatus == '*ONTVANGEN' and $detailBestelStatus != '*ONTVANGEN')
					$headerBestelStatus = '*PART_ONTVANGEN';
				 elseif ($headerBestelStatus != '*ONTVANGEN' and $detailBestelStatus == '*ONTVANGEN')
					$headerBestelStatus = '*PART_ONTVANGEN';
					
				if ($detailBestelStatus == '*CONTROLEFOUT')
					$headerBestelStatus = '*CONTROLEFOUT';

				// - - - - - - - - - - 
				// Header Lever-status
				// - - - - - - - - - - 
				
				$detailLeverStatus = $odRec->odLeverStatus;
				
				if ($detailLeverStatus <= ' ')
					$detailLeverStatus = "*NIET_KLAAR";
				
				
				if ($headerLeverStatus == '*')
					$headerLeverStatus = $detailLeverStatus;
				elseif ($headerLeverStatus != '*KLAAR' and $detailLeverStatus == '*KLAAR')
					$headerLeverStatus = '*PART_KLAAR';
				elseif ($headerLeverStatus == '*KLAAR' and $detailLeverStatus != '*KLAAR')
					$headerLeverStatus = '*PART_KLAAR';
				elseif ($headerLeverStatus == '*STOCK' and $detailLeverStatus == '*GELEVERD')
					$headerLeverStatus = '*GELEVERD';					
				elseif ($headerLeverStatus == '*GELEVERD' and $detailLeverStatus == '*STOCK')
					$headerLeverStatus = '*GELEVERD';						
				elseif ($headerLeverStatus == '*GELEVERD' and $detailLeverStatus != '*GELEVERD')
					$headerLeverStatus = '*PART_GELEVERD';
				elseif ($headerLeverStatus != '*GELEVERD' and $detailLeverStatus == '*GELEVERD')
					$headerLeverStatus = '*PART_GELEVERD';		
				
			
			}
			
			
			if ($headerLeverStatus == '*NIET_KLAAR')
				$headerLeverStatus = "";
			
			
			// Update order-header statussen
			$sqlStat = "Update eba_oh_order_headers set ohBetaalStatus = '$betaalStatus', ohVolledigOntvangen = $volledigOntvangen, ohBestelStatus = '$headerBestelStatus', ohLeverStatus = '$headerLeverStatus' where ohOrdernummer = $pOrderNummer";
			$db2->Query($sqlStat);

			self::SetOrderKlaarVoorAfleveren($pOrderNummer);
            self::SetLevTypePakbon($pOrderNummer);

		}

		// ===================================================================================================
		// Functie: Ophalen orderheader status
		//
		// In:	- Ordernummer 
		//
		// UIt: Return = Status (in text)
		//		- WachtOpBetaling = Wacht op betaling/lidgeld ?
		//		- pMagGewist = Order mag nog gewist worden ?
		//		- pKanAfgehaald = Order kan afgehaald worden?
		//		- pAfgehandeld = Volledig Afgehandeld?
		//
		// ===================================================================================================
         
        Static function GetOrderHeaderStatus($pOrderNummer, &$pWachtOpBetaling = null, &$pMagGewist = null, &$pKanAfgehaald = null, &$pAfgehandeld = null) {
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$wachtOpBetaling = false;
			$kanAfgehaald = false;
			$afgehandeld = false;
			
			$status = "Onbekende status";
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			
			$db->Query($sqlStat);
			
			$ohRec = $db->Row();

			$kledijbonBedrag = 0;
			if ($ohRec->ohBetaalwijze1 == 'KLEDIJBON') 
				$kledijbonBedrag += $ohRec->ohBetaalBedrag1;
			if ($ohRec->ohBetaalwijze2 == 'KLEDIJBON') 
				$kledijbonBedrag += $ohRec->ohBetaalBedrag2;
			
			$kledijbonBedragE = floatval($kledijbonBedrag);
			$totaalPrijsE = floatval($ohRec->ohTotaalPrijs);
			$betaalTotaalE = floatval($ohRec->ohBetaalTotaal);
			
			If ($ohRec->ohBetaalStatus == '*NOK') {

				$wachtOpBetaling = true;
				$status = "Wacht op betaling $totaalPrijsE EUR";
				
				if ($kledijbonBedrag > 0)
					$status .=  "  - Webshop-tegoed: $kledijbonBedragE EUR";				
				
				if ($ohRec->ohBetaalTotaal > 0)
					$status .=  "  - Totaal reeds betaald: $betaalTotaalE EUR)";
				else
					$status .= ")";
			}
			
			If ($ohRec->ohBetaalStatus == '*LIDGELD') {
				$wachtOpBetaling = true;
				$magGewist = true;
				$status =  "Wacht op uw betaling lidgeld";		
			}

				
			$extraStatus = '';
			if ($kledijbonBedrag > 0)
				$extraStatus = " &nbsp;(Er werd $kledijbonBedragE EUR afgehouden van uw webshop-tegoed)";
	
			
			If ($ohRec->ohBestelStatus == '*BESTELLEN') {
				$status = "Wordt asap besteld bij leverancier " . $extraStatus;
			}
					

			If ($ohRec->ohBestelStatus == '*BESTELD')
				$status = "In bestelling bij leverancier" . $extraStatus;
			
			If ($ohRec->ohBestelStatus == '*BACKORDER')
				$status = "In bestelling bij leverancier"  . $extraStatus;				
			
			If ($ohRec->ohBestelStatus == '*AFHALEN')
				$status = "In bestelling bij leverancier"  . $extraStatus;		

			if ($ohRec->ohLeverStatus == "*PART_KLAAR") {
					$status = "Kan gedeeltelijk afgehaald worden"  . $extraStatus;	
					$kanAfgehaald = true;
			}
			
			if ($ohRec->ohLeverStatus == "*KLAAR") {
					$status = "Kan afgehaald worden"  . $extraStatus;	
					$kanAfgehaald = true;
			}			
			If ($ohRec->ohLeverStatus == '*PART_GELEVERD') {
				$status = "Gedeeltelijk door u afgehaald"  . $extraStatus;
				$afgehandeld = false;
			}
			If ($ohRec->ohLeverStatus == '*GELEVERD') {
				$status = "Volledig afgewerkt"  . $extraStatus;
				$afgehandeld = true;
			}
			
			// ------------------------
			// Fill outgoing parameters
			// ------------------------

			if (isset($pWachtOpBetaling))
					$pWachtOpBetaling = $wachtOpBetaling;
			
			if (isset($pMagGewist))
					$pMagGewist = self::ChkOrderWissen($pOrderNummer);
				
			if (isset($pKanAfgehaald))
					$pKanAfgehaald = $kanAfgehaald;		

			if (isset($pAfgehandeld))
					$pAfgehandeld = $afgehandeld;		
			
			$db->close();			
			return $status;
			
		}

         // ===================================================================================================
         // Functie: Test of order mag gewist worden
         //
         // In:	- Ordernummer
         //
         // Uit: Mag gewist?
         // ===================================================================================================

         Static function ChkOrderWissen($pOrderNummer) {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            include_once(SX::GetClassPath("_db.class"));

            $sqlStat = "Select * from eba_od_order_detail where odOrdernummer = $pOrderNummer";

            $magGewist = true;

            $db->Query($sqlStat);

            while ($odRec = $db->Row()){

                 $magGewist = self::ChkOrderLijnWissen($odRec->odId);

                 if (! $magGewist)
                     break;

            }

            if ($magGewist){

                $ohRec = SSP_db::Get_EBA_ohRec($pOrderNummer);

                // Niet meer wissen toestaan als al "iets" betaald, behalve als via kledijbon...
                if ($ohRec->ohBetaalTotaal > 0 && ($ohRec->ohBetaalwijze1 != 'KLEDIJBON' or $ohRec->ohBetaalwijze2 > ' '))
                    $magGewist = false;

             }


             // -------------
             // Einde functie
             // -------------

             return $magGewist;

         }
         // ===================================================================================================
         // Functie: Test of order-lijn mag gewist worden
         //
         // In:	- Orderlijn
         //
         // Uit: Mag gewist?
         // ===================================================================================================

         Static function ChkOrderLijnWissen($pOrderlijn) {

            include_once(SX::GetClassPath("_db.class"));

            $odRec = SSP_db::Get_EBA_odRec($pOrderlijn);

            $bestelStatus = $odRec->odBestelStatus;

            if ($bestelStatus == '*WACHT')
                 return true;

            if ($bestelStatus == '*BESTELLEN')
                return true;

            $isStockLeverancier = self::IsStockLeverancier($odRec->odLeverancier);

            if ($isStockLeverancier and ($bestelStatus == '*AFHALEN'))
                return true;

             if ($isStockLeverancier and ($bestelStatus == '*BACKORDER'))
                 return true;

            // -------------
            // Einde functie
            // -------------

             return false;



         }


		 // ===================================================================================================
		// Functie: Ophalen order-detail status
		//
		// In:	- pOrderDetailId (Order-detail ID)
		//
		// UIt: Return = Status (in text)
		//
		// ===================================================================================================
         
        Static function GetOrderDetailStatus($pOrderDetailId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(SX::GetSxClassPath("tools.class"));

			
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderDetailId";
			
			$status = "Onbekende status";
			
			$db->Query($sqlStat);
			
			if ($odRec = $db->Row()) {

			    $isStockLeverancier = self::IsStockLeverancier($odRec->odLeverancier);
				
				if ($odRec->odBestelStatus == "*WACHT")
					$status = "Wacht op betaling";
				
				elseif ($odRec->odBestelStatus == "*BESTELLEN")
					$status = "Wordt besteld bij onze leverancier";
				
				elseif ($odRec->odBestelStatus == "*BESTELD")
					$status = "In bestelling bij leverancier";
			
				elseif ($odRec->odBestelStatus == "*CONTROLEFOUT")
					$status = "In bestelling bij leverancier (leverfout)";
					
				elseif ($odRec->odBestelStatus == "*BACKORDER") {
				    if ($odRec->odBackorderDatum) {
                        $datum = SX_tools::EdtDate($odRec->odBackorderDatum, '%d %b %Y');
                        $status = "Momenteel niet op voorraad (BACKORDER). Vermoedelijk terug in voorraad op: $datum ";
                    } else
                        $status = "Momenteel niet op voorraad (BACKORDER)";
				}
				
				elseif ($odRec->odBestelStatus == "*AFHALEN" and (! $isStockLeverancier))
					$status = "In bestelling bij leverancier (Ingepakt)";

				elseif ($odRec->odBestelStatus == "*AFHALEN" and $isStockLeverancier)
                    $status = "Op voorraad";

                elseif ($odRec->odLeverStatus == "*KLAAR")
					$status = "Kan afgehaald worden";

                elseif ($odRec->odLeverStatus == "*STOCK")
                    $status = "Door ons teruggenomen";

                elseif ($odRec->odLeverStatus == "*GELEVERD") {
					$datum = SX_tools::EdtDate($odRec->odAfgeleverdOp, '%d %b %Y');
					$status = "Door u afgehaald op $datum";			
				}
				
				
			}
			
			$db->close();
			return $status;
			
		}

		// ===================================================================================================
		// Functie: (RE)set order-statussen alle (openstaande) orders
		//
		// In:	- Gen
		//
		// UIt:	- Get
		//
		// ===================================================================================================
         
        Static function SetOrdersStat($pKlantCode = '') { 
			
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_oh_order_headers";
			
			if ($pKlantCode > ' ')
				$sqlStat .= " where ohKlant = '$pKlantCode' and ohVolledigAfgewerkt <> 1 ";
			else
				$sqlStat .= " where ohVolledigAfgewerkt <> 1 ";
			
			$db->Query($sqlStat);

			while ($ohRec = $db->Row()) {
				
				self::SetOrderStatus($ohRec->ohOrdernummer);
				
				
			}
		}
		
		
		// ===================================================================================================
		// Functie: Mag pakket besteld worden?
		//
		// In:	- Pakket
		//		- Klant
		//
		// UIt:	Return-value = Mag besteld? True/False
		//
		// ===================================================================================================
         
        Static function MagPakketBesteld($pPakketId, $pKlantCode, &$pOrdernummer = null, &$pExtraInfo = null) {
			
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			
			// ----------------------------------------------------------------------
			// Enkel controle nodig indien pakket gekoppeld aan het lidgeld of gratis
			// ----------------------------------------------------------------------

			$sqlStat = "Select * from eba_pk_pakketten where pkId = $pPakketId";
			$db->Query($sqlStat);

			if (!($pkRec = $db->Row()))
				return false;
			
			if ($pkRec->pkInLidgeld != 1 && $pkRec->pkGratis != 1)
				return true;
			
			$categorie = $pkRec->pkCategorie;

			// -------------------------
			// Mag nog niet besteld zijn
			// -------------------------

			$magBesteld = true;
			$huidigSeizoen = self::GetHuidigSeizoen();

			$sqlStat = "Select * from eba_oh_order_headers where ohKlant = '$pKlantCode' and ohPakket > 0 and ohSeizoen = '$huidigSeizoen'";

	    	$db->Query($sqlStat);

			while ($ohRec = $db->Row()) {
								
				$sqlStat = "Select * from eba_pk_pakketten where pkId = $ohRec->ohPakket";
				$db2->Query($sqlStat);
				
				if ($pkRec = $db2->Row()) {
					
					if ($pkRec->pkId == $pPakketId or $pkRec->pkCategorie == $categorie) {
						
						$magBesteld = false;
						
						if (isset($pOrdernummer))
							$pOrdernummer = $ohRec->ohOrdernummer;
                        if (isset($pExtraInfo))
                            $pExtraInfo = "Kledijpakket reeds besteld dit seizoen (order $ohRec->ohOrdernummer) ";
									
					}
				
				}
					
			}

			// ----------------------------------------------------------------------------
			// Specifiek 2021-2022 -> Speler mag geen korting "GEEN KLEDIJPAKKET" ontvangen
            // -----------------------------------------------------------------------------

            if ($pkRec->pkInLidgeld == 1){

                $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pKlantCode'";
                $db->Query($sqlStat);

                if ($lkRec = $db->Row()){

                    if ($lkRec->lkKortingGeenKledijpakket >  0){

                        $magBesteld = false;

                        if (isset($pExtraInfo))
                            $pExtraInfo = "Kledijpakket NIET voorzien in lidgeld (Corona maatregel)";

                    }

                }

            }

            // -------------
            // Einde functie
            // -------------
			
			Return $magBesteld;
			
			
		}		
		
		// ===================================================================================================
		// Functie: Bijhouden besteed bedrag kledijbon / klant
		//
		// In:	- Klant
		//
		// Uit:	Niets...
		//
		// ===================================================================================================
         
        Static function KeepKledijbon($pKlantId) {

		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

		
			// ---------------------------------------------
			// Ophalen besteed bedrag voor betreffende klant
			// ---------------------------------------------

			$kledijbonBesteed = 0;
			
			$huidigSeizoen = self::GetHuidigSeizoen();
			
			$sqlStat = "Select * from eba_oh_order_headers where ohKlant = '$pKlantId' and ohSeizoen = '$huidigSeizoen'";
			
			$db->Query($sqlStat);
			
			while ($ohRec = $db->Row()) {
				
				if ($ohRec->ohBetaalwijze1 == "KLEDIJBON")
						$kledijbonBesteed += $ohRec->ohBetaalBedrag1;
				if ($ohRec->ohBetaalwijze2 == "KLEDIJBON")
						$kledijbonBesteed += $ohRec->ohBetaalBedrag2;			

			
				
			}

			// ------
			// Update
			// ------
			
			$sqlStat = "Update ssp_ad set adKledijbonBesteed = $kledijbonBesteed where adCode = '$pKlantId'";
			$db->Query($sqlStat);

			$db->close();
			Return;
	
		}
		
		// ===================================================================================================
		// Functie: Betaal order met kledijBon?
		//
		// In:	- orderNummer
		//
		// Uit:	- Betaald met kledijbon? VOLLEDIG/NIET/GEDEELTELIJK
		//
		// ===================================================================================================
         
        Static function BetaalMetKledijbon($pOrderNummer) {

		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			
			// ------------------------------
			// Ophalen klant uit order-header
			// ------------------------------
		
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
						
			if (! $ohRec = $db->Row()) {
				$db->close();
				return "NIET";
			}

			if ($ohRec->ohTotaalPrijs <= 0)
			    return "NIET";

			$klantId = $ohRec->ohKlant;
			
						
			// -----------------------------
			// Enkel indien openstaand saldo
			// -----------------------------
		
			$sqlStat = "Select * from ssp_ad where adCode = '$klantId'";
			$db->Query($sqlStat);			
			
			if (! $adRec = $db->Row()){
				$db->close();
				return "NIET";
			}
			
			$kledijBonBedrag = $adRec->adKledijbon + $adRec->adKledijbonInLidgeld;
			
			if ($kledijBonBedrag <= 0){
				$db->close();
				return "NIET";
			}

			if ($kledijBonBedrag == $adRec->adKledijbonBesteed){
				$db->close();
				return "NIET";
			}
			
			if ($kledijBonBedrag < $adRec->adKledijbonBesteed){
				$db->close();
				return "NIET";
			}
			
			// ----------------------------
			// Registreer betaling in order
			// ----------------------------
			
			$kledijBonSaldo = $kledijBonBedrag - $adRec->adKledijbonBesteed;
			
			$betaalBedrag = $ohRec->ohTotaalPrijs;
			$returnVal = "VOLLEDIG";
			
			if ($kledijBonSaldo < $betaalBedrag) {
				$betaalBedrag = $kledijBonSaldo;
				$returnVal = 'GEDEELTELIJK';
			}
		
			
			$sqlStat = "Update eba_oh_order_headers set ohBetaalBedrag1 = $betaalBedrag, ohBetaalTotaal = $betaalBedrag, ohBetaalDatum1 = now(), ohBetaalwijze1 = 'KLEDIJBON' where ohOrdernummer = $pOrderNummer ";
		
			$db->Query($sqlStat);
			
			// ----------------
			// Set status order
			// ----------------
			
			self::ChkOrder($pOrderNummer);
			
			// -------------
			// Einde functie
			// -------------
			$db->close();
			return $returnVal;
					
		}
		
		// ===================================================================================================
		// Functie: Ophalen order-klant
		//
		// In:	- order
		//
		// Uit:	Return-value: Klant-code
		//
		// ===================================================================================================
         
        Static function GetOrderKlant($pOrderNummer) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// --------------------
			// Ophalen order-header
			// --------------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			if (! $ohRec = $db->Row()) {
				$db->close();
				return '';
			}
			
			$db->close();
			return $ohRec->ohKlant;	
			
		}
					
		// ===================================================================================================
		// Functie: Mail naar besteller (via webshop) met bevestiging order
		//
		// In:	- orderNummer
		//
		// Uit:	Return-value: Mail verzonden ? 
		//
		// ===================================================================================================
         
        Static function SndOrderBevestigingsMail($pOrderNummer, $pMailAdres = '') { 		
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));
			
	
			// --------------------
			// Ophalen order-header
			// --------------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";
			$db->Query($sqlStat);
			
			if (! $ohRec = $db->Row()) {
				$db->close();
				return false;
			}

			$gm = $ohRec->ohGm;

			// ----------------------
			// Ophalen klant-gegevens
			// ----------------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$ohRec->ohKlant'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()){
				$db->close();
				return false;	
			}

			// ------------------
			// Bepalen mail-adres
			// ------------------
			
			$mailAdres = self::GetKlantMailString($ohRec->ohKlant);
			
			if ($mailAdres <= ' '){
				$db->close();
				return false;
			}
			
			// --------------
			// Verzenden mail
			// --------------
			
			$mailBody = "<body>". "\r\n";
			
			$mailBody .= "<style>". "\r\n";
			$mailBody .= "table, th, td { ". "\r\n";
			$mailBody .= " border: 1px solid black; ". "\r\n";
			$mailBody .= " border-collapse: collapse;". "\r\n";
			$mailBody .= "} ". "\r\n";
			$mailBody .= "th, td { ". "\r\n";
			$mailBody .= "  padding: 5px; ". "\r\n";
			$mailBody .= "  text-align: left;". "\r\n";
			$mailBody .= " } ". "\r\n";
			$mailBody .= "</style>". "\r\n";
			
			$mailBody .= "Beste $adRec->adVoornaamNaam,". "\r\n";
			$mailBody .= "<br/><br/>". "\r\n";
			$mailBody .= "Uw bestelling met ref. $pOrderNummer werd geregistreerd.". "\r\n";
			$mailBody .= "<br/><br/>". "\r\n";	
			
			// Pakket-info...
			if ($ohRec->ohPakket > 0){
				
				$sqlStat = "Select * from eba_pk_pakketten where pkId = $ohRec->ohPakket";
				$db->Query($sqlStat);

				if ($pkRec = $db->Row()){

                    $pakketPrijs = "";

                    $prijs = $pkRec->pkPrijs + 0;

				    if ($pkRec->pkPrijs)
				        $pakketPrijs = "&nbsp;(Pakketprijs: $prijs EUR)";
				    else
                        $pakketPrijs = "&nbsp;(Pakketprijs: gratis)";

					$mailBody .= "Pakket: <b>$pkRec->pkNaam</b>$pakketPrijs<br/><br/>". "\r\n";
					
					
				}
		
			}
			
			// Orderlijnen...			
			$sqlStat = "Select * from eba_od_order_detail";
			$sqlStat .= " Inner join eba_ar_artikels on arId = odArtikel";
			$sqlStat .= "  where odOrdernummer = $pOrderNummer ";
			$db->Query($sqlStat);	

			$mailBody .= "<table>". "\r\n";
			$mailBody .= "<tr><th>Product</th><th>Maat</th><th>Prijs</th></tr>". "\r\n";
			
			while ($odRec = $db->Row()) {
				
				$mailBody .= "<tr>". "\r\n";
				
				$mailBody .= "<td>$odRec->arNaam</td>". "\r\n";
				$mailBody .= "<td>$odRec->odMaat</td>". "\r\n";				
				$mailBody .= "<td>$odRec->odEenheidsprijs</td>". "\r\n";							
				
				$mailBody .= "</tr>". "\r\n";
				
				
			}
			
			$mailBody .= "</table>". "\r\n";	
			$mailBody .= "<br/>". "\r\n";
			
			$teBetalen = $ohRec->ohTotaalPrijs - $ohRec->ohBetaalTotaal;
			
			if ($ohRec->ohBetaalTotaal >= $ohRec->ohTotaalPrijs && $ohRec->ohBetaalwijze1 == 'KLEDIJBON') {
				$mailBody .= "Het bedrag van $ohRec->ohTotaalPrijs EUR werd van uw kledijbon afgehouden";
			}	
			
			else if ($ohRec->ohBetaalTotaal < $ohRec->ohTotaalPrijs && $ohRec->ohBetaalwijze1 == 'KLEDIJBON') {
				$mailBody .= "Er werd $ohRec->ohBetaalTotaal EUR van uw kledijbon afgehouden<br/><br/>". "\r\n";
				$mailBody .= "Gelieve het rest-bedrag van <b>$teBetalen EUR</b> te storten op:". "\r\n";
				$mailBody .= "<br/><br/>". "\r\n";
				$mailBody .= "IBAN <b>BE67 2930 0744 3187</b> van Schelle Sport, met vermelding: <b>$gm</b>". "\r\n";
			}				

			else if ($teBetalen > 0) {
				
				$mailBody .= "Gelieve het bedrag van <b>$teBetalen EUR</b> te storten op:". "\r\n";
				$mailBody .= "<br/><br/>". "\r\n";
				$mailBody .= "IBAN <b>BE67 2930 0744 3187</b> van Schelle Sport, met vermelding: <b>$gm</b>". "\r\n";
		
			}
			
			else if ($ohRec->ohTotaalPrijs == 0 && $ohRec->ohPakket > 0) {	

				$mailBody .= "Dit kledijpakket is inbegrepen in het lidgeld". "\r\n";
			
			}
			
			$mailBody .= "<br/><br/>". "\r\n";
			$mailBody .= "Sportieve groet,". "\r\n";
			$mailBody .= "<br/><br/>Schelle Sport Webshop". "\r\n";
			
			$mailBody .= "</body>". "\r\n";
			
			
			SX_tools::SendMail('Schelle Sport - Bestelling via webshop', $mailBody, $mailAdres, 'gvh@vecasoftware.com','secretariaat@schellesport.be');
			
			$db->close();
			return true;
		
		}
		
		// ===================================================================================================
		// Functie: Check Doelgroep
		//
		// In:	- userId
		//		- Doelgroep
		//
		// Uit:	Return-value: Behoort tot doelgroep? (true/false)
		//
		// ===================================================================================================
         
        Static function ChkDoelgroep($pUserId, $pDoelgroep) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pUserId'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()) {
				$db->close();
				return false;
			}
			
			// -----------------------
			// Bepaal voetbalcategorie 
			// -----------------------

			$voetbalCat = self::GetVoetbalCat($pUserId);
			
			// ------------------
			// Controle doelgroep
			// ------------------
			
			$behoortTotDoelgroep = false;
			
			$sqlStat = "Select * from eba_dd_doelgroep_detail where ddDoelgroep = $pDoelgroep";
			$db->Query($sqlStat);

			while ($ddRec = $db->Row()) {
				
				if ((trim($ddRec->ddCatVB) > " ") && (trim($ddRec->ddCatVB) == trim($voetbalCat))) {
					$behoortTotDoelgroep = true;
					break;
				}
				
				if ((trim($ddRec->ddPersoon) > " ") && ($ddRec->ddPersoon == $pUserId)) {
					$behoortTotDoelgroep = true;		
					break;
				}
				
				if ((trim($ddRec->ddFunctieVB) > " ") && ((trim($ddRec->ddCatVB) <= " ")) ) {
		
					if (strpos($adRec->adFunctieVB, $ddRec->ddFunctieVB) !== false) {
						$behoortTotDoelgroep = true;	
						break;
					}
				
				}
				
			}
						
			// -------------
			// Einde functie
			// -------------
			
			$db->close();
			return $behoortTotDoelgroep;
				
		}
		
		// ===================================================================================================
		// Functie: Ophalen "Kledijkeuze"
		//
		// In:	- userId
		//
		// Uit:	Return-value: Kledijkeuze (*OPEN,...)
		//
		// ===================================================================================================
         
        Static function GetKledijKeuze($pUserId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 

			// -------------------
			// Ophalen kledijkeuze 
			// -------------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pUserId'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()) {
				$db->close();
				return '*NONE';
			}

			$db->close();
			return $adRec->adKledijKeuze;

		}
		// ===================================================================================================
		// Functie: Registreren "Kledijkeuze"
		//
		// In:	- kledijKeuze
		// 		- userId
		//
		// Uit:	Return-value: Kledijkeuze (*OPEN,...)
		//
		// ===================================================================================================
         
        Static function RegKledijKeuze($pKledijKeuze, $pUserId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 

			// -------------------
			// Opslaan kledijkeuze 
			// -------------------
			
			$sqlStat = "Update ssp_ad set adKledijKeuze = '$pKledijKeuze' where adCode = '$pUserId'";
			$db->Query($sqlStat);
			
			self::SetKledijbonInLidgeld($pUserId);
			
			$db->close();
			return;
			
		}	

		
		// ===================================================================================================
		// Functie: Feedback
		//
		// In:	- userId
		//		- Boodschap
		//		- Oorsprong
		//
		// Uit:	Return-value: Mail verzonden ? 
		//
		// ===================================================================================================
         
        Static function SndFeedback($pUserId, $pBoodschap, $pOorsprong = "*WEBSHOP") { 		
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));
			
			// ----------------------
			// Ophalen klant-gegevens
			// ----------------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pUserId'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()){
				$db->close();
				return false;
			}

			$mailAdressen = $adRec->adMail;

			if ($adRec->adSpelerMail)
                $mailAdressen .= "<br/>" . $adRec->adSpelerMail;
            if ($adRec->adVaderMail)
                $mailAdressen .= "<br/>" . $adRec->adVaderMail;
            if ($adRec->adMoederMail)
                $mailAdressen .= "<br/>" . $adRec->adMoederMail;

			// ---------------------------------------------
			// Zenden mail (voorlopig naar gvh@vecasoftware)
			// ---------------------------------------------
			
			
			// date_default_timezone_set('Europe/Brussels');
			$date = date('d/m/Y H:i:s', time());
			
			$boodschap = nl2br($pBoodschap);
			
			$mailBody = "<body>";
			$mailBody .= "Feedback van: $adRec->adVoornaamNaam ($pUserId) verzonden op: $date";
			$mailBody .= "<br/><br/>";
			$mailBody .= "<div style='font-style: italic'>";
			$mailBody .= $boodschap;
			$mailBody .= "</div>";
			$mailBody .= "<br/><br/><b>Mailadressen:</b><br/><br/>$mailAdressen";

			$mailBody .= "</body>";

			$mailVerzonden =
				SX_tools::SendMail('Schelle Sport - Webshop feedback', $mailBody, 'gvh@vecasoftware.com');
				
			// ----------------------	
			// Registatie in database
			// ----------------------
			
			$values["fbKlant"] = MySQL::SQLValue($pUserId);
			$values["fbOorsprong"] = MySQL::SQLValue($pOorsprong);
			$values["fbFeedback"] = MySQL::SQLValue($pBoodschap);			
		
			$values["fbUserCreatie"] = MySQL::SQLValue($pUserId);				
			
			$fbId = $db->InsertRow("eba_fb_feedback", $values); 
					
			$sqlStat = "Update eba_fb_feedback set fbDatumCreatie = now() where fbId = $fbId";
			$db->Query($sqlStat);				
		
			// ------------	
			// Eind functie
			// ------------
				
			$db->close();
			return $mailVerzonden;

		}
		
		// ===================================================================================================
		// Functie: Nakijken of er "openstaande orders" zijn
		//
		// In:	- userId
		//
		// Uit:	Return-value: Openstaande orders ? (true/false)
		//
		// ===================================================================================================
         
        Static function ChkOpenOrders($pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_oh_order_headers where ohKlant = '$pUserId'";
			$db->Query($sqlStat);
			
			$openOrders = false;
			
			while($ohRec = $db->Row()){

				$wachtOpBetaling = false;
				$magGewist = false;
				$kanAfgehaald = false;
				$afgehandeld = false;
			
				self::GetOrderHeaderStatus($ohRec->ohOrdernummer, $wachtOpBetaling, $magGewist, $kanAfgehaald, $afgehandeld);

				if ($afgehandeld != true){
					
					$openOrders = true;
					break;
					
				}
				
				
				
			}
			
			// -------------
			// Einde functie
			// -------------
			
			$db->close();
			return $openOrders;


		}
		
		// ===================================================================================================
		// Functie: Zend mail naar klant dat bestelde artikels kunnen afgehaald worden
		//
		// In:	- userId
		//
		// Uit:	Return-value: Mail-adres naar waar mail gestuurd(of *ERROR)
		//
		// ===================================================================================================
         
        Static function MailAfhalenOrders($pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);			
			
			include_once(Sx::GetSxClassPath("tools.class"));
			
			// --------------------------------
			// Zijn er afhaalbare orderlijnen ?
			// --------------------------------
				
			$sqlStat	= "Select count(*) as aantal from eba_oh_order_headers "
						. "inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. "where ohKlant = '$pUserId'  and odLeverStatus = '*KLAAR'";
			
			$db->Query($sqlStat);
			
			if (! $ohRec = $db->Row()){
				$db->close();
				return "*ERROR";
			}
			
			if ($ohRec->aantal <= 0){
				$db->close();
				return "*ERROR";
			}
			
			// ----------------------
			// Ophalen klant-gegevens
			// ----------------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pUserId'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()){
				$db->close();
				return "*ERROR";
			}
			
			// ------------------------------
			// Ophalen afhaalbare orderlijnen
			// ------------------------------
				
			$sqlStat	= "Select * from eba_oh_order_headers "
						. "inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. " Inner join eba_ar_artikels on arId = odArtikel "
						. " Inner join ssp_ad on adCode = ohKlant and adMail > ' '"
						. "where ohKlant = '$pUserId'  and odLeverStatus = '*KLAAR'";
						
			$db->Query($sqlStat);
			
			$boodschap = "";
			
			$mailBody = "<body>". "\r\n";
			
			$mailBody .= "<style>". "\r\n";
			$mailBody .= "table, th, td { ". "\r\n";
			$mailBody .= " border: 1px solid black; ". "\r\n";
			$mailBody .= " border-collapse: collapse;". "\r\n";
			$mailBody .= "} ". "\r\n";
			$mailBody .= "th, td { ". "\r\n";
			$mailBody .= "  padding: 5px; ". "\r\n";
			$mailBody .= "  text-align: left;". "\r\n";
			$mailBody .= " } ". "\r\n";
			$mailBody .= "</style>". "\r\n";
			
			$mailBody .= "Beste $adRec->adVoornaamNaam,". "\r\n";
			$mailBody .= "<br/><br/>". "\r\n";
			
			if ($ohRec->aantal > 1)
				$mailBody .= "Volgende $ohRec->aantal artikels zijn klaar om af te halen:". "\r\n";
			else
				$mailBody .= "Volgend artikel is klaar om af te halen:". "\r\n";
			
			$mailBody .= "<br/><br/>". "\r\n";
			
			$mailBody .= "<table>". "\r\n";
			$mailBody .= "<tr><th>Pak nummer</th><th>Product</th><th>Maat</th></tr>". "\r\n";			
					
			while ($odRec = $db->Row()) {
				
				$mailBody .= "<tr>". "\r\n";
				
				$mailBody .= "<td>$odRec->odOrdernummer</td>". "\r\n";				
				$mailBody .= "<td>$odRec->arNaam</td>". "\r\n";
				$mailBody .= "<td>$odRec->odMaat</td>". "\r\n";				
			
				$mailBody .= "</tr>". "\r\n";
				
				// Set flag mail gezonden & date...
				$odId = $odRec->odId;
				$sqlStat	= "Update eba_od_order_detail set odLeverMailGestuurd = 1, odLeverMailGestuurdOp = now() "
							. "where odId = $odId";
				$db2->Query($sqlStat);
					
					
			}
			
			$mailBody .= "</table>". "\r\n";	
			$mailBody .= "<br/><br/>". "\r\n";
			
			// --------------------------------------
			// Afbeelden eerstvolgende afhaalmomenten
			// --------------------------------------
		
			$mailBody .= "De kalender van de afhaalmomenten kan je <a href='http://schellesport.be/index.php?app=kal_afhalen_dsp&layout=full'>HIER</a> bekijken<br/><br/>". "\r\n";	
			
			//$mailBody .= "Vanaf 1 september 2016 kan de kledij ook tijdens de <a href='http://schellesport.be/index.php?app=kal_secretariaat_dsp&layout=full'>openingsuren</a> van het secreariaat afgehaald worden.<br/><br/>";
			
			$db->Query($sqlStat);			
			
			$sqlStat 	= "Select ceDateFrom, ceName, "
						. "DATE_FORMAT(ceTimeFrom, '%Hu%i') as vanaf, "
						. "DATE_FORMAT(ceTimeTo, '%Hu%i') as totmet "
						. "From sx_ce_calendar_events "
						. "where ceCalendar = '*KLEDIJAFHALEN' "
						. " and ceDateFrom >= curdate() "
						. "order by ceDateFrom, ceTimeFrom";
						
						
			$db->Query($sqlStat);
			
			$teller = 0;
						
			while ($ceRec = $db->Row()) {	
			
				if ($teller == 0)
					$mailBody .= "Eerstvolgende afhaalmomenten:<br/><br/>". "\r\n";
				
				if ($i > 4)
					break;
			
				$teller += 1;
			
				$datum = SX_tools::EdtDate($ceRec->ceDateFrom);

				$locatie = $ceRec->ceName;

				$mailBody .= "* $datum: $ceRec->vanaf t/m $ceRec->totmet ($locatie)". "\r\n";
				$mailBody .= "<br/>". "\r\n";

			}

            $mailBody .= "<br/>". "\r\n";
			$mailBody .= "<b>Gelieve een print van deze mail mee te brengen bij afhaling van de artikels.</b>";
            $mailBody .= "<br/><br/>". "\r\n";
            $mailBody .= "Best ook een zakje meenemen indien je meerdere artikels komt afhalen." . "\r\n";
			$mailBody .= "<br/><br/>". "\r\n";
			$mailBody .= "Sportieve groet,". "\r\n";
			$mailBody .= "<br/><br/>Schelle Sport Webshop". "\r\n";
			
			$mailBody .= "</body>". "\r\n";
			
			// --------------
			// Verzenden mail
			// --------------
			
			$mailAdres = self::GetKlantMailString($pUserId);
			
			if ($mailAdres <= " "){
				$db->close();
				return "*ERROR";
			}

		    $mailBodyUTF8 = utf8_encode($mailBody);

			SX_tools::SendMail("Schelle Sport - Kledij klaar om af te halen", $mailBodyUTF8, $mailAdres, 'gvh@vecasoftware.com','webshop@schellesport.be','Schelle Sport - webshop',"","UTF-8");

			$db->close();
			return $mailAdres;
		
		}
		// ===================================================================================================
		// Functie: Zend mail naar "iedereen" dat bestelde artikels kunnen afgehaald worden
		//
		// In:	Enkel indien nog geen mail gestuurd?
		//
		// Uit:	Aantal mails gezonden
		//
		// ===================================================================================================
         
        Static function MailAfhalenOrdersIedereen($pEnkelIndienNogGeenMailGestuurd = true) { 	
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object

			$sqlStat	= "Select distinct(ohKlant) as klant from eba_oh_order_headers "
						. "inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. "Inner join ssp_ad on adCode = ohKlant and adMail > ' '"
						. "where ohOrderType = '*KLANT' and odLeverStatus = '*KLAAR'";

			if ($pEnkelIndienNogGeenMailGestuurd)
				$sqlStat .= " and odLeverMailGestuurd <> 1";
					
			$aantalMails = 0;		
						
			$db->Query($sqlStat);	

			while ($ohRec = $db->Row()){
				
				self::MailAfhalenOrders($ohRec->klant);
				$aantalMails++;
					
			}

			$db->close();
			return $aantalMails;
			
		}
					
		// ===================================================================================================
		// Functie: Reset switch mail gezonden
		//
		// In:	- userId
		//
		// Uit:	Return-value: none
		//
		// ===================================================================================================
         
        Static function ResetMailAfhalenOrders($pUserId) { 	
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);			
			
			include_once(Sx::GetSxClassPath("tools.class"));
			
			// --------------------------------
			// Zijn er afhaalbare orderlijnen ?
			// --------------------------------
				
			$sqlStat	= "Select count(*) as aantal from eba_oh_order_headers "
						. "inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. "where ohOrderType = '*KLANT' and ohKlant = '$pUserId'  and odLeverStatus = '*KLAAR'";
			
			$db->Query($sqlStat);
			
			if (! $ohRec = $db->Row()){
				$db->close();
				return;
			}
			
			if ($ohRec->aantal <= 0){
				$db->close();
				return;
			}
			
			// ------------------------------
			// Ophalen afhaalbare orderlijnen
			// ------------------------------
				
			$sqlStat	= "Select * from eba_oh_order_headers "
						. "inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. " Inner join eba_ar_artikels on arId = odArtikel "
						. " Inner join ssp_ad on adCode = ohKlant and adMail > ' '"
						. "where ohOrderType = '*KLANT' and ohKlant = '$pUserId'  and odLeverStatus = '*KLAAR'";
						
			$db->Query($sqlStat);

					
			while ($odRec = $db->Row()) {
				
				// Set flag mail gezonden & date...
				$odId = $odRec->odId;
				$sqlStat	= "Update eba_od_order_detail set odLeverMailGestuurd = 0 "
							. "where odId = $odId";
				$db2->Query($sqlStat);
					
					
			}

			$db->close();
			return;
		
		}
		
		// ===================================================================================================
		// Functie: Check of nog mail moet gestuurd worden
		//
		// In:	- Klant
		//
		// Uit:	Return-value: Mail te sturen? true/false
		//		- Datum/tijd laatste verstuurde mail
		//
		// ===================================================================================================
         
        Static function CheckMailAfhalenOrders($pKlant, &$pMailDatumTijd) { 	
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			// ---------------------------------------------------------------
			// Zijn er afhaalbare orderlijnen waarvoor nog geen mail gestuurd?
			// ---------------------------------------------------------------
				
			$sqlStat	= "Select * from eba_od_order_detail " 
						. "inner join eba_oh_order_headers on ohOrdernummer = odOrdernummer "
						. "where ohKlant = '$pKlant' and odLeverStatus = '*KLAAR'";

			$db->Query($sqlStat);
			
			$mailTeSturen = false;
		
			
			while ($odRec = $db->Row()){
				
				if ($odRec->odLeverMailGestuurd <> 1) {
					$mailTeSturen = true;
				}
				
				if  (isset($pMailDatumTijd))
					if (($odRec->odLeverMailGestuurd == 1) and ($odRec->odLeverMailGestuurdOp > $pMailDatumTijd)) 
						$pMailDatumTijd = $odRec->odLeverMailGestuurdOp;
				
			}

			return $mailTeSturen;
		
		}	
		
		// ===================================================================================================
		// Functie: Ophalen alle mail-adressen klant
		//
		// In:	- Klant
		//
		// Uit:	Return-value: String met alle mail adressen
		//
		// ===================================================================================================
         
        Static function GetKlantMailString($pKlant) { 	
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// ---------------------------------------
			// Ophalen mail-adressen betreffende klant
			// ---------------------------------------
				
			$sqlStat = "Select * from ssp_ad where adCode = '$pKlant'";

			$db->Query($sqlStat);
			
			$adRec = $db->Row();
			
			$mails = array();
			
			if ($adRec->adMail > ' ')
				$mails[] = $adRec->adMail;
			
			if ($adRec->adSpelerMail > ' ')
				$mails[] = $adRec->adSpelerMail;
			
			if ($adRec->adVaderMail > ' ')
				$mails[] = $adRec->adVaderMail;
			
			if ($adRec->adMoederMail > ' ')
				$mails[] = $adRec->adMoederMail;		

			
			
			// --------------------
			// Opbouwen mail string
			// --------------------
			
			$mailString = '';
			
			foreach ($mails as $mail){
				
				$mailAdres = trim($mail);
				
				if ($mailString) {
					
					if (! strpos($mailString, $mailAdres))
					$mailString .= "; $mailAdres";
				}
				else
					$mailString = $mailAdres;
				
				
				
			}
			
			return $mailString;
		}
			
		// ===================================================================================================
		// Functie: Mail overzicht af te halen pakken 
		//
		// In:	- userId
		//		- leverancier
		//
		// Uit:	Return-value: Mail verzonden ? 
		//
		// ===================================================================================================
         
        Static function SndAfTeHalenPakkenMail($pUserId, $pLeverancier) { 		
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));
			
			// ------------------------------------------
			// Enkel indien paketten klaar om af te halen
			// ------------------------------------------
			
			$sqlStat	= "Select count(distinct(ohOrdernummer)) as aantal from eba_oh_order_headers "
						. "inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. "where odBestelStatus = '*AFHALEN' and odLeverancier = $pLeverancier ";
						
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
				
			if (! $ohRec = $db->Row()) {
				$db->close();				
				return false;	
			}
			
			if ($ohRec->aantal <= 0){
				$db->close();
				return false;
			}

			$aantalPakken = $ohRec->aantal;
			
			// -----------------
			// Ophalen user-info
			// -----------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pUserId'";
						
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
				
			if (! $adRec = $db->Row()) {
				$db->close();				
				return false;
			}
			
			// ------------------------
			// Ophalen leverancier-info
			// ------------------------
			
			$sqlStat = "Select * from eba_le_leveranciers where leId = $pLeverancier";
						
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
				
			if (! $leRec = $db->Row()) {
				$db->close();				
				return false;
			}
			
			// --------------
			// Verzenden mail
			// --------------
			
			$mailBody = "<body>";
			
			$mailBody .= "<style>";
			$mailBody .= "table, th, td { ";
			$mailBody .= " border: 1px solid black; ";
			$mailBody .= " border-collapse: collapse;";
			$mailBody .= "} ";
			$mailBody .= "th, td { ";
			$mailBody .= "  padding: 5px; ";
			$mailBody .= "  text-align: left;";
			$mailBody .= " } ";
			$mailBody .= "</style>";
			
			$mailBody .= "Beste $adRec->adVoornaamNaam,";
			$mailBody .= "<br/><br/>";
			
			if ($aantalPakken > 1)
				$mailBody .= "Volgende $aantalPakken pakken zijn 'klaar om af te halen' bij onze leverancier '$leRec->leNaam'";
			else
				$mailBody .= "Volgend pak is 'klaar om af te halen' bij onze leverancier '$leRec->leNaam'";	
			
			$mailBody .= "<br/><br/>";	
			
			$sqlStat	= "Select distinct(ohOrdernummer) as pak, ohKlant, adVoornaamNaam, "
						. "Case when ohOrderType = '*CLUB' then 'Cluborder' when ohOrderType = '*STOCK' then 'Stockorder' when ohPakket = 0 then 'Bijbestelling' else pkNaam end as pakket "
						. "From eba_oh_order_headers "
						. "Inner join eba_od_order_detail on odOrdernummer = ohOrdernummer "
						. "Inner join ssp_ad on adCode = ohKlant "
						. "Left outer join eba_pk_pakketten On pkId = ohPakket "
						. "where odBestelStatus = '*AFHALEN' and odLeverancier = $pLeverancier "
						. "order by ohOrdernummer";
						
			$db->Query($sqlStat);

			$mailBody .= "<table>";
			$mailBody .= "<tr><th>Pak nummer</th><th>Bestemmeling</th><th>Pakket</ph></tr>";	
			
			while($ohRec = $db->Row()) 
				$mailBody .= "<tr><td>$ohRec->pak</td><td>$ohRec->adVoornaamNaam</td><td>$ohRec->pakket</td></tr>";
			
			$mailBody .= "</table>";
						
			$mailBody .= "<br/><br/>";
			$mailBody .= "Sportieve groet,";
			$mailBody .= "<br/><br/>Schelle Sport Webshop";
			
			$mailBody .= "</body>";
			
			$mailAdres = $adRec->adMail;
			
			SX_tools::SendMail('Schelle Sport - Pakken klaar om af te halen', $mailBody, $mailAdres, 'gvh@vecasoftware.com');
			
			$db->close();
			return true;
		
		}
 		// ===================================================================================================
		// Functie: Ophalen Stock
		//
		// In:	- artikel
		//		- maat
		//
		// Uit:	Return-value: Stock
		//
		// ===================================================================================================
         
        Static function GetStock($pArtikel, $pMaat = '*ALL') { 

		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));
			
			
			$stock = 0;
			
			if ($pMaat != '*ALL')
				$sqlStat = "Select asStock from eba_as_artikel_stock where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ') ";
			else
				$sqlStat = "Select asStock from eba_as_artikel_stock where asArtikel = $pArtikel ";
						
			if (! $db->Query($sqlStat)){
				$db->close();
				return 0;
			}
			
			while ($asRec = $db->Row()) 			
				$stock += $asRec->asStock;	

			
			$db->close();
			return $stock;
			
		}
		
 		// ===================================================================================================
		// Functie: Registratie stock-wijziging 
		//
		// In:	- artikel
		//		- maat
		//		- nieuweStock
		//		- bewegingsCode
		//		- extraOmschrijving
		//		- userId
		//
		// Uit:	Return-value: Geregistreerd?
		//
		// ===================================================================================================
         
        Static function RegStockWijziging($pArtikel, $pMaat, $pNieuweStock, $pBewegingsCode, $pExtraOmschrijving, $pUserId, $pOrderLijn = 0) { 		
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));

			
			// ------------------------
			// Bestaat stock-record al?
			// ------------------------

			$bestaatReeds = false;
			
			$sqlStat = "Select count(*) as aantal from eba_as_artikel_stock where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ') ";
						
			if (! $db->Query($sqlStat)){
				$db->close();
				return false;
			}
				
			if (! $asRec = $db->Row()) {
				$db->close();				
				return false;
			}

			if ($asRec->aantal > 0)
				$bestaatReeds = true;
			
			// --------------------------
			// Add of Update Stock-record
			// --------------------------
			
			if ($bestaatReeds == true) {
				$stock = self::GetStock($pArtikel, $pMaat);
				$stockBeweging = $pNieuweStock - $stock;
				$sqlStat = "UPDATE eba_as_artikel_stock set asStock = $pNieuweStock where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ') ";
			}
			else {
				$sqlStat = "Insert Into eba_as_artikel_stock (asArtikel, asMaat, asStock, asGereserveerd,asInBestellingVoorStock, asToegewezen) Values ($pArtikel, '$pMaat',$pNieuweStock, 0, 0, 0 )";
				$stockBeweging = $pNieuweStock;
			}
				
			$returnVal = $db->Query($sqlStat);
			
			
			// -----------------
			// Add StockBeweging
			// -----------------

			$curDateTime =	date('Y-m-d H:i:s');	
			
			if ($stockBeweging <> 0) {

				$values["sbArtikel"] = MySQL::SQLValue($pArtikel, MySQL::SQLVALUE_NUMBER);
				$values["sbMaat"] = MySQL::SQLValue($pMaat);				
				$values["sbStockBeweging"] = MySQL::SQLValue($stockBeweging, MySQL::SQLVALUE_NUMBER);	
				$values["sbBewegingsCode"] = MySQL::SQLValue($pBewegingsCode);
				$values["sbDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );				
				$values["sbUserCreatie"] = MySQL::SQLValue($pUserId);
				
			
				if ($pOrderLijn <> 0) {

					$sqlStat = "Select * from eba_od_order_detail inner join eba_oh_order_headers on ohOrdernummer = odOrdernummer inner join ssp_ad on adCode = ohKlant where odId = $pOrderLijn ";
					$db->Query($sqlStat);
					$odRec = $db->Row();
					
					$omschrijving = $odRec->adVoornaamNaam;
					
					$values["sbOrdernummer"] = MySQL::SQLValue($odRec->odOrdernummer, MySQL::SQLVALUE_NUMBER);
					$values["sbOrderlijn"] = MySQL::SQLValue($odRec->odId, MySQL::SQLVALUE_NUMBER);				
					$values["sbExtraOmschrijving"] = MySQL::SQLValue($omschrijving);
					
				}

				else {
                    $values["sbExtraOmschrijving"] = MySQL::SQLValue($pExtraOmschrijving);
                }


				$id = $db->InsertRow("eba_sb_artikel_stock_bewegingen", $values);
			
			}
			
			
			// -------------
			// Einde functie
			// -------------
			
			$db->close();
			return $returnVal;
			
		}

 		// ===================================================================================================
		// Functie: Maak "dummy" stock-record indien nog geen stock-record bestaat
		//
		// In:	- artikel
		//		- maat
		//		- userId
		//
		// Uit:	Return-value: Geregistreerd?
		//
		// ===================================================================================================
         
        Static function CrtEmptyStockRecord($pArtikel, $pMaat) {
			
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));

			
			// ------------------------
			// Bestaat stock-record al?
			// ------------------------

			$bestaatReeds = false;
			
			$sqlStat = "Select count(*) as aantal from eba_as_artikel_stock where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ') ";
						
			if (! $db->Query($sqlStat))
				$bestaatReeds = false;
				
			if (! $asRec = $db->Row()) 			
				$bestaatReeds =  false;	

			if ($asRec->aantal > 0)
				$bestaatReeds = true;
			
			
			if ($bestaatReeds == false)
				self::RegStockWijziging($pArtikel, $pMaat,0, '', '','', '*SYSTEM');
				
			
		}

		
 		// ===================================================================================================
		// Functie: Bereken gereserveerde stock (alsook "stock in bestelling")
		//
		// In:	- artikel
		//		- maat
		//
		// Uit:	Return-value: Gereserveerde stock
		//
		// ===================================================================================================
         
        Static function CalcGereserveerdeStock($pArtikel, $pMaat) { 		
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));  
  

			if (! $pMaat){

			    $sqlStat = "Select count(*) as aantal from eba_am_artikelmaten where amArtikel = '$pArtikel'";
			    $db->Query($sqlStat);

			    if ($amRec = $db->Row()){

			        if ($amRec->aantal > 0)

			            return;

                }

            }

			// ------------------------
			// Init gereserveerde stock
			// ------------------------
  
			$sqlStat = "Update eba_as_artikel_stock set asGereserveerd = 0, asToegewezen = 0 where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ')";
			$db->Query($sqlStat);
  
			$gereserveerdeStock = 0;
			$toegewezenStock = 0;
			$inBestellingVoorStock = 0;
			
			// ----------------------------------
			// Bereken "in bestelling voor stock" 
			// ----------------------------------
			
			$sqlStat = "Select sum(odAantal) as aantal from eba_od_order_detail inner join eba_oh_order_headers on ohOrdernummer = odOrdernummer where (odBestelStatus = '*BESTELD' or odBestelStatus = '*AFHALEN' or odBestelStatus = '*BACKORDER') and ohOrderType = '*STOCK' and odArtikel = $pArtikel and (odMaat = '$pMaat' or '$pMaat' <= ' ')";
			
			$db->Query($sqlStat);
			
			if ($odRec = $db->Row()) {
				
				if ($odRec->aantal)
					$inBestellingVoorStock = $odRec->aantal;
				
				
			}
			
			// ---------------------------
			// Bereken gereserveerde stock
			// ---------------------------
  
			$sqlStat = "Select sum(odAantal) as aantal from eba_od_order_detail inner join eba_le_leveranciers on odLeverancier = leId and leStock = 1 where odBestelStatus <> '*ONTVANGEN' and odArtikel = $pArtikel and (odMaat = '$pMaat' or '$pMaat' <= ' ')";

			$db->Query($sqlStat);
			
			if ($odRec = $db->Row()) {
				
				if ($odRec->aantal)
					$gereserveerdeStock = $odRec->aantal;
				
				self::CrtEmptyStockRecord($pArtikel, $pMaat);	
					
				$sqlStat = "Update eba_as_artikel_stock set asGereserveerd = $gereserveerdeStock, asInBestellingVoorStock = $inBestellingVoorStock where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ')";
				
				$db->Query($sqlStat);

		
			}		
			// ------------------------
			// Bereken toegewezen stock
			// ------------------------
  
			$sqlStat = "Select sum(odAantal) as aantal from eba_od_order_detail inner join eba_le_leveranciers on odLeverancier = leId and leStock = 1 where odBestelStatus = '*ONTVANGEN' and odLeverStatus <> '*GELEVERD' and odArtikel = $pArtikel and (odMaat = '$pMaat' or '$pMaat' <= ' ')";

			$db->Query($sqlStat);
			
			if ($odRec = $db->Row()) {
				
				if ($odRec->aantal)
					$toegewezenStock = $odRec->aantal;
				
				self::CrtEmptyStockRecord($pArtikel, $pMaat);	
					
				$sqlStat = "Update eba_as_artikel_stock set asToegewezen = $toegewezenStock where asArtikel = $pArtikel and (asMaat = '$pMaat' or '$pMaat' <= ' ')";
				
				$db->Query($sqlStat);

		
			}		
			$db->close();
			return $gereserveerdeStock;
  
  
		}
		
 		// ===================================================================================================
		// Functie: Bereken gereserveerde stock voor alle artikels (correctie)
		//
		// In:	Niets
		//
		// Uit:	Niets
		//
		// ===================================================================================================
         
        Static function CalcAlleGereserveerdeStock() { 		
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetSxClassPath("tools.class"));  
		

			$sqlStat = "Select * from eba_as_artikel_stock";
			
			$db->Query($sqlStat);	

			while ($asRec = $db->Row()){

				self::CalcGereserveerdeStock($asRec->asArtikel, $asRec->asMaat);
		
			
			}
		
			$db->close();				
			
		}
  
  		
 		// ===================================================================================================
		// Functie: Haal orderlijn (met stockleverancier) uit stock
		//
		// In:	- Orderlijn-id
		// 		- User-id
		//
		// Uit:	Return-value: *NONE
		//
		// ===================================================================================================
         
        Static function GetOrderLineFromStock($pOrderLijn, $pUserId) { 		

		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
			$db->Query($sqlStat);

				
			if (! $odRec = $db->Row()) {
				$db->close();
				return;
			}
				
		
			$stock = self::GetStock($odRec->odArtikel, $odRec->odMaat);
			
			$aantal = $odRec->odAantal;
			if ($aantal <= 0)
				$aantal = 1;
			
			$nieuweStock = $stock - $aantal;
			
			self::RegStockWijziging($odRec->odArtikel, $odRec->odMaat, $nieuweStock,'*STOCKORDER_OUT', '' , $pUserId, $pOrderLijn);
			
			$sqlStat = "Update eba_od_order_detail set odUitStock = 1 where odId = $pOrderLijn";
			$db->Query($sqlStat);	

			$db->close();
			return;

		}
		
 		// ===================================================================================================
		// Functie: Zet orderlijn (met stockleverancier) (terug) in stock
		//
		// In:	- Orderlijn-id
		// 		- User-id
		//
		// Uit:	Return-value: *NONE
		//
		// ===================================================================================================
         
        Static function PutOrderLineInStock($pOrderLijn, $pUserId) { 		

		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
			$db->Query($sqlStat);

				
			if (! $odRec = $db->Row()) {
				$db->close();
				return;
			}
				
		
			$stock = self::GetStock($odRec->odArtikel, $odRec->odMaat);
						
			$aantal = $odRec->odAantal;
			if ($aantal <= 0)
				$aantal = 1;
			
			$nieuweStock = $stock + $aantal;
			
			// error_log($nieuweStock);
			
			self::RegStockWijziging($odRec->odArtikel, $odRec->odMaat, $nieuweStock,'*STOCKORDER_IN', '' , $pUserId, $pOrderLijn);
			
			$sqlStat = "Update eba_od_order_detail set odUitStock = 0 where odId = $pOrderLijn";
			$db->Query($sqlStat);

			$db->close();
			return;

		}	

		
 		// ===================================================================================================
		// Functie: Zet orderlijn-leverancier (enkel indien nog niet in bestelling)
		//
		// In:	- Orderlijn-id
		// 		- Leverancier
		//
		// Uit:	Return-value: Uitgevoerd?
		//
		// ===================================================================================================
         
        Static function SetLeverancier($pOrderLijn, $pLeverancier) { 		

		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
			$db->Query($sqlStat);
			
			if (! $odRec = $db->Row()) {
				$db->close();
				return false;
			}		
			
			if ($odRec->odBestelStatus == '*BESTELD'){
				$db->close();
				return false;
			}
			
			$sqlStat = "Update eba_od_order_detail set odLeverancier = $pLeverancier where odId = $pOrderLijn";
			$db->Query($sqlStat);			
			
			$db->close();
			return true;

			
		}
			
 		// ===================================================================================================
		// Functie: Is stock-leverancier?
		//
		// In:	- leverancier-nr
		//
		// Uit:	Return-value: Is Stock-leverancier?  true/false
		//
		// ===================================================================================================
         
        Static function IsStockLeverancier($pLeverancier) {


			$stockLeverancier = self::GetStockLeverancier();
			
			if ($pLeverancier == $stockLeverancier)
				return true;
			else
				return false;


		}
		
 		// ===================================================================================================
		// Functie: Ophalen STOCK-leverancier
		//
		// In:	Niets
		//
		// Uit:	Return-value: Stock-leverancier
		//
		// ===================================================================================================
         
        Static function GetStockLeverancier() {


			// Voorlopig HARD-coded (performance)
		
			Return 2;


		}	
		
 		// ===================================================================================================
		// Functie: Ophalen TOE TE WIJZEN  Leverancier
		//
		// In:	Niets
		//
		// Uit:	Return-value: Stock-leverancier
		//
		// ===================================================================================================
         
        Static function GetToeTeWijzenLeverancier() {


			// Voorlopig HARD-coded (performance)
		
			Return 3;


		}	
	
			
		
		// ===================================================================================================
		// Functie: Ophalen voetbalcategorie
		//
		// In:	- userId
		//
		// Uit:	Return-value: Categorie
		//
		// ===================================================================================================
         
        Static function GetVoetbalCat($pUserId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 

			// -----------------------
			// Bepaal voetbalcategorie 
			// -----------------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pUserId'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()){
				$db->close();
				return '*NONE';
			}

			$voetbalCat = $adRec->adVoetbalCat;
			
			if ($adRec->adVoetbalCatWebshop > " ")
				$voetbalCat = $adRec->adVoetbalCatWebshop;	

			
			$db->close();
			return $voetbalCat;
		
		}
		
		
		// ===================================================================================================
		// Functie: Set kledijbon in lidgeld
		//
		// In:	- Klant
		//
		// Uit:	Return-value: Categorie
		//
		// ===================================================================================================
         
        Static function SetKledijbonInLidgeld($pKlantId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 

			// -----------------------
			// Bepaal voetbalcategorie 
			// -----------------------
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pKlantId'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()) {
				$db->close();
				return;	
			}
			
			if ($adRec->adKledijbonInLidgeldManueel == 1){
				$db->close();
				return;
			}
			
			$kledijbonInLidgeld = 0;
		
			if ($adRec->adKledijKeuze == '*BEPERKT') {
								
				if ($adRec->adLidgeldVoldaanVB == 'JA')
					$kledijbonInLidgeld = 35;
				
				if ($adRec->adLidgeldVoldaanVB == 'DEEL' && $adRec->adKledijMagBesteld == 1)
					$kledijbonInLidgeld = 35;			
				
			}
			
					
			$sqlStat = "Update ssp_ad set adKledijbonInLidgeld = $kledijbonInLidgeld where adCode = '$pKlantId'";
			$db->Query($sqlStat);
			
			$db->close();
						
		}		
		
		// ===================================================================================================
		// Functie: Berekenen webshop prijs
		//
		// In:	- Artikel
		//		- Maat 
		//
		// Uit:	Return-value: Prijs
		// ===================================================================================================
         
        Static function GetWebshopPrijs($pArtikelId, $pMaat = ' ') { 	
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}
			
			if (! $arRec = $db->Row()) {
				$db->close();
				return 0;	
			}
						
			$kortingPerc = $arRec->arKortingPerc1;
		
			
			If ($arRec->arPrijsPerMaat != 1)
				$cataloogPrijs = $arRec->arCataloogPrijs;
			else {
				
				if ($pMaat <= ' ') {
					$db->close();
					return 0;
				}
				
				$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikelId and amMaat = '$pMaat'";
				
				if (! $db->Query($sqlStat)) {
					$db->close();
					return 0;
				}
				
				if (! $amRec = $db->Row()) {
					$db->close();
					return 0;
				}

				$cataloogPrijs = $amRec->amCataloogPrijs;

				
			}
			
			// --------------------------------------------
			// Berekenen prijs (altijd naar boven afronden)
			// --------------------------------------------
		
			$prijs = floor($cataloogPrijs - (($cataloogPrijs / 100) * $kortingPerc));
			
			if ($arRec->arLogo == 1)
				$prijs += $arRec->arKostPrintLogo;
			
			$db->close();
			return $prijs;
		
		}
		

		// ===================================================================================================
		// Functie: Update artikel webshop prijs
		//
		// In:	- Artikel
		//
		// Uit:	Return-value: None 
		// ===================================================================================================
         
        Static function UpdArtikelWebshopPrijs($pArtikelId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
			
			if (! $arRec = $db->Row()) {
				$db->close();
				return;
			}

			
			$prijs = -1;
			
			if ($arRec->arPrijsPerMaat == 1)
				$prijs = null;
			
			if ($arRec->arGratis == 1)
				$prijs = 0;		

			if ($arRec->arManuelePrijs == 1 && $prijs == -1) 
				$prijs = $arRec->arPrijs;
			
			if ($prijs == -1)
				$prijs = self::GetWebshopPrijs($pArtikelId);
			
			$sqlStat = "update eba_ar_artikels set arPrijs = $prijs where arId = $pArtikelId";
			$db->Query($sqlStat);
			
			// Ook alle maten updaten
			if ($arRec->arPrijsPerMaat != 1) {
				
				$cataloogPrijs = $arRec->arCataloogPrijs;
				
				$sqlStat = "update eba_am_artikelmaten set amCataloogPrijs = $cataloogPrijs, amPrijs = $prijs where amArtikel = $pArtikelId";
				$db->Query($sqlStat);				
	
			}		
			
			$db->close();
			return;			
		
		
		}

		// ===================================================================================================
		// Functie: Update artikelMaat webshop prijs
		//
		// In:	- Artikel
		//		- Maat
		//
		// Uit:	Return-value: None 
		// ===================================================================================================
         
        Static function UpdArtikelMaatWebshopPrijs($pArtikelId, $pMaat) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
			
			if (! $arRec = $db->Row()) {
				$db->close();
				return;
			}
			
			$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikelId and amMaat = '$pMaat'";
					
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
			
			if (! $amRec = $db->Row()) {
				$db->close();
				return;
			}

			$cataloogPrijs = $amRec->amCataloogPrijs;			
			$prijs = -1;
			
			if ($arRec->arPrijsPerMaat != 1) {
				$cataloogPrijs = $arRec->arCataloogPrijs;
				$prijs = $arRec->arPrijs;
			}
			
			if ($arRec->arGratis == 1)
				$prijs = 0;		

			if ($arRec->arManuelePrijs) 
				$prijs = $amRec->amPrijs;

			if ($prijs == -1)
				$prijs = self::GetWebshopPrijs($pArtikelId, $pMaat);

			
			$sqlStat = "update eba_am_artikelmaten set amCataloogPrijs = $cataloogPrijs, amPrijs = $prijs where amArtikel = $pArtikelId and amMaat = '$pMaat'";
			
			$db->Query($sqlStat);
			
			$db->close();
			return;			
		
		
		}	


		// ===================================================================================================
		// Functie: Update artikelMaat webshop prijs (Voor ALLE maten)
		//
		// In:	- Artikel
		//
		// Uit:	Return-value: None 
		// ===================================================================================================
         
        Static function UpdArtikelAlleMatenWebshopPrijs($pArtikelId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}

			
			while ($amRec = $db->Row()) {
				
				$maat = $amRec->amMaat;
				self::UpdArtikelMaatWebshopPrijs($pArtikelId, $maat);

			}

	
			$db->close();
			return;			
		
		
		}	

		// ===================================================================================================
		// Functie: Get Cataloog-prijs
		//
		// In:	- Artikel
		//		- Maat
		//
		// Uit:	Return-value: Cataloogprijs
		// ===================================================================================================
         
        Static function GetCataloogPrijs($pArtikelId, $pMaat) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikelId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}
			
			if (! $arRec = $db->Row()) {
				$db->close();
				return 0;
			}	

			// --------------------------------
			// Cataloog-prijs op artikel-niveau
			// --------------------------------
			
			if ($arRec->arPrijsPerMaat != 1) {
				$db->close();
				return $arRec->arCataloogPrijs;
			}
			
			
			// ----------------------------
			// Cataloogprijs op maat-niveau
			// ----------------------------
			
			$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikelId and amMaat = '$pMaat'";
					
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}
			
			if (! $amRec = $db->Row()) {
				$db->close();
				return 0;
			}		

			$db->close();
			return $amRec->amCataloogPrijs;			
			
		}
		// ===================================================================================================
		// Functie: Update Cataloog-prijs in order-detail
		//
		// In:	- odId
		//
		// Uit:	Return-value: Cataloog prijs
		//		Aankoopprijs
		//		Verkoopprijs
		// ===================================================================================================
         
        Static function UpdOrderDetailCatalogPrijs($pOrderLijn, &$pAankoopPrijs = null, &$pVerkoopPrijs = null) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}
			
			if (! $odRec = $db->Row()) {
				$db->close();
				return 0;
			}		

			
			$cataloogPrijs = self::GetCataloogPrijs($odRec->odArtikel, $odRec->odMaat);
			$aankoopPrijs = 0;
			
			if ($cataloogPrijs > 0) {
				
				$arRec = self::db_arRec($odRec->odArtikel);

                $aankoopPrijs = $cataloogPrijs;

				if ($arRec && $arRec->arKortingPerc2 > 0)
					$aankoopPrijs = $cataloogPrijs * (1 - ($arRec->arKortingPerc2 / 100));

				if ($arRec->arKostPrintLogo)
					$aankoopPrijs = $aankoopPrijs + $arRec->arKostPrintLogo;
	
			}
			

			
			if ($cataloogPrijs > 0) {
				
				$sqlStat = "Update eba_od_order_detail set odCataloogPrijs = $cataloogPrijs, odAankoopPrijs = $aankoopPrijs where odId = $pOrderLijn";
				$db->Query($sqlStat);

				
			}
			
			$db->close();
			
			
			if ($odRec->odEenheidsprijs <> null && $odRec->odEenheidsprijs > 0)
				$verkoopPrijs = $odRec->odEenheidsprijs;
			else 
				$verkoopPrijs = self::GetWebshopPrijs($odRec->odArtikel, $odRec->odMaat); 	
				
			if (isset($pAankoopPrijs))
					$pAankoopPrijs = $aankoopPrijs;			
			
			if (isset($pVerkoopPrijs))
					$pVerkoopPrijs = $verkoopPrijs;	
				
			return $cataloogPrijs;	
			
		}
			
		// ===================================================================================================
		// Functie: Set BEstelstatus afhankelijk van leverancier
		//
		// In:	- Orderlijn ID
		//
		// Uit:	Return-value: none
		// ===================================================================================================
         
        Static function PresetBestelStatus($pOrderLijn) { 

            include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  		
		    include_once(SX::GetClassPath("_db.class"));
				
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";

			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
				
			$odRec = $db->Row();

			$isStockLeverancier = self::IsStockLeverancier($odRec->odLeverancier);

			if (($odRec->odBestelStatus == '*BESTELLEN') and ($isStockLeverancier == TRUE) ) {

			    $asRec = SSP_db::Get_EBA_asRec($odRec->odArtikel, $odRec->odMaat);

			    if ($asRec->asStock <= $asRec->asGereserveerd)
                    $bestelStatus = '*BACKORDER';
                else
                    $bestelStatus = '*AFHALEN';

                if ($odRec->odStockNietInBackorder)
                    $bestelStatus = '*AFHALEN';

				$sqlStat = "Update eba_od_order_detail set odBestelStatus = '$bestelStatus' where odId = $pOrderLijn";
				$db->Query($sqlStat);
				$db->close();
				return;

			}			
			
			if (($odRec->odBestelStatus == '*AFHALEN' or $odRec->odBestelStatus == '*BACKORDER' ) and ($isStockLeverancier != TRUE) and (! $odRec->odBestelBon)) {
				$sqlStat = "Update eba_od_order_detail set odBestelStatus = '*BESTELLEN' where odId = $pOrderLijn";
				$db->Query($sqlStat);
				$db->close();
				return;
			}				
		
		}
		
				
		// ===================================================================================================
		// Functie: Set Cataloog-prijs bestelbon
		//
		// In:	- bestelbon
		//
		// Uit:	Return-value: none
		// ===================================================================================================
         
        Static function SetBestelbonCatalogPrijs($pBestelbon) { 
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// -------------------------
			// (Re-)fill odCataloogPrijs
			// -------------------------
			
			$sqlStat = "Select * from eba_od_order_detail where odBestelBon = $pBestelbon";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return;
			}
	
			$cataloogPrijsTotaal = 0;
			$aankoopPrijsTotaal = 0;
			$verkoopPrijsTotaal = 0;
			$aankoopPrijs = 0;
			$verkoopPrijs = 0;
			
			while ($odRec = $db->Row()) {

				$cataloogPrijs = self::UpdOrderDetailCatalogPrijs($odRec->odId, $aankoopPrijs, $verkoopPrijs);
				
				if ($odRec->odAantal > 0) {
					$cataloogPrijsTotaal += ($cataloogPrijs * $odRec->odAantal);
					$aankoopPrijsTotaal += ($aankoopPrijs * $odRec->odAantal);
					$verkoopPrijsTotaal += ($verkoopPrijs * $odRec->odAantal);
				}
				else {
					$cataloogPrijsTotaal += $cataloogPrijs;
					$aankoopPrijsTotaal += $aankoopPrijs;
					$verkoopPrijsTotaal += $verkoopPrijs;
				}
						
			}	
			
			// --------------------
			// Update bestel-header
			// --------------------

			$sqlStat = "Update eba_bh_bestel_headers set bhCataloogPrijs = $cataloogPrijsTotaal, bhAankoopPrijs = $aankoopPrijsTotaal, bhVerkoopBedrag= $verkoopPrijsTotaal, bhBerekenCataloogPrijs = 0 where bhId = $pBestelbon";
			$db->Query($sqlStat);
			
			$db->close();
			return;		
	
		}
		
		// ===================================================================================================
		// Functie: Get "Kledij-status" (kledijbon besteld?)
		//
		// In:	- persoon
		//
		// Uit:	Return-value:	*BESTELD  		(Kledijpakket besteld)
		//						*TEBESTELLEN 	(Lidgeld OK, maar kledij nog niet besteld)
		//						*NIETS			(Lidgeld niet OK, kledijn niet besteld)
		// ===================================================================================================
         
        Static function GetKledijStatus($pPersoon) { 
		
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
	
			$huidigSeizoen = self::GetHuidigSeizoen();
			
			$lidgeldStatus = self::GetLidgeldStatus($pPersoon);
			
			$sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row()) {
				$db->close();
				return '*ERROR';
			}
			
			$voetbalCat = $adRec->adVoetbalCat;
			if ($adRec->adVoetbalCatWebshop > ' ')
				$voetbalCat = $adRec->adVoetbalCatWebshop;

			// ----------------
			// Corona maatregel
            // ----------------

            $geenPakket = false;

            $sqlStat = "Select * from ela_lk_lidgeld_keuze where lkPersoon = '$pPersoon'";
            $db->Query($sqlStat);

            if ($lkRec = $db->Row())
                if ($lkRec->lkKortingGeenKledijpakket)
                    $geenPakket = true;

            if ($adRec->adClubVerlatenEindeSeizoen)
                $geenPakket = true;

             if ($geenPakket)
                 return '*NVT';

			$sqlStat = "Select count(*) as aantal from eba_oh_order_headers where ohKlant = '$pPersoon' and ohPakket > 0 and ohSeizoen = '$huidigSeizoen' and ohTotaalPrijs = 0";
			$db->Query($sqlStat);
			
			if ($ohRec = $db->Row()) {
				if ($ohRec->aantal > 0) {
					$db->close();
					return '*BESTELD';
				}
			}

			if ($lidgeldStatus == 'OK') {
				$db->close();
				return '*TEBESTELLEN';		
			}

			$db->close();
			return "*NIETS";

		}
		
 		// ===================================================================================================
		// Functie: Check of pakbon kan gedrukt worden
		//
		// In:	- Ordernummer 
		//
		// ===================================================================================================
         
        Static function ChkPakbon($pOrderNummer) {  
		
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pOrderNummer and odBestelStatus = '*AFHALEN'";
			
			$db->Query($sqlStat);

			$odRec = $db->Row();
			
			$db->close();

			if ($odRec->aantal > 0)
				return true;
			else
				return false;
			
		}
				
 		// ===================================================================================================
		// Functie: Ophalen externe leverancier bepaald artikel
		//
		// In:	- Arikel 
		//
		// ===================================================================================================
         
        Static function GetExtLev($pArtikel) {  
	 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_al_artikel_leveranciers inner join eba_le_leveranciers on leId = alLeverancier and leLevType = '*EXTERN' where alArtikel = $pArtikel";
			
			$db->Query($sqlStat);
			
			if (! $alRec = $db->Row()) {
				$db->close();
				return 0;	
			}	

			return $alRec->alLeverancier;
			
			
		}
				
 		// ===================================================================================================
		// Functie: Orderlijn omzetten naar STOCK-leverancier
		//
		// In:	- OrderLijn 
		//
		// ===================================================================================================
         
        Static function SetOrderdetailToStockLev($pOrderLijn) {  
	 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
			
			$db->Query($sqlStat);
			
			if (! $odRec = $db->Row()) {
				$db->close();
				return;	
			}
			
			$ordernummer = $odRec->odOrdernummer;
			$artikel = $odRec->odArtikel;
		
			$stockLeverancier = self::GetStockLeverancier();
			
			if (! self::ChkArtLev($artikel, $stockLeverancier)) {
				$db->close();
				return;					
			}
			
			self::SetLeverancier($pOrderLijn, $stockLeverancier);
			
			self::PresetBestelStatus($pOrderLijn);
			self::ChkOrder($ordernummer);
			
			
		}	
				
 		// ===================================================================================================
		// Functie: Orderlijn omzetten naar EXTERNE-leverancier
		//
		// In:	- OrderLijn 
		//
		// ===================================================================================================
         
        Static function SetOrderdetailToExtLev($pOrderLijn) {  
	 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
			
			$db->Query($sqlStat);
			
			if (! $odRec = $db->Row()) {
				$db->close();
				return;	
			}
			
			$ordernummer = $odRec->odOrdernummer;
			$artikel = $odRec->odArtikel;
		
			$extLeverancier = self::GetExtLev($artikel);
			
			if ($extLeverancier == 0) {
				$db->close();
				return;				
			}
		
			self::SetLeverancier($pOrderLijn, $extLeverancier);
			
			self::PresetBestelStatus($pOrderLijn);
			self::ChkOrder($ordernummer);
			
			
		}	
				
 		// ===================================================================================================
		// Functie: Orderlijn omzetten Naar "Leverancier Toe Te Wijzen"
		//
		// In:	- OrderLijn 
		//
		// ===================================================================================================
         
        Static function SetOrderdetailToeTeWijzen($pOrderLijn) {  
	 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
			
			$db->Query($sqlStat);
			
			if (! $odRec = $db->Row()) {
				$db->close();
				return;	
			}
			
			$ordernummer = $odRec->odOrdernummer;
			$artikel = $odRec->odArtikel;
		
			$toeTeWijzenLeverancier = self::GetToeTeWijzenLeverancier();

			//if (! self::ChkArtLev($artikel, $toeTeWijzenLeverancier)) {
			//	$db->close();
			//	return;					
			//}
		
			self::SetLeverancier($pOrderLijn, $toeTeWijzenLeverancier);
			
			self::PresetBestelStatus($pOrderLijn);
			self::ChkOrder($ordernummer);
			
			
		}	
				
 		// ===========================================================================================================
		// Functie: Ophalen pak lever status (VOLLEDIG, ENKEL STOCK-ITEMS, Deel reeds geleverd, Deel nog in bestelling
		//
		// In:	- Ordernummer 
		//
		// ===========================================================================================================
         
        Static function GetPakLeverStatus($pOrdernummer) {  
	 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// ---------
			// Volledig?
			// ---------

			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pOrdernummer and odLeverStatus <> '*KLAAR'";
			
			$db->Query($sqlStat);
			
			$odRec = $db->Row();
			
			if ($odRec->aantal <= 0)
				return 'VOLLEDIG';
			
			// -----------------
			// Enkel STOCK-ITEMS
			// -----------------
			
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pOrdernummer and odLeverStatus = '*KLAAR' and  odLeverancier <> 2";			
			$db->Query($sqlStat);
			
			$odRec = $db->Row();
			
			if ($odRec->aantal <= 0)
				return 'ENKEL STOCK-ITEMS';			
			
			// ----------------------
			// Deel nog in bestelling
			// -----------------------
			
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pOrdernummer and odBestelStatus <> '*ONTVANGEN'";			
			$db->Query($sqlStat);
			
			$odRec = $db->Row();
			
			if ($odRec->aantal > 0)
				return 'DEEL NOG IN BESTELLING';				
			
			// -------------------
			// Deel reeds geleverd
			// -------------------
			
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pOrdernummer and odLeverStatus = '*GELEVERD'";			
			$db->Query($sqlStat);
			
			$odRec = $db->Row();
			
			if ($odRec->aantal > 0)
				return 'DEEL REEDS AFGELEVERD';		

			return '???';


		}
		
		
		// ===================================================================================================
		// Functie: Set status Bestelheader
		//
		// In:	- bestelbon-id
		//
		// Uit:	Return-value: none
		// ===================================================================================================
         
        Static function SetStatBestelHeader($bhId) { 
	 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			// --------------------------------
			// Lege bestelbon -> Status "*OPEN"
			// --------------------------------

			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odBestelBon = $bhId";
			
			$db->Query($sqlStat);
			$odRec = $db->Row();
			
			if ($odRec->aantal == 0) {
				
				$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*OPEN' where bhId = $bhId";
				$db->Query($sqlStat);
				
				return;
			}	

			// ------------------
			// Volledig ontvangen
			// ------------------
			
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odBestelBon = $bhId and odBestelStatus <> '*ONTVANGEN'";
			
			$db->Query($sqlStat);
			$odRec = $db->Row();
			
			if ($odRec->aantal == 0) {
				
				$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*ONTVANGEN' where bhId = $bhId";
				$db->Query($sqlStat);
				
				return;
			
			}		

			// ----------------------
			// GEDEELTELIJK ontvangen
			// ----------------------
				
			$sqlStat = "Select count(*) as aantal from eba_od_order_detail where odBestelBon = $bhId and odBestelStatus = '*ONTVANGEN'";	

			$db->Query($sqlStat);
			$odRec = $db->Row();
			
			if ($odRec->aantal > 0) {
								
				$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*PART_ONTVANGEN' where bhId = $bhId";
				$db->Query($sqlStat);
				
				return;
			
			}	
			
			if ($odRec->aantal = 0) {
								
				$sqlStat = "Update eba_bh_bestel_headers set bhStatus = '*BESTELD' where bhId = $bhId";
				$db->Query($sqlStat);
				
				return;
			
			}	
			
		}
			
			
		// ===================================================================================================
		// Functie: Opsplitsen orderlijn ivm bestelStatus
		//
		// In:	- Orderlijn ID
		//		- Status 1 (bv. *BACKORDER)
		//		- Aantal 1 
		//		- Status 2 (bv. *AFHALEN)
		//		- Aantal 2 
		//		- Datum Backorder
		//
		// Uit:	Return-value: none
		// ===================================================================================================
         
        Static function SplitOrderLijnBasedOnBestelStatus($pOrderLijn, $pBestelStatus1, $pAantalStatus1, $pBestelStatus2, $pAantalStatus2, $pDatumBackorder = null) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  		
		
			$sqlStat = "Select * From eba_od_order_detail where odId = $pOrderLijn";
			
			$db->Query($sqlStat);
			
			$odRec = $db->Row();

			
			If ($pAantalStatus1 > 0 and $pAantalStatus1 < $odRec->odAantal) {
				
				$fieldList1 = "odOrdernummer, odArtikel, odMaat, odAantal, odEenheidsprijs, odManuelePrijs, odGratis, odRedenGratis, odPakket,  odLeverancier, odBestelBon, odBestelStatus, odBackorderDatum, odOntvangenDoor, odOntvangenOp, odOntvangFoutCode, odLeverMailGestuurd, odLeverMailGestuurdOp,odLeverStatus,odAfgeleverdDoor, odAfgeleverdOp, odControle, odOpmerkingKlant, odDatumCreatie, odUitStock, odTijdCreatie, odDatumUpdate, odTijdUpdate, odUserCreatie, odUserUpdate";
					
				$datumBackorder = "null";	
				if ($pBestelStatus1 == '*BACKORDER' and $pDatumBackorder <> null)	
					$datumBackorder = "'$pDatumBackorder'";
					
				$fieldList2 = "odOrdernummer, odArtikel, odMaat, $pAantalStatus1, odEenheidsprijs, odManuelePrijs, odGratis, odRedenGratis, odPakket,  odLeverancier, odBestelBon, '$pBestelStatus1' , $datumBackorder, odOntvangenDoor, odOntvangenOp, odOntvangFoutCode, odLeverMailGestuurd, odLeverMailGestuurdOp,odLeverStatus,odAfgeleverdDoor, odAfgeleverdOp, odControle, odOpmerkingKlant, odDatumCreatie, 0, odTijdCreatie, odDatumUpdate, odTijdUpdate, odUserCreatie, odUserUpdate";			
				
				$sqlStat = "Insert into eba_od_order_detail ($fieldList1) Select $fieldList2 from  eba_od_order_detail where odId = $pOrderLijn";
				
			
				$db->Query($sqlStat);
					
				$datumBackorder = "null";	
				if ($pBestelStatus2 == '*BACKORDER' and $pDatumBackorder <> null)	
					$datumBackorder = "'$pDatumBackorder'";
				
				$sqlStat = "Update eba_od_order_detail set odAantal = $pAantalStatus2, odBestelStatus = '$pBestelStatus2', odBackorderDatum = $datumBackorder, odUitStock = 0 where odId = $pOrderLijn";							

				$db->Query($sqlStat);			
			}
			
			
			$db->close();
			return;

		}				
			
		// ===================================================================================================
		// Functie: Samenvoegen orderlijnen met bestelstatus '*BESTELD' of '*AFHALEN' voor zelfde artikel, maat
		//
		// In:	- OrderNummer
		//		- BestelBon
		//
		// Uit:	Return-value: none
		// ===================================================================================================
         
        Static function GroupOrderLijnBasedOnBestelStatus($pOrderNummer, $pBestelBon) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  	
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);			
			
			// -----------------------------------
			// Enkel voor ordetype *STOCK of *CLUB
			// -----------------------------------
			
			$sqlStat = "Select * from eba_oh_order_headers where ohOrdernummer = $pOrderNummer";

			$db->Query($sqlStat);
			
			$ohRec = $db->Row();
			
			if ($ohRec->ohOrderType != '*STOCK' && $ohRec->ohOrderType != '*CLUB') {
				$db->close();
				return;
			}
			
			$arr_artikel = array();
			$arr_maat = array();		
			$arr_bestelStatus = array();			
			$arr_aantal = array();	
			$arr_aantalRecords = array();
			
			$sqlStat = "Select odArtikel, odMaat, odBestelStatus, sum(odAantal) as aantal , count(*) as aantalRecords from eba_od_order_detail where odOrdernummer = $pOrderNummer and odBestelBon = $pBestelBon and (odBestelStatus = '*BESTELD' or odBestelStatus = '*AFHALEN' or odBestelStatus = '*KLAAR') Group BY odArtikel, odMaat, odBestelStatus Order BY odArtikel, odMaat, odBestelStatus";
										
			$db->Query($sqlStat);

			while ($odRec = $db->Row()) {
				
				
				$arr_artikel[] = $odRec->odArtikel;
				$arr_maat[] = $odRec->odMaat;				
				$arr_bestelStatus[] = $odRec->odBestelStatus;				
				$arr_aantal[] = $odRec->aantal;	
				$arr_aantalRecords[] = $odRec->aantalRecords;	
				
			}
			
			for ($i = 0; $i < count($arr_artikel); $i++) {
				
				$artikel = $arr_artikel[$i];
				$maat = $arr_maat[$i];
				$bestelStatus = $arr_bestelStatus[$i];
				$aantal = $arr_aantal[$i];
				$aantalRecords = $arr_aantalRecords[$i];
				
				if ($aantalRecords > 1) {
						
					$sqlStat 	= 	"Select * from eba_od_order_detail where odOrdernummer = $pOrderNummer "	
								.	"and odArtikel = $artikel and odMaat = '$maat' and odBestelStatus = '$bestelStatus' and odBestelBon = $pBestelBon";
								
					$db->Query($sqlStat);	

					$y = 0;
					
					while ($odRec = $db->Row()){
						
						$y++;
						$odId = $odRec->odId;
						
						if ($y == 1)
							$sqlStat = "Update eba_od_order_detail set odAantal = $aantal where odId = $odId";
						else
							$sqlStat = "Delete from eba_od_order_detail where odId = $odId";		

						$db2->Query($sqlStat);
								
						
					}
				
				}
						
				
			}
				
			$db->close();
			return;
			
		}
			
		// ===================================================================================================
		// Functie: (Stock)verwerking INTAKE stock-orderline...
		//
		// In:	- OrderLijn
		//		- Nieuwe bestelstatus
		//		- User-ID
		//
		// Uit:	Return-value: none
		// ===================================================================================================
         
        Static function HandleIntakeStockOrderLine($pOrderLijn, $pNewBestelStatus, $pUserId, $pAlways = false) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  	

			$sqlStat = "Select * from eba_od_order_detail where odId = $pOrderLijn";
											
			$db->Query($sqlStat);
			
			if (! $odRec = $db->Row()) {
				$db->close();
				return;
			}
				
			// ------------
			// Put in stock
			// ------------
			
			if (($odRec->odBestelStatus != '*ONTVANGEN' or $pAlways) && $pNewBestelStatus == '*ONTVANGEN' ) {
				
				self::PutOrderLineInStock($odRec->odId, $pUserId);
		
			}
				
			// -------------
			// Get out stock
			// -------------
			
			if (($odRec->odBestelStatus == '*ONTVANGEN' or $pAlways) && $pNewBestelStatus != '*ONTVANGEN' ) {

				self::GetOrderLineFromStock($odRec->odId, $pUserId);
		
			}

		}
		
		// ===================================================================================================
		// Functie: Ophalen cataloogprijs(en) artikel
		//
		// In:	- artikel
		//
		// Uit:	Return-value: String met cataloog-prijs(en)
		// ===================================================================================================
         
        Static function GetCataloogPrijsString($pArtikel) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  

			$sqlStat = "Select * from eba_ar_artikels where arId = $pArtikel";
			$db->Query($sqlStat);
						
			$cataloogPrijsString = "";
			
			if (! $arRec = $db->Row()) {
				$db->close();
				return $cataloogPrijsString;
			}			

			if ($arRec->arPrijsPerMaat <> 1) {
				$db->close();
				$cataloogPrijsString = $arRec->arCataloogPrijs;
				return $cataloogPrijsString;
			}
				
			$sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikel and amRecStatus = 'A'  order by amSort ";

			$db->Query($sqlStat);
			
			$cataloogPrijs = 0;
			
			while($amRec = $db->Row()){
				
				if ($amRec->amCataloogPrijs <> $cataloogPrijs) {
					$cataloogPrijs = $amRec->amCataloogPrijs;
					if ($cataloogPrijsString <= " ")
						$cataloogPrijsString = $amRec->amCataloogPrijs;
					else
						$cataloogPrijsString .= " - $amRec->amCataloogPrijs";
				}
					
			}
				
			$db->close();
			return $cataloogPrijsString;

		}
		
		// ===================================================================================================
		// Functie: Aanmaken"standaard" maten voor een artikel
		//
		// In:	- Artikel
		//		- User-ID
		//
		// Uit:	 Niets
		// ===================================================================================================
         
        Static function CrtStdMaten($pArtikel, $pUserId) { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 

			// --------------------------------------
			// Enkel indien nog geen maten aangemaakt
			// --------------------------------------

			$sqlStat = "Select count(*) as aantal from eba_am_artikelmaten where amArtikel = $pArtikel";
				
			if ($db->Query($sqlStat)) {
				
					$amRec = $db->Row();
					if ($amRec->aantal > 0)
						return false;
			}
			else	
				error_log($sqlStat);
			

			// --------------------------
			// Aanmaken "standaard" maten
			// --------------------------
		
			$arr_maat = array();
            $arr_maattype = array();

			$arr_maat[] = '116';
            $arr_maattype[] = 'JR';

			$arr_maat[] = '128';
            $arr_maattype[] = 'JR';

			$arr_maat[] = '140';
            $arr_maattype[] = 'JR';

			$arr_maat[] = '152';
            $arr_maattype[] = 'JR';

			$arr_maat[] = '164';
            $arr_maattype[] = 'JR';

			$arr_maat[] = 'S';
            $arr_maattype[] = 'SR';

			$arr_maat[] = 'M';
            $arr_maattype[] = 'SR';

			$arr_maat[] = 'L';
            $arr_maattype[] = 'SR';

			$arr_maat[] = 'XL';
            $arr_maattype[] = 'SR';

			$arr_maat[] = 'XXL';
            $arr_maattype[] = 'SR';

			$arr_maat[] = '3XL';
            $arr_maattype[] = 'SR';

			$arr_maat[] = '4XL';
            $arr_maattype[] = 'SR';

			for ($i = 0; $i < count($arr_maat); $i++) {

				$maat = $arr_maat[$i];
                $maattype = $arr_maattype[$i];
			
				$amSort = ($i + 1) * 10;

				$values["amArtikel"] = MySQL::SQLValue($pArtikel, MySQL::SQLVALUE_NUMBER );
				$values["amMaat"] = MySQL::SQLValue($maat);
                $values["amMaattype"] = MySQL::SQLValue($maattype);

				$values["amCataloogPrijs"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );
				$values["amPrijs"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );	
				$values["amSort"] = MySQL::SQLValue($amSort, MySQL::SQLVALUE_NUMBER );	
				
				$values["amUserCreatie"] = MySQL::SQLValue($pUserId);				
				$values["amUserUpdate"] = MySQL::SQLValue($pUserId);
					
				$id = $db->InsertRow("eba_am_artikelmaten", $values); 
				
				$sqlStat = "Update eba_am_artikelmaten set amDatumCreatie = now(), amDatumUpdate = now(), amRecStatus = 'A' where amId = $id";
			
				$db->Query($sqlStat);	

		
			}
				

			return true;
			
		}
		// ===================================================================================================
		// Functie: Zet orders op volledig afgewerkt
		//
		// In:	Niets
		//	
		//
		// Uit:	niets
		// ===================================================================================================
         
        Static function PutOrdersVolledigAfgewerkt() { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			$sqlStat = "Update eba_oh_order_headers set ohVolledigAfgewerkt = 0 where ohVolledigAfgewerkt is Null";
			$db->Query($sqlStat);

			$sqlStat = "SELECT * FROM eba_oh_order_headers where ohVolledigAfgewerkt <> 1";

			if (!$db->Query($sqlStat)) 
				return;

			while ($ohRec = $db->Row()) {

				$magNaarVolledigAfgewerkt = self::MagNaarVolledigAfgewerkt($ohRec->ohOrdernummer, $errMsg, true);

				if ($magNaarVolledigAfgewerkt == true)		
					self::SetVolledigAfgewerkt($ohRec->ohOrdernummer);

			}
			
		}
		
		
		// ===================================================================================================
		// Functie: Stuur mail ivm orders "in winkelwagen"
		//
		// In:	Niets
		//	
		//
		// Uit:	Aantal mails verzonden
		// ===================================================================================================
         
        Static function SndMailsWinkelwagen() { 
		
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			
			include_once(Sx::GetSxClassPath("tools.class"));  
			
			$sqlStat = "Select distinct(wwUserId) as persoon from eba_ww_winkelwagen where (wwLaatsteMailGestuurd is null) or  date(wwLaatsteMailGestuurd) < current_date - interval 7 DAY";
			$db->Query($sqlStat);
			
			$aantalMails = 0;
			
			while ($wwRec = $db->Row()) {
			
				$persoon = $wwRec->persoon;
				
				$adRec = self::db_adRec($persoon);
				
				if ($adRec == null)
					continue;
					
				if ($adRec->adRecStatus <> 'A')
					continue;
				

				
				$mailString = self::GetKlantMailString($persoon);
				$voornaamNaam = $adRec->adVoornaamNaam;
				
				if ($mailString <= ' ')
					continue;
				
				
				$mailTo = $mailString;
				$bccMail = 'gvh@vecasoftware.com';
					
				$fromMail = 'webshop@schellesport.be';
				$fromName = 'Schelle Sport - Secretariaat';
					
				$mailBody 	= 	'Beste,'
							.	'<br/><br/>'
							.	'*** Deze mail werd automatisch verstuurd door Schelle Sport - webshop ***'
							.	'<br/><br/>'
							.	"Er staan nog bestellingen in de winkelwagen van $voornaamNaam!"
							. 	'<br/>'
							.	'Gelieve deze door te voeren via de knop "bestelling plaatsen", of eventueel te wissen.'
							.	'<br/><br/>'
							.	"Login: $persoon"
							.	'<br/>'
							.	'Je kan het wachtwoord eventueel terug instellen via "wachtwoord vergeten?" (onder de menu "Mijn toepassingen")'
							. 	'<br/><br/>'
							.	'Aarzel niet mij te contacteren indien je hier nog vragen over hebt.'
							.	'<br/><br/>'
							.	'Sportieve groet,'
							.	'<br/><br/>'
							.	'Geert Verhelst'
							.	'<br/><br/>'
							.	'Secretariaat & IT | Schelle Sport'
							.	'<br/>'
							.	'Kapelstraat 140, 2627 Schelle'
							.	'<br/>'
							.	'webshop@schellesport.be |www.schellesport.be';
					
							
								

				SX_tools::SendMail('Schelle Sport Webshop - Nog bestellingen in uw winkewagen', $mailBody, $mailTo, $bccMail, $fromMail, $fromName);
				$aantalMails++;
				
				
				$wwId = $wwRec->wwId;
				$sqlStat = "Update eba_ww_winkelwagen set wwLaatsteMailGestuurd = now() where wwUserId = '$persoon'";
				$db2->Query($sqlStat);

			
			}
			
			return $aantalMails;
			
		}

         // ===================================================================================================
         // Functie: Ophalen som (maximale) webshopprijs onderliggende artikels
         //
         // In:	- Pakket
         //     - Type (*WEBSHOP, *CATALOOG, *AANKOOP)
         //     - Min of MAX (*MIN/*MAX)
         //
         // Uit: Return-value: Prijs
         // ===================================================================================================

         Static function GetPakketArtikelsPrijs($pPakket, $pType = '*WEBSHOP', $pMinMax = '*MIN') {

             include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
             include_once(SX::GetClassPath("_db.class"));

             $pkRec = SSP_db::Get_EBA_pkRec($pPakket);

             $pkMaattype = "";

             if ($pkRec)
                 $pkMaattype = $pkRec->pkMaattype;

             $sqlStat = "Select * from eba_pa_pakket_artikels where paPakket = $pPakket";
             $db->Query($sqlStat);

             $pakketLaagstePrijs = 0;
             $pakketHoogstePrijs = 0;

             while ($paRec = $db->Row()){

                 $artikel = $paRec->paArtikel;

                 $sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $artikel and amRecStatus = 'A' order by amSort";
                 $db2->Query($sqlStat);

                 $laagstePrijs = 99999;
                 $hoogstePrijs = 0;

                 while ($amRec = $db2->Row()){

                     $maattype = $amRec->amMaattype;
                     $maat = $amRec->amMaat;

                     if ($maattype and $pkMaattype and ($maattype != $pkMaattype))
                         continue;

                     $prijs = self::GetArtikelPrijs($artikel, $maat, $pType);

                     if ($prijs < $laagstePrijs)
                         $laagstePrijs = $prijs;
                     if ($prijs > $hoogstePrijs)
                         $hoogstePrijs = $prijs;


                 }

                 $pakketLaagstePrijs += $laagstePrijs;
                 $pakketHoogstePrijs += $hoogstePrijs;
             }

             // -------------
             // Einde functie
             // -------------

             $pakketLaagstePrijs = floor($pakketLaagstePrijs);
             $pakketHoogstePrijs = floor($pakketHoogstePrijs);

             if ($pMinMax == '*MIN')
                 return $pakketLaagstePrijs;
             else
                 return $pakketHoogstePrijs;

        }

         // ===================================================================================================
         // Functie:Update pakket prijzen (van betreffende artikels)
         // ===================================================================================================

         Static function UpdPakketArtikelPrijzen(){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

            $sqlStat = "Select * from eba_pk_pakketten where pkRecStatus = 'A'";

            $db->Query($sqlStat);

            while ($pkRec = $db->Row()){

                $pakket = $pkRec->pkId;

                $pkPrijsAankoopMin = SSP_eba::GetPakketArtikelsPrijs($pakket, '*AANKOOP', '*MIN');
                $pkPrijsAankoopMax = SSP_eba::GetPakketArtikelsPrijs($pakket, '*AANKOOP', '*MAX');

                $pkPrijsWebshopMin = SSP_eba::GetPakketArtikelsPrijs($pakket, '*WEBSHOP', '*MIN');
                $pkPrijsWebshopMax = SSP_eba::GetPakketArtikelsPrijs($pakket, '*WEBSHOP', '*MAX');

                $values = array();
                $where = array();

                $values["pkPrijsWebshopMin"] =  MySQL::SQLValue($pkPrijsWebshopMin, MySQL::SQLVALUE_NUMBER);
                $values["pkPrijsWebshopMax"] =  MySQL::SQLValue($pkPrijsWebshopMax, MySQL::SQLVALUE_NUMBER);

                $values["pkPrijsAankoopMin"] =  MySQL::SQLValue($pkPrijsAankoopMin, MySQL::SQLVALUE_NUMBER);
                $values["pkPrijsAankoopMax"] =  MySQL::SQLValue($pkPrijsAankoopMax, MySQL::SQLVALUE_NUMBER);

                $where["pkId"] =  MySQL::SQLValue($pakket, MySQL::SQLVALUE_NUMBER);

                $db2->UpdateRows("eba_pk_pakketten", $values, $where);

            }

            // -------------
            // Einde functie
            // -------------

        }

         // ===================================================================================================
         // Functie: Toevoegen aan winkelwagen
         //
         // In: - User-id
         //     - Pakket
         //     - Artikel
         //     - Maat
         //
         // Uit: Return-value: Aangemaakt? true/false
         // ===================================================================================================

         Static function AddWinkelwagen($pUserId, $pPakket, $pArtikel, $pMaat) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $curDateTime = date('Y-m-d H:i:s');


             if (! $pPakket) {
                 $prijs = self::GetArtikelPrijs($pArtikel, $pMaat);
                 $inLidgeld = 0;
             }
             // --------------------------------------------
             // Registreer pakket-prijs (indien niet gratis)
             // --------------------------------------------

             if ($pPakket){

                 $pkRec = SSP_db::Get_EBA_pkRec($pPakket);

                 if ($pkRec and $pkRec->pkPrijs) {


                     $sqlStat = "Select * from eba_ww_winkelwagen where wwPakket = $pPakket and wwArtikel = 0";
                     $db->Query($sqlStat);

                     if (! $wwRec = $db->Row()){

                         $values["wwUserId"] = MySQL::SQLValue($pUserId);
                         $values["wwPakket"] = MySQL::SQLValue($pPakket, MySQL::SQLVALUE_NUMBER );
                         $values["wwArtikel"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );
                         $values["wwMaat"] = MySQL::SQLValue("");

                         $values["wwPrijs"] = MySQL::SQLValue($pkRec->pkPrijs, MySQL::SQLVALUE_NUMBER );
                         $values["wwInLidgeld"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER );

                         $values["wwDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                         $id = $db->InsertRow("eba_ww_winkelwagen", $values);
                     }

                 }
             }

             // ------------------
             // Registreer artikel
             // ------------------

             $values["wwUserId"] = MySQL::SQLValue($pUserId);
             $values["wwPakket"] = MySQL::SQLValue($pPakket, MySQL::SQLVALUE_NUMBER );
             $values["wwArtikel"] = MySQL::SQLValue($pArtikel, MySQL::SQLVALUE_NUMBER );
             $values["wwMaat"] = MySQL::SQLValue($pMaat);

             $values["wwPrijs"] = MySQL::SQLValue($prijs, MySQL::SQLVALUE_NUMBER );
             $values["wwInLidgeld"] = MySQL::SQLValue($inLidgeld, MySQL::SQLVALUE_NUMBER );

             $values["wwDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

             $id = $db->InsertRow("eba_ww_winkelwagen", $values);


             // -------------
             // Einde functie
             // -------------


         }

         // ===================================================================================================
         // Functie: Copy pakket
         //
         // In: - User-id
         //     - Pakket van
         //     - Pakket naar
         //
         // ===================================================================================================

         Static function CopyPakketItems($pUserId, $pPakketVan, $pPakketNaar) {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

            if ($pPakketVan == $pPakketNaar)
                return;

            // --------------------------------
            // Wissen alle items in pakket-naar
            // --------------------------------

            $sqlStat = "Delete From eba_pa_pakket_artikels where paPakket = $pPakketNaar";

            $db->Query($sqlStat);

            // -------------------
            // Copiëren alle items
            // -------------------

             $curDateTime = date('Y-m-d H:i:s');

             $sqlStat = "Select * from eba_pa_pakket_artikels where paPakket = $pPakketVan";
             $db->Query($sqlStat);

             $values = array();

             while ($pkRec = $db->Row()){

                 $arikel = $pkRec->paArtikel;
                 $sort = $pkRec->paSort;

                 $values["paPakket"] = MySQL::SQLValue($pPakketNaar, MySQL::SQLVALUE_NUMBER );
                 $values["paArtikel"] = MySQL::SQLValue($arikel, MySQL::SQLVALUE_NUMBER );
                 $values["paSort"] = MySQL::SQLValue($sort, MySQL::SQLVALUE_NUMBER );

                 $values["paDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["paDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                 $values["paUserCreatie"] = MySQL::SQLValue($pUserId);
                 $values["paUserUpdate"] = MySQL::SQLValue($pUserId);

                 $id = $db2->InsertRow("eba_pa_pakket_artikels", $values);

             }

            // -------------
            // Einde functie
            // -------------

            return;

         }


         // ===================================================================================================
         // Functie: Stuur mail ivm STOCKBREUK
         //
         // In:	mailadre
         //
         // ===================================================================================================

         Static function SndMailStockbreuk($pMailTo = 'gvh@vecasoftware.com') {

             include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object

             include_once(Sx::GetSxClassPath("tools.class"));


             $sqlStat = "Select count(*) as aantal from eba_as_artikel_stock inner Join eba_ar_artikels on arId = asArtikel left outer join eba_am_artikelmaten on amArtikel = asArtikel and amMaat = asMaat WHERE arRecStatus = 'A' and ((asStock + asInBestellingVoorStock - asGereserveerd) < amStockOnder)";

            $db->Query($sqlStat);

            $asRec = $db->Row();

            if (! $asRec)
                return;

            if ($asRec->aantal <= 0)
                return 0;

            $aantal = $asRec->aantal;

            $fromMail = 'eba@schellesport.be';
            $fromName = 'Schelle Sport - EBA';

            $mailBody 	= "Er zijn $aantal items met STOCKBREUK.";


            SX_tools::SendMail('Schelle Sport EBA - STOCKBREUK', $mailBody, $pMailTo, "", $fromMail, $fromName);

            // -------------
            // Einde functie
            // -------------

             return $aantal;

         }

         // ===================================================================================================
         // Functie: Check Status alle openstaande ordere
         //
         // ===================================================================================================

         Static function ChkStatusAlleOpenOrders(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from eba_oh_order_headers where ohVolledigAfgewerkt <> 1";
             $db->Query($sqlStat);

             while ($ohRec = $db->Row()){

                 $order = $ohRec->ohOrdernummer;
                 self::ChkOrder($order);

             }

             // -------------
             // Einde functie
             // -------------

         }

         // ===================================================================================================
         // Functie: Zet status "klaar voor afleveren" voor alle openstaande klant-orders
         //
         // ===================================================================================================

         Static function SetKlantOrdersKlaarVoorAfleveren(){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from eba_oh_order_headers where ohVolledigAfgewerkt <> 1 and ohOrderType = '*KLANT'";
             $db->Query($sqlStat);

             while ($ohRec = $db->Row()){

                 $order = $ohRec->ohOrdernummer;
                 self::SetOrderKlaarVoorAfleveren($order);
             }

             // -------------
             // Einde functie
             // -------------

         }

         // ===================================================================================================
         // Functie: Zet status "klaar voor afleveren"
         //
         // In:	Order
         //
         // ===================================================================================================

         Static function SetOrderKlaarVoorAfleveren($pOrder) {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             include_once(SX::GetClassPath("_db.class"));

             $sqlStat = "Select count(*) as aantal From eba_od_order_detail where odOrdernummer= $pOrder and (odLeverStatus = '*KLAAR' or odLeverStatus = '*GELEVERD')";

             $db->Query($sqlStat);

             $klaarVoorAfleveren = 0;

             if ($odRec = $db->Row()){

                 if ($odRec->aantal > 0)
                     $klaarVoorAfleveren = 1;

             }

             $sqlStat = "update eba_oh_order_headers set ohKlaarVoorAfleveren = $klaarVoorAfleveren where ohOrdernummer =$pOrder";

             $db->Query($sqlStat);

             // -------------
             // Einde functie
             // -------------


         }


         // ===================================================================================================
         // Functie: Zet Leveranciertype pakbon (*INTERN/*EXTERN/*BEIDE)
         //
         // In:	Order
         //
         // ===================================================================================================

         Static function SetLevTypePakbon($pOrder){

            include(SX::GetSxClassPath("mysql.incl"));
            include_once(SX::GetClassPath("_db.class"));

            $levTypePakbon = "*NONE";

            $sqlStat = "Select * from eba_od_order_detail where odOrdernummer = $pOrder";

            $db->Query($sqlStat);

            while ($odRec = $db->Row()){

                $leverancier = $odRec->odLeverancier;

                if ($leverancier == 1){

                    if ($levTypePakbon == "*NONE")
                        $levTypePakbon = "*EXTERN";
                    if ($levTypePakbon == "*INTERN")
                        $levTypePakbon = "*BEIDE";
                }

                if ($leverancier != 1){

                    if ($levTypePakbon == "*NONE")
                        $levTypePakbon = "*INTERN";
                    if ($levTypePakbon == "*EXTERN")
                        $levTypePakbon = "*BEIDE";
                }

                if ($levTypePakbon == "*BEIDE")
                    break;

            }

            $sqlStat = "Update eba_oh_order_headers set ohLevTypePakbon = '$levTypePakbon' where ohOrdernummer = $pOrder";
            $db->Query($sqlStat);

         }

         // ===================================================================================================
         // Functie: Ophalen (eerste) datum bestelbon bekeken door de levercier
         //
         // In:	Bestelbon
         //
         // Return: DatumTijd of null
         // ===================================================================================================

         Static function GetDatumBestelbonBekekenDoorLeverancier($pBestelbon) {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            include_once(SX::GetClassPath("_db.class"));

            $bhRec = SSP_db::Get_EBA_bhRec($pBestelbon);

            if (! $bhRec)
                return null;

            $leverancier = $bhRec->bhLeverancier;

            $leRec = SSP_db::Get_EBA_leRec($leverancier);

            if (! $leRec)
                 return null;

            $userIdLeverancier = $leRec->leUserId;

            $sqlStat = "Select DATE_FORMAT(Date(loDateTime), '%d-%m-%Y') as datum from sx_lo_logs where loGroup = 'EBA' and loType = 'DOC_BESTELBON' and loKey = '$pBestelbon'  and loPerson = '$userIdLeverancier' order by loDateTime";

            $db->Query($sqlStat);

            if (! $loRec = $db->Row())
                return null;
            else
                return $loRec->datum;

         }


         // ===================================================================================================
         // Functie: Opvullen # dagen in bestelling (in dagen)
         //
         // In:	Orderlijn
         // ===================================================================================================

         Static function FillOrderlijnAantalDagenInBestelling($pOrderlijn) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "select odBestelBon, bhBestelDatum, odBestelStatus, date(odOntvangenOp), DATEDIFF(curdate(), bhBestelDatum) as aantalDagenInBestellingBerekend FROM eba_od_order_detail INNER JOIN eba_bh_bestel_headers ON bhId = odBestelBon  where odId = $pOrderlijn and odBestelStatus <> '*ONTVANGEN' and odBestelStatus <> '*AFHALEN'";

            $db->Query($sqlStat);

            if (! ($odRec = $db->Row()))
                return;

            // ------
            // Update
            // ------

            $aantalDagenInBestellingBerekend = $odRec->aantalDagenInBestellingBerekend;

            $values = array();
            $where = array();

            $values["odAantalDagenInBestelling"] =  MySQL::SQLValue($aantalDagenInBestellingBerekend, MySQL::SQLVALUE_NUMBER);

            $where["odId"] =  MySQL::SQLValue($pOrderlijn, MySQL::SQLVALUE_NUMBER);

            $db->UpdateRows("eba_od_order_detail", $values, $where);

            // -------------
            // Einde functie
            // -------------

            return;

         }

         // ===================================================================================================
         // Functie: Opvullen levertermijn (in dagen)
         //
         // In:	Orderlijn
         // ===================================================================================================

         Static function FillAantalDagenInBestelling() {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from eba_od_order_detail where odBestelStatus <> '*ONTVANGEN' and odBestelStatus <> '*AFHALEN'";
             $db->Query($sqlStat);

             while ($odRec = $db->Row())
                    self::FillOrderlijnAantalDagenInBestelling($odRec->odId);

             // -------------
             // Einde functie
             // -------------

             return;

         }

         // ===================================================================================================
         // Functie: Aanmaken artikel-card (HTML snippet)
         //
         // In:	Artikel
         // Uit: HTML snippet Artikel CARD
         // ===================================================================================================

         Static function GetArtikelCard($pArtikel){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $arRec = SSP_db::Get_EBA_arRec($pArtikel);

             if (! $arRec)
                 return "";

             // -----------
             // Foto (klein)
             // ------------

             $fotoPath = '';
             $fotos = json_decode($arRec->arFoto);

             if ($fotos) {

                 foreach ($fotos as $foto) {

                     if (strpos($foto->type, "image") !== false) {
                         $fotoPath = $foto->name;
                         break;
                     }


                 }
             }

             $html = "<div class='card shadow-lg ' style='margin-top: 10px;'>";

             if ($arRec->arInAanbieding == 1)
                $html .= "<div class=\"ribbon red\"><span>AANBIEDING</span></div>";

             $html .= "<div class='card-header'>";
             $html .= "<h4>$arRec->arNaam</h4>";
             $html .= "</div>";


             $html .= "<div class='card-body'>";

             $html .= "<div class='container'><div class='row'>";



             $html .= "<div class='col-md-4'>";
             $html .= "<img class=\"card-img\" style='max-width: 300px' src='$fotoPath' alt=\"Card image cap\">";
             $html .= "</div>";
             $html .= "<div class='col-md-8'>";
             $html .= nl2br($arRec->arOmschrijving);

             $prijsInfo = self::GetArtikelPrijsInfo($pArtikel);

             if ($prijsInfo)
                 $html .= "<div style='font-weight: bold; padding-top: 15px'>Prijs: $prijsInfo</div>";

             $html .= "<br style='margin-bottom: 10px'/><a href=\"javascript: return false;\"  data-type='A' id='$arRec->arId' class=\" btn btn-success butToevoegen \">Aan winkelwagen toevoegen</a>";

             $html .= "</div>";

             $html .= "</div></div>";

             $html .= "</div>";

             $html .= "</div>";

             // -------------
             // Einde functie
             // -------------

             return $html;

         }


         // ===================================================================================================
         // Functie: Aanmaken pakket-card (HTML snippet)
         //
         // In:	Pakket
         //
         // Uit: HTML snippet Pakket CARD
         // ===================================================================================================

         Static function GetPakketCard($pPakket){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $pkRec = SSP_db::Get_EBA_pkRec($pPakket);

             if (! $pkRec)
                 return "";

             // -----------
             // Foto (klein)
             // ------------

             $fotoPath = '';
             $fotos = json_decode($pkRec->pkFoto);

             if ($fotos) {

                 foreach ($fotos as $foto) {

                     if (strpos($foto->type, "image") !== false) {
                         $fotoPath = $foto->name;
                         break;
                     }


                 }
             }


             $html = "<div class='card shadow-lg ' style='margin-top: 10px;'>";


             if ($pkRec->pkInLidgeld == 1)
                $html .= "<div class=\"ribbon blue\"><span>LIDGELD</span></div>";
             elseif ($pkRec->pkGratis == 1 )
                 $html .= "<div class=\"ribbon red\"><span>GRATIS</span></div>";

             $html .= "<div class='card-header'>";
             $html .= "<h4>$pkRec->pkNaam</h4>";
             $html .= "</div>";


             $html .= "<div class='card-body'>";

             $html .= "<div class='container'><div class='row'>";



             $html .= "<div class='col-md-4'>";
             if ($fotoPath)
                $html .= "<img class=\"card-img\" style='max-width: 300px' src='$fotoPath' alt=\"Geen afbeelding\">";
             else
                 $html .= "<div style='padding-bottom:10px; font-style: italic'>Geen afbeelding beschikbaar</div>";

             $html .= "</div>";
             $html .= "<div class='col-md-8'>";
             $html .= nl2br($pkRec->pkOmschrijving);

             $html .= "<br style='margin-bottom: 10px'/><a href=\"javascript: return false;\"  data-type='P' id='$pkRec->pkId' class=\" btn btn-success butToevoegen \">Aan winkelwagen toevoegen</a>";

             $html .= "</div>";

             $html .= "</div></div>";

             $html .= "</div>";

             $html .= "</div>";

             // -------------
             // Einde functie
             // -------------

             return $html;

         }


         // ===================================================================================================
         // Functie: Check Rubriek DOELGROEP
         //
         // In:	Rubriek
         //     User-id
         //
         // Uit: Geldig (true/false)
         // ===================================================================================================

         Static function ChkRubriekDoelgroep($pRubriek, $pUserId) {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            include_once(SX::GetClassPath("_db.class"));

            $ruRec = SSP_db::Get_EBA_ruRec($pRubriek);

            if ($ruRec->ruDoelgroep){

                $doelgroepOK = SSP_eba::ChkDoelgroep($pUserId, $ruRec->ruDoelgroep);

                if (! $doelgroepOK)
                    return false;

            }

            // -------------
            // Einde functie
            // -------------

             return true;

        }

         // ===================================================================================================
         // Functie: Aanmaken rubriek-NAVBAR (HTML snippet)
         //
         // In:	UserId
         // Uit: HTML snippet Rubriek HEADER
         // ===================================================================================================

         Static function GetRubriekenNavBar($pUserId){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             $html = "<nav class=\"navbar navbar-expand-sm\" style=\"background-color: #0A529E; margin-top: 10px\">";
             $html .= " <ul class=\"navbar-nav\">";

             $sqlStat = "Select * from eba_ru_rubrieken where ruRecStatus = 'A' order by ruSort";

             $db->Query($sqlStat);

             while ($ruRec = $db->Row()){

                 $rubriek = $ruRec->ruId;
                 $refId = "rubriek$rubriek";
                 $naam = $ruRec->ruNaam;

                 $ok = self::ChkRubriekDoelgroep($rubriek, $pUserId);

                 if (! $ok)
                    continue;

                 $html .= "<li class=\"nav-item\">";
                 $html .= " <a class=\"nav-link\" href=\"#$refId\" style=\"color:#FFEB10; font-weight: bold;\">$naam</a>";
                 $html .= "</li>";

             }

            $html .= "</ul></nav>";

            // -------------
            // Einde functie
            // -------------

            return $html;

         }


         // ===================================================================================================
         // Functie: Ophalen artikel-prijs info
         //
         // In:	Artikel
         // Uit: String met prijs-info
         // ===================================================================================================

         Static function GetArtikelPrijsInfo($pArtikel){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            include_once(SX::GetClassPath("_db.class"));

            $arRec = SSP_db::Get_EBA_arRec($pArtikel);

            if ($arRec->arPrijs and (! $arRec->arPrijsPerMaat))
                return $arRec->arPrijs + 0;

            $info = "";

            $sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikel and amMaattype = 'JR' limit 1";
            $db->Query($sqlStat);

            if ($amRec = $db->Row()) {
                $prijs = $amRec->amPrijs + 0;
                $info .= "JR: $prijs";
            }

             $sqlStat = "Select * from eba_am_artikelmaten where amArtikel = $pArtikel and amMaattype = 'SR' limit 1";
             $db->Query($sqlStat);

             if ($amRec = $db->Row()) {
                 $prijs = $amRec->amPrijs + 0;
                 $info .= ", SR: $prijs";
             }

             if ($info)
                 return $info;

             $sqlStat = "Select min(amPrijs) as prijs from eba_am_artikelmaten where amArtikel = $pArtikel";
             $db->Query($sqlStat);
             if ($amRec = $db->Row())
                 $minPrijs = $amrec->prijs + 0;

             $sqlStat = "Select max(amPrijs) as prijs from eba_am_artikelmaten where amArtikel = $pArtikel";
             $db->Query($sqlStat);
             if ($amRec = $db->Row())
                 $maxPrijs = $amrec->prijs + 0;

             if ($minPrjs = $maxPrijs)
                 return $minPrijs;

             return "Afhankelijk maat van $minPrijs tot $maxPrijs";



         }

         // ===================================================================================================
         // Functie: Komt artikel voor in onze webshop?
         //
         // In:	Artikel
         //
         // Uit: True/false
         // ===================================================================================================

         Static function ChkArtikelInWebshop($pArtikel){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

            $sqlStat = "Select count(*) as aantal from eba_ra_rubriek_artikels inner join eba_ru_rubrieken on eba_ru_rubrieken.ruId = eba_ra_rubriek_artikels.raRubriek and eba_ru_rubrieken.ruRecStatus = 'A' where raArtikel = $pArtikel";

            // echo $sqlStat;

            $db->Query($sqlStat);

            if ($raRec = $db->Row())
                if ($raRec->aantal > 0)
                    return true;

            // -------------
            // Einde functie
            // -------------

            return false;

         }

         // ===================================================================================================
         // Functie: Automatische aanmaken stock orderlijnen
         //
         // In:	Ordernummer
         //
         // Uit: Aantal orderlijnen aangemaakt
         // ===================================================================================================

         Static function CrtAutoStockOrder($pStockOrder){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

            include_once(SX::GetClassPath("_db.class"));

            $ohRec = SSP_db::Get_EBA_ohRec($pStockOrder);

            if (! $ohRec)
                return 0;

            $userId = $ohRec->ohUserUpdate;

            $aantalLijnen = 0;

            $sqlStat    = "Select * from eba_as_artikel_stock "
                        . "Left Outer Join eba_ar_artikels on arId = asArtikel "
                        . "left outer join eba_am_artikelmaten on amArtikel = asArtikel and amMaat = asMaat "
                        . "Where arRecStatus = 'A' and ((asStock + asInBestellingVoorStock - asGereserveerd) < amStockOnder) "
                        . "order by asArtikel, amSort";

            $db->Query($sqlStat);

            while ($asRec = $db->Row()) {

                $artikel = $asRec->asArtikel;
                $maat = $asRec->asMaat;

                $aantal = $asRec->amStockBoven - ($asRec->asStock + $asRec->asInBestellingVoorStock - $asRec->asGereserveerd);
                $veelvoud = $asRec->amBestelVeelvoud;

                if (! $veelvoud)
                    $veelvoud = 1;

                if ($veelvoud > 1) {

                    if ($aantal <= $veelvoud)
                        $aantal = $veelvoud;
                    else
                        $aantal = ceil($aantal / $veelvoud) * $veelvoud;
                }

                $leverancier = self::GetExtLev($artikel);

                $curDateTime = date('Y-m-d H:i:s');
                $curDate = date('Y-m-d');

                $values = array();

                $values["odOrdernummer"] = MySQL::SQLValue($pStockOrder, MySQL::SQLVALUE_NUMBER);

                $values["odArtikel"] = MySQL::SQLValue($artikel, MySQL::SQLVALUE_NUMBER);
                $values["odMaat"] = MySQL::SQLValue($maat, MySQL::SQLVALUE_TEXT);
                $values["odAantal"] = MySQL::SQLValue($aantal, MySQL::SQLVALUE_NUMBER);

                $values["odGratis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
                $values["odManuelePrijs"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
                $values["odRedenGratis"] = MySQL::SQLValue($ohRec->ohOrderType, MySQL::SQLVALUE_TEXT);

                $values["odBestelStatus"] = MySQL::SQLValue('*WACHT', MySQL::SQLVALUE_TEXT);
                $values["odLeverStatus"] = MySQL::SQLValue(' ', MySQL::SQLVALUE_TEXT);
                $values["odPakket"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
                $values["odLeverMailGestuurd"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);

                $values["odLeverancier"] = MySQL::SQLValue($leverancier, MySQL::SQLVALUE_NUMBER);


                $values["odDatumCreatie"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
                $values["odDatumUpdate"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATETIME);

                $values["odTijdCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["odTijdUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $values["odUserCreatie"] = MySQL::SQLValue($userId, MySQL::SQLVALUE_TEXT);
                $values["odUserUpdate"] = MySQL::SQLValue($userId, MySQL::SQLVALUE_TEXT);

                $id = $db2->InsertRow("eba_od_order_detail", $values);

                self::PresetBestelStatus($id);

                $aantalLijnen++;

            }

            self::ChkOrder($pStockOrder);

            // -------------
            // Einde functie
            // -------------

            return $aantalLijnen;


         }


         // ===================================================================================================
         // Functie: Zet orderlijn bestelstatus "stockitems" (*AFHALEN of *BACKORDER)
         //
         // In:	Artikel
         //     Maat
         //     Update Header status?
         // ===================================================================================================

         Static function SetBestelstatusStockItem($pArtikel, $pMaat, $pUpdateHeaderStatus = true){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

            include_once(SX::GetClassPath("_db.class"));

            $StockLeverancier = self::GetStockLeverancier();

            $asRec = SSP_db::Get_EBA_asRec($pArtikel, $pMaat);

            if (! $asRec)
                return;

            // ----------------------------
            // Stock Volgens FIFO toewijzen
            // ----------------------------

            $stock= $asRec->asStock;

            $sqlStat = "Select * from eba_od_order_detail Where odArtikel = $pArtikel and odMaat = '$pMaat' and odLeverancier = $StockLeverancier and (odBestelStatus = '*BACKORDER' or odBestelStatus = '*AFHALEN') order by odOrdernummer";

            $db->Query($sqlStat);

            while ($odRec = $db->Row()){

                if ($stock >0)
                    $bestelStatus = '*AFHALEN';
                else
                    $bestelStatus = '*BACKORDER';

                if ($odRec->odStockNietInBackorder)
                    $bestelStatus = '*AFHALEN';

                if ($bestelStatus != $odRec->odBestelStatus){

                    $sqlStat = "Update eba_od_order_detail set odBestelStatus = '$bestelStatus' where odId = $odRec->odId";
                    $db2->Query($sqlStat);

                    self::SetOrderStatus($odRec->odOrdernummer, false);

                }

                $stock = $stock - 1;

            }

            // -------------
            // Einde functie
            // -------------

            return;

         }

         // ===================================================================================================
         // Functie: Zet  bestelstatus "stockitems" (*AFHALEN of *BACKORDER) alle stock-items
         // ===================================================================================================

         Static function SetBestelstatusAlleStockItems() {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

            $sqlStat = "Select * from eba_as_artikel_stock where asGereserveerd > 0";
            $db->Query($sqlStat);

            while ($asRec = $db->Row())
                self::SetBestelstatusStockItem($asRec->asArtikel, $asRec->asMaat);

            // -------------
            // Einde functie
            // -------------

         }

         // ===================================================================================================
         // Functie: Zet datum "klaar afhalen"
         // ===================================================================================================

         Static function SetKlaarAfhalenDatum() {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             $sqlStat   = "SELECT * FROM eba_od_order_detail "
                        . "INNER JOIN eba_oh_order_headers ON ohOrdernummer = odOrdernummer AND ohVolledigAfgewerkt <> 1 "
                        . "WHERE (odKlaarAfhalenDatum is NULL or odKlaarAfhalenDatum < '2000-01-01') AND odBestelStatus = '*AFHALEN'";

             $db->Query($sqlStat);

             while ($odRec = $db->Row()) {

                 $id = $odRec->odId;

                 $curDate = date('Y-m-d');

                 $values = array();
                 $where = array();

                 $values["odKlaarAfhalenDatum"] =  MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
                 $where["odId"] =  MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);

                 $db2->UpdateRows("eba_od_order_detail", $values, $where);

             }

             // -------------
             // Einde functie
             // -------------

         }
         // ===================================================================================================
         // Functie: Toevoegen alle artikel pakketten aan rubiek
         //
         // In:	Rubriek
         //     USER-id
         //
         //  Return: # Pakketten toegevoegd
         //
         // ===================================================================================================

         Static function AddLidgeldPakkettenAanRubriek($pRubriek, $pUser){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

            $sqlStat = "Select * from eba_pk_pakketten where pkInLidgeld = 1 and pkRecStatus = 'A' order by pkNaam";
            $db->Query($sqlStat);

            $aantal = 0;
            $sort = 0;

            while ($pkRec = $db->Row()){

                 $pakket = $pkRec->pkId;

                 $sqlStat = "Select count(*) as aantal from eba_ra_rubriek_artikels where raRubriek = $pRubriek and raPakket = $pakket";
                 $db2->Query($sqlStat);

                 if ($raRec = $db2->Row())
                     if ($raRec->aantal)
                         continue;

                 $curDateTime = date('Y-m-d H:i:s');
                 $sort += 10;

                 $values = array();

                 $values["raRubriek"] = MySQL::SQLValue($pRubriek, MySQL::SQLVALUE_NUMBER);
                 $values["raPakket"] = MySQL::SQLValue($pakket, MySQL::SQLVALUE_NUMBER);
                 $values["raSort"] = MySQL::SQLValue($sort, MySQL::SQLVALUE_NUMBER);
                 $values["raRecStatus"] = MySQL::SQLValue('A', MySQL::SQLVALUE_TEXT);
                 $values["raDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["raDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["raDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["raUserCreatie"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);
                 $values["raUserUpdate"] = MySQL::SQLValue($pUser, MySQL::SQLVALUE_TEXT);

                 $db2->InsertRow("eba_ra_rubriek_artikels", $values);

                 $aantal++;


            }

            // -------------
            // Einde functie
            // -------------

            return $aantal;


         }

         // ===================================================================================================
         // Functie: Pakbon - Enkel stock te leveren?
         //
         // In:	Pakbon
         //
         //  Return: Enkel stock te leveren? true/false
         //
         // ===================================================================================================

         Static function ChkPakbonEnkelStockTeLeveren($pPakbon){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $enkelStock = true;

            $sqlStat = "Select count(*) as aantal from eba_od_order_detail where odOrdernummer = $pPakbon and odLeverStatus = '*KLAAR' and odLeverancier <> 2";
            $db->Query($sqlStat);

            $odRec = $db->Row();

            if ($odRec->aantal > 0)
                $enkelStock = false;


            // -------------
            // Einde functie
            // -------------

             return $enkelStock;

         }
         // ========================================================================================
         // Create/ Update file "kledijverdeling"
         //
         // In: Persoon
         // ========================================================================================

         static function CrtAlleRecordsKledijverdeling(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adRelatieMet like '%V%' and adFunctieVB > ' ' and adFunctieVB <> 'abonnee'";
             $sqlStat .= " and adFunctieVB <> 'afgev' and adFunctieVB <> 'scheids' and adFunctieVB <> 'kinesist' ";
             $db->Query($sqlStat);

             while ($adRec = $db->Row())
                 self::CrtRecKledijverdeling($adRec->adCode);

             // -------------
             // Einde functie
             // -------------

         }

         // ========================================================================================
         // Create/ Update file "kledijverdeling"
         //
         // In: Persoon
         // ========================================================================================

         static function CrtRecKledijverdeling($pPersoon){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             include_once(SX::GetClassPath("settings.class"));
             include_once(SX::GetClassPath("eba.class"));

             // ------------------------
             // Ophalen "huidig seizoen"
             // ------------------------

             $huidigSeizoen = SSP_settings::GetActiefSeizoen();

             // ------------------------------------------------
             // Ophalen bestaande record (indien reeds bestaand)
             // -----------------------------------------------

             $sqlStat = "Select * from eba_kv_kledijverdeling where kvPersoon = '$pPersoon' and kvSeizoen = '$huidigSeizoen'";
             $db->Query($sqlStat);

             $kvRec = $db->Row();

             // ---------------
             // Status lidkaart
             // ---------------

             $lidkaartStatus = 'Niet Aangemaakt';

             $sqlStat = "Select * from ela_ka_kaarten where kaPersoon = '$pPersoon' and kaSeizoen = '$huidigSeizoen' and kaType = '*LIDKAART_VB' and kaPrinted = 1";
             $db->Query($sqlStat);

             while($kaRec = $db->Row()) {

                 if (!$kaRec->kaOntvangen) {
                     $lidkaartStatus = 'Te Ontvangen';
                     $lidkaartCode = $kaRec->kaKaartCode;
                     break;
                 }

                 if ($kaRec->kaOntvangen) {
                     $lidkaartStatus = 'Reeds Ontvangen';
                     $lidkaartCode = $kaRec->kaKaartCode;
                 }

             }


             // -----------
             // Af te halen
             // -----------

             $afTeHalen= 'nada';

             if ($lidkaartStatus == 'Te Ontvangen')
                 $afTeHalen = "<li>Lidkaart</li>";

             $sqlStat = "Select distinct(ohOrdernummer) as pakbon from eba_oh_order_headers";
             $sqlStat .= " inner join eba_od_order_detail on odOrderNummer = ohOrderNummer and odLeverStatus = '*KLAAR'";
             $sqlStat .= " where ohKlant = '$pPersoon' and ohVolledigAfgewerkt <> 1 and ohKlaarVoorAfleveren = 1";

             $db->Query($sqlStat);

             while ($ohRec = $db->Row()){

                 $pakbon = $ohRec->pakbon;

                 $enkelStock = SSP_eba::ChkPakbonEnkelStockTeLeveren($pakbon);

                 if ($enkelStock)
                     $pakbon = "<b>$pakbon (ENKEL STOCK)</b>";
                 else
                     $pakbon = "<b>$pakbon (PAKKET)</b>";

                 if ($afTeHalen == 'nada')
                     $afTeHalen = '';

                 $afTeHalen .= "<li>Pakbon: $pakbon</li>";

             }

             if ($afTeHalen and $afTeHalen != 'nada')
                 $afTeHalen = "<ul>$afTeHalen</ul>";

             // ----------
             // UPDATE/ADD
             // ----------

             $curDateTime = date('Y-m-d H:i:s');

             $values = array();
             $where = array();

             $values["kvPersoon"] =  MySQL::SQLValue($pPersoon, MySQL::SQLVALUE_TEXT);
             $values["kvSeizoen"] =  MySQL::SQLValue($huidigSeizoen, MySQL::SQLVALUE_TEXT);
             $values["kvLidkaartCode"] =  MySQL::SQLValue($lidkaartCode, MySQL::SQLVALUE_TEXT);
             $values["kvLidkaartStatus"] =  MySQL::SQLValue($lidkaartStatus, MySQL::SQLVALUE_TEXT);

             if ($kvRec->kvLidkaartStatus == 'Ontvangen')
                 $values["kvLidkaartRedenNietAfgegeven"] =  null;

             $values["kvAfTeHalen"] =  MySQL::SQLValue($afTeHalen, MySQL::SQLVALUE_TEXT);

             if ($kvRec) {

                 $where["kvId"] = MySQL::SQLValue($kvRec->kvId, MySQL::SQLVALUE_NUMBER);

                 $db->UpdateRows("eba_kv_kledijverdeling", $values, $where);

             } else {

                 $values["kvDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["kvUserUpdate"] =  MySQL::SQLValue('*SYS', MySQL::SQLVALUE_TEXT);
                 $values["kvDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                 $id = $db->InsertRow("eba_kv_kledijverdeling", $values);
             }

             // -------------
             // Einde functie
             // -------------

             return true;

         }


         // -----------
         // Einde CLASS
         // -----------


 	}      
?>