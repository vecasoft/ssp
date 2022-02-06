<?php

class SSP_eppa
{ // define the class

    // ========================================================================================
    // Ophalen URL's documenten tussenkomst mutualiteit (voorbije 3 jaren)
    //
    // In:	Persoon
    //      Mutualiteit (*CM, *OZ, ...)
    //      Session
    //      Type (*MUTUALITEITEN_VB of *MUTUALITEITEN_T)
    //
    // ========================================================================================

    static function GetMutuHTML($pPersoon, $pMutualiteit, $pSession, $pType = '*MUTUALITEITEN_VB') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("settings.class"));

        $taRec = SSP_db::Get_SX_taRec("EPPA_MUTUALITEITEN", $pMutualiteit);
        $mutNaam = $taRec->taName;

        if ($pType != '*MUTUALITEITEN_T') {

            $sqlStat = "Select * from ssp_vs order by vsCode desc limit 4";
            $db->Query($sqlStat);

            $html = "<ul>";

            while ($vsRec = $db->Row()) {

                $seizoen = $vsRec->vsCode;

                $url = "/eppa_document_mutualiteit.php?seid=$pSession&mut=$pMutualiteit&seizoen=$seizoen";
                $link = "<a href='$url' target='_blank'>$mutNaam - Seizoen $seizoen</a>";

                $html .= "<li>$link</li>";
            }

            $html .= "</ul>";
        }

        if ($pType == '*MUTUALITEITEN_T') {

            $url = "/eppa_document_mutualiteit_tennis.php?seid=$pSession&mut=$pMutualiteit";
            $link = "<a href='$url' target='_blank'>Document $mutNaam</a>";

            $html .= "<li>$link</li>";

        }

        // -------------
        // Einde functie
        // -------------

        return $html;




    }

    // -----------
    // Einde CLASS
    // -----------


}



?>