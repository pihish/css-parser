<?php

require_once("parser.php");

// Flag that determines if we will save meta data to DB
$save_meta = false;

// Gets name of file uploaded, all spaces are replaced with underscore
$file_name = str_replace(" ", "_", $_FILES['file']['name']);
// Quick and dirty file extension check to make sure file is at least saved with ".css"
$file_extension = end(explode(".", $file_name));

if($file_extension === "css"){
    // Give file name a timestamp so files with the same name can be differentiated
    $new_file_name = date("mdyHis") . "-" . $file_name;
    // Flag that determines if we should continue with other operations depending on file type
    $extension_check = true;
}

if($extension_check === true){
    // Bring in our Parser class and call the getter / setter
    $parser = new Parser();
    if($parser->parse($_FILES["file"]["tmp_name"], $new_file_name)){
        echo $parser->parse($_FILES["file"]["tmp_name"], $new_file_name);
        // After we call our parser, move the CSS file into the uploads directory
        move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/$new_file_name");
    }
    else{
        echo "400-error";
    };
}
else{
    echo "type-error";
}

// Secret info that connects us to database
$server_address = "http://yourserver";
$database = "creative_market";
$user = "root";
$password = "itsasecret";

// Save some meta data if we want to
if($extension_check === true && $save_meta === true){
    try{
        $connection = new PDO("mysql:host=" . $server_address . ";dbname=" . $database, $user, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $connection->prepare("INSERT INTO images (name, datetime, url)
                                       VALUES (:new_file_name, current_timestamp, CONCAT('uploads/', :new_file_name)");
        $query->execute(array("new_file_name" => $new_file_name));
    }
    catch(PDOException $e){
        echo "Error: " . $e->getMessage();
    }
}

?>