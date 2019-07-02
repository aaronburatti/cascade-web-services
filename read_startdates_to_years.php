<?php
require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );


use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;

$sections = [ 'events', 'features', 'history' ];
$years    = array();

// get all folder-contained assets
$assets  = $admin->getAsset(
    a\Folder::TYPE, '5e7f9d1c814f4e1005d8d12b95b0861c' )->getChildren();

foreach( $assets as $a )
{
    // store page information
    if( $a->getType() == "page" )
    {
        $page  = $a->getAsset( $service );
        $meta  = $page->getMetadata();
        $sDate = $meta->getStartDate();
        array_push( $years, substr( $sDate, 0, 4 ) );
    }
}

// eliminate repeated years
$years = array_unique( $years );


    foreach( $sections as $section )
    {
    file_put_contents('theinbox-'.$section.'-years.php', $years);
    }

?>