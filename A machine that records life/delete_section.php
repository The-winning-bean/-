<?php
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "日记管理";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM sections WHERE id = :id");
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo "<script>alert('分区删除成功');</script>";
        } else {
            echo "删除失败";
        }
    }
} catch(PDOException $e) {
    echo "连接失败: " . $e->getMessage();
}

$conn = null;
?>
