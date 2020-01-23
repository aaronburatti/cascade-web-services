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
$path = "amomentofscience.xml";
$xml = new DomDocument;
$xml->load($path);
$xpath = new DomXPath($xml);

$items = $xml->getElementsByTagName('item');

//Cascade assets need for page assignment
$folder 				= $admin->getAsset(a\Folder::TYPE, '26dc99df814f4e100dca85cb21ff2684' )->getChildren();


$wp_xml = array();

foreach ($items as $item) {
	
	$metaKey 				= $item->getElementsByTagName('meta_key');
	$post_name 				= $item->getElementsByTagName('post_name')[0]->textContent;

	
	foreach ($metaKey as $meta) {
			
		if($meta->nodeValue == 'guest-author'){
		 			
		 	$wp_xml[$post_name] = $meta->nextSibling->nextSibling->nodeValue;

		} 
	}		
}


// echo "<pre>";
// print_r($wp_xml);
// echo"</pre>";

// $page = $admin->getAsset(a\Page::TYPE, 'b5d5372e814f4e101b7f44c124d0ad85' );
// $page->getStructuredData()->setText("cat;contCat;Content", "::CONTENT-XML-CHECKBOX::Podcasts")->getHostAsset()->edit();





foreach($folder as $child){

if($child->getType() == "page"){


	$page 		= $child->getAsset($service);


		foreach($wp_xml as $wp => $author){
	

				if( $wp == $page->getName() ) {

				 	try{

				 		echo "</br>";
						print_r($page->getPath());

						$page->getStructuredData()->setText("guest-author", $author)->getHostAsset()->edit();
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

}


?>

