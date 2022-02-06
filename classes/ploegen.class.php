<?php 
  
class SSP_ploegen { // define the class

    // ========================================================================================
    // Functie: Ophalen ploeg naam of naamKort
    //
    // In: - ploegId
    //     - Type (*NAAM, *NAAMKORT)
    //
    // Return: PloegNaam
    // ========================================================================================
                         
    static function GetNaam($pPloegId, $pType = '*NAAM') {   
    
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
      
		$query = "Select * from ssp_vp where vpId = $pPloegId";
		$naam = '';
				
		if (!$db->Query($query))  {
			$naam =  $query;
		}
		 
		elseif (!$vpRec = $db->Row()) {
			$naam = '???';     // Onbestaande ploeg
		}
	 
			elseif ($pType != '*NAAMKORT') {
				$naam = $vpRec->vpNaam;
			}
		
		else {
		 $naam = $vpRec->vpNaamKort . $vpRec->vpLetter;
		}     
    	
		// =============
		// Function end
		// ============
	
		$db->Close();
	
		return $naam;
      
    		
    }    

	// ========================================================================================
	// Functie: Ophalen ploeg groep
	//
	// In: - ploegId
	//
	// Return: JEUGD/ SENIORS
	// ========================================================================================
			 
	static function GetGroep($pPloegId) {   
          
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object   
		  
		$query = 'Select * from ssp_vp where vpId = '.$pPloegId;
		  
		if (!$db->Query($query))  {
			return $query;
		}

		if (!$vpRec = $db->Row()) {
			return 'Jeugd';   
		}  
		  
		return $vpRec->vpJeugdSeniors;
		
	}    

	// ========================================================================================
	// Functie: Aftesten bestaan ploeg
	//
	// In: - ploeg-id
	//
	// Return: TRUE/FALSE
	// ========================================================================================

	static function Exist($pPloegId) {   
 
     
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
  
		$query = 'Select * from ssp_vp where vpId = '.$pPloegId;
  
		if (!$db->Query($query))  {
			return $query;
		}

		if ($db->RowCount() > 0)
			return TRUE;        
                   
		return FALSE;

	}    

	// ========================================================================================
	// Functie: Ophalen eerstvolgende wedstrijd id
	//
	// In: - ploeg-id
	//     - Aantal uren in het verleden (default=4 uren)
	//     - Enkel nog te spelen wedstrijden? (default =FALSE)
	//
	// Return: WedstrijdID (0 = geen volgende wedstrijd)
	// ========================================================================================
    
	static function GetVolgendeWedstrijd($pPloegId, $pUren = 4, $pEnkelTeSpelen = FALSE) {   
 
 
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  

		$extraWhere = ' ';
		if ($pEnkelTeSpelen == TRUE)
			$extraWhere = 'and vwStatus = "TS"'; // Te spelen

		$query	= 'Select * from ssp_vw '  
				. 'where vwPloeg = ' . $pPloegId . ' and vwDatumTijd >= curdate() '
				. $extraWhere
				. ' order by vwDatumTijd';

		if ($pUren > 0) {
			$query	= 'Select * from ssp_vw '  
					. 'where vwPloeg = ' . $pPloegId . ' and vwDatumTijd + INTERVAL '. $pUren . ' HOUR >= now() '
					. $extraWhere
					. ' order by vwDatumTijd';
			//echo $query;
		}

		
		if (!$db->Query($query))  
			echo $query;	

		if ($vwRec = $db->Row())
			return $vwRec->vwId;
		else
			return 0;
		
	}    

	// ========================================================================================
	// Functie: Ophalen link html snippet naar ploegpaginga.
	//
	// In: - ploeg-id
	//
	// Return: HTML code met link naar plopegpagina
	// ========================================================================================
                 
    
	static function GetPloegPaginaLink($pPloegId, $pLinkText = '', $pAttrib = '') {   
 


		if ($pLinkText > ' ')
			$link = $pLinkText;
		else
			$link = self::GetNaam($pPloegId, '*NAAMKORT');


		$linkPloegPagina = "index.php?app=ploegpagina_subpage&parm1=$pPloegId&layout=full";
		$ploegPaginaHtml = "<a $pAttrib href='$linkPloegPagina'>$link</a>";

		return $ploegPaginaHtml;
		
	}    

	// ========================================================================================
	// Functie: Ophalen actief seizoen
	//
	// In: Geen
	//
	// Return: Actief Seizoen (bv: 2013-2014)
	// ========================================================================================

	static function GetSeizoen() {   
 
     
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
  
		$query = 'Select * from ssp_vs where vsHuidigSeizoen = 1';
  
		if (!$db->Query($query))  {
			return '';
		}

		if ($vsRec = $db->Row())
			return $vsRec->vsCode;
			
                   
		return '2014-2015';

	}  
	
    // ========================================================================================
    // Functie: Get ploeg RGB kleurcode
    //
    // In: - ploegId
    //
    // Return: PloegNaam
    // ========================================================================================
                         
    static function GetKleurCode($pPloegId) {
	
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
      
		$query = "Select * from ssp_vp where vpId = $pPloegId";
		$color = '';
				
		if (!$db->Query($query))  
			return $color;
			
		if (! $vpRec = $db->Row())
			return $color;
			
		if ($vpRec->vpKleur == 'Geel')
			$color = '#FFFF00';
			
		if ($vpRec->vpKleur == 'Blauw')
			$color = '#38C0F1';
		
		if ($vpRec->vpKleur == 'Groen')
			$color = '#9ACB11';
				
		if ($vpRec->vpKleur == 'Rood')
			$color = '#FF8000'; 
			
		return $color;


	}
	
    // ========================================================================================
    // Functie: Get ploeg kleur-box
    //
    // In: - ploegId
    //
    // Return: HTML-code color-box
    // ========================================================================================
                         
    static function GetKleurCodeBox($pPloegId) {
	
		$color = self::GetKleurCode($pPloegId);
		
		if ($color <= ' ')
			return '';
			
		$colorBox =  "<div style='display: inline; margin-left: 5px; background-color: $color; border: 1px solid'>&nbsp;&nbsp;</div>";
		return $colorBox;
		
	}
        
} // End class
       
?>