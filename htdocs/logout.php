<?php
require_once 'includes/config.php';

// セッション破棄
$_SESSION = [];
session_destroy();

// ログイン画面にリダイレクト
header("Location: index.php");
exit;
