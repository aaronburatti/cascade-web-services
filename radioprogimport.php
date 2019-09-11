<?php

require_once( 'ws_library/php7-cascade-ws-ns-master/auth_soap_user.php' );
use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;
include_once("ws_auth.php"); 

u\DebugUtility::setTimeSpaceLimits(7200);



//load the author
$authorKeys = array();
$authorNames = array();

$authors = fopen("afterglowusers.csv", "r");

while(!feof($authors)){
$authorKeys[] = fgetcsv($authors)[0];
}

$authors = fopen("afterglowusers.csv", "r");

while(!feof($authors)){
$authorNames[] = fgetcsv($authors)[1];
}



//load the media XML
$mediaPath = "afterglowmedia.xml";
$media = new DomDocument;
$media->load($mediaPath);
$mediaX = new DomXPath($media);

//grab each media item
$mediaItems = $media->getElementsByTagName('item');
$picParentId = $media->getElementsByTagName('post_id');




//load the post xml
$path = "afterglowposts.xml";
$xml = new DomDocument;
$xml->load($path);
$xpath = new DomXPath($xml);

//grab each WP post
$items = $xml->getElementsByTagName('item');

//Cascade assets need for page assignment
$folder 				= $admin->getAsset(a\Folder::TYPE, '2162fe00814f4e100dca85cb119eb37d' );
$contentType			= $admin->getAsset(a\ContentType::TYPE, 'a2a6f12c814f4e105ec7d9f5c9d051c3');
$postFormat 			= $admin->getAsset(a\ScriptFormat::TYPE, 'f0cf41b8814f4e1062a68fcca083e4b7');
$indexBlock 			= $admin->getAsset(a\IndexBlock::TYPE, 'bc01fa60814f4e1060f6cc4f45deb7a3');
$metaTags 				= $admin->getAsset(a\ScriptFormat::TYPE, '47e8967b814f4e1065f4ac762084518b');


