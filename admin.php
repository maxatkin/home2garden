<?PHP
include "global.php";
include "file.php";
include "sidemenu.php";
include "securedb.php";
include "login.php";
include "noticeboard.php";

//need to go through this whole function and make sure all things are error checked

function ExtractAndCheckFormSubmission($formvars){
  $extractedvars = array();
  foreach($formvars as $name => $var){
   // print_r($formvars); echo "<br/>";
    if($var["status"] != "Value") $extractedvars[$name] = array("value" => $var["comment"], "status" => "Error");
    else if(strlen($var["value"]) == 0) $extractedvars[$name] = array("Value" => "No input", "status" => "Error");
    else $extractedvars[$name] = array("value" => $var["value"], "status" => "Value");
  }
  return $extractedvars;
}

function CategoryEdit_Common($defname, $defdescription, $reload, $action, $errormsg, $objectid = "None"){
  //Display picture
  $picture_name = "tmp_cat/" . $GLOBALS['userid'];
  $picture_file = "images/" . $picture_name . ".jpg";
  if(file_exists(BASE_PATH . $picture_file)) $currentpicture = "<img src = '[URLPATH]image.php?image=" . $picture_name ."&cache=no'/>";
  else $currentpicture = "None";

  if($action === "Edit"){
    $infomsg = "Edit Existing Category";
    $pagetype = "edit";
  }
  if($action === "Add"){
    $infomsg = "Add New Category";
    $pagetype = "add";
  }
  $ret = SendPage("editdb.tem", array("errormsg" => $errormsg, "type" => "Category", "info" => $infomsg, "selcat" => $catlist,
                                      "objectid" => $objectid, "selsubcat" => "", "defname" => $defname,
                                      "defdescription" => $defdescription, "currentpicture" => $currentpicture, 
                                      "typeeditor" => "", "action"=> $action, "pagetype" => $pagetype,
                                      "objecttype" => "category"), FALSE);
  return $ret;
}

function SubCategoryEdit_Common($defname, $defdescription, $reload, $action, $errormsg, $objectid = "None"){
  //Make option lists
  $setdefaultcat = "";
  if($objectid != "None"){
    $prod_catinfo = ReadDB("subcategories", array("id", "catid"), array("id = %d", $objectid));
    if($prod_catinfo["status"] != "Error"){
      $prod_catinfo = $prod_catinfo["value"];
      $prodcatid = $prod_catinfo[0]["catid"];
      $setdefaultcat = array("fieldtomatch" => "id", "fieldvalue" => $prodcatid);
    }
  }

  $option_list = MakeOptionList("categories", array("name", "id"), "value", "" , "", $setdefaultcat); 
  if($option_list["status"] === "Error") $option_list = "";
  else $option_list = $option_list["value"];
  $catlist = SendPage("editdb_optionlist.tem",
                array("errormsg" => "", "rowtype" => "row1", "label" => "Select Category", 
                      "name" => "category", "optionlist" => $option_list), FALSE);
  if($catlist["status"] === "Error"){
    echo $catlist["comment"];
    $catlist = "";
  }
  else $catlist = $catlist["value"];

  //Display picture
  $picture_name = "tmp_subcat/" . $GLOBALS['userid'];
  $picture_file = "images/" . $picture_name . ".jpg";
  if(file_exists(BASE_PATH . $picture_file)) $currentpicture = "<img src = '[URLPATH]image.php?image=" . $picture_name ."&cache=no'/>";
  else $currentpicture = "None";

  if($action === "Edit"){
    $infomsg = "Edit Existing SubCategory";
    $pagetype = "edit";
  }
  if($action === "Add"){
    $infomsg = "Add New SubCategory";
    $pagetype = "add";
  }
  $ret = SendPage("editdb.tem", array("errormsg" => $errormsg, "type" => "SubCategory", "info" => $infomsg, "selcat" => $catlist,
                                      "objectid" => $objectid, "selsubcat" => "", "defname" => $defname,
                                      "defdescription" => $defdescription, "currentpicture" => $currentpicture, 
                                      "typeeditor" => "", "action"=> $action, "pagetype" => $pagetype, 
                                      "objecttype" => "subcategory"), FALSE);
  return $ret;
}

