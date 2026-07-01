<?php
$host = "localhost";
$database = "badcut";
$user = "root";
$password = "";
$link = mysqli_connect($host, $user, $password, $database);

if (!$link) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($link, "utf8mb4");
?>