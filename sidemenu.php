<?php
if(!defined("_SIDEMENU_H_")){
define("_SIDEMENU_H_", 1);

include "securedb.php";
include "global.php";
include "file.php";

function BuildSideMenu(){
  /*returns category info as map such that each category
    has a 'name' field, a way to link to that category and 
    an array of subcategories
  */
  if($GLOBALS['groupid'] <= 1) $selectcond = array("trade = %d", 0);
  else $selectcond = "";
  $categories = ReadDB("categories", array("id", "name"), $selectcond, array("displayorder", "ASC"));
  if($categories["status"] == "Error") return $categories;
  $categories = $categories["value"];

  foreach($categories as $key => $category){
    if($GLOBALS['groupid'] <= 1) $subcatcond = array("catid = %d AND trade = 0", $category["id"]);
    else $subcatcond = array("catid = %d", $category["id"]);

    $subcategories = ReadDB("subcategories", array("id", "name"), $subcatcond, array("displayorder", "ASC"));
    if($subcategories["status"] == "Error") return $subcategories;
    $subcategories = $subcategories["value"];
    $categories[$key]["subcats"] = $subcategories;
  }
  return packReturn($categories, "Value");
}

function DisplaySideMenu($type= "", $id = "" ){
  $categories = BuildSideMenu();
  if($categories["status"] == "Error") return $categories;

  foreach($categories["value"] as $cat => $catinfo){
    $subcats = $catinfo["subcats"];
    $subcats_str = "";

    foreach($subcats as $subcat => $subcatinfo){
      $content = array();
      $content["subcatname"] = $subcatinfo["name"];
      $content["subcatlink"] = URL_PATH . "index.php?subcat=" . $subcatinfo["id"];
      if($type == "subcat" and $subcatinfo["id"] == $id) $content["highlight"] = "style='background-color: #fffff0;'";
      else $content["highlight"] = "";
      $ret = SendPage("sidemenusubcat.tem", $content, FALSE);
      if($ret["status"] == "Error") return $ret;
      $subcats_str = $subcats_str . $ret["value"];
    }

    $content = array();
    $content["catname"] = $catinfo["name"];
    $content["catlink"] = URL_PATH . "index.php?cat=" . $catinfo["id"];
    $content["subcats"] = $subcats_str;
    if($type == "cat" and $catinfo["id"] == $id) $content["highlight"] = "style='background-color: #fffff0;'";
    else $content["highlight"] = "";

    $ret = SendPage("sidemenucat.tem", $content, FALSE);
    if($ret["status"] == "Error") return $ret;
    $sidemenu_str = $sidemenu_str . $ret["value"];
  }

  return packReturn($sidemenu_str, "Value");
}

}

?>
