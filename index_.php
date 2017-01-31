<?php
$servername = "localhost";
$username = "scada";
$password = "123456";
$dbname = "scada";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "SQL connection succeeded!";
}

$sql = "INSERT INTO `monitoring_info` (time, temp_1, temp_2, pressure_1)
VALUES ('".date("Y-m-d H:i:s")."', 1".rand(2, 6).".".rand(0, 99).", 56.8, 59);";
echo "Executing SQL-command: <br/><blockquote>".$sql."</blockquote><br/>";


if ($conn->multi_query($sql) === true) {
    echo "New records created successfully<br/>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();