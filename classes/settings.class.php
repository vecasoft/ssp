<?php 
 
class SSP_settings{ // define the class


	// ========================================================================================
	// Functie: Ophalen ploegId van de eerste ploeg
	//
	// In: Geen...
	//
	// Return: PloegID eerste ploeg
	// ========================================================================================
	Static function GetEerstePloegId() {
		return 375;
	}

	// ========================================================================================
	// Functie: Ophalen ploegId van de reserven ploeg
	//
	// In: Geen...
	//
	// Return: PloegID Reserven
	// ========================================================================================
	Static function GetReservenId() {
		return 376;
	}

	// ========================================================================================
	// Functie: Ophalen actief seizoen
	//
	// In: Type (*ACTIEF, *VORIG)	//
	// Return: Seizoen
	// ========================================================================================
	Static function GetActiefSeizoen($pType = '*ACTIEF') {

		include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object	
		
		$sqlStat = 'Select * from ssp_vs order by vsCode';
		$db->Query($sqlStat);

        $seizoen = null;

		while ($vsRec = $db->Row()){

            if ($pType == '*VORIG' and $vsRec->vsHuidigSeizoen == 1)
                break;

            $seizoen = $vsRec->vsCode;

		    if ($pType == '*ACTIEF' and $vsRec->vsHuidigSeizoen == 1)
                break;
        }

        // -------------
        // Einde functie
        // -------------
			
		return $seizoen;
	
	}

    // ========================================================================================
    // Functie: Ophalen seizoen op basis van datum
    //
    // In: Datum
    //
    // Return: Seizoen
    // ========================================================================================
    Static function GetSeizoen($pDatum) {

        include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object

        $datum = substr($pDatum,0,10);

        $sqlStat = "Select * from ssp_vs where vsDatumVan <= '$datum' and vsDatumTot >= '$datum'";
        $db->Query($sqlStat);

        if ($vsRec = $db->Row())
            $seizoen = $vsRec->vsCode;
        else
            $seizoen = null;

        // -------------
        // Einde functie
        // -------------

        return $seizoen;


    }


    // ========================================================================================
	// Functie: Ophalen link naar een statische pagina
	//
	// In: - type (COMP, STAND
	//     - Text voor link
	//     - attrributen                            OPTIONEEL
	//     - extra toevoeging aan de link           OPTIONEEL
	//
	// Return: Link-code
	// ========================================================================================

	Static function GetLink($type, $linkText, $attrib='', $linkExtra='') {

		$link = '';
	
 		if ($type == 'COMP')
			$link = 'index.php?option=com_content&view=article&id=79&Itemid=112';
		  
		
		if ($type == 'PERSBERICHTEN')
			$link = 'index.php?app=persberichten_subpage';
			
		if ($type == 'PERSBERICHT')
			$link = 'index.php?app=persbericht_subpage&parm1=';	
			
		if ($type == 'PLOEGPAGINAS_JEUGD')
			$link = 'index.php?app=article_subpage&parm1=7&layout=full';
			
		if ($type == 'PLOEGPAGINAS_SENIORS')
			$link = 'index.php?app=article_subpage&parm1=8&layout=full';
			
		if ($type == 'STAND')
			$link = 'index.php?option=com_content&view=article&id=74&Itemid=153';

		if ($type == 'TOEPASSINGEN')
			$link = 'index.php?app=app_subpage&extapp=my_apps';
			
		if ($type == 'VOETBALVERSLAG')
			$link = 'index.php?app=wedstrijdverslag_subpage&parm1=';

		if ($type == 'VOETBALVERSLAG_SENIORS')
			$link = 'index.php?app=wedstrijdverslag_subpage&parm1=';
		

		if ($type == 'TORNOOIEN_JEUGD')
			$link = 'index.php?app=tornooien_subpage';

		$link = $link . $linkExtra;


		return '<a href="' . $link . '" ' . $attrib . '>' . $linkText . '</a>';
		
	}    

	// ========================================================================================
	// Functie: Ophalen background-kleur (voor subfiles, ...)
	//
	// In: Color (green, red, yellow, ...)
	//
	// Return: PloegID eerste ploeg
	// ========================================================================================
	Static function GetBackgroundColor($pColor) {
		
		$color = trim(strtolower($pColor));
		
		if ($color == 'green')
			$color = '#2AF75E';
		
		if ($color == 'yellow')
			$color = '#FFFFAA';		
		
		if ($color == 'red')
			$color = '#F75E2B';	
		
		if ($color == 'grey')
			$color = '#B5B5B5';	
		
		if ($color == 'blue')
			$color = '#C4C4FF';	
		
		if ($color == 'grey')
			$color = '#CECECE';	
		
		return $color;
		

	}

}
       
?>