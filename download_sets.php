<?php

/*
	PHP SCRIPT TO BACK UP FLICKR PHOTOSETS

	Arguments:
	
	APIKEY, SECRET, FLICKER_USER
	
*/

require_once("phpFlickr.php");

$key = $argv[1];
$secret = $argv[2];
$user = $argv[3];



$f = new phpFlickr( $key, $secret );




// set the script to never time out

set_time_limit(0);

ini_set('error_reporting', E_ALL);

if($f) print "API ok ...\n\n";


$sets = $f->photosets_getList( $user )['photoset'];



 
foreach ( $sets as $set )
{
	

	$set_name = $set['title']['_content'];
	
	$folder = makeFilename( $set_name );
	
	// get the photos from a set
	
	$photos = $f->photosets_getPhotos( $set['id'] );
	
	
	if($photos) print " got $folder photos ..\n\n\n";
	
	// Loop through the photos and output the html
	process_set( $photos['photoset'], $folder, $f ) ;

}




function process_set( $photos, $folder, $f )
{	
	foreach ($photos['photo'] as $photo) {
	
		 
		
		// get image URLs
		
		$photoSizes = $f->photos_getSizes($photo['id']);
		
		$originalSizeURL = $photoSizes[count($photoSizes) - 1]['source'];
		
		$thumbURL = $f->buildPhotoURL($photo, "square");
		
		 
		
		// display the thumbnail
		
		print $photo['title'] . " [$originalSizeURL]" ."\n";
		
		 
		
		// download the original version
		
		grabPic( $originalSizeURL, $folder, $photo['title']."_".$photo['id']);
		
	
	}
}


function file_get( $filename, $url )
{
	$ch = curl_init ($url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$rawdata=curl_exec($ch);
	curl_close ($ch);
	
	$fp = fopen( $filename,'w');
	fwrite($fp, $rawdata);
	fclose($fp);
}

 

function grabPic( $URL, $folder, $title)

{
	
	if ( !file_exists( "./" . $folder )) {
    	mkdir(  "./" . $folder, 0777, true );
	}


	$filename = $folder . "/" . makeFilename($title).".jpg";
	
	$source = $URL;
	
	$newimage = file_get($filename, $source);

}

 

function makeFilename($string)

{


	return preg_replace("/[^A-Za-z0-9]/", "_", $string);

}

 



?>
