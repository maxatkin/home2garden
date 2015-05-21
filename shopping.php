<?php
include "global.php";
include "file.php";
include "securedb.php";
include "sidemenu.php";
include "login.php";
include "noticeboard.php";

function UnpackBasketCookie($basket){
  $orders = array();
  $products = StrExplode(":", $basket);
  foreach($products as $product){
    $orderinfo = StrExplode("=", $product);
    $order["id"] = $orderinfo[0];
    $types =  StrExplode(";", $orderinfo[1]);
    $typeorder = array();
    foreach($types as $type){
      $typeinfo = StrExplode(",", $type);
      $typeid = $typeinfo[0];
      $quantity = $typeinfo[1];
      $typeorder[$typeid] = $quantity;
    }
    $order["types"] = $typeorder;
    array_push($orders, $order);
  }
  return $orders;
}

function AddToBasket($order, $basket){
  $newproduct = TRUE;
  //Check if items are already present in basket, in which case add to that quantity
  foreach($order["types"] as $type => $quantity) $order["types"][$type] = (integer)$quantity;
  foreach($basket as $key => $product){
    if($product["id"] == $order["id"]){
      foreach($order["types"] as $type => $quantity){
        $product["types"][$type] = $product["types"][$type] + $quantity;
      }
      $newproduct = FALSE;
      $basket[$key] = $product;
      break;
    }
  }
  //If items are not present just push onto array
  if($newproduct)array_push($basket, $order);

  foreach($basket as $key => $product){
    foreach($product["types"] as $type => $quantity){
      if($quantity <= 0) unset($basket[$key]["types"][$type]);
    }
    $total_quantity = 0;
    foreach($product["types"] as $type => $quantity){    
      $total_quantity = $total_quantity + $quantity;
    }
    if($total_quantity <= 0) unset($basket[$key]);
  }
  return $basket;
}

function BuildBasketCookie($basket){
  $cookiebasket = "";
  foreach($basket as $product){
    $add_to_basket = "";
    foreach($product["types"] as $typeid => $quantity){
      $add_to_basket = $add_to_basket . $typeid . "," . $quantity . ";";
    }
    $add_to_basket = $product["id"] . "=" . $add_to_basket . ":";
    $cookiebasket = $cookiebasket . $add_to_basket;
  }
  return packReturn($cookiebasket, "Value");
}


function BuildPaypalHiddenFields($basket){
  $list_number = 0;
  $rowtype = FALSE;
  $paypalfields = "";
  $total_price = 0.00;
  $shipping = 0.00;

  foreach($basket as $product){
    //Get product info from database using product id from cookie
    $productinfo = ReadDB("products", array("id", "name"), array("id = %d", (integer)$product["id"]));
    if($productinfo["status"] == "Value") $productinfo = $productinfo["value"][0];

    //Calculate total cost minus shipping
    foreach($product["types"] as $type => $quantity){
      if($GLOBALS['groupid'] > 1) $pricetable = "tradeprices";
      else $pricetable = "prices";
      $order = ReadDB($pricetable, array("id", "name", "description", "price"), array("id = %d", (integer)$type));
      if($order["status"] == "Value") $order = $order["value"][0];
	  //discount calculation
	  if(date("Y") == "2009") $order["price"] = round($order["price"]-($order["price"] * .20), 2);
      $order["price"] = sprintf("%01.2f", $order["price"]);
      $total_price = $total_price + $quantity*$order["price"];      
    }
  }

  //if($total_price <= 50.00) $shipping = 4.99;
 // else if($total_price <= 50.00 and $total_price > 100.00) $shipping = 6.99;
  //if($total_price <= 50.00) $shipping = 4.95;
 // else $shipping = 0.00;



  foreach($basket as $product){
    //Get product info from database using product id from cookie
    $productinfo = ReadDB("products", array("id", "name"), array("id = %d", (integer)$product["id"]));
    if($productinfo["status"] == "Value") $productinfo = $productinfo["value"][0];

    //Then displays how many of each type of this product (i.e. small, medium etc..) have been ordered.
    foreach($product["types"] as $type => $quantity){
      $list_number = $list_number + 1;
      if($GLOBALS['groupid'] > 1) $pricetable = "tradeprices";
      else $pricetable = "prices";
      $order = ReadDB($pricetable, array("id", "name", "description", "price"), array("id = %d", (integer)$type));
      if($order["status"] == "Value") $order = $order["value"][0];
	   //discount calculation
	 //if(date("Y") == "2009") {$order["disc_text"] = '<span style="font-size:small;color:red;">&nbsp;Discounted</span>'; 
	 //$order["price"] = round($order["price"]-($order["price"] * .20), 2);
	 //}
	 else $order["disc_text"] = '<span style="font-size:small;color:white;">&nbsp;Discounted</span>';
      $order["price"] = sprintf("%01.2f", $order["price"]);
      $order["type"] = $order["name"];
      if(strlen($order["description"]) > 1) $order["description"] = "(".$order["description"].")";
      $order["name"] = $productinfo["name"] . ": " . $order["type"] . $order["description"];
      $order["quantity"] = $quantity;
      $order["number"] = $list_number;
      $ret = SendPage("paypalfields.tem", $order, FALSE);
      if($ret["status"] == "Error") return $ret;
      else $paypalfields = $paypalfields . $ret["value"];
    }
  }
  if($list_number == 0) return packReturn("", "Value");
  $paypalinfo = array("paypalfields" => $paypalfields, "shipping" => $shipping);
  return SendPage("paypalform.tem", $paypalinfo, FALSE);
}

