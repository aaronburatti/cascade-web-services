<?php
require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;

u\DebugUtility::setTimeSpaceLimits(18000);
 


//load the post xml
$path = "harmonia.xml";
$xml = new DomDocument;
$xml->load($path);
$xpath = new DomXPath($xml);

$items = $xml->getElementsByTagName('item');

//Cascade assets need for page assignment
$folder 				= $admin->getAsset(a\Folder::TYPE, 'b5709e5b814f4e101b7f44c18d8e75d0' )->getChildren();


$wp_xml = array();

foreach ($items as $item) {
	
	$category			= $item->getElementsByTagName('category');
	$post_name 				= $item->getElementsByTagName('post_name')[0]->textContent;

	foreach ($category as $cat) {
		
		if( $cat->nodeValue == "podcasts" ) {

			array_push($wp_xml, $post_name);

			}			

		
	}

}





foreach($folder as $child){

$page 		= $child->getAsset($service);


	foreach($wp_xml as $wp){
	

				if( $wp == $page->getName() ) {

			 	try{
				print_r($page->getPath());

				$page->getStructuredData()->setText("cat;contCat:Content", "Podcasts")->getHostAsset()->edit();
				$page->publish();
				echo" found one";

				}catch(Exception $e){	
			       $page->display();	  		
				   	echo $e;
						  	}
				catch(Error $er){
					$page->display();
				   	echo $er;
						  	}


			}

		}

	}





?>