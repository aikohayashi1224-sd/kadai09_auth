<?php
$page_title = '賃貸・契約';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'housing_type', 'property_name', 'property_address',
        'management_company', 'management_phone',
        'contract_end_date', 'guarantor_name', 'guarantor_phone',
        'mortgage_bank',
        'utility_electric', 'utility_gas', 'utility_water',
        'internet_provider', 'phone_carrier', 'housing_note'
    ];
    $data = ['user_id' => $user_id];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? '';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO housing
                (user_id, housing_type, property_name, property_address,
                 management_company, management_phone,
                 contract_end_date, guarantor_name, guarantor_phone,
                 mortgage_bank,
                 utility_electric, utility_gas, utility_water,
                 internet_provider, phone_carrier, housing_note)
            VALUES
                (:user_id, :housing_type, :property_name, :property_address,
                 :management_company, :management_phone,
                 :contract_end_date, :guarantor_name, :guarantor_phone,
                 :mortgage_bank,
                 :utility_electric, :utility_gas, :utility_water,
                 :internet_provider, :phone_carrier, :housing_note)
            ON DUPLICATE KEY UPDATE
                housing_type        = VALUES(housing_type),
                property_name       = VALUES(property_name),
                property_address    = VALUES(property_address),
                management_company  = VALUES(management_company),
                management_phone    = VALUES(management_phone),
                contract_end_date   = VALUES(contract_end_date),
                guarantor_name      = VALUES(guarantor_name),
                guarantor_phone     = VALUES(guarantor_phone),
                mortgage_bank       = VALUES(mortgage_bank),
                utility_electric    = VALUES(utility_electric),
                utility_gas         = VALUES(utility_gas),
                utility_water       = VALUES(utility_water),
                internet_provider   = VALUES(internet_provider),
                phone_carrier       = VALUES(phone_carrier),
                housing_note        = VALUES(housing_note)
        ");
        $stmt->execute($data);
        $success = '保存しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM housing WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch() ?: [];

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>賃貸・契約</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <form method="post">

                <h3 class="section-heading">住居</h3>
                <div class="form-group">
                    <label>住居の種別</label>
                    <select name="housing_type" id="housing_type">
                        <?php foreach (['', '賃貸', '持ち家', 'その他'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($row['housing_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>物件名・建物名</label>
                    <input type="text" name="property_name" value="<?= htmlspecialchars($row['property_name'] ?? '') ?>" placeholder="例：○○マンション101号室">
                </div>
                <div class="form-group">
                    <label>住所</label>
                    <input type="text" name="property_address" value="<?= htmlspecialchars($row['property_address'] ?? '') ?>">
                </div>

                <!-- 賃貸のみ表示するエリア -->
                <div id="rental_section">
                    <h3 class="section-heading">管理会社・契約情報（賃貸）</h3>
                    <div class="form-group">
                        <label>管理会社名</label>
                        <input type="text" name="management_company" value="<?= htmlspecialchars($row['management_company'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>管理会社の電話番号</label>
                        <input type="tel" name="management_phone" value="<?= htmlspecialchars($row['management_phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>契約満了日</label>
                        <input type="date" name="contract_end_date" value="<?= htmlspecialchars($row['contract_end_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>保証人名</label>
                        <input type="text" name="guarantor_name" value="<?= htmlspecialchars($row['guarantor_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>保証人の電話番号</label>
                        <input type="tel" name="guarantor_phone" value="<?= htmlspecialchars($row['guarantor_phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- 持ち家のみ表示するエリア -->
                <div id="owned_section">
                    <h3 class="section-heading">住宅ローン（持ち家）</h3>
                    <div class="form-group">
                        <label>借入銀行・ローン会社</label>
                        <input type="text" name="mortgage_bank" value="<?= htmlspecialchars($row['mortgage_bank'] ?? '') ?>">
                    </div>
                </div>

                <h3 class="section-heading">各種契約</h3>
                <div class="form-group">
                    <label>電力会社</label>
                    <input type="text" name="utility_electric" value="<?= htmlspecialchars($row['utility_electric'] ?? '') ?>" placeholder="例：東京電力">
                </div>
                <div class="form-group">
                    <label>ガス会社</label>
                    <input type="text" name="utility_gas" value="<?= htmlspecialchars($row['utility_gas'] ?? '') ?>" placeholder="例：東京ガス">
                </div>
                <div class="form-group">
                    <label>水道</label>
                    <input type="text" name="utility_water" value="<?= htmlspecialchars($row['utility_water'] ?? '') ?>" placeholder="例：○○市水道局">
                </div>
                <div class="form-group">
                    <label>インターネットプロバイダ</label>
                    <input type="text" name="internet_provider" value="<?= htmlspecialchars($row['internet_provider'] ?? '') ?>" placeholder="例：○○光">
                </div>
                <div class="form-group">
                    <label>携帯キャリア</label>
                    <input type="text" name="phone_carrier" value="<?= htmlspecialchars($row['phone_carrier'] ?? '') ?>" placeholder="例：docomo">
                </div>

                <div class="form-group">
                    <label>備考</label>
                    <textarea name="housing_note"><?= htmlspecialchars($row['housing_note'] ?? '') ?></textarea>
                </div>

                <button type="submit">保存する</button>
            </form>
        </div>
    </div>
</div>

<script>
// 賃貸／持ち家で表示切り替え
function toggleHousingSections() {
    const type = document.getElementById('housing_type').value;
    document.getElementById('rental_section').style.display = (type === '賃貸') ? 'block' : 'none';
    document.getElementById('owned_section').style.display  = (type === '持ち家') ? 'block' : 'none';
}
document.getElementById('housing_type').addEventListener('change', toggleHousingSections);
toggleHousingSections(); // 初期表示
</script>

<?php require_once '../includes/footer.php'; ?>