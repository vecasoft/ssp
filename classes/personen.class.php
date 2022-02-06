<?php 

class SSP_personen
{ // define the class

    // ========================================================================================
    // Function: Get adRec
    //
    // In:	- code (bv. gverhelst)
    //
    // Return: adRec
    // ========================================================================================

    static function GetAdRec($pCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ad where adCode = '$pCode'";

        if (!$db->Query($sqlStat))
            return null;

        if (!$adRec = $db->Row())
            return null;

        return $adRec;

    }

    // ========================================================================================
    // Function: Get adRec
    //
    // In:	- code (bv. gverhelst)
    //
    // Return: adRec
    // ========================================================================================

    static function db_adRec($pCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ad where adCode = '$pCode'";

        if (!$db->Query($sqlStat))
            return null;

        if (!$adRec = $db->Row())
            return null;

        return $adRec;

    }

    // ===================================================================================================
    // Functie: Get contact-formullier Record
    //
    // In:	- contactformulier ID
    //
    // Uit:	- cfRec
    //
    // ===================================================================================================

    Static function db_cfRec($pCfId) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

        $sqlStat = "Select * from ssp_cf_contactformulier where cfId = $pCfId";

        if (!$db->Query($sqlStat)) {
            $db->close();
            return null;
        }

        if (!$cfRec = $db->Row()) {
            $db->close();
            return null;
        } else {
            $db->close();
            return $cfRec;
        }

    }

    // ========================================================================================
    // Function: Get naam
    //
    // In:	- code (bv. gverhelst)
    //
    // Return: Voornaam + naam
    // ========================================================================================

    static function GetNaam($pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if (! $pPersoon)
            return null;

        $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";
        $db->Query($sqlStat);

        if ($adRec = $db->Row())
            return $adRec->adVoornaamNaam;

        $sqlStat = "Select * from sx_us_users where usUserId = '$pPersoon'";
        $db->Query($sqlStat);

        if ($usRec = $db->Row())
            return $usRec->usName;

        // -------------
        // Einde functie
        // -------------

        return null;


    }

    // ========================================================================================
    // Function: Get Tel + Mail
    //
    // In:	- code (bv. gverhelst)
    //
    // Return: Tel & Mail
    // ========================================================================================

    static function GetContactInfo($code) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($code <= " ")
            return '&nbsp;';

        $query = 'Select * from ssp_ad where adCode = "' . $code . '"';

        if (!$db->Query($query))
            return $query;

        if (!$adRec = $db->Row())
            return $query;

        $mail = '&nbsp;';

        if ($adRec->adMail)
            $mail = '<a href="mailto:' . $adRec->adMail . '">' . $adRec->adMail . '</a>';


        return '<b>' . $adRec->adVoornaam . '&nbsp;' . $adRec->adNaam . '</b><br/><br/>'
            . 'Tel.: ' . $adRec->adTel . '<br/>'
            . 'Mail: ' . $mail;

    }

    // ========================================================================================
    // Function: Get Tel
    //
    // In:	- code (bv. gverhelst)
    //
    // Return: Tel
    // ========================================================================================

    static function GetTel($code) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($code <= " ")
            return '&nbsp;';

        $query = 'Select * from ssp_ad where adCode = "' . $code . '"';

        if (!$db->Query($query))
            return $query;

        if (!$adRec = $db->Row())
            return $query;

        if ($adRec->adTel > ' ') {
            return $adRec->adTel;
        } else {
            return '&nbsp;';
        }

    }

    // ========================================================================================
    // Function: Get Mail link
    //
    // In:	- code (bv. gverhelst)
    //		- mailOrName = Afbeelden mailadres of naam
    //
    // Return: Mail
    // ========================================================================================

    static function GetMail($code, $mailOrName = '*MAIL') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($code <= " ")
            return '&nbsp;';

        $query = 'Select * from ssp_ad where adCode = "' . $code . '"';

        if (!$db->Query($query))
            return $query;

        if (!$adRec = $db->Row())
            return $query;

        $mail = '&nbsp;';

        if ($adRec->adMail && $mailOrName == '*MAIL')
            $mail = '<a href="mailto:' . $adRec->adMail . '">' . $adRec->adMail . '</a>';
        if ($adRec->adMail && $mailOrName != '*MAIL')
            $mail = '<a href="mailto:' . $adRec->adMail . '">' . $adRec->adVoornaam . '&nbsp;' . $adRec->adNaam . '</a>';

        return $mail;

    }
    // ========================================================================================
    // Function: Get Functies Voetbal - Omschrijvingen
    //
    // In:	- pFunctieVB = String met functie-codes
    //
    // Return: String met functies
    // ========================================================================================

    static function GetOmschrijvingFunctieVB($pFunctieVB) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($pFunctieVB <= " ")
            return '&nbsp;';

        $query = "Select * from ssp_fv";

        if (!$db->Query($query))
            return $query;

        $omschrijvingFunctieVB = "";

        while ($fvRec = $db->Row()) {

            $pos = strpos($pFunctieVB, $fvRec->fvCode);

            if ($pos !== false) {

                if ($omschrijvingFunctieVB > " ")
                    $omschrijvingFunctieVB .= ', ' . $fvRec->fvNaam;
                else
                    $omschrijvingFunctieVB = $fvRec->fvNaam;

            }

        }

        return $omschrijvingFunctieVB;

    }
    // ========================================================================================
    // Function: Get Functies Schelle Sport - Omschrijvingen
    //
    // In:	- pFunctieSSP = String met functie-codes
    //
    // Return: String met functies
    // ========================================================================================

    static function GetOmschrijvingFunctieSSP($pFunctieSSP) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($pFunctieSSP <= " ")
            return '&nbsp;';

        $query = "Select * from ssp_fs";

        if (!$db->Query($query))
            return $query;

        $omschrijvingFunctieSSP = "";

        while ($fsRec = $db->Row()) {

            $pos = strpos($pFunctieSSP, $fsRec->fvCode);

            if ($pos !== false) {

                if ($omschrijvingFunctieSSP > " ")
                    $omschrijvingFunctieSSP .= ', ' . $fsRec->fvNaam;
                else
                    $omschrijvingFunctieSSP = $fsRec->fvNaam;

            }

        }

        return $omschrijvingFunctieSSP;

    }
    // ========================================================================================
    // Function: Get Omschrijving MEDEWERKER
    //
    // In:	- pMedewerkerType = String met medewerker-codes
    //
    // Return: String met type(s) medewerker
    // ========================================================================================

    static function GetOmschrijvingMW($pMedewerkerType) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($pMedewerkerType <= " ")
            return '&nbsp;';

        $query = "Select * from sx_ta_tables where taTable = 'AD_MEDEWERKER_TYPE'";

        if (!$db->Query($query))
            return $query;

        $omschrijvingMW = "";

        while ($taRec = $db->Row()) {

            $pos = strpos($pMedewerkerType, $taRec->taCode);

            if ($pos !== false) {

                if ($omschrijvingMW > " ")
                    $omschrijvingMW .= ', ' . $taRec->taName;
                else
                    $omschrijvingMW = $taRec->taName;

            }

        }

        return $omschrijvingMW;

    }

    // ========================================================================================
    // Function: Mag contact gewist worden?
    //
    // In:	- Code = Contact code (vb gverhelst)
    //
    // Return: *OK of reden waarom niet mag gewist worden
    // ========================================================================================

    static function MagGewist($pCode) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $message = "*OK";

        // ----------------------
        // Check EBA bestellingen
        // ----------------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from eba_oh_order_headers where ohKlant = '$pCode'";
            $db->Query($sqlStat);
            If ($ohRec = $db->Row())
                if ($ohRec->aantal > 0)
                    $message = "Er zijn EBA bestellingen voor deze persoon";

        }

        // --------------------
        // Check EVA evaluaties
        // --------------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from eva_eh_evaluatie_headers where ehPersoon = '$pCode'";

            $db->Query($sqlStat);

            If ($ehRec = $db->Row())
                if ($ehRec->aantal > 0)
                    $message = "Er zijn EVA evaluaties voor deze persoon";

        }

        // -----------------------
        // Check ERA aanwezigheden
        // -----------------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from ssp_twbs_aw where awSpeler = '$pCode'";

            $db->Query($sqlStat);

            If ($awRec = $db->Row())
                if ($awRec->aantal > 0)
                    $message = "Er zijn ERA aanwezigheden voor deze persoon";

        }

        // ----------------------
        // Check ERA afwezigheden
        // ----------------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from ssp_twbs_af where afPersoon = '$pCode'";

            $db->Query($sqlStat);

            If ($afRec = $db->Row())
                if ($afRec->aantal > 0)
                    $message = "Er zijn ERA afwezigheden voor deze persoon";

        }

        // -----------------
        // Check ERA ploegen
        // -----------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from ssp_vp_sp where spPersoon = '$pCode'";

            $db->Query($sqlStat);

            If ($spRec = $db->Row())
                if ($spRec->aantal > 0)
                    $message = "Deze persoon is in ERA toegewezen aan een ploeg";

        }

        // ----------------
        // Check Namenlijst
        // ----------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from ssp_nl where nlPersoon = '$pCode'";

            $db->Query($sqlStat);

            If ($nlRec = $db->Row())
                if ($nlRec->aantal > 0)
                    $message = "Deze persoon is in minstens één namenlijst";

        }

        // ----------------
        // Check Documenten
        // ----------------

        if ($message == '*OK') {

            $sqlStat = "Select count(*) as aantal from ssp_cd_contact_documenten where cdCode = '$pCode'";

            $db->Query($sqlStat);

            If ($cdRec = $db->Row())
                if ($cdRec->aantal > 0)
                    $message = "Aan deze persoon is er minstens één document gekoppeld";

        }

        // Einde functie
        return $message;


    }

    // ========================================================================================
    // Function: Ophalen contactgevens (HTML)
    //
    // In:	- Code = Contact code (vb gverhelst)
    // In:	- Type = *ALL=Alles, *ADRES, *MAIL, *TEL
    //
    // Return: Contactgegevens in HTML
    // ========================================================================================

    static function GetContactDataHTML($pCode, $pType = '*ALL') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $HTML = "";

        $sqlStat = "Select * from ssp_ad where adCode = '$pCode'";

        if (!$db->Query($sqlStat)) {
            $db->close();
            return $HTML;
        }

        if (!$adRec = $db->Row()) {
            $db->close();
            return $HTML;
        }

        $voorNaam = $adRec->adVoornaam;

        if ($pType == '*ALL' or $pType == '*ADRES') {

            $HTML .= $adRec->adAdres1;
            if ($adRec->adAdres2 > ' ')
                $HTML .= "<br/>$adRec->adAdres2";
            if ($adRec->adGemeente > ' ')
                $HTML .= "<br/>$adRec->adPostnr&nbsp;$adRec->adGemeente<br/>";

        }

        if ($pType == '*ALL' or $pType == '*MAIL') {

            if ($HTML > ' ')
                $HTML .= "<br/>";


            if ($adRec->adMail > ' ') {

                $mailFrom = '';

                if ($adRec->adMoederMailBasis == 1)
                    $mailFrom = 'Moeder:';
                if ($adRec->adVaderMailBasis == 1)
                    $mailFrom = 'Vader:';
                if ($adRec->adSpelerMailBasis == 1)
                    $mailFrom = "$voorNaam:";

                $HTML .= "$mailFrom <a href='mailto: $adRec->adMail'>$adRec->adMail</a>";

            }


            if ($adRec->adVaderMail > ' ') {

                $mailFrom = 'Vader:';

                $HTML .= "<br/>$mailFrom <a href='mailto: $adRec->adVaderMail'>$adRec->adVaderMail</a>";

            }

            if ($adRec->adMoederMail > ' ') {

                $mailFrom = 'Moeder:';

                $HTML .= "<br/>$mailFrom <a href='mailto: $adRec->adMoederMail'>$adRec->adMoederMail</a>";

            }


            if ($adRec->adSpelerMail > ' ') {

                $mailFrom = "$voorNaam:";

                $HTML .= "<br/>$mailFrom <a href='mailto: $adRec->adSpelerMail'>$adRec->adSpelerMail</a>";

            }


        }


        if ($pType == '*ALL' or $pType == '*TEL') {

            if ($HTML > ' ')
                $HTML .= '<br/>';

            if ($adRec->adTel > ' ') {

                $telFrom = '';

                if ($adRec->adMoederTelBasis == 1)
                    $telFrom = 'Moeder:';
                if ($adRec->adVaderTelBasis == 1)
                    $telFrom = 'Vader:';
                if ($adRec->adSpelerTelBasis == 1)
                    $telFrom = "$voorNaam:";

                $HTML .= "$telFrom $adRec->adTel";

            }

            if ($adRec->adVaderTel > ' ')
                $HTML .= "<br/>Vader: $adRec->adVaderTel";

            if ($adRec->adMoederTel > ' ')
                $HTML .= "<br/>Moeder: $adRec->adMoederTel";

            if ($adRec->adSpelerTel > ' ')
                $HTML .= "<br/>$voorNaam: $adRec->adSpelerTel";
        }


        $db->close();
        Return $HTML;


    }


    // ===================================================================================================
    // Functie: Get Documenten (in HTML formaat)
    //
    // In: 	- Contact
    //		- SessionId
    // ===================================================================================================

    static function GetDocumentenHTML($pContact, $pSession) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        include_once(SX::GetSxClassPath("tools.class"));
        include_once(SX::GetSxClassPath("tables.class"));
        include_once(SX::GetClassPath("settings.class"));

        $blue = SSP_settings::GetBackgroundColor('blue');
        $green = SSP_settings::GetBackgroundColor('green');

        $sqlStat = "Select * from ssp_cd_contact_documenten left outer join  sx_ta_tables on taTable = 'AD_DOC_TYPE' and taCode = cdDocType where cdCode = '$pContact' and cdRecStatus = 'A' order by cdCode, taSort, cdDocType, cdId desc";

        if (!$db->Query($sqlStat))
            return "*NONE";

        $HTML = "*";

        while ($cdRec = $db->Row()) {

            $leesRechten = $cdRec->cdLeesRechten;

            if (!self::CheckDocLeesRechten($leesRechten, $pSession))
                continue;

            if ($HTML == "*")
                $HTML = "";

            if ($HTML == "")
                $HTML = "<table style='font-family: Arial, Helvetica, Sans-Serif; font-size: 14px;'><tr><th style='border: 1px solid blue; padding: 5px; text-align: center; background-color: $blue; color: black'>Document</th><th style='border: 1px solid blue; padding: 5px;text-align: center; background-color: $blue; color: black'>Type</th></tr>";

            $HTML .= "<tr'>";

            $url = "#";
            $naam = $cdRec->cdNaam;
            $docType = $cdRec->cdDocType;
            $docTypeDesc = SX_tables::GetDesc('AD_DOC_TYPE', $docType);

            $documents = json_decode($cdRec->cdDocument);

            foreach ($documents as $document) {
                $path = SX_tools::GetFilePath($document->name);
                $fileName = SX_tools::GetFilePath($document->usrName);
            }

            $type = pathinfo($path, PATHINFO_EXTENSION);
            $image = SX::GetDocImage($type);

            $HTML .= "<td style='border: 1px solid blue; padding: 5px;'><a href='$path' target='_blank' style='color: blue; text-decoration: none;'><div style='float: left'>$image</div><div style='float: left; margin-left: 10px;padding-top: 8px'>$naam</div</a></td>";

            $HTML .= "<td style='border: 1px solid blue; padding: 5px;text-align: center'>$docTypeDesc</td>";

            $HTML .= "</tr>";


        }

        if ($HTML != "*")
            $HTML .= "</table>";

        if ($HTML == "*")
            $HTML = "*NONE";

        // -------------
        // Functie einde
        // -------------

        $db->close();
        return $HTML;

    }

    // ===================================================================================================
    // Functie: Check Documenten
    //
    // In: 	- Contact
    //		- SessionId
    // ===================================================================================================

    static function CheckDocumenten($pContact, $pSession) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        include_once(SX::GetSxClassPath("tools.class"));

        $sqlStat = "Select * from ssp_cd_contact_documenten where cdCode = '$pContact' and cdRecStatus = 'A' order by cdDocType, cdId desc";

        if (!$db->Query($sqlStat))
            return false;

        $return = false;

        while ($cdRec = $db->Row()) {

            $leesRechten = $cdRec->cdLeesRechten;

            if (!self::CheckDocLeesRechten($leesRechten, $pSession))
                continue;

            $return = true;
            break;


        }

        // -------------
        // Functie einde
        // -------------

        $db->close();
        return $return;

    }

    // ===================================================================================================
    // Functie: Document Leesrechten?
    //
    // In: 	- LeesRechten
    //		- SessionId
    // ===================================================================================================

    static function CheckDocLeesRechten($pLeesRechten, $pSessionId) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

        include_once(SX::GetSxClassPath("sessions.class"));

        $userId = SX_sessions::GetSessionUserId($pSessionId);

        if ($userId == "*NONE")
            return false;

        if ($userId == 'webmaster')
            return true;

        if ($userId == 'ehoefkens')
            return true;

        // ------------------------
        // Sportieve staf & bestuur
        // ------------------------

        if ($pLeesRechten == '*SPORTIEVE_STAF') {

            $sqlStat = "Select count(*) as aantal from ssp_ad where adCode = '$userId' and (adFunctieVB LIKE '%bestuur%' or adFunctieVB like '%sp.staf%') and adRecStatus = 'A'";

            if ($db->Query($sqlStat))
                if ($adRec = $db->Row())
                    if ($adRec->aantal >= 1)
                        return true;

        }

        // ---------------------------
        // Trainers (& afgevaardugden)
        // ---------------------------

        if ($pLeesRechten == '*TRAINERS') {

            $sqlStat = "Select count(*) as aantal from ssp_ad where adCode = '$userId' and (adFunctieVB LIKE '%bestuur%' or adFunctieVB like '%sp.staf%' or adFunctieVB LIKE '%trainer%' or adFunctieVB LIKE '%afgev%') and adRecStatus = 'A'";

            if ($db->Query($sqlStat))
                if ($adRec = $db->Row())
                    if ($adRec->aantal >= 1)
                        return true;

        }

        // ----------------------------------------------
        // Else: Specifieke leesrechten (EVA authority) ?
        // ----------------------------------------------

        $sqlStat = "Select count(*) as aantal from eva_sp_security_per_persoon where spPersoon = 'pPersoon' and spUser = '$userId'";

        if ($db->Query($sqlStat))
            if ($spRec = $db->Row())
                if ($spRec->aantal >= 1)
                    return true;

        // ------------------
        // Else: geen rechten
        // ------------------

        return false;


    }

    // ===================================================================================================
    // Functie: Opvullen/aanvullen MAIL-LIST bestand
    //
    // In: 		- Mail Groep (bv. *U7)
    //			- Modus (*RUN, *CHECK)   Default = *RUN
    //
    // Return:	- Records added?
    //
    // ===================================================================================================

    static function FillMailListFile($pMailGroep, $pModus = '*RUN') {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetSxClassPath("auth.class"));

        // -------------
        // Get mailgroep
        // -------------

        $sqlStat = "Select * from ssp_mg_mail_groepen where mgMailGroep = '$pMailGroep'";
        $db->Query($sqlStat);

        if (!$mgRec = $db->Row())
            return false;

        $role1 = $mgRec->mgRole1;
        if (! $role1)
            $role1 = '*NONE';

        $role2 = $mgRec->mgRole2;
        if (! $role2)
            $role2 = '*NONE';

        $role3 = $mgRec->mgRole3;
        if (! $role3)
            $role3 = '*NONE';

        $runNumber = $mgRec->mgLaatsteRun + 1;
        $laatsteRunAantal = 0;
        $laatsteRunAantalNieuw = 0;
        $laatsteRunAantalGewist = 0;

        $sqlStat = "Update ssp_ml_mail_list set mlCheckRun = -1 where mlMailGroep = '$pMailGroep'";
        $db->Query($sqlStat);

        // -----------------
        // Find all contacts
        // -----------------

        $somethingChanged = false;

        $sqlStat = "Select * from ssp_ad inner join sx_rp_role_personen on sx_rp_role_personen.rpPersoon = ssp_ad.adCode and (sx_rp_role_personen.rpRole = '$role1' or sx_rp_role_personen.rpRole = '$role2' or sx_rp_role_personen.rpRole = '$role3') where adRecStatus = 'A'";

        $db->Query($sqlStat);

        while ($adRec = $db->Row()) {

            $code = $adRec->adCode;

            $mailArr = array();

            if (trim($adRec->adMail) > ' ')
                $mailArr[] = $adRec->adMail;

            if ($mgRec->mgEnkelHoofdMail <> 1) {

                if (trim($adRec->adVaderMail) > ' ')
                    $mailArr[] = $adRec->adVaderMail;

                if (trim($adRec->adMoederMail) > ' ')
                    $mailArr[] = $adRec->adMoederMail;

                if (trim($adRec->adSpelerMail) > ' ')
                    $mailArr[] = $adRec->adSpelerMail;
            }

            foreach ($mailArr as $mail) {

                $sqlStat = "Select count(*) as aantal from ssp_ml_mail_list where mlMailGroep = '$pMailGroep' and mlMail = '$mail' and mlRecStatus = 'A' ";
                $db2->Query($sqlStat);

                $mlRec = $db2->Row();

                if ($mlRec->aantal <= 0) {

                    $sqlStat = "Delete from ssp_ml_mail_list where mlMailGroep = '$pMailGroep' and mlMail = '$mail' ";
                    $db3->Query($sqlStat);

                    $laatsteRunAantalNieuw++;

                    if ($pModus == '*RUN') {

                        $curDateTime = date('Y-m-d H:i:s');

                        $values = array();

                        $values["mlMailGroep"] = MySQL::SQLValue($pMailGroep);
                        $values["mlMail"] = MySQL::SQLValue($mail);
                        $values["mlCode"] = MySQL::SQLValue($code);

                        $values["mlCreatieRun"] = MySQL::SQLValue($runNumber, MySQL::SQLVALUE_NUMBER);
                        $values["mlUpdateRun"] = MySQL::SQLValue($runNumber, MySQL::SQLVALUE_NUMBER);
                        $values["mlCheckRun"] = MySQL::SQLValue($runNumber, MySQL::SQLVALUE_NUMBER);

                        $values["mlCreatieDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                        $values["mlUpdateDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                        $values["mlRecStatus"] = MySQL::SQLValue('A');

                        $db3->InsertRow("ssp_ml_mail_list", $values);

                    }

                    $somethingChanged = true;


                }

                else {

                    if ($pModus == '*RUN') {

                        $sqlStat = "update ssp_ml_mail_list set mlUpdateRun = $runNumber,  mlCheckRun = $runNumber, mlDeleteRun = 0,  mlUpdateDatum = now(), mlRecStatus = 'A' where mlMailGroep = '$pMailGroep' and mlMail = '$mail'";

                        $db3->Query($sqlStat);
                    }

                    if ($pModus == '*CHECK') {

                        $sqlStat = "update ssp_ml_mail_list set mlCheckRun = $runNumber where mlMailGroep = '$pMailGroep' and mlMail = '$mail'";

                        $db3->Query($sqlStat);
                    }

                }

            }


        }

        // --------
        // Delete ?
        // --------

        sleep(5);


        if ($pModus == '*RUN')
            $sqlStat = "Select count(*) as aantal from ssp_ml_mail_list where mlMailGroep = '$pMailGroep' and mlUpdateRun <> $runNumber and mlRecStatus = 'A' and mlCode > ' '";


        if ($pModus == '*CHECK')
            $sqlStat = "Select count(*) as aantal from ssp_ml_mail_list where mlMailGroep = '$pMailGroep' and mlCheckRun <> $runNumber and mlRecStatus = 'A' and mlCode > ' '";


        $db3->Query($sqlStat);

        $mlRec = $db3->Row();

        $laatsteRunAantalGewist = $mlRec->aantal;

        if ($laatsteRunAantalGewist > 0) {

            if ($pModus == '*RUN') {

                $sqlStat = "Update ssp_ml_mail_list set mlDeleteRun = $runNumber, mlDeleteDatum = now(), mlRecStatus = 'H' where mlMailGroep = '$pMailGroep' and mlCheckRun <> $runNumber and mlRecStatus = 'A' and mlCode > ' '";

                $db3->Query($sqlStat);
            }

            $somethingChanged = true;

        }

        // -----------------
        // Update Run number
        // -----------------

        if ($pModus == '*RUN') {

            $sqlStat = "select count(*) as aantal from ssp_ml_mail_list where mlMailGroep = '$pMailGroep' and mlRecStatus = 'A'";
            $db->Query($sqlStat);

            $mlRec = $db->Row();

            $laatsteRunAantal = $mlRec->aantal;


            $sqlStat = "Update ssp_mg_mail_groepen set mgWijzigingen = 0, mgLaatsteRun = $runNumber, mgLaatsteRunDatum = now(),mgLaatsteRunAantal= $laatsteRunAantal, mgLaatsteRunAantalNieuw = $laatsteRunAantalNieuw, mgLaatsteRunAantalGewist = $laatsteRunAantalGewist where mgMailGroep = '$pMailGroep'";

            $db->Query($sqlStat);
        }

        if (($pModus == '*CHECK') and ($somethingChanged == true)) {

            $sqlStat = "Update ssp_mg_mail_groepen set mgWijzigingen = 1 where mgMailGroep = '$pMailGroep'";

            $db->Query($sqlStat);
        }

        // ------------
        // End function
        // ------------

        return $somethingChanged;

    }
    // ===================================================================================================
    // Functie: Opvullen/aanvullen MAIL-LIST bestand
    //
    // In: 		- Mail Groep (bv. *U7)
    //			- Modus (*RUN, *CHECK)   Default = *RUN
    //
    // Return:	- Records added?
    //
    // ===================================================================================================

    static function SetMailGroepAantal($pMailGroep) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...


        $sqlStat = "select count(*) as aantal from ssp_ml_mail_list where mlMailGroep = '$pMailGroep' and mlRecStatus = 'A'";
        $db->Query($sqlStat);

        $mlRec = $db->Row();

        $aantal = $mlRec->aantal;

        $sqlStat = "Update ssp_mg_mail_groepen set mgLaatsteRunAantal = $aantal where mgMailGroep = '$pMailGroep'";
        $db->Query($sqlStat);

    }

    // ===================================================================================================
    // Functie: Opvullen/aanvullen MAIL-LIST bestand
    //
    // In: 		update if needed?
    //
    // Return:	None
    //
    // ===================================================================================================

    static function CheckMailLists($pUpdateIfNeeded = false) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

        $sqlStat = "Select * from ssp_mg_mail_groepen where mgRecStatus = 'A'";

        $db->Query($sqlStat);

        while ($mgRec = $db->Row()) {

            $mailGroep = $mgRec->mgMailGroep;

            $somethingChanged = self::FillMailListFile($mailGroep, "*CHECK");


            if ($somethingChanged && $pUpdateIfNeeded)
                self::FillMailListFile($mailGroep, "*RUN");

        }

    }


    // ===================================================================================================
    // Functie: Ophalen users gekoppeld aan mail-adres
    //
    // In: 		- Mail
    //
    // Return:	- Array met users
    //
    // ===================================================================================================

    static function GetMailUsers($pMail) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

        $userId = array();

        if ($pMail <= ' ')
            return $userId;

        $sqlStat = "Select * from sx_us_users inner join ssp_ad on adCode = usUserId and adRecStatus = 'A' where (adMail = '$pMail' or adSpelerMail = '$pMail' or adVaderMail = '$pMail' or adMoederMail = '$pMail') and adCode <> 'webmaster'";

        $db->Query($sqlStat);

        while ($usRec = $db->Row()) {

            $userId[] = $usRec->usUserId;

        }

        return $userId;

    }


    // ===================================================================================================
    // Functie: Wijzigen wachtwoord - MAIL
    //
    // In: 		- Cange Pass Id

    //
    // ===================================================================================================

    static function MailWijzigWachtwoord($pCpId) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        include_once(Sx::GetSxClassPath("tools.class"));

        $sqlStat = "Select * from sx_cp_change_pass_requests where cpId = $pCpId";

        $db->Query($sqlStat);

        if (!$cpRec = $db->Row())
            return;

        $mailTo = $cpRec->cpMail;
        $userId = $cpRec->cpUserId;
        $code = $cpRec->cpURLCode;

        // $url = "http://schellesport.be/sx_pass_change/sx_cp_change_pass_requests_edit.php?code=$code";
        $url = "<a href='"
            . "http://schellesport.be/sx_pass_change/sx_cp_change_pass_requests_edit.php?code=$code"
            . "'>Klik hier om wachtwoord opnieuw in te stellen</a>";


        // ----------
        // HTML Start
        // ----------

        $mailBody = "<!DOCTYPE html>";
        $mailBody .= "<html>";
        $mailBody .= "<head>";

        $mailBody .= "<style>";
        $mailBody .= "table, th, td { ";
        $mailBody .= " border: 1px solid black; ";
        $mailBody .= " border-collapse: collapse;";
        $mailBody .= "} ";
        $mailBody .= "th, td { ";
        $mailBody .= "  padding: 5px; ";
        $mailBody .= "  text-align: left;";
        $mailBody .= " } ";
        $mailBody .= "</style>";

        $mailBody .= "</head>";

        $mailBody .= "<body>" . "\r\n";

        $mailBody .= "Beste,<br/><br/>" . "\r\n";

        $mailBody .= "Je hebt aangegeven een nieuw wachtwoord te willen instellen voor login: <b>$userId</b>.<br/>" . "\r\n";
        $mailBody .= "Klik daarvoor op onderstaande link (deze link is 24 uur geldig).<br/>" . "\r\n";
        $mailBody .= "<b>Heeft u NIET gevraagd om een nieuw wachtwoord aan te maken? Negeer dan deze mail</b>." . "\r\n";
        $mailBody .= "<br/><br/>$url" . "\r\n";
        $mailBody .= "<br/><br/>Sportieve groet," . "\r\n";
        $mailBody .= "<br/><br/>De Webmeester" . "\r\n";

        // --------
        // End HTML
        // --------

        $mailBody .= "</body></html>" . "\r\n";


        // ---------
        // Send mail
        // ---------


        SX_tools::SendMail('Schelle Sport - Wijzigen wachtwoord ', $mailBody, $mailTo, "gvh@vecasoftware.com");


    }
    // ===================================================================================================
    // Functie: Wijzigen wachtwoord via MAIL
    //
    // In: 	- Cange Pass Id
    //
    // Out:	- Booschap (Indien fout)
    //
    // ===================================================================================================

    static function WijzigWachtwoordViaMail($pCpId, &$pBoodschap) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        include_once(Sx::GetSxClassPath("tools.class"));
        include_once(Sx::GetSxClassPath("sessions.class"));

        // ----------------
        // Ophalen "request"
        // -----------------

        $pBoodschap = '*';

        $sqlStat = "Select * From sx_cp_change_pass_requests where cpId = $pCpId";

        $db->Query($sqlStat);

        if (!$cpRec = $db->Row()) {
            $pBoodschap = "Code niet (meer) geldig!";
            return false;
        }


        // --------------------------
        // Valideren (extra security)
        // --------------------------

        $mail = $cpRec->cpMail;
        $userId = $cpRec->cpUserId;

        $users = self::GetMailUsers($mail);

        $geldigeUser = false;


        foreach ($users as $user) {
            if ($user == $userId)
                $geldigeUser = true;
        }

        if (!$geldigeUser) {
            $pBoodschap = "Code ongeldig voor deze gebruiker!";
            $sqlStat = "Update sx_cp_change_pass_requests set cpStatus = 'X' where cpId = $pCpId";
            $db->Query($sqlStat);
            return false;
        }


        // -------------------
        // Wijzigen wachtwoord
        // -------------------

        $userId = $cpRec->cpUserId;
        $passNew = $cpRec->cpPass;

        $passChanged = SX_sessions::SetUserPassword($userId, $passNew);


        if ($passChanged == true)
            $sqlStat = "Update sx_cp_change_pass_requests set cpPass = '', cpPass2 = '', cpStatus = 'H' where cpId = $pCpId";
        else
            $sqlStat = "Update sx_cp_change_pass_requests set cpPass = '', cpPass2 = '', cpStatus = 'E' where cpId = $pCpId";

        $db->Query($sqlStat);

        return $passChanged;

    }

    // ===================================================================================================
    // Functie: Aanmaken leden database record op basis van contact-formulier
    //
    // In: 	- contact formulier ID
    // 		- Code aan te maken
    // 		- Voetbal Categorie
    // 		- User ID
    //
    // Out:	- Fout-melding (*OK indien volledig OK)
    //
    // ===================================================================================================

    static function Crt_adRec_from_cfRec($pCfId, $pCode, $pVoetbalCat, $pUserId) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        include_once(Sx::GetClassPath("ela.class"));

        $cfRec = self::db_cfRec($pCfId);

        if ($cfRec == null)
            return "Onverwachte fout (cfRec bestaat niet)";

        // --------------------------------
        // Nieuwe code mag nog niet bestaan
        // --------------------------------

        $adRec = self::db_adRec($pCode);
        if ($adRec <> null)
            return "Code bestaat reeds";

        // -------------------------
        // Aanmaken record in ssp_ad
        // -------------------------

        $curDate = date('Y-m-d');

        $voornaam = $cfRec->cfVoornaam;
        $naam = $cfRec->cfNaam;
        $naamVoornaam = "$naam $voornaam";
        $voornaamNaam = "$voornaam $naam";

        $values["adCode"] = MySQL::SQLValue($pCode);
        $values["adVoornaam"] = MySQL::SQLValue($voornaam);
        $values["adNaam"] = MySQL::SQLValue($naam);

        $values["adNaamVoornaam"] = MySQL::SQLValue($naamVoornaam);
        $values["adVoornaamNaam"] = MySQL::SQLValue($voornaamNaam);

        $values["adOnvolledig"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);

        $values["adAdres1"] = MySQL::SQLValue($cfRec->cfStraat);
        $values["adPostnr"] = MySQL::SQLValue($cfRec->cfPostnummer);
        $values["adGemeente"] = MySQL::SQLValue($cfRec->cfGemeente);
        $values["adNationaliteit"] = MySQL::SQLValue('BE');
        $values["adTel"] = MySQL::SQLValue($cfRec->cfTel);
        $values["adMail"] = MySQL::SQLValue($cfRec->cfMail);
        $values["adRelatieMet"] = MySQL::SQLValue('V');

        if ($cfRec->cfContactType == 'Vader') {

            $values["adVaderMailBasis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
            $values["adVaderTelBasis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
            $values["adVaderVoornaam"] = MySQL::SQLValue($cfRec->cfContactVoornaam);
            $values["adVaderNaam"] = MySQL::SQLValue($cfRec->cfContactNaam);

        }

        if ($cfRec->cfContactType == 'Moeder') {

            $values["adMoederMailBasis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
            $values["adMoederTelBasis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
            $values["adMoederVoornaam"] = MySQL::SQLValue($cfRec->cfContactVoornaam);
            $values["adMoederNaam"] = MySQL::SQLValue($cfRec->cfContactNaam);

        }

        if ($cfRec->cfContactType == 'Speler') {

            $values["adSpelerMailBasis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);
            $values["adSpelerTelBasis"] = MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);

        }

        $values["adFunctieVB"] = MySQL::SQLValue('speler');
        $values["adGeboorteDatum"] = MySQL::SQLValue($cfRec->cfGeboortedatum, MySQL::SQLVALUE_DATE);
        $values["adGeboortePlaats"] = MySQL::SQLValue($cfRec->cfGeboorteplaats, MySQL::SQLVALUE_TEXT);

        if ($cfRec->cfGeboortedatum > 0) {

            $geboorteJaar = date('Y', strtotime($cfRec->cfGeboortedatum));
            $geboorteMaand = date('m', strtotime($cfRec->cfGeboortedatum));

            $values["adGeboorteJaar"] = MySQL::SQLValue($geboorteJaar, MySQL::SQLVALUE_NUMBER);
            $values["adGeboorteMaand"] = MySQL::SQLValue($geboorteMaand, MySQL::SQLVALUE_NUMBER);
        }

        $values["adLidgeldVoldaanVB"] = MySQL::SQLValue('PROEF');
        $values["adProefperiode"] = MySQL::SQLValue(1,MySQL::SQLVALUE_NUMBER );
        $values["adVoetbalCat"] = MySQL::SQLValue($pVoetbalCat);

        $randomString = substr(str_shuffle("23456789abcdefghjkmnprstuvwxyz"), 0, 5);
        $values['adLidkaartTempPass'] = MySQL::SQLValue($randomString);

        $values["adDatumCreatie"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
        $values["adDatumUpdate"] = MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);
        $values["adUserCreatie"] = MySQL::SQLValue('*CONTACT');
        $values["adUserUpdate"] = MySQL::SQLValue($pUserId);
        $values["adRecStatus"] = MySQL::SQLValue('A');

        $db->InsertRow("ssp_ad", $values);

        SSP_ela::ValBetalingLidgeldVoetbal($pCode);

        $sqlStat = "Update ssp_cf_contactformulier set cfCode = '$pCode', cfVoetbalCat = '$pVoetbalCat' where cfId = $pCfId";
        $db->Query($sqlStat);


        return "Record aangemaakt in ledenbestand";

    }

    // ===================================================================================================
    // Functie: Update status "aangesloten" in contactformulieren
    //
    // In: 	NONE
    //
    // Out:	NONE
    //
    // ===================================================================================================

    static function UpdStatAangeslotenContactFormulieren() {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from ssp_cf_contactformulier where cfCode > ' ' and cfIsAangesloten <> 1";

        $db->Query($sqlStat);

        while ($cfRec = $db->Row()) {

            $adRec = self::db_adRec($cfRec->cfCode);

            if ($adRec <> null) {

                if ($adRec->adBondsNr > ' ') {

                    $cfId = $cfRec->cfId;
                    $datumAangesloten = $adRec->adAanslDatum;

                    $sqlStat = "Update ssp_cf_contactformulier set cfIsAangesloten = 1, cfDatumAangesloten = '$datumAangesloten' where cfId = $cfId";

                    $db2->Query($sqlStat);

                }

            }

        }

    }

    // ===================================================================================================
    // Functie: Ophalen alle mail-adressen persoon
    //
    // In:	- Persoon
    //
    // Uit:	Return-value: String met alle mail adressen
    //
    // ===================================================================================================

    Static function GetPersoonMailString($pPersoon) {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

        // ---------------------------------------
        // Ophalen mail-adressen betreffende klant
        // ---------------------------------------

        $sqlStat = "Select * from ssp_ad where adCode = '$pPersoon'";

        $db->Query($sqlStat);

        $adRec = $db->Row();

        $mails = array();

        if ($adRec->adMail > ' ')
            $mails[] = $adRec->adMail;

        if ($adRec->adSpelerMail > ' ')
            $mails[] = $adRec->adSpelerMail;

        if ($adRec->adVaderMail > ' ')
            $mails[] = $adRec->adVaderMail;

        if ($adRec->adMoederMail > ' ')
            $mails[] = $adRec->adMoederMail;


        // --------------------
        // Opbouwen mail string
        // --------------------

        $mailString = '';

        foreach ($mails as $mail) {

            $mailAdres = trim($mail);

            if ($mailString) {

                if (!strpos($mailString, $mailAdres))
                    $mailString .= "; $mailAdres";
            } else
                $mailString = $mailAdres;


        }

        return $mailString;

    }

    // ===================================================================================================
    // Functie: Fill file sx_rp_role_personen
    //
    // In:	- Role (*ALL = alle), *HALF1, *HALF2
    //
    // Uit:	Return-value: String met alle mail adressen
    //
    // ===================================================================================================

    Static function FillRolePersonen($pRole = '*ALL') {

        include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(SX::GetSxClassPath("auth.class"));

        $vanaf = 1;
        $totMet = 999;

        if ($pRole == '*HALF1') {

            $sqlStat = "Select count(*) as aantal from sx_ro_roles";
            $db->Query($sqlStat);

            if ($roRec = $db->Row())
                $totMet = round($roRec->aantal / 2, 0);

        }

        if ($pRole == '*HALF2') {

            $sqlStat = "Select count(*) as aantal from sx_ro_roles";
            $db->Query($sqlStat);

            if ($roRec = $db->Row())
                $vanaf = round($roRec->aantal / 2, 0) - 1;

        }

        $sqlStat = "Select * from sx_ro_roles";
        $db->Query($sqlStat);

        $i = 0;

        while ($roRec = $db->Row()){

            $i++;


            if ($i < $vanaf)
                continue;
            if ($i > $totMet)
                continue;

            $role = $roRec->roCode;

            if (($pRole <> '*ALL') and ($pRole <> '*HALF1') and ($pRole <> '*HALF2') and ($pRole <> $role))
                continue;

            // ----------------------------
            // Get array "personen in role"
            // ----------------------------

            $personenInRole = array();

            $sqlStat = "Select * from ssp_ad where adRecStatus = 'A'";
            $db2->Query($sqlStat);

            while ($adRec = $db2->Row()){

                $persoon = $adRec->adCode;

                $select = SX_auth::CheckUserRole($persoon, $role);

                if ($select == true) {
                    $personenInRole[] = $persoon;
                }

            }

            // -----------
            // Put in file
            // -----------

            $sqlStat = "Delete From sx_rp_role_personen where rpRole = '$role'";
            $db2->Query($sqlStat);

            $curDateTime =	date('Y-m-d H:i:s');

            foreach ($personenInRole as $persoonInRole){

                $values = array();

                $values["rpRole"] = MySQL::SQLValue($role);
                $values["rpPersoon"] = MySQL::SQLValue($persoonInRole);
                $values["rpDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME );

                $db2->InsertRow("sx_rp_role_personen", $values);


            }

            $sqlStat = "Update sx_ro_roles set roLastRefresh = now() where roCode = '$role'";
            $db2->Query($sqlStat);

        }

    }

    // ===================================================================================================
    // Functie: Opvullen "familie van" (voor voetbal-spelers)
    //
    // In:	Geen
    //
    // Uit:	Geen
    //
    // ===================================================================================================

    Static function FillFamilieVan() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
        $db3 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        $sqlStat = "Select * from ssp_ad where adFunctieVB like '%speler%' and adRecStatus = 'A'";

        $db->Query($sqlStat);

        while ($adRec = $db->Row()){

            $naam = $adRec->adNaam;
            $adres1 = $adRec->adAdres1;
            $postnr = $adRec->adPostnr;
            $oudsteSpeler = "";
            $aantal = 0;

            // --------------------------------------
            // Ophalen "oudste" speler van de familie
            // --------------------------------------

            if ($naam and $adres1 and $postnr) {

                $sqlStat2 = "Select * from ssp_ad where adNaam = '$naam' and adFunctieVB like '%speler%'  and adAdres1 = '$adres1' and adPostnr = '$postnr' and adRecStatus = 'A' order by adGeboorteJaar";

                $db2->Query($sqlStat2);

                while ($adRec2 = $db2->Row()){

                    $aantal++;

                    if (! $oudsteSpeler)
                        $oudsteSpeler = $adRec2->adCode;


                }

                if ($aantal > 1) {

                    $sqlStat3 = "Update ssp_ad set adFamilieVan = '$oudsteSpeler' where adNaam = '$naam' and adAdres1 = '$adres1' and adPostnr = '$postnr' and adFunctieVB like '%speler%' and adRecStatus = 'A'";

                    $db3->Query($sqlStat3);


                }



            }

        }

    }

    // ===================================================================================================
    // Functie: Ophalen HTML-snippet Personalia - Bestuur voetbal (Bootstrap 4 versie)
    //
    // In:	Geen
    //
    // Uit:	HTML-snippet
    //
    // ===================================================================================================

    Static function GetPersonaliaBestuurVoetbalHTML() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ad inner join sx_ta_tables on taTable = 'BESTUUR' and taCode = adCode where adFunctieVB like '%bestuur%' and adRecStatus = 'A' ORDER BY taSort";

        $db->Query($sqlStat);
        $html = "";

        $html .= "<input class=\"form-control\" id=\"tblBestuurZoeken\" type=\"text\" placeholder=\"Zoek...\">";
        $html .= "<br/>";

        $html .= "<table style=\"margin-top: 20px;\"  class=\"table table-bordered\">";
        $html .=  "<thead>";
        $html .= "<tr>";
        $html .= "<th>Naam</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Functie</th>";
        $html .= "<th class='d-lg-none'>Functie / Contact</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Contactgegevens</th>";
        $html .= "</tr>";
        $html .=  "</thead>";

        $html .= "<tbody id=\"tblBestuur\">";

        while ($adRec = $db->Row()){

            $mail = $adRec->adMail;

            if ($adRec->taAlfaData)
                $mail = $adRec->taAlfaData;

            $fotoPath = "";

            if ($adRec->adFoto){

                $fotos = json_decode($adRec->adFoto);

                if ($fotos) {
                    foreach ($fotos as $foto) {

                        if (strpos($foto->type, "image") !== false)
                            $fotoPath = $foto->thumbnail;

                    }
                }

            }

            $html .= "<tr>";

            $html .= "<td>";
            $html .= $adRec->adVoornaamNaam;

            if ($fotoPath){

                $html .= "<br/>";
                $html .= "<img class=\"img-fluid\" src='$fotoPath'>";

            }

            $html .= "</td>";
            $html .= "<td>$adRec->taDescription<div class='d-lg-none' style='padding-top: 10px'><a href='mailto:$adRec->adMail'>$mail</a><br/>$adRec->adTel</div></td>";
            $html .= "<td  class='d-lg-table-cell d-none'><a href='mailto:$adRec->adMail'>$mail</a><br/>$adRec->adTel</td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        $html .= "<script>";
        $html .= "$(document).ready(function(){";
        $html .= "$(\"#tblBestuurZoeken\").on(\"keyup\", function() {";
        $html .= "var value = $(this).val().toLowerCase();";
        $html .= "$(\"#tblBestuur tr\").filter(function() {";
        $html .= "$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)";
        $html .= "});";
        $html .= "});";
        $html .= "});";
        $html .= "</script>";

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ===================================================================================================
    // Functie: Ophalen HTML-snippet Personalia - Sportieve Staf (Bootstrap 4 versie)
    //
    // In:	Data-level: *VOLLEDIG of *BEPERKT
    //
    // Uit:	HTML-snippet
    //
    // ===================================================================================================

    Static function GetPersonaliaSportieveStafHTML($pDataLevel = '*BEPERKT') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from ssp_ad inner join sx_ta_tables on taTable = 'SPORTIEVE_STAF' and taCode = adCode where adRecStatus = 'A' ORDER BY taSort";

        $db->Query($sqlStat);
        $html = "";

        $html .= "<input class=\"form-control\" id=\"tblSportieveStafZoeken\" type=\"text\" placeholder=\"Zoek...\">";
        $html .= "<br/>";

        $html .= "<table style=\"margin-top: 20px;\"  class=\"table table-bordered\">";
        $html .=  "<thead>";
        $html .= "<tr>";
        $html .= "<th>Naam</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Functie</th>";
        $html .= "<th class='d-lg-none'>Functie / Contact</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Contactgegevens</th>";
        $html .= "</tr>";
        $html .=  "</thead>";

        $html .= "<tbody id=\"tblSportieveStaf\">";

        while ($adRec = $db->Row()){

            $mail = $adRec->adMail;

            if ($adRec->taAlfaData)
                $mail = $adRec->taAlfaData;

            $fotoPath = "";

            if ($adRec->adFoto){

                $fotos = json_decode($adRec->adFoto);

                if ($fotos) {
                    foreach ($fotos as $foto) {

                        if (strpos($foto->type, "image") !== false)
                            $fotoPath = $foto->thumbnail;

                    }
                }

            }

            $html .= "<tr>";

            $html .= "<td>";
            $html .= $adRec->adVoornaamNaam;

            if ($fotoPath){

                $html .= "<br/>";
                $html .= "<img class=\"img-fluid\" src='$fotoPath'>";

            }

            $tel = $adRec->adTel;

            if ($pDataLevel != '*VOLLEDIG')
                $tel = "";

            $html .= "</td>";
            $html .= "<td>$adRec->taDescription<div class='d-lg-none' style='padding-top: 10px'><a href='mailto:$adRec->adMail'>$mail</a><br/>$tel</div></td>";
            $html .= "<td  class='d-lg-table-cell d-none'><a href='mailto:$adRec->adMail'>$mail</a><br/>$tel</td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        $html .= "<script>";
        $html .= "$(document).ready(function(){";
        $html .= "$(\"#tblSportieveStafZoeken\").on(\"keyup\", function() {";
        $html .= "var value = $(this).val().toLowerCase();";
        $html .= "$(\"#tblSportieveStaf tr\").filter(function() {";
        $html .= "$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)";
        $html .= "});";
        $html .= "});";
        $html .= "});";
        $html .= "</script>";

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ===================================================================================================
    // Functie: Ophalen HTML-snippet Personalia - Trainers (Bootstrap 4 versie)
    //
    // In:	Geen
    //
    // Uit:	HTML-snippet
    //
    // ===================================================================================================

    Static function GetPersonaliaTrainersHTML() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select adCode, adVoornaamNaam, adMail, adTel, adFunctieVB, vpid, taName AS Categorie, vpVoetbalCat, vpNaam as Ploeg, vpSort";
        $sqlStat .= " From ssp_ad";
        $sqlStat .= " Inner Join ssp_vp ON vpTrainer = adCode or vpTrainer2 = adCode or vpTrainer3 = adCode or vpTrainer4 = adCode or vpTrainer5 = adCode";
        $sqlStat .= " Inner Join ssp_vs ON vsCode = vpSeizoen and vsHuidigSeizoen = 1";
        $sqlStat .= " Inner Join sx_ta_tables ON taTable = 'VOETBAL_CAT' and taCode = vpVoetbalCat";
        $sqlStat .= " Where adRecStatus = 'A'";
        $sqlStat .= " Order by vpSort Desc, adVoornaamNaam";

        $db->Query($sqlStat);
        $html = "";

        $html .= "<input class=\"form-control\" id=\"tblTrainersZoeken\" type=\"text\" placeholder=\"Zoek...\">";
        $html .= "<br/>";

        $html .= "<table style=\"margin-top: 20px;\"  class=\"table table-bordered\">";
        $html .=  "<thead>";
        $html .= "<tr>";
        $html .= "<th>Naam</th>";

        // Grote schermen...
        $html .= "<th class='d-lg-table-cell d-none'>Ploeg</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Mail</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Tel</th>";

        // Kleine Schermen...
        $html .= "<th class='d-lg-none'>Ploeg / Contact</th>";

        $html .= "</tr>";
        $html .=  "</thead>";

        $html .= "<tbody id=\"tblTrainers\">";

        while ($adRec = $db->Row()){

            $mail = "<a href='mailto: $adRec->adMail'>$adRec->adMail</a>";

            $fotoPath = "";

            if ($adRec->adFoto){

                $fotos = json_decode($adRec->adFoto);

                if ($fotos) {
                    foreach ($fotos as $foto) {

                        if (strpos($foto->type, "image") !== false)
                            $fotoPath = $foto->thumbnail;

                    }
                }

            }

            $html .= "<tr>";

            $html .= "<td>";
            $html .= $adRec->adVoornaamNaam;

            if ($fotoPath){

                $html .= "<br/>";
                $html .= "<img class=\"img-fluid\" src='$fotoPath'>";

            }

            $html .= "</td>";

            // Grote schermen...
            $html .= "<td class='d-lg-table-cell d-none'>$adRec->Ploeg</td>";
            $html .= "<td class='d-lg-table-cell d-none'>$mail</td>";
            $html .= "<td class='d-lg-table-cell d-none'>$adRec->adTel</td>";

            // Kleine schermen...
            $html .= "<td class='d-lg-none'>$adRec->Ploeg<br/><br/>$mail<br/>$adRec->adTel</td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        $html .= "<script>";
        $html .= "$(document).ready(function(){";
        $html .= "$(\"#tblTrainersZoeken\").on(\"keyup\", function() {";
        $html .= "var value = $(this).val().toLowerCase();";
        $html .= "$(\"#tblTrainers tr\").filter(function() {";
        $html .= "$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)";
        $html .= "});";
        $html .= "});";
        $html .= "});";
        $html .= "</script>";

        // -------------
        // Einde functie
        // -------------

        return $html;

    }

    // ===================================================================================================
    // Functie: Ophalen HTML-snippet Personalia - Afgevaardigden (Bootstrap 4 versie)
    //
    // In:	Geen
    //
    // Uit:	HTML-snippet
    //
    // ===================================================================================================

    Static function GetPersonaliaAfgevaardigdenHTML() {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select adCode, adVoornaamNaam, adMail, adTel, adFunctieVB, vpid, taName AS Categorie, vpVoetbalCat, vpNaam as Ploeg, vpSort";
        $sqlStat .= " From ssp_ad";
        $sqlStat .= " Inner Join ssp_vp ON vpDelege = adCode or vpDelege2 = adCode or vpDelege3 = adCode";
        $sqlStat .= " Inner Join ssp_vs ON vsCode = vpSeizoen and vsHuidigSeizoen = 1";
        $sqlStat .= " Inner Join sx_ta_tables ON taTable = 'VOETBAL_CAT' and taCode = vpVoetbalCat";
        $sqlStat .= " Where adRecStatus = 'A'";
        $sqlStat .= " Order by vpSort Desc, adVoornaamNaam";

        $db->Query($sqlStat);
        $html = "";

        $html .= "<input class=\"form-control\" id=\"tblAfgevaardigdenZoeken\" type=\"text\" placeholder=\"Zoek...\">";
        $html .= "<br/>";

        $html .= "<table style=\"margin-top: 20px;\"  class=\"table table-bordered\">";
        $html .=  "<thead>";
        $html .= "<tr>";
        $html .= "<th>Naam</th>";

        // Grote schermen...
        $html .= "<th class='d-lg-table-cell d-none'>Ploeg</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Mail</th>";
        $html .= "<th class='d-lg-table-cell d-none'>Tel</th>";

        // Kleine Schermen...
        $html .= "<th class='d-lg-none'>Ploeg / Contact</th>";

        $html .= "</tr>";
        $html .=  "</thead>";

        $html .= "<tbody id=\"tblAfgevaardigden\">";

        while ($adRec = $db->Row()){

            $mail = "<a href='mailto: $adRec->adMail'>$adRec->adMail</a>";

            $fotoPath = "";

            if ($adRec->adFoto){

                $fotos = json_decode($adRec->adFoto);

                if ($fotos) {
                    foreach ($fotos as $foto) {

                        if (strpos($foto->type, "image") !== false)
                            $fotoPath = $foto->thumbnail;

                    }
                }

            }

            $html .= "<tr>";

            $html .= "<td>";
            $html .= $adRec->adVoornaamNaam;

            if ($fotoPath){

                $html .= "<br/>";
                $html .= "<img class=\"img-fluid\" src='$fotoPath'>";

            }

            $html .= "</td>";

            // Grote schermen...
            $html .= "<td class='d-lg-table-cell d-none'>$adRec->Ploeg</td>";
            $html .= "<td class='d-lg-table-cell d-none'>$mail</td>";
            $html .= "<td class='d-lg-table-cell d-none'>$adRec->adTel</td>";

            // Kleine schermen...
            $html .= "<td class='d-lg-none'>$adRec->Ploeg<br/><br/>$mail<br/>$adRec->adTel</td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        $html .= "<script>";
        $html .= "$(document).ready(function(){";
        $html .= "$(\"#tblAfgevaardigdenZoeken\").on(\"keyup\", function() {";
        $html .= "var value = $(this).val().toLowerCase();";
        $html .= "$(\"#tblAfgevaardigden tr\").filter(function() {";
        $html .= "$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)";
        $html .= "});";
        $html .= "});";
        $html .= "});";
        $html .= "</script>";

        // -------------
        // Einde functie
        // -------------

        return $html;

    }





    // -----------
    // EINDE CLASS
    // -----------


}
	  
?>