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
$path = "news-2017.xml";
$xml = new DomDocument;
$xml->load($path);
$xpath = new DomXPath($xml);

$items = $xml->getElementsByTagName('item');

//Cascade assets need for page assignment
//$folder 				= $admin->getAsset(a\Folder::TYPE, '3109032e814f4e10029a25a6eaf90212' )->getChildren();


$cascade_name = array();
$post_name = array();

foreach ($items as $item) {

	$post   	  = $item->getElementsByTagName('post_name')[0]->textContent;
	$post_id	  = $item->getElementsByTagName('post_id')[0]->textContent;
	$post_name[]  = $post . '-' . $post_id;
	
}





foreach($folder as $child){

if($child->getType() == "page"){

	$page 					= $child->getAsset($service);
	$cascade_name[] 		= $page->getName();

	
	 }
}


 $t = array_diff( $post_name, $cascade_name );
echo count($t);

foreach ($t as $a) {
	file_put_contents("news-2017-missing.txt", $a.PHP_EOL, FILE_APPEND);
	
}

echo "<pre>";
print_r($cascade_name);
echo "</pre>";


/*


	


echo "ok, done-zo";



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
*/

?>