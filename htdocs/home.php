<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$category_filter = $_GET['category'] ?? null;
$order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';

$sql = "SELECT t.*, u.is_admin 
        FROM threads t
        JOIN users u ON t.created_by = u.student_id
        WHERE ? IS NULL OR t.category = ?
        ORDER BY t.created_at $order";
$stmt = $pdo->prepare($sql);
$stmt->execute([$category_filter, $category_filter]);
$threads = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM threads ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <header>
        <h1>スレッド一覧</h1>

    </header>
    // ...ユーザー情報表示部分にリンク追加
    <div class="user-info">
        学籍番号: <?php echo h($_SESSION['student_id']); ?>
        <?php if (is_admin()): ?>
            <span class="admin-badge">管理者</span>
        <?php endif; ?>
        <a href="account_edit.php" class="btn">アカウント情報変更</a>
        <a href="logout.php" class="logout-btn">ログアウト</a>
    </div>
    <div class="controls">
        <a href="create_thread.php" class="btn">スレッド作成</a>

        <div class="sort-filter">
            <form method="get" class="inline-form">
                <label for="order">並び順:</label>
                <select name="order" id="order" onchange="this.form.submit()">
                    <option value="desc" <?= (!isset($_GET['order']) || $_GET['order'] === 'desc') ? 'selected' : '' ?>>新しい順</option>
                    <option value="asc" <?= (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'selected' : '' ?>>古い順</option>
                </select>
                <?php if (isset($_GET['category'])): ?>
                    <input type="hidden" name="category" value="<?php echo h($_GET['category']); ?>">
                <?php endif; ?>
            </form>

            <form method="get" class="inline-form">
                <label for="category">カテゴリ:</label>
                <select name="category" id="category" onchange="this.form.submit()">
                    <option value="">すべて</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo h($cat); ?>"
                            <?php echo isset($_GET['category']) && $_GET['category'] === $cat ? 'selected' : ''; ?>>
                            <?php echo h($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($_GET['order'])): ?>
                    <input type="hidden" name="order" value="<?php echo h($_GET['order']); ?>">
                <?php endif; ?>
            </form>

            <?php if (isset($_GET['category']) || isset($_GET['order'])): ?>
                <a href="home.php" class="btn">すべて表示</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="thread-list">
        <?php if (empty($threads)): ?>
            <p>スレッドがありません</p>
        <?php else: ?>
            <?php foreach ($threads as $thread): ?>
                <div class="thread-item">
                    <div class="thread-header">
                        <h3><a href="thread.php?id=<?php echo $thread['thread_id']; ?>">
                                <?php echo h($thread['title']); ?>
                            </a></h3>
                        <span class="category"><?php echo h($thread['category']); ?></span>
                    </div>

                    <div class="thread-meta">
                        <span>投稿者: <?php echo h($thread['created_by']); ?></span>
                        <span>投稿日時: <?php echo h($thread['created_at']); ?></span>

                        <?php if ($_SESSION['student_id'] === $thread['created_by'] || $_SESSION['student_id'] === '9877389'): ?>
                            <form method="post" action="delete.php" class="inline-form">
                                <input type="hidden" name="thread_id" value="<?php echo $thread['thread_id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('本当に削除しますか？')">削除</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>

</html>