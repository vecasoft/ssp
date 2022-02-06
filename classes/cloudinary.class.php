<?php

class SSP_cloudinary { // define the class


		// ========================================================================================
		// Functie: Send file naar "cloudinary"
		//
		// In: - File path 
		// 	   - File name
		// 	   - Folder
		//
		// Return: Resulting array
		// ========================================================================================
                 
    
		static function SendFile($pFilePath, $pFileName = "", $pFolder = "") {

            include_once $_SESSION["SX_BASEPATH"] . '/vendor/autoload.php';

			//require_once(SX::GetClassPath("Cloudinary", "cloudinary"));
 			//require_once(SX::GetClassPath("Api", "cloudinary"));
  			//require_once(SX::GetClassPath("Uploader", "cloudinary"));

						
			Cloudinary::config(array( 
			  "cloud_name" => "schellesport", 
			  "api_key" => "375293375912472", 
			  "api_secret" => "rxQMtcNrRWIMx130Y1T7eWTifcc" 
			));	
			
			$pathInfo = pathinfo($pFilePath);
			$fileExt = strtolower($pathInfo["extension"]);
			
			if ($fileExt != 'pdf') {
				$arrReturn = \Cloudinary\Uploader::upload($pFilePath, array("resource_type" => "auto", "folder" => $pFolder, "public_id" => $pFileName));
			}
			
			if ($fileExt == 'pdf') {
				
				$arrReturn = \Cloudinary\Uploader::upload($pFilePath, array("resource_type" => "auto", "folder" => $pFolder, "public_id" => $pFileName));
				
				$fileExt2 = strtolower($arrReturn["format"]);
				
				if ($fileExt2 != "pdf") {
					$arrReturn = \Cloudinary\Uploader::upload($pFilePath, array("resource_type" => "raw", "folder" => $pFolder, "public_id" => $pFileName));
				}
			
			}		
		

			$arrReturn["format"]= strtolower($arrReturn["format"]);

			if (($arrReturn["format"] == "raw") or ($arrReturn["format"] <= " ")) {

				$arrReturn["format"] = $fileExt;
				
			}
				
			
			
			return $arrReturn;
			
		}    


		// ========================================================================================
		// Functie: Send file naar "cloudinary"
		//
		// In: - File path 
		// 	   - File name
		// 	   - Folder
		//
		// Return: Resulting array
		// ========================================================================================
                 
    
		static function DelFile($pPublicId) {


            include_once $_SESSION["SX_BASEPATH"] . '/vendor/autoload.php';
			//require_once(SX::GetClassPath("Cloudinary", "cloudinary"));
 			//require_once(SX::GetClassPath("Api", "cloudinary"));
  			//require_once(SX::GetClassPath("Uploader", "cloudinary"));

						
			Cloudinary::config(array( 
			  "cloud_name" => "schellesport", 
			  "api_key" => "375293375912472", 
			  "api_secret" => "rxQMtcNrRWIMx130Y1T7eWTifcc" 
			));		
			
			\Cloudinary\Uploader::destroy($pPublicId, array("public_id" => $pPublicId));
			
			\Cloudinary\Uploader::destroy($pPublicId, array("resource_type" => "raw", "public_id" => $pPublicId));			
			
		}
        
}
       
?>