<?php 
     class SSP_eva { // define the class
	 
 		// ===================================================================================================
		// Functie: Aanmaken eva_ed_evaluatie_detail Records
		//
		// In:	- ehId = Evaluatie-header ID
		//		- Persoon = Geëvalueerde
		//		- Type = *VELDSPELER/ *DOELMAN/ *TRAINER
		// ===================================================================================================
         
        static function CrtEvaluatieDetail($ehId, $persoon, $type="*VELDSPELER") {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
			
			// --------------------------
			// Get categorie geëvalueerde
			// --------------------------

			$query = "Select * from ssp_ad where adCode = '$persoon'";
		
			if (! $db->Query($query))
				return;
				
			$adRec = $db->Row();
			
			$voetbalCat =  $adRec->adVoetbalCat;
			$geboorteJaar =  $adRec->adGeboorteJaar;

			if (($voetbalCat <= " ") && ($type != '*JEUGDTRAINER'))
				return;

			// ------------------------------
			// Translate to "vraag-categorie"
			// ------------------------------
			
			$eva_cat = "";
			
			if ($type == "*VELDSPELER") {
			
				if ($voetbalCat == "U6")
					$eva_cat = "*U6";
				if ($voetbalCat == "U7")
					$eva_cat = "*U7";
				if ($voetbalCat == "U8")
					$eva_cat = "*U8";
				if ($voetbalCat == "U9")
					$eva_cat = "*U9";
				if ($voetbalCat == "U10" or $voetbalCat == "U11")
					$eva_cat = "*U10-U11";	
				if ($voetbalCat == "U12" or $voetbalCat == "U13")
					$eva_cat = "*U12-U13";	
				if ($voetbalCat == "U14" or $voetbalCat == "U15")
					$eva_cat = "*U14-U15";					
				if ($voetbalCat == "U16" or $voetbalCat == "U17")
					$eva_cat = "*U16-U17";
				if ($voetbalCat == "U21" or $voetbalCat == "SEN")
					$eva_cat = "*U21";
                if ($voetbalCat == "G" )
                    $eva_cat = "*GTEAM";

            }

			if ($type == "*DOELMAN") {
			
				if ($voetbalCat == "U6")
					$eva_cat = "*DOELMAN_JEUGD";
				if ($voetbalCat == "U7" or $voetbalCat == "U8" or $voetbalCat == "U9")
					$eva_cat = "*DOELMAN_JEUGD";
				if ($voetbalCat == "U10" or $voetbalCat == "U11")
					$eva_cat = "*DOELMAN_JEUGD";	
				if ($voetbalCat == "U12" or $voetbalCat == "U13")
					$eva_cat = "*DOELMAN_JEUGD";	
				if ($voetbalCat == "U14" or $voetbalCat == "U15")
					$eva_cat = "*DOELMAN_JEUGD";					
				if ($voetbalCat == "U16" or $voetbalCat == "U17")
                    $eva_cat = "*DOELMAN_JEUGD";
                if ($voetbalCat == "U21")
                    $eva_cat = "*DOELMAN_JEUGD";
				if ($voetbalCat == "SEN")
					$eva_cat = "*DOELMAN_JEUGD";
			}		
			
			if ($type == "*JEUGDTRAINER")
				$eva_cat = "*JEUGDTRAINER";

			if ($eva_cat <= " ")
				return;

			// -------------------------------------
			// Copiëren vragen naar evaluatie-detail
			// -------------------------------------
			
			$query = "Select * from eva_vr_vragen where vrRecStatus = 'A' and vrVoetbalCat = '$eva_cat'";
			
			if (! $db->Query($query))
				return;

			$datetime = date_create()->format('Y-m-d H:i:s');
			
			while($vrRec = $db->Row()) {
	
				$values["edEvaluatieId"] = MySQL::SQLValue($ehId, MySQL::SQLVALUE_NUMBER);
				$values["edPersoon"] = MySQL::SQLValue($persoon);
				$values["edSort"] = MySQL::SQLValue($vrRec->vrSort, MySQL::SQLVALUE_NUMBER );
				$values["edRubriek"] = MySQL::SQLValue($vrRec->vrRubriek);
				$values["edVraag"] = MySQL::SQLValue($vrRec->vrVraag);
				$values["edQuotering"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
				$values["edToelichting"] = MySQL::SQLValue(" ");
				$values["edGewicht"] = MySQL::SQLValue($vrRec->vrGewicht, MySQL::SQLVALUE_NUMBER);
				$values["edVraagId"] = MySQL::SQLValue($vrRec->vrId, MySQL::SQLVALUE_NUMBER);			
				$values["edToelichtingVerplicht"] = MySQL::SQLValue($vrRec->vrToelichtingVerplicht, MySQL::SQLVALUE_NUMBER);
					
				$values["edDatumUpdate"] = MySQL::SQLValue($datetime);
				$values["edUserUpdate"] =  MySQL::SQLValue("*SQL");
				
				$result = $db2->InsertRow("eva_ed_evaluatie_detail", $values); 
	
			
			}
			
				
		}
		
		
		// ===================================================================================================
		// Functie: Bijwerken bestand: "eva_sp_security_per_persoon" op basis van "eva_es_evaluatie_security"
		//
		// In: - userId
		// ===================================================================================================
         
        static function UpdPersoonSecurity($userId) {  
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 

			// -----------------------------------------
			// Delete all security-records of given user
			// -----------------------------------------
			
			$query = "Delete from  eva_sp_security_per_persoon where spUser = '$userId'";
			if (! $db->Query($query))
				return;
			
			// -----------------------------------------------
			// Create records in "eva_sp_security_per_persoon"
			// -----------------------------------------------
			
            $query = "Select * from eva_es_evaluatie_security where esAuthority = '*ALL' and esUser = '$userId'";
			
			if (! $db->Query($query))
				return;
		
			while($esRec = $db->Row()) {
			
				// ------------------------------
				// Get personen based on category
				// ------------------------------
				if ($esRec->esVoetbalCat > ' ') {
				
					$personen = self::getCatPersonen($esRec->esVoetbalCat);
					
					foreach ($personen as $persoon) {
						self::crtSecPerPersoonRec($persoon, $userId, $esRec->esAuthority);
					}
						
				}
				
				// ---------------------
				// Directly on "persoon"
				// ---------------------
				
				if ($esRec->esPersoon > ' ') {
				
					self::crtSecPerPersoonRec($esRec->esPersoon, $userId, $esRec->esAuthority);

						
				}	
			
			}
            $query = "Select * from eva_es_evaluatie_security where esAuthority = '*OWN' and esUser = '$userId'";
			
			if (! $db->Query($query))
				return;
		
			while($esRec = $db->Row()) {
			
				// ------------------------------
				// Get personen based on category
				// ------------------------------
				if ($esRec->esVoetbalCat > ' ') {
				
					$personen = self::getCatPersonen($esRec->esVoetbalCat);
					
					foreach ($personen as $persoon) {
						self::crtSecPerPersoonRec($persoon, $userId, $esRec->esAuthority);
					}
						
				}
				
				// ---------------------
				// Directly on "persoon"
				// ---------------------
				
				if ($esRec->esPersoon > ' ') {
				
					self::crtSecPerPersoonRec($esRec->esPersoon, $userId, $esRec->esAuthority);

						
				}	
			
			}

		}   
		
		// ===================================================================================================
		// Functie: Create eva_sp_security_per_persoon-record
		//
		// In: - categorie
		// ===================================================================================================
         
        static function crtSecPerPersoonRec($persoon, $userId, $authority) {  
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
		
			$datetime = date_create()->format('Y-m-d H:i:s');
			
			$values["spPersoon"] = MySQL::SQLValue($persoon);
			$values["spUser"] = MySQL::SQLValue($userId);
			$values["spAuthority"] = MySQL::SQLValue($authority);
			$values["spDatumCreatie"] =  MySQL::SQLValue($datetime);

		
			$result = $db->InsertRow("eva_sp_security_per_persoon", $values); 
			
			
		}
		
		
		// ===================================================================================================
		// Functie: Get alle "personen" van een bepaalde "categorie"
		//
		// In: - categorie
		// ===================================================================================================
         
        static function getCatPersonen($categorie) {  
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			$personen = array();
			
			$query = "Select * from ssp_ad where adVoetbalCat = '$categorie'";
			
			if (! $db->Query($query))
				return $personen;			
			
			$i = 0;
			
			while($adRec = $db->Row()) {
			
				$personen[$i] = $adRec->adCode;
				$i++;
				
			}
			
			return $personen;
			
				
		}
		
		// ===================================================================================================
		// Functie: Get evaluaties (in HTML formaat)
		//
		// In: 	- peroon
		//		- SessionId
		// ===================================================================================================
         
        static function GetEvaluatiesHTML($pPersoon, $pSession) {  
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
	 		include_once(SX::GetClassPath("personen.class"));
		 	include_once(SX::GetSxClassPath("tools.class"));	
				
			$sqlStat = "Select * from eva_eh_evaluatie_headers where ehPersoon = '$pPersoon' order by ehDatumCreatie desc";
				
			if (! $db->Query($sqlStat))
				return "*NONE";	

			$HTML = "*";
			
			while ($ehRec = $db->Row()) {
				
				if (! self::CheckLeesRechten($pPersoon, $pSession, $ehRec->ehUserCreatie ))
					continue;
				
				if ($HTML == "*")
					$HTML = "";
				
				if ($HTML > " ")
					$HTML .= "<br style='clear: both'/>";
				
				$evaluator = SSP_personen::GetNaam($ehRec->ehUserCreatie);
				$datum = SX_tools::EdtDate($ehRec->ehDatumCreatie,'%d %B %Y');
				$image = SX::GetDocImage('pdf');
				
				$url = "/eva_evaluatie.php?seid=" . $pSession . "&ehid=" . $ehRec->ehId;
				
				$HTML .= "<a href='$url' target='_blank' style='color: blue'><div style='float: left; width: 40px'>$image</div><div style='float: left'>Evaluator: $evaluator, datum: $datum</div></a>";
				
				
				
			}
			

			if ($HTML == "*")
				$HTML = "*NONE";
			
			// -------------
			// Functie einde
			// -------------
			
			$db->close();
			return $HTML;

		}
		
		// ===================================================================================================
		// Functie: Check evaluaties 
		//
		// In: 	- peroon
		//		- SessionId
		// ===================================================================================================
         
        static function CheckEvaluaties($pPersoon, $pSession) {  
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
	 		include_once(SX::GetClassPath("personen.class"));
				
			$sqlStat = "Select * from eva_eh_evaluatie_headers where ehPersoon = '$pPersoon' order by ehDatumCreatie desc";
				
			if (! $db->Query($sqlStat))
				return false;

			$return = false;
			
			while ($ehRec = $db->Row()) {
				
				if (! self::CheckLeesRechten($pPersoon, $pSession, $ehRec->ehUserCreatie ))
					continue;
				
				$return = true;
				break;
				
				
			}
			
			// -------------
			// Functie einde
			// -------------
			
			$db->close();
			return $return;
			
		}		

		// ===================================================================================================
		// Functie: Leesrechten? 
		//
		// In: 	- peroon
		//		- SessionId
		// ===================================================================================================
         
        static function CheckLeesRechten($pPersoon, $pSessionId, $pEvaluator) { 
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			
			include_once(SX::GetSxClassPath("sessions.class"));
			
			$userId = SX_sessions::GetSessionUserId($pSessionId);

			if (! $userId)
			    return false;

			if ($userId == "*NONE")
				return false;
			
			if ($userId == $pEvaluator )
				return true;
			
			if ($userId == 'webmaster')
			    return true;
			
			// ----------------------------------------------
			// Bestuur & sportieve staf -> Altijd leesrechten
			// ----------------------------------------------
			
			$sqlStat = "Select count(*) as aantal from ssp_ad where adCode = '$userId' and (adFunctieVB LIKE '%bestuur%' or adFunctieVB like '%sp.staf%') and adRecStatus = 'A'";
				
			if ($db->Query($sqlStat))
				if ($adRec = $db->Row())
					if ($adRec->aantal >= 1)
						return true;


			// -------------------------------------------------------------
		    // Op basis zelfde security "where-statement" als EVA toepassing
			// -------------------------------------------------------------

			$where = self::GetSecAdWHERE($userId);

			if ($where){

			    $sqlStat = "Select count(*) as aantal from ssp_ad where adCode = '$pPersoon' and ($where)";
                $db->Query($sqlStat);

                if ($adRec = $db->Row()) {

                    if ($adRec->aantal >= 1)
                        return true;

                }

            }

			// ------------------
			// Else: geen rechten
			// ------------------
					
			return false;


		}

		// ===================================================================================================
		// Functie: Ophalen categorieën waarvoor toegang
		//
		// In: 	- userId
		//
		// Out: - array met Categorieën
		// ===================================================================================================
         
        static function GetEvaAuthCats($pUserId) { 
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
			include_once(SX::GetSxClassPath("auth.class"));
			include_once(SX::GetClassPath("settings.class"));

			$cats = array();
			
			$allCats = false;
			$autoCats = false;
			
			// -----------------------------
			// Expliciet aan user toegewezen
			// -----------------------------
			
			$sqlStat = "Select * from eva_es_evaluatie_security where esVoetbalCat > ' ' and esUser = '$pUserId'";

			if ($allCats == false) {
					
				if (! $db->Query($sqlStat))
					return $cats;
				
				while($esRec = $db->Row()) {
					
					if ($esRec->esVoetbalCat == '*ALL') {
						$allCats = true;
						break;
					}
					
					if ($esRec->esVoetbalCat == '*AUTO') {
						$autoCats = true;
						continue;
					}
					
					$cats[] = $esRec->esVoetbalCat;
										
				}
				
			}
		
			// -----------
			// Via de role
			// -----------
			
			if ($allCats == false) {
				
				$sqlStat = "Select * from eva_es_evaluatie_security where esVoetbalCat > ' ' and esRole > ' '";

					
				if (! $db->Query($sqlStat))
					return $cats;
				
				while($esRec = $db->Row()) {
					
					$checkUserRole = SX_auth::CheckUserRole($pUserId, $esRec->esRole);
					
					if ($checkUserRole == true) {
					
						if ($esRec->esVoetbalCat == '*ALL') {
							$allCats = true;
							break;
						}
						
						if ($esRec->esVoetbalCat == '*AUTO') {
							$autoCats = true;
							continue;
						}
						
						$cats[] = $esRec->esVoetbalCat;
					
					}
										
				}
				
			}	

			// --------
			// ALL Cats
			// --------
						
			if ($allCats == true) {
				
				unset($cats); 
				$cats = array();
				
				$sqlStat = "Select * from sx_ta_tables where taTable = 'EVA_VOETBAL_CAT' and taCode <> '*ALL' and taCode <> '*AUTO' order by taSort";

				$db->Query($sqlStat);
				
				while ($taRec = $db->Row()) {
					
					$cats[] = $taRec->taCode;
										
				}
				
				$db->close();
				return $cats;
					
			}

			// -------------------------------------------
			// AUTO Cats (based on "trainers" in "ploegen")
			// -------------------------------------------


			if ($autoCats == true) {

				
				$actiefSeizoen = SSP_settings::GetActiefSeizoen();
							
				$sqlStat = "Select distinct(vpVoetbalCat) from ssp_vp where vpSeizoen = '$actiefSeizoen' and ( vpTrainer = '$pUserId' or vpTrainer2 = '$pUserId' or vpTrainer3 = '$pUserId' or vpTrainer4 = '$pUserId' or vpTrainer5 = '$pUserId')";

				$db->Query($sqlStat);
				
				while ($vpRec = $db->Row()) {
					
					$cats[] = $vpRec->vpVoetbalCat;
										
				}
				
			}
			
			// ------------
			// End function
			// ------------
			
			$db->close();
			return $cats;

		}

		// ===================================================================================================
		// Functie: Ophalen specifieke spelers waarvoor toegang
		//
		// In: 	- userId
		//
		// Out: - array met spelers
		// ===================================================================================================
         
        static function GetEvaAuthSpelers($pUserId) { 
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

			include_once(SX::GetSxClassPath("auth.class"));
			include_once(SX::GetClassPath("settings.class"));

			$spelers = array();
			
			// -----------------------------
			// Expliciet aan user toegewezen
			// -----------------------------
			
			$sqlStat = "Select * from eva_es_evaluatie_security where esPersoon > ' ' and esUser = '$pUserId'";

			if ($allCats == false) {
					
				if (! $db->Query($sqlStat))
					return $spelers;
				
				while($esRec = $db->Row()) {
					
					$spelers[] = $esRec->esPersoon;
										
				}
				
			}
	
			// -----------
			// Via de role
			// -----------
			
			if ($allCats == false) {
				
				$sqlStat = "Select * from eva_es_evaluatie_security where esPersoon > ' ' and esRole > ' '";

					
				if (! $db->Query($sqlStat))
					return $spelers;
				
				while($esRec = $db->Row()) {
					
					$checkUserRole = SX_auth::CheckUserRole($pUserId, $esRec->esRole);
					
					if ($checkUserRole == true) {

						$spelers[] = $esRec->esPersoon;
					
					}
										
				}
				
			}	

			// ----------------------------------
			// Sowieso spelers vaneigen ploeg(en)
            // ----------------------------------

            $actiefSeizoen = SSP_settings::GetActiefSeizoen();

            $sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and ( vpTrainer = '$pUserId' or vpTrainer2 = '$pUserId' or vpTrainer3 = '$pUserId' or vpTrainer4 = '$pUserId' or vpTrainer5 = '$pUserId')";

            $db->Query($sqlStat);

            while ($vpRec = $db->Row()) {

                $ploeg = $vpRec->vpId;

                $sqlStat = "Select * from ssp_vp_sp where spPloeg = $ploeg and spType = 'Speler'";
                $db2->Query($sqlStat);

                while ($spRec = $db2->Row())
                    $spelers[] = $spRec->spPersoon;

                
            }


            // ------------
			// End function
			// ------------
			
			$db->close();

            $spelers = array_unique($spelers);

			return $spelers;

		}



         // ===================================================================================================
         // Functie: Ophalen WHERE-statement voor ssp_ad file (gebruikt in EVA-applicatie)
         //
         // In User-id
         //    Prefix (bijvoorbeeld: 'persoon.')
         //
         // Out: Where statement
         // ===================================================================================================

         static function GetSecAdWHERE($pUserId, $pPrefix = ""){

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             include_once(SX::GetSxClassPath("auth.class"));
             include_once(SX::GetClassPath("settings.class"));

             $cats = self::GetEvaAuthCats($pUserId);
             $spelers = self::GetEvaAuthSpelers($pUserId);

             $where = null;

             // -----------
             // Categorieën
             // -----------


             foreach($cats as $cat){


                 if ($cat != 'JEUGDTRAINER' and $cat != 'GTEAM_TRAINER') {

                     if (! $where)
                         $where = "(" . $pPrefix . "adVoetbalCat = '$cat'";
                     else
                         $where .= " or " .$pPrefix . "adVoetbalCat = '$cat'";

                 } else {

                     if ($cat == 'JEUGDTRAINER') {

                         if (!$where)
                             $where = "(" . $pPrefix . "adFunctieVB LIKE '%jeugd.tr%'";
                         else
                             $where .= " or " . $pPrefix . "adFunctieVB LIKE '%jeugd.tr%'";
                     }

                     if ($cat == 'GTEAM_TRAINER') {

                         if (!$where)
                             $where = "(" . $pPrefix . "adFunctieVB LIKE '%gt.tr%'";
                         else
                             $where .= " or " . $pPrefix . "adFunctieVB LIKE '%gt.tr%'";
                     }


                 }

             }

             foreach($spelers as $speler){

                 if (! $where)
                     $where = "(" . $pPrefix ."adCode = '$speler'";
                 else
                     $where .= " or " . $pPrefix . "adCode = '$speler'";

             }

             if ($where)
                 $where .= ")";



             // -------------
             // Einde functie
             // -------------

             if (! $where)
                 $where = "1=2";

             return $where;

         }

		// ===================================================================================================
		// Functie: Check type evaluatietype voor bepaalde persoon
		//
		// In: 	- persoon
		//		- evaluatie type
		//
		// Out: - OK? false/true
		// ===================================================================================================
         
        static function ChkEvaType($pPersoon, $pType) {
		
 			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object

            $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";

            $db->Query($sqlStat);

            if (! $adRec = $db->Row())
                return false;

            $isSpeler = false;
            $isJeugdTrainer = false;
            $isGteamTrainer = false;

            $functieVb = $adRec->adFunctieVB;

            // -------------
            // Is "speler" ?
            // -------------

            $pos = stripos($functieVb, 'speler');

            if ($pos === false)
                $isSpeler = false;
            else
                $isSpeler = true;

            // ------------------
            // Is "Jeugdtrainer"?
            // ------------------

            $pos = stripos($functieVb, 'jeugd.tr');

            if ($pos === false)
                $isJeugdTrainer = false;
            else
                $isJeugdTrainer = true;

            $pos = stripos($functieVb, 'gt.tr');

            if ($pos === false)
                $isGteamTrainer = false;
            else
                $isGteamTrainer = true;


            if ($pType == '*VELDSPELER' or $pType == '*DOELMAN')
                $returnVal = $isSpeler;

            if ($pType == '*JEUGDTRAINER')
                $returnVal = $isJeugdTrainer;

            if ($pType == '*GTEAMTRAINER')
                $returnVal = $isGteamTrainer;

            // -------------
            // Einde functie
            // -------------

            return $returnVal;


		}
         // ===================================================================================================
         // Functie: Delete Evaluatie Detail
         //
         // In:	- Evaluatie-header
         //
         // ===================================================================================================

         static function DelEvaluatieDetail($pEvaluatie){

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

            $sqlStat = "Delete from eva_ed_evaluatie_detail where edEvaluatieId = $pEvaluatie";
            $db->Query($sqlStat);

            // -------------
            // Einde functie
            // -------------

         }

		 // ===================================================================================================
         // Functie: Copy Evaluatie
         //
         // In:	- Evaluatie-header
         //     - Datum Creatie
         //     - USER-id
         //
         // Uit: Nieuwe Evaluatie ID
         // ===================================================================================================

         static function CpyEvaluatie($pEvaluatie, $pDatumCreatie, $pUser){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             include_once(SX::GetClassPath("_db.class"));

             // --------------------------------
             // Enkel indien bestaande evaluatie
             // --------------------------------

             $ehRec = SSP_db::Get_EVA_ehRec($pEvaluatie);

             if (! $ehRec)
                 return 0;

             // --------------------------------
             // Aanmaken nieuwe evaluatie-header
             // --------------------------------

             $curDate = date('Y-m-d');

             $values = array();

             $values["ehPersoon"] = MySQL::SQLValue($ehRec->ehPersoon);
             $values["ehType"] = MySQL::SQLValue($ehRec->ehType);
             $values["ehDatumCreatie"] = MySQL::SQLValue($pDatumCreatie, MySQL::SQLVALUE_DATE);

             $values["ehDatumUpdate"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
             $values["ehUserCreatie"] = MySQL::SQLValue($pUser);
             $values["ehUserUpdate"] = MySQL::SQLValue($pUser);
             $values["ehRecStatus"] = MySQL::SQLValue('A');

             $ehId = $db->InsertRow("eva_eh_evaluatie_headers", $values);

             // -------------------------
             // Copiëren evaluatie detail
             // -------------------------

             $sqlStat = "Select * from eva_ed_evaluatie_detail where edEvaluatieId = $pEvaluatie order by edSort";
             $db->Query($sqlStat);

             while ($edRec = $db->Row()){

                 $values = array();

                 $values["edEvaluatieId"] = MySQL::SQLValue($ehId, MySQL::SQLVALUE_NUMBER);

                 $values["edPersoon"] = MySQL::SQLValue($edRec->edPersoon);
                 $values["edRubriek"] = MySQL::SQLValue($edRec->edRubriek);
                 $values["edVraag"] = MySQL::SQLValue($edRec->edVraag);
                 $values["edQuotering"] = MySQL::SQLValue($edRec->edQuotering, MySQL::SQLVALUE_NUMBER);
                 $values["edToelichting"] = MySQL::SQLValue($edRec->edToelichting);
                 $values["edGewicht"] = MySQL::SQLValue($edRec->edGewicht, MySQL::SQLVALUE_NUMBER);
                 $values["edSort"] = MySQL::SQLValue($edRec->edSort, MySQL::SQLVALUE_NUMBER);
                 $values["edVraagId"] = MySQL::SQLValue($edRec->edVraagId, MySQL::SQLVALUE_NUMBER);
                 $values["edToelichtingVerplicht"] = MySQL::SQLValue($edRec->edToelichtingVerplicht, MySQL::SQLVALUE_NUMBER);

                 $values["edDatumUpdate"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
                 $values["edUserUpdate"] = MySQL::SQLValue($pUser);

                 $id = $db2->InsertRow("eva_ed_evaluatie_detail", $values);


             }
             // -------------
             // Einde functie
             // -------------

             return $ehId;

         }


         // -----------
         // EINDE CLASS
         // ------------


    }
       
?>