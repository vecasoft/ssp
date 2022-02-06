<?php 

class SSP_doc { // define the class


	// ========================================================================================
	// Function: Get Document Authority
	//
	// In:	docId
	//		userId
	//     	
	//
	// Return: *UPDATE, *READ, *NONE
	// ========================================================================================
                     
	static function GetDocAuth($pDocId, $pUserId = '*CURRENT') {   
          
		include(SX::GetSxClassPath('mysql.incl'));  // Create DB-object...  
		include_once(SX::GetSxClassPath('auth.class'));  
		include_once(SX::GetSxClassPath("sessions.class"));
		
		$query = 'Select * from doc_do_documents where doId = ' . $pDocId;

		
		if (!$db->Query($query)) 
			return '*NONE';
		
		if (! $doRec = $db->Row())
			return '*NONE';

			
		// ----------------------------
		// Owner has always full access
		// ----------------------------
		
		if ($pUserId != '*CURRENT')
			$userId = $pUserId;
		else
			$userId = SX_sessions::GetSessionUserId();
				
		if ($doRec->doUserCreated == $pUserId || $doRec->doUserLastUpdate == $userId)
			return '*UPDATE';

						
		// ------------------------------
		//	Get authority based on folder
		// ------------------------------
		
  		$fldrAuth =  self::GetFolderAuth($doRec->doFolder, $userId);
		
		if ($fldrAuth == '*FULL' or $fldrAuth == '*READ')
			return '*READ';
		
		if ($fldrAuth == '*SUPER')
			return '*UPDATE';
		
		return '*NONE';
 
	}

	// ========================================================================================
	// Function: Get Folder Authority
	//
	// In:	folder
	//      userId 
	//
	// Return: Authority (*FULL, *READ, *NONE)
	// ========================================================================================
                     
	static function GetFolderAuth($pFolder, $pUserId) {   
          
		include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...  
		include_once(SX::GetSxClassPath('auth.class'));  
		include_once(SX::GetSxClassPath("sessions.class"));

		$query = 'Select * from doc_fa_folderauth where faFolder = ' . $pFolder;
	
		if (!$db->Query($query)) 
			return '*NONE';
			
		if ($db->RowCount() <= 0)
			return '*NONE';
		
		$auth = '*NONE';
		
		while($faRec = $db->Row()) {

			$isInRole = SX_auth::CheckUserRole($pUserId, $faRec->faRole);
			
			if ($isInRole == true) {
				
				if ($auth == '*NONE' && $faRec->faAuth != '*NONE')
					$auth = $faRec->faAuth;
				elseif ($auth == '*READ' and $faRec->faAuth == '*FULL' )
					$auth = $faRec->faAuth;			
				elseif ($auth == '*FULL' and $faRec->faAuth == '*SUPER' )
					$auth = $faRec->faAuth;					
		
			}

			}
		
		// ------------
		// Function end
		// ------------
		
  		return $auth;
  
	}


    // ========================================================================================
    // Function: Get Document URL
    //
    // In:	Document-ID
    //      OR Code
    //      Bijhouden # views & datum laatste view (defaukt = false)
    //
    // Return: URL
    // ========================================================================================

    static function GetDocURL($pDocument, $pCode = '',  $pKeepNumberOfViews = false){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        if ($pDocument)
            $sqlStat = "Select * from doc_do_documents where doId = $pDocument";

        Elseif ($pCode)
            $sqlStat = "Select * from doc_do_documents where doCode = '$pCode'";
        else
            return '*NONE';

        $db->Query($sqlStat);

        if (! ($doRec = $db->Row()))
            return '*NONE';

        // ------------
        // Keep # views
        // ------------

        if ($pKeepNumberOfViews){

            $document = $doRec->doId;

            $sqlStat = "update doc_do_documents set doNumberOfViews = doNumberOfViews + 1, doDateLastView = now() where doId = $document";
            $db->Query($sqlStat);

        }


        // -------------
        // Einde functie
        // -------------

        $url = $doRec->doURL;

        // Replace http with https
        If (substr($url,0,5) == 'http:')
            $url = str_replace('http:', 'https:', $url);


        return $url;

    }

