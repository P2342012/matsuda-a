<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['student_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

// データ取得
$thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
$table_data = isset($_POST['table_data']) ? json_decode($_POST['table_data'], true) : null;

if (!$thread_id || !$table_data) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

// スレッド存在確認
$stmt = $pdo->prepare("SELECT 1 FROM threads WHERE thread_id = ?");
$stmt->execute([$thread_id]);
if (!$stmt->fetch()) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// HTMLテーブルを生成
$html = '';
if (!empty($table_data['title'])) {
    $html .= '<p><strong>' . h($table_data['title']) . '</strong></p>';
}

if (!empty($table_data['comment'])) {
    $html .= '<p>' . nl2br(h($table_data['comment'])) . '</p>';
}

$html .= '<table class="data-table">';
$html .= '<thead><tr>';

// ヘッダー行
foreach ($table_data['header'] as $header) {
    $html .= '<th>' . h($header) . '</th>';
}
$html .= '</tr></thead><tbody>';

// データ行
foreach ($table_data['cells'] as $row) {
    $html .= '<tr>';
    foreach ($row as $cell) {
        $html .= '<td>' . h($cell) . '</td>';
    }
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// コメントとして保存
$stmt = $pdo->prepare("INSERT INTO comments (thread_id, student_id, content) VALUES (?, ?, ?)");
$stmt->execute([
    $thread_id,
    $_SESSION['student_id'],
    $html
]);

header('Content-Type: application/json');
echo json_encode(['success' => true]);
