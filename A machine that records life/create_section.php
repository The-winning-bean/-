<?php
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "日记管理";

function generatePath($title, $conn) {
    $base_path = '/sections/' . $title;
    $path = $base_path . '/index.php';
    $counter = 1;

    while (pathExists($path, $conn)) {
        $path = $base_path . '-' . $counter . '/index.php';
        $counter++;
    }

    return $path;
}

function pathExists($path, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sections WHERE url = :url");
    $stmt->bindParam(':url', $path);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function createHtmlFile($path, $coverPath, $title) {
    $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title</title>
    <link rel="stylesheet" href="static/styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
        }

        nav {
            background-color: #333;
            padding: 1rem;
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: space-around;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            margin: 0;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        nav ul li a:hover {
            color: #ff6347;
            transform: scale(1.1);
        }

        .main-content {
            display: flex;
        }

        .cover-section {
            width: 25%;
            padding: 20px;
            box-sizing: border-box;
            background-color: white;
            margin: 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .cover-image img {
            width: 100%;
            border-radius: 10px;
        }

        .cover-title {
            margin-top: 10px;
        }

        .content-section {
            width: 70%;
            padding: 20px;
            box-sizing: border-box;
            margin: 20px;
        }

        .content-section ul {
            list-style-type: none;
            padding: 0;
        }

        .content-section li {
            margin-bottom: 10px;
        }

        .content-section li a {
            text-decoration: none;
            color: #333;
        }

        .content-section li a:hover {
            color: #575757;
        }

        .upload-form {
            margin-bottom: 20px;
        }

        .upload-form input[type="file"] {
            margin-bottom: 10px;
        }

        .upload-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }

        .upload-form input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="../../index.html">日月竹墨神机</a></li>
            <li><a href="../../index.html">日记</a></li>
            <li><a href="http://127.0.0.1/">画廊</a></li>
            <li><a href="../../敬请期待.html">个人信息</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="cover-section">
            <div class="cover-image">
                <img src="$coverPath" alt="Cover Image">
            </div>
            <div class="cover-title">
                <h2>$title</h2>
            </div>

            <div class="upload-form">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="pdfFile" accept=".pdf" required>
                    <input type="submit" value="上传 PDF 文件">
                </form>
            </div>
        </div>

        <div class="content-section">
            <h2>文件列表</h2>
            <ul id="fileList">
                <!-- 文件列表项将在这里生成 -->
            </ul>
        </div>
    </div>

    <script>
        // JavaScript to dynamically load file list as HTML
        fetch('files.php')
            .then(response => response.text())
            .then(html => {
                const fileListContainer = document.getElementById('fileList');
                fileListContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('加载文件列表时出错:', error);
                document.getElementById('fileList').innerHTML = '<p>加载文件列表时出错。</p>';
            });
    </script>
</body>
</html>
HTML;

    file_put_contents($path, $htmlContent);
}

function createUploadScript($path) {
    $uploadScriptContent = <<<PHP
<?php
if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset(\$_FILES['pdfFile']) && \$_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        \$fileTmpPath = \$_FILES['pdfFile']['tmp_name'];
        \$fileName = basename(\$_FILES['pdfFile']['name']);
        \$uploadDir = __DIR__ . '/upload/';
        \$filePath = \$uploadDir . \$fileName;

        if (!is_dir(\$uploadDir)) {
            mkdir(\$uploadDir, 0777, true);
        }

        if (move_uploaded_file(\$fileTmpPath, \$filePath)) {
            echo json_encode(['status' => 'success', 'message' => '文件上传成功']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '文件移动失败']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '无效的文件上传']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '无效的请求方法']);
}
?>
PHP;

    file_put_contents(dirname($path) . '/upload.php', $uploadScriptContent);
}

function createFilesScript($path) {
    $filesScriptContent = <<<PHP
<?php
\$uploadDir = __DIR__ . '/upload/';
\$files = array_diff(scandir(\$uploadDir), array('.', '..'));

\$fileListHtml = '<ul>';
foreach (\$files as \$file) {
    \$fileUrl = '/sections/' . basename(__DIR__) . '/upload/' . \$file;
    \$fileListHtml .= '<li><a href="' . \$fileUrl . '" target="_blank" style="color: #007bff; text-decoration: none;">' . htmlspecialchars(\$file) . '</a></li>';

}
\$fileListHtml .= '</ul>';

header('Content-Type: text/html');
echo \$fileListHtml;
?>
PHP;

    file_put_contents(dirname($path) . '/files.php', $filesScriptContent);
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'];

        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $cover = $_FILES['cover'];

            $coverPath = '/uploads/' . basename($cover['name']);
            $targetCoverFile = $_SERVER['DOCUMENT_ROOT'] . $coverPath;

            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/uploads')) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads', 0777, true);
            }

            if (move_uploaded_file($cover['tmp_name'], $targetCoverFile)) {
                $url = generatePath($title, $conn);

                $targetHtmlFile = $_SERVER['DOCUMENT_ROOT'] . $url;
                $targetDir = dirname($targetHtmlFile);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                    mkdir($targetDir . '/upload', 0777, true);
                    createUploadScript($targetHtmlFile);
                    createFilesScript($targetHtmlFile);
                }

                createHtmlFile($targetHtmlFile, $coverPath, $title);

                $stmt = $conn->prepare("INSERT INTO sections (title, cover, url) VALUES (:title, :cover, :url)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':cover', $coverPath);
                $stmt->bindParam(':url', $url);

                if ($stmt->execute()) {
                    $lastId = $conn->lastInsertId();
                    echo json_encode(['status' => 'success', 'message' => '新分区创建成功', 'id' => $lastId, 'title' => $title, 'cover' => $coverPath, 'url' => $url]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => '创建失败']);
                }
            } else {
                file_put_contents('debug.log', "Failed to move uploaded file.\n", FILE_APPEND);
                echo json_encode(['status' => 'error', 'message' => '文件上传失败']);
            }
        } else {
            file_put_contents('debug.log', "Invalid cover file. Error code: " . $_FILES['cover']['error'] . "\n", FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => '无效的封面文件']);
        }
    }
} catch(PDOException $e) {
    file_put_contents('debug.log', "Database connection failed: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => '连接失败: ' . $e->getMessage()]);
}

$conn = null;
?>
