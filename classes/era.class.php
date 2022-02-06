<?php 
     class SSP_era { // define the class

         // ========================================================================================
         // Get adRec (Persoonlijke gegevens)
         //
         // In:	- Persoon
         //
         // Return: rhRec
         // ========================================================================================

         static function Get_adRec($pPersoon) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";

             if (!$db->Query($sqlStat))
                 return null;

             if (! $adRec = $db->Row())
                 return null;

             $db->close();
             return $adRec;

         }

         // =======================================================================================
         // Get taRec (table record)
         //
         // In:	- Table
         //     - Code
         //
         // Return: rhRec
         // ========================================================================================

         static function Get_taRec($pTable, $pCode) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from sx_ta_tables where taTable = '$pTable' and taCode = '$pCode'";

             if (!$db->Query($sqlStat))
                 return null;

             if (! $taRec = $db->Row())
                 return null;

             $db->close();
             return $taRec;

         }

         // =======================================================================================
         // Get twRec
         //
         // In:	- wedstrijd/training ID
         //
         // Return: twRec
         // ========================================================================================

         static function Get_twRec($pWedstrijdId) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_twbs_tw where twId = $pWedstrijdId";

             if (!$db->Query($sqlStat))
                 return null;

             if (! $twRec = $db->Row())
                 return null;

             $db->close();
             return $twRec;

         }
         // ========================================================================================
         // Get vpRec (voetbalploeg record)
         //
         // In:	- ploeg
         //
         // Return: rhRec
         // ========================================================================================

         static function Get_vpRec($pPloegId) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_vp where vpId = $pPloegId";

             if (!$db->Query($sqlStat))
                 return null;

             if (! $vpRec = $db->Row())
                 return null;

             $db->close();
             return $vpRec;

         }

         // ===================================================================================================
		// Functie: Ophalen "ERA Samenvattingen" alle spelers
		// ===================================================================================================
         
        Static function CrtSamenvattingen() {  
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			include_once(Sx::GetClassPath("settings.class"));
			
			$seizoen = SSP_settings::GetActiefSeizoen();
						
			$sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieVB like '%speler%'";
			
			$db->Query($sqlStat);
		
			set_time_limit(300);
			
			While ($adRec = $db->Row()) {
				
				$persoon = $adRec->adCode;
				
				self::CrtPersoonSamenvatting($persoon, $seizoen);
			}
			
		}
		
	  	// ===================================================================================================
		// Functie: Aanmaken "ERA Samenvatting" bepaalde persoon
		//
		// In:	- Persoon
		//		- Seizoen
		//
		// ===================================================================================================
         
        Static function CrtPersoonSamenvatting($pPersoon, $pSeizoen) {  
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			// -----------------
			// Wis huidig record
			// -----------------
			
			$sqlStat = "Delete from era_es_samenvatting where esPersoon = '$pPersoon' and esSeizoen = '$pSeizoen'";
			$db->Query($sqlStat);
			
			// -----
			// Ploeg
			// -----
			
			$ploegId = 0;
			
			$sqlStat = "Select * from ssp_vp_sp inner join ssp_vp on vpId = spPloeg where spPersoon = '$pPersoon' and spType = 'Speler' and vpSeizoen = '$pSeizoen' and (vpJeugdSeniors = 'Jeugd' or vpJeugdSeniors = 'Seniors') order by spPloeg desc";
			$db->Query($sqlStat);	
			
			if ($spRec = $db->Row())
				$ploegId = $spRec->spPloeg;
			
			if ($ploegId == 0) {
				
				$db->close();
				return;
			}
			
			// ---------------------
			// Get ERA aanwezigheden
			// ---------------------
			
			$wedstrijdAanwezig = 0;
			$wedstrijdAfwezig = 0;
			$esWedstrijdOnverwittigdAfwezig = 0;
			$wedstrijdAanwezigPerc = 0;
 				
			$trainingAanwezig = 0;
			$trainingAfwezig = 0;
			$esTrainingOnverwittigdAfwezig = 0;
			$trainingAanwezigPerc = 0;
			
			$tornooiAanwezig = 0;
			$tornooiAfwezig = 0;
			$esTornooiOnverwittigdAfwezig = 0;
			$tornooiAanwezigPerc = 0;
			
			$laatsteKeerAanwezig = 'null';
			
			$sqlStat = "Select * from ssp_twbs_aw inner join ssp_twbs_tw on twId = awTW  and twSeizoen = '$pSeizoen' where awSpeler = '$pPersoon' and awType = 'Speler' and awRedenAfwezig <> 'Afgelast'  and awRedenAfwezig <> 'Keeperstraining'";
			
			$db->Query($sqlStat);

			while ($awRec = $db->Row()) {
				
				$aanwezig = 0;
				$afwezig = 0;	
				$datum = $awRec->twDatum;
								
				if ($awRec->awAanwezig == 1) {
					$aanwezig = 1;
					$laatsteKeerAanwezig = "'$awRec->twDatum'";
				}
				
				if  ($awRec->awAanwezig == 0) {
					
					$afwezig = 1;	
					
					if ($awRec->awRedenAfwezig != 'Gekwetst' && $awRec->awRedenAfwezig != 'Ziek' && $awRec->awRedenAfwezig != 'Niet verwittigd' && $awRec->awRedenAfwezig != 'Geschorst') {
						if (self::ChkPersoonDatumAanwezig($pPersoon, $datum)) {
							$afwezig = 0;	
						}
					}		
										
				}
			
		

				if (trim($awRec->twType) == 'TRAINING') {
						$trainingAanwezig += $aanwezig;					
						$trainingAfwezig += $afwezig;	
						if($awRec->awRedenAfwezig == 'Niet verwittigd')
							$esTrainingOnverwittigdAfwezig += $afwezig;
					
				}


				if (trim($awRec->twType) == 'OEFEN' or trim($awRec->twType) == 'COMPETITIE') {
					$wedstrijdAanwezig += $aanwezig;
					$wedstrijdAfwezig += $afwezig;					
						if($awRec->awRedenAfwezig == 'Niet verwittigd')
							$esWedstrijdOnverwittigdAfwezig += $afwezig;					
				}		

				if (trim($awRec->twType) == 'TORNOOI') {
					$tornooiAanwezig += $aanwezig;
					$tornooiAfwezig += $afwezig;		
					if($awRec->awRedenAfwezig == 'Niet verwittigd')
						$esTornooiOnverwittigdAfwezig += $afwezig;						
					
				}	
				
			}
			

			// -------------------
			// Bereken percentages
			// -------------------
			
			$totaal = $trainingAanwezig + $trainingAfwezig;
			if ($totaal > 0)
				$trainingAanwezigPerc = ($trainingAanwezig  * 100 )/ $totaal;
			
			$totaal = $wedstrijdAanwezig + $wedstrijdAfwezig;
			if ($totaal > 0)
				$wedstrijdAanwezigPerc =  ($wedstrijdAanwezig  * 100 )/ $totaal;
			
			$totaal = $tornooiAanwezig + $tornooiAfwezig;
			if ($totaal > 0)
				$tornooiAanwezigPerc =  ($tornooiAanwezig  * 100 )/ $totaal;
			
			// -------------------
			// Creeer nieuw record
			// -------------------		
			
			$sqlStat 	= "Insert into era_es_samenvatting set "
						. "esPersoon = '$pPersoon', "
						. "esSeizoen = '$pSeizoen', "	
						. "esWedstrijdAanwezig = $wedstrijdAanwezig, "
						. "esWedstrijdAfwezig = $wedstrijdAfwezig, "
						. "esWedstrijdOnverwittigdAfwezig = $esWedstrijdOnverwittigdAfwezig, "
						. "esWedstrijdAanwezigPerc = $wedstrijdAanwezigPerc, "
						. "esTrainingAanwezig = $trainingAanwezig, "
						. "esTrainingAfwezig = $trainingAfwezig, "
						. "esTrainingOnverwittigdAfwezig = $esTrainingOnverwittigdAfwezig, "
						. "esTrainingAanwezigPerc = $trainingAanwezigPerc, "	
						. "esTornooiAanwezig = $tornooiAanwezig, "
						. "esTornooiAfwezig = $tornooiAfwezig, "
						. "esTornooiOnverwittigdAfwezig = $esTornooiOnverwittigdAfwezig, "
						. "esTornooiAanwezigPerc = $tornooiAanwezigPerc, "	
						. "esLaatsteKeerAanwezig = $laatsteKeerAanwezig, "
						. "esPloeg = $ploegId, "
						. "esLaatsteWijziging = now() ";
						
	    	$db->Query($sqlStat);
			
			$db->close();
			
		}		

	  	// ===================================================================================================
		// Functie: Persoon aanwezig op bepaalde datum? (true/false)
		//
		// In:	- Persoon
		//		- Datum
		//
		// ===================================================================================================
         
        Static function ChkPersoonDatumAanwezig($pPersoon, $pDatum) {  
		
			 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			
			$returnVal = false;
			
			$sqlStat = "Select * From ssp_twbs_aw inner join ssp_twbs_tw on twId = awTW where awSpeler = '$pPersoon' and awAanwezig = 1 and twDatum = '$pDatum'";
			$db->Query($sqlStat);	
			
			if ($awRec = $db->Row()) 
				$returnVal = true;
					
			$db->close();
			return $returnVal;
					
					
		}			
	
		// ========================================================================================
		// Function: Ophalen aanwezigheden (HTML)
		//
		// In:	- Code = Contact code (vb gverhelst)
		//
		// Return: Aanwezigheden in HTML
		// ========================================================================================
		   
		static function GetAanwezighedenHTML($pCode) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 
			include_once(SX::GetClassPath("settings.class"));
				
			$grey = SSP_settings::GetBackgroundColor('grey');
			$green = SSP_settings::GetBackgroundColor('green');
			$red = SSP_settings::GetBackgroundColor('red');		
			$blue = SSP_settings::GetBackgroundColor('blue');			
			$yellow = SSP_settings::GetBackgroundColor('yellow');		
			
			$HTML = "";
			
			$sqlStat = "Select * from era_es_samenvatting inner join ssp_vp on vpId = esPloeg where esPersoon = '$pCode' order by esSeizoen desc";

			
			if (! $db->Query($sqlStat)) {
				$db->close();
				return $HTML;
			}

			
			$HTML = "";
			$aantal = 0;
			
			while ($esRec = $db->Row()) {
				
				$aantal++;
				
				if ($aantal > 2)
					break;
				
				$styleTABLE = "font-family: Arial, Helvetica, Sans-Serif; font-size: 14px; padding: 5px; ";
				$StyleTH = "border: 1px solid blue; padding: 5px; background-color: $blue; text-align: center; ";
				$StyleTD = "border: 1px solid blue; padding: 5px; text-align: center;";
				
				
				if ($HTML > ' ')
					$HTML .= "<br/>";
				
				$HTML .= "<b>Seizoen: $esRec->esSeizoen </b><br/>";
				
				$HTML .= "<table style='$styleTABLE'>";
				
				$HTML .= "<tr><th style='$StyleTH'>Type</th><th style='$StyleTH'>Aanwezig</th><th style='$StyleTH'>Afwezig</th><th style='$StyleTH'>Onverwittigd</th><th style='$StyleTH'>Aanw. %</th></tr>";
				
				// ----------
				// Trainingen
				// ----------
				
				$styleOnverwittigdAfwezig = $StyleTD;
				if ($esRec->esTrainingOnverwittigdAfwezig > 0)
					$styleOnverwittigdAfwezig .= "background-color: $red;";
				
				$styleAanwezigPerc = $StyleTD;
				if ($esRec->esTrainingAanwezigPerc > 65)
					$styleAanwezigPerc .= "background-color: $green;";
				elseif ($esRec->esTrainingAanwezigPerc >= 50)
					$styleAanwezigPerc .= "background-color: $yellow;";
				else
					$styleAanwezigPerc .= "background-color: $red;";
					
				$HTML .= "<tr><td style='$StyleTD'>Trainingen</td><td style='$StyleTD'>$esRec->esTrainingAanwezig</td><td style='$StyleTD'>$esRec->esTrainingAfwezig</td><td style='$styleOnverwittigdAfwezig'>$esRec->esTrainingOnverwittigdAfwezig</td><td style='$styleAanwezigPerc'>$esRec->esTrainingAanwezigPerc</td></tr>";		
				
				// -----------
				// Wedstrijden
				// ------------
				
				$styleOnverwittigdAfwezig = $StyleTD;
				if ($esRec->esWedstrijdOnverwittigdAfwezig > 0)
					$styleOnverwittigdAfwezig .= "background-color: $red;";
				
				$styleAanwezigPerc = $StyleTD;
				if ($esRec->esWedstrijdAanwezigPerc > 65)
					$styleAanwezigPerc .= "background-color: $green;";
				elseif ($esRec->esWedstrijdAanwezigPerc >= 50)
					$styleAanwezigPerc .= "background-color: $yellow;";
				else
					$styleAanwezigPerc .= "background-color: $red;";
					
				$HTML .= "<tr><td style='$StyleTD'>Wedstrijden</td><td style='$StyleTD'>$esRec->esWedstrijdAanwezig</td><td style='$StyleTD'>$esRec->esWedstrijdAfwezig</td><td style='$styleOnverwittigdAfwezig'>$esRec->esWedstrijdOnverwittigdAfwezig</td><td style='$styleAanwezigPerc'>$esRec->esWedstrijdAanwezigPerc</td></tr>";		
				
				// --------
				// Tornooien
				// ---------
				
				$styleOnverwittigdAfwezig = $StyleTD;
				if ($esRec->esTornooiOnverwittigdAfwezig > 0)
					$styleOnverwittigdAfwezig .= "background-color: $red;";
				
				$styleAanwezigPerc = $StyleTD;

					
				$HTML .= "<tr><td style='$StyleTD'>Tornooien</td><td style='$StyleTD'>$esRec->esTornooiAanwezig</td><td style='$StyleTD'>$esRec->esTornooiAfwezig</td><td style='$styleOnverwittigdAfwezig'>$esRec->esTornooiOnverwittigdAfwezig</td><td style='$styleAanwezigPerc'>$esRec->esTornooiAanwezigPerc</td></tr>";				
				
				
				$HTML .= "</table>";
				
				$HTML .= "Ploeg: $esRec->vpNaam"; 
				$HTML .= "<br/>Update datum: $esRec->esLaatsteWijziging";
				
			}
		
					
			$db->close();
			Return $HTML;

		
		}		
	
		// ========================================================================================
		// Function: Check aanwezigheden 
		//
		// In:	- Code = Contact code (vb gverhelst)
		//
		// Return: true/false
		// ========================================================================================
		   
		static function CheckAanwezigheden($pCode) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 
			include_once(SX::GetClassPath("settings.class"));

			$aantal = 0;
			
			$sqlStat = "Select count(*) as aantal from era_es_samenvatting where esPersoon = '$pCode'";
			
			if (! $db->Query($sqlStat)) {
				$db->close();
				return false;
			}
	
			
			If (! $esRec = $db->Row()) {
				$db->close();
				return false;
			}
				
				
			$db->close;
			
			if ($esRec->aantal > 0)
				return true;
			else
				return false;
		}		
	
		// ========================================================================================
		// Function: Toevoegen alle spelers bepaalde categorie aan ploeg 
		//
		// In: 	ploeg
		//		categorie
		//		userId
		//
		// Return: Aantal toegevoegde spelers
		// ========================================================================================
		   
		static function AddPloegAlleSpelers($pPloeg, $pCat, $pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 
			
			$sqlStat = "Select * from ssp_ad where adRecStatus = 'A' and adFunctieVB like '%speler%' and adVoetbalCat = '$pCat'";
		
			if (! $db->Query($sqlStat)) {
				$db->close();
				return 0;
			}	

			$aantal = 0;
			while ($adRec = $db->Row()) {
				
				$persoon = $adRec->adCode;
				$toegevoegd = self::AddPloegPersoon($pPloeg, $persoon, 'Speler', $pUserId);
				
				if ($toegevoegd == true)
					$aantal++;
			
			}
			
			
			return $aantal;
			
		}
	
		// ========================================================================================
		// Function: Toevoegen "persoon" aan bepaalde ploeg
		//
		// In: 	ploeg
		//		persoon
		//		type (Trainer, Speler)
		//		UserId
		//
		// Return: Toegevoegd?
		// ========================================================================================
		   
		static function AddPloegPersoon($pPloeg, $pPersoon, $pType, $pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 
			
			// --------------------
			// mag nog niet bestaan
			// --------------------
			
			$sqlStat = "Select count(*) as aantal from ssp_vp_sp where spPloeg = $pPloeg and spPersoon = '$pPersoon'";
			
			if (! $db->Query($sqlStat)) {
				$db->close();

				return false;
			}	
		
			if (! $spRec = $db->Row())
				return false;
							
			if ($spRec->aantal >= 1)
				return false;		
			
			// ---------
			// toevoegen
			// ---------
			$sqlStat 	= "Insert into ssp_vp_sp set "
						. "spPloeg = $pPloeg, "
						. "spPersoon = '$pPersoon', "	
						. "spType = '$pType', "
						. "spLangAfwezig = 0, "
						. "spRedenAfwezig = '', "
						. "spUserCreatie = '$pUserId', "
						. "spDatumCreatie = now(), "
						. "spUserUpdate = '$pUserId', "
						. "spDatumUpdate = now() ";
						
			$return = $db->Query($sqlStat);	
			
			return $return;
		}
		
	
		// ========================================================================================
		// Function: Ophalen ERA Authority 
		//
		// In: 	UserId
		//
		// Return: Array ploegen
		// ========================================================================================
		   
		static function GetEraAuthPloegen($pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			
			include_once(SX::GetClassPath("settings.class"));
			include_once(SX::GetSxClassPath("auth.class"));

			$actiefSeizoen = SSP_settings::GetActiefSeizoen();
			
			$ploegen = array();
			
			// -----------------------
			// Authority ALLE ploegen?
			// -----------------------
			
			$allePloegen = false;	
			
			$sqlStat = "Select * from sx_au_authority where auApCode = 'era' and auLevel = '*FULL'";
			
			if (! $db->Query($sqlStat)) {
				$db->close();
				return $ploegen;
			}
			
			while ($auRec = $db->Row()) {
				
				
				if ($auRec->auUserId > ' ' && $auRec->auUserId == $pUserId)
					$allePloegen = true;
				
				if ($auRec->auRole > ' ') {
					
					$checkUserRole = SX_auth::CheckUserRole($pUserId, $auRec->auRole);
					
					if ($checkUserRole == true)
						$allePloegen = true;
						
					
				}
				
				
				if ($allePloegen == true)
					break;
				
			}
			
			
			if ($allePloegen == true) {
					
				$sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and vpOpvSpelersAanw = 1 order by vpSort";
				
				if (! $db->Query($sqlStat)) {
					$db->close();
					return $ploegen;
				}	

				while ($vpRec = $db->Row()) {
					
					
					$ploegen[] = $vpRec->vpId;
					
					
					
				}
			
				
				return $ploegen;
				
				
			}
			
			// -------------------------------------------------------
			// Authority SPECIFIEKE ploegen? (op basis authority file)
			// -------------------------------------------------------
			
			$sqlStat = "Select * from sx_au_authority where auApCode = 'era' and auLevel = '*PLOEG' and auTeamId <> 0";
		
			if (! $db->Query($sqlStat)) {
				$db->close();
				return $ploegen;
			}
			
			while ($auRec = $db->Row()) {
				
				if ($auRec->auUserId > ' ' && $auRec->auUserId == $pUserId) {
					$ploegen[] = $auRec->auTeamId;
					continue;
				}
				
				if ($auRec->auRole > ' ') {
					
					$checkUserRole = SX_auth::CheckUserRole($pUserId, $auRec->auRole);
					
					if ($checkUserRole == true)
						$ploegen[] = $auRec->auTeamId;
						
					
				}
				
			}
			
			// -----------------------------------------------------
			// Authority SPECIFIEKE ploegen? (op basis ploegen file)
			// -----------------------------------------------------
			
			$sqlStat = "Select * from sx_au_authority where auApCode = 'era' and auLevel = '*PLOEG' and (auTeamId is NULL or auTeamId = 0)";
			
			if (! $db->Query($sqlStat)) {
				$db->close();
				return $ploegen;
			}
			
			$specifiekePloeg = false;	

			
			while ($auRec = $db->Row()) {
				
				
				if ($auRec->auUserId > ' ' && $auRec->auUserId == $pUserId) {
					$specifiekePloeg = true;
					break;
				}
				
				if ($auRec->auRole > ' ') {
					
					$checkUserRole = SX_auth::CheckUserRole($pUserId, $auRec->auRole);
					
					if ($checkUserRole == true) {
						$specifiekePloeg = true;
						break;
					}
						
					
				}
				
	
			}
			
			if ($specifiekePloeg == true) {	
			
			
				$sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and vpOpvSpelersAanw = 1 and (vpTrainer = '$pUserId' or vpTrainer2 = '$pUserId' or vpTrainer3 = '$pUserId' or vpTrainer4 = '$pUserId' or vpTrainer5 = '$pUserId' or vpEraBeheerder1 = '$pUserId' or vpEraBeheerder2 = '$pUserId' or vpEraBeheerder3 = '$pUserId' or vpEraBeheerder4 = '$pUserId')";
			
				if (! $db->Query($sqlStat)) {
					$db->close();
					return $ploegen;
				}		


				while ($vpRec = $db->Row()) {
					
					$ploegen[] = $vpRec->vpId;
				
					// Ook andere ploegen zelfde categorie...
					$voetbalCat = $vpRec->vpVoetbalCat;
					$vpId = $vpRec->vpId;
					
					$sqlStat2 = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and vpVoetbalCat = '$voetbalCat' and vpOpvSpelersAanw = 1 and vpId <> $vpId and (vpVoetbalCat<> 'JEUGD' or vpOpvSpelersAanw =  1)  and vpVoetbalCat <> 'EXTERN' ";
					
					if ($db2->Query($sqlStat2)) {
					
						while ($vpRec2 = $db2->Row())
							$ploegen[] = $vpRec2->vpId;
							
					}

				}
			}
			
			// -------------
			// Einde functie
			// -------------
			
			$db->close();
            $db2->close();
			return $ploegen;
			
			
		}
	
		// ========================================================================================
		// Function: Ophalen ERA Default Ploeg
		//
		// In: 	UserId
		//
		// Return: PloegId (0 = geen default ploeg) 
		// ========================================================================================
		   
		static function GetEraDftPloeg($pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...	
			include_once(SX::GetClassPath("settings.class"));
			
			$ploegId = 0;
			$actiefSeizoen = SSP_settings::GetActiefSeizoen();
			
			$sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and  vpTrainer = '$pUserId'";
			
			if ($db->Query($sqlStat)) {
				
				if ($vpRec = $db->Row());
					$ploegId = $vpRec->vpId;
		
			}
			
			if ($ploegId == 0){
				
				$sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and (vpTrainer2 = '$pUserId' or vpTrainer3 = '$pUserId'  or vpTrainer4 = '$pUserId'  or vpTrainer5 = '$pUserId')";
				
				
				if ($db->Query($sqlStat)) {
					
					if ($vpRec = $db->Row());
						$ploegId = $vpRec->vpId;
			
				}				
			
				
			}

			
			$db->close();
			return $ploegId;


		}
	
		// ========================================================================================
		// Function: Ophalen ERA Default Ploeg
		//
		// In: 	UserId
		//
		// Return: PloegId (0 = geen default ploeg) 
		// ========================================================================================
		   
		static function GetEigenCatPloegen($pUserId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...	
			include_once(SX::GetClassPath("settings.class"));
			
			$ploegId = 0;
			$actiefSeizoen = SSP_settings::GetActiefSeizoen();
			
						
			$cats = array();
			$ploegen = array();			
			
			$sqlStat = "Select distinct(vpVoetbalCat) as cat from ssp_vp where vpSeizoen = '$actiefSeizoen' and vpVoetbalCat <> 'EXTERN' and (vpVoetbalCat <> 'JEUGD' or vpOpvSpelersAanw = 1) and (vpTrainer = '$pUserId' or vpTrainer2 = '$pUserId' or vpTrainer3 = '$pUserId'  or vpTrainer4 = '$pUserId' or vpTrainer5 = '$pUserId' or vpEraBeheerder1 = '$pUserId' or vpEraBeheerder2 = '$pUserId' or vpEraBeheerder3 = '$pUserId' or vpEraBeheerder4 = '$pUserId')";

			
			if ($db->Query($sqlStat)) {
				
				while ($vpRec = $db->Row())
					$cats[] = $vpRec->cat;

		
			}
			
			foreach($cats as $cat){

				$sqlStat = "Select * from ssp_vp where  vpSeizoen = '$actiefSeizoen' and vpVoetbalCat = '$cat'";
				
				if ($db->Query($sqlStat)) {
						
						while ($vpRec = $db->Row())
							$ploegen[] = $vpRec->vpId;
				
				}				
				
			

			}

	
			$db->close();
			return $ploegen;


		}	

	
		// ========================================================================================
		// Function: Fill Aantal Trainers in ssp_twbs_tw	
		//
		// In: 	twId
		//
		// Return: Aantal trainers
		// ========================================================================================
		   
		static function FillTwAantalTrainers($pTWId) { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...	
			
			$sqlStat = "Select count(*) as aantalTrainers From ssp_twbs_aw where awTW = $pTWId and awType = 'Trainer' and awHulpTrainer <> 1 and awAanwezig = 1";
			
			$aantalTrainers = 0;
			
			$db->Query($sqlStat);
			
			if ($awRec = $db->Row()) {
				
				if ($awRec->aantalTrainers <> null)	
					$aantalTrainers = $awRec->aantalTrainers;
			}
			
			$sqlStat = "Update ssp_twbs_tw set twAantalTrainers = $aantalTrainers where twId = $pTWId";
			
			$db->Query($sqlStat);
		
			// ------------
			// Eind functie
			// ------------
		
			$db->close();
			return;
		
		}

		// ========================================================================================
		// Function: Fill Aantal Trainers (voor alle trainingen & wedstrijden in ERA)	
		//
		// In: 	none
		//
		// Return: none
		// ========================================================================================
		   
		static function FillAantalTrainersAlleTw() { 	
		
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...	
			
			$sqlStat = "Select * from ssp_twbs_tw";

			$db->Query($sqlStat);

			while ($twRec = $db->Row()) 
				self::FillTwAantalTrainers($twRec->twId);

			$db->close();
			return;
		
		}

		// ========================================================================================
         // Function: Zet op "afgelast"
         //
         // In: 	Wedstrijd-ID
         //
         // ========================================================================================

         static function SetAfgelast($pWedstrijdId){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Update ssp_twbs_aw set awAanwezig = 0, awRedenAfwezig = 'Afgelast', awStartPos1 = 0, awStartPos2 = 0, awStartPos3 = 0, awStartPos4 = 0, awSpeelPerc = 0 where awTW = $pWedstrijdId";

             $db->Query($sqlStat);

             $sqlStat = "Update ssp_twbs_tw set twVerslagStatus = '*NVT' where twId = $pWedstrijdId";
             $db->Query($sqlStat);
         }

         // ========================================================================================
         // Function: Get wedstrijdverslag HTML (input-fields)
         //
         // In: 	Wedstrijd-ID
         //         Wedstrijddeel (1,2,3,4)
         //         Positie
         //
         // Return: Speler (*NONE indien geen speler gevonden)
         // ========================================================================================

         static function GetPosSpeler($pWedstrijdId, $pWedstrijdDeel, $pPositie) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $speler = '*NONE';

             $sqlStat  = "Select * from ssp_twbs_aw where awTW = $pWedstrijdId and awStartPos$pWedstrijdDeel =  $pPositie";

             $db->Query($sqlStat);

             if ($awRec = $db->Row())
                 $speler = $awRec->awSpeler;

             if ($speler == '*NONE'){

                 $sqlStat = "Select * from ssp_twbs_pn_positie_niemand where pnTW = $pWedstrijdId and pnWedstrijddeel = $pWedstrijdDeel and pnPositie = $pPositie";

                 $db->Query($sqlStat);

                 if ($pnRec = $db->Row())
                     $speler = '*NIEMAND';

             }

             return $speler;

         }

         // ========================================================================================
         // Function: Ophalen verslag type (*NVT, *BASIS, *VOLLEDIG)
         //
         // In: 	Wedstrijd-ID
         //
         // Return: Verslag Type
         // ========================================================================================

         static function GetVerslagType($pWedstrijdId){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // --------------
             // Wedstrijd-info
             // --------------

             $sqlStat = "Select * from ssp_twbs_tw where twId = $pWedstrijdId";

             $db->Query($sqlStat);

             if ( ! $twRec = $db->Row() )
                 return '*NVT';

             // --------------------
             // Verslagtype op ploeg
             // --------------------

             $ploeg = $twRec->twPloeg;

             $vpRec = self::Get_vpRec($ploeg);

             if ($vpRec and ($vpRec->vpWedstrijdvorm == 'NVT'))
                 return '*NVT';

             // -------------------------
             // Verslagtype op basis type
             // -------------------------

             $type = $twRec->twType;

             $taRec = self::Get_taRec('TWBS_TYPE', $type);

             if (! $taRec)
                return '*NVT';

             $arr_verslaginfo = json_decode($taRec->taAlfaData,true);

             $verslagType = $arr_verslaginfo['verslag'];

             if ($verslagType == '*WEDSTRIJDVORM') {

                 $taRec = self::Get_taRec('ERA_WEDSTRIJDVORM', $twRec->twWedstrijdvorm);

                 if (! $taRec)
                     $verslagType = '*NVT';
                 else {
                     $arr_verslaginfo = json_decode($taRec->taAlfaData,true);
                     $verslagType = $arr_verslaginfo['verslag'];
                 }
             }

             // -------------
             // Einde functie
             // -------------

             return $verslagType;

         }

         // ========================================================================================
         // Function: Get wedstrijdverslag veld afbeelding
         //
         // In: 	Wedstrijd-ID
         //
         // Return: Path naar afbeelding (*ERROR indien niet gevonden)
         // ========================================================================================

         static function GetWedstrijdverslagAfbeelding($pWedstrijdId) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // --------------
             // Wedstrijd-info
             // --------------

             $sqlStat = "Select * from ssp_twbs_tw where twId = $pWedstrijdId";

             $db->Query($sqlStat);

             if ((! $twRec = $db->Row()) or (! $twRec->twWedstrijdvorm))
                 return '*ERROR';

             // -------------
             // wedstrijdvorm
             // --------------

             $taRec = self::Get_taRec('ERA_WEDSTRIJDVORM',$twRec->twWedstrijdvorm );

             if (! $taRec or (! $taRec->taAlfaData ))
                 return '*ERROR';

             $arr_wedstrijdvorm = json_decode($taRec->taAlfaData,true);

             $picture = $arr_wedstrijdvorm['picture'];

             if (! $picture)
                 return '*ERROR';

             $path = SX::GetSiteImgPath($picture);

             if (! $path)
                 return '*ERROR';
             else
                 return $path;

         }

         // ========================================================================================
         // Function: Get wedstrijdverslag HTML (input-fields)
         //
         // In: 	Wedstrijd-ID
         //         Wedstrijddeel (1,2,3,4)
         //
         // Return: HTML-code
         // ========================================================================================

         static function GetWedstrijdverslagHTML($pWedstrijdId, $pWedstrijdDeel=1) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $html = '';

             // -------------------------------
             // Enkel indien "VOLLEDIG" verslag
             // -------------------------------

             $verslagType = self::GetVerslagType($pWedstrijdId);

             if ($verslagType <> '*VOLLEDIG')
                 return "";

             // --------------
             // Wedstrijd-info
             // --------------

             $sqlStat = "Select * from ssp_twbs_tw where twId = $pWedstrijdId";

             $db->Query($sqlStat);

             if ((! $twRec = $db->Row()) or (! $twRec->twWedstrijdvorm))
                return 'ERROR: WEDSTRIJD NIET GEKEND';

             // --------
             // Posities
             // --------

             $taRec = self::Get_taRec('ERA_WEDSTRIJDVORM',$twRec->twWedstrijdvorm );

             if (! $taRec or (! $taRec->taAlfaData ))
                 return 'ERROR: WEDSTRIJDVORM NIET GEKEND';

              $arr_posities = json_decode($taRec->taAlfaData,true);

             // -------
             // Spelers
             // -------

             $arr_spelers = [];

             $sqlStat = "Select * from ssp_twbs_aw where awTW = $pWedstrijdId and awType = 'Speler'and awAanwezig = 1 order by awSpeler";

             $db->Query($sqlStat);

             while ($awRec = $db->Row())
                 $arr_spelers[] = $awRec->awSpeler;

             // -------------------------------
             // Create input-fields (listboxes)
             // -------------------------------

             foreach ($arr_posities['posities'] as $posities){

                 $arr_posities_line = explode("&", $posities);

                 $htmlString = "<div class='form-group form-inline'>";

                 foreach ($arr_posities_line as $positie) {

                     $selSpeler = self::GetPosSpeler($pWedstrijdId, $pWedstrijdDeel, $positie);

                     $options = "<option value='''>A.u.b. selecteren</option>";

                     foreach ($arr_spelers as $speler){

                         $spelerNaam = $speler;

                         $adRec = self::Get_adRec($speler);
                         if ($adRec) {
                             $spelerNaam = utf8_encode($adRec->adVoornaamNaam);
                         }

                         if ($speler == $selSpeler)
                            $option = "<option value='$speler' selected>$spelerNaam</option>";
                         else
                             $option = "<option value='$speler'>$spelerNaam</option>";

                         $options .= $option;

                     }


                     if ($selSpeler == '*NIEMAND')
                        $options .= "<option value='*NIEMAND' selected>*NIEMAND</option>";
                     else
                         $options .= "<option value='*NIEMAND'>*NIEMAND</option>";

                     $id = "POS_" . $positie . "_DEEL_" . $pWedstrijdDeel;

                     $label
                         = "<label><div style='width:30px; text-align: center'>$positie</div></label>";

                     $select
                         = "<select id='$id'  data-positie='$positie' data-deel='$pWedstrijdDeel' class='form-control customField spelerPositie' name='$id'>"
                         . $options
                         . "</select>";


                     $htmlString .= $label . $select;

                 }

                 $htmlString .= "</div>";

                 $html .= $htmlString;

             }

             // ------------
             // copy buttons
             // ------------

             $copyButtons = "";

             if ($pWedstrijdDeel > 1) {

                 for ($deelVan = 1; $deelVan < $pWedstrijdDeel; $deelVan++) {

                     $id = "COPYDEEL_" . $deelVan . "_" . $pWedstrijdDeel;

                     $copyButton = "<button style='display:inline-block; margin-left: 5px' type='button' data-deel='$pWedstrijdDeel' data-deelvan='$deelVan' class='btn btn-success copyFrom'>Copieer van deel $deelVan</button>";

                     $copyButtons = $copyButtons . $copyButton;

                 }
             }

             if ($copyButtons > " ")
                 $copyButtons = "<div style='padding-bottom: 5px'>$copyButtons</div>";

             $html = "<div style='text-align: center; width: 900px; padding: 5px'>$copyButtons $html</div>";



             $db->close();
             return $html;

         }

         // ========================================================================================
         // Function: Handle VERSLAG input
         //
         // In: 	Wedstrijd-ID
         //         (various input fields)
         //         Enkel controle?
         //
         //
         // Out:    (eerste) wedstrijddeel met een fout
         //
         // Return: (Fout)Boodschap (of "*OK")
         // ========================================================================================

         static function HdlVerslagInput($pWedstrijdId, &$pWedstrijddeel, $pEnkelControle = false, $pVerslag = null ){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             // -------------------------
             // init uitgaande parameters
             // -------------------------

             $pWedstrijddeel = 0;

             // -------------------------------------
             // Registreer verslag-text als opgegeven
             // -------------------------------------

             if ($pVerslag) {

                 $values["twVerslag"] = MySQL::SQLValue($pVerslag);
                 $where["twId"] = MySQL::SQLValue($pWedstrijdId, MySQL::SQLVALUE_NUMBER);

                 $db->UpdateRows("ssp_twbs_tw", $values, $where);

             }

             // --------------
             // Wedstrijd-info
             // --------------

             $sqlStat = "Select * from ssp_twbs_tw where twId = $pWedstrijdId";

             $db->Query($sqlStat);

             if ((! $twRec = $db->Row()) or (! $twRec->twWedstrijdvorm))
                 return 'ERROR: WEDSTRIJD NIET GEKEND';

             // --------
             // Posities
             // --------

             $taRec = self::Get_taRec('ERA_WEDSTRIJDVORM',$twRec->twWedstrijdvorm );

             if (! $taRec or (! $taRec->taAlfaData ))
                 return 'ERROR: WEDSTRIJDVORM NIET GEKEND';

             $verslagType = self::GetVerslagType($pWedstrijdId);

             if ($verslagType != '*VOLLEDIG') {

                 if (! $twRec->twVerslag ) {

                     $boodschap = "Verslag niet ingegeven";
                     $sqlStat = "Update ssp_twbs_tw set twVerslagstatus = '*GEEN' where twid = $pWedstrijdId";
                     $db->Query($sqlStat);
                     return $boodschap;
                 }

                 $boodschap = "*OK";
                 $sqlStat = "Update ssp_twbs_tw set twVerslagstatus = '*OK' where twid = $pWedstrijdId";
                 $db->Query($sqlStat);
                 return $boodschap;

             }

             $arr_posities = json_decode($taRec->taAlfaData,true);

             // -------------------------------
             // Handle input-fields (listboxes)
             // -------------------------------

             if (! $pEnkelControle) {

                 $sqlStat = "Update ssp_twbs_aw set awStartPos1 = 0, awStartPos2 = 0, awStartPos3 = 0, awStartPos4 = 0  where awTW = $pWedstrijdId";
                 $db->Query($sqlStat);

                 $sqlStat = "Delete From ssp_twbs_pn_positie_niemand where pnTW = $pWedstrijdId";
                 $db->Query($sqlStat);

                 foreach ($arr_posities['wedstrijddelen'] as $wedstrijddeel) {

                     $id = "COPYDEEL1_" . $wedstrijddeel;
                     $copyDeel1 = $_REQUEST["$id"];

                     //if ($copyDeel1 == 1) {

                         //self::CpyVerslagInfo($pWedstrijdId,$wedstrijddeel,1);
                         //continue;
                     //}

                     foreach ($arr_posities['posities'] as $posities) {

                         $arr_posities_line = explode("&", $posities);

                         foreach ($arr_posities_line as $positie) {

                             $id = "POS_" . $positie . "_DEEL_" . $wedstrijddeel;

                             $speler = $_REQUEST["$id"];

                             if ($speler && ($speler != '*NIEMAND')) {
                                 $sqlStat = "Update ssp_twbs_aw set awStartPos$wedstrijddeel = $positie where awTW = $pWedstrijdId and awSpeler = '$speler'";
                                 $db->Query($sqlStat);
                             }

                             if ($speler == '*NIEMAND') {
                                 $sqlStat = "Insert Into ssp_twbs_pn_positie_niemand VALUES($pWedstrijdId, $wedstrijddeel, $positie)";
                                 $db->Query($sqlStat);
                             }


                         }
                     }

                 }

             }

             // ----------------
             // Speel-percentage
             // ----------------

             $sqlStat = "Select * from ssp_twbs_aw where awTW = $pWedstrijdId";
             $db->Query($sqlStat);

             while($awRec = $db->Row()){

                 $speler = $awRec->awSpeler;
                 $speelPerc = 0;

                 if ($awRec->awStartPos1)
                     $speelPerc += 25;
                 if ($awRec->awStartPos2)
                     $speelPerc += 25;
                 if ($awRec->awStartPos3)
                     $speelPerc += 25;
                 if ($awRec->awStartPos4)
                     $speelPerc += 25;

                 $sqlStat = "Update ssp_twbs_aw set awSpeelPerc = $speelPerc where awTW = $pWedstrijdId and awSpeler = '$speler'";
                 $db2->Query($sqlStat);

             }


             // --------
             // Controle
             // --------

             $boodschap = '*OK';
             $arr_ontbrekend = [];

             foreach ($arr_posities['wedstrijddelen'] as $wedstrijddeel) {

                 $arr_ontbrekend[$wedstrijddeel] = false;

                 foreach ($arr_posities['posities'] as $posities) {

                     $arr_posities_line = explode("&", $posities);

                     foreach ($arr_posities_line as $positie) {

                        $speler = self::GetPosSpeler($pWedstrijdId, $wedstrijddeel, $positie);

                        if (!$speler or ($speler == '*NONE')){

                            $arr_ontbrekend[$wedstrijddeel] = true;

                        }

                     }

                 }

             }

             // -------------
             // Einde functie
             // -------------

             $boodschap = "";

             foreach ($arr_posities['wedstrijddelen'] as $wedstrijddeel) {

                 if ($arr_ontbrekend[$wedstrijddeel]) {

                     if ($pWedstrijddeel == 0)
                         $pWedstrijddeel = $wedstrijddeel;

                     if (!$boodschap)
                         $boodschap = "Ontbrekende speler(s) in wedstrijddeel $wedstrijddeel";
                     else
                         $boodschap .= ", $wedstrijddeel";
                 }
             }

             if (! $boodschap) {

                 $boodschap = "*OK";
                 $sqlStat = "Update ssp_twbs_tw set twVerslagstatus = '*OK' where twid = $pWedstrijdId";
                 $db->Query($sqlStat);

             } else{
                 $sqlStat = "Update ssp_twbs_tw set twVerslagstatus = '*DEEL' where twid = $pWedstrijdId";
                 $db->Query($sqlStat);

             }

             // --------------------------
             // Test of "niets" ingebracht
             // --------------------------

             if ($boodschap != '*OK') {

                 if (! $twRec->twVerslag) {
                     $sqlStat = "Update ssp_twbs_tw set twVerslagstatus = '*GEEN' where twid = $pWedstrijdId";
                     $db->Query($sqlStat);
                 }
             }


             return $boodschap;

         }
         // ========================================================================================
         // Function: Ophalen "geselecteerden" (in eeen string)
         //
         // In: 	Wedstrijd-ID
         // ========================================================================================

         static function GetWedstrijdSelectieString($pWedstrijdId ) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_twbs_aw inner join ssp_ad on adCode = awSpeler where awTW = $pWedstrijdId and awType = 'speler' and awAanwezig = 1 order by adVoornaamNaam";
             $db->Query($sqlStat);

             $selectie = null;
             $aantal = 0;

             while ($awRec = $db->Row()){

                 $aantal++;

                 $naam = $awRec->adVoornaamNaam;

                 if (! $selectie)
                     $selectie = $naam;
                 else
                     $selectie .= ", $naam";

             }

             if ($aantal > 0)
                 $selectie = "<b>$aantal spelers geslecteerd:</b> $selectie";
             else
                 $selectie = "GEEN SPELERS GESELCTEERD...";

             // -------------
             // Einde functie
             // -------------

             return utf8_encode($selectie);

         }
         // ========================================================================================
         // Function: Ophalen "geselecteerden" (in een HTML list)
         //
         // In: 	Wedstrijd-ID
         // ========================================================================================

         static function GetWedstrijdSelectieHTML($pWedstrijdId ) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_twbs_aw inner join ssp_ad on adCode = awSpeler where awTW = $pWedstrijdId and awType = 'speler' and awAanwezig = 1 order by adVoornaamNaam";
             $db->Query($sqlStat);

             $html = "<b>Volledige selectie</b>:<ul>";

             while ($awRec = $db->Row()){

                 $naam = $awRec->adVoornaamNaam;
                 $html .= "<li>$naam</li>";
             }

             $html .= "</ul>";

             // -------------
             // Einde functie
             // -------------

             // return utf8_encode($html);
             return $html;

         }

         // ========================================================================================
         // Function: Ophalen mail template
         //
         // In: 	Geen
         // ========================================================================================

         static function GetDftMailTemplate(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat  = "Select * from evim_im_info_mail where imTemplateGroep = '*WEDSTRIJD' and imRecStatus = 'A'";
             $db->Query($sqlStat);

             if ($imRec = $db->Row())
                 return $imRec->imId;
             else
                 return null;

             // -------------
             // Einde functie
             // -------------

         }

         // ========================================================================================
         // Function: Opvullen MAIL-related velden
         //
         // In: 	Wedstrijd-ID
         // ========================================================================================

         static function FillWedstrijdMailVelden($pWedstrijd) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));
             include_once(SX::GetSxClassPath("tools.class"));

             $twRec = SSP_db::Get_ERA_twRec($pWedstrijd);

             if (!$twRec)
                 return;

             if (! $twRec->twTegenstander)
                 return;

             $naam = $twRec->twNaam;
             $tegenstander = $twRec->twTegenstander;

             $datumRaw = $twRec->twDatum;
             $datum = SX_tools::EdtDate($datumRaw);

             $datumKort = SX_tools::EdtDate($datumRaw, "%a %d/%m" );

             $aanvang = "";

             if ($twRec->twWedstrijdId){

                 $vwRec = SSP_db::Get_SSP_vwRec($twRec->twWedstrijdId);

                 if ($vwRec->vwTijd) {
                     $aanvang = $vwRec->vwTijd;
                     $aanvang = substr($aanvang,0,5);
                 }

                 if ($vwRec->vwTegenstander)
                     $tegenstander = $vwRec->vwTegenstander;

             }

             $ploeg = "Schelle";

             $vpRec = SSP_db::Get_SSP_vpRec($twRec->twPloeg);

             if ($vpRec) {
                 $ploeg = $vpRec->vpNaam;
             }

             $twMailTemplate = self::GetDftMailTemplate();

             if ($twRec->twUitThuis == "Thuis")
                $wedstrijd = "$ploeg - $tegenstander";
            else
                $wedstrijd = "$tegenstander - $ploeg";

             $twMailOnderwerp = "Schelle Sport - Deelname wedstrijd: $datumKort ($wedstrijd) ";

             $twMailWedstrijdInfo = "Wedstrijd: $wedstrijd \nDatum: $datum \nAanvang: $aanvang";

             if (! $twRec->twMailExtraInfo)
                 $twMailExtraInfo = "Indien je niet kan deelnemen, gelieve zo snel mogelijk te verwittigen!";
             else
                 $twMailExtraInfo =  $twRec->twMailExtraInfo;


             // ------
             // Update
             // ------

             $values = array();
             $where = array();

             $values["twMailTemplate"] =  MySQL::SQLValue($twMailTemplate, MySQL::SQLVALUE_NUMBER);
             $values["twMailOnderwerp"] =  MySQL::SQLValue($twMailOnderwerp);
             $values["twMailWedstrijdInfo"] =  MySQL::SQLValue($twMailWedstrijdInfo);
             $values["twMailExtraInfo"] =  MySQL::SQLValue($twMailExtraInfo);
             $values["twMailNaar"] =  MySQL::SQLValue('*NIEUW');

             $where["twId"] =  MySQL::SQLValue($pWedstrijd, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("ssp_twbs_tw", $values, $where);


             // -------------
             // Einde functie
             // -------------

         }
         // ========================================================================================
         // Function: Ophalen wedstrijd mail preview
         //
         // In: 	Wedstrijd
         // ========================================================================================

         static function GetMailTemplateHTML($pWedstrijd) {

             include_once(SX::GetClassPath("_db.class"));
             include_once(Sx::GetClassPath("evim.class"));

             $twRec = SSP_db::Get_ERA_twRec($pWedstrijd);

             if (! $twRec)
                 return "ONVERWACHTE FOUT";

             if (! $twRec->twMailTemplate)
                 return "GEEN TEMPLATE";

             $html = SSP_evim::GetTemplatePreview($twRec->twMailTemplate);

             // -------------
             // Einde functie
             // -------------

             return $html;

         }

         // ========================================================================================
         // Function: Versturen wedstrijd-selectie
         //
         // In: 	Wedstrijd
         //         Zender
         //
         // Return: Aantal mails verstuurd
         // ========================================================================================

         static function MailWedstrijdSelectie($pWedstrijd, $pZender) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
             include_once(SX::GetClassPath("_db.class"));
             include_once(SX::GetClassPath("personen.class"));
             include_once(SX::GetClassPath("evim.class"));

             $twRec = SSP_db::Get_ERA_twRec($pWedstrijd);


             if (!$twRec)
                 return 0;

             $template = $twRec->twMailTemplate;
             $onderwerp = $twRec->twMailOnderwerp;
             $mailNaar = $twRec->twMailNaar;

             $imRec = SSP_db::Get_EVIM_imRec($template);

             if (! $imRec)
                 return 0;

             $adRec = SSP_db::Get_SSP_adRec($pZender);

             if (!$adRec)
                 return 0;

             if (! $adRec->adMail)
                 return 0;


             $mailZender = $adRec->adMail;
             $naamZender = $adRec->adVoornaamNaam;

             // -----------------
             // Create MAILHEADER
             // -----------------

             if ($twRec->twMailHeaderId)

                 $mailHeader = $twRec->twMailHeaderId;

             else {

                 $curDateTime = date('Y-m-d H:i:s');

                 $values = array();

                 $values["vhMail"] = MySQL::SQLValue($template, MySQL::SQLVALUE_NUMBER);
                 $values["vhMailType"] = MySQL::SQLValue('*WEDSTRIJD');
                 $values["vhOmschrijving"] = MySQL::SQLValue($onderwerp);
                 $values["vhMailBCC"] = MySQL::SQLValue('gvh@vecasoftware.com');

                 $values["vhUserCreatie"] = MySQL::SQLValue($pZender);
                 $values["vhDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                 $values["vhRecStatus"] = MySQL::SQLValue('A');

                 $mailHeader = $db->InsertRow("evim_vh_versturen_headers", $values);

                 // -----------------------
                 // Registratie mail-header
                 // -----------------------

                 $values = array();
                 $where = array();

                 $values["twMailHeaderId"] = MySQL::SQLValue($mailHeader, MySQL::SQLVALUE_NUMBER);
                 $where["twId"] = MySQL::SQLValue($pWedstrijd, MySQL::SQLVALUE_NUMBER);

                 $db->UpdateRows("ssp_twbs_tw", $values, $where);
             }

             // ------------
             // Create MAILS
             // ------------

             $aantalMailsVerstuurd = 0;

             $sqlStat = "Select * from ssp_twbs_aw inner join ssp_ad on adCode = awSpeler where awTW = $pWedstrijd and awType = 'speler'  and awAanwezig = 1 order by adVoornaamNaam";
             $db->Query($sqlStat);

            while ($awRec = $db->Row()) {

                $persoon = $awRec->awSpeler;

                $persoonRec = SSP_db::Get_SSP_adRec($persoon);

                if ($mailNaar == "*NIEUW"){

                    $sqlStat = "Select count(*) as aantal from evim_vd_versturen_detail where vdHeader = $mailHeader and vdPersoon = '$persoon'";
                    $db2->Query($sqlStat);

                    if ($vdRec = $db2->Row())
                        if ($vdRec->aantal >= 1)
                            continue;

                }

                $mailString = SSP_personen::GetPersoonMailString($persoon);

                // $mailString = "gvh@vecasoftware.com";

                if (! $mailString)
                    continue;

                // ----------------------
                // Template vars & values
                // ----------------------

                $arr_VARS = array();
                $arr_VALUES = array();

                $arr_VARS[] = "VOORNAAM_NAAM";
                $arr_VALUES[] = $persoonRec->adVoornaamNaam;

                $arr_VARS[] = "WEDSTRIJD_INFO";
                $arr_VALUES[] = nl2br($twRec->twMailWedstrijdInfo);

                $arr_VARS[] = "SAMENKOMEN";
                $arr_VALUES[] = nl2br($twRec->twMailSamenkomenOm);

                $arr_VARS[] = "EXTRA_INFO";
                if  ($twRec->twMailExtraInfo)
                    $arr_VALUES[] = "<br/>" . nl2br($twRec->twMailExtraInfo) . "<br/>";
                else
                    $arr_VALUES[] = "<br/>";

                $arr_VARS[] = "SELECTIE";

                if  ($twRec->twMailVolledigeSelectie == 'Ja'){

                    $selectieHTML = self::GetWedstrijdSelectieHTML($pWedstrijd);

                    $arr_VALUES[] = "$selectieHTML <br/>";

                }

                else
                    $arr_VALUES[] = "<br/>";


                $arr_VARS[] = "AFZENDER";
                $arr_VALUES[] = $naamZender;

                $mailBody = '<html><body>';
                $mailBody .= nl2br($imRec->imTekst);
                $mailBody .= '</body></html>';

                $mailBody = SSP_evim::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);

                $values = array();

                $values["vdHeader"] = MySQL::SQLValue($mailHeader, MySQL::SQLVALUE_NUMBER);
                $values["vdPersoon"] = MySQL::SQLValue($persoon);

                $values["vdMailTo"] = MySQL::SQLValue($mailString);


                if ($aantalMailsVerstuurd == 0)
                    $values["vdMailBCC"] = MySQL::SQLValue('gvh@vecasoftware.com');

                $values["vdMailOnderwerp"] = MySQL::SQLValue($onderwerp);
                $values["vdMailBody"] = MySQL::SQLValue($mailBody);

                $mail = $db2->InsertRow("evim_vd_versturen_detail", $values);

                SSP_evim::SndMail($mail, $mailZender, $naamZender);

                $aantalMailsVerstuurd++;

            }

            // Reset MAIL-NAAR

            $values = array();
            $where = array();

            $values["twMailNaar"] =  MySQL::SQLValue("", MySQL::SQLVALUE_NUMBER);

            $where["twId"] =  MySQL::SQLValue($pWedstrijd, MySQL::SQLVALUE_NUMBER);

            $db->UpdateRows("ssp_twbs_tw", $values, $where);

            // -------------
            // Einde functie
            // -------------

             return $aantalMailsVerstuurd;

         }

         // ========================================================================================
         // Function: Kopieer verslag info (van deel 1)
         //
         // In: 	Wedstrijd-ID
         //         Wedstrijddeel NAAR
         //         Wedstrijddeel VAN
         // ========================================================================================

         static function CpyVerslagInfo($pWedstrijdId, $pWedstrijdDeel, $pWedstrijdDeelVan = 1 ) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             $sqlStat = "Update ssp_twbs_aw "
                        ."set awStartPos$pWedstrijdDeel = awStartPos$pWedstrijdDeelVan "
                        ."Where awTW = $pWedstrijdId ";

             $db->Query($sqlStat);

             $sqlStat = "Delete From  ssp_twbs_pn_positie_niemand where pnTW = $pWedstrijdId and pnWedstrijddeel = $pwedstrijdDeel ";
             $db->Query($sqlStat);

             $sqlStat = "Select * from ssp_twbs_pn_positie_niemand where pnTW = $pWedstrijdId and pnWedstrijddeel = $pWedstrijdDeelVan";

             $db->Query($sqlStat);

             while ($pnRec = $db->Row()){

                 $positie = $pnRec->pnPositie;

                 $values["pnTW"] = MySQL::SQLValue($pWedstrijdId, MySQL::SQLVALUE_NUMBER);
                 $values["pnWedstrijddeel"] = MySQL::SQLValue($pWedstrijdDeel, MySQL::SQLVALUE_NUMBER);
                 $values["pnPositie"] = MySQL::SQLValue($positie, MySQL::SQLVALUE_NUMBER);

                 $db2->InsertRow("ssp_twbs_pn_positie_niemand", $values);
             }


         }

         // ========================================================================================
         // Function: Bepaal & registreer wedstrijdverslag status
         //
         // In: 	Wedstrijd-ID
         // ========================================================================================

         static function SetVerslagStatus($pWedstrijdId){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $verslagType = self::GetVerslagType($pWedstrijdId);

             if ($verslagType == '*NVT') {

                 $sqlStat = "Update ssp_twbs_tw set twVerslagStatus = '*NVT' where twId = $pWedstrijdId";
                 $db->Query($sqlStat);

             }

             elseif ($verslagType == '*BASIS'){

                 $twRec = self::get_twRec($pWedstrijdId);

                 if ($twRec->twVerslag > ' ')
                     $sqlStat = "Update ssp_twbs_tw set twVerslagStatus = '*OK' where twId = $pWedstrijdId";
                 else
                     $sqlStat = "Update ssp_twbs_tw set twVerslagStatus = '*GEEN' where twId = $pWedstrijdId";

                 $db->Query($sqlStat);
             }
             else
                 self::HdlVerslagInput($pWedstrijdId, $wedstrijdDeel, true);



         }

         // ========================================================================================
         // Function: Ophalen & opvullen Wedstrijd  bij ERA Wedstrijd-header
         //
         // In: 	Training/Wedstrijd-ID
         // ========================================================================================

         static function FillTwWedstrijdId($pTW) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_twbs_tw where twId = $pTW";
             $db->Query($sqlStat);

             if (! $twRec = $db->Row())
                 return;

             if ($twRec->twType == 'TRAINING')
                 return;
             if ($twRec->twType == 'TORNOOI')
                 return;

             $ploeg = $twRec->twPloeg;

             if (! $ploeg)
                 return;

             $datum = $twRec->twDatum;

             if (! $datum)
                 return;

             $sqlStat = "Select * from ssp_vw where vwPloeg = $ploeg and vwDatum = '$datum' and (vwStatus = 'TS' or vwStatus = 'GS')";

             $db->Query($sqlStat);
             $vwRec = $db->Row();

             if (! $vwRec){

                 $sqlStat = "Select * from ssp_vw where vwPloeg = $ploeg and vwDatum = '$datum'";
                 $db->Query($sqlStat);
                 $vwRec = $db->Row();

             }

             if (! $vwRec)
                 return;

             $wedstrijd = $vwRec->vwId;

             if ($wedstrijd){

                 $sqlStat = "Update ssp_twbs_tw set twWedstrijdId = $wedstrijd where twId = $pTW";
                 $db->Query($sqlStat);

            }


            // -------------
            // Einde functie
            // -------------


         }
         // ========================================================================================
         // Function: Is trainer van een ploeg?
         //
         // In: 	Persoon
         //         Ploeg
         //
         // Return: Trainer? (true/false)
         // ========================================================================================

         static function IsPloegTrainer($pPersoon, $pPloeg){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $isPloegTrainer = false;

             $sqlStat = "Select count(*) from ssp_vp  where vpId = $pPloeg and (vpTrainer = '$pPersoon' or vpTrainer2 = '$pPersoon' or vpTrainer3 = '$pPersoon' or vpTrainer4 = '$pPersoon' or vpTrainer5 = '$pPersoon')";

             $db->Query($sqlStat);

             if ($vpRec = $db->Row())
                 if ($vpRec->aantal >= 1)
                     $isPloegTrainer = true;


             // -------------
             // Einde functie
             // -------------

             return $isPloegTrainer;


         }



         // ========================================================================================
         // Function: Is trainer een "hulp-trainer"?
         //
         // In: 	Persoon
         //
         // Return: Hulptrainer? (true/false)
         // ========================================================================================

         static function IsHulpTrainer($pPersoon){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $isHulpTrainer = false;

             $sqlStat = "Select count(*) as aantal from ssp_ad where adCode = '$pPersoon' and adFunctieVB like '%hulp.tr%'";
             $db->Query($sqlStat);

             if ($adRec = $db->Row())
                 if ($adRec->aantal >= 1)
                     $isHulpTrainer = true;


             // -------------
             // Einde functie
             // -------------

             return $isHulpTrainer;


         }


         // -----------
         // Einde CLASS
         // -----------

 	}
?>