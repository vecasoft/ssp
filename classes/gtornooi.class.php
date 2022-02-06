<?php 
  
class SSP_gtornooi{ // define the class

    // ========================================================================================
    // Functie: Inschrijving: Aanmaken detail-records
    //
    // In: Inschrijving ID
    //
        // ========================================================================================
                         
    static function CrtInschrijvingDetail($pInschrijving) {
    
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from ssp_gtornooi where id = $pInschrijving";
        $db->Query($sqlStat);

        if (! $hrRec = $db->Row())
            return;

        $cats = array();
        $nivs = array();
        $aantalSpelers = array();

        if ($hrRec->ploeg1Cat) {
            $cats[] = $hrRec->ploeg1Cat;
            $nivs[] =$hrRec->ploeg1Niv;
            $aantalSpelers[] = $hrRec->ploeg1AantalSpelers;
        }
        if ($hrRec->ploeg2Cat) {
            $cats[] = $hrRec->ploeg2Cat;
            $nivs[] =$hrRec->ploeg2Niv;
            $aantalSpelers[] = $hrRec->ploeg2AantalSpelers;
        }
        if ($hrRec->ploeg3Cat) {
            $cats[] = $hrRec->ploeg3Cat;
            $nivs[] =$hrRec->ploeg3Niv;
            $aantalSpelers[] = $hrRec->ploeg3AantalSpelers;
        }
        if ($hrRec->ploeg4Cat) {
            $cats[] = $hrRec->ploeg4Cat;
            $nivs[] =$hrRec->ploeg4Niv;
            $aantalSpelers[] = $hrRec->ploeg4AantalSpelers;
        }
        if ($hrRec->ploeg5Cat) {
            $cats[] = $hrRec->ploeg5Cat;
            $nivs[] =$hrRec->ploeg5Niv;
            $aantalSpelers[] = $hrRec->ploeg5AantalSpelers;
        }
        if ($hrRec->ploeg6Cat) {
            $cats[] = $hrRec->ploeg6Cat;
            $nivs[] =$hrRec->ploeg6Niv;
            $aantalSpelers[] = $hrRec->ploeg6AantalSpelers;
        }

        foreach ($cats as $key => $cat) {

            $niv = $nivs[$key];
            $aantal = $aantalSpelers[$key];

            $values = array();

            $values["headerId"] = MySQL::SQLValue($pInschrijving, MySQL::SQLVALUE_NUMBER);

            $values["categorie"] = MySQL::SQLValue($cat);
            $values["niveaAB"] = MySQL::SQLValue($niv);
            $values["aantalSpelers"] = MySQL::SQLValue($aantal, MySQL::SQLVALUE_NUMBER);

            $id = $db->InsertRow("ssp_gtornooi_teams", $values);

        }

        // -------------
        // Einde functie
        // -------------

    }

        
} // End class
       
?>