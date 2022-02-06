<?php 
     class SSP_eta { // define the class
	 
		// ===================================================================================================
		// Functie: Get ploeg-record
		//
		// In:	- ploegID
		//
		// Uit:	- vpRec
		//
		// ===================================================================================================
         
        Static function db_vpRec($pPloegId) {
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$sqlStat = "Select * from ssp_vp where vpId = $pPloegId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return null;
			}
				
			if (! $vpRec = $db->Row()) {
				$db->close();			
				return null;
			}
			else {
				$db->close();			
				return $vpRec;				
			}
				 
		}
		
		// ===================================================================================================
		// Functie: Aanmaken taak vanuit "request"
		//
		// In:	- workflow
		//		- Naam
		//		- omschrijving
		//		- Request ID
		//		- User ID (Aanvrager) 
		//
		// Uit:	- TaakId
		//
		// ===================================================================================================
         
        Static function CrtTaakVanRequest($pWorkflow, $pNaam, $pOmschrijving, $pRequestId, $pUserId ) {
				 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			
			$dateTime =	date('Y-m-d H:i:s');
			$curDateTime =	date('Y-m-d H:i:s');	
				
			$sqlStat = "Select taId from eta_ta_taken where taRequest = $pRequestId";

			$db->Query($sqlStat);
			
			if (! $taRec = $db->Row()) {
		
				$values["taWorkflow"] = MySQL::SQLValue($pWorkflow);
				$values["taNaam"] = MySQL::SQLValue($pNaam);
				$values["taOmschrijving"] = MySQL::SQLValue($pOmschrijving);
				$values["taRequest"] = MySQL::SQLValue($pRequestId, MySQL::SQLVALUE_NUMBER);
				$values["taUserCreatie"] = MySQL::SQLValue($pUserId);				
				$values["taUserUpdate"] = MySQL::SQLValue($pUserId);
				$values["taDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
				$values["taDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
				
				$taakId = $db->InsertRow("eta_ta_taken", $values);
				
				// ------------
				// Taak-stappen
				// ------------
				
				$sqlStat  = "Select * from eta_ws_workflow_stappen where wsWorkflow = '$pWorkflow' order by wsSequentie, wsId ";
				$db2->Query($sqlStat);
				
				while ($wsRec = $db2->Row()){
					
					$values2["tsTaak"] = MySQL::SQLValue($taakId, MySQL::SQLVALUE_NUMBER);
					$values2["tsSequentie"] = MySQL::SQLValue($wsRec->wsSequentie, MySQL::SQLVALUE_NUMBER);
					$values2["tsNaam"] = MySQL::SQLValue($wsRec->wsNaam);
					$values2["tsGroep"] = MySQL::SQLValue($wsRec->wsGroep, MySQL::SQLVALUE_NUMBER);			
					
					$taakStapId = $db->InsertRow("eta_ts_taak_stappen", $values2);
					
				}

				
				
			
			} 
			
			Else {
				
				$taakId = $taRec->taId;
				
				$sqlStat = "Update eta_ta_taken set taOmschrijving = '$pOmschrijving', taDatumCreatie = now(), taUserUpdate= '$pUserId' where taRequest = $pRequestId";
				$db->Query($sqlStat);			
			
			}
				

			return $taakId;

		}
 			
		// ===================================================================================================
		// Functie: Aanmaken taak op basis "XW" (Aanvraag THUIS-wedstrijd)
		//
		// In:	- Request ID
		//
		// Uit:	- TaakId
		//
		// ===================================================================================================
         
        Static function CrtTaakVanWedstrijdRequest($pRequestId) {
				 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(SX::GetSxClassPath("tools.class"));
				
			$sqlStat = "Select * from eta_rx_requests where rxId = $pRequestId";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return null;
			}
				
			if (! $rxRec = $db->Row()) {
				$db->close();			
				return null;
			}

			// ------------
			// Get workflow 
			// ------------
			
			$workflow = $rxRec->rxWorkflow;
			
			$sqlStat = "Select * from eta_wh_workflow_headers where whWorkflow = '$workflow'";
						
			if (! $db->Query($sqlStat)) {
				$db->close();
				return null;
			}			
					
			if (! $whRec = $db->Row()) {
				$db->close();			
				return null;
			}

			// --------------------------
			// Aanmaken taak-omschrijving
			// --------------------------
			
			$datum = SX_tools::EdtDate($rxRec->rxDatum, '%A %d/%m/%Y');
			
			$vpRec = self::db_vpRec($rxRec->rxPloeg);
			
			$ploeg = "$vpRec->vpNaam ($vpRec->vpSeizoen)";
			$tegenstander = $rxRec->rxTegenstander;
			
			
			
			$format='%H:%M';
			$aanvang = strftime($format, strtotime($rxRec->rxAanvang));
			
			$omschrijving = "Ploeg: $ploeg\n";
			$omschrijving .= "Tegenstander: $tegenstander\n";		
			$omschrijving .= "Datum: $datum\n";		
			$omschrijving .= "Aanvang: $aanvang\n";	
			$omschrijving .= "\n" . $rxRec->rxExtraInfo;	

			$ploegNaam = $vpRec->vpNaam;
			
			$naam = "$ploegNaam - $tegenstander op $datum";
			
			
			$taakId = self::CrtTaakVanRequest($rxRec->rxWorkflow, $naam, $omschrijving, $pRequestId, $rxRec->rxUserCreatie);
			
		}
 			
		// ===================================================================================================
		// Functie: Zet taakstap-status op OK
		//
		// In:	- TaakstapId
		//		- UserId
		//
		// ===================================================================================================
         
        Static function SetTaakstapOK($pTaakstapId, $pUserId) {
				 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(SX::GetSxClassPath("tools.class"));
			
			$sqlStat = "Update eta_ts_taak_stappen set tsTaakstapStatus = '*OK', tsDatumDone = date(now()), tsUserDone = '$pUserId', tsDatumUpdate = now(), tsUserUpdate = '$pUserId' where tsId = $pTaakstapId";
			$db->Query($sqlStat);
					
		}
				
		// ========================================================================================
		// Function: Mag taak gewist worden?
		//
		// In:	Taak
		//
		// Return: true/false
		// ========================================================================================
		
		static function ChkTaakWissen($pTaakId) {   

			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			
			$magGewist = false;
			
			// ---------------------------------------------------------
			// Mag indien enkel niet verwerkte stappen (of geen stappen)
			// ---------------------------------------------------------
			
			$sqlStat = "Select count(*) as aantal from eta_ts_taak_stappen where tsTaak = $pTaakId and tsTaakstapStatus <> '*TODO'";
			$db->Query($sqlStat);
			
			$tsRec = $db->Row();
			
			if ($tsRec->aantal <= 0)
				$magGewist = true;
			
			// -------------
			// Einde functie
			// -------------
			
			return $magGewist;
		
		
		}
				
		// ========================================================================================
		// Function: Taak wissen
		//
		// In:	Taak
		//
		// ========================================================================================
		
		static function DltTaak($pTaakId) {   

			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			
			// -------------------
			// Wissen Taak-stappen
			// -------------------
			
			$sqlStat = "Delete From eta_ts_taak_stappen where tsTaak = $pTaakId";
			$db->Query($sqlStat);
			
			// -----------
			// Wissen Taak			
			// -----------
				
			$sqlStat = "Delete From eta_ta_taken where taId = $pTaakId";
			$db->Query($sqlStat);

		}
			
 	}      
?>