<?php 
  
class SSP_kersttornooi { // define the class

    // ========================================================================================
    // Functie: Ophalen aantal plaatsen bepaalde categorie
    //
    // In: Categorie
    //
    // Return: Aantal plaatsen (0 = volzet)
    // ========================================================================================
                         
    static function GetAantalPlaatsen($pCategorie) {
    
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
        include_once(SX::GetClassPath("_db.class"));

        $taRec = SSP_db::Get_SX_taRec('KERSTTORNOOI_CATEGORIE',$pCategorie);

        if (! $taRec)
            return 0;

        $aantalPlaatsen = intval($taRec->taNumData);

        if (! $aantalPlaatsen)
            return 0;

        // ----------------------------------------------------
        // Aftrekken reeds ingeschreven teams voor de categorie
        // ----------------------------------------------------

        $sqlStat = "Select count(*) as aantal from event_kersttornooi where categorie = '$pCategorie'";
        $db->Query($sqlStat);

        $ztRec = $db->Row();

        if ($ztRec->aantal > 0)
            $aantalPlaatsen -= $ztRec->aantal;


        if ($aantalPlaatsen < 0)
            $aantalPlaatsen = 0;


        // -------------
        // Einde functie
        // -------------
	
		$db->Close();
	
		return $aantalPlaatsen;

    }

    // ========================================================================================
    // Functie: Controleren of bepaalde categorie volzet -> In HISTORIEK zetten
    //
    // In: Geen
    //
    // Return: Geen
    // ========================================================================================

    static function ChkCategorieVolzet() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);


        $sqlStat = "Select * from sx_ta_tables where taTable = 'KERSTTORNOOI_CATEGORIE'";

        $db->Query($sqlStat);

        while ($taRec = $db->Row() ){

            $categorie = $taRec->taCode;

            $aantalPlaatsen = self::GetAantalPlaatsen($categorie);

            if ($aantalPlaatsen <= 0) {

                $sqlStat = "Update sx_ta_tables set taRecStatus = 'H' where taTable = 'KERSTTORNOOI_CATEGORIE' and taCode = '$categorie' ";
                $db2->Query($sqlStat);

            } else {

                $sqlStat = "Update sx_ta_tables set taRecStatus = 'A' where taTable = 'KERSTTORNOOI_CATEGORIE' and taCode = '$categorie' ";
                $db2->Query($sqlStat);


            }


        }

    }

    // ========================================================================================
    // Functie: Ophalen aantal plaatsen HTML
    //
    // In: Geen
    //
    // Return: HTML snippet
    // ========================================================================================

    static function GetAantalPlaatsenHTML() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from sx_ta_tables where taTable = 'KERSTTORNOOI_CATEGORIE' order by taSort";

        $db->Query($sqlStat);

        $html = "<fieldset><legend>Open plaatsen</legend>";
        $i = 0;

        while ($taRec = $db->Row()){

            $i++;

            $categorie = $taRec->taCode;

            $aantal = self::GetAantalPlaatsen($categorie);

            if ($i > 1)
                $html .=  ", ";

            if ($aantal)
                $html .= "<b>$categorie</b>: $aantal plaatsen";
            else
                $html .= "<b>$categorie</b>: <span style='color:red; font-weight: bold'>VOLZET</span>";

        }


        $html .= "</fieldset>";

        // -------------
        // Einde functie
        // -------------

        return $html;



    }
        
} // End class
       
?>