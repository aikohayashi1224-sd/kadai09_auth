<?php
$page_title = '基本情報';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'         => $user_id,
        'last_name'       => $_POST['last_name'] ?? '',
        'first_name'      => $_POST['first_name'] ?? '',
        'last_name_kana'  => $_POST['last_name_kana'] ?? '',
        'first_name_kana' => $_POST['first_name_kana'] ?? '',
        'birth_date'      => $_POST['birth_date'] ?? '',
        'address'         => $_POST['address'] ?? '',
        'blood_type'      => $_POST['blood_type'] ?? '',
        'hometown'        => $_POST['hometown'] ?? '',
        'my_number'       => $_POST['my_number'] ?? '',
    ];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO profile
                (user_id, last_name, first_name, last_name_kana, first_name_kana,
                 birth_date, address, blood_type, hometown, my_number)
            VALUES
                (:user_id, :last_name, :first_name, :last_name_kana, :first_name_kana,
                 :birth_date, :address, :blood_type, :hometown, :my_number)
            ON DUPLICATE KEY UPDATE
                last_name       = VALUES(last_name),
                first_name      = VALUES(first_name),
                last_name_kana  = VALUES(last_name_kana),
                first_name_kana = VALUES(first_name_kana),
                birth_date      = VALUES(birth_date),
                address         = VALUES(address),
                blood_type      = VALUES(blood_type),
                hometown        = VALUES(hometown),
                my_number       = VALUES(my_number)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>基本情報</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">
                <h3 class="section-heading">氏名</h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>姓</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name'] ?? '') ?>" placeholder="例：山田">
                    </div>
                    <div class="form-group">
                        <label>名</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name'] ?? '') ?>" placeholder="例：花子">
                    </div>
                    <div class="form-group">
                        <label>せい（ふりがな）</label>
                        <input type="text" name="last_name_kana" value="<?= htmlspecialchars($row['last_name_kana'] ?? '') ?>" placeholder="例：やまだ">
                    </div>
                    <div class="form-group">
                        <label>めい（ふりがな）</label>
                        <input type="text" name="first_name_kana" value="<?= htmlspecialchars($row['first_name_kana'] ?? '') ?>" placeholder="例：はなこ">
                    </div>
                </div>

                <h3 class="section-heading">基本情報</h3>
                <div class="form-group">
                    <label>生年月日</label>
                    <input type="date" name="birth_date" value="<?= htmlspecialchars($row['birth_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>血液型</label>
                    <select name="blood_type">
                        <?php foreach (['', 'A', 'B', 'O', 'AB', '不明'] as $bt): ?>
                            <option value="<?= $bt ?>" <?= ($row['blood_type'] ?? '') === $bt ? 'selected' : '' ?>>
                                <?= $bt === '' ? '選択してください' : $bt ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>現住所</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($row['address'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>出身地</label>
                    <input type="text" name="hometown" value="<?= htmlspecialchars($row['hometown'] ?? '') ?>" placeholder="例：北海道札幌市">
                </div>
                <div class="form-group">
                    <label>マイナンバー</label>
                    <input type="text" name="my_number" value="<?= htmlspecialchars($row['my_number'] ?? '') ?>">
                    <small style="color:#e74c3c;">※ 引受人の閲覧権限に注意してください。</small>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
