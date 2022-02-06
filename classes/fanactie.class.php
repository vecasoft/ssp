<?php 
     
class SSP_fanactie { // define the class
	 
	// ===================================================================================================
	// Functie: Toewijzen M2 aan fan-ID 
	//
	// In:	- fan-ID
	//
	// Return:  false/true
	//
	// ===================================================================================================
	 
	public function ToewijzenM2($pFanId) {  

		 
		include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
		
		// ---------------
		// Get needed data
		// ---------------
		
		$query = "Select * From ssp_fanactie where id = $pFanId";
		
		if (! $db->Query($query))
			return false;
		
		if (!$fanRec = $db->Row())
			return false;

		// ------
		// Type A
		// ------
		
		if ($fanRec->m2_A > 0) 
			self::ToewijzenM2PerType($pFanId, 'A',$fanRec->m2_A);
		
		// ------
		// Type B
		// ------
		
		if ($fanRec->m2_B > 0) 
			self::ToewijzenM2PerType($pFanId, 'B',$fanRec->m2_B);				
		
		// ------
		// Type C
		// ------
		
		if ($fanRec->m2_C > 0) 
			self::ToewijzenM2PerType($pFanId, 'C',$fanRec->m2_C);	
		
		// ------
		// Type D
		// ------
		
		if ($fanRec->m2_D > 0) 
			self::ToewijzenM2PerType($pFanId, 'D',$fanRec->m2_D);	
		
		return true;

	}
	
	// ===================================================================================================
	// Functie: Toewijzen M2 aan fan-ID per Type
	//
	// In:	- fan-ID
	//		- Type (A/B/...)
	//		- Aantal
	//
	// Return:  false/true
	//
	// ===================================================================================================
	 
	public function ToewijzenM2PerType($pFanId,$pType,$pAantal) {  

		 
		include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
		
		
		$neededM2 = $pAantal;
			
		$query = "Select count(*) as teller from ssp_fanactie_m2 where fanId = $pFanId and type = '$pType'";
		
		if (! $db->Query($query))
				return false;
	
		if (!$m2Rec = $db->Row())
			return false;
				
		$neededM2 = $neededM2 - $m2Rec->teller;
			
		if ($neededM2 > 0 ) {
			
		
			for($i=1; $i <= $neededM2 ; $i++ ) {
				
				$query = "Select * from ssp_fanactie_m2 where fanId = 0 and type = '$pType' order by nummer";
				
				if (! $db->Query($query))
					return false;
		
				if (!$m2Rec = $db->Row())
					return false;
				
				$freeNummer = $m2Rec->nummer;
				
				$query = "Update ssp_fanactie_m2 set fanId = $pFanId, datumToegewezen = now() where nummer = $freeNummer";
					
				if (! $db->Query($query))
					return false;
			}
					
		}
		
		return true;
		
	}
		
	// ===================================================================================================
	// Functie: Vrijgeven M2 
	//
	// In:	- nummer
	//
	// Return:  false/true
	//
	// ===================================================================================================
	 
	public function VrijgevenM2($pNummer) {  

		 
		include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
		$query = "Update ssp_fanactie_m2 set fanId = 0 where nummer = $pNummer";
					
		if (! $db->Query($query))
			return false;
		
		return true;


	}
	
	// ===================================================================================================
	// Functie: Vrijgeven M2 van fan-ID 
	//
	// In:	- fan-ID
	//
	// Return:  false/true
	//
	// ===================================================================================================
	 
	public function VrijgevenFanM2($pFanId) {  

		 
		include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
		$query = "Update ssp_fanactie_m2 set fanId = 0 where fanId = $pFanId";
					
		if (! $db->Query($query))
			return false;
		
		return true;


	}
	
	// ===================================================================================================
	// Functie: Ophalen M2 van fan-ID §(comma-separated string)
	//
	// In:	- fan-ID
	//
	// Return:  String met m2 nummers
	//
	// ===================================================================================================
	 
	public function GetFanM2String($pFanId) {  

		 
		include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object  
		
		$query = "Select * from ssp_fanactie_m2 where fanId = $pFanId";
					
		if (! $db->Query($query))
			return '';
		
		$string = '';
		
		while($fm2Rec = $db->Row()) {
			
			if ($string <= ' ')
				$string = $fm2Rec->nummer;
			else
				$string = $string . ',' . $fm2Rec->nummer;
	
		}
		
		return $string;


	}	
}
       
?>