<?php
session_start();

// すでにログイン済みならダッシュボードへ
if (isset($_SESSION['trustee_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'メールアドレスとパスワードを入力してください。';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM trustees WHERE email = ?");
        $stmt->execute([$email]);
        $trustee = $stmt->fetch();

        if ($trustee && password_verify($password, $trustee['password'])) {
            $_SESSION['trustee_id']   = $trustee['id'];
            $_SESSION['trustee_name'] = $trustee['name'];
            $_SESSION['trustee_user_id'] = $trustee['user_id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'メールアドレスまたはパスワードが正しくありません。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>引受人ログイン - エンディングノート</title>
    <link rel="stylesheet" href="/kadai09/css/style.css">
</head>
<body>
    <div class="container" style="max-width:420px; margin-top:80px;">
        <div class="card">
            <h2 style="margin-bottom:8px;">引受人ログイン</h2>
            <p style="color:#777; font-size:0.9rem; margin-bottom:24px;">
                引受人として登録されたメールアドレスとパスワードでログインしてください。
            </p>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>パスワード</label>
                    <input type="password" name="password">
                </div>
                <button type="submit" style="width:100%;">ログイン</button>
            </form>

            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                <a href="/kadai09/index.php">ユーザーログインはこちら</a>
            </p>
        </div>
    </div>
</body>
</html>