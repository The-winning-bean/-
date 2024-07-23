<?php
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "日记管理";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT id, title, cover, url FROM sections");
    $stmt->execute();

    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($sections);

} catch(PDOException $e) {
    echo "连接失败: " . $e->getMessage();
}

$conn = null;
?>
