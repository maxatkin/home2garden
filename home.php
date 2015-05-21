<?php
include "global.php";
include "file.php";
include "sidemenu.php";
include "securedb.php";
include "login.php";
include "noticeboard.php";
include "search.php";

function DisplaySections($table, $link, $condition = "", $condition_id = 0){
  $content = array();
  if($GLOBALS['groupid'] <= 1){
    $selectcond = "trade = 0";
    if($condition == "") $condition = $selectcond;
    else $condition = $condition . " AND " .$selectcond;
  }

  if($condition == "") $ret = ReadDB($table, array("id", "name", "description"), "", array("displayorder", "ASC"));
  else $ret = ReadDB($table, array("id", "name", "description"), array($condition, (integer)$condition_id), array("displayorder", "ASC"));
  if($ret["status"] == "Error") return $ret;

  $category_icon = "<table border = '0'><tr>";
  $category_list = $ret["value"];
  $column_count = 0;

  foreach($category_list as $category => $category_info){
    $id = sprintf("%d", $category_info["id"]);
    $content["name"] = $category_info["name"];
    $content["description"] = $category_info["description"];
    $imagepath = "images/" . $table . "/" . $id . "/" . $id . ".jpg";
    $imagelink = "index.php?" . $link . $id;
    $content["link"] = URL_PATH . $imagelink;
    if(file_exists(BASE_PATH . $imagepath)) $content["image"] = "<a href='". URL_PATH .$imagelink . "'><img src='". URL_PATH. $imagepath ."'/></a>";
    else $content["image"] = "";
    $ret = SendPage("category.tem", $content, FALSE);
    if($ret["status"] == "Error") return $ret;
    $category_icon = $category_icon . "<td  valign=\"top\" style='text-align: center;'>" . $ret["value"] . "</td>";
    $column_count = $column_count + 1;
    if($column_count == 3){
      $category_icon = $category_icon . "</tr></table><table border = '0'><tr>";
      $column_count = 0;
    }
  }
  $category_icon = $category_icon . "</tr></table>";
  return packReturn($category_icon, "Value");
}

$content = array();

$cat    = GetFromForm("cat");
$subcat = GetFromForm("subcat");
$prod   = GetFromForm("product");
$login  = GetFromForm("login");
$logout  = GetFromForm("logout");
$contact  = GetFromForm("contact");
$aboutus  = GetFromForm("aboutus");
$trylogin  = GetFromForm("trylogin");
$tradedocs = GetFromForm("tradedocs");
$dosearch  = GetFromForm("dosearch");

