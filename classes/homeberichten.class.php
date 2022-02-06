<?php 

class SX_homeberichten { // define the class

	// ========================================================================================
	// Function: Get Boven-titel
	//
	// In:	- bhId = Homebericht ID	 
	//
	// Return: Name
	// ========================================================================================
                     
	static function GetTitelBoven($hbId) {
          
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
		
		$query = "Select * from ssp_hb where hbId = " . $hbId;
		
		if (!$db->Query($query)) { 
			return '*ERROR';
		}
		
		if (! $hbRec = $db->Row())
			return '*UNKNOWN';
		
				  
  		return $hbRec->hbTitelBoven;
 
	} 

	// ========================================================================================
	// Function: Get Titel
	//
	// In:	- bhId = Homebericht ID	 
	//
	// Return: Name
	// ========================================================================================
                     
	static function GetTitel($hbId) {
          
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
		
		$query = "Select * from ssp_hb where hbId = " . $hbId;
		
		if (!$db->Query($query)) { 
			return '*ERROR';
		}
		
		if (! $hbRec = $db->Row())
			return '*UNKNOWN';
		
				  
  		return $hbRec->hbTitel;
 
	}

    // ========================================================================================
    // Function: Zijn er snelberichten?
    //
    // Return: true/false
    // ========================================================================================

    static function ChkSnelberichten() {

	    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select count(*) as aantal from ssp_sb where sbDatumTot >= CURRENT_DATE  and sbDoelgroep <> '*VACATURE' order by sbSort, sbId desc";

        $db->Query($sqlStat);

        $returnVal = false;

        if ($sbRec = $db->Row())
            if ($sbRec->aantal > 0)
                $returnVal = true;

        // -------------
        // Einde functie
        // -------------

        return $returnVal;


    }
    // ========================================================================================
    // Function: Copy "homebricht"
    //
    // In:	- ID
    //      - Nieuwe titel "boven"
    //      - User
    //
    // Return: Nieuwe ID
    // ========================================================================================

    static function CopyHB($pHB, $pTitelBoven,  $pUser) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_hb where hbId = $pHB ";
        $db->Query($sqlStat);

        if ( ! $hbRec = $db->Row())
            return 0;

        $hbTitelBoven = $hbRec->hbTitelBoven;
        $hbTitel = $hbRec->hbTitel;
        $hbTekstRechts = $hbRec->hbTekstRechts;
        $hbTekstOnder = $hbRec->hbTekstOnder;
        $hbToonTitel = $hbRec->hbToonTitel;
        $hbKleurAchtergrond = $hbRec->hbKleurAchtergrond;

        $values = array();

        $curDateTime = date('Y-m-d H:i:s');

        $values["hbTitelBoven"] = MySQL::SQLValue($pTitelBoven);
        $values["hbTitel"] = MySQL::SQLValue($hbTitel);
        $values["hbTekstRechts"] = MySQL::SQLValue($hbTekstRechts);
        $values["hbTekstOnder"] = MySQL::SQLValue($hbTekstOnder);
        $values["hbToonTitel"] = MySQL::SQLValue($hbToonTitel,MySQL::SQLVALUE_NUMBER );
        $values["hbKleurAchtergrond"] = MySQL::SQLValue($hbKleurAchtergrond);

        $values["hbActief"] = MySQL::SQLValue(1,MySQL::SQLVALUE_NUMBER );

        $values["hbDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["hbUserCreatie"] = MySQL::SQLValue($pUser);
        $values["hbDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
        $values["hbUserUpdate"] = MySQL::SQLValue($pUser);

        $id = $db->InsertRow("ssp_hb", $values);

        // ------------
        // Function end
        // ------------

        return $id;

    }


    // ---------
    // EINDE CLASS
    // -----------

}
       
?>