<?php

require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;

u\DebugUtility::setTimeSpaceLimits(18000);


$folder 				= $admin->getAsset(a\Folder::TYPE, '441168c8814f4e10459dc6a817514a59' );
$contentType			= $admin->getAsset(a\ContentType::TYPE, 'bc0fa04e814f4e1060f6cc4f9b91b2d1');
$postFormat 			= $admin->getAsset(a\ScriptFormat::TYPE, 'f0cf41b8814f4e1062a68fcca083e4b7');
$indexBlock 			= $admin->getAsset(a\IndexBlock::TYPE, 'bc01fa60814f4e1060f6cc4f45deb7a3');


$authorKeys = array();
$authorNames = array();
//load the authors
$authors = fopen("newsusers.csv", "r");
//Array of author handles
while(!feof($authors)){
$authorKeys[] = fgetcsv($authors)[0];
}
//reload the authors to reset the placeholder
$authors = fopen("newsusers.csv", "r");
//Array of author names
while(!feof($authors)){
$authorNames[] = fgetcsv($authors)[1];
}


$location = "news-2018-missing-1.txt";
$missing = fopen($location, 'r') ;
$miss = fread($missing, filesize($location));
$missed = preg_split("/[\s,]+/", $miss);



// $mediaPath = "newsmedia-2017.xml";
// $media = new DomDocument;
// $media->load($mediaPath);
// $mediaX = new DomXPath($media);

$path = "news-2018.xml";
$xml = new DomDocument;
$xml->load($path);
$xmlX = new DomXPath($xml);


	$p_name 			 = '';
	$title 				 = '';
	$teaser 			 = '';
	$author 			 = '';
	$date 				 = ''; 
	$tags 				 = '';
	$content 			 = '';
	$post_id 			 = '';
	$metaValue 			 = '';
	$metaKey 			 = '';
	$category 			 = '';
	$excerpt 			 = '';
	$thumbnail_url		 = '';


echo "<pre>";

