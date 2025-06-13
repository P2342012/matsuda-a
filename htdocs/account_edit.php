<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['student_id'];
$errors = [];

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE student_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: logout.php");
    exit;
}

$name = $user['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // バリデーション
    if (empty($name)) {
        $errors[] = "名前を入力してください";
    } elseif (strlen($name) > 100) {
        $errors[] = "名前は100文字以内で入力してください";
    }

    // 現在のパスワード検証
    if (!password_verify($current_password, $user['password_hash'])) {
        $errors[] = "現在のパスワードが正しくありません";
    }

    // 新しいパスワード処理
    $password_updated = false;
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "新しいパスワードは8文字以上で入力してください";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "新しいパスワードが一致しません";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $password_updated = true;
        }
    }

    // 更新処理
    if (empty($errors)) {
        if ($password_updated) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, password_hash = ? WHERE student_id = ?");
            $stmt->execute([$name, $password_hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE student_id = ?");
            $stmt->execute([$name, $user_id]);
        }

        $_SESSION['success_message'] = "アカウント情報を更新しました";
        header("Location: account_edit.php");
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <header>
        <h1>アカウント情報変更</h1>
        <div class="user-info">
            学籍番号: <?php echo htmlspecialchars($user_id); ?>
            <?php if ($user['is_admin']): ?>
                <span class="admin-badge">管理者</span>
            <?php endif; ?>
            <a href="home.php" class="btn">ホームに戻る</a>
            <a href="logout.php" class="logout-btn">ログアウト</a>
        </div>
    </header>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success" style="background-color: #dff0d8; color: #3c763d; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="name">名前:</label>
            <input type="text" id="name" name="name" required
                maxlength="100" value="<?php echo htmlspecialchars($name); ?>">
        </div>

        <div class="form-group">
            <label for="current_password">現在のパスワード:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-group">
            <label for="new_password">新しいパスワード (変更しない場合は空欄):</label>
            <input type="password" id="new_password" name="new_password" minlength="8">
        </div>

        <div class="form-group">
            <label for="confirm_password">新しいパスワード確認:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <button type="submit" class="btn">更新</button>
    </form>
</div>
</body>

</html>