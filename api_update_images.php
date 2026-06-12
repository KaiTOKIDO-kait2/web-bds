<?php
// Tệp tin API để nhận ảnh từ local upload lên và cập nhật vào Database
header('Content-Type: application/json; charset=utf-8');

// Đọc file .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo json_encode(["status" => "error", "message" => "Không tìm thấy file .env"]);
    exit;
}

$envVariables = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $envVariables[trim($key)] = trim($value);
    }
}

$dbHost = $envVariables['MYSQL_HOST'] ?? 'localhost';
$dbName = $envVariables['MYSQL_DATABASE'] ?? 'realestatephp_new';
$dbUser = $envVariables['MYSQL_USER'] ?? 'root';
$dbPass = $envVariables['MYSQL_PASSWORD'] ?? '';
$dbPort = $envVariables['MYSQL_PORT'] ?? 3306;

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Lỗi kết nối CSDL: " . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

// 1. Lấy danh sách Property ID (pid) theo loại (Nhà, Văn phòng...)
if ($action === 'get_pids') {
    $type_keyword = $_POST['type_keyword'] ?? '';
    
    // NHA -> tìm type_name chứa 'Nhà', 'Căn hộ', 'Biệt thự'
    // VAN_PHONG -> tìm type_name chứa 'Văn phòng'
    
    $sql = "SELECT pid FROM property WHERE 1=1";
    if ($type_keyword === 'NHA') {
        $sql .= " AND (type LIKE '%Nhà%' OR type LIKE '%Căn hộ%' OR type LIKE '%Biệt thự%')";
    } elseif ($type_keyword === 'VAN_PHONG') {
        $sql .= " AND type LIKE '%Văn phòng%'";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(["status" => "success", "pids" => $pids]);
    exit;
}

// 2. Nhận ảnh và cập nhật Database
if ($action === 'upload') {
    $pid = $_POST['pid'] ?? 0;
    if (!$pid) {
        echo json_encode(["status" => "error", "message" => "Thiếu PID"]);
        exit;
    }
    
    $uploadDir = __DIR__ . '/admin/property/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $imageFields = ['pimage', 'pimage1', 'pimage2', 'pimage3', 'pimage4'];
    $uploadedNames = ['', '', '', '', ''];
    
    for ($i = 0; $i < 5; $i++) {
        $fileKey = 'image_' . $i;
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            // Đặt tên file ngẫu nhiên để tránh trùng
            $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
            $newName = uniqid('auto_') . '_' . time() . '_' . $i . '.' . $ext;
            
            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $newName)) {
                $uploadedNames[$i] = $newName;
            }
        }
    }
    
    // Xóa các ảnh bản đồ (sơ đồ) vì yêu cầu là bỏ qua
    $sql = "UPDATE property SET 
            pimage = :img0, pimage1 = :img1, pimage2 = :img2, pimage3 = :img3, pimage4 = :img4,
            mapimage = '', topmapimage = '', groundmapimage = ''
            WHERE pid = :pid";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':img0' => $uploadedNames[0],
        ':img1' => $uploadedNames[1],
        ':img2' => $uploadedNames[2],
        ':img3' => $uploadedNames[3],
        ':img4' => $uploadedNames[4],
        ':pid' => $pid
    ]);
    
    echo json_encode(["status" => "success", "message" => "Đã cập nhật bài đăng ID $pid"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Action không hợp lệ"]);
