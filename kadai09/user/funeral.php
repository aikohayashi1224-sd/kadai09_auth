<?php
$page_title = '葬儀';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'      => $user_id,
        'funeral_type' => $_POST['funeral_type'] ?? '',
        'religion'     => $_POST['religion'] ?? '',
        'funeral_note' => $_POST['funeral_note'] ?? '',
    ];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO funeral (user_id, funeral_type, religion, funeral_note)
            VALUES (:user_id, :funeral_type, :religion, :funeral_note)
            ON DUPLICATE KEY UPDATE
                funeral_type = VALUES(funeral_type),
                religion     = VALUES(religion),
                funeral_note = VALUES(funeral_note)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM funeral WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>葬儀</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>希望する葬儀の形式</label>
                    <select name="funeral_type">
                        <?php foreach (['', '一般葬', '家族葬', '直葬（火葬のみ）', '自然葬（樹木葬・散骨など）', '宗教葬', 'お任せ', 'その他'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['funeral_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>宗派</label>
                    <input type="text" name="religion"
                           value="<?= htmlspecialchars($row['religion'] ?? '') ?>"
                           placeholder="例：浄土宗、無宗教 など">
                </div>
                <div class="form-group">
                    <label>自由記述（希望・メッセージなど）</label>
                    <textarea name="funeral_note" style="height:160px;"
                              placeholder="例：派手にしなくていい。好きだった曲を流してほしい。"><?= htmlspecialchars($row['funeral_note'] ?? '') ?></textarea>
                </div>
                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