function BuildBasketsContent($basket, $item_template){
  $total = 0;
  $rowtype = FALSE;
  $basketpage = "";
  

  foreach($basket as $product){
    //Get product info from database using product id from cookie
    $productinfo = ReadDB("products", array("id", "name"), array("id = %d", (integer)$product["id"]));
    if($productinfo["status"] == "Value") $productinfo = $productinfo["value"][0];

    //Then displays how many of each type of this product (i.e. small, medium etc..) have been ordered.
    foreach($product["types"] as $type => $quantity){
      if($GLOBALS['groupid'] > 1) $pricetable = "tradeprices";
      else $pricetable = "prices";
      $order = ReadDB($pricetable, array("id", "name", "description", "price"), array("id = %d", (integer)$type));
      if($order["status"] == "Value") $order = $order["value"][0];
	  //discount calculation
	 //if(date("Y") == "2009") {$order["disc_text"] = '<span style="font-size:small;color:red;">&nbsp;Discounted</span>'; 
	 //$order["price"] = round($order["price"]-($order["price"] * .20), 2);
	 //}
	 else $order["disc_text"] = '<span style="font-size:small;color:white;">&nbsp;Discounted</span>';
      $order["price"] = sprintf("%01.2f", $order["price"]);
      $order["type"] = $order["name"];
      if(strlen($order["description"]) > 1) $order["description"] = "(".$order["description"].")";
      $order["name"] = $productinfo["name"];
      $order["quantity"] = $quantity;
      $order["delete"] = URL_PATH . "shopping.php?Order=1&product=" . $productinfo["id"] . "&" . $type . "=-1";
      $total = $total + $quantity * $order["price"];
      if($rowtype == FALSE) $order["rowtype"] = "row1";
      else $order["rowtype"] = "row2";
      $rowtype = !$rowtype;

      $ret = SendPage($item_template, $order, FALSE);
      if($ret["status"] == "Error") return $ret;
      else $basketpage = $basketpage . $ret["value"];
    }
  }
  $total = sprintf("%01.2f", $total);
  $ret = array("items" => $basketpage, "total" => $total);
  return packReturn($ret, "Value");
}

$addorder   = GetFromForm("Order");
$address    = GetFromForm("address");
$confirm    = GetFromForm("confirm");
$finish     = GetFromForm("finish");
$product_id = GetFromForm("product");
$paypalfinish = GetFromForm("paypalfinish");
$paypalcancel = GetFromForm("paypalcancel");
$basket     = GetCookie("basket");
$typeids    = GetNumericIds();

if($basket["status"] == "Value") $basket = UnpackBasketCookie($basket["value"]);
else $basket = array();

if($addorder["status"] == "Value" and $product_id["status"] == "Value"){
  $product_id = $product_id["value"];
  $new_order = array("id" => $product_id);
  $new_order["types"] =  $typeids;
  $basket = AddToBasket($new_order, $basket);
  $savebasket = BuildBasketCookie($basket);
  $savebasket = $savebasket["value"];
  if(setcookie("basket", $savebasket) == FALSE) echo "You must enable cookies in order to use the shopping basket";
}

if($address["status"] == "Value"){
  $ret = SendPage("addressform.tem", 
  array("errorbox" => "", "deffirstname" => "", "deflastname" => "", "defproperty" => "", "defstreet" => "", "deftown" => "",
       "defprovince" => "", "defpostcode" => "", "defcountry" => "", "deftelephone" => "","defemail" => ""), FALSE);
  if($ret["status"] == "Value"){
    $content["content"] = $ret["value"];
    $content["textbox"] = "";
  }
}