//loop through the posts
foreach ($items as $item) {
	
	//get the content of the data nodes for which there are 
	//accompanying places in Cascade	
	$dName				= $item->getElementsByTagName('post_name')[0]->textContent;
	$title 				= $item->getElementsByTagName('title')[0]->textContent;
	$teaser 			= strip_tags($item->getElementsByTagName('excerpt')[0]->textContent);
	$author 			= $item->getElementsByTagName('creator')[0]->textContent;
	$date 				= $item->getElementsByTagName('pubDate')[0]->textContent; 
	$tags				= $item->getElementsByTagName('category')[0]->textContent;
	$content			= $item->getElementsByTagName('content')[0]->textContent;
	//$postId 			= $item->getElementsByTagName('post_id')[0]->textContent;
	$metaValue 			= $item->getElementsByTagName('meta_value');
	$metaKey 			= $item->getElementsByTagName('meta_key');
	$category			= $item->getElementsByTagName('category');

	
	


	$castCat = "";
	foreach ($category as $cat) {
		
		if($cat->textContent == "shows"){
			$castCat = "Shows";
			
		} elseif($cat->textContent == "podcasts"){
			$castCat = "Podcasts";
		 } 
	}



	//format date for Cascade compatibility
	$date				= new DateTime($date);
	$date->setTimeZone(new DateTimeZone('UTC'));
	$date = $date->format('Y-m-d\TH:i:s.\0\0\0\Z');
 	
 	


 	/*
	* Manipulate the image info embedded in the post 
 	*/


	//remove the embedded image tag from post body
	 preg_match( "/<img.*? \/>/", $content, $imageString);
	 $content = str_replace($imageString[0], "", $content);

	
 	//isolate the img tag. 
	preg_match( "<img.*? \/>", $content, $imageString[0]);
	$image = $imageString[0];
	$image = $image[0];

	
	// // isolate the image URL
	preg_match("(src=\".*?\")", $image, $imageSrc);
	$i = str_replace("src=\"", "", $imageSrc[0]);
	$imageUrl = str_replace("\"", "", $i);
	$imageUrl = str_replace(".org/", ".org/wpimages/", $imageUrl);
	$imageUrl = str_replace("files/", "", $imageUrl);
	
	
	//isolate the alt tag
	preg_match("(alt=\".*?\")", $image, $imageAlt);
	$i = str_replace("alt=\"", "", $imageAlt[0]);
	$imageAlt = str_replace("\"", "", $i);

	

	//isolate the caption
	preg_match("/>.*?\(/", $image, $imageCap[0]);
	



	/*
	* Find the attachment image from seperate XML sheet and it's relevant data
	*/
	

	$postId = '';

	foreach ($metaKey as $meta) {
		
		if($meta->nodeValue == '_thumbnail_id'){
		 $postId = $meta->nextSibling->nextSibling->nodeValue;

		} 

	}

	


	$imageAlt = "";
	$imageUrl = "";
		
	  foreach ($picParentId as $parentPic) {

		 if($parentPic->nodeValue == $postId){
			
		 	//$title  = $mediaItem->getElementsByTagName('content')[0]->textContent;
		   $picItem = $parentPic->parentNode;
		   $imageUrl    = $picItem->getElementsByTagName('attachment_url')[0]->textContent;

		   $pattern = "/afterglow\/files/";
		   $replacement ="wpimages/afterglow";
		   $imageUrl = preg_replace($pattern, $replacement, $imageUrl);

		   $imageAlt    = $picItem->getElementsByTagName('excerpt')[0]->textContent; 

		  
 		}
	  }


	
	/*
	* Format the post body to eliminate short codes, WP specific markup, and html enitites
	*/

	//specific replacement for "curved" apostrophes and quotations
	$pattern = "[\’]";
	$replacement ="&#8217;";
	$content = preg_replace($pattern, $replacement, $content);

	$pattern = "[\“]";
	$replacement ="&#8220;";
	$content = preg_replace($pattern, $replacement, $content);

	$pattern = "[\”]";
	$replacement ="&#8221;";
	$content = preg_replace($pattern, $replacement, $content);

	$pattern = "[\—]";
	$replacement ="&#8212;";
	$content = preg_replace($pattern, $replacement, $content);	


	$pattern = "(\[caption.*?caption])";
	$replacement ="";
	$content = preg_replace($pattern, $replacement, $content);


	/*
	* removal of the image tag
	*/

	// $pattern = "(<img.*?\/>)";
	// $replacement ="";
	// $content = preg_replace($pattern, $replacement, $content);
	
	$content = str_replace("[cf]embed[/cf]", "", $content);
	$content = str_replace("&nbsp;", "", $content);	

	

	/*
	* Find the real media link and replace with new url to converted mp3
	*/

	$mp3 = "";
	foreach($metaValue as $value){
		if(strpos($value->nodeValue, '.mp3') !== false){
			$mp3 = preg_split('/(mp3)/', $value->nodeValue)[0] . 'mp3';
		} elseif(strpos($value->nodeValue, '.ram') !== false){
			$mp3Slug = preg_match("/afterglow\/.*?.ram/", $value->nodeValue, $mp3);
			$mp3Slug = preg_replace("/afterglow\//", "", $mp3[0]);
			$mp3Slug = preg_replace("/.ram/", "", $mp3Slug);
			$mp3 = "https://indianapublicmedia.org/podcasts/audio/afterglow/old/".$mp3Slug.".mp3";
		}
	}

	
	

	/*
	* Put tags in an array of objects 
	*/

 		$tags = array();
 		foreach($category as $cat){	
 			if($cat->getAttribute("domain") == "post_tag"){
 			$obj = new stdClass;
 			$obj->name = $cat->getAttribute("nicename");
 			$tags[] = $obj;
 			}
 		}


	/*
	* Get the Author's Full name from the author csv
	*/

	$authNameIndex = array_search($author, $authorKeys);
	$authName = $authorNames[$authNameIndex];
	
	if($authName == 'Adam Schweigert'|| $authName == 'Eoban Binder' || $authName == 'G. Pablo Vanwoerkom' || $authName == 'display_name'){
		$authName = "WFIU Staff";
	}
	
	

	/*
	* Format the Post Body with HTML so that it looks good inside of Cascade
	*/

	//prep p tags to put around everything
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
			
			
			if(strpos($match, "httpv") !== false){
				

				$uri = explode("=", $match)[1];
				$src = "https://www.youtube.com/embed/".$uri;
				

				$src = str_replace("<p>", '', $src);
				$src = str_replace("</p>", '', $src);
			
				$iframe = "<iframe width='560' height='315' src='{$src}' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen='true'></iframe>";

				$content = str_replace($match, $iframe, $content);	

			} 


			if(strpos($match, "httpvh") !== false){
				
				$uri = explode("=", $match)[1];
				$src = "https://www.youtube.com/embed/".$uri;
				
				$src = str_replace("<p>", '', $src);
				$src = str_replace("</p>", '', $src);
			
				$iframe = "<iframe width='560' height='315' src='{$src}' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen='true'></iframe>";

				$content = str_replace($match, $iframe, $content);
				
			} 

			if(strpos($match, "http://www.youtube") !== false){
				

				$uri = explode("=", $match)[1];
				$src = "https://www.youtube.com/embed/".$uri;
				

				$src = str_replace("<p>", '', $src);
				$src = str_replace("</p>", '', $src);
			
				$iframe = "<iframe width='560' height='315' src='{$src}' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen='true'></iframe>";

				$content = str_replace($match, $iframe, $content);	

			} 
			
			if(strpos($match, "https://www.youtube") !== false){
				

				$uri = explode("=", $match)[1];
				$src = "https://www.youtube.com/embed/".$uri;
				

				$src = str_replace("<p>", '', $src);
				$src = str_replace("</p>", '', $src);
			
				$iframe = "<iframe width='560' height='315' src='{$src}' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen='true'></iframe>";

				$content = str_replace($match, $iframe, $content);	

			} 	
			
			
		}

		if(empty($teaser)){
			$teaser = $title;
		}
	

	

	  try{
	 			//create page
			    $page = $cascade->createDataDefinitionPage( $folder, $dName, $contentType);


			//Populate the page

			//attach correct scripts    
	 		$page->setRegionFormat( "PHP", "left-8", $postFormat )->
                       setRegionFormat( "PHP", "meta tags", $metaTags)->
                       setRegionBlock( "PHP", "meta tags", $indexBlock)->
                       setRegionBlock( "PHP", "left-8", $indexBlock)->
                       //set metadata     
                       getMetadata()->
                       setStartDate($date)->
                       setTeaser($teaser)->
                       setDisplayName($title)->
                       getHostAsset()->
                       getStructuredData()->
                       //DD with usable image
                       setText( "media;legacyImg", $imageUrl )->
                       setText( "media;picAlt", $imageAlt )->
                       setText( "text", $content )->
                       setText( "media;pod", $mp3 )->
                       setText( "guest-author", $authName )->
                       getHostAsset()->edit();


		} 
		catch(Exception $e){
			  		echo $e;
			  	}
		catch(Error $er){
			  		echo $er;
			  	}
	 
} //end items loop



?>