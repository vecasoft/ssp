<?php 

class SX_homeberichten { // define the class


	// ========================================================================================
	// Function: Get banner name
	//
	// In:	- baId = Banner ID	 
	//
	// Return: Name
	// ========================================================================================
                     
	public function GetTitel($hbId) {   
          
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
		
		$query = "Select * from ssp_hb where hbId = " . $baId;
		
		if (!$db->Query($query)) { 
			return '*ERROR';
		}
		
		if (! $hbRec = $db->Row())
			return '*UNKNOWN';
		
				  
  		return $hbRec->hbTitel;
 
	}    
	
	
}
       
?>