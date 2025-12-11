<?php
$host = "localhost";
$user = "root";      // default XAMPP
$pass = "";          // default XAMPP kosong
$db   = "kantin";    // sesuaikan dengan nama database kamu

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
