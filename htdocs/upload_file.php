<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$thread_id = $_GET['thread_id'] ?? 0;

// スレッド存在チェック
$stmt = $pdo->prepare("SELECT 1 FROM threads WHERE thread_id = ?");
$stmt->execute([$thread_id]);
if (!$stmt->fetch()) {
    header("Location: home.php");
    exit;
}

$error = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // コンテンツの取得（空文字を許可）
    $content = trim($_POST['content'] ?? '');

    // ファイルエラーチェック
    $uploadErrors = [];
    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['error'] as $key => $errorCode) {
            if ($errorCode !== UPLOAD_ERR_OK && $errorCode !== UPLOAD_ERR_NO_FILE) {
                $uploadErrors[] = getUploadErrorMessage($errorCode);
            }
        }
    }

    if (!empty($uploadErrors)) {
        $error .= implode("<br>", $uploadErrors);
    }

    // コメントとファイルの両方が空の場合のみエラー
    if (empty($content) && (empty($_FILES['files']) || array_sum($_FILES['files']['size']) === 0)) {
        $error = "コメントかファイルのいずれかは必須です";
    }

    if (empty($error)) {
        try {
            // トランザクション開始
            $pdo->beginTransaction();

            // コメント作成（空文字を許可）
            $stmt = $pdo->prepare("INSERT INTO comments (thread_id, student_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$thread_id, $_SESSION['student_id'], $content]);
            $comment_id = $pdo->lastInsertId();

            // ファイルアップロード処理
            if (!empty($_FILES['files'])) {
                foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) continue;

                    $file_name = $_FILES['files']['name'][$key];
                    $file_size = $_FILES['files']['size'][$key];
                    $file_tmp = $_FILES['files']['tmp_name'][$key];

                    // ZIPファイル処理
                    if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) === 'zip') {
                        handle_zip_upload([
                            'name' => $file_name,
                            'tmp_name' => $file_tmp,
                            'size' => $file_size
                        ], $comment_id);
                        continue;
                    }

                    // 通常ファイル処理
                    $new_file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file_name);
                    $upload_path = UPLOAD_DIR . $new_file_name;

                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $stmt = $pdo->prepare("UPDATE comments SET file_path = CONCAT(COALESCE(file_path, ''), ?) WHERE comment_id = ?");
                        $stmt->execute([$upload_path . "\n", $comment_id]);
                    }
                }
            }

            $pdo->commit();
            header("Location: thread.php?id=" . $thread_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Upload error: " . $e->getMessage());
            $error = "ファイルのアップロードに失敗しました";
        }
    }
}

// エラーメッセージ表示関数
function getUploadErrorMessage($code)
{
    $errors = [
        UPLOAD_ERR_INI_SIZE => "ファイルサイズが大きすぎます（最大3GB）",
        UPLOAD_ERR_FORM_SIZE => "ファイルサイズがフォームの制限を超えています",
        UPLOAD_ERR_PARTIAL => "ファイルが一部しかアップロードされていません",
        UPLOAD_ERR_NO_FILE => "ファイルが選択されていません",
        UPLOAD_ERR_NO_TMP_DIR => "一時フォルダが存在しません",
        UPLOAD_ERR_CANT_WRITE => "ディスクへの書き込みに失敗しました",
        UPLOAD_ERR_EXTENSION => "拡張モジュールによってアップロードが中止されました",
    ];
    return $errors[$code] ?? "不明なエラーが発生しました (コード: $code)";
}

include 'includes/header.php';
?>
<!-- 以下HTML部分は同じ -->

<div class="container">
    <header>
        <h1>ファイルアップロード</h1>
        <div class="user-info">
            学籍番号: <?php echo htmlspecialchars($_SESSION['student_id']); ?>
            <a href="thread.php?id=<?php echo htmlspecialchars($thread_id); ?>" class="btn">スレッドに戻る</a>
            <a href="logout.php" class="logout-btn">ログアウト</a>
        </div>
    </header>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="thread_id" value="<?= h($thread_id) ?>">

        <div class="form-group">
            <label for="content">コメント:</label>
            <textarea id="content" name="content" rows="3" placeholder="コメントを入力"></textarea>
        </div>

        <div class="form-group">
            <label>ファイル選択（複数可・最大3GB）:</label>
            <input type="file" name="files[]" multiple accept="*/*, .zip">
            <p class="hint">
                対応形式: 全てのファイル形式（ZIPフォルダ可）<br>
                フォルダをアップロードする場合はZIP形式で圧縮してください<br>
                「開く」「キャンセル」の上のボタンを「すべてのファイル」にするとZIP以外もアップロードできます（ファイル形式は問いません）
            </p>
        </div>

        <button type="submit" class="btn">アップロード</button>
    </form>
</div>