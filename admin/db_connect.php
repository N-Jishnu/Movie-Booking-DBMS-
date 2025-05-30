<?php 

$conn = new mysqli('localhost', 'root', '', 'theater_db') or die("Could not connect to MySQL: " . mysqli_error($conn));

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");
