<?php
//Grab the get variable and explode at the space
$bucket	= $_GET["name"];
$store = explode("-", $bucket);
//include the S3 class 
if (!class_exists('S3'))require_once('s3/S3.php');

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', $store[0]);
if (!defined('awsSecretKey')) define('awsSecretKey', $store[1]);

//instantiate the class
$s3 = new S3(awsAccessKey, awsSecretKey);
 
// Get the contents of our bucket
$bucket_contents = $s3->getBucket($store[2],$store[3]);

header("Content-type: text/xml"); 
$xml_output = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$xml_output .= '<playlist version="1" xmlns="http://xspf.org/ns/0/">\n'; 
$xml_output .= "<trackList>\n"; 
foreach ($bucket_contents as $file){
	$fname = $file['name'];
	$furl = "http://$store[2].s3.amazonaws.com/".urlencode($fname);
	if(preg_match("/\.mp3$/i", $furl))
{ 
	
	 if (isset($outputted[$furl])) { 
        continue;
    }
	 
    $xml_output .= "\t<track>\n"; 
    $xml_output .= "\t\t<location>" . $furl . "</location>\n"; 

    $xml_output .= "\t\t<creator>" . $fname . "</creator>\n";
	$xml_output .= "\t\t<album>" . $fname . "</album>\n"; 
	$xml_output .= "\t\t<title>" .  basename($fname) . "</title>\n";
	$xml_output .= "\t\t<annotation>I love this song</annotation>\n"; 
	$xml_output .= "\t\t<duration>32000</duration>\n"; 
	$xml_output .= "\t\t<image>covers/smetana.jpg</image>\n";
	$xml_output .= "\t\t<info></info>\n";
	$xml_output .= "\t\t<link>" . $furl . "</link>\n";
    $xml_output .= "\t</track>\n"; 
	$outputted[$furl] = true;
}  
} 
$xml_output .= "</trackList>"; 
echo $xml_output; 
?>