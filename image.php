<?php
include "global.php";

function MakeFilePath($imagefile){
  if(!(strpos($imagefile, ".") === False)) return False;
  //if(strpos($imagefile, "images/") == 0) $imagefile = BASE_PATH . $imagefile .".jpg";
  $imagefile = BASE_PATH . "images/". $imagefile .".jpg";
  if(is_file($imagefile)) return $imagefile;
  return False;
}

$imagetime = 0;
$imagesize = 0;
$imagefile = GetFromForm("image");
$cache = GetFromForm("cache");
if($imagefile["status"] == "Value") $imagefile = MakeFilePath($imagefile["value"]);
else $imagefile = False;

if($imagefile == False) $imagefile = BASE_PATH . "images/imgerr.jpg";

$imagetime = filemtime($imagefile);
$imagesize = filesize($imagefile);

header('Last-Modified: '.gmdate('D, d M Y H:i:s', $imagetime).' GMT', true, 200);
header('Content-Length: '. $imagesize);
header('Content-Type: image/jpeg');
if($cache["status"] == "Value"){
  header("cache-Control: no-store, no-cache, must-revalidate");
  header("cache-Control: post-check=0, pre-check=0", false);
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header("Pragma: no-cache"); 
}

readfile($imagefile);

?>
