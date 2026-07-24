<?php
// タイトルが設定されていない場合のデフォルト
$page_title = $page_title ?? 'エンディングノート';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - エンディングノート</title>
    <link rel="stylesheet" href="/kadai09/css/style.css">
    <!-- <link rel="stylesheet" href="/gs_kadai/kadai09/css/style.css"> -->
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 class="site-title"><a href="/kadai09/index.php">📋 エンディングノート</a></h1>
            <nav class="site-nav">
                <span>ようこそ、<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>さん</span>
                <a href="/kadai09/logout.php">ログアウト</a>
            </nav>
        </div>
    </header>
    <div class="container">