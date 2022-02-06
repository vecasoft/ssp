<?php 
     class SSP_evra { // define the class
	 
 		// ===================================================================================================
		// Functie: Aanmaken reservaties op basis van reservatie-reeks
		//
		// In:	- Reeks
         //
		// Out: Niets
		//
		// ===================================================================================================
         
        static function CrtReeksReservaties($pReeks) {
 
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
			d1
			$query = "Select * from rva_rr_reservatie_reeksen where rrId = $pReeks";
			
			if (! $db->Query($query))
				return;
				
			if (! $rrRec = $db->Row())
				return;

			
			// ---------
			// Wekelijks
			// ---------
			
			If ($rrRec->rrFrequentie == '*WEEK')
				$data = self::GetDagInDatumReeks($rrRec->rrReeksDatumStart, $rrRec->rrReeksDatumEinde, $rrRec->rrDag);


			self::CrtReeksReservatie($rrRec, $data[0]);
			
			return;
				
		}

		// ===================================================================================================
		// Functie: Aanmaken reservatie op basis van reservatie-reeks en datum
		//
		// In:	- ReservatieReeks-Record
		//		- Datum
		//
		// Out: Niets
		//
		// ===================================================================================================

        static function CrtReeksReservatie($p_rrRec, $pDatum) {

			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object

            $values = array();

			$values["rsLocatie"] = MySQL::SQLValue($p_rrRec->rrLocatie);
			$values["rsKlant"] = MySQL::SQLValue($p_rrRec->rrKlant,SQLVALUE_NUMBER);		
			$values["rsTitel"] = MySQL::SQLValue($p_rrRec->rrReeksNaam);			

			$orderNummer = $db->InsertRow("evra_rs_reservaties", $values);
			
			$seizoen = self::GetHuidigSeizoen();
					
			$sqlStat = "Update eba_oh_order_headers set ohDatumCreatie = now(), ohDatumUpdate = now(), ohSeizoen = '$seizoen', ohOrderDatum = now() where ohOrdernummer = $orderNummer";


		}
 		
 		// ===================================================================================================
		// Functie: Get mondays in string
		//
		// In:	- Datum van
		//		- Datum tot
		//		- Dag (*MA .. *ZO)
		//
		// Out: - Array met data
		//
		// ===================================================================================================
		
		static function GetDagInDatumReeks($pDatumVanString, $pDatumTotString, $pDag) {

			$dateFrom = new \DateTime($pDatumVanString);
			$dateTo = new \DateTime($pDatumTotString);
			$dates = [];

			if ($dateFrom > $dateTo) {
				return $dates;
			}
			
			$dagNummer = 1;
			If ($pDag == '*DI')
				$dagNummer = 2;			
			If ($pDag == '*WO')
				$dagNummer = 3;				
			If ($pDag == '*DO')
				$dagNummer = 4;				
			If ($pDag == '*VR')
				$dagNummer = 5;				
			If ($pDag == '*ZA')
				$dagNummer = 6;	
			If ($pDag == '*ZO')
				$dagNummer = 7;	
			
			if ($dagNummer != $dateFrom->format('N')) {
				
				if ($dagNummer == 1)
					$dateFrom->modify('next monday');
				elseif ($dagNummer == 2)
					$dateFrom->modify('next tuesday');	
				elseif ($dagNummer == 3)
					$dateFrom->modify('next wednesday');
				elseif ($dagNummer == 4)
					$dateFrom->modify('next thursday');	
				elseif ($dagNummer == 5)
					$dateFrom->modify('next friday');
				elseif ($dagNummer == 6)
					$dateFrom->modify('next saturday');	
				else
					$dateFrom->modify('next sunday');					
			}

			while ($dateFrom <= $dateTo) {
				$dates[] = $dateFrom->format('Y-m-d');
				$dateFrom->modify('+1 week');
			}

			return $dates;
		}

         // ===================================================================================================
         // Functie: Update "interne" klant
         //
         // In:	persoon
         //
         // ===================================================================================================

         static function UpdInterneKlant($pPersoon) {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";
             $db->Query($sqlStat);

             if (! $adRec = $db->Row())
                 return;

             $sqlStat = "Select * from evra_kl_klanten where klCode = '$pPersoon'";
             $db->Query($sqlStat);
             if (! $klRec = $db->Row())
                 return;

             $klId = $klRec->klId;

             $values["klNaam"] = MySQL::SQLValue($adRec->adNaam);
             $values["klVoornaam"] = MySQL::SQLValue($adRec->adVoornaam);
             $values["klAdres"] = MySQL::SQLValue($adRec->adAdres1);
             $values["klPostnr"] = MySQL::SQLValue($adRec->adPostnr);
             $values["klGemeente"] = MySQL::SQLValue($adRec->adGemeente);
             $values["klLand"] = MySQL::SQLValue($adRec->adLand);

             //$values["klTel"] = MySQL::SQLValue($adRec->adTel);
             //$values["klMail"] = MySQL::SQLValue($adRec->adMail);

             $where["klId"] = MySQL::SQLValue($klId, MySQL::SQLVALUE_NUMBER);

             $db->UpdateRows("evra_kl_klanten", $values, $where);


         }

    }
       
?>