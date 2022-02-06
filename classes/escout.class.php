<?php 

     class SSP_escout { // define the class

         // ========================================================================================
         // Function: Aanmaken eerste scouting-detail
         //
         // In:	Scouting
         //
         // OUt: None
         // ========================================================================================

         static function CrtEersteScoutDetail($pScouting){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // ---------------------------------------------
             // Niet als er reeds een scouting-detail bestaat
             // ---------------------------------------------

             $sqlStat = "Select count(*) as aantal from escout_sd_scouting_detail where sdScouting = $pScouting";
             $db->Query($sqlStat);

             if ($sdRec = $db->Row())
                 if ($sdRec->aantal > 0)
                     return;

             // -----------------------
             // Ophalen scouting header
             // -----------------------

             $sqlStat = "Select * from escout_sc_scouting where scId = $pScouting";
             $db->Query($sqlStat);

             if (! $scRec  = $db->Row())
                 return;

             // ------------------------------
             // Aanmaken eerste scouting detail
             // -------------------------------

             $values = array();

             $values["sdScouting"] = MySQL::SQLValue($pScouting, MySQL::SQLVALUE_NUMBER);
             $values["sdDatum"] = MySQL::SQLValue($scRec->scEersteScoutingDatum, MySQL::SQLVALUE_DATE);

             $values["sdDatumCreatie"] = MySQL::SQLValue($scRec->scDatumCreatie, MySQL::SQLVALUE_DATETIME);
             $values["sdDatumUpdate"] = MySQL::SQLValue($scRec->scDatumUpdate, MySQL::SQLVALUE_DATETIME);

             $values["sdUserCreatie"] = MySQL::SQLValue($scRec->scUserCreatie, MySQL::SQLVALUE_TEXT);
             $values["sdUserUpdate"] = MySQL::SQLValue($scRec->scUserUpdate, MySQL::SQLVALUE_TEXT);

             $id = $db->InsertRow("escout_sd_scouting_detail", $values);

             // -------------
             // Einde functie
             // -------------

             return;

         }

         // ========================================================================================
         // Function: Test of delete scouting toegestaan (als geen detail)
         //
         // In:	Scouting
         //
         // Return: Delete toegestaan?
         // ========================================================================================

         static function ChkDelete($pScouting){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             // ---------------------------------------------
             // Niet als er reeds een scouting-detail bestaat
             // ---------------------------------------------

             $sqlStat = "Select count(*) as aantal from escout_sd_scouting_detail where sdScouting = $pScouting";
             $db->Query($sqlStat);

             if ($sdRec = $db->Row())
                 if ($sdRec->aantal > 0)
                     return false;

             // -------------
             // Einde functie
             // -------------

             return true;

         }


         // -----------
         // EINDE CLASS
         // ----------


 	}      
?>