if($trylogin["status"] == "Value"){
  $uname = GetFromForm("username");
  $upass = GetFromForm("password");
  $login_error = TRUE;

  if($GLOBALS['userid'] > 1 && $GLOBALS['groupid'] > 1){
    $ret = SendPage("finish.tem", array("finishmsg" => "Already logged in!"), FALSE);
    if($ret["status"] == "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }

  else if($uname["status"] == "Error" or $upass["status"] == "Error"){
    $login_error = TRUE; 
    $error_msg = "No username or password supplied";
  }
  else{
    $ret = login($uname["value"], $upass["value"]);
    if($ret["status"] == "Error") $error_msg = "Error logging in";
    else if($ret["value"] == FALSE) $error_msg = "Username or password are incorrect";
    else{ 
      $login_error = FALSE;
      if($GLOBALS['groupid'] == 2){
        $ret = SendPage("gotoadmin.tem", array(), FALSE);
        if($ret["status"] == "Error") echo $ret["comment"];
        else{
          $content["content"] = $ret["value"];
          $content["textbox"] = "";
        }
      }
      else{
        $ret = SendPage("finish.tem", array("finishmsg" => "Login was successful"), FALSE);
        if($ret["status"] == "Error") echo $ret["comment"];
        else{
          $content["content"] = $ret["value"];
          $content["textbox"] = "";
        }
      }
    }
  }
  if($login_error){
    $ret = SendPage("login.tem", array("errormsg" => $error_msg), FALSE);
    if($ret["status"] == "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
}

else if($login["status"] == "Value"){
  if($GLOBALS['userid'] > 1 && $GLOBALS['groupid'] > 1){
    $ret = SendPage("finish.tem", array("finishmsg" => "Already logged in!"), FALSE);
    if($ret["status"] == "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
  else{
    $ret = SendPage("login.tem", array("errormsg" => ""), FALSE);
    if($ret["status"] == "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["textbox"] = ""; 
    }
  }
}

else if($logout["status"] == "Value"){
  $logret = logout();
  if($logret["status"] == "Error") $msg = "There has been a problem: unable to logout. Please clear your cookies if you wish to logout.";
  else $msg = "Logout successful!";
  $ret = SendPage("finish.tem", array("finishmsg" => $msg), FALSE);
  if($ret["status"] == "Error") echo $ret["comment"];
  else{
    $content["content"] = $ret["value"];
    $content["textbox"] = "";
  }
}

else if($dosearch["status"] == "Value"){
  $prodtosearch  = GetFromForm("prodtosearch");
  if($prodtosearch["status"] == "Value"){
    $prodtosearch = $prodtosearch["value"];
    $ret = SearchSections("products", "product=", $prodtosearch);
    if($ret["status"] == "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["textbox"] = "Searched products for keywords: " . $prodtosearch;
    }
  }
}

else if($prod["status"] == "Value"){
  $abort = FALSE;
  $prod_info = ReadDB("products", array("id", "name", "description", "priceid"), array("id = %d", (integer)$prod["value"]));
  if($prod_info["status"] == "Error"){
    $abort = TRUE;
    echo $prod_info["comment"];
  }

  else{
    $prod_info = $prod_info["value"][0];
    if($GLOBALS['groupid'] > 1) $pricetable = "tradeprices";
    else $pricetable = "prices";
    $price_info = ReadDB($pricetable, array("id", "name", "description", "price"), array("priceid = %d", (integer)$prod_info["priceid"]));
    if($price_info["status"] == "Error"){
      echo $price_info["comment"];
      $abort = TRUE;
    }
  }

  if($abort == FALSE){
    $price_info = $price_info["value"];
    $picture_file = "images/products/" . $prod_info["id"] . "/" . $prod_info["id"] . ".jpg";
    $picture_file_big = "images/products/" . $prod_info["id"] . "/" . $prod_info["id"] . "_big.jpg";
    if(file_exists(BASE_PATH . $picture_file_big)){
      $prod_info["image"] =  "<a href=" . URL_PATH . "bigimage.php?product_id=". $prod_info["id"]. " onClick='return popup(this, \"product\")'> <img src = " . URL_PATH . $picture_file . " /></a>";
    }
    else $prod_info["image"] = "<img src = " . URL_PATH . $picture_file . " /></a>";

    foreach($price_info as $price){
      if(strlen($price["description"] > 0)) $price["description"] = "(" . $price["description"] . ")";
      $formatprice = sprintf("%01.2f", $price["price"]);
      $price_list_content = array("id" => $price["id"], "name" => $price["name"],
                                  "description" => $price["description"], "price" => $formatprice);
      $ret = SendPage("pricelist.tem", $price_list_content, FALSE);
      if($ret["status"] == "Error") echo $ret["comment"];
      else $price_list_str = $price_list_str . $ret["value"];
    }
    $prod_info["price"] = $price_list_str;
    

    $ret = SendPage("product.tem", $prod_info, FALSE);
    if($ret["status"] == "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
}

else if($subcat["status"] == "Value"){
  $subcatinfo = ReadDB("subcategories", array("id", "name", "catid", "description"), array("id = %d", $subcat["value"]));
  $location = "";
  $subcatdescription = "";
  if($subcatinfo["status"] != "Error"){
    $subcatinfo = $subcatinfo["value"];
    $subcatdescription = $subcatinfo[0]["description"];
    $catinfo = ReadDB("categories", array("id", "name"), array("id = %d", $subcatinfo[0]["catid"]));
    if($catinfo["status"] != "Error"){
      $catinfo = $catinfo["value"];
      $catlink = URL_PATH . "index.php?" . "cat=" . $catinfo[0]["id"];
      $subcatlink = URL_PATH . "index.php?" . "subcat=" . $subcatinfo[0]["id"];
      $location = "<a href='" . $catlink . "'>". $catinfo[0]["name"] . 
                  "</a> - <a href='" . $subcatlink . "'>". $subcatinfo[0]["name"] . "</a>:"; 
    }
  }

  $link = "product=";
  $prod_icon = DisplaySections("products", $link, "subcatid = %d", $subcat["value"]);

  $textbox_content = array();
  $textbox_content["location"] = "<h3>".$location."</h3>";
  $textbox_content["intro"] = $subcatdescription;
  $rettextbox = SendPage("content_textbox.tem", $textbox_content, FALSE);

  $page_error = False;
  if($prod_icon["status"] == "Error"){
    echo $prod_icon["comment"];
    $page_error = True;
  }
  if($rettextbox["status"] == "Error"){
    echo $rettextbox["comment"];
    $page_error = True;
  }

  if($page_error == False){
    $content = array();
    $content["content"] = $prod_icon["value"];
    $content["textbox"] = $rettextbox["value"];
  }
}

else if($cat["status"] == "Value"){
  $catinfo = ReadDB("categories", array("id", "name", "description"), array("id = %d", $cat["value"]));
  $location = "";
  $catdescription = "";
  if($catinfo["status"] != "Error"){
    $catinfo = $catinfo["value"];
    $location = "<a href='" . $catlink . "'>". $catinfo[0]["name"] ."</a>:";
    $catdescription = $catinfo[0]["description"];
  }
  $link = "subcat=";
  $subcat_icon = DisplaySections("subcategories", $link, "catid = %d", $cat["value"]);

  $textbox_content = array();
  $textbox_content["location"] = "<h3>".$location."</h3>";
  $textbox_content["intro"] = $catdescription;
  $rettextbox = SendPage("content_textbox.tem", $textbox_content, FALSE);

  $page_error = False;
  if($subcat_icon["status"] == "Error"){
    echo $subcat_icon["comment"];
    $page_error = True;
  }
  if($rettextbox["status"] == "Error"){
    echo $rettextbox["comment"];
    $page_error = True;
  }

  if($page_error == False){
    $content = array();
    $content["content"] = $subcat_icon["value"];
    $content["textbox"] = $rettextbox["value"];
  }
}

else if($contact["status"] == "Value"){
  $ret = SendPage("contact.tem", array(), FALSE);

  $textbox_content = array();
  $textbox_content["location"] = "";
  $textbox_content["intro"] = $ret["value"];
  $rettextbox = SendPage("content_textbox.tem", $textbox_content, FALSE);
 
  $page_error = False;
  if($ret["status"] == "Error"){
    echo $ret["comment"];
    $page_error = True;
  }
  if($rettextbox["status"] == "Error"){
    echo $rettextbox["comment"];
    $page_error = True;
  }

  if($page_error == False){
    $content["content"] = "";
    $content["textbox"] = $rettextbox["value"];
  }
}

else if($aboutus["status"] == "Value"){
  $ret = SendPage("aboutus.tem", array(), FALSE);

  $textbox_content = array();
  $textbox_content["location"] = "";
  $textbox_content["intro"] = $ret["value"];
  $rettextbox = SendPage("content_textbox.tem", $textbox_content, FALSE);
 
  $page_error = False;
  if($ret["status"] == "Error"){
    echo $ret["comment"];
    $page_error = True;
  }
  if($rettextbox["status"] == "Error"){
    echo $rettextbox["comment"];
    $page_error = True;
  }

  if($page_error == False){
    $content["content"] = "";
    $content["textbox"] = $rettextbox["value"];
  }
}

else if($tradedocs["status"] == "Value"){
  $textbox_content = array();
  $textbox_content["location"] = "<h3>Trade Customer Documents</h3>";
  $textbox_content["intro"] = "Here you may download documents such as our current price list";
  $rettextbox = SendPage("content_textbox.tem", $textbox_content, FALSE);
  $file_links = DisplayFiles(); 

  $page_error = False;

  if($file_links["status"] == "Error"){
    echo $file_links["comment"];
    $page_error = True;
  }

  if($rettextbox["status"] == "Error"){
    echo $rettextbox["comment"];
    $page_error = True;
  }

  if($page_error == False){
    $content = array();
    $content["content"] = $file_links["value"];
    $content["textbox"] = $rettextbox["value"];
  }
}



else{
  $link = "cat=";
  $category_icon = DisplaySections("categories", $link);
  if($category_icon["status"] == "Error") echo $catgory_icon["comment"];

  $textbox_content = array();
  $textbox_content["location"] = "";
  $textbox_content["intro"] = "Home2Garden are manufacturers and distributors of home and garden products including the very popular stainless steel Wind Dancers. These graceful wind-driven sculptures create a soothing, meditative movement, evoking a sense of peace and tranquility.";
  $rettextbox = SendPage("content_textbox.tem", $textbox_content, FALSE);
 
  $page_error = False;
  if($category_icon["status"] == "Error"){
    echo $catgory_icon["comment"];
    $page_error = True;
  }
  if($rettextbox["status"] == "Error"){
    echo $rettextbox["comment"];
    $page_error = True;
  }

  if($page_error == False){
    $content = array();
    $content["content"] = $category_icon["value"];
    $content["textbox"] = $rettextbox["value"];
  }

  if(array_key_exists("REMOTE_HOST", $_SERVER)){
    $ip = $_SERVER["REMOTE_ADDR"];
    $ip = gethostbyaddr($ip);
  }
  else $ip = "Unknown";

  if(array_key_exists("HTTP_REFERER", $_SERVER)) $referer = $_SERVER["HTTP_REFERER"];
  else $referer = "Direct Link";
  if(array_key_exists("HTTP_USER_AGENT", $_SERVER)) $user_agent = $_SERVER["HTTP_USER_AGENT"];
  else $user_agent = "";
  if(strpos($referer, "home2garden") == False) InsertDB("userstats", array("ip" => $ip, "referer" => $referer));
}




//START SHOOPING BASKET

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
 // $shipping = 5.00;
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
      $order["price"] = sprintf("%01.2f", $order["price"]);
      $total_price = $total_price + $quantity*$order["price"];      
    }
  }

  //if($total_price <= 25.00) $shipping = 2.99;
 // else if($total_price <= 50.00 and $total_price > 25.00) $shipping = 4.99;
 // else if($total_price <= 75.00 and $total_price > 50.00) $shipping = 6.99;
 // else $shipping = 0.00;
  // if($total_price <= 50.00) $shipping = 4.95;
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
  $basketcontent = BuildBasketsContent($basket, "calulator.tem");
  if($basketcontent["status"] == "Value"){
    $basketcontent = $basketcontent["value"];
    if($GLOBALS['groupid'] < 100){ 
      $paypalform = BuildPaypalHiddenFields($basket);
      if($paypalform["status"] == "Value") $paypalform = $paypalform["value"];
      else $paypalform = "";
    }
    else $paypalform = "<a href=\"[URLPATH]shopping.php?address=true\">Continue to create an order invoice...</a>";

    $basketcontent["paypalform"] = $paypalform;
    $ret = SendPage("calulator2.tem", $basketcontent, FALSE);
    if($ret["status"] == "Value"){
      $content2["content"] = $ret["value"];
      $content2["textbox"] = "";
    }
  }
}

//END SHOPPING BASKET



$highlight_type = "";
$highlight_id = "";
if($cat["status"] == "Value"){
  $highlight_type = "cat";
  $highlight_id = $cat["value"];
}
if($subcat["status"] == "Value"){
  $highlight_type = "subcat";
  $highlight_id = $subcat["value"];
}
$sidemenu_str = DisplaySideMenu($highlight_type, $highlight_id);
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


//What the heck is this?
/*
if($_GET[subcat]==18 or $_GET[product]==46 or $_GET[product]==47 or $_GET[product]==49) 
	{ ?><title>Welcome to the Home2Garden Company!</title><? }
else {?>
	<title>Welcome to the Home2Garden Company</title>
<? }
*/

$ret = SendPage("header.tem", $header_menu);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("sidemenubox.tem", $sidemenu_content);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("index.tem", $content);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("noticeboard.tem", $notice_content);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("calulator2.tem", $basketcontent);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("footer.tem");
if($ret["status"] == "Error") echo $ret["comment"];


?>
