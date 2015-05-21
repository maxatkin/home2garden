<?php
if(!defined("_SEARCH_H_")){
define("_SEARCH_H_", 1);

include "global.php";
include "file.php";
include "securedb.php";

function SearchSections($table, $link, $search, $condition = "", $condition_id = 0){
  $content = array();
  if($GLOBALS['groupid'] <= 1){
    $selectcond = "products.trade = 0";
    if($condition == "") $condition = $selectcond;
    else $condition = $condition . " AND " .$selectcond;
  }
  if(strlen($search) == 0) PackReturn(0, "Error", "No search words given");
  $keywords = split(" ", $search);
  $searchcond = "";
  $joiner = "";
  $snd_joiner = "";

  $tables_to_search = array("products", "subcategories", "categories");

  foreach($tables_to_search as $tablename){  
    $searchcond = $searchcond . $snd_joiner . "(";
    foreach($keywords as $word){
      $searchcond = $searchcond . $joiner . " (" . $tablename . ".name LIKE '%%" . $word .  "%%')";
      $joiner = "AND";
      if($word[strlen($word) - 1] == "s"){
        $newword = substr($word, 0 , strlen($word) - 1);
        $searchcond = $searchcond . "OR (" . $tablename . ".name LIKE '%%" . $newword .  "%%')";
      }
    }
    $searchcond = $searchcond . ")";
    $snd_joiner = "OR";
    $joiner = "";
  }

  $searchcond = "(" . $searchcond . ") AND products.subcatid = subcategories.id AND subcategories.catid = categories.id";
  if($condition == "") $condition = $searchcond;
  else $condition = $condition . " AND (" .$searchcond . ")";

  $ret = ReadDB("products,subcategories,categories", array("products.id", "products.name", "products.description"),
                 array($condition, (integer)$condition_id), array("products.displayorder", "ASC"));
  if($ret["status"] == "Error") return $ret;

  $category_icon = "<table border = '0'><tr>";
  $category_list = $ret["value"];
  $column_count = 0;

  foreach($category_list as $category => $category_info){
    $id = sprintf("%d", $category_info["id"]);
    $content["name"] = $category_info["name"];
    $content["description"] = $category_info["description"];
    $imagepath = "images/products/" . $id . "/" . $id . ".jpg";
    $imagelink = "index.php?" . $link . $id;
    $content["link"] = URL_PATH . $imagelink;
    if(file_exists(BASE_PATH . $imagepath)) $content["image"] = "<a href='". URL_PATH .$imagelink . "'><img src='". URL_PATH. $imagepath ."'/></a>";
    else $content["image"] = "";
    $ret = SendPage("category.tem", $content, FALSE);
    if($ret["status"] == "Error") return $ret;
    $category_icon = $category_icon . "<td style='text-align: center;'>" . $ret["value"] . "</td>";
    $column_count = $column_count + 1;
    if($column_count == 3){
      $category_icon = $category_icon . "</tr></table><table border = '0'><tr>";
      $column_count = 0;
    }
  }
  $category_icon = $category_icon . "</tr></table>";
  return packReturn($category_icon, "Value");
}


}?>
