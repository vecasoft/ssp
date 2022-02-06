<?php 

class SSP_epra { // define the class

	// ========================================================================================
	// Get rhRec	
	//
	// In:	- RekeningID
	//
	// Return: rhRec
	// ========================================================================================
	
	static function GetRhRec($pRekeningId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from epra_rh_rekening_headers where rhId = $pRekeningId";
		
		if (!$db->Query($sqlStat)) 
			return null;

		if (! $rhRec = $db->Row())
			return null;
		
		$db->close();			 
		return $rhRec;
	
	} 

	// ========================================================================================
	// Get rdRed	
	//
	// In:	- Rekening-detaiID
	//
	// Return: rhRec
	// ========================================================================================
	
	static function GetRdRec($RekeningDetailId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from epra_rd_rekening_detail where rdId = $RekeningDetailId";
		
		if (!$db->Query($sqlStat)) 
			return null;

		if (! $rdRec = $db->Row())
			return null;
		
		$db->close();			 
		return $rdRec;
	
	} 
	
	// ========================================================================================
	// Get raRec	
	//
	// In:	- Afspraak ID
	//
	// Return: raRec
	// ========================================================================================
	
	static function GetRaRec($pAfspraakId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from epra_ra_rekening_afspraken where raId = $pAfspraakId";
		
		if (!$db->Query($sqlStat)) 
			return null;

		if (! $raRec = $db->Row())
			return null;
					 
		$db->close();
		return $raRec;
	
	} 
	
	// ========================================================================================
	// Get adRec	
	//
	// In:	- Persoon-code
	//
	// Return: adRec
	// ========================================================================================
	
	static function GetAdRec($pPersoon) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";
		
		if (!$db->Query($sqlStat)) 
			return null;

		if (! $adRec = $db->Row())
			return null;
					 
