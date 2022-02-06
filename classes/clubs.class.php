<?php 
  

class SSP_clubs { // define the class


		// ========================================================================================
		// Functie: Ophalen ploeg naam of naamKort
		//
		// In: - ploegId
		//     - Type (*NAAM, *NAAMKORT)
		//
		// Return: PloegNaam
		// ========================================================================================
                 
    
		public function GetNaam($pClubId, $pAsLink=FALSE, $pAttr='') {   
 
           
			include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
 
      
			$query = 'Select * from ssp_cl where clId = '.$pClubId;
                   
			if (!$db->Query($query)) { 
				return $query;
			}
      
  
			if (!$clRec = $db->Row())
			return '???';   
		  
   
			$clubNaam = $clRec->clNaam;

			  if ($pAsLink == TRUE && $clRec->clWebsite > ' ') {
			  
				$clubNaam =  "<a $pAttr target = '_blank' href='$clRec->clWebsite'>$clRec->clNaam</a>";
			  
			  }
      
			return $clubNaam;
 
		}    

 		// ========================================================================================
		// Functie: Ophalen link naar een club website
		//
		// In: - club-id
		//     - Text voor link (*NAAM = Naam van de club)
		//
		// Return: Link-code
		// ========================================================================================

	
		public function GetSiteLink($pClubId, $pLinkText='*NAAM') {
           
			include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object 
		   
			$query = 'Select * from ssp_cl where clId = '. $pClubId;
     	
			if (! $db->Query($query))
				return 'ERROR';
				
			if (! $clRec = $db->Row())
				return 'ERROR';

			if ($pLinkText == '*NAAM') {
				$linkText = $clRec->clNaam;
			}
			else {
				$linkText = $pLinkText;
			}
 
			if ($clRec->clWebsite <= ' ')
				return $linkText;
				
			$linkClub = $clRec->clWebsite;

			$link = '<a href="' . $linkClub . '" target="_blank">' . $linkText . '</a>';
			
			return $link;
	
		} 
        
}
       
?>