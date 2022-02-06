<?php 
     class SSP_scanning { // define the class
	 
         // ===================================================================================================
         // Functie: Scan (inlezen) kaart
         //
         // In:	- QRcode van de kaart
         //
         // Return: Kaart-record (null indien ongeldig)
         // ===================================================================================================

         static function ScanKaart($pQRCode) {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             include_once(SX::GetClassPath("_db.class"));
             include_once(SX::GetClassPath("settings.class"));

             // --------------
             // Decode QR-code
             // --------------

             $decoded = json_decode($pQRCode);

             $kaart = $decoded->kk;

             // ------------------------------
             // Kaartcode -> "Nieuwe" lidkaart (sinds seizoen 2019-2020)
             // ------------------------------

             if ($kaart){

                $kaRec = SSP_db::Get_ELA_kaRec($kaart);

                if (! $kaRec)
                    return null;

                if ($kaRec->kaRecStatus == 'A')
                    return $kaRec;

                $persoon = $kaRec->kaPersoon;

             }

             // --------------------------------------------------
             // Geen kaartcode -> "ophalen persoon van oude kaart"
             // --------------------------------------------------

             if (! $kaart)
                 $persoon = $decoded->id;

             if (! $persoon)
                 return null;


             // ---------------------------------------
             // Opzoeken kaartcode op basis van persoon (lidkaart VB is hoogste in waarde, abonnement is laagste in "waarde")
             // ---------------------------------------

             $seizoen = SSP_settings::GetActiefSeizoen();
             $type = "";

             $sqlStat = "Select * from ela_ka_kaarten where kaPersoon = '$persoon' and kaRecStatus = 'A' and (kaSeizoen = '$seizoen' or kaSeizoen = 'Levenslang')";
             $db->Query($sqlStat);

             while ($kaRec = $db->Row()){

                 if ($kaRec->kaType == '*LIDKAART_VB'){

                     $kaRecSelected = $kaRec;
                     break;

                 }

                 if (! $type or ($kaRec->kaType == '*LIDKAART_T')){

                     $type = $kaRec->kaType;
                     $kaRecSelected = $kaRec;
                     continue;

                 }

             }

             // -------------
             // Einde functie
             // -------------

             if ($kaRecSelected)
                 return $kaRecSelected;
             else
                 return null;

         }

         // ===================================================================================================
         // Functie: Handle scan kaart  voor bepaald event
         //
         // In:	- Kaart
         //		- Event
         //
         // Uit:   - Eventuele foutboodschap
         //        - Switch "reeds gebruikt" ? (true/false)
         //
         // Return:	Valid? true/false
         //
         // ===================================================================================================

         static function HdlScanKaart($pKaart, $pEvent,  &$pBoodschap, &$pReedsGebruikt) {

             include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object
             include_once(SX::GetClassPath("_db.class"));

             // -------------------------
             // Init uitgaande parameters
             // -------------------------

             $pBoodschap = "";
             $pReedsGebruikt = false;

             // -------------
             // Ophalen event
             // -------------

             $query = "Select * from ssp_scanning_events where seId = $pEvent";

             if (! $db->Query($query)) {
                 $pBoodschap = "Onverwachte fout - Scanning event niet in database";
                 return false;
             }

             $seRec = $db->Row();

             if (! $seRec){
                 $pBoodschap = "Onverwachte fout - Scanning event niet in database";
                 return false;
             }

             // ------------------------
             // ophalen & aftesten kaart
             // ------------------------

             $kaRec = SSP_db::Get_ELA_kaRec($pKaart);

             if (! $kaRec) {
                 $pBoodschap = "Onverwachte fout - kaart niet in database";
                 return false;
             }

             if ($kaRec->kaRecStatus != 'A') {
                 $pBoodschap = "KAART NIET MEER GELDIG (historiek)";
                 return false;
             }

             $persoon = $kaRec->kaPersoon;

             $adRec = SSP_db::Get_SSP_adRec($persoon);

             if (! $adRec) {
                 $pBoodschap = "Onverwachte fout - persoon niet in database";
                 return false;
             }

             if ($adRec->adRecStatus != 'A') {
                 $pBoodschap = "KAART NIET MEER GELDIG (Persoon in historiek)";
                 return false;
             }


             $pBoodschap  = $adRec->adVoornaamNaam;

             if ($kaRec->kaNaam)
                 $pBoodschap  = $kaRec->kaVoornaam . " " . $kaRec->kaNaam . '*';

             $ksRec = SSP_db::Get_ELA_ksRec($kaRec->kaType, $kaRec->kaSubtype);

             if ($kaRec->kaType == '*ABONNEMENT_VB') {

                 $titel = $ksRec->ksKaarttitel;

                 $pBoodschap = "$pBoodschap - Abonnement $titel";


             }


             if ($kaRec->kaType == '*LIDKAART_VB') {

                 if ($ksRec) {

                     $titel = $ksRec->ksKaarttitel;

                     if ($kaRec->kaSubtype == '*SPELER') {

                         $cat = $adRec->adVoetbalCat;

                         if ($cat)
                            $titel = "$titel - $cat";

                     }

                     $pBoodschap = "$pBoodschap ($titel)";

                 }

             }

			// ---------
			// WEDSTRIJD
			// ---------


			if (($seRec->seJeugdWedstrijden == 1) or ($seRec->seSeniorWedstrijden == 1)) {

				$test = 'NOK';
				
				if ($seRec->seJeugdWedstrijden == 1 and $kaRec->kaWedstrijdenJeugd == 1) {
					$test = 'OK';
				}
					
				if ($seRec->seSeniorWedstrijden == 1 and $kaRec->kaWedstrijdenSeniors == 1) {
					$test = 'OK';
				}
		
		
				if ($test == "NOK") {

					if ($kaRec->kaWedstrijdenSeniors != 1)
				        $pBoodschap = "Kaart niet geldig voor SENIOR-WEDSTRIJDEN";

                    if ($kaRec->kaWedstrijdenJeugd != 1)
                        $pBoodschap = "Kaart niet geldig voor JEUGDWEDSTRIJD";


					return false;
				}


				$sqlStat = "Update ela_ka_kaarten set kaLaatsteScan = now(),kaPrinted = 1, kaOntvangen = 1,  kaAantalScan = kaAantalScan + 1 where kaKaartCode = '$pKaart' ";
				$db->Query($sqlStat);

				return true;
			
			}
			

			// -------------------
            // Aftesten eet-events
            // -------------------


            if ($seRec->seEetEvent == 1) {

                if ($kaRec->kaEetEvents != 1) {
                    $pBoodschap = "Deze kaart is niet geldig voor EET-events";
                    return false;
                }


                // Check reeds gebruikt...
                $persoon = $kaRec->kaPersoon;

                $sqlStat = "select count(*) as aantal from ssp_scanning_detail inner join ela_ka_kaarten on kaKaartCode = scKaartCode where kaPersoon = '$persoon' and scEvent = $pEvent";

                $db->Query($sqlStat);
                $scRec = $db->Row();

                if ($scRec->aantal >= 1) {

                    $pBoodschap = "Kaart reeds gebruikt voor dit eet-event";
                    $pReedsGebruikt = true;
                    return false;

                }


                $sqlStat = "Update ela_ka_kaarten set kaLaatsteScan = now(), kaPrinted = 1, kaOntvangen = 1, kaAantalScan = kaAantalScan + 1 where kaKaartCode = '$pKaart' ";
                $db->Query($sqlStat);

                return true;

            }

             $sqlStat = "Update ela_ka_kaarten set kaLaatsteScan = now(), kaPrinted = 1, kaOntvangen = 1, kaAantalScan = kaAantalScan + 1 where kaKaartCode = '$pKaart' ";
             $db->Query($sqlStat);

            // -------------
            // Einde functie
            // -------------

             return true;


				
		}
			

		// -----------
		// Einde CLASS
        //------------

    }
       
?>