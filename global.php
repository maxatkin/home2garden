<?php

if(!defined("_GLOBAL_H_")){
define("_GLOBAL_H_", 1);
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Pragma: no-cache');

define("BASE_PATH",""); //"/var/www/h2g/");
//define("ORDER_EMAIL","max.atkin@chch.ox.ac.uk"); 
define("ORDER_EMAIL","mick.mckenna@arcavitsystems.com"); 
//define("URL_PATH", "http://home2garden.co.uk"); //"http://81.111.147.216/h2g/");
define("URL_PATH", "http://domain2118317.sites.streamlinedns.co.uk/"); //"http://81.111.147.216/h2g/");
//error_reporting(0);
//chdir(BASE_PATH);

$GLOBALS['userid'] = 1;
$GLOBALS['groupid'] = 1;


function packReturn($ret_value, $ret_status, $desc = "NULL"){
  $ret_arr = array("value" => $ret_value, "status" => $ret_status, "comment" => $desc);
  return $ret_arr;
}

function sendRawStr($rawstr){
  echo $rawstr;
}

function StrExplode($sep, $str){
  $ret = array();
  $split = explode($sep, $str);
  foreach($split as $substr){
    if(strlen($substr) > 0) array_push($ret, $substr);
  }
  return $ret;
}

function GetCookie($formvar){
  if(array_key_exists($formvar, $_COOKIE) == TRUE){
    return packReturn($_COOKIE[$formvar], "Value");
  }
  return packReturn(0, "No value", "No cookie called $formvar exists");
}

function GetFromForm($formvar){
  if(array_key_exists($formvar, $_POST) == TRUE)
    return packReturn($_POST[$formvar], "Value");
  if(array_key_exists($formvar, $_GET) == TRUE)
    return packReturn($_GET[$formvar], "Value");
  return packReturn(0, "No value", "No variable called $formvar exists");
}

function GetNumericIds(){
  $ret = Array();
  foreach($_POST as $formvar => $value){
    if(is_numeric($formvar)) $ret[$formvar] = $value;
  }
  foreach($_GET as $formvar => $value){
    if(is_numeric($formvar)) $ret[$formvar] = $value;
  }
  return $ret;
}

function MatchFromForm($pattern){
  $ret = Array();
  foreach($_POST as $formvar => $value){
    if(strpos($formvar, $pattern) === 0){
      $ret[substr($formvar, strlen($pattern))] = $value;
    }
  }
  foreach($_GET as $formvar => $value){
    if(strpos($formvar, $pattern) === 0){
      $ret[substr($formvar, strlen($pattern))] = $value;
    }
  }
  return $ret;  
}

function FixPath($path){
  if($path[strlen($path) - 1] != "/") $path = $path . "/";
  return $path;
}

function ReadInfo($path){
  if(is_dir(BASE_PATH . $path) == FALSE)
    return packReturn(0, "Error", "No intro information specified for section $section");

  $path = FixPath($path);
  if(is_file(BASE_PATH . $path . "name") == FALSE)
    return packReturn(0, "Error", "No name specified for section $section");
  if(is_file(BASE_PATH . $path . "description") == FALSE)
    return packReturn(0, "Error", "No description specified for section $section");
  if(is_file(BASE_PATH . $path . "image.jpg") == FALSE)
    return packReturn(0, "Error", "No intro image specified for section $section");

  $name = FileToStr(BASE_PATH . $path . "name");
  if($name["status"] == "Error") return $name;
  $description = FileToStr(BASE_PATH . $path . "description");
  if($description["status"] == "Error") return $description;

  $section_info = array();  
  $section_info["name"] = $name["value"];
  $section_info["description"] = $description["value"];
  $section_info["image"] = $path . "image.jpg";

  //Get extra info if present
  //put each price in an array
  if(is_file(BASE_PATH . $path . "retailprices") == TRUE){
    $prices = FileToArray(BASE_PATH . $path . "retailprices");
    if($prices["status"] == "Value"){
      $prices = $prices["value"];
      $prices = explode(":", $prices);
      $section_info["price"] = $prices;
    }
  }
  return packReturn($section_info, "Value");
}

function GetSectionInfo($path, $intropath = "intro"){
  $section_list = array();
  $ret = list_of_type(BASE_PATH . $path, "dir");
  if($ret["status"] == "Error") return $ret;
  $dirs = $ret["value"];

  $path = FixPath($path);
  foreach($dirs as $section){
    if($section == "intro") continue;
    $ret = ReadInfo($path . $section . "/" . $intropath);
    if($ret["status"] == "Error") return $ret;
    $section_list[$section] = $ret["value"];
  }
  return packReturn($section_list, "Value");
}

}
?>
