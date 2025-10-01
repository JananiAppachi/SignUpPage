<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require 'predis/autoload.php';
Predis\Autoloader::register();

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// MySQL connection
$mysqli = new mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
if ($mysqli->connect_error) {
    die(json_encode(["status"=>"error","message"=>"MySQL connection failed"]));
}

// Redis connection
$redis = new Predis\Client([
    "scheme" => "tcp",
    "host" => $_ENV['REDIS_HOST'],
    "port" => $_ENV['REDIS_PORT'],
    "password" => $_ENV['REDIS_PASS']
]);

// Get POST data
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

if(!$email || !$password){
    echo json_encode(["status"=>"error","message"=>"Email and password required"]);
    exit;
}

// Fetch user from MySQL
$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_id, $password_hash);

if($stmt->num_rows === 0){
    echo json_encode(["status"=>"error","message"=>"User not found"]);
    exit;
}

$stmt->fetch();

if(password_verify($password, $password_hash)){
    // Generate session token
    $token = bin2hex(random_bytes(16));
    $redis->set($token, $user_id);
    $redis->expire($token, 3600); // token valid for 1 hour

    echo json_encode(["status"=>"success","token"=>$token]);
} else {
    echo json_encode(["status"=>"error","message"=>"Invalid password"]);
}

$stmt->close();
$mysqli->close();
?>
