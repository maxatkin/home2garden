<?php

if(!defined("_FILE_H_")){
define("_FILE_H_", 1);

function GetFilePointerReader($filename){
  $fp = fopen($filename, "r");
  if($fp == FALSE) return packReturn(fp, "Error", "Couldn't open the file");
  return packReturn($fp, "Value");
}

function GetFilePointerWriter($filename){
  $fp = fopen($filename, "w");
  if($fp == FALSE) return packReturn(fp, "Error", "Couldn't open the file");
  return packReturn($fp, "Value");
}

function ParseForContent($template_str, $content = FALSE){
  if($content == FALSE) return packReturn($template_str, "Value");
  foreach ($content as $key => $value){
    $key_with_marker = "[" . $key . "]";
    $template_str = str_replace($key_with_marker, $value, $template_str);
  }
  return packReturn($template_str, "Value");
}

function SendPage($template_file, $content = FALSE, $send = TRUE){
  $file_return = GetFilePointerReader(BASE_PATH . "templates/" . $template_file);
  if($file_return["status"] == "Error") return $file_return;

  $fp = $file_return["value"];
  if($content == FALSE) $content = array("URLPATH" => URL_PATH);
  else $content["URLPATH"] = URL_PATH;

  while (feof($fp) != TRUE){
    $newline = ParseForContent(fgets($fp), $content);
    $content_str = $content_str . $newline["value"];
  }
  fclose($fp);
  if($send){
    echo $content_str;
    return packReturn(0, "No Value");
  }
  return packReturn($content_str, "Value");
}

function FileToStr($filename){
  $ret = file_get_contents($filename);
  if($ret == FALSE) return packReturn(0, "Error", "Unable to read file $filename");
  return packReturn($ret, "Value");
}

function FileToArray($filename){
  $ret = file($filename);
  if($ret == FALSE) return packReturn(0, "Error", "Unable to read file $filename");
  return packReturn($ret, "Value");
}

function list_directory($dir){
  $dir_listing = array();
  $dh = opendir($dir);
  if($dh == FALSE) return packReturn(0, "Error", "$dir is not a directory");
  while(($file = readdir($dh)) != FALSE){
    if($file != "." and $file != "..") $dir_listing[$file] = filetype($dir . "/" . $file);
  }
  closedir($dh);
  return packReturn($dir_listing, "Value");
}

function list_of_type($dir, $type){
  $i = 0;
  $dir_listing = array();
  $ret = list_directory($dir);
  if($ret["status"] == "Error") return $ret;
  else{
    $product_list = $ret["value"];
    foreach($product_list as $key => $value){
      if($value == $type) $dir_listing[$i] = $key;
      $i++;
    }
  }
  return packReturn($dir_listing, "Value");
}

function DisplayFiles(){
  if($GLOBALS['groupid'] <= 1) return PackReturn(0, "Error", "You have not logged in as a trade customer");

  $file_desc = ReadDB("tradefiles", array("id", "description", "displayorder"), "", array("displayorder DESC", "0"));

  if($file_desc["status"] == "Error") return $file_desc;
  $file_desc = $file_desc["value"];

  $file_str = "<ul>";
  foreach($file_desc as $key => $values){
    $id = $values["id"];
    $desc = $values["description"];
    $file_dir = "files/" . $id . "/";
    $files = list_of_type(BASE_PATH . $file_dir, "file");
    if($files["status"] == "Error") continue;
    $filename = $file_dir . $files["value"][0];
    $file_str = $file_str . "<li><a href='" . URL_PATH . $filename . "'>". $desc ."</a>";
  }
  $file_str = $file_str . "</ul>";
  return PackReturn($file_str, "Value");
}


}?>
