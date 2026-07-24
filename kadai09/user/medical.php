<?php
$page_title = '医療・健康';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'              => $user_id,
        'doctor_name'          => $_POST['doctor_name'] ?? '',
        'doctor_hospital'      => $_POST['doctor_hospital'] ?? '',
        'doctor_phone'         => $_POST['doctor_phone'] ?? '',
        'diseases'             => $_POST['diseases'] ?? '',
        'allergies'            => $_POST['allergies'] ?? '',
        'medications'          => $_POST['medications'] ?? '',
        'life_support'         => $_POST['life_support'] ?? '',
        'organ_donation'       => $_POST['organ_donation'] ?? '未記入',
        'organ_heart'          => isset($_POST['organ_heart']) ? 1 : 0,
        'organ_lung'           => isset($_POST['organ_lung']) ? 1 : 0,
        'organ_liver'          => isset($_POST['organ_liver']) ? 1 : 0,
        'organ_kidney'         => isset($_POST['organ_kidney']) ? 1 : 0,
        'organ_pancreas'       => isset($_POST['organ_pancreas']) ? 1 : 0,
        'organ_small_intestine'=> isset($_POST['organ_small_intestine']) ? 1 : 0,
        'organ_eye'            => isset($_POST['organ_eye']) ? 1 : 0,
        'organ_skin'           => isset($_POST['organ_skin']) ? 1 : 0,
        'organ_bone'           => isset($_POST['organ_bone']) ? 1 : 0,
    ];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO medical
                (user_id, doctor_name, doctor_hospital, doctor_phone,
                 diseases, allergies, medications, life_support,
                 organ_donation,
                 organ_heart, organ_lung, organ_liver, organ_kidney,
                 organ_pancreas, organ_small_intestine, organ_eye, organ_skin, organ_bone)
            VALUES
                (:user_id, :doctor_name, :doctor_hospital, :doctor_phone,
                 :diseases, :allergies, :medications, :life_support,
                 :organ_donation,
                 :organ_heart, :organ_lung, :organ_liver, :organ_kidney,
                 :organ_pancreas, :organ_small_intestine, :organ_eye, :organ_skin, :organ_bone)
            ON DUPLICATE KEY UPDATE
                doctor_name           = VALUES(doctor_name),
                doctor_hospital       = VALUES(doctor_hospital),
                doctor_phone          = VALUES(doctor_phone),
                diseases              = VALUES(diseases),
                allergies             = VALUES(allergies),
                medications           = VALUES(medications),
                life_support          = VALUES(life_support),
                organ_donation        = VALUES(organ_donation),
                organ_heart           = VALUES(organ_heart),
                organ_lung            = VALUES(organ_lung),
                organ_liver           = VALUES(organ_liver),
                organ_kidney          = VALUES(organ_kidney),
                organ_pancreas        = VALUES(organ_pancreas),
                organ_small_intestine = VALUES(organ_small_intestine),
                organ_eye             = VALUES(organ_eye),
                organ_skin            = VALUES(organ_skin),
                organ_bone            = VALUES(organ_bone)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM medical WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>医療・健康</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">

                <h3 class="section-heading">かかりつけ医</h3>
                <div class="form-group">
                    <label>担当医師名</label>
                    <input type="text" name="doctor_name" value="<?= htmlspecialchars($row['doctor_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>病院・クリニック名</label>
                    <input type="text" name="doctor_hospital" value="<?= htmlspecialchars($row['doctor_hospital'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>電話番号</label>
                    <input type="tel" name="doctor_phone" value="<?= htmlspecialchars($row['doctor_phone'] ?? '') ?>">
                </div>

                <h3 class="section-heading">健康情報</h3>
                <div class="form-group">
                    <label>既往歴・持病</label>
                    <textarea name="diseases"><?= htmlspecialchars($row['diseases'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>アレルギー（薬・食物など）</label>
                    <textarea name="allergies"><?= htmlspecialchars($row['allergies'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>常用薬</label>
                    <textarea name="medications"><?= htmlspecialchars($row['medications'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>延命治療の希望</label>
                    <select name="life_support">
                        <?php foreach (['', '希望する', '希望しない', '家族に任せる', '状況による'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['life_support'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h3 class="section-heading">臓器提供</h3>
                <div class="form-group">
                    <label>臓器提供の意思</label>
                    <select name="organ_donation">
                        <?php foreach (['未記入', '提供する', '一部提供', '家族に一任', '提供しない'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['organ_donation'] ?? '未記入') === $t ? 'selected' : '' ?>>
                                <?= $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>提供する臓器の指定（任意）</label>
                    <?php
                    $organs = [
                        'organ_heart'           => '心臓',
                        'organ_lung'            => '肺',
                        'organ_liver'           => '肝臓',
                        'organ_kidney'          => '腎臓',
                        'organ_pancreas'        => '膵臓',
                        'organ_small_intestine' => '小腸',
                        'organ_eye'             => '眼球（角膜）',
                        'organ_skin'            => '皮膚',
                        'organ_bone'            => '骨・骨髄',
                    ];
                    foreach ($organs as $col => $label):
                        $checked = !empty($row[$col]) ? 'checked' : '';
                    ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="<?= $col ?>" value="1" <?= $checked ?>>
                        <?= $label ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>