<?php

require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;
include_once("ws_auth.php"); 



$section = ['events','features','history'];

$years = array();


$folder = $admin->getAsset(a\Folder::TYPE, '5e7f9d1c814f4e1005d8d12b95b0861c')->getChildren();
foreach($folder as $f){
	
	if($f->getType() == "page"){
		$id = $f->getID();
		$page = $admin->getAsset(a\Page::TYPE, $id);
		$meta = $page->getMetadata();
		$sDate = $meta->getStartDate();
		array_push($years, substr($sDate,0,4));
	}
}


$years = array_unique($years);

u\DebugUtility::setTimeSpaceLimits(7200);

$site_name = "BL-RTV-WEBS.main";
$folder = $cascade->getAsset(a\Folder::TYPE, '27a51fe9814f4e1053a4bcaee8f5c598');
$ct = $cascade->getAsset(a\ContentType::TYPE, '68fe44e2814f4e105eb9ce4c145da647');

foreach ($years as $year) {
	foreach ($section as $s) {
	
		$pageName = $s . '-' . $year;
		$page = $cascade->createPage($folder, $pageName, $ct);
		$pageID = $page->getID();

		$page_edit = $cascade->getAsset(a\PAGE::TYPE, $pageID);
			  		$page_edit->setRegionNoBlock(
			  			"PHP",
			  			"page-header",
			  			true
			  		)->edit();

		$page_edit = $cascade->getAsset(a\PAGE::TYPE, $pageID);
			  		$page_edit->setRegionFormat(
			  			"PHP",
			  			"lower",
			  			$cascade->getAsset(a\ScriptFormat::TYPE,'788b323f814f4e105eb9ce4ce08138ee')
			  		)->edit();

	}
}



 //u\ReflectionUtility::showMethodSignatures( "cascade_ws_property\Child" );
 //u\DebugUtility::dump($ct);

?>
