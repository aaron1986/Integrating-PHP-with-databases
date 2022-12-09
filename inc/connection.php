<?php
try{
    $db = new PDO("sqlite:".__DIR__."/database.db");
} catch(Exception $e) {
    echo "unable to connect to database";
    echo $e -> getMessage();
    exit;
}



?>