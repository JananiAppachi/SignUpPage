<?php
require 'vendor/autoload.php';
require 'predis/autoload.php';
Predis\Autoloader::register();

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ----------------- MySQL -----------------
$mysqli = new mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
if ($mysqli->connect_error) {
    die(json_encode(["status"=>"error","message"=>"MySQL connection failed"]));
}

// ----------------- MongoDB -----------------
$mongoClient = new MongoDB\Client($_ENV['MONGO_URI']);
$collection = $mongoClient->test->profiles;

// ----------------- Redis -----------------
$redis = new Predis\Client([
    "scheme" => "tcp",
    "host" => $_ENV['REDIS_HOST'],
    "port" => $_ENV['REDIS_PORT'],
    "password" => $_ENV['REDIS_PASS']
]);

// ----------------- Session Validation -----------------
$token = $_REQUEST['token'] ?? null;
if(!$token){
    echo json_encode(["status"=>"error","message"=>"No session token"]);
    exit;
}
$user_id = $redis->get($token);
if(!$user_id){
    echo json_encode(["status"=>"error","message"=>"Session expired or invalid"]);
    exit;
}

// ----------------- Handle GET Request -----------------
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $profile = $collection->findOne(['user_id' => intval($user_id)]);
    echo json_encode(["status"=>"success", "profile"=>$profile]);
    exit;
}

// ----------------- Handle POST Request -----------------
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $age = $_POST['age'] ?? null;
    $dob = $_POST['dob'] ?? null;
    $contact = $_POST['contact'] ?? null;

    if(!$age || !$dob || !$contact){
        echo json_encode(["status"=>"error","message"=>"All fields required"]);
        exit;
    }

    $collection->updateOne(
        ['user_id' => intval($user_id)],
        ['$set' => ['age'=>$age,'dob'=>$dob,'contact'=>$contact]],
        ['upsert'=>true]
    );

    echo json_encode(["status"=>"success","message"=>"Profile updated successfully"]);
    exit;
}
?>
