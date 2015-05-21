<?php
if(!defined("_BIGPIC_H_")){
define("_BIGPIC_H_", 1);

include "global.php";
include "file.php";
include "securedb.php";

$product_id = GetFromForm("product_id");

if($product_id["status"] == "Error") echo $product_id["comment"];
else $product_id = $product_id["value"];

$content = array();
$content["image"] = URL_PATH . "images/products/" . $product_id . "/" . $product_id . "_big.jpg";

$ret = SendPage("bigimagepage.tem", $content);
if($ret["status"] == "Error") echo $ret["comment"];

}
?>
