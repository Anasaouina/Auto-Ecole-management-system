
<?php
$host = "localhost";
$username = "postgres";
$password = "1234";
$database = "ibdw";
$port = "5432";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    $pdo = new PDO($dsn);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
