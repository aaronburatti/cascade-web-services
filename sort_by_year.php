<?php
require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;
u\DebugUtility::setTimeSpaceLimits(18000);
$folder = $admin->getAsset(a\Folder::TYPE, '297ab63a814f4e10086fabccc64da308')->getChildren();
// $st = array_slice($folder, 0, count($folder)/2);
// $nd = array_slice($folder, count($folder)/2, count($folder));
// $fold = array_reverse($st);
foreach ($folder as $child) 
{
	
	if($child->getType() == "page")
	{
		
		try
		{
				$page 		  = $child->getAsset($service);
				$date = new DateTime($page->getMetadata()->getStartDate());
	            $year = $date->format("Y");
	            echo $year.PHP_EOL;

	            if($cascade->getAsset( a\Folder::TYPE, "/news-".$year, "BL-RTV-WEBS.news" ) != NULL){
	            	$page->move($cascade->getAsset( a\Folder::TYPE, "/news-".$year, "BL-RTV-WEBS.news" ), false);
	            }

		}catch(Exception $e){
			echo $e;
		} catch(Error $e){
			echo $e;
		}
	}
}
?>