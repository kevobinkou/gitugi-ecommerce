<?php
// Clever Cloud MySQL Database connection settings
$host = 'burncxmkuc5zyp9jomb2-mysql.services.clever-cloud.com';
$user = 'ukayrxzzxphwxzt6';
$password = 'n4XnABzsrAjiaBqgBoil';
$database = 'burncxmkuc5zyp9jomb2';
$port = 3306; // Optional if default

// Create connection
$conn = mysqli_connect($host, $user, $password, $database, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
