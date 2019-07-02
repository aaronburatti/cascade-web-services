<?php

require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;
include_once("ws_auth.php"); 

//empty arrays needed later
$years  = array();
$names   = array();
$pageName = array();

$ids = ["87d2e310814f4e1047b03a0ecebfde46", "0e761e8f814f4e10381fd14e0f9ec52d", "87d2220f814f4e1047b03a0efbee2369"];

//loop through folders
foreach($ids as $id){

	//get folder's children
	$folder = $admin->getAsset(a\Folder::TYPE, $id);
	$childPages = $folder->getChildren();

	//find years and name of section
	foreach ($childPages as $child) {
		array_push($years, explode("-", $child->getPathPath())[2]);
		array_push($names, explode("/", $child->getPathPath())[1]);
	}

	//make leave only one copy of each value and isolate the name as a string
	$name = array_unique($names);
	$name = $name[0];
	$years = array_unique($years);

	//loop through years
	foreach ($years as $year){
		//get an array ready, that is reinitialized with each year
		$months = array();
		//loop through each page
		foreach($childPages as $child){
			//get the array of string parts
			$pageName = explode("-", $child->getPathPath());
			//if the page contains the $year value
			if($pageName[2] == $year){
				//get the numeric value of the month
				$monthNum = explode("-", $child->getPathPath())[3];
				//add to array
				array_push($months, $monthNum);		
			}

		}
		//put the values in order
		sort($months);
		//write values to a file
		file_put_contents($name."-".$year."-months.php", json_encode($months));
	}

}

?>