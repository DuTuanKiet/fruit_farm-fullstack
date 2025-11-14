<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Lỗi kết nối CSDL: ' . $conn->connect_error]);
    exit();
}

$query = trim($_GET['q'] ?? '');
$results = [];

if (strlen($query) >= 2) {
    // Tạo chuỗi tìm kiếm dạng +xoai +xanh*
    $searchTerm = '+' . str_replace(' ', ' +', $query) . '*';

    $sql = "
        SELECT id, name, price, image_url,
            (
                (MATCH(name) AGAINST(? IN BOOLEAN MODE) * 10) +
                (MATCH(description) AGAINST(? IN BOOLEAN MODE))
            ) AS relevance_score
        FROM products
        WHERE MATCH(name) AGAINST(? IN BOOLEAN MODE)
        HAVING relevance_score > 2
        ORDER BY relevance_score DESC
        LIMIT 5
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Lỗi SQL: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $results = $result->fetch_all(MYSQLI_ASSOC);
    }

    $stmt->close();
}

echo json_encode($results);
$conn->close();
exit();
