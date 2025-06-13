<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// ログイン確認
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// カテゴリ定義
$categories = [
    '教科' => ['国語', '数学', '物理', '化学', '世界史', '日本史', '英語'],
    '研究関連' => ['卒論', '研究内容相談', 'イベント'],
    'その他' => ['趣味', '雑談', '気軽な相談']
];

// スレッド作成処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];

    if (empty($title)) {
        $error = "スレッド名を入力してください";
    } elseif (strlen($title) > 100) {
        $error = "スレッド名は100文字以内で入力してください";
    } else {
        // スレッド作成
        $stmt = $pdo->prepare("INSERT INTO threads (title, category, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $category, $_SESSION['student_id']]);

        header("Location: home.php");
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <header>
        <h1>スレッド作成</h1>
        <div class="user-info">
            学籍番号: <?php echo htmlspecialchars($_SESSION['student_id']); ?>
            <a href="home.php" class="btn">ホームに戻る</a>
            <a href="logout.php" class="logout-btn">ログアウト</a>
        </div>
    </header>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="title">スレッド名:</label>
            <input type="text" id="title" name="title" required maxlength="100">
        </div>

        <div class="form-group">
            <label>カテゴリ:</label>
            <?php foreach ($categories as $group => $items): ?>
                <fieldset>
                    <legend><?php echo htmlspecialchars($group); ?></legend>
                    <?php foreach ($items as $item): ?>
                        <label>
                            <input type="radio" name="category" value="<?php echo htmlspecialchars($item); ?>" required>
                            <?php echo htmlspecialchars($item); ?>
                        </label><br>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">作成</button>
    </form>
</div>
</body>

</html>