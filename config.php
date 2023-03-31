<?php
$hostname = "localhost";
$username = "username";
$password = "password";
$dbname = "olympic_games";
$db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>