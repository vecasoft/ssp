<?php 
     class SSP_evim { // define the class
	 

		// ========================================================================================
		// Function: CrtInfoMailDetail
		//
		// In:	Header
		//
		// Uit: Aantal records aangemaakt
		//
		// ========================================================================================
		
		static function CrtInfoMails($pHeader) {

			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

            include_once(SX::GetClassPath("tennis.class"));
			
			$aantalGemaakt = 0;
			
			$sqlStat = "Select * from evim_vh_versturen_headers where vhId = $pHeader";
			$db->Query($sqlStat);
			
			if (! $vhRec = $db->Row())
				return 0;		

			if ($vhRec->vhFunctieVB > ' ' )
				$functieVB = '%' .  $vhRec->vhFunctieVB . '%';
			else
				$functieVB  = '*';

			if ($vhRec->vhFunctieSSP > ' ' )
				$functieSSP = '%' . $vhRec->vhFunctieSSP . '%';
			else
				$functieSSP  = '*';

			if ($vhRec->vhVoetbalCat > ' ' )
                $voetbalCat = $vhRec->vhVoetbalCat;
            else
                $voetbalCat  = '*';

            if ($vhRec->vhVoetbalLidgeldStatus > ' ' )
                $voetbalLidgeldStatus = $vhRec->vhVoetbalLidgeldStatus;
            else
                $voetbalLidgeldStatus  = '*';

            $innerJoin = "";
			if ($vhRec->vhLidkaartVanaf) {
			    $lidkaartVanaf = $vhRec->vhLidkaartVanaf;
                $innerJoin = "Inner join ela_ka_kaarten on kaPersoon = adCode and kaType = '*LIDKAART_VB' and kaOntvangen <> 1 and substr(kaDatumPrinted,1,10) >= '$lidkaartVanaf' ";
            }

			if ($vhRec->vhAbonnementkaartVanaf) {
                $abonnementkaartVanaf = $vhRec->vhAbonnementkaartVanaf;
                $innerJoin = "Inner join ela_ka_kaarten on kaPersoon = adCode and kaType = '*ABONNEMENT_VB' and kaOntvangen <> 1 and substr(kaDatumPrinted,1,10) >= '$abonnementkaartVanaf' ";
            }

			if ($vhRec->vhTennisAansluiting){

			    $aansluitingVanaf = $vhRec->vhTennisAansluitingVanaf;
                $tennisSeizoen = SSP_tennis::GetSeizoen();

			    $innerJoin .= " inner join tennis_aansluiting_aanvragen on aaCode = adCode and aaSeizoen = '$tennisSeizoen' and aaAansluitingVTV = 1 and aaVTVnummer > ' ' and aaAansluitdatum >= '$aansluitingVanaf' ";

            }

			$sqlStat 	= "Select distinct(adCode) as persoon From ssp_ad "
                        . $innerJoin
						. "where (adRecStatus = 'A') "
						. "and (adMail > ' ') "
                        . "and (adClubVerlatenEindeSeizoen <> 1) "
						. "and (adCode not in (select vdPersoon from evim_vd_versturen_detail where vdHeader = $pHeader)) "
						. "and ('$functieVB' = '*' or adFunctieVB like '$functieVB') "
						. "and ('$functieSSP' = '*' or adFunctieSSP like '$functieSSP') "
                        . "and ('$voetbalCat' = '*' or adVoetbalCat = '$voetbalCat') "
                        . "and ('$voetbalLidgeldStatus' = '*' or adLidgeldVoldaanVB = '$voetbalLidgeldStatus') ";

			$db->Query($sqlStat);
			
			while ($adRec = $db->Row()){
				
				$persoon = $adRec->persoon;
				
				$values["vdHeader"] = MySQL::SQLValue($pHeader, MySQL::SQLVALUE_NUMBER);	
				$values["vdPersoon"] = MySQL::SQLValue($persoon);

				$mail = $db2->InsertRow("evim_vd_versturen_detail", $values);
				
				self::CrtInfoMail($mail);
				
				$aantalGemaakt++;
								
			}
	
			$sqlStat = "Update evim_vh_versturen_headers set vhNieuwToevoegen = 'Nee' where vhId = $pHeader";
			
			$db->Query($sqlStat);
	
			// -------------
			// Einde functie
			// -------------
			
			return $aantalGemaakt;
			
		}

		// ========================================================================================
		// Function: CrtInfoMail
		//
		// In:	Mail
		//
		// Uit: Aangemaakt? true/false (evim_vd_versturen_detail Record opgevuld)
		//
		// ========================================================================================
		
		static function CrtInfoMail($pMail) {   

			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);			
			
			include_once(SX::GetClassPath("personen.class"));
		
			// -------------------
			// Ophalen nodige data
			// -------------------
			
			$mail = $pMail;
			
			$sqlStat = "Select * from evim_vd_versturen_detail where vdId = $mail";
			$db->Query($sqlStat);
			
			if (! $vdRec = $db->Row())
				return false;
			
			$vhId = $vdRec->vdHeader;
			
			$sqlStat = "Select * from evim_vh_versturen_headers where vhId = $vhId";
			$db->Query($sqlStat);
			
			if (! $vhRec = $db->Row())
				return false;	
			
			$persoon = $vdRec->vdPersoon;
			
			$sqlStat = "Select * from ssp_ad where adCode = '$persoon'";
			$db->Query($sqlStat);
			
			if (! $adRec = $db->Row())
				return false;				
			
			$zender = $vhRec->vhZender;
			
			$sqlStat = "Select * from evim_mz_mail_zenders where mzId = $zender";
			$db->Query($sqlStat);
			
			if (! $mzRec = $db->Row())
				return false;		
		
			$mail = $vhRec->vhMail;
			
			$sqlStat = "Select * from evim_im_info_mail where imId = $mail";
			$db->Query($sqlStat);
			
			if (! $imRec = $db->Row())
				return false;
			
			// ----------------------
            // Template vars & values
            // ----------------------

            $arr_VARS = array();
            $arr_VALUES = array();

            $arr_VARS[] = "VOORNAAM_NAAM";
            $arr_VALUES[] = $adRec->adVoornaamNaam;

            $arr_VARS[] = "NAAM_VOORNAAM";
            $arr_VALUES[] = $adRec->adNaamVoornaam;

            $arr_VARS[] = "NAAM";
            $arr_VALUES[] = $adRec->adVoornaamNaam;

            $arr_VARS[] = "VOORNAAM";
            $arr_VALUES[] = $adRec->adVoornaam;

            $arr_VARS[] = "ACHTERNAAM";
            $arr_VALUES[] = $adRec->adNaam;

            $arr_VARS[] = "VTVNUMMER";
            $arr_VALUES[] = $adRec->adTennisLidnummer;

			// ---------------------
			// Ophalen mail-adressen
			// ---------------------
						
			$mailTo = SSP_personen::GetPersoonMailString($persoon);
			$mailCC = $vhRec->vhMailCC;
			$mailBCC = $vhRec->vhMailBCC;	
			
			$mailOnderwerp = $imRec->imOnderwerp;
			
			// $mailOnderwerp = utf8_encode($mailOnderwerp);
			
			$mailOnderwerp = self::FillTemplateVars($mailOnderwerp, $arr_VARS, $arr_VALUES);
			
			$mailBody = '<html><body>';
			
			$mailBody .= nl2br($imRec->imTekst);		
			if ($imRec->imHandtekeningToevoegen == 1)
					$mailBody .= "<br/>" . nl2br($mzRec->mzHandtekening);	

			$mailBody .= '</body></html>';
		
			// $mailBody = utf8_encode($mailBody);
			
			$mailBody = self::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);
			
			
			// -------------------------------
			// Update evim_vd_versturen_detail
			// -------------------------------
			
			$values["vdMailOnderwerp"] = MySQL::SQLValue($mailOnderwerp);
			$values["vdMailBody"] = MySQL::SQLValue($mailBody);

			
			$db2->UpdateRows("evim_vd_versturen_detail", $values, array("vdId" => $pMail));
						
			$sqlStat = "Update evim_vd_versturen_detail set vdMailTo = '$mailTo', vdMailCC = '$mailCC', vdMailBCC = '$mailBCC'  where vdId = $pMail";
	
			if ($db->Query($sqlStat))
				return true;
			else;
				return false;
		
		}
         // ========================================================================================
         // Function: Check of mail-header mag gewist worden (indien geen detail)
         //
         // In:	Header
         //
         // Uit: Mag gewist ?
         //
         // ========================================================================================

         static function ChkDeleteMailHeader($pHeader) {

		    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

            $sqlStat = "Select count(*) as aantal from evim_vd_versturen_detail where vdHeader = $pHeader";

            $db->Query($sqlStat);

            if (! $vdRec = $db->Row())
                return true;

            if ($vdRec->aantal > 0)
                return false;

            // -------------
            // Einde functie
            // -------------

            return true;

		}

		// ========================================================================================
		// Function: Verzenden mail
		//
		// In:	mail-id
		//
		// Uit: Mail verstuurd? true/false
		//
		// ========================================================================================
		
		static function SndMail($pMail, $pMailZender = "", $pNaamZender = "") {

			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			include_once(SX::GetSxClassPath("tools.class"));
			
			// -------------------
			// Ophalen nodige data
			// -------------------

			$sqlStat = "Select * from evim_vd_versturen_detail where vdId = $pMail";
			$db->Query($sqlStat);
			
			if (! $vdRec = $db->Row())
				return false;

			$vhId = $vdRec->vdHeader;
			
			$sqlStat = "Select * from evim_vh_versturen_headers where vhId = $vhId";
			$db->Query($sqlStat);
			
			if (! $vhRec = $db->Row())
				return false;

			if (! $pMailZender) {

                $zender = $vhRec->vhZender;

                $sqlStat = "Select * from evim_mz_mail_zenders where mzId = $zender";
                $db->Query($sqlStat);

                if (!$mzRec = $db->Row())
                    return false;

			}

			// --------------
			// Verzenden mail
			// --------------			
			
			$mailSubject = utf8_encode($vdRec->vdMailOnderwerp);
			$mailBody = utf8_encode($vdRec->vdMailBody);
			
			$mailTo = $vdRec->vdMailTo;
			$mailCC = $vdRec->vdMailCC;
			$mailBCC = $vdRec->vdMailBCC;

			if (! $pMailZender) {

                $fromMail = $mzRec->mzMail;
                $fromName = utf8_encode($mzRec->mzNaam);
            }

            if ($pMailZender){

                $fromMail = $pMailZender;
                $fromName = $pNaamZender;

            }

			// -------
			// Bijlage
            // -------

            $bijlagePath = "";

            if ($vhRec->vhBijlage){

                $fileArray = my_json_decode($vhRec->vhBijlage);

                $fileName = basename($fileArray[0]["name"]);

                $origName = $fileArray[0]["usrName"];

                $bijlagePath = $_SESSION["SX_BASEPATH"] . '/_files/evim/' . $fileName;


            }

			
			SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName,$bijlagePath,'UTF-8', $mailCC, $origName);

			// -------------------------------
			// Update evim_vd_versturen_detail
			// -------------------------------
			
			$sqlStat = "Update evim_vd_versturen_detail set vdMailVerstuurd = 1, vdMailVerstuurdOp = now() where vdId = $pMail";

			$db->Query($sqlStat);

			// -------------
			// Einde functie
			// -------------
			
			return true;
		}
		
		// ========================================================================================
		// Function: Verzenden INFO-mail (ALLE nog niet verzonden)
		//
		// In:	vhId
		//
		// Uit: Aantal mails gestuurd
		//
		// ========================================================================================
		
		static function SndOpenMails($pHeader) {

			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
			
			$sqlStat = "Select * from evim_vd_versturen_detail where vdHeader = $pHeader and (vdMailVerstuurd is null or vdMailVerstuurd <> 1)";
			$db->Query($sqlStat);
			
			$aantalMails = 0;
			
			While ($vdRec = $db->Row()){
				
				if (self::SndMail($vdRec->vdId))
					$aantalMails++;
				
			}			
			
			// -------------
			// Einde functie
			// -------------
			
			return $aantalMails;
			
		}

         // ========================================================================================
         // Function: Aanmaken mails ivm zomertornooi (uitnodiging)
         //
         // In:	Header
         //
         // Uit: Aantal records aangemaakt
         //
         // ========================================================================================

         static function CrtZomertornooiMails($pHeader) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
             $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             $aantalGemaakt = 0;

             // ---------------
             // Get header info
             // ---------------

             $sqlStat = "Select * from evim_vh_versturen_headers where vhId = $pHeader";
             $db->Query($sqlStat);

             if (! $vhRec = $db->Row())
                 return 0;

             $reedsIngeschreven = $vhRec->vhZomertornooiIngeschreven;

             // ---------------------------------------
             // Aanmaken mail voor alle contactpersonen
             // ---------------------------------------

             $sqlStat = "Select * from ssp_cc inner join ssp_cl on clId = ccClub where ccUitnodigenTornooi = 1 order by clZoeknaam";

             $db->Query($sqlStat);

             while ($ccRec = $db->Row()){

                 $club = $ccRec->ccClub;

                 // -----------------------------------
                 // Test op "reeds ingeschreven" switch
                 // -----------------------------------

                 if ($reedsIngeschreven){

                     $sqlStat = "Select count(*) as aantal from ssp_zomertornooi where clubLink = $club";
                     $db3->Query($sqlStat);

                     if ($ztRec = $db3->Row()){

                         if (($reedsIngeschreven == 'Nee') and ($ztRec->aantal > 0))
                             continue;

                         if (($reedsIngeschreven == 'Ja') and ($ztRec->aantal == 0))
                             continue;
                     }


                 }


                 $contactPersoon = $ccRec->ccId;
                 $mailTo = $ccRec->ccMail;

                 if (! $mailTo)
                     continue;

                 $values["vdHeader"] = MySQL::SQLValue($pHeader, MySQL::SQLVALUE_NUMBER);
                 $values["vdClub"] = MySQL::SQLValue($club, MySQL::SQLVALUE_NUMBER);
                 $values["vdContactPersoon"] = MySQL::SQLValue($contactPersoon, MySQL::SQLVALUE_NUMBER);

                 $mail = $db2->InsertRow("evim_vd_versturen_detail", $values);

                 self::CrtZomertornooiMail($mail);
                 $aantalGemaakt++;

             }

             $sqlStat = "Update evim_vh_versturen_headers set vhNieuwToevoegen = 'Nee' where vhId = $pHeader";

             $db->Query($sqlStat);

             // -------------
             // Einde functie
             // -------------

             return $aantalGemaakt;

         }

         // ========================================================================================
         // Function: Aanmaken zomertornooi mail
         //
         // In:	Mail
         //
         // Uit: Aangemaakt? true/false (evim_vd_versturen_detail Record opgevuld)
         //
         // ========================================================================================

         static function CrtZomertornooiMail($pMail) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             include_once(SX::GetClassPath("personen.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------

             $mail = $pMail;

             $sqlStat = "Select * from evim_vd_versturen_detail where vdId = $mail";
             $db->Query($sqlStat);

             if (! $vdRec = $db->Row())
                 return false;

             $vhId = $vdRec->vdHeader;

             $sqlStat = "Select * from evim_vh_versturen_headers where vhId = $vhId";
             $db->Query($sqlStat);

             if (! $vhRec = $db->Row())
                 return false;

             $zender = $vhRec->vhZender;

             $sqlStat = "Select * from evim_mz_mail_zenders where mzId = $zender";
             $db->Query($sqlStat);

             if (! $mzRec = $db->Row())
                 return false;

             $mailTemplate = $vhRec->vhMail;

             $sqlStat = "Select * from evim_im_info_mail where imId = $mailTemplate";
             $db->Query($sqlStat);

             if (! $imRec = $db->Row())
                 return false;

             $contactPersoon = $vdRec->vdContactPersoon;
             $sqlStat = "Select * from ssp_cc where ccId = $contactPersoon";
             $db->Query($sqlStat);

             if (! $ccRec = $db->Row())
                 return false;

             $club = $ccRec->ccClub;

             $sqlStat = "Select * from ssp_cl where clId = $club";
             $db->Query($sqlStat);

             if (! $clRec = $db->Row())
                 return false;


             // ----------------------
             // Template vars & values
             // ----------------------

             $arr_VARS = array();
             $arr_VALUES = array();

             $arr_VARS[] = "VOORNAAM_NAAM";
             $arr_VALUES[] = $ccRec->ccVoornaam . " " . $ccRec->ccNaam;

             $arr_VARS[] = "VOORNAAM";
             $arr_VALUES[] = $ccRec->ccVoornaam;

             $arr_VARS[] = "CLUB";
             $arr_VALUES[] = $clRec->clNaam;

             $arr_VARS[] = "AANWEZIG_HUN_TORNOOI";
             $arr_VALUES[] = self::GetAanwezigheidHunTornooi($club);

             $mailOnderwerp = $imRec->imOnderwerp;

             $mailOnderwerp = self::FillTemplateVars($mailOnderwerp, $arr_VARS, $arr_VALUES);

             $mailBody = '<html><body>';

             $mailBody .= nl2br($imRec->imTekst);
             if ($imRec->imHandtekeningToevoegen == 1)
                 $mailBody .= "<br/>" . nl2br($mzRec->mzHandtekening);

             $mailBody .= '</body></html>';

             $mailBody = self::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);


             // -------------------------------
             // Update evim_vd_versturen_detail
             // -------------------------------


             $values["vdMailOnderwerp"] = MySQL::SQLValue($mailOnderwerp);
             $values["vdMailBody"] = MySQL::SQLValue($mailBody);

             $values["vdMailTo"] = MySQL::SQLValue($ccRec->ccMail);
             $values["vdMailCC"] = MySQL::SQLValue($vhRec->vhMailCC);
             $values["vdMailBCC"] = MySQL::SQLValue($vhRec->vhMailBCC);

             $db2->UpdateRows("evim_vd_versturen_detail", $values, array("vdId" => $pMail));

             // -------------
             // Einde functie
             // -------------

             return true;


         }

         // ========================================================================================
         // Function: Ophalen aanwezigheid op hun tornooi
         //
         // In:	Club
         //
         // Uit: String
         //
         // ========================================================================================

         static function GetAanwezigheidHunTornooi($pClub){

		    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // ----------------------------------------------
             // Check voorbije tornooien (max. 1 jaar geleden)
             // ----------------------------------------------

            $sqlStat = "Select count(*) as aantal from ssp_cl_et where etClub = $pClub and etDatum >= DATE_SUB(NOW(),INTERVAL 1 YEAR) and etStatus <= ' '";

            $db->Query($sqlStat);

            if (! $etRec = $db->Row())
                return "";

            if ($etRec->aantal <= 0)
                return "";

            $aantal = $etRec->aantal;

            if ($aantal > 1)
                $aanwezigheid = "<b>PS. We waren op jullie voorgaande tornooi(en) met $aantal ploegen aanwezig.</b>";

            if ($aantal == 1)
                $aanwezigheid = "<b>PS. We waren op jullie voorgaande tornooi(en) aanwezig.</b>";

             // ---------------------------
             // Check toekomstige tornooien
             // ---------------------------

             $sqlStat = "Select count(*) as aantal from ssp_cl_et where etClub = $pClub and etDatum > CURDATE() and etStatus <= ' '";
             $db->Query($sqlStat);

             if ($etRec = $db->Row()){

                 $aantal = $etRec->aantal;

                 if ($aantal > 1)
                     $aanwezigheid = "<b>PS. We zijn op jullie toekomstig tornooi met $aantal ploegen ingeschreven.</b>";

                 if ($aantal == 1)
                     $aanwezigheid = "<b>PS. We zijn op jullie toekomstig tornooi ingeschreven.</b>";

             }


            // -------------
            // Einde functie
            // -------------

             return "<br/>$aanwezigheid<br/>";



         }

         // ========================================================================================
         // Function: Get Template Preview HTML
         //
         // In:	Template
         //
         // Uit: HTML string
         //
         // ========================================================================================

         static function GetTemplatePreview($pTemplate) {

		    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            include_once(SX::GetClassPath("_db.class"));

            $imRec = SSP_db::Get_EVIM_imRec($pTemplate);

            return nl2br($imRec->imTekst);

            // -------------
            // Einde functie
            // -------------

         }

         // ========================================================================================
         // Function: Fill Template Variables
         //
         // In:	String
         //		Variable names
         //		Variable values
         //
         // Uit: String
         //
         // ========================================================================================

         static function FillTemplateVars($pString, $pArr_VARS, $pArr_VALUES) {

             $string = $pString;
             // $string = utf8_encode($pString);

             for ($i = 0; $i < count($pArr_VARS); $i++) {

                 $varName = '[' . $pArr_VARS[$i] . ']';
                 // $varName = utf8_encode($varName);

                 $varValue = $pArr_VALUES[$i];
                 // $varValue = utf8_encode($varValue);

                 $string = str_ireplace($varName, $varValue, $string);
             }


             $badchar=array(
                 // control characters
                 chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), chr(9), chr(10),
                 chr(11), chr(12), chr(13), chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20),
                 chr(21), chr(22), chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30),
                 chr(31),
                 // non-printing characters
                 chr(127), chr(160)
             );

             //replace the unwanted chars
             $string = str_replace($badchar, '', $string);

             return $string;

         }





         // -----------
         // EINDE CLASS
         // -----------

 	}      
?>