		$db->close();
		return $adRec;
	
	}
	// ========================================================================================
	// Get caRec	
	//
	// In:	- Categorie
	//		- Seizoen (*HUIDIG)
	//
	// Return: caRec
	// ========================================================================================
	
	static function GetCaRec($pVergoedingCat, $pSeizoen = '*HUIDIG') {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from epra_ca_categorie_afspraken inner join ssp_vs on vsCode = caSeizoen where caCategorie = '$pVergoedingCat'";
		
		if (!$db->Query($sqlStat)) 
			return null;
		
		$caRec = null;
		
		while ($caRec2 = $db->Row()){
			
			if ($caRec2->vsHuidigSeizoen == 1 and $pSeizoen == '*HUIDIG') {
				
				$caRec = $caRec2;
				break;
								
			}
			
			if ($pSeizoen <> '*HUIDIG' and $caRec2->caSeizoen == $pSeizoen){
				
				$caRec = $caRec2;
				break;				
				
			}
			
			
		}
		
		$db->close();
		return $caRec;
	
	}

    // ========================================================================================
    //  Actief seizoen EPRA
    //
    // Return: adRec
    // ========================================================================================

    static function GetActiefSeizoenEPRA() {

	    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("settings.class"));


        //$sqlStat = "Select max(raSeizoen) as seizoen from epra_ra_rekening_afspraken";
        //$db->Query($sqlStat);

        //if ($raRec = $db->Row())
        //    $seizoen = $raRec->seizoen;
        //else
            $seizoen = SSP_settings::GetActiefSeizoen();

        // -------------
        // Einde functie
        // -------------

        return $seizoen;

    }

    // ========================================================================================
	// Moet wedstrijd vergoed worden?	
	//
	// In:	- Wedstrijd-ID
	//
	// Return: adRec
	// ========================================================================================
	
	static function ChkWedstrijdVergoeden($pWedstrijdId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from ssp_vw where vwId = $pWedstrijdId";
		
		if (!$db->Query($sqlStat)) 
			return false;

		if (! $vwRec = $db->Row())
			return false;
		
		$returnVal = false;
		
		if ($vwRec->vwVergoeden == 'J')
			$returnVal = true;
		
		
		if ($vwRec->vwVergoeden == 'T'){
			
			$wedstrijdType = $vwRec->vwType;
			
			$sqlStat = "Select * from ssp_wt where wtCode = '$wedstrijdType'";
			$db->Query($sqlStat);
			$wtRec = $db->Row();
			
			if ($wtRec->wtVergoeden == 'J')
				$returnVal = true;
		
		}
		
					 
		$db->close();
		return $returnVal;
	
	}	
	// ========================================================================================
	// Get Table Name
	//
	// In:	- Table
	//		- Code
	//
	// Return: Name
	// ========================================================================================
	
	static function GetTableName($pTable, $pCode) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from sx_ta_tables where taTable = '$pTable' and taCode = '$pCode'";
		
		$name = null;
		
		if (!$db->Query($sqlStat)) 
			$name = null;

		if ($taRec = $db->Row())
			$name = $taRec->taName;
					 
		$db->close();
		return $name;
	
	} 		

	// ========================================================================================
	// Get Ploeg naam
	//
	// In:	- Ploeg
	//
	// Return: Name
	// ========================================================================================
	
	static function GetPloegNaam($pPloegId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from ssp_vp where vpId = $pPloegId";
		
		$naam = null;
		
		if (!$db->Query($sqlStat)) 
			$naam = null;

		if ($vpRec = $db->Row())
			$naam = $vpRec->vpNaam;
					 
		$db->close();
		return $naam;
	
	} 

	// ========================================================================================
	// "Save" bankrekening (in ssp_ad)
	//
	// In:	Persoon
	//		Bankrekening	
	//
	// Return: None
	// ========================================================================================
	
	static function SaveBankrekening($pPersoon, $pBankrekening) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		  
		if ($pPersoon > ' ' and $pBankrekening > ' ') {
			
			$sqlStat = "Update ssp_ad set adBankRekening = '$pBankrekening' where adCode = '$pPersoon'";
			$db->Query($sqlStat);
		}
		
	} 	

	// ========================================================================================
	// Create new "Run"
	//
	// In: niets
	//
	// Return: RundID
	// ========================================================================================
	
	static function CrtRun() {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$curDateTime =	date('Y-m-d H:i:s');

		$values["prDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );	
			
		$id = $db->InsertRow("epra_pr_prestatie_runs", $values);
		
		return $id;
	
	} 	
	
	// ========================================================================================
	// Check Rekening Detail/ERA
	//
	// In: 	Rekening
	//		ERA-link
	//		Bedrag
	//		Aantal Trainers
	//		Vergoedings %
	//		Omschrijving
	//		Run nbr
	//		Datum
    //      AfspraakID
    //      Wedstrijd (optioneel)
    //      Diverse Prestatie (optioneel)
	//
	// Return: true/false
	// ========================================================================================
	
	static function ChkRekeningDetail($pRekeningId, $pERA, $pBedrag, $pAantalTrainers, $pvergoedingPerc, $pOmschrijving, $pRun, $pDatum, $pAfspraakId, $pWedstrijd=0, $pDiversePrestatie = 0) {

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = null;

        if ($pERA)
		    $sqlStat = "Select * from epra_rd_rekening_detail where rdRekening= $pRekeningId and rdERA = $pERA";
        elseif ($pWedstrijd)
            $sqlStat = "Select * from epra_rd_rekening_detail where rdRekening= $pRekeningId and rdWedstrijd = $pWedstrijd";
        elseif ($pDiversePrestatie)
            $sqlStat = "Select * from epra_rd_rekening_detail where rdRekening= $pRekeningId and rdDiversePrestatie = $pDiversePrestatie";

        if (! $sqlStat)
            return false;

		$db->Query($sqlStat);		
		
		if (! $rdRec = $db->Row()) {
			$db->close();
			return false;
		}

		// ------
		// Update
        // ------

		$values = array();
		$where = array();

		$values["rdRun"] =  MySQL::SQLValue($pRun, MySQL::SQLVALUE_NUMBER);
        $values["rdDatum"] =  MySQL::SQLValue($pDatum, MySQL::SQLVALUE_DATE);
        $values["rdAantalTrainers"] =  MySQL::SQLValue($pAantalTrainers, MySQL::SQLVALUE_NUMBER);
        $values["rdVergoedingPerc"] =  MySQL::SQLValue($pvergoedingPerc, MySQL::SQLVALUE_NUMBER);

        if ($rdRec->rdBedragManueel <> 1)
            $values["rdOmschrijving"] =  MySQL::SQLValue($pOmschrijving);

        $values["rdAfspraak"] =  MySQL::SQLValue($pAfspraakId, MySQL::SQLVALUE_NUMBER);
        // $values["rdWedstrijd"] =  MySQL::SQLValue($pWedstrijd, MySQL::SQLVALUE_NUMBER);

        if ($rdRec->rdBedragManueel <> 1)
            $values["rdBedrag"] =  MySQL::SQLValue($pBedrag, MySQL::SQLVALUE_NUMBER);

		$where["rdRekening"] =  MySQL::SQLValue($pRekeningId, MySQL::SQLVALUE_NUMBER);

		if ($pERA)
            $where["rdERA"] =  MySQL::SQLValue($pERA, MySQL::SQLVALUE_NUMBER);
		if ($pWedstrijd)
            $where["rdWedstrijd"] =  MySQL::SQLValue($pWedstrijd, MySQL::SQLVALUE_NUMBER);
        if ($pDiversePrestatie)
            $where["rdDiversePrestatie"] =  MySQL::SQLValue($pDiversePrestatie, MySQL::SQLVALUE_NUMBER);

		$db->UpdateRows("epra_rd_rekening_detail", $values, $where);

		// -------------
		// Einde functie
		// -------------

		$db->close();
		return true;
	
	}
	
		
	// ========================================================================================
	// Fill rekening-detail
	//
	// In: Rekening (Optioneel)
	//
	// Return: RundID
	// ========================================================================================
	
	static function CrtRekeningDetail($pRekening = 0){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("wedstrijden.class"));
        include_once(SX::GetClassPath("settings.class"));

        $runId = self::CrtRun();
        $curDateTime = date('Y-m-d H:i:s');

        $actiefSeizoen = SSP_settings::GetActiefSeizoen();

        // ----------------------
        // Fill from Betaalschema
        // ----------------------

        $prestatiebron = '*BETAALSCHEMA';
        $prestatiebron2 = '*BETAALSCHEMA_DAG';

        $sqlStat = "Select * from epra_ra_rekening_afspraken where raRecStatus = 'A' and ($pRekening = 0 or $pRekening = raRekening) and (raPrestatiebron = '$prestatiebron' or raPrestatiebron = '$prestatiebron2')";

        $db->Query($sqlStat);

        while ($raRec = $db->Row()) {

            $rekeningId = $raRec->raRekening;
            $afspraakId = $raRec->raId;

            $seizoen = $raRec->raSeizoen;

            $sqlStat = "Select * from epra_bs_betaalschema where bsAfspraak = $afspraakId and date(bsDatum) <= current_date and bsStatus = '*OPEN' and bsRecStatus = 'A' ";


            $db2->Query($sqlStat);

            while ($bsRec = $db2->Row()) {

                $betaalschemaId = $bsRec->bsId;
                $omschrijving = 'Periodieke vergoeding';

                $sqlStat = "Select * from epra_rh_rekening_headers where rhId = $rekeningId";
                $db3->Query($sqlStat);

                if ($rhRec = $db3->Row()) {

                    $hoedanigheid = self::GetTableName('EPRA_HOEDANIGHEID', $rhRec->rhHoedanigheid);

                    $omschrijving .= " ($hoedanigheid)";


                }

                if ($bsRec->bsOmschrijvingPrestatie)
                    $omschrijving = $bsRec->bsOmschrijvingPrestatie;


                // -----------------------------
                // Create rekening-detail-record
                // -----------------------------

                if (!$seizoen)
                    $seizoen = $actiefSeizoen;

                $values["rdRun"] = MySQL::SQLValue($runId, MySQL::SQLVALUE_NUMBER);
                $values["rdRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);
                $values["rdAfspraak"] = MySQL::SQLValue($afspraakId, MySQL::SQLVALUE_NUMBER);
                $values["rdDatum"] = MySQL::SQLValue($bsRec->bsDatum, MySQL::SQLVALUE_DATE);
                $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
                $values["rdTransactieCode"] = MySQL::SQLValue('*BETAALSCHEMA');
                $values["rdPlusMin"] = MySQL::SQLValue('+');
                $values["rdBedrag"] = MySQL::SQLValue($bsRec->bsBedrag, MySQL::SQLVALUE_NUMBER);
                $values["rdOmschrijving"] = MySQL::SQLValue($omschrijving);
                $values["rdBetaalschema"] = MySQL::SQLValue($bsRec->bsId, MySQL::SQLVALUE_NUMBER);
                $values["rdUserCreatie"] = MySQL::SQLValue('*RUN');
                $values["rdUserUpdate"] = MySQL::SQLValue('*RUN');

                $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db3->InsertRow("epra_rd_rekening_detail", $values);

                self::CalcRekeningSaldo($rekeningId);

                // --------------------------
                // Update betaalschema-record
                // --------------------------

                if ($id > 0) {

                    $sqlStat = "Update epra_bs_betaalschema set bsStatus = '*GEBOEKT' where bsId = $betaalschemaId";
                    $db3->Query($sqlStat);

                }

            }

        }

        // -----------------------------------
        // Fill from ERA-prestaties (TRAINERS)
        // -----------------------------------

        $prestatiebron = '*ERA';
        $transactieCode = '*ERA_PRESTATIE';

        $sqlStat = "Select * from epra_ra_rekening_afspraken inner join epra_rh_rekening_headers on rhId = raRekening where raRecStatus = 'A' and ($pRekening = 0 or $pRekening = raRekening) and (raSeizoen = '$actiefSeizoen' or raSeizoen <= ' ' or raSeizoen is null) and raPrestatiebron = '$prestatiebron' order by raDatumVan";

        $db->Query($sqlStat);

        while ($raRec = $db->Row()) {

            $persoon = $raRec->rhPersoon;
            $rekeningId = $raRec->rhId;
            $afspraakId = $raRec->raId;
            $seizoen = $raRec->raSeizoen;

            $sqlStat = "Select * from ssp_twbs_aw inner join ssp_twbs_tw on twId = awTW and twDatum <= DATE(NOW()) inner join epra_ra_rekening_afspraken on raId = $afspraakId WHERE awSpeler = '$persoon' and awType = 'Trainer' AND awAanwezig = 1 and twDatum >= raDatumVan and twDatum <= raDatumTot";

            $db2->Query($sqlStat);

            while ($awRec = $db2->Row()) {

                $bedrag = $raRec->raPrestatieBedrag;
                $vergoedingPerc = 100;

                if ($awRec->twAantalTrainers > 1 and $awRec->awHulpTrainer <> 1) {

                    $meerTrainersToegelaten = self::ChkMeerTrainersToegelaten($awRec->twPloeg, $awRec->twDatum);

                    if ($meerTrainersToegelaten == false) {
                        $vergoedingPerc = 100 / $awRec->twAantalTrainers;
                        $bedrag *= ($vergoedingPerc / 100);
                    }

                }


                $omschrijving = "ERA Prestatie";

                if ($awRec->twType) {
                    $omschrijvingType = self::GetTableName("TWBS_TYPE", $awRec->twType);

                    if ($omschrijvingType)
                        $omschrijving = $omschrijvingType;
                }

                $ploegNaam = self::GetPloegNaam($awRec->twPloeg);

                if ($ploegNaam)
                    $omschrijving .= " ($ploegNaam)";

                if ($vergoedingPerc < 100) {

                    $vergoedingPercEdit = number_format($vergoedingPerc, 2, ',', '.');
                    $omschrijving .= "  - $awRec->twAantalTrainers trainers, vergoed aan $vergoedingPercEdit %";
                }

                if (self::ChkRekeningDetail($rekeningId, $awRec->awTW, $bedrag, $awRec->twAantalTrainers, $vergoedingPerc, $omschrijving, $runId, $awRec->twDatum, $afspraakId) == true)
                    continue;

                // Bepaal seizoen
                if (!$seizoen) {

                    $ploeg = $awRec->twPloeg;
                    $vpRec = SSP_db::Get_SSP_vpRec($ploeg);

                    if ($vpRec)
                        $seizoen = $vpRec->vpSeizoen;
                    else
                        $seizoen = $actiefSeizoen;
                }

                $values["rdRun"] = MySQL::SQLValue($runId, MySQL::SQLVALUE_NUMBER);
                $values["rdRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);
                $values["rdAfspraak"] = MySQL::SQLValue($afspraakId, MySQL::SQLVALUE_NUMBER);
                $values["rdDatum"] = MySQL::SQLValue($awRec->twDatum, MySQL::SQLVALUE_DATE);
                $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
                $values["rdTransactieCode"] = MySQL::SQLValue($transactieCode);
                $values["rdAantalTrainers"] = MySQL::SQLValue($awRec->twAantalTrainers, MySQL::SQLVALUE_NUMBER);
                $values["rdVergoedingPerc"] = MySQL::SQLValue($vergoedingPerc, MySQL::SQLVALUE_NUMBER);
                $values["rdPlusMin"] = MySQL::SQLValue('+');
                $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
                $values["rdOmschrijving"] = MySQL::SQLValue($omschrijving);
                $values["rdERA"] = MySQL::SQLValue($awRec->awTW, MySQL::SQLVALUE_NUMBER);
                $values["rdUserCreatie"] = MySQL::SQLValue('*RUN');
                $values["rdUserUpdate"] = MySQL::SQLValue('*RUN');

                $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db3->InsertRow("epra_rd_rekening_detail", $values);

            }

            // ------------------------------------------------------------------------------
            // Wissen prestaties onder deze afspraak die blijkbaar niet meer in ERA voorkomen
            // ------------------------------------------------------------------------------

            $sqlStat = "Delete From epra_rd_rekening_detail where rdRekening = $rekeningId and rdRun < $runId and rdAfspraak = $afspraakId and rdTransactieCode = '$transactieCode'";
            $db3->Query($sqlStat);

            // ---------------------------
            // Herberekenen saldo rekening
            // ---------------------------

            self::CalcRekeningSaldo($rekeningId);


        }

        // -----------------------------------
        // Fill from ERA-prestaties (SPELERS)
        // -----------------------------------

        $prestatiebron = '*ERA_SPELER';
        $transactieCode = '*ERA_PRESTATIE';

        $sqlStat = "Select * from epra_ra_rekening_afspraken inner join epra_rh_rekening_headers on rhId = raRekening where raRecStatus = 'A' and ($pRekening = 0 or $pRekening = raRekening) and (raSeizoen = '$actiefSeizoen' or raSeizoen <= ' ' or raSeizoen is null) and raPrestatiebron = '$prestatiebron' order by raDatumVan";

        $db->Query($sqlStat);

        while ($raRec = $db->Row()) {

            $persoon = $raRec->rhPersoon;
            $rekeningId = $raRec->rhId;
            $afspraakId = $raRec->raId;
            $hoedanigheid = $raRec->rhHoedanigheid;
            $seizoen = $raRec->raSeizoen;

            $sqlStat = "Select * from ssp_twbs_aw "
                . " inner join ssp_twbs_tw on twId = awTW and twDatum <= DATE(NOW()) "
                . " Inner join ssp_vp on vpId = twPloeg and vpVoetbalCat = 'SEN' "
                . " Inner join ssp_ad on adCode = awSpeler "
                . " inner join epra_ra_rekening_afspraken on raId = $afspraakId "
                . " Where awSpeler = '$persoon' "
                . "  and awType = 'Speler' "
                . "  and awAanwezig = 1 "
                . "  and twType <> 'TRAINING' "
                . "  and twDatum >= raDatumVan "
                . "  and twDatum <= raDatumTot";

            $db2->Query($sqlStat);

            while ($awRec = $db2->Row()) {

                if ($awRec->twUitslag != 'W' and $awRec->twUitslag != 'G' and $awRec->twUitslag != 'V')
                    continue;

                if ($awRec->vpIsEerstePloeg <> 1 and $awRec->vpIsTweedePloeg <> 1)
                    continue;

                if ($hoedanigheid == '*SPELER_1' and $awRec->vpIsEerstePloeg <> 1)
                    continue;

                if ($hoedanigheid == '*SPELER_2' and $awRec->vpIsTweedePloeg <> 1)
                    continue;


                $bedrag = 0;

                if ($awRec->twUitslag == 'W')
                    $bedrag = $raRec->raPremieWinst;
                if ($awRec->twUitslag == 'G')
                    $bedrag = $raRec->raPremieGelijk;

                if ($bedrag <= 0)
                    continue;

                // -----------------
                // Bepalen wedstrijd
                // -----------------

                $vwRec = SSP_wedstrijden::GetVwRecBasedOnPloegDatum($awRec->twPloeg, $awRec->twDatum);

                if ($vwRec == null)
                    continue;

                $teVergoeden = self::ChkWedstrijdVergoeden($vwRec->vwId);

                if ($teVergoeden == false)
                    continue;

                $vergoedingPerc = 100;

                $omschrijving = "Wedstrijd-premie";

                if ($awRec->twType) {
                    $omschrijvingType = self::GetTableName("TWBS_TYPE", $awRec->twType);

                    if ($omschrijvingType)
                        $omschrijving = $omschrijvingType;
                }

                $ploegNaam = self::GetPloegNaam($awRec->twPloeg);
                $tegenstander = $vwRec->vwTegenstander;

                if ($vwRec->vwUitThuis == 'T')
                    $omschrijving .= " ($ploegNaam - $tegenstander)";
                else
                    $omschrijving .= " ($tegenstander - $ploegNaam)";

                if ($awRec->twUitslag == 'W')
                    $omschrijving .= " WINST";

                if ($awRec->twUitslag == 'G')
                    $omschrijving .= " GELIJK";

                if ($awRec->twUitslag == 'V')
                    $omschrijving .= " VERLIES";

                if (self::ChkRekeningDetail($rekeningId, $awRec->awTW, $bedrag, $awRec->twAantalTrainers, $vergoedingPerc, $omschrijving, $runId, $awRec->twDatum, $afspraakId) == true)
                    continue;

                // Bepaal seizoen
                if (!$seizoen) {

                    $ploeg = $awRec->twPloeg;
                    $vpRec = SSP_db::Get_SSP_vpRec($ploeg);

                    if ($vpRec)
                        $seizoen = $vpRec->vpSeizoen;
                    else
                        $seizoen = $actiefSeizoen;
                }

                $values["rdRun"] = MySQL::SQLValue($runId, MySQL::SQLVALUE_NUMBER);
                $values["rdRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);
                $values["rdAfspraak"] = MySQL::SQLValue($afspraakId, MySQL::SQLVALUE_NUMBER);
                $values["rdDatum"] = MySQL::SQLValue($awRec->twDatum, MySQL::SQLVALUE_DATE);
                $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
                $values["rdTransactieCode"] = MySQL::SQLValue($transactieCode);
                $values["rdAantalTrainers"] = MySQL::SQLValue($awRec->twAantalTrainers, MySQL::SQLVALUE_NUMBER);
                $values["rdVergoedingPerc"] = MySQL::SQLValue($vergoedingPerc, MySQL::SQLVALUE_NUMBER);
                $values["rdPlusMin"] = MySQL::SQLValue('+');
                $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
                $values["rdOmschrijving"] = MySQL::SQLValue($omschrijving);
                $values["rdERA"] = MySQL::SQLValue($awRec->awTW, MySQL::SQLVALUE_NUMBER);
                $values["rdUserCreatie"] = MySQL::SQLValue('*RUN');
                $values["rdUserUpdate"] = MySQL::SQLValue('*RUN');

                $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db3->InsertRow("epra_rd_rekening_detail", $values);
            }

            // ------------------------------------------------------------------------------
            // Wissen prestaties onder deze afspraak die blijkbaar niet meer in ERA voorkomen
            // ------------------------------------------------------------------------------

            $sqlStat = "Delete From epra_rd_rekening_detail where rdRekening = $rekeningId and rdRun < $runId and rdAfspraak = $afspraakId and rdTransactieCode = '$transactieCode'";
            $db3->Query($sqlStat);

            // ---------------------------
            // Herberekenen saldo rekening
            // ---------------------------

            self::CalcRekeningSaldo($rekeningId);


        }

        // ------------------------------------
        // Fill from SCHEIDSRECHTERS-prestaties
        // ------------------------------------

        $prestatiebron = '*SCHEIDSRECHTER';
        $transactieCode = '*SCHEIDSRECHTER';

        $sqlStat = "Select * from epra_ra_rekening_afspraken inner join epra_rh_rekening_headers on rhId = raRekening where raRecStatus = 'A'and ($pRekening = 0 or $pRekening = raRekening) and (raSeizoen = '$actiefSeizoen' or raSeizoen <= ' ' or raSeizoen is null) and raPrestatiebron = '$prestatiebron' order by raDatumVan";

        $db->Query($sqlStat);

        while ($raRec = $db->Row()){

            $persoon = $raRec->rhPersoon;
            $rekeningId = $raRec->rhId;
            $afspraakId = $raRec->raId;
            $hoedanigheid = $raRec->rhHoedanigheid;
            $seizoen = $raRec->raSeizoen;

            $sqlStat = "Select * from ssp_vw inner join epra_ra_rekening_afspraken on raId = $afspraakId where vwScheidsrechter = '$persoon' and vwDatum >= raDatumVan and vwDatum <= raDatumTot";

            $db2->Query($sqlStat);

            while ($vwRec = $db2->Row()){

                $bedrag = $raRec->raPrestatieBedrag;

                if ($bedrag <= 0)
                    continue;

                $omschrijving = "Scheidsrechter";

                $ploegNaam = self::GetPloegNaam($vwRec->vwPloeg);
                $tegenstander = $vwRec->vwTegenstander;

                if ($vwRec->vwUitThuis == 'T')
                    $omschrijving .= " ($ploegNaam - $tegenstander)";
                else
                    $omschrijving .= " ($tegenstander - $ploegNaam)";


                if (self::ChkRekeningDetail($rekeningId, 0, $bedrag, 1, 100, $omschrijving, $runId, $vwRec->vwDatum, $afspraakId, $vwRec->vwId) == true)
                    continue;

                // Bepaal seizoen
                $seizoen = $actiefSeizoen;

                $values["rdRun"] = MySQL::SQLValue($runId, MySQL::SQLVALUE_NUMBER);
                $values["rdRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);
                $values["rdAfspraak"] = MySQL::SQLValue($afspraakId, MySQL::SQLVALUE_NUMBER);
                $values["rdDatum"] = MySQL::SQLValue($vwRec->vwDatum, MySQL::SQLVALUE_DATE);
                $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
                $values["rdTransactieCode"] = MySQL::SQLValue($transactieCode);
                $values["rdAantalTrainers"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
                $values["rdVergoedingPerc"] = MySQL::SQLValue(100, MySQL::SQLVALUE_NUMBER);
                $values["rdPlusMin"] = MySQL::SQLValue('+');
                $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
                $values["rdOmschrijving"] = MySQL::SQLValue($omschrijving);
                $values["rdERA"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
                $values["rdWedstrijd"] = MySQL::SQLValue($vwRec->vwId, MySQL::SQLVALUE_NUMBER);
                $values["rdUserCreatie"] = MySQL::SQLValue('*RUN');
                $values["rdUserUpdate"] = MySQL::SQLValue('*RUN');

                $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
                $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );

                $id = $db3->InsertRow("epra_rd_rekening_detail", $values);
            }

            // ------------------------------------------------------------------------------
            // Wissen prestaties onder deze afspraak die blijkbaar niet meer in ERA voorkomen
            // ------------------------------------------------------------------------------

            $sqlStat = "Delete From epra_rd_rekening_detail where rdRekening = $rekeningId and rdRun < $runId and rdAfspraak = $afspraakId and rdTransactieCode = '$transactieCode'";
            $db3->Query($sqlStat);

            // -------------------------
            // Herbereken saldo rekening
            // -------------------------

            self::CalcRekeningSaldo($rekeningId);

        }

        // ----------------------------
        // Fill from Diverse-prestaties
        // ----------------------------

        $prestatiebronnen = array();
        $prestatieTypes = array();

        $sqlStat = "Select * from era_dt_diverse_prestatie_types where dtRecStatus = 'A' and dtPrestatiebronEpra > ' '";
        $db->Query($sqlStat);

        while ($dtRec = $db->Row()) {
            $prestatiebronnen[] = $dtRec->dtPrestatiebronEpra;
            $prestatieTypes[] = $dtRec->dtCode;
        }

        foreach($prestatiebronnen as $key=>$prestatiebron) {

            $transactieCode = '*DIVERSE_PRESTATIE';
            $prestatieType = $prestatieTypes[$key];

            $sqlStat = "Select * from epra_ra_rekening_afspraken inner join epra_rh_rekening_headers on rhId = raRekening where raRecStatus = 'A' and ($pRekening = 0 or $pRekening = raRekening) and raPrestatiebron = '$prestatiebron' order by raDatumVan";

            $db->Query($sqlStat);

            while ($raRec = $db->Row()) {

                $persoon = $raRec->rhPersoon;
                $rekeningId = $raRec->rhId;
                $afspraakId = $raRec->raId;
                $seizoen = $raRec->raSeizoen;
                $datumVan = $raRec->raDatumVan;
                $datumTot = $raRec->raDatumTot;

                $sqlStat = "Select * from era_dp_diverse_prestaties where dpPersoon = '$persoon' and dpPrestatieType = '$prestatieType' and dpDatum <= DATE(NOW()) and dpDatum >= '$datumVan' and dpDatum <= '$datumTot'";

                $db2->Query($sqlStat);

                while ($dpRec = $db2->Row()) {

                    $bedrag = $raRec->raPrestatieBedrag * $dpRec->dpAantalEenheden;
                    $vergoedingPerc = 100;

                    $omschrijving = "Diverse prestatie";
                    $dtRec = SSP_db::Get_ERA_dtRec($prestatieType);

                    if ($dtRec)
                        $omschrijving = $dtRec->dtNaam . " - " . $dpRec->dpOmschrijving;

                    $taRec = SSP_db::Get_SX_taRec('ERA_PRESTATIE_EENHEDEN', $dpRec->dpEenheid);

                    $eenheid = "x.";

                    if ($taRec->taAlfaData)
                        $eenheid = $taRec->taAlfaData;

                    $omschrijving .= " [" . $dpRec->dpAantalEenheden . " $eenheid" . "]";


                    if (self::ChkRekeningDetail($rekeningId, 0, $bedrag, $awRec->twAantalTrainers, $vergoedingPerc, $omschrijving, $runId, $dpRec->dpDatum, $afspraakId, 0, $dpRec->dpId) == true)
                        continue;

                    // Bepaal seizoen
                    if (!$seizoen)
                        $seizoen = $actiefSeizoen;

                    $values["rdRun"] = MySQL::SQLValue($runId, MySQL::SQLVALUE_NUMBER);
                    $values["rdRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);
                    $values["rdAfspraak"] = MySQL::SQLValue($afspraakId, MySQL::SQLVALUE_NUMBER);
                    $values["rdDatum"] = MySQL::SQLValue($dpRec->dpDatum, MySQL::SQLVALUE_DATE);
                    $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
                    $values["rdTransactieCode"] = MySQL::SQLValue($transactieCode);
                    // $values["rdAantalTrainers"] = MySQL::SQLValue($awRec->twAantalTrainers, MySQL::SQLVALUE_NUMBER);
                    $values["rdVergoedingPerc"] = MySQL::SQLValue($vergoedingPerc, MySQL::SQLVALUE_NUMBER);
                    $values["rdPlusMin"] = MySQL::SQLValue('+');
                    $values["rdBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
                    $values["rdOmschrijving"] = MySQL::SQLValue($omschrijving);
                    $values["rdERA"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
                    $values["rdWedstrijd"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
                    $values["rdBetaalschema"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);
                    $values["rdDiversePrestatie"] = MySQL::SQLValue($dpRec->dpId, MySQL::SQLVALUE_NUMBER);
                    $values["rdUserCreatie"] = MySQL::SQLValue('*RUN');
                    $values["rdUserUpdate"] = MySQL::SQLValue('*RUN');

                    $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                    $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                    $id = $db3->InsertRow("epra_rd_rekening_detail", $values);
                }

                // ---------------------------------------------------------------------------------------------
                // Wissen prestaties onder deze afspraak die blijkbaar niet meer in diverse prestaties voorkomen
                // ---------------------------------------------------------------------------------------------

                $sqlStat = "Delete From epra_rd_rekening_detail where rdRekening = $rekeningId and rdRun < $runId and rdAfspraak = $afspraakId and rdTransactieCode = '$transactieCode'";
                $db3->Query($sqlStat);

                // ---------------------------
                // Herberekenen saldo rekening
                // ---------------------------

                self::CalcRekeningSaldo($rekeningId);

            }

        }

        // ---------------------------------------------
		// Aanmaken boekingen op basis tabel "te boeken"
        // ---------------------------------------------

        self::HdlTeBoeken($runId);

		return $runId;

	} 
	
	// ========================================================================================
	// Meer trainers toegelaten?
	//
	// In:	PloegID
	//		Datum prestatie
	//
	// Return: true/false
	// ========================================================================================
	
	static function ChkMeerTrainersToegelaten($pPloegId, $pDatum) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
		
		$sqlStat = "Select * from ssp_vp where vpId = $pPloegId";
		$db->Query($sqlStat);
		
		if (! $vpRec = $db->Row())
			return false;
		
		$returnVal = false;
		$voetbalCat = $vpRec->vpVoetbalCat;
		
		$sqlStat = "Select count(*) as aantal from epra_mt_meer_trainers_toegelaten where (mtCategorie <= ' ' or mtCategorie = '$voetbalCat') and mtploeg is null and mtDatumVan <= '$pDatum' and mtDatumTot >= '$pDatum'";
		$db->Query($sqlStat);

		if ($mtRec = $db->Row() and $mtRec->aantal >= 1)
			$returnVal = true;
		
		if ($returnVal == false) {
			
			$sqlStat = "Select count(*) as aantal from epra_mt_meer_trainers_toegelaten where mtploeg = $pPloegId and mtDatumVan <= '$pDatum' and mtDatumTot >= '$pDatum'";
			

			$db->Query($sqlStat);

			if ($mtRec = $db->Row() and $mtRec->aantal >= 1)
				$returnVal = true;			
		}
		
		return $returnVal;

	}

		
	// ========================================================================================
	// Aanmaken Betaalvoorstel
	//
	// In:	Voorstel-ID
	//
	// Return: voorstel-id
	// ========================================================================================
	
	static function CrtBetaalvoorstel($pVoorstelId, $pUserId) {
		
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
		
		$curDateTime =	date('Y-m-d H:i:s');
		
		//$values["uvHoedanigheid"] = MySQL::SQLValue($pHoedanigheid);
		//$values["uvUserCreatie"] = MySQL::SQLValue($pUserId);
		// $values["uvDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
		
		// $voorstelId = $db2->InsertRow("epra_uv_uitbetaling_voorstel", $values);
		
		$sqlStat = "Select * From epra_uv_uitbetaling_voorstel where uvId = $pVoorstelId";
		$db->Query($sqlStat);
		
		$uvRec = $db->Row();
		
		$voorstelId = $uvRec->uvId;
		$hoedanigheid = $uvRec->uvHoedanigheid;
		$datumTot = $uvRec->uvDatumTot;
		
		// ----------------------------------------------------------
		// Verwerk alle rekeningen met saldo betreffende hoedanigheid
		// ----------------------------------------------------------
		
		$sqlStat = "Select * from epra_rh_rekening_headers inner join ssp_ad on adCode = rhPersoon where rhHoedanigheid = '$hoedanigheid' and rhSaldo > 0 ORDER BY adNaamVoornaam";
			

		
		$db->Query($sqlStat);
		
		while ($rhRec = $db->Row()){
		
			$rekeningId = $rhRec->rhId;
			// $saldo = $rhRec->rhSaldo;
			$saldo = self::CalcRekSaldoDatumTot($rekeningId, $datumTot);
			
			if ($saldo <= 0)
				continue;
			
			$bankrekening = $rhRec->adBankRekening;
			
			$values2["udVoorstel"] = MySQL::SQLValue($voorstelId, MySQL::SQLVALUE_NUMBER);	
			$values2["udRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);				
			$values2["udBetaalStatus"] = MySQL::SQLValue('*OPEN');	
			$values2["udBedragVoorstel"] = MySQL::SQLValue($saldo, MySQL::SQLVALUE_NUMBER);	
			$values2["udBedrag"] = MySQL::SQLValue($saldo, MySQL::SQLVALUE_NUMBER);				
			$values2["udBedragBetaald"] = MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);				
			$values2["udBetaalwijze"] = MySQL::SQLValue('*BANK');
			$values2["udBankrekening"] = MySQL::SQLValue($bankrekening);
			$values2["udUserCreatie"] = MySQL::SQLValue($pUserId);
			$values2["udUserUpdate"] = MySQL::SQLValue($pUserId);
			$values2["udDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );	
			$values2["udDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );	
			
			$db2->InsertRow("epra_ud_uitbetaling_voorstel_detail", $values2);
			
		}
		// -----------------------------------
		// Set totaal voorstel, totaal betaald
		// -----------------------------------
		
		self::CalcBetaalVoorstelTotalen($voorstelId);
		

		return $voorstelId;
		
	}
	
	// ========================================================================================
	// Aanmaken Betaalvoorstel
	//
	// In:	Voorstel-ID
	//
	// Return: none
	// ========================================================================================
	
	static function CalcBetaalVoorstelTotalen($pVoorstelId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
		
		$sqlStat = "Select sum(udBedrag) as totaalVoorstel, sum(udBedragBetaald) as totaalBetaald from epra_ud_uitbetaling_voorstel_detail where udVoorstel = $pVoorstelId";
						
		$db->Query($sqlStat);
		
		$totaalVoorstel = 0;
		$totaalBetaald = 0;
		
		if ($udRec = $db->Row()){
			
			if ($udRec->totaalVoorstel) {
				$totaalVoorstel = $udRec->totaalVoorstel;
				$totaalBetaald = $udRec->totaalBetaald;	
			}
			
		}
			
		$sqlStat = "Update epra_uv_uitbetaling_voorstel set uvTotaalVoorstel = $totaalVoorstel, uvTotaalBetaald = $totaalBetaald where uvId = $pVoorstelId";
		$db->Query($sqlStat);			
	
	}
	
	// ========================================================================================
	// Bereken & opvullen rekening Saldo
	//
	// In:	- Rekening ID
	//
	// Return: Niets
	// ========================================================================================
	
	static function CalcRekeningSaldo($pRekeningId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		
		$saldo = 0;
		
		$sqlStat = "Select sum(rdBedrag) as bedrag From epra_rd_rekening_detail where rdRekening = $pRekeningId and rdPlusMin = '+'";
		$db->Query($sqlStat);
		
		if ($rdRec = $db->Row())
			$saldo += $rdRec->bedrag;
		
		$sqlStat = "Select sum(rdBedrag) as bedrag From epra_rd_rekening_detail where rdRekening = $pRekeningId and rdPlusMin = '-'";
		$db->Query($sqlStat);
		
		if ($rdRec = $db->Row())
			$saldo -= $rdRec->bedrag;		
		
		$sqlStat = "Update epra_rh_rekening_headers set rhSaldo = $saldo, rhSaldoDatum = now() where rhId = $pRekeningId";
		$db->Query($sqlStat);
		
		$db->close();		
		
	}
	// ========================================================================================
	// Bereken saldo t/m prestatiedatum
	//
	// In:	- Rekening ID
	//		- Prestatiedatum t/m
	//
	// Return: Saldo
	// ========================================================================================
	
	static function CalcRekSaldoDatumTot($pRekeningId,$pDatum) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		
		$saldo = 0;
		
		$sqlStat = "Select sum(rdBedrag) as bedrag From epra_rd_rekening_detail where rdRekening = $pRekeningId and rdPlusMin = '+' and rdDatum <= '$pDatum'";
		$db->Query($sqlStat);
		
		if ($rdRec = $db->Row())
			$saldo += $rdRec->bedrag;
		
		$sqlStat = "Select sum(rdBedrag) as bedrag From epra_rd_rekening_detail where rdRekening = $pRekeningId and rdPlusMin = '-'";
		$db->Query($sqlStat);
		
		if ($rdRec = $db->Row())
			$saldo -= $rdRec->bedrag;	

		// -------------
		// Einde functie
		// -------------
		
		return $saldo;
		
	}	
	
	// ========================================================================================
	// Mag rekening gewist worden?
	//
	// In:	rekening
	//
	// Return: true/false
	// ========================================================================================
	
	static function ChkRekeningWissen($pRekeningId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
		
		// ------------------------------
		// Niet als gekoppelde afspraken
		// -----------------------------
		
		$sqlStat = "Select count(*) as aantal from epra_ra_rekening_afspraken where raRekening = $pRekeningId";
		$db->Query($sqlStat);
		
		if ($raRec = $db->Row() and $raRec->aantal > 0)
			return false;
		
		// ---------------
		// Niet als detail
		// ---------------
		
		$sqlStat = "Select count(*) as aantal from epra_rd_rekening_detail where rdRekening = $pRekeningId";
		$db->Query($sqlStat);
		
		if ($rdRec = $db->Row() and $rdRec->aantal > 0)
			return false;		
		
		// -------------------------
		// Anders: mag gewist worden
		// -------------------------
		
		return true;
		
	}
	
	// ========================================================================================
	// Mag afspraak gewist worden?
	//
	// In:	afspraak
	//
	// Return: true/false
	// ========================================================================================
	
	static function ChkAfspraakWissen($pAfspraakId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
		
		// ---------------
		// Niet als detail
		// ---------------
		
		$sqlStat = "Select count(*) as aantal from epra_rd_rekening_detail where rdAfspraak = $pAfspraakId";
		$db->Query($sqlStat);
		
		if ($rdRec = $db->Row() and $rdRec->aantal > 0)
			return false;		
		
		// ---------------------
		// Niet als betaalschema
		// ---------------------
		
		$sqlStat = "Select count(*) as aantal from epra_bs_betaalschema where bsAfspraak = $pAfspraakId";
		$db->Query($sqlStat);
		
		if ($bsRec = $db->Row() and $bsRec->aantal > 0)
			return false;
		
		// -------------------------
		// Anders: mag gewist worden
		// -------------------------
		
		return true;
		
	}

	// ========================================================================================
	// Check of specifieke afspraak toegestaan
	//
	// In:	Rekening
	//	
	// Uit: Specifieke afspraak toegestaan?
	//
	// ========================================================================================
	
	static function ChkAfspraakToegestaan($pRekeningId, &$pBoodschap) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

		$toegestaan = true;
		
		$sqlStat = "Select * From epra_rh_rekening_headers where rhId = $pRekeningId";
		$db->Query($sqlStat);
		$rhRec = $db->Row();
		
		if ($rhRec->rhHoedanigheid == '*SPELER_SENIORS') {
			
			$persoon = $rhRec->rhPersoon;
			
			$sqlStat = "Select * From ssp_ad where adCode = '$persoon'";
			$db->Query($sqlStat);
			$adRec = $db->Row();

			if (! $adRec->adVergoedingCat)  {
				
				$toegestaan = false;
				$pBoodschap = "Geen vergoedingscategorie gedefinieerd in ledendatabase voor betrokken speler ";
			
			} else 
				$vergoedingCat = $adRec->adVergoedingCat;
		
			if ($toegestaan){
				
				if ($vergoedingCat != '*SPECIFIEK') {
					
					$caRec = self::GetCaRec($vergoedingCat);
					
					if (! $caRec) {
						$toegestaan = false;
						$pBoodschap = "Geen actieve categorie-afspraak voor categorie $vergoedingCat";				
					}
					
				}
				
			}
		
		
		}
		
		return $toegestaan;
		
	}

	// ========================================================================================
	// Check prestatiebron toegestaan voor rekening
	//
	// In:	Rekening
	//		Prestatie-bron
	//	
	// Uit: Specifieke afspraak toegestaan?
	//
	// ========================================================================================
	
	static function ChkPrestatiebron($pRekeningId, $pPrestatiebron, &$pBoodschap) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

		$toegestaan = false;
		$pBoodschap = '';
		
		// ----------------------
		// Ophalen "hoedanigheid"
		// ----------------------
		
		$sqlStat = "Select * From epra_rh_rekening_headers where rhId = $pRekeningId";
		$db->Query($sqlStat);
		$rhRec = $db->Row();
		
		$hoedanigheid = $rhRec->rhHoedanigheid;
		$persoon = $rhRec->rhPersoon;
		
		
		// ----------------------
		// Ophalen "prestatiebron"
		// ----------------------
		
		$sqlStat = "Select * From sx_ta_tables where taTable = 'EPRA_PRESTATIE_BRON' and taCode = '$pPrestatiebron'";
		$db->Query($sqlStat);
		$taRec = $db->Row();		
		
		$hoedanigheden = $taRec->taAlfaData;
		
		$pBoodschap = "Prestatie-bron niet toegestaan voor hoedanigheid";
		
		if ($hoedanigheden <= ' ' or $hoedanigheden == '*ALL')
			$toegestaan = true;
		else {
		
			$pos = strpos($hoedanigheden, $hoedanigheid);
		
			if ($pos !== false)
				$toegestaan = true;
		}
		
		
		// -------------------------------------------------------------------
		// Voor spelers 1ste ploeg: enkel indien vergoeding-categorie ingevuld
		// -------------------------------------------------------------------

		if ($toegestaan && ($hoedanigheid == '*SPELER_1')  && ($pPrestatiebron == '*ERA_SPELER')) {
			
			$adRec = self::GetAdRec($persoon);
			
			if ((! $adRec->adVergoedingCat)  or ($adRec->adVergoedingCat <=  ' ') ){
				
				$toegestaan = false;
				$pBoodschap = "Geen vergoeding-categorie gedefinieerd voor speler in ledenbestand";
				
			}
			
		}
		
		
		// -------------
		// Einde functie
		// -------------
				
		return $toegestaan;
		
	}
	
	// ========================================================================================
	// Ophalen defaults voor nieuwe afspraak
	//
	// In:	Rekening 
	//		Prestatie-bron 
	//	
	// Uit: Datum van
	//		Datum tot
	//		Omschrijving prestatie
	//		Vergoeding-categorie
    //      Seizoen
	//
	//
	// ========================================================================================
	
	static function GetAfspraakDefaults($pRekeningId, $pPrestatiebron, &$pDatumVan, &$pDatumTot, &$pOmschrijvingPrestatie, &$pVergoedingCat, &$pSeizoen ) {

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

		include_once(SX::GetClassPath("settings.class"));
			
		// -------------------------	
		// Init uitgaande parameters
		// -------------------------
		
		$pDatumVan = null;
		$pDatumTot = null;
		$pOmschrijvingPrestatie = null;
		$pVergoedingCat = null;
        $pSeizoen = null;

		// ---------------------
		// Ophalen rekening-info
		// ---------------------
		
		$rhRec = self::GetRhRec($pRekeningId);
		
		// -------------------------------------------------
		// Bepalen datum van/tot op basis van actief seizoen
		// -------------------------------------------------
				
		$actiefSeizoen = SSP_settings::GetActiefSeizoen();

        $pSeizoen =  $actiefSeizoen;

        $vsRec = SSP_db::Get_SSP_vsRec($actiefSeizoen);

	    $pDatumVan = $vsRec->vsDatumVan;
		$pDatumTot = $vsRec->vsDatumTot;

		// --------------------
		// Bepalen omschrijving
		// --------------------
		
		if ($pPrestatiebron == '*ERA')
			$pOmschrijvingPrestatie = 'Vergoeding trainer (op basis ERA)';
		
		if ($pPrestatiebron == '*ERA' && $rhRec->rhHoedanigheid == '*TRAINER_JEUGD')
			$pOmschrijvingPrestatie = 'Vergoeding jeugdtrainer (op basis ERA)';	
		
		if ($pPrestatiebron == '*ERA' && $rhRec->rhHoedanigheid == '*TRAINER_SENIORS')
			$pOmschrijvingPrestatie = 'Vergoeding seniortrainer (op basis ERA)';			
		
		if ($pPrestatiebron == '*BETAALSCHEMA' && $rhRec->rhHoedanigheid == '*TRAINER_SENIORS')
			$pOmschrijvingPrestatie = 'Vaste vergoeding SENIOR-TRAINER';
		
		if ($pPrestatiebron == '*BETAALSCHEMA' && $rhRec->rhHoedanigheid == '*SPORTIEVE_STAF')
			$pOmschrijvingPrestatie = 'Vaste vergoeding SPORTIEVE STAF';
		
		if ($pPrestatiebron == '*BETAALSCHEMA' && $rhRec->rhHoedanigheid == '*MEDEWERKER')
			$pOmschrijvingPrestatie = 'Vaste vergoeding MEDEWERKER';
		
		if ($pPrestatiebron == '*ERA_SPELER' && $rhRec->rhHoedanigheid == '*SPELER_1')
			$pOmschrijvingPrestatie = 'Wedstrijd-premie speler 1ste ploeg';		
			
		if ($pPrestatiebron == '*ERA_SPELER' && $rhRec->rhHoedanigheid == '*SPELER_2')
			$pOmschrijvingPrestatie = 'Wedstrijd-premie speler 2de ploeg';
		
		// ----------------------------
		// Bepalen vergoeding-categorie
		// ----------------------------
		
		if ($pPrestatiebron == '*ERA_SPELER' && $rhRec->rhHoedanigheid == '*SPELER_1') {
			
			$persoon = $rhRec->rhPersoon;
			
			$adRec = self::GetAdRec($persoon);
			$pVergoedingCat = $adRec->adVergoedingCat;
						
			
		}
		
		if ($pPrestatiebron == '*ERA_SPELER' && $rhRec->rhHoedanigheid == '*SPELER_2') {
			
			$pVergoedingCat = '*2DEPLOEG';
						
			
		}	
	}
	
	// ========================================================================================
	// Check of rekening volledig goed opgezet (actieve afspraak, ...)
	//
	// In:	rekening
	//	
	// Uit: Status (in tekst)	
	//
	// Return: *OK, *ERROR, *WARNING
	//
	// ========================================================================================
	
	static function ChkRekening($pRekeningId, &$pStatus) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
		
		
		// ------------------------------------------------------------------------------
		// ERROR indien speler_seniors & geen vergoeding-categorie ingevuld bij de speler
		// ------------------------------------------------------------------------------
		
		$sqlStat = "Select * From epra_rh_rekening_headers where rhId = $pRekeningId";
		$db->Query($sqlStat);
		$rhRec = $db->Row();
		
		if ($rhRec->rhHoedanigheid == '*SPELER_1') {
			
			$persoon = $rhRec->rhPersoon;
			
			$sqlStat = "Select * From ssp_ad where adCode = '$persoon'";
			$db->Query($sqlStat);
			$adRec = $db->Row();	

			if (! $adRec->adVergoedingCat) {
				
				$pStatus =  "Vergoeding-categorie niet ingevuld bij speler";
				return '*ERROR';			

			}
			
			
			if ($adRec->adVergoedingCat > ' ' && $adRec->adVergoedingCat != '*SPECIFIEK') {
				//$pStatus = "*OK";
				//return '*OK';
			}
							
		}

		
		// -----------------------------------
		// ERROR als geen gekoppelde afspraken
		// ------------------------------------

		$sqlStat = "Select count(*) as aantal from epra_ra_rekening_afspraken where raRekening = $pRekeningId";
		$db->Query($sqlStat);
		
		if ((! $raRec = $db->Row()) or ($raRec->aantal <= 0)) {
			
			$pStatus =  "Geen gekoppelde afspraak";
			return '*ERROR';
			
		}
		
		// --------------------------------
		// WARNING als geen actieve spraken
		// --------------------------------

		$sqlStat = "Select count(*) as aantal from epra_ra_rekening_afspraken where raRekening = $pRekeningId and raDatumVan <= current_date and raDatumTot >= current_date  and raRecStatus = 'A'";
		$db->Query($sqlStat);
		
		if ((! $raRec = $db->Row()) or ($raRec->aantal <= 0)) {
			
			$pStatus =  "Geen ACTIEVE afspraak";
			return '*WARNING';
			
		}		
		
		// ---------
		// Alles OK
		// --------

		$pStatus = "*OK";	
		return "*OK";
		
	}	
	
	// ========================================================================================
	// Aanmaken (ontbrekende) rekeningen JEUGD-trainers
	//
	// In:	user-ID
	//
	// Return: Aantal rekeningen aangemaakt
	// ========================================================================================
	
	static function CrtRekeningenJeugdTrainers($pUserId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
		$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

		$curDateTime =	date('Y-m-d H:i:s');
		
		$aantalAangemaakt = 0;
		
		$sqlStat = "Select * From ssp_ad Where (adFunctieVB Like '%jeugd.tr%' or adFunctieVB Like '%inv.tr%') and adRecStatus = 'A'";
		
		$db->Query($sqlStat);
		
		while ($adRec = $db->Row()){
			
			$persoon = $adRec->adCode;
		
			$sqlStat = "Select count(*) as aantal from epra_rh_rekening_headers where rhPersoon = '$persoon' and rhHoedanigheid = '*TRAINER_JEUGD'";
			$db2->Query($sqlStat);
			
			if ($rhRec = $db2->Row() and $rhRec->aantal > 0)
				continue;
				
			$values["rhPersoon"] = MySQL::SQLValue($persoon);
			$values["rhHoedanigheid"] = MySQL::SQLValue('*TRAINER_JEUGD');			
			$values["rhUserCreatie"] = MySQL::SQLValue($pUserId);				
			$values["rhUserUpdate"] = MySQL::SQLValue($pUserId);
			$values["rhDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
			$values["rhDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
			
			$rekening = $db2->InsertRow("epra_rh_rekening_headers", $values);

			$aantalAangemaakt++;
			
			self::CrtAfspraakJeugdtrainer($rekening, $pUserId);
			
		}

		// -----------------------------------------------
		// Ontbrekende afspraken aanmaken (huidig seizoen)
        // -----------------------------------------------

        $sqlStat = "Select * from epra_rh_rekening_headers where rhRecStatus = 'A' and rhHoedanigheid = '*TRAINER_JEUGD'";
		$db->Query($sqlStat);

		while ($rhRec = $db->Row()){

		    $rekening = $rhRec->rhId;

            self::GetAfspraakDefaults($rekening, $prestatiebron, $datumVan, $datumTot, $omschrijvingPrestatie, $vergoedingCat, $seizoen);

            $sqlStat = "select count(*) as aantal from epra_ra_rekening_afspraken where raRekening = $rekening and raRecStatus = 'A' and raSeizoen = '$seizoen'";

            $db2->Query($sqlStat);
            $raRec = $db2->Row();

            if (! $raRec->aantal) {
                self::CrtAfspraakJeugdtrainer($rekening, $pUserId);
                $aantalAangemaakt++;
            }
        }

		// -------------
		// Einde functie
		// -------------
		
		return $aantalAangemaakt;
		
	}
	
	// ========================================================================================
	// Aanmaken (ontbrekende) rekeningen (SENIOR-)spelers
	//
	// In:	user-ID
	//
	// Return: Aantal rekeningen aangemaakt
	// ========================================================================================
	
	static function CrtRekeningenSpelers($pUserId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
		$db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

		$curDateTime =	date('Y-m-d H:i:s');
		
		$aantalAangemaakt = 0;
		
		$sqlStat = "Select * From ssp_ad Where adFunctieVB Like '%speler%' and (adVoetbalCat = 'SEN' or adVoetbalCat = 'U21') and adRecStatus = 'A'";
		
		
		$db->Query($sqlStat);
		
		while ($adRec = $db->Row()){
			
			$persoon = $adRec->adCode;
			
			// ----------
			// 1ste ploeg
			// ----------
		
			$sqlStat = "Select count(*) as aantal from epra_rh_rekening_headers where rhPersoon = '$persoon' and rhHoedanigheid = '*SPELER_1'";
			$db2->Query($sqlStat);
			
			$rhRec = $db2->Row();
			
			if ($rhRec->aantal == 0) {
					
				$values["rhPersoon"] = MySQL::SQLValue($persoon);
				$values["rhHoedanigheid"] = MySQL::SQLValue('*SPELER_1');			
				$values["rhUserCreatie"] = MySQL::SQLValue($pUserId);				
				$values["rhUserUpdate"] = MySQL::SQLValue($pUserId);
				$values["rhDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
				$values["rhDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );

                $rekening = $db2->InsertRow("epra_rh_rekening_headers", $values);

				$aantalAangemaakt++;
				
				self::CrtAfspraakSpeler($rekening, $pUserId);
				
			}

			// ---------
			// 2de ploeg
			// ---------
		
			$sqlStat = "Select count(*) as aantal from epra_rh_rekening_headers where rhPersoon = '$persoon' and rhHoedanigheid = '*SPELER_2'";
			$db2->Query($sqlStat);
			
			$rhRec = $db2->Row();
			
			if ($rhRec->aantal == 0) {

				$values["rhPersoon"] = MySQL::SQLValue($persoon);
				$values["rhHoedanigheid"] = MySQL::SQLValue('*SPELER_2');			
				$values["rhUserCreatie"] = MySQL::SQLValue($pUserId);				
				$values["rhUserUpdate"] = MySQL::SQLValue($pUserId);
				$values["rhDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
				$values["rhDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );

                $rekening = $db2->InsertRow("epra_rh_rekening_headers", $values);

				$aantalAangemaakt++;
				
				self::CrtAfspraakSpeler($rekening, $pUserId);
				
			}
			
		}

        // -----------------------------------------------
        // Ontbrekende afspraken aanmaken (huidig seizoen)
        // -----------------------------------------------

        $sqlStat = "Select * from epra_rh_rekening_headers where rhRecStatus = 'A' and (rhHoedanigheid = '*SPELER_1' or rhHoedanigheid = '*SPELER_2')";
        $db->Query($sqlStat);

        while ($rhRec = $db->Row()){

            $rekening = $rhRec->rhId;

            self::GetAfspraakDefaults($rekening, $prestatiebron, $datumVan, $datumTot, $omschrijvingPrestatie, $vergoedingCat, $seizoen);

            $sqlStat = "select count(*) as aantal from epra_ra_rekening_afspraken where raRekening = $rekening and raRecStatus = 'A' and raSeizoen = '$seizoen'";

            $db2->Query($sqlStat);
            $raRec = $db2->Row();

            if (! $raRec->aantal) {

                $afspraak = self::CrtAfspraakSpeler($rekening, $pUserId);

                if ($afspraak)
                    $aantalAangemaakt++;

            }
        }

        // -------------
		// Einde functie
		// -------------
		
		return $aantalAangemaakt;
		
	}
		
	// ========================================================================================
	// Aanmaken Afspraak voor (nieuwe) rekening Jeugdtrainer
	//
	// In:	Rekening-ID
	// 		User-ID
	//
	// Return: Afspraak- ID
	// ========================================================================================
	
	static function CrtAfspraakJeugdtrainer($pRekeningId, $pUserId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
		
		$prestatiebron = '*ERA';
		
		$rhRec = self::GetRhRec($pRekeningId);
		
		if ($rhRec == null)
			return 0;
		
		$persoon = $rhRec->rhPersoon;
		
		$adRec = self::GetAdRec($persoon);
		
		if ($adRec == null)
			return 0;
;				
		$basisBedrag = self::GetDiplomaBedragToeslag('*GEEN');
		
		if ($adRec->adDiplomaVoetbal and $adRec->adDiplomaVoetbal > ' ')
			$diploma = $adRec->adDiplomaVoetbal;
		else
			$diploma = '*GEEN';	
		
		if ($diploma > ' ' <> null and $diploma > ' ' and $diploma <> '*GEEN')
			$diplomaToeslag = self::GetDiplomaBedragToeslag($diploma);
		else
			$diplomaToeslag = 0;

		$ancienniteit = $adRec->adAncienniteit;
		
		$ancienniteitToeslag = 0;
			
		if ($ancienniteit > 0)
			$ancienniteitToeslag = self::GetAncienniteitToeslag($ancienniteit);

		$prestatieBedrag = $basisBedrag + $diplomaToeslag + $ancienniteitToeslag;
	
	    self::GetAfspraakDefaults($pRekeningId, $prestatiebron, $datumVan, $datumTot, $omschrijvingPrestatie, $vergoedingCat, $seizoen);

		$curDateTime = date('Y-m-d H:i:s');

		$ventilatieRekening = self::GetRekeningDefaultVentilatie($pRekeningId);
				
		$values["raRekening"] = MySQL::SQLValue($pRekeningId, MySQL::SQLVALUE_NUMBER);
		$values["raDatumVan"] = MySQL::SQLValue($datumVan, MySQL::SQLVALUE_DATE);
		$values["raDatumTot"] = MySQL::SQLValue($datumTot, MySQL::SQLVALUE_DATE);
        $values["raSeizoen"] = MySQL::SQLValue($seizoen);

		$values["raPrestatiebron"] = MySQL::SQLValue($prestatiebron);
        $values["raVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

		$values["raOmschrijvingPrestatie"] = MySQL::SQLValue($omschrijvingPrestatie);
		
		$values["raBasisBedrag"] = MySQL::SQLValue($basisBedrag, MySQL::SQLVALUE_NUMBER);		
		$values["raDiploma"] = MySQL::SQLValue($diploma);
		$values["raDiplomaToeslag"] = MySQL::SQLValue($diplomaToeslag, MySQL::SQLVALUE_NUMBER);	
		$values["raAncienniteit"] = MySQL::SQLValue($ancienniteit, MySQL::SQLVALUE_NUMBER);	
		$values["raAncienniteitToeslag"] = MySQL::SQLValue($ancienniteitToeslag, MySQL::SQLVALUE_NUMBER);	
		$values["raPrestatieBedrag"] = MySQL::SQLValue($prestatieBedrag, MySQL::SQLVALUE_NUMBER);	
		
		$values["raUserCreatie"] = MySQL::SQLValue($pUserId);				
		$values["raUserUpdate"] = MySQL::SQLValue($pUserId);
		$values["raDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
		$values["raDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
			
		$id = $db->InsertRow("epra_ra_rekening_afspraken", $values);		
	
	}
	
	// ========================================================================================
	// Aanmaken Afspraak voor (nieuwe) rekening Senior-speler
	//
	// In:	Rekening-ID
	// 		User-ID
	//
	// Return: Afspraak- ID
	// ========================================================================================
	
	static function CrtAfspraakSpeler($pRekeningId, $pUserId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

		$prestatiebron = '*ERA_SPELER';
		
		$rhRec = self::GetRhRec($pRekeningId);
		
		if ($rhRec == null)
			return 0;

		$persoon = $rhRec->rhPersoon;
		
		$adRec = self::GetAdRec($persoon);
		
		if ($adRec == null)
			return 0;

		// ----------------------------------------------------------
		// Vergoedingcategorie moet ingevuld zijn en niet "specifiek"
		// ----------------------------------------------------------

		if ($rhRec->rhHoedanigheid == '*SPELER_1') { 
			
			if (! $adRec->adVergoedingCat or $adRec->adVergoedingCat == '*SPECIFIEK')
				return 0;
			
			$vergoedingCat = $adRec->adVergoedingCat;

		}

		if ($rhRec->rhHoedanigheid == '*SPELER_2') {
            $vergoedingCat = '*2DEPLOEG';
		}

		$caRec = self::GetCaRec($vergoedingCat);
		
		if (! $caRec)
			return 0;

		$premieWinst = $caRec->caPremieWinst;
		$premieGelijk = $caRec->caPremieGelijk;
		
		self::GetAfspraakDefaults($pRekeningId, $prestatiebron, $datumVan, $datumTot, $omschrijvingPrestatie, $vergoedingCat, $seizoen);

		$curDateTime = date('Y-m-d H:i:s');

        $ventilatieRekening = self::GetRekeningDefaultVentilatie($pRekeningId);
				
		$values["raRekening"] = MySQL::SQLValue($pRekeningId, MySQL::SQLVALUE_NUMBER);
		$values["raDatumVan"] = MySQL::SQLValue($datumVan, MySQL::SQLVALUE_DATE);
		$values["raDatumTot"] = MySQL::SQLValue($datumTot, MySQL::SQLVALUE_DATE);
        $values["raSeizoen"] = MySQL::SQLValue($seizoen);

		$values["raPrestatiebron"] = MySQL::SQLValue($prestatiebron);
		$values["raVergoedingCat"] = MySQL::SQLValue($vergoedingCat);
        $values["raVentilatieRekening"] = MySQL::SQLValue($ventilatieRekening, MySQL::SQLVALUE_NUMBER);

		$values["raOmschrijvingPrestatie"] = MySQL::SQLValue($omschrijvingPrestatie);		
		
		$values["raPremieWinst"] = MySQL::SQLValue($premieWinst, MySQL::SQLVALUE_NUMBER);	
		$values["raPremieGelijk"] = MySQL::SQLValue($premieGelijk, MySQL::SQLVALUE_NUMBER);	
		
		$values["raUserCreatie"] = MySQL::SQLValue($pUserId);				
		$values["raUserUpdate"] = MySQL::SQLValue($pUserId);
		$values["raDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
		$values["raDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
			
		$afspraak = $db->InsertRow("epra_ra_rekening_afspraken", $values);

		// -------------
		// Einde functie
		// -------------

        return $afspraak;
	
	}
	
	// ========================================================================================
	// Zet betaal-voorstel detail op "betaald"
	//
	// In:	- VoorstelDetail-ID
	//		- Betaalwijze
	//		- User-ID
	//
	// Return: Niets
	// ========================================================================================
	
	static function SetVoorstelDetailBetaald($pVoorstelDetailId, $pBetaalwijze, $pUserId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 
		
		// -------------------------
		// Bepalen bedrag te betalen
		// -------------------------
		
		$sqlStat = "Select * from epra_ud_uitbetaling_voorstel_detail where udId = $pVoorstelDetailId";
		
		$db->Query($sqlStat);
		
		$teBetalen = 0;
		
		if ($udRec = $db->Row())
			$teBetalen = $udRec->udBedrag - $udRec->udBedragBetaald;
		
		if ($teBetalen <= 0)
			return;
		
		$voorstel = $udRec->udVoorstel;
		$sqlStat = "Select * from epra_uv_uitbetaling_voorstel where uvId = $voorstel";	
		$db->Query($sqlStat);
		$uvRec = $db->Row();
		
		$datumBetaling = $uvRec->uvBetaalDatum;
		
		if ($datumBetaling == null or $datumBetaling < ' ')
			$datumBetaling = date('Y-m-d');
		
		if ($pBetaalwijze == '*BANK' and $udRec->udBankrekening <= ' ')
			return;
		
		// ----------------------------
		// Update betaalvoorstel-detail
		// ----------------------------
		
		
		$sqlStat = "Update epra_ud_uitbetaling_voorstel_detail set udBetaalStatus = '*BETAALD', udBetaalDatum = '$datumBetaling', udBedragBetaald = udBedragBetaald + $teBetalen, udBetaalwijze = '$pBetaalwijze' where udId = $pVoorstelDetailId";
		
		$db->Query($sqlStat);
		
		$sqlStat = "Select * from epra_ud_uitbetaling_voorstel_detail where udId = $pVoorstelDetailId";
		
		$db->Query($sqlStat);
		$udRec = $db->Row();
		
		// --------------------------
		// Aanmaken entry in rekening
		// --------------------------
		
		// $datumBetaling = date('Y-m-d');
		
		$omschrijving = "Uitbetaling";
		
		if ($udRec->udBetaalwijze == '*BANK') {
			
			$omschrijving .= " via overschrijving";
			if ($udRec->udBankrekening > ' ')
					$omschrijving .= " op rekening: $udRec->udBankrekening";
		
		}
		
		if ($udRec->udBetaalwijze == '*CASH') 
			$omschrijving .= " (cash)";

		$curDateTime =	date('Y-m-d H:i:s');

		$seizoen = self::GetActiefSeizoenEPRA();
		
		$values["rdRekening"] = MySQL::SQLValue($udRec->udRekening, MySQL::SQLVALUE_NUMBER);
		$values["rdDatum"] = MySQL::SQLValue($datumBetaling, MySQL::SQLVALUE_DATE);
        $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
		$values["rdTransactieCode"] = MySQL::SQLValue('*UITBETALING');		
		$values["rdPlusMin"] = MySQL::SQLValue('-');
		$values["rdBedrag"] = MySQL::SQLValue($teBetalen, MySQL::SQLVALUE_NUMBER);		
		$values["rdOmschrijving"] = MySQL::SQLValue($omschrijving);
		
		$values["rdVoorstelDetail"] = MySQL::SQLValue($udRec->udId, MySQL::SQLVALUE_NUMBER);
		
		$values["rdUserCreatie"] = MySQL::SQLValue($pUserId);
		$values["rdUserUpdate"] = MySQL::SQLValue($pUserId);
		
		$values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
		$values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
		
		$id = $db->InsertRow("epra_rd_rekening_detail", $values);
		
		self::CalcRekeningSaldo($udRec->udRekening);
		

		// ------------------------
		// Berekenen totaal betaald
		// ------------------------
	
		$sqlStat = "Select * from epra_ud_uitbetaling_voorstel_detail where udId = $pVoorstelDetailId";
		$db->Query($sqlStat);
		
		if ($udRec = $db->Row())
			self::CalcBetaalVoorstelTotalen($udRec->udVoorstel);
		
		// -------------
		// Einde Functie
		// -------------
		
		$db->close();

	}
	
	// ========================================================================================
	// Update rekening-detail na wijziging voorstel-detail
	//
	// In: voorstl detail-ID
	//
	// Return: None
	// ========================================================================================
	
	static function SyncRekeningDetailMetVoorstelDetail($pVoorstelDetailId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 
		
		$sqlStat = "Select * from epra_ud_uitbetaling_voorstel_detail where udId = $pVoorstelDetailId";
		
		$db->Query($sqlStat);
		
		if ($udRec = $db->Row()){
			
			$betaalwijze = $udRec->udBetaalwijze;
			$betaalDatum = $udRec->udBetaalDatum;	
		
			$omschrijving = "Uitbetaling";
			
			if ($udRec->udBetaalwijze == '*BANK') {
				
				$omschrijving .= " via overschrijving";
				if ($udRec->udBankrekening > ' ')
						$omschrijving .= " op rekening: $udRec->udBankrekening";
			
			}
			
			if ($udRec->udBetaalwijze == '*CASH') 
				$omschrijving .= " (cash)";
			
			$sqlStat = "Update epra_rd_rekening_detail set rdDatum = '$betaalDatum', rdOmschrijving = '$omschrijving' where rdVoorstelDetail = $pVoorstelDetailId";

			$db->Query($sqlStat);
			
		}
		
		
	
	}
	
		
	// ========================================================================================
	// Zet betaal-voorstel detail op "niet betaald"
	//
	// In:	- VoorstelDetail-ID
	//
	// Return: Niets
	// ========================================================================================
	
	static function ResetVoorstelDetailNietBetaald($pVoorstelDetailId) {   


		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 	
		
		if ($pVoorstelDetailId > 0) {
			
			$sqlStat = "Select * from epra_rd_rekening_detail where rdVoorstelDetail = $pVoorstelDetailId";
			$db->Query($sqlStat);	
			$rdRec = $db->Row();
			
			$sqlStat = "Delete from epra_rd_rekening_detail where rdVoorstelDetail = $pVoorstelDetailId";
			$db->Query($sqlStat);
			
			$sqlStat = "Update epra_ud_uitbetaling_voorstel_detail set udBedragBetaald = 0 where udId = $pVoorstelDetailId";
			$db->Query($sqlStat);
			
			self::CalcRekeningSaldo($rdRec->rdRekening);
			
			$sqlStat = "Select * from epra_ud_uitbetaling_voorstel_detail where udId = $pVoorstelDetailId";
			$db->Query($sqlStat);
			
			if ($udRec = $db->Row())
				self::CalcBetaalVoorstelTotalen($udRec->udVoorstel);
			
		}
	
	}
		
	// ========================================================================================
	// Aanmaken betaal-schema reeks (telkens op de laatste dag van de maand)
	//
	// In:	- Afspraak-ID
	//		- User-ID
	//
	// Return: Niets
	// ========================================================================================
	
	static function CrtVergoedingsSchema($pAfspraakId, $pUserId) {   


		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 	
		
		$sqlStat = "Select * from epra_ra_rekening_afspraken where raId = $pAfspraakId";
		$db->Query($sqlStat);

		if (! $raRec = $db->Row())
		    return;

        $prestatiebron = $raRec->raPrestatiebron;
        $bedrag = $raRec->raBasisBedrag;

		$curDateTime =	date('Y-m-d H:i:s');

		$datums = array();

        if ($prestatiebron == '*BETAALSCHEMA')
            $datums = self::GetReeksDatumsEindeMaand($raRec);
        if ($prestatiebron == '*BETAALSCHEMA_DAG')
            $datums = self::GetReeksDatumsDagelijks($raRec);

        foreach($datums as $datum) {

            $omschrijving = self::CrtOmschrijvingPrestatie($pAfspraakId);

            $sqlStat = "Select count(*) as aantal from epra_bs_betaalschema where bsAfspraak = $pAfspraakId and bsDatum = '$datum'";

            $db->Query($sqlStat);

            if ((! $bsRec = $db->Row()) or ($bsRec->aantal <= 0) or ($bsRec->aantal == null)) {

                $values["bsAfspraak"] = MySQL::SQLValue($pAfspraakId, MySQL::SQLVALUE_NUMBER);
                $values["bsDatum"] = MySQL::SQLValue($datum, MySQL::SQLVALUE_DATE);
                $values["bsBedrag"] = MySQL::SQLValue($bedrag, MySQL::SQLVALUE_NUMBER);
                $values["bsStatus"] = MySQL::SQLValue('*OPEN');
                $values["bsOmschrijvingPrestatie"] = MySQL::SQLValue($omschrijving);

                $values["bsUserCreatie"] = MySQL::SQLValue($pUserId);
                $values["bsUserUpdate"] = MySQL::SQLValue($pUserId);

                $values["bsDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
                $values["bsDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );

                $id = $db->InsertRow("epra_bs_betaalschema", $values);
            }


        }

		
	}

    // ========================================================================================
    // Ophalen datums "einde maand"
    //
    // In:	Afspraak (record)

    //
    // Return: Array met datums "einde maand" (MySQL format)
    // ========================================================================================

    static function GetReeksDatumsEindeMaand($pAfspraakRec){

	    $datums = array();

	    $datumTot = new DateTime($pAfspraakRec->raDatumTot);

	    $volgendeDatum =  new DateTime($pAfspraakRec->raDatumVan);
	    $volgendeDatum->modify('last day of this month');

        $i = 0;

        while ($i < 999) {

            $i++;

            $datumE = $volgendeDatum->format('Y-m-d');

            $datums[] = $datumE;

            $volgendeDatum->modify('first day of this month');
            $volgendeDatum->modify( 'next month' );
            $volgendeDatum->modify('last day of this month');

            if ($volgendeDatum > $datumTot)
                break;
        }

        // -------------
	    // Einde functie
	    // -------------

        return $datums;

    }

    // ========================================================================================
    // Ophalen datums "dagelijks"
    //
    // In:	Afspraak (record)

    //
    // Return: Array met datums "einde maand" (MySQL format)
    // ========================================================================================

    static function GetReeksDatumsDagelijks($pAfspraakRec){

        $datums = array();

        $datumVan = new DateTime($pAfspraakRec->raDatumVan);
        $datumTot = new DateTime($pAfspraakRec->raDatumTot);
        $aantalDagenPerMaand = $pAfspraakRec->raAantalDagenPerMaand;

        $maand =  clone $datumVan->modify('first day of previous month');

        While ($maand < $datumTot) {

            $maand = $maand->modify('first day of next month');

            if ($maand < $datumTot) {

                $maandStart = clone $maand->modify('first day of this month');
                $maandEinde = clone $maand->modify('last day of this month');

                $i = 0;
                $aantalMaand = 0;

                $datum = clone $maandStart;

                While ($i < 31) {

                    $i++;

                    $dag = self::GetDatumDagcode($datum);

                    if (($aantalMaand < $aantalDagenPerMaand) and self::ChkAfspraakDag($pAfspraakRec, $dag, '*BASIS')) {

                        $datumE = $datum->format('Y-m-d');
                        $datums[] = $datumE;
                        $aantalMaand++;

                    }

                    $datum->modify('next day');

                    if ($datum > $maandEinde)
                        break;
                }

                if ($aantalMaand < $aantalDagenPerMaand) {

                    $datum = clone $maandStart;
                    $i = 0;

                    While ($i < 31) {

                        $i++;

                        $dag = self::GetDatumDagcode($datum);

                        if (($aantalMaand < $aantalDagenPerMaand) and self::ChkAfspraakDag($pAfspraakRec, $dag, '*RESERVE')) {

                            $datumE = $datum->format('Y-m-d');
                            $datums[] = $datumE;
                            $aantalMaand++;

                        }

                        $datum->modify('next day');

                        if ($datum > $maandEinde)
                            break;
                    }

                }


            }

        }

        // -------------
        // Einde functie
        // -------------

        asort($datums);

        return $datums;

    }

    // ========================================================================================
    // Ophalen datum dag (*MA, *DI, ...)
    //
    // In:	Datum
    //
    // Return: Dag-code
    // ========================================================================================

    static function GetDatumDagcode($pDatum){

        $dag = $pDatum->format('N');

        if ($dag == 1)
            return '*MA';
        if ($dag == 2)
            return '*DI';
        if ($dag == 3)
            return '*WO';
        if ($dag == 4)
            return '*DO';
        if ($dag == 5)
            return '*VR';
        if ($dag == 6)
            return '*ZA';
        if ($dag == 7)
            return '*ZO';

    }


    // ========================================================================================
    // Check Afspraak Dag
    //
    // In:	Afspraak (Record)
    //      Dag
    //      Basis of Extra dag (*BASIS, *RESERVE)
    //
    // Return: Dag-code
    // ========================================================================================

    static function ChkAfspraakDag($pAfspraakRec, $pDag, $pBasisExtra = '*BASIS') {


	    if ($pBasisExtra == '*BASIS'){

	        if ($pDag == $pAfspraakRec->raDag1)
	            return true;
            if ($pDag == $pAfspraakRec->raDag2)
                return true;
            if ($pDag == $pAfspraakRec->raDag3)
                return true;

        }

        if ($pBasisExtra == '*RESERVE'){

            if ($pDag == $pAfspraakRec->raReserveDag1)
                return true;
        }

        // -------------
        // Einde functie
        // -------------

        return false;



    }

    // ========================================================================================
	// Opmaken omschrijving betaal-schema
	//
	// In:	- Afspraak-ID
	//
	// Return: Omschrijving
	// ========================================================================================
	
	static function CrtOmschrijvingPrestatie($pAfspraakId) {   
	
		$omschrijving = "Periodieke vergoeding";
		
		$raRec = self::GetRaRec($pAfspraakId);
		
		if ($raRec == null)
			return $omschrijving;
		
		if ($raRec->raOmschrijvingPrestatie)
			return $raRec->raOmschrijvingPrestatie;
		
		$rekeningId = $raRec->raRekening;
		
		$rhRec = self::GetRhRec($rekeningId);
		
		if ($rhRec == null)
			return $omschrijving;	
		
		$hoedanigheid = self::GetTableName('EPRA_HOEDANIGHEID', $rhRec->rhHoedanigheid);
		
		$omschrijving .= " ($hoedanigheid)";
		
		return $omschrijving;

	
	}
		
	// ========================================================================================
	// Test of vergoedings-schema bestaat
	//
	// In:	- Afspraak-ID
	//
	// Return: *NO, *PAST, *YES
	// ========================================================================================
	
	static function ChkVergoedingsSchema($pAfspraakId) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object... 	
		
		$sqlStat = "Select count(*) as aantal from epra_bs_betaalschema where bsAfspraak = $pAfspraakId";
		
		$db->Query($sqlStat);
		
		if (! $bsRec = $db->Row() )
			return '*NO';
		
		if (($bsRec->aantal == null) or ($bsRec->aantal <= 0))
			return '*NO';
		
		$sqlStat = "Select count(*) as aantal from epra_bs_betaalschema where bsAfspraak = $pAfspraakId and date(bsDatum) >= current_date";		
		$db->Query($sqlStat);
		
		if (! $bsRec = $db->Row() )
			return '*PAST';
		
		if (($bsRec->aantal == null) or ($bsRec->aantal <= 0))
			return '*PAST';

		return '*YES';
		
		
	}
	// ========================================================================================
	// Get rekeningID voor specifieke "persoon/Hoedanigheid"
	//
	// In:	- Persoon
	//		- Hoedanigheid
	//
	// Return: Rekening ID ("0" als niet gevonden)
	// ========================================================================================
	
	static function GetRekeningId($pPersoon, $pHoedanigheid) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		
		$sqlStat = "Select * From epra_rh_rekening_headers where rhPersoon = '$pPersoon' and rhHoedanigheid = '$pHoedanigheid' order by rhId desc";
		
		if (!$db->Query($sqlStat)) 
			return 0;	
		
		$rekeningId = 0;
		
		while ($rhRec = $db->Row()) {		
		
			$rekeningId = $rhRec->rhId;
			break;
			
		
		}
		
		$db->close();
		return $rekeningId;
		
		
	}

	
	// ========================================================================================
	// Get afspraak-ID voor specifieke "Rekening/prestatiebron/datum"
	//
	// In:	- RekeningID
	//		- Prestatie-bron (bv *ERA)
	//		- Referentie Datum (MYSQL-format)
	//
	// Return: Afspraak ID ("0" als niet gevonden)
	// ========================================================================================
	
	static function GetAfspraakId($pRekeningId, $pPrestatiebron, $pRefDatum) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		$sqlStat = "Select * from epra_ra_rekening_afspraken where raRekening = $pRekeningId and raPrestatiebron = '$pPrestatiebron' order by raId desc";
		
		if (!$db->Query($sqlStat)) 
			return 0;

		$afspraakId = 0;
		$refDatum = new DateTime($pRefDatum);
					
		while ($raRec = $db->Row()) {

			$date = new DateTime();
			$date->setDate(2000, 1, 1);
			$datumVan = $date;

			$date = new DateTime();
			$date->setDate(2999, 12, 31);
			$datumTot = $date;

			
			if ($raRec->raDatumVan) {
				$datumVan = new DateTime($raRec->raDatumVan);
			}
			
			if ($raRec->raDatumTot) {
				$datumTot = new DateTime($raRec->raDatumTot);
			}
			
			
			if ($refDatum >= $datumVan and $refDatum <= $datumTot) {
				$afspraakId = $raRec->raId;
				break;
			}

		}
		
		$db->close();
		return $afspraakId;
		
	}    

	// ========================================================================================
	// Get diploma bedrag/toeslag
	//
	// In:	- Voetbal-diploma
	//
	// Return: Bedrag/toeslag
	// ========================================================================================
	
	static function GetDiplomaBedragToeslag($pVoetbalDiploma) {   

		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  	
		  
		 if (!$pVoetbalDiploma or $pVoetbalDiploma < ' ')
			 $diploma = '*GEEN';
		 else
			 $diploma = $pVoetbalDiploma;
		  
		$sqlStat = "Select * from sx_ta_tables where taTable= 'AB_DIPLOMA_VOETBAL' and taCode = '$diploma'";
		$db->Query($sqlStat);

		
		if (! $taRec = $db->Row())
			return 0;
		else
			return $taRec->taNumData;

	} 

	// ========================================================================================
	// Check "tijdige" ingave ERA prestatie
	//
	// In:	Persoon
	//		Datum prestatie
	//		Datum ingave (laatste wijziging)
	//		ERA-link
	// 		Rekening
	//
	// Return: *OK      (Ingave binnen de week)
	//		   *WARNING (Ingave > week te laat)
	//		   *ERROR   (Ingave later dan datum uitbetaling)
	// ========================================================================================
	
	static function ChkTijdigeIngaveERA($pPersoon, $pDatumPrestatie, $pDatumIngave, $pERA, $pRekeningId) {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

            if ($pDatumPrestatie >= $pDatumIngave )
                return '*OK';

            $datumPrestatie = new DateTime($pDatumPrestatie);

            $datumIngaveInput = substr($pDatumIngave,0,10);
            $datumIngave = new DateTime($datumIngaveInput);

            $dagenVerschil = $datumIngave->diff($datumPrestatie)->format("%a");

            if ($dagenVerschil <= 7)
                return '*OK';

            if ($dagenVerschil > 7) {

                $returnVal = '*WARNING';

                $sqlStat	= "Select count(*) as aantal from epra_rd_rekening_detail "
                    . "Inner join epra_ud_uitbetaling_voorstel_detail on udId = rdVoorstelDetail "
                    . "Inner join epra_uv_uitbetaling_voorstel on uvId = udVoorstel "
                    . "where rdRekening = $pRekeningId "
                    . " and rdTransactieCode = '*UITBETALING' "
                    . " and uvDatumTot >= '$pDatumPrestatie' "
                    . " and uvDatumCreatie < '$datumIngaveInput' ";

                $db->Query($sqlStat);

                $rdRec = $db->Row();

                if ($rdRec and $rdRec->aantal > 0)
                    $returnVal = '*ERROR';


            }


            // -------------
            // Einde functie
            // -------------


            return $returnVal;


	}

    // ========================================================================================
    // In historiek zetten alle rekeningen waarvan eigenaar in historiek
    //
    // Return: # rekenigen in historiek gezet
    // ========================================================================================

    static function PutRekeningenInHistoriek() {

	    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from epra_rh_rekening_headers left outer join ssp_ad on adCode =  rhPersoon where rhRecStatus = 'A' and (adRecStatus = 'H' or adRecStatus is null)";
        $db->Query($sqlStat);

        $aantal = 0;

        while ($rhRec = $db->Row()){

            $rekening = $rhRec->rhId;

            self::PutRekeningInHistoriek($rekening);

            $aantal++;

        }

	    // -------------
	    // Einde functie
	    // -------------

        return $aantal;

	}

    // ========================================================================================
    // In historiek zetten specifieke rekening
    //
    // In:	- Rekening
    //
    // ========================================================================================

    static function PutRekeningInHistoriek($pRekening){

	    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update epra_rh_rekening_headers set rhRecStatus = 'H' where rhId = $pRekening";
	    $db->Query($sqlStat);
	    
	    self::PutRekeningAfsprakenInHistoriek($pRekening);
	    
    }
    
    // ========================================================================================
    // In historiek zetten alle afspraken specifieke rekening
    //
    // In:	- Rekening
    //
    // ========================================================================================

    static function PutRekeningAfsprakenInHistoriek($pRekening){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Update epra_ra_rekening_afspraken set raRecStatus = 'H' where raRekening = $pRekening";
        $db->Query($sqlStat);

    }
    // ========================================================================================
    // In historiek zetten alle "oude" afspraken (vorig seizoen & vervallen)
    //
    // ========================================================================================

    static function PutOudeAfsprakenInHistoriek(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $aantal = 0;
        $huidigSeizoen = SSP_settings::GetActiefSeizoen();

        $sqlStat = "Select count(*) as aantal from epra_ra_rekening_afspraken where epra_ra_rekening_afspraken.raRecStatus = 'A' and raSeizoen < '$huidigSeizoen' and raDatumTot < current_date";
        $db->Query($sqlStat);

        if ($raRec = $db->Row())
            $aantal = $raRec->aantal;

        $sqlStat = "update epra_ra_rekening_afspraken set raRecStatus = 'H' where epra_ra_rekening_afspraken.raRecStatus = 'A' and raSeizoen < '$huidigSeizoen' and raDatumTot < current_date";
        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

        return $aantal;

    }

    // ========================================================================================
	// Get Anciënniteit toeslag
	//
	// In:	- Anciënniteit
	//
	// Return: Toeslag (0,5 EUR per 4 jaar)
	// ========================================================================================
	
	static function GetAncienniteitToeslag($pAncienniteit) {   

        if ($pAncienniteit < 5)
            return 0;
        else if ($pAncienniteit < 9)
            return 1 / 2;
        else if ($pAncienniteit < 13)
            return 1;
        else
            return 1 + 1/2;

	}


    // ========================================================================================
    // Boek "Te boeken"
    //
    // In:	Niets
    //
    // Return: Niets
    // ========================================================================================

    static function HdlTeBoeken($pRunId, $pUserId = '*RUN') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from epra_tb_te_boeken inner join sx_ta_tables on taTable = 'EPRA_TRANSACTIECODE' and taCode = tbTransactieCode where tbBoekStatus = '*OPEN' and date(tbVerrichtingsDatum) <= current_date ";

        $db->Query($sqlStat);

        while ($tbRec = $db->Row()){

            $tbId = $tbRec->tbId;

            //------------------------
            //  Bekijk alle rekeningen
            // -----------------------

            $persoon = $tbRec->tbPersoon;

            $sqlStat = "Select * from epra_rh_rekening_headers where rhPersoon = '$persoon'";

            $db2->Query($sqlStat);

            $geboekt = false;

            while ($rhRec = $db2->Row()) {

                $rekeningId = $rhRec->rhId;

                if ($geboekt)
                    break;

               $magGeboekt = false;

               if ($tbRec->tbRekening && $tbRec->tbRekening != $rekeningId )
                   continue;

               if ($tbRec->tbBoekCriterium == '*DATUM')
                   $magGeboekt = true;

                if ($tbRec->tbBoekCriterium == '*DATUM_BOEKINGEN') {

                    $sqlStat = "Select count(*) as aantal from epra_rd_rekening_detail where rdRekening = $rekeningId and rdPlusMin = '+'";
                    $db3->Query($sqlStat);

                    $rdRec = $db3->Row();

                    $magGeboekt = ($rdRec->aantal >  0);
                }

                if ($magGeboekt){

                   if ($tbRec->taAlfaData == '+')
                       $plusMin = '+';
                   else
                       $plusMin = '-';

                   $curDateTime =	date('Y-m-d H:i:s');

                   $seizoen = self::GetActiefSeizoenEPRA();

                   // Create detail-record
                   $values["rdRun"] = MySQL::SQLValue($pRunId, MySQL::SQLVALUE_NUMBER);
                   $values["rdRekening"] = MySQL::SQLValue($rekeningId, MySQL::SQLVALUE_NUMBER);
                   $values["rdDatum"] = MySQL::SQLValue($tbRec->tbVerrichtingsDatum, MySQL::SQLVALUE_DATE);
                   $values["rdSeizoen"] = MySQL::SQLValue($seizoen);
                   $values["rdTransactieCode"] = MySQL::SQLValue($tbRec->tbTransactieCode);
                   $values["rdPlusMin"] = MySQL::SQLValue($plusMin);
                   $values["rdBedrag"] = MySQL::SQLValue($tbRec->tbBedrag, MySQL::SQLVALUE_NUMBER);
                   $values["rdOmschrijving"] = MySQL::SQLValue($tbRec->tbOmschrijving);

                   $values["rdTeBoeken"] = MySQL::SQLValue($tbId, MySQL::SQLVALUE_NUMBER);

                   $values["rdUserCreatie"] = MySQL::SQLValue($pUserId);
                   $values["rdUserUpdate"] = MySQL::SQLValue($pUserId);

                   $values["rdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );
                   $values["rdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );

                   $rekeningDetail = $db3->InsertRow("epra_rd_rekening_detail", $values);

                   if ($rekeningDetail){

                       self::CalcRekeningSaldo($rekeningId);

                       $sqlStat = "Update epra_tb_te_boeken set tbBoekStatus = '*GEBOEKT', tbBoekdatum = now(), tbRekeningDetail = $rekeningDetail where tbId = $tbId";

                       $geboekt = true;

                       $db3->Query($sqlStat);

                   }


               }



            }

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Ophalen default ventilatie van een "hoedanigheid"
    //
    // In: Hoedanigheid
    //
    // Return: Ventilatie-rekening
    // ========================================================================================

    static function GetHoedanigheidVentilatie($pHoedanigheid){

	    include_once(SX::GetClassPath("_db.class"));

	    $taRec = SSP_db::Get_SX_taRec('EPRA_HOEDANIGHEID',$pHoedanigheid);

	    if (! $taRec)
	        return null;

	    // -------------
	    // Einde functie
	    // -------------

        return $taRec->taInteger;


    }

    // ========================================================================================
    // Ophalen default ventilatie van een rekening
    //
    // In: Rekening
    //
    // Return: Ventilatie-rekening
    // ========================================================================================

    static function GetRekeningDefaultVentilatie($pRekening) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $rhRek = self::GetRhRec($pRekening);

        If (! $rhRek)
            return 0;

        $hoedanigheid = $rhRek->rhHoedanigheid;

        $taRec = SSP_db::Get_SX_taRec('EPRA_HOEDANIGHEID', $hoedanigheid);

        If (! $taRec)
            return 0;

        $ventilatieRekening = $taRec->taInteger;

        $vrRec = SSP_db::Get_EFIN_vrRec($ventilatieRekening);

        if (! $vrRec)
            return 0;

        // -------------
        // Einde functie
        // -------------

        return $ventilatieRekening;


    }

    // ========================================================================================
    // Ophalen eenheid van een afspraak
    //
    // In: Afspraak
    //
    // Return: Eenheid
    // ========================================================================================

    static function GetAfspraakEenheid($pAfspraak){

	    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ----------------
        // Ophalen afspraak
        // ----------------

        $sqlStat = "Select * from epra_ra_rekening_afspraken where raId = $pAfspraak";
        $db->Query($sqlStat);

        if (! $raRec = $db->Row())
            return "xxx";


        $bron = $raRec->raPrestatiebron;

        if (! $bron)
            return 'xxx';


        $taRec = SSP_db::Get_SX_taRec('EPRA_PRESTATIE_BRON', $bron);

        if (! $taRec)
            return 'xxx';

        return $taRec->taDescription;

    }

    // ========================================================================================
    // In historiek plaatsen alle oude afspraken
    // ========================================================================================

    static function PutAfsprakenInHist() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $seizoen = self::GetActiefSeizoenEPRA();

        $sqlStat = "Select count(*) as aantal from  epra_ra_rekening_afspraken where raSeizoen < '$seizoen' and raDatumTot < current_date and raRecStatus <> 'H'";
        $db->Query($sqlStat);

        $raRec = $db->Row();
        $aantal = $raRec->aantal;

        $sqlStat = "update epra_ra_rekening_afspraken set raRecStatus = 'H' where raSeizoen < '$seizoen' and raDatumTot < current_date";

        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

        return $aantal;

    }


    // -----------
    // EINDE CLASS
    // -----------

}

?>