    // ========================================================================================
    // Function: Fill doCode (alternate key)
    //
    // In:	Document-ID
    // ========================================================================================

    static function FillDocumentCode($pDocument){

	    include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from doc_do_documents where doId = $pDocument";

        $db->Query($sqlStat);

        if (!$doRec = $db->Row())
            return;

        if ($doRec->doCode)
            return; // Reeds opgevuld

        $doCode = null;

        for ($i=0; $i < 99; $i++) {

            $doCodeTest = substr(str_shuffle("0123456789abcdefghjkmnprstuvwxyzABCDEFGHIJKLMNOPQ"), 0, 7);

                $sqlStat = "Select * from doc_do_documents where doCode = '$doCodeTest'";
                $db->Query($sqlStat);

                if (! $doRec = $db->Row()){

                    $doCode = $doCodeTest;
                    break;

                }

        }

        if (! $doCode)
            return;

        // ------
        // Update
        // ------

        $values = array();
        $where = array();

        $values["doCode"] =  MySQL::SQLValue($doCode);

        $where["doId"] =  MySQL::SQLValue($pDocument, MySQL::SQLVALUE_NUMBER);

        $db->UpdateRows("doc_do_documents", $values, $where);


    }


    // ========================================================================================
    //  Get Folders JSON( Als input voor JSTREE)
    //
    // In:	Geen
    //
    // Return: JSON-string
    //
    // ========================================================================================

    static function GetFoldersJSON(){

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...

        $sqlStat = "Select * from doc_fd_folders where (fdMother = 0) or (fdMother is null) order by fdSort, fdName";

        $db->Query($sqlStat);

        $json = "";

        while ($fdRec = $db->Row()){

            $folder = $fdRec->fdId;
            $jsonFd = self::GetFolderJSON($folder);

            if (! $json)
                $json = $jsonFd;
            else
                $json .= ",$jsonFd";

        }

        $json = "[" . $json . "]";

        // -------------
        // Einde functie
        // -------------

        return $json;


    }


    // ========================================================================================
    //  Get Folder JSON (Als input voor JSTREE)
    //
    // In:	Folder
    //
    // Return: JSON-string
    //
    // ========================================================================================

    static function GetFolderJSON($pFolder) {

        include(SX::GetSxClassPath("mysql.incl"));  // Create DB-object...
        include_once(SX::GetClassPath("_db.class"));

        $fdRec = SSP_db::Get_DOC_fdRec($pFolder);

        $id = $fdRec->fdId;
        $text = $fdRec->fdName;

        // ------------------
        // Ophalen "children"
        // ------------------

        $sqlStat = "Select * from doc_fd_folders where fdMother = $id order by fdSort, fdName";
        error_log($sqlStat);
        $db->Query($sqlStat);

        $children = array();
        $chkChildren = false;
        $childrenJSON = "";

        while ($fdRec = $db->Row()){

            $childFolder = $fdRec->fdId;
            error_log("childfolder: $childFolder");

            $jsonChild = self::GetFolderJSON($childFolder);

            $children[] = $jsonChild;
            $chkChildren = true;
        }

        if ($chkChildren){

            $teller = 1;

            foreach ($children as $child){

                if ($teller == 1)
                    $childrenJSON = "\"children\": [";

                if ($teller > 1)
                    $childrenJSON .= ",";

                $childrenJSON .= $child;

                $teller++;


            }

            $childrenJSON .= "]";

        }

        if (!$childrenJSON)
            $json = "{\"id\":$id,\"text\":\"$text\"}";
        else
            $json = "{\"id\":$id,\"text\":\"$text\" , $childrenJSON}";

        // -------------
        // Einde functie
        // -------------

        return $json;

    }



    // -----------
    // Einde CLASS
    // -----------


}
       
?>