function AddEdit_Common($defname, $defdescription, $reload, $newpriceid, $action, $errormsg, $objectid = "None"){
  //Make option lists
  $setdefaultcat = "";
  $setdefaultsubcat = "";

  if($objectid != "None"){
    $prod_catinfo = ReadDB("products", array("id", "catid", "subcatid"), array("id = %d", $objectid));
    if($prod_catinfo["status"] != "Error"){
      $prod_catinfo = $prod_catinfo["value"];
      $prodcatid = $prod_catinfo[0]["catid"];
      $prodsubcatid = $prod_catinfo[0]["subcatid"];
      $setdefaultcat = array("fieldtomatch" => "id", "fieldvalue" => $prodcatid);
      $setdefaultsubcat = array("fieldtomatch" => "id", "fieldvalue" => $prodsubcatid);
    }
  }

  if($reload === "true"){
    $selected_category = GetFromForm("categrory");
    $selected_subcategory = GetFromForm("subcategory");
    $setdefaultcat = array("fieldtomatch" => "id", "fieldvalue" => $selected_category["value"]);
    $setdefaultsubcat = array("fieldtomatch" => "id", "fieldvalue" => $selected_subcategory["value"]);
  }

  print_r($setdefaultsubcat);

  $option_list = MakeOptionList("categories", array("name", "id"), "value", "" , "", $setdefaultcat); 
  if($option_list["status"] === "Error") $option_list = "";
  else $option_list = $option_list["value"];
  $catlist = SendPage("editdb_optionlist.tem",
                array("errormsg" => "", "rowtype" => "row1", "label" => "Select Category", 
                      "name" => "category", "optionlist" => $option_list), FALSE);
  if($catlist["status"] === "Error"){
    echo $catlist["comment"];
    $catlist = "";
  }
  else $catlist = $catlist["value"];

  $option_list = MakeOptionList("subcategories", array("name", "id"), "value", "", "", $setdefaultsubcat); 
  if($option_list["status"] === "Error") $option_list = "";
  else $option_list = $option_list["value"];
  $subcatlist = SendPage("editdb_optionlist.tem",
                  array("errormsg" => "", "rowtype" => "row2", "label" => "Select Subcategory", 
                        "name" => "subcategory", "optionlist" => $option_list), FALSE);
  if($subcatlist["status"] === "Error"){
    echo $subcatlist["comment"];
    $subcatlist = "";
  }
  else $subcatlist = $subcatlist["value"];

   //Display product types
  $currenttypes = "";
  $pricetables = array("Retail" => "prices", "Trade" => "tradeprices");
  foreach($pricetables as $key => $pricetable){
    $price_info = ReadDB($pricetable, array("id", "name", "description", "price"), array("priceid = %d", $newpriceid));
    if($price_info["status"] === "Error") $currenttypes = "None";
    else $price_info = $price_info["value"];

    foreach($price_info as $type){
      $typerow = SendPage("editdb_typerow.tem",  array("id"=> $type["id"], "name"=> $type["name"], 
                          "description"=> $type["description"],"price"=> $type["price"], "retailtrade" => $key), FALSE);
      if($typerow["status"] === "Error") continue;
      $currenttypes .= $typerow["value"];
    }
  }

  //If anything has been typed in the type-editor make sure it is redisplayed
  $deftypename = GetFromForm("typename");
  $deftypedesc = GetFromForm("typedesc"); 
  $deftypeprice = GetFromForm("typeprice"); 
  $deftypename = $deftypename["value"];
  $deftypedesc = $deftypedesc["value"];
  $deftypeprice = $deftypeprice["value"];

  $typeeditor = SendPage("editdb_typeeditor.tem",  array("currenttypes" => $currenttypes, "deftypename" => $deftypename, 
                         "deftypedesc" => $deftypedesc, "deftypeprice" => $deftypeprice, "priceid" => $newpriceid), FALSE);
  if($typeeditor["status"] === "Error") $typeeditor = "";
  else $typeeditor = $typeeditor["value"];    

  //Display picture
  $picture_name = "tmp_prod/" . $GLOBALS['userid'];
  $picture_file = "images/" . $picture_name . ".jpg";
  if(file_exists(BASE_PATH . $picture_file)) $currentpicture = "<img src = '[URLPATH]image.php?image=" . $picture_name ."&cache=no'/>";
  else $currentpicture = "None";

  $picture_file_big = "images/tmp_prod/" . $GLOBALS['userid'] . "_big.jpg";
  if(file_exists(BASE_PATH . $picture_file_big))
    $currentpicture_big = "<a href=" . URL_PATH . $picture_file_big . " onClick='return popup(this, \"product\")'> Click to Display</a>";

  else $currentpicture_big = "None";

  $bigpicture = SendPage("addbigimage.tem",  array("currentbigpicture" => $currentpicture_big), FALSE);
  if($bigpicture["status"] === "Error") $typeeditor = "";
  else $bigpicture = $bigpicture["value"];    

  if($action === "Edit"){
    $infomsg = "Edit Existing Product";
    $pagetype = "edit";
  }
  if($action === "Add"){
    $infomsg = "Add New Product";
    $pagetype = "add";
  }
  $ret = SendPage("editdb.tem", array("errormsg" => $errormsg, "type" => "Product", "info" => $infomsg, "selcat" => $catlist,
                                      "objectid" => $objectid, "selsubcat" => $subcatlist, "defname" => $defname,
                                      "defdescription" => $defdescription, "currentpicture" => $currentpicture, 
                                      "typeeditor" => $typeeditor, "action"=> $action, "pagetype" => $pagetype,
                                      "objecttype" => "product", "bigpicture" => $bigpicture), FALSE);
  return $ret;
}

