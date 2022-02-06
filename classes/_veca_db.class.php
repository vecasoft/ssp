<?php 

class VECA_db { // define the class

    // ========================================================================================
    // Get VECA Klant Record
    //
    // In:	Klant ID
    //
    // Return: klRec
    // ========================================================================================

    static function Get_VECA_klRec($pKlant) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from veca_klanten where klId = $pKlant";
        $db->Query($sqlStat);

        if ($klRec = $db->Row())
            return $klRec;
        else
            return null;

    }

    // ========================================================================================
    // Get VECA Factuur-detail Record
    //
    // In:	Uitgaande Factuur-detail ID
    //
    // Return: udRec
    // ========================================================================================

    static function Get_VECA_udRec($pUitgaandeFactuurDetail) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from veca_ud_uitgaande_factuur_detail where udId = $pUitgaandeFactuurDetail";
        $db->Query($sqlStat);

        if ($udRec = $db->Row())
            return $udRec;
        else
            return null;

    }

    // ========================================================================================
    // Get VECA Factuur Record
    //
    // In:	Uitgaande Factuur ID
    //
    // Return: ufRec
    // ========================================================================================

    static function Get_VECA_ufRec($pUitgaandeFactuur) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from veca_uf_uitgaande_facturen where ufId = $pUitgaandeFactuur";
        $db->Query($sqlStat);

        if ($ufRec = $db->Row())
            return $ufRec;
        else
            return null;

    }



    // -----------
    // Einde CLASS
    // -----------

}

?>