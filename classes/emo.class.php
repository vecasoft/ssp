<?php 

class SSP_emo
{ // define the class

    // ========================================================================================
    // Aanvullen dossier header data
    //
    // In:	Dossier ID
    //      Interface (*TRAINER, *KINE, *ADMIN)
    //      Actie (*OPSTARTEN, *UPDATE)
    //
    // Return: Niets...
    // ========================================================================================

    static function UpdDossierHeader($pDossier, $pInterface = null, $pActie='*UPDATE') {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetSxClassPath("tools.class"));
        include_once(Sx::GetClassPath("_db.class"));

        // -------------------
        // Ophalen nodige data
        // -------------------

        $mhRec = SSP_db::Get_EMO_mhRec($pDossier);

        if (! $mhRec)
            return;

        $adRec = SSP_db::Get_SSP_adRec($mhRec->mhPersoon);

        if (! $adRec)
            return;

        // ------------
        // Naam dossier
        // ------------

        $naam = $adRec->adNaamVoornaam;

        if ($adRec->adVoetbalCat)
            $naam .= " ($adRec->adVoetbalCat)";

        //if ($adRec->adGeboorteJaar)
        //    $naam = "$naam ($adRec->adGeboorteJaar)";

        $datum = SX_tools::EdtDate($mhRec->mhDatumStart, '%d/%m/%Y');
        $blessure = $mhRec->mhBlessure;

        if ($blessure) {

            $blRec = SSP_db::Get_EMO_blRec($blessure);

            if ($blRec)
                $naam = "$naam - $blRec->blNaam";
        } else
                $naam = "$naam - $mhRec->mhBlessureOmschrijving";

        $naam = "$naam ($datum)";

        // ------------------------------
        // Aanmaken detail bij *OPSTARTEN
        // ------------------------------

