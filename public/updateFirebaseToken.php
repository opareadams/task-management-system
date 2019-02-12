<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "melcom_db";

$mobile = $_GET['mobile'];
$token = $_GET['token'];


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "UPDATE melcom_db.users set firebase_token='".$token."' where mobile = ".$mobile." ";
echo $sql;

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
