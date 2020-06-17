<?php

require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;

u\DebugUtility::setTimeSpaceLimits(18000);
 

$folder = $admin->getAsset(a\Folder::TYPE, '64b34427814f4e106a274cf07a5b198d' )->getChildren();

$len = count($folder);

$st = array_slice($folder, 0, $len/2);
$nd = array_slice($folder, count($folder)/2, count($folder));
//$nd = array_reverse($nd);


foreach($nd as $child){

	try{

	 $page 			= $child->getAsset($service);
	 $legImg 		= $page->getStructuredData()->getText("media;legacyImg");

	 if(!is_null($legImg) && preg_match("(httpss)", $legImg) || preg_match("(http://)", $legImg)){

		 	$pattern 		= [ "(http://)", "(httpss)" ];
			$replacement 	= [ "https://", "https"];
			$legImg			= preg_replace($pattern, $replacement, $legImg);

			echo "<br/>";
		 	echo $legImg;
		 	echo "<br/>";

			$page->getStructuredData()->setText("media;legacyImg", $legImg)->getHostAsset()->edit();
			$page->publish();
			echo "Found one: " . $page->getPath();

			}


		}   catch(Exception $e){	
		       //$page->display();	  		
			   	echo $e;
					  	}
			catch(Error $er){
				//$page->display();
			   	echo $er;
					  	}	 

}

?>