        if (($mhRec->mhStatus == '*OPSTARTEN') and $pInterface){

            $sqlStat = "Select count(*) as aantal from emo_md_medisch_dossier_detail where mdDossier = $pDossier";
            $db->Query($sqlStat);

            if ($mdRec = $db->Row()){

                if ($mdRec->aantal <= 0){

                    $curDateTime = date('Y-m-d H:i:s');

                    If ($pInterface == '*TRAINER')
                        $actie = '*MELDING_TRAINER';
                    If ($pInterface == '*ADMIN')
                        $actie = '*MELDING_ADMIN';
                    If ($pInterface == '*KINE')
                        $actie = '*MELDING_KINE';

                    $values = array();

                    $values["mdDossier"] = MySQL::SQLValue($pDossier, MySQL::SQLVALUE_NUMBER);
                    $values["mdActie"] = MySQL::SQLValue($actie);
                    $values["mdDatum"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);
                    $values["mdSpelerStatus"] = MySQL::SQLValue('*NIETS');

                    $values["mdUserCreatie"] = MySQL::SQLValue($mhRec->mhUserCreatie);
                    $values["mdDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                    $values["mdUserUpdate"] = MySQL::SQLValue($mhRec->mhUserCreatie);
                    $values["mdDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                    $values["mdRecStatus"] = MySQL::SQLValue('A');

                    $id = $db->InsertRow("emo_md_medisch_dossier_detail", $values);

                }

            }


        }

        // ---------------------------------------
        // Status Speler (= status laatste detail)
        // ---------------------------------------

        $spelerStatus = '*NIETS';

        $sqlStat = "Select * from emo_md_medisch_dossier_detail where mdDossier = $pDossier order by mdDatum desc, mdId desc";
        $db->Query($sqlStat);

        if ($mdRec = $db->Row())
            $spelerStatus = $mdRec->mdSpelerStatus;

        // --------------
        // Dossier-status
        // --------------

        $status = $mhRec->mhStatus;

        If ($pActie == '*OPSTARTEN')
            $status = '*OPSTARTEN';

        If ($pActie == '*UPDATE')
            $status = '*OPEN';

        // --------------------
        // Omschrijving melding
        // --------------------

        $blessureOmschrijvingMelder = $mhRec->mhBlessureOmschrijvingMelder;

        If ($pInterface = '*KINE' and $pActie == '*UPDATE')
            $blessureOmschrijvingMelder = '*UPDATE';

        // ------
        // update
        // ------

        $values = array();
        $where = array();

        $values["mhNaam"] =  MySQL::SQLValue($naam);
        $values["mhStatus"] =  MySQL::SQLValue($status);
        $values["mhSpelerStatus"] =  MySQL::SQLValue($spelerStatus);

        $values["mhBlessureOmschrijvingMelder"] =  MySQL::SQLValue($blessureOmschrijvingMelder);

        $where["mhId"] =  MySQL::SQLValue($pDossier, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("emo_mh_medisch_dossier_headers", $values, $where);

        if ($status != '*AFGESLOTEN') {
            $sqlStat = "Update emo_mh_medisch_dossier_headers set mhDatumAfsluiten = null where mhId = $pDossier";
            $db->Query($sqlStat);
        }

        // -------------
        // Einde functie
        // -------------


    }

    // ========================================================================================
    // Automatisch afsluiten dossier
    //
    // In:	Dossier ID
    //
    // Return:Afgesloten ? true/false
    // ========================================================================================

    static function AutoCloseDossier($pDossier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        include_once(Sx::GetClassPath("_db.class"));

        // -------------------------------
        // Enkel indien nog niet afgeloten
        // --------------------------------

        $mhRec = SSP_db::Get_EMO_mhRec($pDossier);

        if (! $mhRec)
            return false;

        if ($mhRec->mhStatus == '*AFGESLOTEN')
            return false;

        // ------------------------------------
        // Enkel indien status-speler = "ALLES"
        // ------------------------------------

        if ($mhRec->mhSpelerStatus != '*ALLES')
            return false;

        // --------------------------------------------
        // Laatste update detail moet ouder dan 5 dagen
        // --------------------------------------------

        $sqlStat = "Select count(*) as aantal from emo_md_medisch_dossier_detail where mdDossier = $pDossier and mdSpelerStatus = '*ALLES' and mdDatumUpdate > CURRENT_DATE() - INTERVAL 5 DAY ";
        $db->Query($sqlStat);

        $mdRec = $db->Row();

        if ($mdRec->aantal < 0)
            return false;

        // ---------
        // Afsluiten
        // ---------

        $curDate = date('Y-m-d');

        $values["kaDatumCreatie"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

        $values = array();
        $where = array();

        $values["mhStatus"] =  MySQL::SQLValue('*AFGESLOTEN');
        $values["mhDatumAfsluiten"] =  MySQL::SQLValue($curDate, MySQL::SQLVALUE_DATE);

        $where["mhId"] =  MySQL::SQLValue($pDossier, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("emo_mh_medisch_dossier_headers", $values, $where);

        // -------------
        // Einde functie
        // -------------

        return true;

    }

    // ========================================================================================
    // Automatisch afsluiten dossiers

    // ========================================================================================

    static function AutoCloseDossiers(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from emo_mh_medisch_dossier_headers where mhStatus <> '*AFGESLOTEN'";
        $db->Query($sqlStat);

        while ($mhRec = $db->Row()){

            $dossier = $mhRec->mhId;

            self::AutoCloseDossier($dossier);

        }

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Check Delete Dossier Header
    //
    // In:	Dossier ID
    //
    // Return: Foutboodschap of *OK
    // ========================================================================================

    static function ChkDeleteDossierHeader($pDossier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        // ---------------------------------------------------------------
        // mag niet gewist worden indien nog detail of status "*OPSTARTEN"
        // --------------------------------------------------------------

        $mhRec = SSP_db::Get_EMO_mhRec($pDossier);

        if ($mhRec->mhStatus == '*OPSTARTEN')
            return '*OK';

        $sqlStat = "Select count(*) as aantal from emo_md_medisch_dossier_detail where mdDossier = $pDossier";
        $db->Query($sqlStat);

        if ($mdRec = $db->Row()) {

            if ($mdRec->aantal > 0)
                return "Wissen niet mogelijk, er zijn acties gekoppeld aan dit dossier";

        }

        // -------------
        // Einde functie
        // -------------

        return '*OK';

    }

    // ========================================================================================
    // Delete Dossier
    //
    // In:	Dossier ID

    // ========================================================================================

    static function DeleteDossier($pDossier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        // ----------------------
        // Delete detail & header
        // ----------------------

        $sqlStat = "Delete from emo_md_medisch_dossier_detail where mdDossier = $pDossier";
        $db->Query($sqlStat);

        $sqlStat = "Delete from emo_mh_medisch_dossier_headers where mhId = $pDossier";
        $db->Query($sqlStat);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    //  Opvullen id-code van de "Specifieke Training" (stRec)
    //
    // In:	Specifieke Training ID
    //
    // ========================================================================================

    static function FillStIdCode($pSpecifiekeTraining){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $idCode = str_pad($pSpecifiekeTraining, 5, '0', STR_PAD_LEFT);

        $values = array();
        $where = array();

        $values["stIdCode"] =MySQL::SQLValue($idCode);

        $where["stId"] =  MySQL::SQLValue($pSpecifiekeTraining, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("emo_st_specifieke_trainingen", $values, $where);

        // -------------
        // Einde functie
        // -------------

    }

    // ========================================================================================
    // Check actie toegestaan voor specifieke interface
    //
    // In:	Actie
    //      INterface (*KINE, *TRAINER, *ADMIN)
    //
    // Return: Is actie toegestaan?
    // ========================================================================================

    static function ChkActieToegestaan($pActie, $pInterface = '*KINE'){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $taRec = SSP_db::Get_SX_taRec('EMO_ACTIE_CODE', $pActie);

        $sqlStat = "Select count(*) as aantal from sx_ta_tables where taTable = 'EMO_ACTIE_CODE' and taCode = '$pActie' and taAlfaData like '%$pInterface%'";
        $db->Query($sqlStat);

        if ($taRec = $db->Row())
            if ($taRec->aantal > 0)
                return true;

        // -------------
        // Einde functie
        // -------------

        return false;

    }

    // ========================================================================================
    // Check eerste consult
    //
    // In:	Dossier ID
    //      Dossier detail ID (Optional)
    //
    // Return: Eerste consult reeds gebeurd? (true/false)
    // ========================================================================================

    static function ChkEersteConsult($pDossier, $pDossierDetail = null){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select count(*) as aantal from emo_md_medisch_dossier_detail where mdDossier = $pDossier and mdActie = '*CONSULT_EERSTE'";

        if ($pDossierDetail)
            $sqlStat .= " and mdId <> $pDossierDetail";

        $db->Query($sqlStat);

        if ($mdRec = $db->Row()){

            if ($mdRec->aantal >= 1)
                return true;

        }

        // -------------
        // Einde functie
        // -------------

        return false;

    }

    // ========================================================================================
    // Ophalen categorie van een speler
    //
    // In:	Speler
    //
    // Return: Categorie (null indien geen categorie gevonden)
    // ========================================================================================

    static function GetSpelerCategorie($pSpeler){

        include_once(SX::GetClassPath("_db.class"));

        $adRec = SSP_db::Get_SSP_adRec($pSpeler);

        if (!$adRec)
             return null;

        if ($adRec->adVoetbalCat)
            return $adRec->adVoetbalCat;
        else
            return null;

    }
    // ========================================================================================
    // Ophalen kleurspeler status
    //
    // In:	Speler status
    //      Retunr type:    *KLEUR -> *GROEN, *ROOD, ...
    //                      *BACKGROUND (Default) -> Background-color
    //
    // Return: Kleur
    // ========================================================================================

    static function GetSpelerStatusKleur($pStatus, $pType='*BACKGROUND'){

        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("_db.class"));

        include_once(Sx::GetClassPath("settings.class"));

        $green = SSP_settings::GetBackgroundColor('green');
        $yellow = SSP_settings::GetBackgroundColor('yellow');
        $red = SSP_settings::GetBackgroundColor('red');
        $orange = SSP_settings::GetBackgroundColor('orange');
        $blue = SSP_settings::GetBackgroundColor('blue');

        $taRec = SSP_db::Get_SX_taRec('EMO_SPELER_STATUS',$pStatus);

        $kleurCode = $taRec->taAlfaData;

        if ($pType == '*KLEUR')
            return $kleurCode;

        if ($kleurCode == '*GROEN')
            return $green;
        if ($kleurCode == '*ROOD')
            return $red;
        if ($kleurCode == '*GEEL')
            return $yellow;
        if ($kleurCode == '*ORANJE')
            return $orange;
        if ($kleurCode == '*BLAUW')
            return $blue;

        return null;

    }


    // ========================================================================================
    // Ophalen trainers van een speler (trainers van dezelde categorie
    //
    // In:	Speler
    //      Ophalen trainers ?
    //      Ophalen sportieve staf ?
    //      Toevoegen GC ?
    //
    // Return: Array met trainers (null indien geen trainers gevonden)
    // ========================================================================================

    static function GetSpelerTrainers($pSpeler, $pGetTrainers = true, $pGetSportieveStaf=true){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        include_once(SX::GetClassPath("settings.class"));
        include_once(SX::GetClassPath("ela.class"));

        $adRec = SSP_db::Get_SSP_adRec($pSpeler);

        if (! $adRec)
            return null;

        $cat = $adRec->adVoetbalCat;

        if (! $cat)
            return null;

        $isDoelman = false;
        if (SSP_ela::ChkFunctieVB($adRec->adFunctieVB, '*DOELMAN'))
            $isDoelman = true;

        $huidigSeizoen = SSP_settings::GetActiefSeizoen();

        // ------------------------------------------
        // Alle trainers van de betreffende categorie
        // ------------------------------------------


        if ($pGetTrainers) {

            $sqlStat = "Select * from ssp_vp where vpSeizoen = '$huidigSeizoen' and vpVoetbalCat = '$cat'";
            $db->Query($sqlStat);

            $trainers = array();
            $ploegenGevonden = false;

            while ($vpRec = $db->Row()) {

                $ploegenGevonden = true;

                if ($vpRec->vpTrainer)
                    $trainers[] = $vpRec->vpTrainer;
                if ($vpRec->vpTrainer2)
                    $trainers[] = $vpRec->vpTrainer2;
                if ($vpRec->vpTrainer3)
                    $trainers[] = $vpRec->vpTrainer3;
                if ($vpRec->vpTrainer4)
                    $trainers[] = $vpRec->vpTrainer4;
                if ($vpRec->vpTrainer5)
                    $trainers[] = $vpRec->vpTrainer5;

            }

            if (!$ploegenGevonden) {

                $vorigSeizoen = SSP_settings::GetActiefSeizoen('*VORIG');

                $sqlStat = "Select * from ssp_vp where vpSeizoen = '$vorigSeizoen' and vpVoetbalCat = '$cat'";
                $db->Query($sqlStat);

                while ($vpRec = $db->Row()) {

                    if ($vpRec->vpTrainer)
                        $trainers[] = $vpRec->vpTrainer;
                    if ($vpRec->vpTrainer2)
                        $trainers[] = $vpRec->vpTrainer2;
                    if ($vpRec->vpTrainer3)
                        $trainers[] = $vpRec->vpTrainer3;
                    if ($vpRec->vpTrainer4)
                        $trainers[] = $vpRec->vpTrainer4;
                    if ($vpRec->vpTrainer5)
                        $trainers[] = $vpRec->vpTrainer5;

                }

            }
        }

        // -------------------------------------------------------------
        // Trainers van de ploegen waarin speler specifiek geregistreerd
        // -------------------------------------------------------------

        $sqlStat = "Select * from ssp_vp inner join ssp_vp_sp on spPloeg = vpId and spPersoon = '$pSpeler' where vpSeizoen = '$huidigSeizoen'";

        $db->Query($sqlStat);

        while ($vpRec = $db->Row()){

            if ($vpRec->vpTrainer)
                $trainers[] = $vpRec->vpTrainer;
            if ($vpRec->vpTrainer2)
                $trainers[] = $vpRec->vpTrainer2;
            if ($vpRec->vpTrainer3)
                $trainers[] = $vpRec->vpTrainer3;
            if ($vpRec->vpTrainer4)
                $trainers[] = $vpRec->vpTrainer4;
            if ($vpRec->vpTrainer5)
                $trainers[] = $vpRec->vpTrainer5;
        }

        // --------------------------------
        // toevoegen verantwoordelijke bouw
        // --------------------------------

        if ($pGetSportieveStaf) {

            $functie = null;

            if ($cat == 'U6' or $cat == 'U7' or $cat == 'U8' or $cat == 'U9')
                $functie = 'verantw.ob';
            if ($cat == 'U10' or $cat == 'U11' or $cat == 'U12' or $cat == 'U13')
                $functie = 'verantw.mb';
            if ($cat == 'U15' or $cat == 'U16' or $cat == 'U17' or $cat == 'U21')
                $functie = 'verantw.bb';
            if ($cat == 'SEN')
                $functie = 'verantw.sen';
            if ($cat == 'G')
                $functie = 'verantw.gt';

            if ($functie) {

                $sqlStat = "Select * from ssp_ad where adFunctieVB like '%$functie%'";
                $db->Query($sqlStat);

                while ($adRec = $db->Row())
                    $trainers[] = $adRec->adCode;

            }

            // Doelmannen verantwoordelijke...
            if ($isDoelman) {

                $sqlStat = "Select * from ssp_ad where adFunctieVB like '%verantw.dm%'";
                $db->Query($sqlStat);

                while ($adRec = $db->Row())
                    $trainers[] = $adRec->adCode;

            }

        }

        // -------------
        // Einde functie
        // -------------

        return array_unique($trainers);

    }

    // ===============================================================================================
    // Ophalen dossiers waartoe iemand toegang heeft
    //
    // In:	User
    //
    // Return: Array met dossiers (-1 als toegang tot ALLE dossiers, -2 indien tot geen enkel dossier)
    // ===============================================================================================

    static function GetDossiers($pUser){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("ela.class"));

        $adRec = SSP_db::Get_SSP_adRec($pUser);

        $dossiers = array();

        if (! $adRec or (! $adRec->adFunctieVB)){

            $dossiers[] = -2;
            return $dossiers;

        }

        // -------------------
        // Bestuur -> Iedereen
        // -------------------

        if (SSP_ela::ChkFunctieVB($adRec->adFunctieVB, '*BESTUUR')){

            $dossiers[] = -1;
            return $dossiers;

        }

        // ---------------------
        // Alle dossiers aflopen
        // ---------------------

        $sqlStat = "Select * from emo_mh_medisch_dossier_headers order by mhId desc";
        $db->Query($sqlStat);

        while ($mhRec = $db->Row()){

            // door user zelf aangemaakt -> sowieso toegang
            if ($mhRec->mhUserCreatie == $pUser){
                $dossiers[] = $mhRec->mhId;
                continue;
            }

            $persoon = SSP_db::Get_SSP_adRec($mhRec->mhPersoon);

            if (! $persoon)
                continue;

            // Enkel spelers...
            if (! SSP_ela::ChkFunctieVB($persoon->adFunctieVB,'*SPELER'))
                continue;

            $trainers = self::GetSpelerTrainers($persoon->adCode);

            if (! in_array($pUser, $trainers))
                continue;

            $dossiers[] = $mhRec->mhId;

        }

        // -------------
        // Einde functie
        // -------------

        return $dossiers;

    }
    // ===============================================================================================
    // Ophalen dossiers waartoe iemand toegang heeft (in de vorm van "sql WHERE-clause"-
    //
    // In:	User
    //
    // Return: Where clause
    // ===============================================================================================

    static function GetDossiersSqlExtraWhere($pUser){


        if ($pUser == 'webmaster')
            return '1=1';

        $dossiers = self::GetDossiers($pUser);

        $where = '1=1';

        if (! $dossiers)
            $where= '1=2';

        if ($dossiers[0] != -1) {

            $in = '';

            foreach ($dossiers as $dossier){

                if (! $in)
                    $in = $dossier;
                else
                    $in .= ',' . $dossier;

	        }

            if ($in)
                $where= "mhId in ($in)";

        }

        // -------------
        // Einde functie
        // -------------

        return $where;


    }


    // ========================================================================================
    // Mail "gewijzigde" status naar betrokken trainer(s) voor specifiek dossier
    //
    // In:	Dossier ID
    //
    // ========================================================================================

    static function MailDossierStatusNaarTrainers($pDossier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $sqlStat = "Select * from emo_md_medisch_dossier_detail where mdDossier = $pDossier and mdInfoMailVerstuurd = 0 order by mdDatum";
        $db->Query($sqlStat);

        while ($mdRec = $db->Row() ){

            $id = $mdRec->mdId;

            if (self::MailDossierDetailStatusNaarTrainers($id)){

                $values = array();
                $where = array();

                $values["mdInfoMailVerstuurd"] =  MySQL::SQLValue(1, MySQL::SQLVALUE_NUMBER);

                $where["mdId"] =  MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);

                // $db->UpdateRows("emo_md_medisch_dossier_detail", $values, $where);

            }

        }

        // -------------
        // Einde functie
        // -------------

    }
    // ========================================================================================
    // Versturen nodige mails alle openstaande dossiers
    // ========================================================================================

    static function SndOpenDossiersMAILS(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from emo_mh_medisch_dossier_headers where mhRecStatus = 'A'";
        $db->Query($sqlStat);

        while($mhRec = $db->Row())
            self::SndDossierMAILS($mhRec->mhId);


    }

    // ========================================================================================
    // Versturen nodige mails voor bepaald dossier
    //
    // In:	Dossier
    //
    // Out: Mail(s) gestuurd?
    //
    // ========================================================================================

    static function SndDossierMAILS($pDossier){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));
        $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

        include_once(Sx::GetClassPath("settings.class"));
        include_once(Sx::GetSxClassPath("tools.class"));

        $mhRec = SSP_db::Get_EMO_mhRec($pDossier);

        if (! $mhRec)
            return false;

        $sqlStat = "Select * from emo_md_medisch_dossier_detail where mdDossier = $pDossier and mdMailsVerzenden = 1 order by mdDatum desc, mdId desc";
        $db->Query($sqlStat);

        while ($mdRec = $db->Row()){

            $actie = $mdRec->mdActie;

            $sqlStat = "Select * from emo_mg_mail_groepen where mgActie like '%$actie%' and mgRecStatus = 'A'";
            $db2->Query($sqlStat);

            while ($mgRec = $db2->Row()) {

                $aantal = self::SndDossierMAIL($mhRec, $mdRec, $mgRec);
            }

            // --------------
            // Set mail status
            // ---------------

            $values = array();
            $where = array();

            $values["mdMailsVerzenden"] =  MySQL::SQLValue(0, MySQL::SQLVALUE_NUMBER);

            $where["mdId"] =  MySQL::SQLValue($mdRec->mdId, MySQL::SQLVALUE_NUMBER);

            $db2->UpdateRows("emo_md_medisch_dossier_detail", $values, $where);

        }

        // -------------
        // Einde functie
        // -------------

        return $aantal;

    }


    // ========================================================================================
    // Versturen Mail
    //
    // In:	Dossier-header record
    //      Dossier-detail record
    //      Mail-groep record
    //
    // Out: Aantal mails verstuurd
    //
    // ========================================================================================

    static function SndDossierMAIL($pMhRec, $pMdRec, $pMgRec){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        include_once(SX::GetClassPath("ela.class"));
        include_once(SX::GetClassPath("evim.class"));

        $speler = $pMhRec->mhPersoon;

        $spelerRec = SSP_db::Get_SSP_adRec($speler);

        if (! $spelerRec)
            return 0;

        $spelerNaam = $spelerRec->adNaamVoornaam;

        if ($spelerRec->adVoetbalCat){

            $cat = $spelerRec->adVoetbalCat;

            $taRec = SSP_db::Get_SX_taRec('VOETBAL_CAT', $cat);
            if ($taRec)
                $cat = $taRec->taName;

            $spelerNaam = "$spelerNaam ($cat)";

        }

        // ---------------------
        // Blessure-omschrijving
        // ---------------------

        $blessureOmschrijving = nl2br($pMhRec->mhBlessureOmschrijving);

        if ($pMhRec->mhBlessure){

            $blRec = SSP_db::Get_EMO_blRec($pMhRec->mhBlessure);

            if ($blRec)
                $blessureOmschrijving = $blRec->blNaam . "<br/><br/>" . $blessureOmschrijving;
        }

        // ---------------------
        // Specifieke trainingen
        // ---------------------

        $specifiekeTrainingen = "";

        if ($pMdRec->mdSpecifiekeTraining or $pMdRec->mdInfoSpelerTrainer){

            $codes = explode(',', $pMdRec->mdSpecifiekeTraining);

            foreach ($codes as $code){

                $sqlStat = "Select * from emo_st_specifieke_trainingen where stIdCode = '$code'";
                $db->Query($sqlStat);

                if ($stRec = $db->Row()){
                    $specifiekeTrainingen .= "<li>$stRec->stNaam</li>";
                }

            }

            if ($specifiekeTrainingen){

                $specifiekeTrainingen = "<b>Toegelaten trainingen</b>:<ul>$specifiekeTrainingen</ul>";

            }

            if ($pMdRec->mdInfoSpelerTrainer){

                if ($specifiekeTrainingen)
                    $specifiekeTrainingen = "$specifiekeTrainingen<br/>";

                $info = nl2br($pMdRec->mdInfoSpelerTrainer);

                $specifiekeTrainingen = "$specifiekeTrainingen<b>Extra info</b>:<br/><br/>$info";

            }


        }

        // -------
        // Bijlage
        // -------

        $bijlagePath = "";
        $bijlageNaam = "";

        if ($pMgRec->mgBijlage){

            $bijlagen = json_decode($pMgRec->mgBijlage);

            if ($bijlagen) {

                foreach ($bijlagen as $bijlage) {

                    $filePath = basename($bijlage->name);
                    $bijlageNaam = $bijlage->usrName;
                    break;
                }

            }

            $bijlagePath = $_SESSION["SX_BASEPATH"] . '/_files/emo/bijlagen/' . $filePath;

        }

        // ---------------------
        // Bepalen mail-adressen
        // ---------------------

        $mailTo_personen = array();
        $mailTo_mails = array();

        // ------
        // Speler
        // ------

        if (strpos($pMgRec->mgBestemmelingen, 'speler') !== false){

            $mails = SSP_ela::GetPersoonMails($speler);

            foreach ($mails as $mail){

                $mailTo_personen[] = $speler;
                $mailTo_mails[] = $mail;

            }
        }

        // ----------------------------
        // Trainer en/of sportieve staf
        // ----------------------------

        $getTrainers = false;
        $getSportieveStaf = false;

        if (strpos($pMgRec->mgBestemmelingen, 'trainers') !== false)
            $getTrainers = true;
        if (strpos($pMgRec->mgBestemmelingen, 'sport.staf') !== false)
            $getSportieveStaf = true;

        if ($getTrainers or $getSportieveStaf){

            $trainers = self::GetSpelerTrainers($speler, $getTrainers, $getSportieveStaf);

            foreach ($trainers as $trainer){

                $adRec = SSP_db::Get_SSP_adRec($trainer);

                if ($adRec->adMail or $adRec->adMailSchelleSport) {

                    $mailTo_personen[] = $trainer;

                    if ($adRec->adMailSchelleSport)
                        $mailTo_mails[] = $adRec->adMailSchelleSport;
                    else
                        $mailTo_mails[] = $adRec->adMail;


                }
            }
        }


        // --------------
        // Versturen mail
        // --------------

        $arr_VARS = array();
        $arr_VALUES = array();

        $arr_VARS[] = "NAAM_SPELER";
        $arr_VALUES[] = $spelerNaam;


        $arr_VARS[] = "BLESSURE";
        $arr_VALUES[] = "<br/>$blessureOmschrijving";

        $arr_VARS[] = "STATUS_SPELER";

        $spelerStatus = $pMhRec->mhSpelerStatus;
        $taRec = SSP_db::Get_SX_taRec('EMO_SPELER_STATUS', $spelerStatus);

        if ($taRec) {

            $spelerStatusNaam = $taRec->taName;
            $color = self::GetSpelerStatusKleur($spelerStatus);
            $statusSpeler = "<span style='background-color: $color; font-weight: bold'>&nbsp;$spelerStatusNaam&nbsp;</span>";

        }
        else
            $statusSpeler = "Onbekend";


        if ($specifiekeTrainingen)
            $statusSpeler .= "<br/><br/>$specifiekeTrainingen";


        $arr_VALUES[] = $statusSpeler;

        $mailBody = '<html><body>';
        $mailBody .= nl2br($pMgRec->mgMailBody);
        $mailBody .= '</body></html>';

        // $mailBody = utf8_encode($mailBody);

        $mailBody = SSP_evim::FillTemplateVars($mailBody, $arr_VARS, $arr_VALUES);

        $mailSubject = SSP_evim::FillTemplateVars($pMgRec->mgMailSubject, $arr_VARS, $arr_VALUES);

        $aantalMails = 0;

        foreach ($mailTo_mails as $key=>$mail) {

            $mailTo = $mail;
            $persoon = $mailTo_personen[$key];

            // $mailTo = $mailTo_personen[$key] . "@vecasoftware.com";

            $mailBCC = "emo@vecasoftware.com";

            $fromMail = "secretariaat@schellesport.be";
            $fromName = "Schelle Sport - Secretariaat";

            SX_tools::SendMail($mailSubject, $mailBody, $mailTo, $mailBCC, $fromMail, $fromName, $bijlagePath, 'UTF-8', '', $bijlageNaam);

            $aantalMails++;

            // --------------------------------
            // Registratie in "verzonden mails"
            // --------------------------------

            $values = array();

            $curDateTime = date('Y-m-d H:i:s');

            $values["vmDossier"] = MySQL::SQLValue($pMhRec->mhId, MySQL::SQLVALUE_NUMBER);
            $values["vmDossierDetail"] = MySQL::SQLValue($pMdRec->mdId, MySQL::SQLVALUE_NUMBER);
            $values["vmMailGroep"] = MySQL::SQLValue($pMgRec->mgId, MySQL::SQLVALUE_NUMBER);

            $values["vmMailNaarPersoon"] = MySQL::SQLValue($persoon, MySQL::SQLVALUE_TEXT);
            $values["vmMailNaarMail"] = MySQL::SQLValue($mail, MySQL::SQLVALUE_TEXT);

            $values["vmMailSubject"] = MySQL::SQLValue($mailSubject, MySQL::SQLVALUE_TEXT);
            $values["vmMailBody"] = MySQL::SQLValue($mailBody, MySQL::SQLVALUE_TEXT);

            if ($bijlageNaam)
                $values["vmMailBijlage"] = MySQL::SQLValue($bijlageNaam, MySQL::SQLVALUE_TEXT);
            else
                $values["vmMailBijlage"] = MySQL::SQLValue("Geen...", MySQL::SQLVALUE_TEXT);

            $values["vmMailVerstuurdOp"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

            $id = $db->InsertRow("emo_vm_verzonden_mails", $values);



        }


        // -------------
        // Einde functie
        // -------------

        return $aantalMails;

    }


    // ========================================================================================
    // HTML snippet met geblesseerde spelers van een bepoalde trainer
    //
    // In:	Trainer
    //
    // Out:  HTML snippet (null indien geen geblesseerde spelers)
    //
    // ========================================================================================

    static function GetTrainerSpelersStatusHTML($pTrainer){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $statusHTML = null;

        $htmls = array();

        $sqlStat = "Select * from emo_mh_medisch_dossier_headers where mhSpelerStatus <> '*ALLES'";
        $db->Query($sqlStat);

        $aantal =  0;

        while ($mhRec = $db->Row()){

            $speler = $mhRec->mhPersoon;

            $trainers = self::GetSpelerTrainers($speler, true, false);

            if (! in_array($pTrainer, $trainers))
                continue;

            $aantal++;

            $taRec = SSP_db::Get_SX_taRec('EMO_SPELER_STATUS', $mhRec->mhSpelerStatus);
            $spelerStatus = $taRec->taName;

            $kleur = self::GetSpelerStatusKleur($mhRec->mhSpelerStatus);

            $htmls[]    = $mhRec->mhNaam
                        . "<span style='background-color: $kleur; margin-left: 10px'>&nbsp;<b>$spelerStatus</b>&nbsp;</span>";

        }


        if ($aantal){

            $statusHTML = "<ul>";

            foreach ($htmls as $key=>$html){
                $statusHTML .= "<li>$html</li>";
            }

            $statusHTML .= "</ul>";

            $statusHTML = "<div class='jumbotron well' style='padding: 5px; padding-bottom: 0px; margin: 0px'>$statusHTML</div>";


        }


        // -------------
        // Einde functie
        // -------------

        return $statusHTML;

    }


    // -----------------
    // END CLASS SSP_EMO
    // -----------------
}

?>