foreach ($missed as $miss) {
	echo "hi";
	var_dump($miss);

	$return = $xmlX->query("//channel/item/post_name[text() = '".$miss."']");

	$item_nodes = $return[0]->parentNode->childNodes;
	
	

	foreach ($item_nodes as $r) {
		if(get_class($r) == "DOMElement")
		{
			if($r->nodeName == "post_name"){
				$p_name = $r->nodeValue;
			} elseif($r->nodeName == "title"){
				$title = $r->nodeValue;
			} elseif($r->nodeName == "pubDate"){
				
				$date = $r->nodeValue;
				$date = new DateTime($date);
				$date->setTimeZone(new DateTimeZone('UTC'));
				$date = $date->format('Y-m-d\TH:i:s.\0\0\0\Z');

			} elseif($r->nodeName == "creator"){
				$author = $r->nodeValue;
				
				$authNameIndex = array_search($author, $authorKeys);
				$authName = $authorNames[$authNameIndex];
				
				if($r->nodeName == "postmeta" && preg_match("/(guest-author)/", $r->nodeValue)){

					$authName = trim(preg_split("/guest-author/", $r->nodeValue)[1]);
					
				}
				

			} elseif($r->nodeName == "content"){
				
				$content = $r->nodeValue;

				if(preg_match( "<img.*? \/>", $content) == 1){
					 $pattern = "/news\/files/";
				     $replacement ="wpimages/news";
				     $content = preg_replace($pattern, $replacement, $content);
				}

				$pattern = [ "[\’]", "[®]", "[—]", "[“]", "[”]", "[&#160;]", "(\[pullquote.*?\])", "(\[\/pullquote\])", "(\[cf\].*?\[\/cf\])", "(\[photo.*?\])", "(\[slideshow.*?\])", "(\[caption.*\])", "(\[\/caption\])" ];
				$replacement = [ "&apos;", "&reg;", "&mdash;", "&#34;", "&#34;", " ", "<blockquote>" ,"</blockquote>", "", "", "", "", "" ];
				$content = preg_replace($pattern, $replacement, $content);

				$prepend = "<p>";
				$append  = "</p>";

				//make an array of paragraphs
				$paragraphs = explode("\n", $content);
				
				//surround each string with html
				$tmp = array();
				foreach($paragraphs as $paragraph){
					if(empty($paragraph) == false){	
					$paragraph = $prepend.$paragraph.$append;
					array_push($tmp, $paragraph);
					}	
				}

				//bring array back to a whole string
				$content = implode($tmp);				

				preg_match_all("/(<p>http.*?<\/p>)/", $content, $matches);
				$matches = $matches[0];
				$queryMatch = array();
				
				// //loop through the oembed urls
				foreach($matches as $match) {
					
					
					if(strpos($match, "httpv") !== false || strpos($match, "httpvh") !== false || strpos($match, "http://www.youtube") !== false || strpos($match, "https://www.youtube") !== false || strpos($match, "https://www.youtu.be") !== false){
						
						$uri = explode("=", $match)[1];
						$src = "https://www.youtube.com/embed/".$uri;
						
						$src = str_replace("<p>", '', $src);
						$src = str_replace("</p>", '', $src);
					
						$iframe = "<iframe width='560' height='315' src='{$src}' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen='true'></iframe>";
						$content = str_replace($match, $iframe, $content);	
					} else {
						
							
						$subMatch = substr($match, 0, 40);
						
						$subMatch = str_replace("<p>", "", $subMatch);
						$subMatch = str_replace("</p>", '', $subMatch);
						
						$query = "//meta_value[text()[contains(., '$subMatch')]]";
						$q = $xmlX->query($query);
						$queryMatch = $q[0]->textContent;	
						$content = str_replace($match, $queryMatch, $content); 
			
					}
				}

			
			} elseif($r->nodeName == "post_id"){
				$post_id = $r->nodeValue;
			}elseif($r->nodeName == "excerpt"){
				$teaser = strip_tags($r->nodeValue);
			} elseif($r->nodeName == "postmeta" && preg_match("/(thumbnail)/", $r->nodeValue) ){
				 
				 $thumbnail_url = trim(preg_split("/thumbnail/", $r->nodeValue)[1]);
				 $thumbnail_url = trim(preg_split("/_id/", $thumbnail_url)[0]);
				 //print_r($thumbnail_url);
				 echo "<br/>";
				 $pattern = "/news\/files/";
			     $replacement ="wpimages/news";
			     $thumbnail_url = preg_replace($pattern, $replacement, $thumbnail_url);
			     //print_r($thumbnail_url);
				 echo "<br/>";

			     $pattern = "(http://)";
			     $replacement = "https://";
			     $thumbnail_url = preg_replace($pattern, $replacement, $thumbnail_url);
			    //print_r($thumbnail_url);

				 echo "<br/>";
				
			}
			 
		}
		//print_r($r);
	}
	
	if(empty($content) != 1){
	// echo "<br/>";
	// //echo $p_name;
	// echo "<br/>";
	// echo $title;
	// echo "<br/>";
	// //echo $date;
	// echo "<br/>";
	// //echo $author;
	// echo "<br/>";
	// echo $content;

	// echo "<br/>";
	// //echo $teaser;
	// echo "<br/>";
	// //echo $post_id;
	// echo "<br/>";
	// //echo empty($thumbnail_url);
	// echo $thumbnail_url;
	// echo "<br/>";
	// echo "<br/>";
	// echo "<br/>";
	// echo "<br/>";
	// echo "<br/>";
	// echo "<br/>";
	// echo "<br/>";
	
	$ca_name = $p_name . '-' . $post_id;

	try{
 			//create page
		    $page = $cascade->createDataDefinitionPage( $folder, $ca_name, $contentType);


			//Populate the page

			//attach correct scripts    
	 			$page->setRegionNoFormat( "PHP", "recent category stories", true )->                       
                       setRegionNoBlock( "PHP", "recent category stories", true)->
                       //set metadata     
                       getMetadata()->
                       setStartDate($date)->
                       setTeaser($teaser)->
                       setDisplayName($title)->
                       getHostAsset()->
                       getStructuredData()->
                       //DD with usable image
                       setText( "media;legacyImg", $thumbnail_url )->
                       //setText( "media;picAlt", $imageAlt )->
                       setText( "text", $content )->
                       //setText( "media;pod", $mp3 )->
                       setText( "guest-author", $authName )->
                       getHostAsset()->edit();
                       sleep(5);

		} 
		catch(Exception $e){

			  		echo $e;
			  	}
		catch(Error $er){
			  		echo $er;
			  	}

	}
}
echo "</pre>";






// $location = "news-2014-missing.txt";
// $missing = fopen($location, 'r') ;
// $miss = fread($missing, filesize($location));
// $missed = preg_split("/[\s,]+/", $miss);

// $t = '';

// foreach ($missed as $m) {


// 	$t = explode("-",$m);
// 	$q = count($t);
// 	$r = array_slice($t, 0, $q-1 );
// 	$str = '';
// 	$count = 0;
// 	$arrlen = count($r);



// 		foreach ($r as $l) {
				
// 			$str = $str.$l.'-';
// 		}

// 		$str = substr($str, 0, -1);
// 		file_put_contents("2015-missing.txt", $str.PHP_EOL, FILE_APPEND);
// 		var_dump($str);
// 		echo "<br>";

// 	}





?>