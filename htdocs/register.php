<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['student_id'])) {
    header("Location: home.php");
    exit;
}

$errors = [];
$student_id = '';
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // バリデーション
    if (!preg_match('/^\d{7}$/', $student_id)) {
        $errors[] = "学籍番号は7桁の数字で入力してください";
    }

    if (empty($name)) {
        $errors[] = "名前を入力してください";
    } elseif (strlen($name) > 100) {
        $errors[] = "名前は100文字以内で入力してください";
    }

    if (strlen($password) < 8) {
        $errors[] = "パスワードは8文字以上で入力してください";
    } elseif ($password !== $confirm_password) {
        $errors[] = "パスワードが一致しません";
    }

    // 学籍番号重複チェック
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE student_id = ?");
        $stmt->execute([$student_id]);
        if ($stmt->fetch()) {
            $errors[] = "この学籍番号は既に登録されています";
        }
    }

    // 登録処理
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (student_id, name, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $name, $password_hash]);

        $_SESSION['success_message'] = "アカウント登録が完了しました。ログインしてください。";
        header("Location: index.php");
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <header>
        <h1>アカウント作成</h1>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="student_id">学籍番号 (7桁):</label>
            <input type="text" id="student_id" name="student_id"
                pattern="\d{7}" required
                value="<?php echo htmlspecialchars($student_id); ?>">
        </div>

        <div class="form-group">
            <label for="name">名前:</label>
            <input type="text" id="name" name="name" required
                maxlength="100" value="<?php echo htmlspecialchars($name); ?>">
        </div>

        <div class="form-group">
            <label for="password">パスワード (8文字以上):</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>

        <div class="form-group">
            <label for="confirm_password">パスワード確認:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn">登録</button>
        <a href="index.php" class="btn">ログイン画面へ戻る</a>
    </form>
</div>
</body>

</html>