else if($confirm["status"] == "Value"){
  $basketcontent = BuildBasketsContent($basket, "showitems.tem");
  if($basketcontent["status"] == "Value") $basketcontent = $basketcontent["value"];

  $confirm_content["items"] = $basketcontent["items"];
  $confirm_content["total"] = $basketcontent["total"];

  $firstname     = GetFromForm("firstname");
  $lastname      = GetFromForm("lastname");
  $property      = GetFromForm("property");
  $street        = GetFromForm("street");
  $town          = GetFromForm("town");
  $province      = GetFromForm("province");
  $postcode      = GetFromForm("postcode");
  $country       = GetFromForm("country");
  $telephone     = GetFromForm("telephone");
  $email         = GetFromForm("email");

  $confirm_content["firstname"]   = $firstname["value"];
  $confirm_content["lastname"]    = $lastname["value"];
  $confirm_content["property"]    = $property["value"];
  $confirm_content["street"]      = $street["value"];
  $confirm_content["town"]        = $town["value"];
  $confirm_content["province"]    = $province["value"];
  $confirm_content["postcode"]    = $postcode["value"];
  $confirm_content["country"]     = $country["value"];
  $confirm_content["telephone"]   = $telephone["value"];
  $confirm_content["email"]       = $email["value"];

  $gotoAddressPage = FALSE;
  if(strlen($firstname["value"]) < 1) $gotoAddressPage = TRUE;
  if(strlen($street["value"]) < 1) $gotoAddressPage = TRUE;
  if(strlen($town["value"]) < 1) $gotoAddressPage = TRUE;
  if(strlen($country["value"]) < 1) $gotoAddressPage = TRUE;
  if(strlen($telephone["value"]) < 1) $gotoAddressPage = TRUE;
  if(strlen($property["value"]) < 1) $gotoAddressPage = TRUE;

  if($gotoAddressPage){
    $ret = SendPage("addressform.tem",
    array("errorbox" => "Not all required fields were completed", "deffirstname" => $confirm_content["firstname"],
          "deflastname" => $confirm_content["lastname"], "defproperty" => $confirm_content["property"],
          "defstreet" => $confirm_content["street"], "deftown" => $confirm_content["town"],
          "defprovince" => $confirm_content["province"], "defpostcode" => $confirm_content["postcode"],
          "defcountry" => $confirm_content["country"], "deftelephone" => $confirm_content["telephone"],"defemail" => $confirm_content["email"]), FALSE);
    if($ret["status"] == "Value"){
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
  else{
    $ret = SendPage("confirm.tem", $confirm_content, FALSE);
    if($ret["status"] == "Value"){
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
}

else if($finish["status"] == "Value"){  
  $firstname     = GetFromForm("firstname");
  $lastname      = GetFromForm("lastname");
  $property      = GetFromForm("property");
  $street        = GetFromForm("street");
  $town          = GetFromForm("town");
  $province      = GetFromForm("province");
  $postcode      = GetFromForm("postcode");
  $country       = GetFromForm("country");
  $telephone     = GetFromForm("telephone");
  $email         = GetFromForm("email");

  $firstname   = $firstname["value"];
  $lastname    = $lastname["value"];
  $property    = $property["value"];
  $street      = $street["value"];
  $town        = $town["value"];
  $province    = $province["value"];
  $postcode    = $postcode["value"];
  $country     = $country["value"];
  $telephone   = $telephone["value"];
  $email       = $email["value"];
  if(strlen($email["value"]) < 1) $email = "No Email given";

  $message = "An order has been placed by: \n\n" 
             . "Name: " .  $firstname ." ". $lastname ."\n\n"
             . "Addres: " . "\n" . $property ."\n". 
               $street ."\n". $town ."\n". $province ."\n". $postcode ."\n". $country."\n\n"
             . "Telephone: " . $telephone . "\n\n"
             . "Email: " . $email . "\n\nFor the following items:\n\n";

  foreach($basket as $product){
    //Get product info from database using product id from cookie
    $productinfo = ReadDB("products", array("id", "name"), array("id = %d", (integer)$product["id"]));
    if($productinfo["status"] == "Value") $productinfo = $productinfo["value"][0];

    foreach($product["types"] as $type => $quantity){
      if($GLOBALS['groupid'] > 1) $pricetable = "tradeprices";
      else $pricetable = "prices";
      $order = ReadDB($pricetable, array("id", "name", "description", "price"), array("id = %d", (integer)$type));
      if($order["status"] == "Value") $order = $order["value"][0];
	   //discount calculation
	// if(date("Y") == "2009") {$order["disc_text"] = '<span style="font-size:small;color:red;">&nbsp;Discounted</span>'; 
	// $order["price"] = round($order["price"]-($order["price"] * .20), 2);
	// }
	 else $order["disc_text"] = '<span style="font-size:small;color:white;">&nbsp;Discounted</span>';
      $order["price"] = sprintf("%01.2f", $order["price"]);
      $order["type"] = $order["name"];
      if(strlen($order["description"]) > 1) $order["description"] = "(".$order["description"].")";
      $order["name"] = $productinfo["name"];
      $order["quantity"] = $quantity;
      $message = $message . "Product: ". $order["name"] ." ". $order["type"] ." ". $order["description"] ."\n".
                 "Price: £" . $order["price"] ."\n".
                 "Quantity : " . $order["quantity"]. "\n\n";
    }
  }
  if(mail(ORDER_EMAIL, "Order From Home2Garden.co.uk", $message, "From: home2garden.co.uk") == FALSE) echo "failed";
  setcookie("basket", FALSE);
  $ret = SendPage("finish.tem", array("finishmsg" => "Your order is now complete. Thank you for shopping with Home To Garden.<br/>"), FALSE);
  if($ret["status"] == "Value"){
    $content["content"] = $ret["value"];
    $content["textbox"] = "";
  }
}

else if($paypalfinish["status"] == "Value"){  
  setcookie("basket", FALSE);
  $ret = SendPage("finish.tem", array("finishmsg" => "Your order is now complete. Thank you for shopping with Home To Garden.<br/>"), FALSE);
  if($ret["status"] == "Value"){
    $content["content"] = $ret["value"];
    $content["textbox"] = "";
  }
}

else if($paypalcancel["status"] == "Value"){  
  setcookie("basket", FALSE);
  $ret = SendPage("finish.tem", array("finishmsg" => "Your order has been cancelled.<br/>"), FALSE);
  if($ret["status"] == "Value"){
    $content["content"] = $ret["value"];
    $content["textbox"] = "";
  }
}

else{
  $basketcontent = BuildBasketsContent($basket, "items.tem");
  if($basketcontent["status"] == "Value"){
    $basketcontent = $basketcontent["value"];
    if($GLOBALS['groupid'] < 100){ 
      $paypalform = BuildPaypalHiddenFields($basket);
      if($paypalform["status"] == "Value") $paypalform = $paypalform["value"];
      else $paypalform = "";
    }
    else $paypalform = "<a href=\"[URLPATH]shopping.php?address=true\">Continue to create an order invoice...</a>";

    $basketcontent["paypalform"] = $paypalform;
    $ret = SendPage("basket.tem", $basketcontent, FALSE);
    if($ret["status"] == "Value"){
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
}

$sidemenu_str = DisplaySideMenu();
if($sidemenu_str["status"] == "Error") echo $sidemenu_str["comment"];
else $sidemenu_content = array("menu" => $sidemenu_str["value"]);

$notice_str = DisplayNotices();
if($notice_str["status"] == "Error") echo $notice_str["comment"];
else $notice_content = array("notices" => $notice_str["value"]);

header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Pragma: no-cache');

if($GLOBALS['userid'] > 1 and $GLOBALS['groupid'] > 1){
  $logaction = "logout";
  $logname = "Logout";
}
else{
  $logaction = "login";
  $logname = "Login";
}

if($GLOBALS['groupid'] == 2){
  $homeaction = "admin.php";
  $homename = "Admin Page";
}
else{
  $homeaction = "index.php";
  $homename = "Home";
}

$header_menu =  array("logaction" => $logaction, "logname" => $logname, "homeaction" => $homeaction, "homename" => $homename);


$ret = SendPage("header.tem", $header_menu);
if($ret["status"] == "Error") echo $ret["comment"];
$ret = SendPage("sidemenubox.tem", $sidemenu_content);
if($ret["status"] == "Error") echo $ret["comment"];

//discount content:
//if ($yearnow == "2008") $content["banner"] = '<table border="0" style="color: red; text-align: center;"><tr><td>'.$banner.'</td></tr></table>';
//else $content["banner"] = '<table border="0" style="color:#FFFFFF"><tr><td>'.$banner. '</td></tr></table>';


$ret = SendPage("index.tem", $content);
if($ret["status"] == "Error") echo $ret["comment"];

if($k == NULL)$notice_content["xpinner"] = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='168' height='236'>
  <param name='MOVIE' value='images/media/spinner_30_236.swf'>
  <param name='QUALITY' value='HIGH'>
  <param name='BGCOLOR' value='#FFFFFF'>
     <embed src='images/media/spinner_30_236.swf'
            quality='high'
            type='application/x-shockwave-flash'
            bgcolor='#FFFFFF'
            width='168' height='236'>
     </embed>
</object>";
 else $notice_content["xpinner"] = '<table border="0" style="height:200px;">
  <tr>
    <td>Placeholder</td>
  </tr>
</table>';
$ret = SendPage("noticeboard.tem", $notice_content);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("footer.tem");
if($ret["status"] == "Error") echo $ret["comment"];
?>
