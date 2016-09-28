<?php

require __DIR__ . "/../vendor/autoload.php";

//connect to database
$oDb = new PDO("sqlite:" . __DIR__ . "/../products.sqlite");

$oApp = new \Slim\Slim(array(
        'view' => new \PHPView\PHPView(),
        'templates.path' => __DIR__ . "/../views" ));

$oApp->get("/", function()use($oApp){
    $oApp->render("main.phtml");
});

$oApp->get("/product", function()use($oApp){
    $oApp->render("product.phtml");
});

$oApp->get("/about", function()use($oApp){
   $oApp->render("about.phtml"); 
});

$oApp->get("/crud", function()use($oApp){
   $oApp->render("crud.phtml");
});

$oApp->get("/contact", function()use($oApp){
   $oApp->render("contact.phtml"); 
});

$oApp->get("/privacy", function()use($oApp){
   $oApp->render("privacy.phtml"); 
});

$oApp->get("/success", function()use($oApp){
   $oApp->render("success.phtml"); 
});

$oApp->get("/failure", function()use($oApp){
   $oApp->render("failure.phtml"); 
});

$oApp->get("/products/:productID", function($nId){
    renderProduct($nId);
});

//endpoints for crud

$oApp->get("/crud/products", new \Auth(), function() use($oDb){
    $oStmt = $oDb->prepare("SELECT * FROM products");
    $oStmt->execute();
    $aProducts = $oStmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($aProducts);
});

$oApp->post("/crud/products", new \Auth(), function() use($oDb, $oApp){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("INSERT INTO products(name, description) VALUES(:name, :description)");
    $oStmt->bindParam("name", $oData->name);
    $oStmt->bindParam("description", $oData->description);
    $oStmt->execute();
    $oData->productID = $oDb->lastInsertId();
    echo json_encode($oData);
});

$oApp->post("/crud/products/:productID", new \Auth(), function($nProductID) use($oDb, $oApp){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("UPDATE products SET name = :name, description = :description where productID = :id");
    $oStmt->bindParam("name", $oData->name);
    $oStmt->bindParam("description", $oData->description);
    $oStmt->bindParam("id", $nProductID);
    $oStmt->execute();
    echo json_encode($oData);
});

$oApp->delete("/crud/products/:productID", new \Auth(), function($nProductID) use($oDb, $oApp){
    $oStmt = $oDb->prepare("DELETE FROM products where productID = :id");
    $oStmt->bindParam("id", $nProductID);
    $oStmt->execute();
    echo '{"result":"success"}';
});

// endpoints for auth

$oApp->get("/login", function() use( $oApp){
    // see if this is the original redirect or if it's the callback
    $sCode = $oApp->request->params('code');
    // get the uri to redirect to
    $sUrl = "http";
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
    {
        $sUrl .= "s";
    }
    $sUrl .= "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $oAuth = new \Oauth2($sUrl);
    if($sCode == null){
        $oApp->response->redirect($oAuth->redirectUrl());
    }else{
        $oAuth->handleCode($sCode);
        $oApp->response->redirect("/");
    }
});
$oApp->get("/currentUser", new \Auth(), function() use($oApp){
    echo json_encode($_SESSION['CurrentUser']);
});
$oApp->get("/logout", function(){
    session_start();
    unset($_SESSION["CurrentUser"]);
});

$oApp->run();

function renderProduct($nId){
    global $oApp, $oDb;
    // fetching product
    $oStmt = $oDb->prepare("SELECT * FROM products WHERE productID = :id");
    $oStmt->bindParam("id", $nId);
    $oStmt->execute();
    $aProduct = $oStmt->fetchAll(PDO::FETCH_OBJ);
    
    //fetching images
    $oStmt = $oDb->prepare("SELECT * FROM images WHERE productID = :id");
    $oStmt->bindParam("id", $nId);
    $oStmt->execute();
    $aImages = $oStmt->fetchAll(PDO::FETCH_OBJ);

    //fetching offers
    $oStmt = $oDb->prepare("SELECT * FROM offers WHERE productID = :id");
    $oStmt->bindParam("id", $nId);
    $oStmt->execute();
    $aOffers = $oStmt->fetchAll(PDO::FETCH_OBJ);

    //fetching list of products for the bar across the bottom
    $oStmt = $oDb->prepare("SELECT * FROM products");
    $oStmt->execute();
    $aProducts = $oStmt->fetchAll(PDO::FETCH_OBJ);    
    
    // render template with data
    $oApp->render("product.phtml", array("product"=>$aProduct[0], "images"=>$aImages, "offers"=>$aOffers, "products"=>$aProducts));   
}
