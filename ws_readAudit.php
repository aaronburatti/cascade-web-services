<?php

include_once('ws_auth.php');

$auditParam = new stdClass();
$auditParam->identifier='aed2a37c814f4e102295f2469c418be3';



$audit = [//'authentication'=> $auth_param,
		  'auditParameters'=>$auditParam ];

try{

	$auditParams = array('authentication'=>$auth, 'auditParameters'=>$audit);

	$reply = $client->readAudits($auditParams);
	var_dump($reply);

}catch(Exception $e){
	var_dump($e);
}


?>