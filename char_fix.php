<?php
require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;

u\DebugUtility::setTimeSpaceLimits(18000);


$folder 				= $admin->getAsset(a\Folder::TYPE, '17d43d23814f4e1069ba3975ee271fbe' )->getChildren();

//$page = $admin->getAsset(a\Page::TYPE, '2826d63b814f4e1069ba3975165c29b3');

//$folder = array_reverse($folder);

foreach($folder as $child){

		try{

		$page 			= $child->getAsset($service);
		$dd 			= $page->getStructuredData();
		// $recipeT 		= $dd->getText("earthEats;recipe;recipeText");
		// $recipeHead 	= $dd->getText("earthEats;recipe;recipeHeader");
		$articleBod 	= html_entity_decode($dd->getText("text"));


		$pattern = [ "[\’]", "[®]", "[—]", "[“]", "[”]", "[&#160;]" ];
		$replacement = [ "&apos;", "&reg;", "&mdash;", "&#34;", "&#34;", " " ];

		// $recipeText 	= preg_replace($pattern, $replacement, $recipeT);
		// $recipeHeader	= preg_replace($pattern, $replacement, $recipeHead);
		$articleBody	= preg_replace($pattern, $replacement, $articleBod);
		

			// ->setText("earthEats;recipe;recipeText", $recipeText)
			//    ->setText("earthEats;recipe;recipeHeader", $recipeHeader)
			
			$dd->setText("text", $articleBody)
			   ->getHostAsset()->edit();
			     

			   if( $articleBod !== $articleBody ){

			   echo "</br>";
			   print_r($page->getPath());

			   $page->publish();

			   }

			} catch(Exception $e){	
			       $page->display();	  		
				   	echo $e;
						  	}
				catch(Error $er){
					$page->display();
				   	echo $er;
						  	}
    
		
}



?>