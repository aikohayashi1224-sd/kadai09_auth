<?php
$page_title = '引受人へのメッセージ';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// 引受人一覧を取得
$stmt = $pdo->prepare("SELECT id, name, relationship FROM trustees WHERE user_id = ? ORDER BY priority");
$stmt->execute([$user_id]);
$trustees = $stmt->fetchAll();

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['messages'] ?? [] as $trustee_id => $message) {
            $trustee_id = (int)$trustee_id;
            $message    = trim($message);

            // そのtrusee_idがこのユーザーのものか確認
            $check = $pdo->prepare("SELECT id FROM trustees WHERE id = ? AND user_id = ?");
            $check->execute([$trustee_id, $user_id]);
            if (!$check->fetch()) continue;

            $stmt = $pdo->prepare("
                INSERT INTO messages (user_id, trustee_id, message)
                VALUES (:user_id, :trustee_id, :message)
                ON DUPLICATE KEY UPDATE message = VALUES(message)
            ");
            $stmt->execute([
                'user_id'    => $user_id,
                'trustee_id' => $trustee_id,
                'message'    => $message,
            ]);
        }
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

// 既存メッセージを取得（trustee_id をキーにした連想配列）
$stmt = $pdo->prepare("SELECT trustee_id, message FROM messages WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing = [];
foreach ($stmt->fetchAll() as $row) {
    $existing[$row['trustee_id']] = $row['message'];
}

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>引受人へのメッセージ</h2>
            <p class="page-desc">引受人それぞれへ、感謝や伝えたいことを書き残しておきましょう。</p>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <?php if (empty($trustees)): ?>
                <p style="color:#999;">
                    引受人がまだ登録されていません。<br>
                    先に<a href="trustees.php">引受人設定</a>から登録してください。
                </p>
            <?php else: ?>
                <form method="post">
                    <?php foreach ($trustees as $trustee): ?>
                        <h3 class="section-heading">
                            <?= htmlspecialchars($trustee['name']) ?>さん
                            <span style="font-size:0.85rem; font-weight:normal; color:#777;">
                                （<?= htmlspecialchars($trustee['relationship']) ?>）
                            </span>
                        </h3>
                        <div class="form-group">
                            <textarea
                                name="messages[<?= $trustee['id'] ?>]"
                                style="height:180px;"
                                placeholder="<?= htmlspecialchars($trustee['name']) ?>さんへのメッセージを書いてください"
                            ><?= htmlspecialchars($existing[$trustee['id']] ?? '') ?></textarea>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit">保存する</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>