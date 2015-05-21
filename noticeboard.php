<?php
if(!defined("_NOTICE_H_")){
define("_NOTICE_H_", 1);

include "securedb.php";
include "global.php";
include "file.php";


function DisplayNotices(){
  if($GLOBALS['groupid'] <= 1) $notice_cond = array("trade = 0");
  else $notice_cond = "";

  $notices = ReadDB("notices", array("id", "link", "trade"), $notice_cond);
  if($notices["status"] == "Error") return $notices;
  $notices = $notices["value"];
  $notice_string = "<table cellpadding=0 cellspacing=0>";
  foreach($notices as $key => $values){
    $id = $values["id"];
    $notice_picture = "images/notices/" . $id . "/" . $id . ".jpg";
    if(!file_exists(BASE_PATH . $notice_picture)) continue;
    $notice_string = $notice_string . "<tr><td><div class='notice'><a href='". $values["link"] ."'><img src='". URL_PATH . $notice_picture."'/></a></div></tr></td>";
  }
  $notice_string = $notice_string . "</table>";
  return PackReturn($notice_string, "Value");
}

}
?>
