<?php
// db_connection.php

$servername = "aws-0-ap-southeast-1.pooler.supabase.com";
$port = "5432";
$username = "postgres.chnpfhzdigypbpnbztkm";
$password = "elearning111211-A";
$dbname = "postgres";

try {
    $conn = new PDO("pgsql:host=$servername;port=$port;dbname=$dbname;sslmode=require", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>