if($GLOBALS['groupid'] != 2){
  $ret = SendPage("login.tem", array("errormsg" => ""), FALSE);
  if($ret["status"] === "Error") echo $ret["comment"];
  else{
    $content["content"] = $ret["value"];
    $content["textbox"] = "";
  }
}

else{
  $addproduct = GetFromForm("addproduct");
  $editproduct  = GetFromForm("editproduct");
  $deleteproduct  = GetFromForm("deleteproduct");

  $addcategory = GetFromForm("addcategory");
  $editcategory  = GetFromForm("editcategory");
  $deletecategory = GetFromForm("deletecategory");

  $addsubcategory = GetFromForm("addsubcategory");
  $editsubcategory  = GetFromForm("editsubcategory");
  $deletesubcategory = GetFromForm("deletesubcategory");

  $adduser = GetFromForm("adduser");

  $addfile = GetFromForm("addfile");
  $delfile = GetFromForm("delfile");
  $uploadfile = GetFromForm("uploadfile");
  $finishfile = GetFromForm("finishfile");

  $pagetype  = GetFromForm("pagetype");
  $addimage = GetFromForm("addimage");
  $addimagebig = GetFromForm("addimagebig");
  $addtype = GetFromForm("addtype");
  $finish_user = GetFromForm("finishuser");
  $deltype = GetFromForm("deltype");
  $finishedit = GetFromForm("finishedit");
  $pagetype = $pagetype["value"];
  $errormsg = "";

/////////////////////////////Actions
  if($addimage["status"] === "Value"){
    $objecttype  = GetFromForm("objecttype");
    if($objecttype["value"] === "product"){
      move_uploaded_file($_FILES['prodimage']['tmp_name'], BASE_PATH . "images/tmp_prod/" . $GLOBALS['userid'] . ".jpg");
      if($pagetype === "add") $addproduct["status"] = "Value";
      if($pagetype === "edit") $editproduct["status"] = "Value";
    }
    if($objecttype["value"] == "category"){
      move_uploaded_file($_FILES['prodimage']['tmp_name'], BASE_PATH . "images/tmp_cat/" . $GLOBALS['userid'] . ".jpg");
      if($pagetype === "add") $addcategory["status"] = "Value";
      if($pagetype === "edit") $editcategory["status"] = "Value";
    }
    if($objecttype["value"] == "subcategory"){
      move_uploaded_file($_FILES['prodimage']['tmp_name'], BASE_PATH . "images/tmp_subcat/" . $GLOBALS['userid'] . ".jpg");
      if($pagetype == "add") $addsubcategory["status"] = "Value";
      if($pagetype == "edit") $editsubcategory["status"] = "Value";
    }
  }

  if($addimagebig["status"] === "Value"){
    move_uploaded_file($_FILES['prodimage_big']['tmp_name'], BASE_PATH . "images/tmp_prod/" . $GLOBALS['userid'] . "_big.jpg");
    if($pagetype === "add") $addproduct["status"] = "Value";
    if($pagetype === "edit") $editproduct["status"] = "Value";
  }

  if($addtype["status"] === "Value"){
    $typename = GetFromForm("typename");
    $typedesc = GetFromForm("typedesc"); 
    $typeprice = GetFromForm("typeprice"); 
    $typebuyer = GetFromForm("typebuyer");
    $typepriceid = GetFromForm("priceid");
    $typename = $typename["value"];
    $typedesc = $typedesc["value"];
    $typeprice = $typeprice["value"];
    $typebuyer = $typebuyer["value"];
    $typepriceid = $typepriceid["value"];

    if($typebuyer === "retail"){
      InsertDB("prices", array("name" => $typename, "description" => $typedesc, "price" => $typeprice, "priceid" => $typepriceid));
    }
    else if($typebuyer === "trade"){
      InsertDB("tradeprices", array("name" => $typename, "description" => $typedesc, "price" => $typeprice, "priceid" => $typepriceid));
    }
    if($pagetype === "add") $addproduct["status"] = "Value";
    if($pagetype === "edit") $editproduct["status"] = "Value";
  }

  if($deltype["status"] === "Value"){
    $retail_to_delete = MatchFromForm("Retail");
    $trade_to_delete = MatchFromForm("Trade");
    foreach($retail_to_delete as $typeid => $action){
      if($action === "Delete") DeleteDB("prices", array("id = %d", $typeid));
    }
    foreach($trade_to_delete as $typeid => $action){
      if($action === "Delete") DeleteDB("tradeprices", array("id = %d", $typeid));
    }
    if($pagetype === "add") $addproduct["status"] = "Value";
    if($pagetype === "edit") $editproduct["status"] = "Value";
  }

  if($finish_user["status"] === "Value"){
    $username = GetFromForm("username");
    $password = GetFromForm("password");
    $errormsg = "";
    if($username["status"] === "Error") $errormsg = "Did not provide username";
    if($password["status"] === "Error") $errormsg = "Did not provide password";

    $varstocheck = array("username" => $username, "password" => $password);
    $finalvars = ExtractAndCheckFormSubmission($varstocheck);  

    $formfinished = TRUE;
    foreach($finalvars as $varname => $var){
      if($var["status"] === "Error"){
        $errormsg .= "The field: ". $varname . " was missing a value <br/>";
        $formfinished = FALSE;
        $adduser["status"] = "Value";
      }
    }

    if($formfinished == TRUE){
      $username = $username["value"];
      $password = $password["value"];
      $ret = AddUser($username, $password);
      if($ret["status"] === "Error") echo $ret["comment"];      
    }
  }

  if($finishfile["status"] === "Value"){
    $filedesc = GetFromForm("filedesc");
    $varstocheck = array("filedesc" => $filedesc);
    $finalvars = ExtractAndCheckFormSubmission($varstocheck);  
    $formfinished = TRUE;
    foreach($finalvars as $varname => $var){
      if($var["status"] === "Error"){
        $errormsg .= "The field: ". $varname . " was missing a value <br/>";
        $formfinished = FALSE;
        $addfile["status"] = "Value";
      }
    }
    
    if($formfinished == TRUE){
      $filedesc = $filedesc["value"];
      $fileid = InsertDB("tradefiles", array("description" => $filedesc));
      if($fileid["status"] === "Value"){
        if(!file_exists(BASE_PATH . "files/" . $fileid["value"])) mkdir(BASE_PATH . "files/" . $fileid["value"]);
        move_uploaded_file($_FILES['tradefile']['tmp_name'], BASE_PATH . "files/" . $fileid["value"] . "/" . $_FILES['tradefile']['name']);
      }
      else{
        $addfile["status"] = "Value";
        $errormsg = $fileid["comment"];
      }
    }
  }

  if($finishedit["status"] === "Value"){
    $objectid = GetFromForm("objectid");
    $name = GetFromForm("title");
    $description = GetFromForm("description"); 
    $category = GetFromForm("category");
    $subcategory = GetFromForm("subcategory");
    $newpriceid = GetFromForm("priceid");
    $objecttype = GetFromForm("objecttype");

    if($objecttype["value"] === "product"){
      $varstocheck = array("name" => $name, "priceid" => $newpriceid, "subcatid" => $subcategory);
    }
  
    if($objecttype["value"] === "category"){
      $varstocheck = array("name" => $name, "description" => $description);
    }
  
    if($objecttype["value"] === "subcategory"){
      $varstocheck = array("name" => $name, "description" => $description, "catid" => $category);
    }

    $finalvars = ExtractAndCheckFormSubmission($varstocheck);  
    $formfinished = TRUE;
    foreach($finalvars as $varname => $var){
      if($var["status"] === "Error"){
        $errormsg .= "The field: ". $varname . " was missing a value <br/>";
        $formfinished = FALSE;
      }
    }
    if($formfinished == FALSE){
      $objecttype  = GetFromForm("objecttype");
      if($objecttype["value"] === "product"){
        if($pagetype === "add") $addproduct["status"] = "Value";
        if($pagetype === "edit") $editproduct["status"] = "Value";
      } 
      if($objecttype["value"] === "category"){
        if($pagetype === "add") $addcategory["status"] = "Value";
        if($pagetype === "edit") $editcategory["status"] = "Value";
      }
      if($objecttype["value"] === "subcategory"){
        if($pagetype === "add") $addsubcategory["status"] = "Value";
        if($pagetype === "edit") $editsubcategory["status"] = "Value";
      }
    }

    else if($formfinished){
      $name = $name["value"];
      $description = $description["value"];
      $category = $category["value"];
      $subcategory = $subcategory["value"];
      $newpriceid = $newpriceid["value"];
      $objectid = $objectid["value"];
      
      $objecttype  = GetFromForm("objecttype");
  
      if($objecttype["value"] === "product"){
        $catid = ReadDB("subcategories", array("catid"), array("id = %d", $subcategory));
        if($catid["status"] === "Error") echo "Warning no category found for product";
        else{
          $category = $catid["value"][0]["catid"];
          $field_array = array("name" => $name, "description" => $description, "catid" => $category,
                               "subcatid" => $subcategory, "priceid" => $newpriceid);
          $table = "products";
          $tmp_image_dir = "images/tmp_prod/";
          $image_dir = "images/products/";
        }
      }
  
      if($objecttype["value"] === "category"){
        $field_array = array("name" => $name, "description" => $description);
        $table = "categories";
        $tmp_image_dir = "images/tmp_cat/";
        $image_dir = "images/categories/";
      }
  
      if($objecttype["value"] === "subcategory"){
        $field_array = array("name" => $name, "description" => $description, "catid" => $category);
        $table = "subcategories";
        $tmp_image_dir = "images/tmp_subcat/";
        $image_dir = "images/subcategories/";
      }
  
      if($objectid === "None"){
        $newid = InsertDB($table, $field_array);
      }
      else{
        UpdateDB($table, $field_array, array("id = %d", $objectid));
        $newid["value"] = (integer)$objectid;
        $newid["status"] = "Value";
      }
      if($newid["status"] === "Value"){
        $newid = $newid["value"];
        $picture_file = $tmp_image_dir . $GLOBALS['userid'] . ".jpg";
        $picture_file_big = $tmp_image_dir . $GLOBALS['userid'] . "_big.jpg";
        if($objectid === "None"){
          if(file_exists(BASE_PATH . $picture_file)){
            mkdir(BASE_PATH . $image_dir . $newid);
            rename(BASE_PATH . $picture_file, BASE_PATH . $image_dir . $newid . "/" .$newid . ".jpg");
          }
          if(file_exists(BASE_PATH . $picture_file_big)){
            if(!file_exists(BASE_PATH . $image_dir . $newid)) mkdir(BASE_PATH . $image_dir . $newid);
            rename(BASE_PATH . $picture_file_big, BASE_PATH . $image_dir . $newid . "/" .$newid . "_big.jpg");
          }
        }
        else{

//error_reporting(E_ALL);
//ini_set('display_errors','On');


          echo BASE_PATH . $image_dir . $newid . "/" .$newid . ".jpg<br/>";
          if(file_exists(BASE_PATH . $picture_file)){
            if(!file_exists(BASE_PATH . $image_dir . $newid)) mkdir(BASE_PATH . $image_dir . $newid);
            if(file_exists(BASE_PATH . $image_dir . $newid . "/" .$newid . ".jpg")){
              if(unlink(BASE_PATH . $image_dir . $newid . "/" .$newid . ".jpg") == true) echo "unlinked";
              else echo "unlink failed";
            }
            if(rename(BASE_PATH . $picture_file, BASE_PATH . $image_dir . $newid . "/" .$newid . ".jpg") == true) echo "yes";
            else echo "no";
            echo "here2 ";
          }
          if(file_exists(BASE_PATH . $picture_file_big)){
            if(!file_exists(BASE_PATH . $image_dir . $newid)) mkdir(BASE_PATH . $image_dir . $newid);
            if(file_exists(BASE_PATH . $image_dir . $newid . "/" .$newid . "_big.jpg")){
              unlink(BASE_PATH . $image_dir . $newid . "/" .$newid . "_big.jpg");
            }
            rename(BASE_PATH . $picture_file_big, BASE_PATH . $image_dir . $newid . "/" .$newid . "_big.jpg");
          }
        }
      }
    }
  }

  $defname = GetFromForm("title");
  $defdescription = GetFromForm("description"); 
  $reload = GetFromForm("reload"); 
  $newpriceid = GetFromForm("priceid");
  $defvars = ExtractAndCheckFormSubmission(array("name" => $defname, "description" => $defdescription,"priceid" =>$newpriceid));
  foreach($defvars as $name => $var){
    if($var["status"] === "Error") $defvars[$name]["value"] = "";
  }
  $defname = $defvars["name"]["value"];
  $defdescription = $defvars["description"]["value"];
  $newpriceid = $defvars["priceid"]["value"];
  

///////////////////////////////Edit Product

  if($editproduct["status"] === "Value"){
    $startedit = GetFromForm("startedit");
    if($startedit["status"] === "Value"){ 
      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];

      if($reload["status"] != "Value"){
        $prodinfo = ReadDB("products", array("id", "name", "description", "priceid", "catid", "subcatid"), array("id = %d", $objectid));
        if($prodinfo["status"] === "Error") echo $prodinfo["comment"];
        else $prodinfo  = $prodinfo["value"][0];
        $defname = $prodinfo["name"];
        $defdescription = $prodinfo["description"];
        $newpriceid = $prodinfo["priceid"];

        $picture_file = "images/tmp_prod/" . $GLOBALS['userid'] . ".jpg";
        if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);
        $rett = copy(BASE_PATH . "images/products/" . $objectid . "/" . $objectid . ".jpg", BASE_PATH . $picture_file);

        $picture_file_big = "images/tmp_prod/" . $GLOBALS['userid'] . "_big.jpg";
        if(file_exists(BASE_PATH . $picture_file_big)) unlink(BASE_PATH . $picture_file_big);
        $rett = copy(BASE_PATH . "images/products/" . $objectid . "/" . $objectid . "_big.jpg", BASE_PATH . $picture_file_big);
      }
      $ret = AddEdit_Common($defname, $defdescription, $reload["value"], $newpriceid, "Edit", $errormsg,  $objectid);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("products", array("name", "id"), "value", "", array("name", "0")); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("chooseedit.tem", array("errormsg" => $errormsg, "label" => "Product", 
                      "optionlist" => $option_list, "gotopage" => "editproduct", "action" => "startedit",
                      "buttonlabel" => "Edit"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }

/////////////////////////////////////////Add product

  else if($addproduct["status"] === "Value"){
    //Set up inital things like figuring out new priceid for prices table
    if($reload["status"] != "Value"){
      $find_newpriceid = ReadDB("products", array("max(priceid) as max_priceid"));
      $find_newpriceid = $find_newpriceid["value"];
      $find_newpriceid = $find_newpriceid[0]["max_priceid"];
      $newpriceid = $find_newpriceid + 1;
      $picture_file = "images/tmp_prod/" . $GLOBALS['userid'] . ".jpg";
      if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);

      $picture_file_big = "images/tmp_prod/" . $GLOBALS['userid'] . "_big.jpg";
      if(file_exists(BASE_PATH . $picture_file_big)) unlink(BASE_PATH . $picture_file_big);
    }
    $ret = AddEdit_Common($defname, $defdescription, $reload["value"], $newpriceid, "Add", $errormsg);

    if($ret["status"] === "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["intro"] = "";
    }
  }
  
/////////////////////////////////////////Delete product

  else if($deleteproduct["status"] === "Value"){ 
    $delete_selected = GetFromForm("delete_selected");
    $confirmdelete = GetFromForm("confirmdelete");
    if($delete_selected["status"] === "Value" and $confirmdelete["status"] === "Value"){ 
      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];

      $priceid = ReadDB("products", array("priceid"), array("id = %d", (integer)$objectid));
      if($priceid["status"] === "Error") echo $priceid["comment"];
      else $priceid  = $priceid["value"][0]["priceid"];
      DeleteDB("prices", array("priceid = %d", (integer)$priceid));
      DeleteDB("tradeprices", array("priceid = %d", (integer)$priceid));
      DeleteDB("products", array("id = %d", (integer)$objectid));

      $picture_file = "images/products/" . $objectid . "/" . $objectid . ".jpg";
      $picture_file_big = "images/products/" . $objectid . "/" . $objectid . "_big.jpg";
      if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);
      if(file_exists(BASE_PATH . $picture_file_big)) unlink(BASE_PATH  . $picture_file_big);

      $ret = SendPage("adminpage.tem", array(), FALSE);
      if($ret["status"] === "Value"){
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("products", array("name", "id"), "value", "", array("name", "0")); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("choosedelete.tem", array("errormsg" => $errormsg, "label" => "Product", 
                      "optionlist" => $option_list, "gotopage" => "deleteproduct", "action" => "delete_selected",
                      "buttonlabel" => "Delete"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }

///////////////////////////////Edit Category

  else if($editcategory["status"] === "Value"){
    $startedit = GetFromForm("startedit");
    if($startedit["status"] === "Value"){ 
      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];

      if($reload["status"] != "Value"){
        $prodinfo = ReadDB("categories", array("id", "name", "description"), array("id = %d", $objectid));
        if($prodinfo["status"] === "Error") echo $prodinfo["comment"];
        else $prodinfo  = $prodinfo["value"][0];
        $defname = $prodinfo["name"];
        $defdescription = $prodinfo["description"];

        $picture_file = "images/tmp_cat/" . $GLOBALS['userid'] . ".jpg";
        if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);
        $rett = copy(BASE_PATH . "images/categories/". $objectid . "/" . $objectid . ".jpg", BASE_PATH . $picture_file);
      }    

      $ret = CategoryEdit_Common($defname, $defdescription, $reload["value"], "Edit", $errormsg,  $objectid);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("categories", array("name", "id"), "value", "", array("name", "0")); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("chooseedit.tem", array("errormsg" => $errormsg, "label" => "Category", 
                      "optionlist" => $option_list, "gotopage" => "editcategory", "action" => "startedit",
                      "buttonlabel" => "Edit"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }


/////////////////////////////////////////Add Category

  else if($addcategory["status"] === "Value"){
    //Set up inital things like figuring out where to put pictures
    if($reload["status"] != "Value"){
      $picture_file = "images/tmp_cat/" . $GLOBALS['userid'] . ".jpg";
      if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);
    }
    $ret = CategoryEdit_Common($defname, $defdescription, $reload["vaue"], "Add", $errormsg);

    if($ret["status"] === "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["intro"] = "";
    }
  }

/////////////////////////////////////////Delete category

  else if($deletecategory["status"] === "Value"){ 
    $delete_selected = GetFromForm("delete_selected");
    $confirmdelete = GetFromForm("confirmdelete");
    if($delete_selected["status"] === "Value" and $confirmdelete["status"] === "Value"){ 

      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];
      DeleteDB("categories", array("id = %d", (integer)$objectid));
      
      if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . "images/categories/". $objectid . "/" .$objectid . ".jpg");

      $ret = SendPage("adminpage.tem", array(), FALSE);
      if($ret["status"] === "Value"){
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("categories", array("name", "id"), "value", "", array("name", "0")); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("choosedelete.tem", array("errormsg" => $errormsg, "label" => "Category", 
                      "optionlist" => $option_list, "gotopage" => "deletecategory", "action" => "delete_selected",
                      "buttonlabel" => "Delete"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }

///////////////////////////////Edit Subcategory

  else if($editsubcategory["status"] === "Value"){
    $startedit = GetFromForm("startedit");
    if($startedit["status"] === "Value"){ 
      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];

      if($reload["status"] != "Value"){
        $prodinfo = ReadDB("subcategories", array("id", "name", "description", "catid"), array("id = %d", $objectid));
        if($prodinfo["status"] === "Error") echo $prodinfo["comment"];
        else $prodinfo  = $prodinfo["value"][0];
        $defname = $prodinfo["name"];
        $defdescription = $prodinfo["description"];

        $picture_file = "images/tmp_subcat/" . $GLOBALS['userid'] . ".jpg";
        if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);
        $rett = copy(BASE_PATH . "images/subcategories/". $objectid . "/" . $objectid . ".jpg", BASE_PATH . $picture_file);
      }    

      $ret = SubCategoryEdit_Common($defname, $defdescription, $reload["value"], "Edit", $errormsg,  $objectid);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("subcategories", array("name", "id"), "value", "", array("name", "0")); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("chooseedit.tem", array("errormsg" => $errormsg, "label" => "Sub-category", 
                      "optionlist" => $option_list, "gotopage" => "editsubcategory", "action" => "startedit",
                      "buttonlabel" => "Edit"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }

/////////////////////////////////////////Add SubCategory

  else if($addsubcategory["status"] === "Value"){
    //Set up inital things like figuring out where to put pictures
    if($reload["status"] != "Value"){
      $picture_file = "images/tmp_subcat/" . $GLOBALS['userid'] . ".jpg";
      if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . $picture_file);
    }
    $ret = SubCategoryEdit_Common($defname, $defdescription, $reload["value"], "Add", $errormsg);

    if($ret["status"] === "Error") echo $ret["comment"];
    else{
      $content["content"] = $ret["value"];
      $content["intro"] = "";
    }
  }

/////////////////////////////////////////Delete Subcategory

  else if($deletesubcategory["status"] === "Value"){ 
    $delete_selected = GetFromForm("delete_selected");
    $confirmdelete = GetFromForm("confirmdelete");
    if($delete_selected["status"] === "Value" and $confirmdelete["status"] === "Value"){ 

      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];
      DeleteDB("subcategories", array("id = %d", (integer)$objectid));
      
      if(file_exists(BASE_PATH . $picture_file)) unlink(BASE_PATH . "images/subcategories/". $objectid . "/" .$objectid . ".jpg");

      $ret = SendPage("adminpage.tem", array(), FALSE);
      if($ret["status"] === "Value"){
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("subcategories", array("name", "id"), "value", "", array("name", "0")); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("choosedelete.tem", array("errormsg" => $errormsg, "label" => "SubCategory", 
                      "optionlist" => $option_list, "gotopage" => "deletesubcategory", "action" => "delete_selected",
                      "buttonlabel" => "Delete"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }

  else if($adduser["status"] === "Value"){
    $ret = SendPage("adduser.tem", array("errormsg" => $errormsg), FALSE);
    if($ret["status"] === "Value"){
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }

  else if($addfile["status"] === "Value"){
    $ret = SendPage("addfile.tem", array("errormsg" => $errormsg), FALSE);
    if($ret["status"] === "Value"){
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }

/////////////////////////////////////////Delete File

  else if($delfile["status"] === "Value"){ 
    $delete_selected = GetFromForm("delete_selected");
    $confirmdelete = GetFromForm("confirmdelete");
    if($delete_selected["status"] === "Value" and $confirmdelete["status"] === "Value"){ 

      $objectid = GetFromForm("objectid");
      $objectid = $objectid["value"];
      DeleteDB("tradefiles", array("id = %d", (integer)$objectid));

      $ret = SendPage("adminpage.tem", array(), FALSE);
      if($ret["status"] === "Value"){
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
    else{
      $option_list = MakeOptionList("tradefiles", array("description", "id"), "value"); 
      if($option_list["status"] === "Error") $option_list = "";
      else $option_list = $option_list["value"];
      $ret = SendPage("choosedelete.tem", array("errormsg" => $errormsg, "label" => "Trade File", 
                      "optionlist" => $option_list, "gotopage" => "delfile", "action" => "delete_selected",
                      "buttonlabel" => "Delete"), FALSE);
      if($ret["status"] === "Error") echo $ret["comment"];
      else{
        $content["content"] = $ret["value"];
        $content["intro"] = "";
      }
    }
  }

  else{
    $hits = ReadDB("userstats", array("ip"));
    if($hits["status"] === "Value"){
      $hits = sizeof($hits["value"]);
    }
    else $hits = "";

    $referers = ReadDB("userstats", array("referer"), array("referer <> 'Direct Link'"), array("id DESC LIMIT 10"));
    if($referers["status"] === "Value"){
      $referers = $referers["value"];
    }
    $referer_str = "";

    foreach($referers as $key => $referer){
      if(strlen($referer["referer"]) > 70) $referer_link = "Link";
      else $referer_link = $referer["referer"];
      $referer_str = $referer_str. "<a href='" . $referer["referer"] . "'>" . $referer_link . "</a><br/>";
    }

    $ret = SendPage("adminpage.tem", array("number" => $hits, "referers" => $referer_str), FALSE);
    if($ret["status"] === "Value"){
      $content["content"] = $ret["value"];
      $content["textbox"] = "";
    }
  }
}


$sidemenu_str = DisplaySideMenu();
if($sidemenu_str["status"] === "Error") echo $sidemenu_str["comment"];
else $sidemenu_content = array("menu" => $sidemenu_str["value"]);
  
$notice_str = DisplayNotices();
if($notice_str["status"] === "Error") echo $notice_str["comment"];
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

$ret = SendPage("index.tem", $content);
if($ret["status"] == "Error") echo $ret["comment"];

$ret = SendPage("noticeboard.tem", $notice_content);
if($ret["status"] == "Error") echo $ret["comment"];
$ret = SendPage("footer.tem");
if($ret["status"] == "Error") echo $ret["comment"];

?>
