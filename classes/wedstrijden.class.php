<?php 
     class SSP_wedstrijden
     { // define the class

         // ===================================================================================================
         // Functie: Get ploeg-record
         //
         // In: Ploeg
         //
         // Return: vpRec
         //
         // ===================================================================================================

         Static function Get_vpRec($pPloeg) {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $sqlStat = "Select * from ssp_vp where vpId = $pPloeg";

             if (!$db->Query($sqlStat)) {
                 $db->close();
                 return null;
             }

             if (!$vpRec = $db->Row()) {
                 $db->close();
                 return null;
             } else {
                 $db->close();
                 return $vpRec;
             }

         }

         // ========================================================================================
         // Function: Get vwRec (Gebaseerd op ploeg & datum)
         //
         // In:	- Ploeg
         //		- Datum
         //		- Enkel actief? (actief = gespeeld, te spelen)
         //
         // Return: rhRec
         // ========================================================================================

         static function GetVwRecBasedOnPloegDatum($pPloeg, $pDatum, $pEnkelActief = true)
         {

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...


             if ($pEnkelActief == true)
                 $sqlStat = "Select * from ssp_vw where vwPloeg = $pPloeg and vwDatum = '$pDatum' and (vwStatus = 'GS' or vwStatus = 'TS')";
             else
                 $sqlStat = "Select * from ssp_vw where vwPloeg = $pPloeg and vwDatum = '$pDatum'";

             if (!$db->Query($sqlStat))
                 return null;

             if (!$vwRec = $db->Row())
                 return null;

             $db->close();
             return $vwRec;

         }

         // ====================================================================================
         // Functie: Ophalen wedstrijd info string (datum, tijd, tegenstander, uit/thuis, uitslag
         //
         // In: - WedstrijdId
         //     - type (1=Volledige string, 2=Ingekort - zonder ploegnaam, type enkel indien <> competitie, 3=Titel voor frontpage (datum/tegenstander/uitslag...,, 4= idem 3 met tijd..., 5=Idem 4 met <br> voor tegenstander, F=Frontpage
         //
         // Return: String met alle informatie
         // ======================================================================================

         static function GetWedstrijdInfoString($wedstrijdId, $type = 1)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             include_once(SX::GetClassPath("ploegen.class"));
             include_once(SX::GetClassPath("clubs.class"));
             include_once(SX::GetSxClassPath("tools.class"));

             $infoString = '';

             $today1 = strtotime('-6 hours');
             $today2 = strtotime('tomorrow');

             $query = 'Select * from ssp_vw where vwId = ' . $wedstrijdId;

             if (!$db->Query($query))
                 return '';

             if (!$vwRec = $db->Row())
                 return '';

             $ploegNaam = '';

             if ($type == 1)
                 $ploegNaam = SSP_ploegen::GetNaam($vwRec->vwPloeg) . ' op&nbsp;';

             $datumTijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d %b %y - %H:%M');
             $datumTijd3 = SX_tools::EdtDate($vwRec->vwDatumTijd, '%d/%m');
             $datumTijd4 = SX_tools::EdtDate($vwRec->vwDatumTijd, '%d/%m - %H:%M');

             $datumTijd2 = strtotime($vwRec->vwDatumTijd);
             if ($datumTijd2 >= $today1 and $datumTijd2 <= $today2) {
                 $datumTijd = '<span style="color: red">VANDAAG - ' . SX_tools::EdtDate($vwRec->vwDatumTijd, '%H:%M') . '</span>';
             }

             $tegenstander = $vwRec->vwTegenstander;

             $uitThuis = 'op verplaatsing bij';
             $stand = $vwRec->vwDoelpTegen . '-' . $vwRec->vwDoelpVoor;

             if ($vwRec->vwUitThuis == 'T') {
                 $uitThuis = 'thuis tegen';
                 $stand = $vwRec->vwDoelpVoor . '-' . $vwRec->vwDoelpTegen;
             }

             if ($vwRec->vwStatus != 'GS') {
                 $stand = '';
             }

             $standInfo = ' ';
             if ($stand > ' ')
                 $standInfo = '&nbsp;(uitslag: ' . $stand . ')&nbsp;';


             $style = ' ';
             $extraInfo = 0;

             $afgelast = 0;
             $statusNaam = self::GetWedstrijdStatusOmschrijving($vwRec->vwStatus, $afgelast);

             if ($afgelast == 1) {
                 $style = 'text-decoration: line-through';
                 $extraInfo = '&nbsp;<span style="color:red">:' . $statusNaam . '</span>&nbsp';
             }


             $wedstrijdType = '&nbsp; - ' . self::GetWedstrijdTypeOmschrijving($vwRec->vwType, 'L');

             if ($type == 2 && $vwRec->vwType == 'CW')
                 $wedstrijdType = '&nbsp;';

             $op = '';
             // if ($type == 1)
             //	$op = '&nbsp;op&nbsp;';

             // link naar tegenstander
             $tegenstanderLink = $vwRec->vwTegenstander;

             if ($vwRec->vwClub > 0)
                 $tegenstanderLink = SSP_clubs::GetSiteLink($vwRec->vwClub, $vwRec->vwTegenstander);


             if ($type == 1 || $type == 2) {
                 return '<span style="' . $style . '">' . $ploegNaam . $op . $datumTijd . ' ' . $uitThuis . ' ' . $tegenstander . $standInfo . $wedstrijdType . '</span>';
             }

             if ($type == 3) {

                 $string = $datumTijd3 . ':';

                 if ($vwRec->vwUitThuis == 'T')
                     $string = $string . ' Schelle - ' . $tegenstanderLink;
                 if ($vwRec->vwUitThuis == 'U')
                     $string = $string . ' ' . $tegenstanderLink . ' - Schelle';

                 $string = $string . ' ' . $stand;

                 return $string;

             }

             if ($type == 4) {


                 $string = $datumTijd4 . ':';

                 if ($vwRec->vwUitThuis == 'T')
                     $string = $string . ' Schelle - ' . $tegenstanderLink;
                 if ($vwRec->vwUitThuis == 'U')
                     $string = $string . ' ' . $tegenstanderLink . ' - Schelle';


                 $string = $string . ' ' . $stand;

                 return $string;

             }

             if ($type == 5) {


                 $string = $datumTijd4 . ':' . '<br/>';

                 if ($vwRec->vwUitThuis == 'T')
                     $string = $string . ' Schelle - ' . $tegenstanderLink;
                 if ($vwRec->vwUitThuis == 'U')
                     $string = $string . ' ' . $tegenstanderLink . ' - Schelle';


                 $string = $string . ' ' . $stand;

                 return $string;

             }

             if ($type == 'F'){

                 $ploegNaam = SSP_ploegen::GetNaam($vwRec->vwPloeg , '*NAAMKORT');
                 $datumWedstrijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d/%m %H:%M');

                 $string = $datumWedstrijd;

                 if ($vwRec->vwUitThuis == 'T')
                     $string = "$string&nbsp;&nbsp;<b>$ploegNaam</b> - $vwRec->vwTegenstander";
                 if ($vwRec->vwUitThuis == 'U')
                     $string = "$string&nbsp;&nbsp; $vwRec->vwTegenstander - <b>$ploegNaam</b>";

                 // Type wedstrijd
                 If ($vwRec->vwType != 'CW'){

                     $type = self::GetWedstrijdTypeOmschrijving($vwRec->vwType, 'K');

                     $string = "$string <span class='text-white bg-dark' >&nbsp;<b>$type</b>&nbsp;</span>";


                 }

                 return $string;

             }


         }

         // ================================================================================
         // Functie: Ophalen wedstrijd-status omschrijving
         //
         // In:	Code
         //
         // Out: Afgelast? (0/1)
         //
         // Return: Omschrijving
         //
         // =================================================================================

         static function GetWedstrijdStatusOmschrijving($code, &$pAfgelast)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $omschrijving = $code;
             $pAfgelast = '0';

             $query = "Select * from ssp_ws where wsCode = '" . $code . "'";

             if (!$db->Query($query))
                 return $omschrijving;

             if (!$wsRec = $db->Row())
                 return $omschrijving;

             $omschrijving = $wsRec->wsNaam;
             $pAfgelast = $wsRec->wsAfgelast;

             return $omschrijving;

         }

         // ===========================================================================
         // Functie: Ophalen wedstrijd-type omschrijving
         //
         // In: - code
         //     - Type (L/K)   L=Lange omschrijving (DEFAULT), K=Korte omschrijvingµ
         //     - Ronde(H/T)
         //
         // Return: Omschrijving
         // ============================================================================

         static function GetWedstrijdTypeOmschrijving($code, $type = 'L', $ronde = ' ')
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $omschrijving = $code;

             $query = "Select * from ssp_wt where wtCode = '" . $code . "'";

             if (!$db->Query($query))
                 return $omschrijving;

             if (!$wtRec = $db->Row())
                 return $omschrijving;


             if ($type == "L")
                 $omschrijving = $wtRec->wtNaam;
             else
                 $omschrijving = $wtRec->wtNaamKort;

             if ($ronde == 'H')
                 $omschrijving = $omschrijving . ' (Heen)';
             if ($ronde == 'T')
                 $omschrijving = $omschrijving . ' (Terug)';


             return $omschrijving;

         }


         // ===========================================================
         // Functie: Get wedstrijd "extra info" as tooltip
         //
         // In: - WedstrijdId
         //	   - Type (*TOOLTIP, *HTML)
         //
         // Return: Extra info (as tooltip)
         // ============================================================

         static function GetWedstrijdExtraInfoTooltip($pWedstrijdId, $pType = '*TOOLTIP'){

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             include_once(SX::GetSxClassPath("tools.class"));

             $query = 'Select * from ssp_vw where vwId = ' . $pWedstrijdId;

             if (!$db->Query($query))
                 return null;

             if (!$vwRec = $db->Row())
                 return null;

             // (extra) info
             $extraInfo = null;

             if ($vwRec->vwOrigDatum > 0)
                 $extraInfo = 'Verplaatste wedstrijd van ' . SX_tools::EdtDate($vwRec->vwOrigDatum, '%d %b %Y') . '<br/><br/>';

             $infoTooltip = '';

             $infoHTML = null;

             if ($extraInfo)
                 $infoHTML = $extraInfo;

             if ($vwRec->vwInfo)
                $infoHTML = $infoHTML . nl2br($vwRec->vwInfo);

             if ($infoHTML)

                 $infoTooltip = '<div style="float: left; margin-top: -2px"><span class="hasTooltip glyphicon glyphicon-info-sign" style="font-size: 150%; color: #0A529E; text-decoration: none"></span>' . '<div style="display:none">' . $infoHTML . '</div></div>';

             if ($pType == '*HTML')
                 return $infoHTML;
             else
                 return $infoTooltip;

         }

         // ===========================================================
         // Functie: Get wedstrijd ploeg-ID
         //
         // In: - WedstrijdId
         //
         // Return: Ploeg-ID
         // ============================================================

         static function GetPloeg($wedstrijdId)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $query = 'Select * from ssp_vw where vwId = ' . $wedstrijdId;

             if (!$db->Query($query))
                 return 0;

             if (!$vwRec = $db->Row())
                 return '';

             return $vwRec->vwPloeg;

         }
         // =============================================================
         // Functie: Ophalen authoriteit ploegen voor verslagentoepassing
         //
         // In: - userId
         //
         // Return: array met ploegen
         // =============================================================

         static function GetAuthVerslagenToepassing($pUserId)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             include_once(SX::GetSxClassPath("auth.class"));
             include_once(SX::GetClassPath("settings.class"));


             $ploegen = array();
             $allePloegen = false;
             $actiefSeizoen = SSP_settings::GetActiefSeizoen();

             // -----------------------
             // Authority ALLE ploegen?
             // -----------------------

             $allePloegen = false;

             $sqlStat = "Select * from sx_au_authority where auApCode = 'wedstrijd_uitslag_en_verslag_beheer' and auLevel = '*FULL'";

             if (!$db->Query($sqlStat)) {
                 $db->close();
                 return $ploegen;
             }

             while ($auRec = $db->Row()) {


                 if ($auRec->auUserId > ' ' && $auRec->auUserId == $pUserId)
                     $allePloegen = true;

                 if ($auRec->auRole > ' ') {

                     $checkUserRole = SX_auth::CheckUserRole($pUserId, $auRec->auRole);

                     if ($checkUserRole == true)
                         $allePloegen = true;


                 }


                 if ($allePloegen == true)
                     break;

             }

             if ($allePloegen == true) {

                 $sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' order by vpSort";

                 if (!$db->Query($sqlStat)) {
                     $db->close();
                     return $ploegen;
                 }

                 while ($vpRec = $db->Row()) {
                     $ploegen[] = $vpRec->vpId;
                 }

                 return $ploegen;


             }

             // -------------------------------------------------------
             // Authority SPECIFIEKE ploegen? (op basis authority file)
             // -------------------------------------------------------

             $sqlStat = "Select * from sx_au_authority where auApCode = 'wedstrijd_uitslag_en_verslag_beheer' and auLevel = '*PLOEG' and auTeamId <> 0";

             if (!$db->Query($sqlStat)) {
                 $db->close();
                 return $ploegen;
             }

             while ($auRec = $db->Row()) {

                 if ($auRec->auUserId > ' ' && $auRec->auUserId == $pUserId) {
                     $ploegen[] = $auRec->auTeamId;
                     continue;
                 }

                 if ($auRec->auRole > ' ') {

                     $checkUserRole = SX_auth::CheckUserRole($pUserId, $auRec->auRole);

                     if ($checkUserRole == true)
                         $ploegen[] = $auRec->auTeamId;


                 }

             }

             // -----------------------------------------------------
             // Authority SPECIFIEKE ploegen? (op basis ploegen file)
             // -----------------------------------------------------

             $sqlStat = "Select * from sx_au_authority where auApCode = 'wedstrijd_uitslag_en_verslag_beheer' and auLevel = '*PLOEG' and (auTeamId is NULL or auTeamId = 0)";

             if (!$db->Query($sqlStat)) {
                 $db->close();
                 return $ploegen;
             }

             $specifiekePloeg = false;


             while ($auRec = $db->Row()) {

                 if ($auRec->auUserId > ' ' && $auRec->auUserId == $pUserId) {
                     $specifiekePloeg = true;
                     break;
                 }

                 if ($auRec->auRole > ' ') {

                     $checkUserRole = SX_auth::CheckUserRole($pUserId, $auRec->auRole);

                     if ($checkUserRole == true) {
                         $specifiekePloeg = true;
                         break;
                     }


                 }


             }

             if ($specifiekePloeg == true) {


                 $sqlStat = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and (vpTrainer = '$pUserId' or vpTrainer2 = '$pUserId' or vpTrainer3 = '$pUserId' or vpTrainer4 = '$pUserId' or vpTrainer5 = '$pUserId' or vpDelege = '$pUserId' or vpDelege2 = '$pUserId'  or vpDelege3 = '$pUserId')";

                 if (!$db->Query($sqlStat)) {
                     $db->close();
                     return $ploegen;
                 }


                 while ($vpRec = $db->Row()) {

                     $ploegen[] = $vpRec->vpId;

                     // Ook andere ploegen zelfde categorie...
                     $voetbalCat = $vpRec->vpVoetbalCat;
                     $vpId = $vpRec->vpId;

                     $sqlStat2 = "Select * from ssp_vp where vpSeizoen = '$actiefSeizoen' and vpVoetbalCat = '$voetbalCat' and vpId <> $vpId";

                     if ($db2->Query($sqlStat2)) {

                         while ($vpRec2 = $db2->Row()) {
                             $ploegen[] = $vpRec2->vpId;
                         }

                     }

                     $db2->close();

                 }
             }

             // -------------
             // Einde functie
             // -------------

             $db->close();
             return $ploegen;
         }

         // =============================================================
         // Functie: Mail de thuis-wedstrijden  (volgende 7 dagen)
         //
         // In: Type =	*ALWAYS = Alle wedstrijden (Default)
         //				*CHANGE = Enkel indien er wijzigingen zouden zijn
         //
         // Return:¨*NONE
         // =============================================================

         static function MailThuisWedstrijden($pType = '*ALWAYS')
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             include_once(Sx::GetSxClassPath("tools.class"));
             include_once(Sx::GetSxClassPath("tables.class"));


             $date = new DateTime();

             $date->modify('monday this week');

             $maandag = $date->format('Y-m-d');
             $date->modify('+1 days');
             $dinsdag = $date->format('Y-m-d');
             $date->modify('+1 days');
             $woensdag = $date->format('Y-m-d');
             $date->modify('+1 days');
             $donderdag = $date->format('Y-m-d');
             $date->modify('+1 days');
             $vrijdag = $date->format('Y-m-d');
             $date->modify('+1 days');
             $zaterdag = $date->format('Y-m-d');
             $date->modify('+1 days');
             $zondag = $date->format('Y-m-d');

             $date->modify('+1 days');
             $maandag2 = $date->format('Y-m-d');
             $date->modify('+1 days');
             $dinsdag2 = $date->format('Y-m-d');
             $date->modify('+1 days');
             $woensdag2 = $date->format('Y-m-d');
             $date->modify('+1 days');
             $donderdag2 = $date->format('Y-m-d');
             $date->modify('+1 days');
             $vrijdag2 = $date->format('Y-m-d');
             $date->modify('+1 days');
             $zaterdag2 = $date->format('Y-m-d');
             $date->modify('+1 days');
             $zondag2 = $date->format('Y-m-d');

             $week = date('W');

             $toBeMailed = true;

             if ($pType == "*CHANGE") {

                 $sqlStat = "Select count(*) as aantal from ssp_vw where vwUitThuis = 'T' and vwStatus <> 'GS' and vwDatum >= DATE_ADD(now(), INTERVAL -1 DAY) and (vwDatum = '$maandag' or vwDatum = '$dinsdag' or vwDatum = '$woensdag' or vwdatum = '$donderdag' or vwDatum = '$vrijdag' or vwDatum = '$zaterdag' or vwDatum = '$zondag' or vwDatum = '$maandag2' or vwDatum = '$dinsdag2' or vwDatum = '$woensdag2' or vwdatum = '$donderdag2' or vwDatum = '$vrijdag2' or vwDatum = '$zaterdag2' or vwDatum = '$zondag2') and (vwMailWeek <> $week or vwMailWedstrijdStatus <> vwStatus or vwMailWedstrijdDatum <> vwDatum or vwMailWedstrijdTijd <> vwTijd)";

                 $db->Query($sqlStat);

                 $vwRec = $db->Row();

                 if ($vwRec->aantal <= 0)
                     $toBeMailed = false;

             }

             if ($toBeMailed == false)
                 return;

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

             // --------------------
             // Reeds mail gestuurd?
             // --------------------

             $reedMailGestuurd = false;

             $sqlStat = "Select count(*) as aantal from ssp_vw where vwMailWeek = $week";

             $db->Query($sqlStat);

             $vwRec = $db->Row();

             if ($vwRec->aantal > 0)
                 $reedMailGestuurd = true;

             // --------------------------
             // Thuiswedstrijden DEZE week
             // --------------------------

             $sqlStat = "Select * from ssp_vw inner join ssp_vp on vpId = vwPloeg where vwUitThuis = 'T' and vwDatum >= DATE_ADD(now(), INTERVAL -1 DAY) and (vwDatum = '$maandag' or vwDatum = '$dinsdag' or vwDatum = '$woensdag' or vwdatum = '$donderdag' or vwDatum = '$vrijdag' or vwDatum = '$zaterdag' or vwDatum = '$zondag') order by vwDatum, vwTijd,  vpSort Desc";

             $mailBody .= "<h1>Thuiswedstrijden - Deze week</h1>" . "\r\n";

             $mailBody .= "<table><tr><th>Datum</th><th>Aanvang</th><th>Ploeg</th><th>Tegenstander</th><th>Status</th><th>Type</th><th>Wijziging*</th></tr>" . "\r\n";

             $db->Query($sqlStat);

             $afgelast = 0;

             while ($vwRec = $db->Row()) {

                 $toBeMailed = true;

                 $datum = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d %b');
                 $tijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%H:%M');
                 $type = self::GetWedstrijdTypeOmschrijving($vwRec->vwType);
                 $status = self::GetWedstrijdStatusOmschrijving($vwRec->vwStatus, $afgelast);

                 $style = '';
                 if ($vwRec->vwStatus != 'TS' and $vwRec->vwStatus != 'GS')
                     $style = "style='text-decoration: line-through'";

                 $wijziging = '';

                 if ($vwRec->vwMailWeek == 0 && $reedMailGestuurd == true)
                     $wijziging = "Toegevoegde wedstrijd";

                 if ($vwRec->vwMailWeek <> 0) {

                     if ($vwRec->vwMailWedstrijdStatus <> $vwRec->vwStatus)
                         $wijziging = $wijziging .= "Status";
                     if ($vwRec->vwMailWedstrijdDatum <> $vwRec->vwDatum)
                         if ($wijziging > " ")
                             $wijziging = $wijziging .= " & Datum";
                         else
                             $wijziging = "Datum";
                     if ($vwRec->vwMailWedstrijdTijd <> $vwRec->vwTijd)
                         if ($wijziging > " ")
                             $wijziging = $wijziging .= " & Aanvang";
                         else
                             $wijziging = "Aanvang";

                 }

                 if ($wijziging <= ' ')
                     $wijziging = '&nbsp;';


                 $colorStatus = 'black';
                 if ($vwRec->vwStatus <> 'TS' and $vwRec->vwStatus <> 'GS')
                     $colorStatus = 'red';

                 $mailBody .= "<tr><td $style>$datum</td><td $style>$tijd</td><td $style>$vwRec->vpNaam</td><td $style>$vwRec->vwTegenstander</td><td style='color: $colorStatus'>$status</td><td>$type</td><td style='color: red'>$wijziging</td></tr>" . "\r\n";

             }

             $mailBody .= "</table>";

             // Set log fields
             $sqlStat = "Update ssp_vw set vwMailWeek = $week, vwMailWedstrijdStatus = vwStatus, vwMailWedstrijdDatum = vwDatum, vwMailWedstrijdTijd = vwTijd  where vwUitThuis = 'T' and vwDatum >= DATE_ADD(now(), INTERVAL -1 DAY) and (vwDatum = '$maandag' or vwDatum = '$dinsdag' or vwDatum = '$woensdag' or vwdatum = '$donderdag' or vwDatum = '$vrijdag' or vwDatum = '$zaterdag' or vwDatum = '$zondag')";

             $db->Query($sqlStat);

             // ------------------------------
             // Thuiswedstrijden VOLGENDE week
             // ------------------------------

             $sqlStat = "Select * from ssp_vw inner join ssp_vp on vpId = vwPloeg where vwUitThuis = 'T' and vwDatum >= DATE_ADD(now(), INTERVAL -1 DAY) and (vwDatum = '$maandag2' or vwDatum = '$dinsdag2' or vwDatum = '$woensdag2' or vwdatum = '$donderdag2' or vwDatum = '$vrijdag2' or vwDatum = '$zaterdag2' or vwDatum = '$zondag2') order by vwDatum, vwTijd, vpSort Desc";

             $mailBody .= "<h1>Thuiswedstrijden - Volgende week</h1>";

             $mailBody .= "<table><tr><th>Datum</th><th>Aanvang</th><th>Ploeg</th><th>Tegenstander</th><th>Status</th><th>Type</th><th>Wijziging*</th></tr>" . "\r\n";

             $db->Query($sqlStat);

             $afgelast = 0;

             while ($vwRec = $db->Row()) {

                 $toBeMailed = true;

                 $datum = SX_tools::EdtDate($vwRec->vwDatumTijd, '%a %d %b');
                 $tijd = SX_tools::EdtDate($vwRec->vwDatumTijd, '%H:%M');
                 $type = self::GetWedstrijdTypeOmschrijving($vwRec->vwType);
                 $status = self::GetWedstrijdStatusOmschrijving($vwRec->vwStatus, $afgelast);

                 $style = '';
                 if ($vwRec->vwStatus != 'TS' and $vwRec->vwStatus != 'GS')
                     $style = "style='text-decoration: line-through'";

                 $wijziging = '';

                 if ($vwRec->vwMailWeek == 0 && $reedMailGestuurd == true)
                     $wijziging = "Toegevoegde wedstrijd";

                 if ($vwRec->vwMailWeek <> 0) {

                     if ($vwRec->vwMailWedstrijdStatus <> $vwRec->vwStatus)
                         $wijziging = $wijziging .= "Status";
                     if ($vwRec->vwMailWedstrijdDatum <> $vwRec->vwDatum)
                         if ($wijziging > " ")
                             $wijziging = $wijziging .= " & Datum";
                         else
                             $wijziging = "Datum";
                     if ($vwRec->vwMailWedstrijdTijd <> $vwRec->vwTijd)
                         if ($wijziging > " ")
                             $wijziging = $wijziging .= " & Aanvang";
                         else
                             $wijziging = "Aanvang";

                 }

                 if ($wijziging <= ' ')
                     $wijziging = '&nbsp;';

                 $colorStatus = 'black';
                 if ($vwRec->vwStatus <> 'TS' and $vwRec->vwStatus <> 'GS')
                     $colorStatus = 'red';

                 $mailBody .= "<tr><td $style>$datum</td><td $style>$tijd</td><td $style>$vwRec->vpNaam</td><td $style>$vwRec->vwTegenstander</td><td style='color: $colorStatus'>$status</td><td>$type</td><td style='color: red'>$wijziging</td></tr>" . "\r\n";

             }

             // Set log fields
             $sqlStat = "Update ssp_vw set vwMailWeek = $week, vwMailWedstrijdStatus = vwStatus, vwMailWedstrijdDatum = vwDatum, vwMailWedstrijdTijd = vwTijd  where vwUitThuis = 'T' and vwDatum >= DATE_ADD(now(), INTERVAL -1 DAY) and (vwDatum = '$maandag2' or vwDatum = '$dinsdag2' or vwDatum = '$woensdag2' or vwdatum = '$donderdag2' or vwDatum = '$vrijdag2' or vwDatum = '$zaterdag2' or vwDatum = '$zondag2')";

             $db->Query($sqlStat);

             $mailBody .= "</table>" . "\r\n";
             $mailBody .= "<br/>(*) = Wijziging sinds vorige mail" . "\r\n";
             $mailBody .= "<br/><br/>Deze mail wordt sowieso elke maandag en vrijdag verzonden ('s morgens)." . "\r\n";
             $mailBody .= "<br/>Bij elke wijzigingen wordt deze mail opnieuw verzonden ('smorgens en/of late namiddag)" . "\r\n";
             $mailBody .= "<br/><br/>Sportieve groet," . "\r\n";
             $mailBody .= "<br/><br/>De Webmeester" . "\r\n";
             // --------
             // End HTML
             // --------

             $mailBody .= "</body></html>" . "\r\n";

             // ---------
             // Send mail
             // ---------

             if ($toBeMailed == true) {

                 // $mailAdres = "horeca@schellesport.be;ticketing@schellesport.be; voetbal@schellesport.be; sportief@schellesport.be";
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'horeca@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'teamhoreca@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'ticketing@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'voetbal@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'sportief@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'logistiek@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'wedstrijden@schellesport.be');
                 SX_tools::SendMail('Schelle Sport - Thuiswedstrijden ', $mailBody, 'friekienick@hotmail.com');

             }

         }

         // ====================================================================================
         // Functie: Opvullen ssp_wk (wedstrijden nweek/dagen)
         //
         // In: Geen
         //
         // Return: *NONE
         // ======================================================================================

         static function Fill_ssp_wk()
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             // -----------------------
             // Ophalen hoogste seizoen
             // -----------------------

             $sqlStat = "Select max(vpSeizoen) as seizoen from ssp_vp";
             $db->Query($sqlStat);


             if (!$vpRec = $db->Row())
                 return;

             $seizoen = $vpRec->seizoen;

             // ----------------------------------------
             // Verwerk alle wedstrijden hoogste seizoen
             // ----------------------------------------

             $arr_datum = array();
             $arr_dag = array();
             $arr_dagCode = array();
             $arr_tekst = array();

             $date = new DateTime();

             $day = $date->format('w');

             if ($day <> 1)
                 $date->modify('monday this week');

             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "MA";
             $arr_tekst[] = null;

             $date->modify('+1 days');
             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "DI";
             $arr_tekst[] = null;

             $date->modify('+1 days');
             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "WO";
             $arr_tekst[] = null;

             $date->modify('+1 days');
             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "DO";
             $arr_tekst[] = null;

             $date->modify('+1 days');
             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "VR";
             $arr_tekst[] = null;

             $date->modify('+1 days');
             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "ZA";
             $arr_tekst[] = null;

             $date->modify('+1 days');
             $arr_datum[] = $date->format('Y-m-d');
             $arr_dagCode[] = "ZO";
             $arr_tekst[] = null;

             // -----------------
             // 20 volgende weken
             // -----------------

             for ($w = 1; $w <= 20; $w++) {

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "MA+$w";
                 $arr_tekst[] = null;

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "DI+$w";
                 $arr_tekst[] = null;

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "WO+$w";
                 $arr_tekst[] = null;

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "DO+$w";
                 $arr_tekst[] = null;

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "VR+$w";
                 $arr_tekst[] = null;

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "ZA+$w";
                 $arr_tekst[] = null;

                 $date->modify('+1 days');
                 $arr_datum[] = $date->format('Y-m-d');
                 $arr_dagCode[] = "ZO+$w";
                 $arr_tekst[] = null;

             }

             // -----------------
             // Keep veld "tekst"
             // -----------------

             $sqlStat = "Select * from ssp_wk_weken where wkTekst is not null";
             $db->Query($sqlStat);

             while ($wkRec = $db->Row()) {

                 $datum = $wkRec->wkDatum;

                 $key = array_search($datum, $arr_datum);

                 if ($key <> false)
                     $arr_tekst[$key] = $wkRec->wkTekst;

             }

             // ----------------------------------------
             // Opnieuw naanmaken bestand "ssp_wk_weken"
             // ----------------------------------------


             $sqlStat = "Delete From ssp_wk_weken";
             $db->Query($sqlStat);

             $sqlStat = "Select vwDatum, year(vwDatum) as jaar, week(vwDatum,1) as week, dayofweek(vwDatum) as dag from ssp_vw inner join ssp_vp ON vpId = vwPloeg AND vpSeizoen =  '$seizoen'where vwUitThuis = 'T'";
             $db->Query($sqlStat);

             while ($vwRec = $db->Row()) {

                 $jaar = $vwRec->jaar;
                 $week = $vwRec->week;
                 $dag = $vwRec->dag;
                 $datum = $vwRec->vwDatum;

                 $dagCode = '*NONE';
                 $tekst = null;

                 $key = array_search($datum, $arr_datum);

                 if ($key <> false) {
                     $dagCode = $arr_dagCode[$key];
                     $tekst = $arr_tekst[$key];
                 }

                 $values["wkJaar"] = MySQL::SQLValue($jaar, MySQL::SQLVALUE_NUMBER);
                 $values["wkWeek"] = MySQL::SQLValue($week, MySQL::SQLVALUE_NUMBER);
                 $values["wkDag"] = MySQL::SQLValue($dag, MySQL::SQLVALUE_NUMBER);
                 $values["wkDatum"] = MySQL::SQLValue($datum, MySQL::SQLVALUE_DATE);
                 $values["wkDagCode"] = MySQL::SQLValue($dagCode);
                 $values["wkTekst"] = MySQL::SQLValue($tekst);

                 $db2->InsertRow("ssp_wk_weken", $values);

             }


             // -------------
             // Einde functie
             // -------------

             return;

         }

         // ====================================================================================
         // Functie: Ophalen aantal thuiswedstrijden
         //
         // In: Datum
         //
         //
         // Return: Aantal Thuiswedstrijden
         // ======================================================================================

         static function GetAantalThuiswedstrijden($pDatum)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $sqlStat = "Select count(*) as aantal from ssp_vw where vwDatum = '$pDatum' and vwUitThuis = 'T' and (vwStatus = 'TS' or vwStatus = 'GS')";
             $db->Query($sqlStat);

             // -------------
             // Einde functie
             // -------------

             if ($vwRec = $db->Row())
                 return $vwRec->aantal;
             else
                 return 0;


         }

         // ====================================================================================
         // Functie: Controle alle kleedkamers ingevuld?
         //
         // In: Datum
         //
         //
         // Return: *ALLE, *GEEN, *DEEL
         // ======================================================================================

         static function ChkKleedkamersIngevuld($pDatum)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $aantalWedstrijden = self::GetAantalThuiswedstrijden($pDatum);

             $sqlStat = "Select count(*) as aantal from ssp_vw where vwDatum = '$pDatum' and vwUitThuis = 'T' and (vwStatus = 'TS' or vwStatus = 'GS') and vwKleedkamer > ' '";
             $db->Query($sqlStat);
             $vwRec = $db->Row();

             $kleedkamerSchelle = $vwRec->aantal;

             $sqlStat = "Select count(*) as aantal from ssp_vw where vwDatum = '$pDatum' and vwUitThuis = 'T' and (vwStatus = 'TS' or vwStatus = 'GS') and vwKleedkamerTegenstander > ' '";
             $db->Query($sqlStat);
             $vwRec = $db->Row();

             $kleedkamerTegenstander = $vwRec->aantal;

             // -------------
             // Einde functie
             // -------------

             If ($kleedkamerSchelle <= 0 and $kleedkamerTegenstander <= 0)
                 return '*GEEN';

             If ($kleedkamerSchelle == $aantalWedstrijden and $kleedkamerTegenstander == $aantalWedstrijden)
                 return '*ALLE';

             return '*DEEL';


         }

         // ====================================================================================
         // Functie: Controle alle terreinen ingevuld?
         //
         // In: Datum
         //
         //
         // Return: *ALLE, *GEEN, *DEEL
         // ======================================================================================

         static function ChkTerreinenIngevuld($pDatum)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $aantalWedstrijden = self::GetAantalThuiswedstrijden($pDatum);

             $sqlStat = "Select count(*) as aantal from ssp_vw where vwDatum = '$pDatum' and vwUitThuis = 'T' and (vwStatus = 'TS' or vwStatus = 'GS') and vwTerrein > ' '";
             $db->Query($sqlStat);
             $vwRec = $db->Row();

             $terreinen = $vwRec->aantal;


             // -------------
             // Einde functie
             // -------------

             If ($terreinen <= 0)
                 return '*GEEN';

             If ($terreinen == $aantalWedstrijden)
                 return '*ALLE';

             return '*DEEL';

         }
         // ====================================================================================
         // Functie: Controle alle scheidsrechters ingevuld?
         //
         // In: Datum
         //
         //
         // Return: *ALLE, *GEEN, *DEEL
         // ======================================================================================

         static function ChkScheidsrechtersIngevuld($pDatum)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object

             $aantalWedstrijden = self::GetAantalThuiswedstrijden($pDatum);

             $sqlStat = "Select count(*) as aantal from ssp_vw where vwDatum = '$pDatum' and vwUitThuis = 'T' and (vwStatus = 'TS' or vwStatus = 'GS') and vwScheidsrechter > ' '";
             $db->Query($sqlStat);
             $vwRec = $db->Row();

             $scheidsrechters = $vwRec->aantal;


             // -------------
             // Einde functie
             // -------------

             If ($scheidsrechters <= 0)
                 return '*GEEN';

             If ($scheidsrechters == $aantalWedstrijden)
                 return '*ALLE';

             return '*DEEL';

         }

         // ====================================================================================
         // Functie: Opvullen wedstrijddag Kleedkamers & velden & scheidsrechters met defaults
         //
         // In: Datum
         //
         //
         // Return: NONE
         // ======================================================================================

         static function FillKleedkamersEnTerreinenMetDefaults($pDatum)
         {

             include(SX::GetSxClassPath("mysql.incl"));    // Creates a $db object
             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);


             $aantalWedstrijden = self::GetAantalThuiswedstrijden($pDatum);

             $sqlStat = "Select * from ssp_vw inner join ssp_vp on vpId = vwPloeg where vwDatum = '$pDatum' and vwUitThuis = 'T' and (vwStatus = 'TS' or vwStatus = 'GS')";
             $db->Query($sqlStat);

             while ($vwRec = $db->Row()) {

                 $kleedkamerSchelle = '';
                 $kleedkamerTegenstander = '';
                 $terrein = '';
                 $scheidsrechter = '';
                 $changed = false;

                 if ($vwRec->vwKleedkamer)
                     $kleedkamerSchelle = $vwRec->vwKleedkamer;
                 if ($vwRec->vwKleedkamerTegenstander)
                     $kleedkamerTegenstander = $vwRec->vwKleedkamerTegenstander;
                 if ($vwRec->vwTerrein)
                     $terrein = $vwRec->vwTerrein;
                 if ($vwRec->vwScheidsrechter)
                     $scheidsrechter = $vwRec->vwScheidsrechter;

                 if ($kleedkamerSchelle <= ' ' and $vwRec->vpKleedkamer > ' ') {
                     $changed = true;
                     $kleedkamerSchelle = $vwRec->vpKleedkamer;
                 }

                 if ($kleedkamerTegenstander <= ' ' and $vwRec->vpKleedkamerTegenstander > ' ') {
                     $changed = true;
                     $kleedkamerTegenstander = $vwRec->vpKleedkamerTegenstander;
                 }

                 if ($terrein <= ' ' and $vwRec->vpTerrein > ' ') {
                     $changed = true;
                     $terrein = $vwRec->vpTerrein;
                 }

                 if ($scheidsrechter <= ' ' and $vwRec->vpScheidsrechter > ' ') {
                     $changed = true;
                     $scheidsrechter = $vwRec->vpScheidsrechter;
                 }

                 if ($changed == true) {

                     $vwId = $vwRec->vwId;

                     $sqlStat = "Update ssp_vw set vwKleedkamer = '$kleedkamerSchelle', vwKleedkamerTegenstander = '$kleedkamerTegenstander', vwTerrein = '$terrein', vwScheidsrechter = '$scheidsrechter' where vwid = $vwId";

                     $db2->Query($sqlStat);


                 }


             }


             // -------------
             // Einde functie
             // -------------


             return;

         }

         // ====================================================================================
         // Functie: Ophalen (default) wedstrijd-categorie
         //
         // In: Ploeg
         //     Type wedstrijd
         //
         // Return: Categorie
         // ======================================================================================

         static function GetWedstrijdCatDefault($pPloeg, $pType) {

             $vpRec = self::Get_vpRec($pPloeg);

             if (! $vpRec)
                 return null;

             if ($pType == 'CW' and $vpRec->vpCategorieCompetitie)
                 return $vpRec->vpCategorieCompetitie;
             else
                 return $vpRec->vpVoetbalCat;

         }

         // ====================================================================================
         // Functie: Ophalen (default) wedstrijd-niveau
         //
         // In: Ploeg
         //
         // Return: Categorie
         // ======================================================================================

         static function GetWedstrijdNivDefault($pPloeg) {

             $vpRec = self::Get_vpRec($pPloeg);

             if (! $vpRec)
                 return null;

             // -------------
             // Einde functie
             // -------------

             return $vpRec->vpNiveauCompetitie;

         }

         // ====================================================================================
         // Functie: Ophalen HTML-snippet Wedstrijd-CARD (BS4)
         //
         // In: Wedstrijd
         //
         // Return: HTML-snippet
         // ======================================================================================

         static function GetWedstrijdCard($pWedstrijd){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));
             include_once(SX::GetSxClassPath("tools.class"));

             // -------------------
             // Ophalen nodige data
             // -------------------


             $imgCancelled = SX::GetSiteImgPath('cancelled.jpg');

             $vwRec = SSP_db::Get_SSP_vwRec($pWedstrijd);
             $vpRec = SSP_db::Get_SSP_vpRec($vwRec->vwPloeg);

             $status = $vwRec->vwStatus;
             $sqlStat = "Select * from ssp_ws where wsCode = '$status'";
             $db->Query($sqlStat);
             $wsRec = $db->Row();


             $tegenstander = $vwRec->vwTegenstander;
             $wedstrijd ="";

             $datum = SX_tools::EdtDate($vwRec->vwDatum);
             $aanvang = "Aanvang: " . substr($vwRec->vwTijd,0,5);

             if ($vwRec->vwUitThuis == 'T') {
                 $uitThuis = "Thuiswedstrijd";
                 $wedstrijd .= "$vpRec->vpNaam - $tegenstander";
             }
             else {
                 $uitThuis = "Op verplaatsing";
                 $wedstrijd .= "$tegenstander - $vpRec->vpNaam";
             }


             $extraInfo = self::GetWedstrijdExtraInfoTooltip($pWedstrijd, '*HTML');

             $verslagURL = "";

             if ($vwRec->vwVerslagStatus == 'OK')
                 $verslagURL = "/index.php?app=wedstrijdverslag_subpage&parm1=" . $vwRec->vwId;


             $uitslag = "";

             if ($status != 'TS'){

                 if (($status == 'GS') and ($vwRec->vwDoelpVoor != null)) {

                     if ($vwRec->vwUitThuis == 'T')
                         $uitslag = "($vwRec->vwDoelpVoor - $vwRec->vwDoelpTegen)";
                     if ($vwRec->vwUitThuis == 'U')
                         $uitslag = "($vwRec->vwDoelpTegen - $vwRec->vwDoelpVoor)";

                  }

             }


             // -----------------------------
             // Aanmaken BS4 CARD HTML-snippet
             // ------------------------------

             $html = "<div class='card'>";

             $html .= "<div class='card-header'>";
             $html .= "<h4>$datum</h4>";
             $html .= "</div>";

            // $html .= "<img class=\"card-img img-responsive\" src=\"$imgCancelled\" width='500px'>";
             $html .= "<div class='card-body'>";

             $html .= "<h6 class=\"card-subtitle\">$uitThuis</h6>";
             $html .= "<h5 class='card-title'>$wedstrijd $uitslag</h5>";

             if ($status == 'TS' or $status == 'GS')
                $html .= "$aanvang";
             else
                 $html .= "<b style='color:red'>$wsRec->wsNaam</b>";

             // - - - - - - - - - - - - - - - - - - - - - - - - -
             // Kleedkamer of veld (Enkel voor thuis-wedstrijden)
             // - - - - - - - - - - - - - - - - - - - - - - - - -

             if ($vwRec->vwUitThuis == 'T' and ($vwRec->vwKleedkamer or $vwRec->vwTerrein)){

                 $kleedkamer = $vwRec->vwKleedkamer;
                 $kleedkamerCode = $kleedkamer;
                 $terrein = $vwRec->vwTerrein;

                 if ($kleedkamer == 'K01' or $kleedkamer == 'K01H')
                     $kleedkamerCode = "1";
                 if ($kleedkamer == 'K02' or $kleedkamer == 'K02H')
                     $kleedkamerCode = "2";
                 if ($kleedkamer == 'K03' or $kleedkamer == 'K03H')
                     $kleedkamerCode = "3";
                 if ($kleedkamer == 'K04' or $kleedkamer == 'K04H')
                     $kleedkamerCode = "4";
                 if ($kleedkamer == 'K05' or $kleedkamer == 'K05H')
                     $kleedkamerCode = "5";
                 if ($kleedkamer == 'K06' or $kleedkamer == 'K06H')
                     $kleedkamerCode = "6";
                 if ($kleedkamer == 'K07' or $kleedkamer == 'K07H')
                     $kleedkamerCode = "7";
                 if ($kleedkamer == 'K08' or $kleedkamer == 'K08H')
                     $kleedkamerCode = "8";
                 if ($kleedkamer == 'K09' or $kleedkamer == 'K09H')
                     $kleedkamerCode = "9";
                 if ($kleedkamer == 'K10' or $kleedkamer == 'K10H')
                     $kleedkamerCode = "10";
                 if ($kleedkamer == 'K11' or $kleedkamer == 'K11H')
                     $kleedkamerCode = "11";
                 if ($kleedkamer == 'K12' or $kleedkamer == 'K12H')
                     $kleedkamerCode = "12";


                 $html .= "<br>Kleedkamer: $kleedkamerCode, Terrein: $terrein";

             }


             if ($wedstrijdStatus)
                 $html .= "<br/>$wedstrijdStatus";

             if ($verslagURL)
                $html .= "<br/><br/><a target='_blank' href=\"$verslagURL\" class=\"card-link text-primary\">Wedstrijdverslag</a>";

             // $html .= "</div>";
             $html .= "</div>";


             if ($extraInfo)
                 $html .= "<div class='card-footer bg-warning'>$extraInfo</div>";

             $html .= "</div>";

             // -------------
             // Einde functie
             // -------------

             return $html;

         }

         // ====================================================================================
         // Functie: Opladen wedstrijden - Aanvullen werkfile (ssp_ow_opladen_wedsrijden)
         // ======================================================================================

         static function OpladenWedstrijden_prepare(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("settings.class"));

             $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);

             $sqlStat = "Select * from ssp_ow_opladen_wedstrijden where owStatus = '*OPGELADEN'";
             $db->Query($sqlStat);

            echo $sqlStat;

             while ($owRec = $db->Row()){

                 $scheidsrechterCode = null;
                 $terreinCode = null;
                 $ploegId = 0;

                 if ($owRec->owScheidsrechter == 'GEEN')
                     $scheidsrechterCode = '*GEEN';
                 if ($owRec->owScheidsrechter == 'KBVB')
                     $scheidsrechterCode = '*KBVB';

                 if ($owRec->owTerrein){

                     $terrein = strtoupper($owRec->owTerrein);

                     $sqlStat = "Select * from sx_ta_tables where taTable = 'VW_TERREIN' and upper(taName) = '$terrein'";
                     $db2->Query($sqlStat);

                     if ($taRec = $db2->Row())
                         $terreinCode = $taRec->taCode;

                 }


                 $ploegNaam = strtoupper($owRec->owPloeg);
                 $seizoen = SSP_settings::GetSeizoen($owRec->owDatum);

                 $sqlStat = "Select * from ssp_vp where upper(vpNaam) = '$ploegNaam' and vpSeizoen = '$seizoen'";
                 $db2->Query($sqlStat);

                 if ($vpRec = $db2->Row())
                     $ploegId = $vpRec->vpId;

                 $values = array();
                 $where = array();

                 $values["owTerreinCode"] =  MySQL::SQLValue($terreinCode, MySQL::SQLVALUE_TEXT);
                 $values["owScheidsrechterCode"] =  MySQL::SQLValue($scheidsrechterCode, MySQL::SQLVALUE_TEXT);
                 $values["owPloegId"] =  MySQL::SQLValue($ploegId, MySQL::SQLVALUE_NUMBER);

                 $values["owStatus"] =  MySQL::SQLValue("*AANGEVULD", MySQL::SQLVALUE_TEXT);

                 $where["owId"] =  MySQL::SQLValue($owRec->owId, MySQL::SQLVALUE_NUMBER);

                 $db2->UpdateRows("ssp_ow_opladen_wedstrijden", $values, $where);


             }



             // -------------
             // Einde functie
             // -------------

         }

         // ====================================================================================
         // Functie: Opladen wedstrijden - ssp_ow_opladen_wedsrijden -> ssp_vp
         // ======================================================================================

         static function OpladenWedstrijden_create() {

            include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
            $db2 = new MySQL(true, $sql_database, $sql_server, $sql_userId, $sql_password);
            include_once(SX::GetClassPath("_db.class"));

            $sqlStat = "Select * from ssp_ow_opladen_wedstrijden where owStatus = '*AANGEVULD' and owPloegId <>  0 order by owPloegId";
            $db->Query($sqlStat);

            $i = 0;

            $ploegId = 0;

            while ($owRec = $db->Row()){

                $i++;
                if ($i > 10000)
                    break;

                // --------------------------------------
                // Ophalen alle nodige wedstrijd-gegevens
                // --------------------------------------

                $datum = $owRec->owDatum;
                $tijd = $owRec->owUur;
                $datumTijd = substr($datum,0,10) . ' ' . $tijd;
                $tegenstander = $owRec->owTegenstander;
                $uitThuis=  substr(strtoupper($owRec->owUitThuis),0,1);
                $heenTerug=  substr(strtoupper($owRec->owHeenTerug),0,1);
                $reeks = $owRec->owReeks;
                $terrein = $owRec->owTerreinCode;
                $scheidsrechter = $owRec->owScheidsrechterCode;
                $curDateTime = date('Y-m-d H:i:s');

                if ($owRec->owPloegId != $ploegId) {

                    $ploegId = $owRec->owPloegId;
                    $vpRec = SSP_db::Get_SSP_vpRec($ploegId);

                    $sqlStat = "update ssp_vp set ssp_vp vpReeks = '$reeks' where vpId = $ploegId";
                    $db2->Query($sqlStat);

                }

                $categorie = $vpRec->vpVoetbalCat;
                $niveau = $vpRec->vpNiveauCompetitie;

                // ------------------
                // Aanmaken wedstrijd
                // ------------------

                $values = array();

                $values["vwPloeg"] = MySQL::SQLValue($ploegId, MySQL::SQLVALUE_NUMBER);

                $values["vwDatum"] = MySQL::SQLValue($datum, MySQL::SQLVALUE_DATE);
                $values["vwTijd"] = MySQL::SQLValue($tijd, MySQL::SQLVALUE_TIME);
                $values["vwDatumTijd"] = MySQL::SQLValue($datumTijd, MySQL::SQLVALUE_DATETIME);
                $values["vwTegenstander"] = MySQL::SQLValue($tegenstander, MySQL::SQLVALUE_TEXT);
                $values["vwType"] = MySQL::SQLValue("CW", MySQL::SQLVALUE_TEXT);
                $values["vwStatus"] = MySQL::SQLValue("TS", MySQL::SQLVALUE_TEXT);
                $values["vwUitThuis"] = MySQL::SQLValue($uitThuis, MySQL::SQLVALUE_TEXT);
                $values["vwHeenTerug"] = MySQL::SQLValue($heenTerug, MySQL::SQLVALUE_TEXT);

                $values["vwReeks"] = MySQL::SQLValue($reeks, MySQL::SQLVALUE_TEXT);
                $values["vwNiveau"] = MySQL::SQLValue($niveau, MySQL::SQLVALUE_TEXT);
                $values["vwCategorie"] = MySQL::SQLValue($categorie, MySQL::SQLVALUE_TEXT);

                $values["vwTerrein"] = MySQL::SQLValue($terrein, MySQL::SQLVALUE_TEXT);
                $values["vwScheidsrechter"] = MySQL::SQLValue($scheidsrechter, MySQL::SQLVALUE_TEXT);

                $values["vwUserUpdate"] = MySQL::SQLValue('*OPGELADEN', MySQL::SQLVALUE_TEXT);
                $values["vwDatumUpdate"] = MySQL::SQLValue($curDateTime, MySQL::SQLVALUE_DATETIME);

                $id = $db2->InsertRow("ssp_vw", $values);

                $owId = $owRec->owId;
                $sqlStat = "Update ssp_ow_opladen_wedstrijden set owStatus = '*AANGEMAAKT' where owId = $owId";
                $db2->Query($sqlStat);

            }





         }

         // ====================================================================================
         // Functie: Zet alle wedstrijden in het verleden op "gespeeld"
         //
         // In:
         //
         // Return: Aantal wedstrijden op gespeeld gezet
         // ======================================================================================

         static function SetWedstrijdenOpGespeeld(){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

             $sqlStat = "Select * from ssp_vw where vwStatus = 'TS' and  DATE(DATE_ADD(vwDatum, INTERVAL + 1 DAY)) < now()";

             // -------------
             // Einde functie
             // -------------

         }

         // ====================================================================================
         // Functie: Get array met alle TEAM info (voor opbouwen JSON string etc)
         //
         // In: PLOEG-id
         //     Ook wedstrijd-info?
         //
         // Return: array met alle wedstrijd-info
         // ======================================================================================

         static function GetTeamInfoArray($pTeam, $pGetWedstrijdInfo = false){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));
             include_once(SX::GetClassPath("settings.class"));

             $arrTEAM = array();

             $vpRec = SSP_db::Get_SSP_vpRec($pTeam);

             if (! $vpRec)
                 return $arrTEAM;

             // --------
             // Categorie
             // ---------

             $cat = $vpRec->vpVoetbalCat;

             $taRec = SSP_db::Get_SX_taRec('VOETBAL_CAT', $cat);
             if ($taRec)
                 $cat = $taRec->taName;

             // ------
             // FOTO's
             // ------

             $fotoPath = null;
             $fotoGrootPath = null;

             $fotos = json_decode($vpRec->vpFoto);
             if ($fotos) {
                 foreach ($fotos as $foto) {
                     $fotoPath = SX_tools::GetFilePath($foto->name);
                 }
             }

             $fotos = json_decode($vpRec->vpFotoGroot);
             if ($fotos) {
                 foreach ($fotos as $foto) {
                     $fotoGrootPath = SX_tools::GetFilePath($foto->name);
                 }
             }

             // --------
             // Trainers
             // --------

             $trainers = array();

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

             $arrTEAMTRAINERS = array();

             foreach ($trainers as $trainer) {

                 $adRec = SSP_db::Get_SSP_adRec($trainer);

                 $arrTEAMTRAINER = array();

                 $arrTEAMTRAINER['naam'] = utf8_encode($adRec->adVoornaamNaam);
                 $arrTEAMTRAINER['mail'] = utf8_encode($adRec->adMail);
                 $arrTEAMTRAINER['tel'] = utf8_encode($adRec->adTel);

                 $arrTEAMTRAINERS[] = $arrTEAMTRAINER;

             }

             // --------------
             // Afgevaardigden
             // --------------

             $afgevaardigden = array();
             $arrTEAMAFGEVAARDIGDEN = array();

             if ($vpRec->vpDelege)
                 $afgevaardigden[] = $vpRec->vpDelege;
             if ($vpRec->vpDelege2)
                 $afgevaardigden[] = $vpRec->vpDelege2;
             if ($vpRec->vpDelege3)
                 $afgevaardigden[] = $vpRec->vpDelege3;

             foreach ($afgevaardigden as $afgevaardigde) {

                 $adRec = SSP_db::Get_SSP_adRec($afgevaardigde);

                 $arrTEAMAFGEVAARDIGDE = array();

                 $arrTEAMAFGEVAARDIGDE['naam'] = utf8_encode($adRec->adVoornaamNaam);
                 $arrTEAMAFGEVAARDIGDE['mail'] = utf8_encode($adRec->adMail);
                 $arrTEAMAFGEVAARDIGDE['tel'] = utf8_encode($adRec->adTel);

                 $arrTEAMAFGEVAARDIGDEN[] = $arrTEAMAFGEVAARDIGDE;

             }


             // -----------
             // Wedstrijden
             // -----------

             if ($pGetWedstrijdInfo) {

                 $arrWEDSTRIJDEN = array();
                 $arrWEDSTRIJD = array();

                 $sqlStat = "Select * from ssp_vw where vwPloeg = $pTeam order by vwDatum";

                 $db->Query($sqlStat);

                 while ($vwRec = $db->Row()) {

                     $arrWEDSTRIJD = self::GetWedstrijdInfoArray($vwRec->vwId);
                     $arrWEDSTRIJDEN[] = $arrWEDSTRIJD;

                 }

             }

             // ------------
             // Build ARRAY
             // ------------

             $arrTEAM['naam'] = utf8_encode($vpRec->vpNaam);
             $arrTEAM['categorie'] = utf8_encode($cat);
             $arrTEAM['seizoen'] = utf8_encode($vpRec->vpSeizoen);
             $arrTEAM['foto'] = utf8_encode($fotoPath);
             $arrTEAM['fotoGroot'] = utf8_encode($fotoGrootPath);
             $arrTEAM['wedstrijden'] = $arrWEDSTRIJDEN;
             $arrTEAM['trainers'] = $arrTEAMTRAINERS;
             $arrTEAM['afgevaardigden'] = $arrTEAMAFGEVAARDIGDEN;

             $basePHP = $_SESSION['SX_BASEPHP'];

             $arrTEAM['ploegpagina'] = "$basePHP?app=subpage-team&id=$pTeam";

             // -------------
             // Einde functie
             // -------------


             return $arrTEAM;

         }

         // ====================================================================================
         // Functie: Get array met all WEDSTRIJD info (voor opbouwen JSON string etc)
         //
         // In: WedstrijdId
         //
         // Return: array met alle wedstrijd-info
         // ======================================================================================

         static function GetWedstrijdInfoArray($pWedstrijd){

             include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
             include_once(SX::GetClassPath("_db.class"));

             include_once(SX::GetSxClassPath("tools.class"));
             include_once(SX::GetClassPath("settings.class"));

             $arrWEDSTRIJD = array();

             $vwRec = SSP_db::Get_SSP_vwRec($pWedstrijd);

             if (! $vwRec)
                 return $arrWEDSTRIJD;

             $datum = SX_tools::EdtDate($vwRec->vwDatum);
             $tijd = substr($vwRec->vwTijd,0,5);

             $tegenstander = $vwRec->vwTegenstander ;

             if ($vwRec->vwUitThuis == 'U')
                 $wedstrijd = "$tegenstander - Schelle Sport";
             else
                 $wedstrijd = "Schelle Sport - $tegenstander";


            $scheidsrechter = "";
            if ($vwRec->vwScheidsrechter && (substr($vwRec->vwScheidsrechter,0,1) != '*')){

                $adRec = SSP_db::Get_SSP_adRec($vwRec->vwScheidsrechter);

                if ($adRec)
                    $scheidsrechter = $adRec->adVoornaamNaam;

            }

            $kleedkamer = "";

            if ($vwRec->vwKleedkamer){

                $taRec = SSP_db::Get_SX_taRec('VW_KLEEDKAMER',$vwRec->vwKleedkamer);

                if ($taRec)
                    $kleedkamer = $taRec->taName;

            }

            $terrein = "";

            if ($vwRec->vwTerrein){

                $taRec = SSP_db::Get_SX_taRec('VW_TERREIN',$vwRec->vwTerrein);

                if ($taRec)
                    $terrein = $taRec->taName;

            }


            $type = "";

             if ($vwRec->vwType){

                 $type = self::GetWedstrijdTypeOmschrijving($vwRec->vwType, 'L');

             }

             $uitThuis = "";

             if ($vwRec->vwUitThuis) {

                 if ($vwRec->vwUitThuis == 'U')
                     $uitThuis = "Uit";
                 else
                     $uitThuis = "Thuis";

             }

             // ------------
             // Build ARRAY
             // ------------

             $arrWEDSTRIJD['datum'] = utf8_encode($datum);
             $arrWEDSTRIJD['tijd'] = utf8_encode($tijd);
             $arrWEDSTRIJD['type'] = utf8_encode($type);
             $arrWEDSTRIJD['tegenstander'] = utf8_encode($vwRec->vwTegenstander);
             $arrWEDSTRIJD['wedstrijd'] = utf8_encode($wedstrijd);
             $arrWEDSTRIJD['scheidsrechter'] = utf8_encode($scheidsrechter);
             $arrWEDSTRIJD['kleedkamer'] = utf8_encode($kleedkamer);
             $arrWEDSTRIJD['terrein'] = utf8_encode($terrein);
             $arrWEDSTRIJD['uitThuis'] = utf8_encode($uitThuis);

             $arrWEDSTRIJDEN[] = $arrWEDSTRIJD;

             // -------------
             // Einde functie
             // -------------

             return $arrWEDSTRIJD;

         }

         // -----------
         // EINDE CLASS
         // -----------

     }

?>