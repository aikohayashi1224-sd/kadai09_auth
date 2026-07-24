-- ========================================================
-- エンディングノート データベース作成SQL
-- ========================================================

-- データベース作成
CREATE DATABASE IF NOT EXISTS ending_note DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ending_note;

-- ========================================================
-- 1. users（ユーザー）
-- ========================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    survival_check_time TIME DEFAULT '09:00:00',
    alert_days INT DEFAULT 2,
    last_checked_at DATETIME,
    is_disclosed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================================
-- 2. trustees（引受人）
-- ========================================================
CREATE TABLE trustees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    priority TINYINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 3. trustees_permissions（引受人ごとの公開項目設定）
-- ========================================================
CREATE TABLE trustees_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trustee_id INT NOT NULL,
    section VARCHAR(50) NOT NULL,
    item VARCHAR(100) NOT NULL,
    is_visible TINYINT(1) DEFAULT 0,
    FOREIGN KEY (trustee_id) REFERENCES trustees(id) ON DELETE CASCADE
);

-- ========================================================
-- 4. profile（基本情報）
-- ========================================================
CREATE TABLE profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    last_name VARCHAR(50),
    first_name VARCHAR(50),
    last_name_kana VARCHAR(50),
    first_name_kana VARCHAR(50),
    birth_date DATE,
    address VARCHAR(255),
    blood_type VARCHAR(5),
    hometown VARCHAR(255),
    my_number VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 5. medical（医療・健康）
-- ========================================================
CREATE TABLE medical (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    doctor_name VARCHAR(100),
    doctor_hospital VARCHAR(100),
    doctor_phone VARCHAR(20),
    diseases TEXT,
    allergies TEXT,
    medications TEXT,
    life_support ENUM('する', 'しない', '家族に任せる'),
    organ_donation ENUM('する', 'しない', '家族に任せる'),
    organ_heart TINYINT(1) DEFAULT 0,
    organ_lung TINYINT(1) DEFAULT 0,
    organ_liver TINYINT(1) DEFAULT 0,
    organ_kidney TINYINT(1) DEFAULT 0,
    organ_pancreas TINYINT(1) DEFAULT 0,
    organ_small_intestine TINYINT(1) DEFAULT 0,
    organ_eye TINYINT(1) DEFAULT 0,
    organ_skin TINYINT(1) DEFAULT 0,
    organ_bone TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 6. assets（財産）
-- ========================================================
CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    bank_type ENUM('普通', '当座', '貯蓄'),
    bank_number VARCHAR(20),
    bank_note TEXT,
    property_address VARCHAR(255),
    property_type ENUM('所有', '賃貸', 'その他'),
    property_note TEXT,
    investment_company VARCHAR(100),
    investment_type VARCHAR(100),
    investment_note TEXT,
    insurance_company VARCHAR(100),
    insurance_number VARCHAR(100),
    insurance_receiver VARCHAR(100),
    insurance_note TEXT,
    card_company VARCHAR(100),
    card_brand ENUM('VISA', 'Mastercard', 'JCB', 'AMEX', 'その他'),
    card_note TEXT,
    loan_company VARCHAR(100),
    loan_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 7. digital（デジタル）
-- ========================================================
CREATE TABLE digital (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    email_address VARCHAR(255),
    email_note TEXT,
    sns_service VARCHAR(100),
    sns_id VARCHAR(100),
    sns_note TEXT,
    sns_after_death ENUM('削除', '追悼アカウントにする', 'そのまま残す', '家族に任せる'),
    sns_post_message TEXT,
    sns_post_person VARCHAR(100),
    sns_password VARCHAR(255),
    sns_password_location VARCHAR(255),
    subscription_service VARCHAR(100),
    subscription_fee DECIMAL(8,0),
    subscription_cancel TEXT,
    subscription_note TEXT,
    password_management TEXT,
    device_disposal TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 8. work（仕事）
-- ========================================================
CREATE TABLE work (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    employment_type ENUM('会社員', 'フリーランス', '自営業', 'その他'),
    company_name VARCHAR(100),
    company_phone VARCHAR(20),
    company_address VARCHAR(255),
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    accountant_name VARCHAR(100),
    accountant_phone VARCHAR(20),
    work_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 9. housing（賃貸・契約）
-- ========================================================
CREATE TABLE housing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    housing_type ENUM('持ち家', '賃貸', 'その他'),
    property_name VARCHAR(100),
    property_address VARCHAR(255),
    management_company VARCHAR(100),
    management_phone VARCHAR(20),
    contract_end_date DATE,
    guarantor_name VARCHAR(100),
    guarantor_phone VARCHAR(20),
    mortgage_bank VARCHAR(100),
    utility_electric VARCHAR(100),
    utility_gas VARCHAR(100),
    utility_water VARCHAR(100),
    internet_provider VARCHAR(100),
    phone_carrier VARCHAR(100),
    housing_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 10. belongings（動産・コレクション）
-- ========================================================
CREATE TABLE belongings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category ENUM('宝飾品・時計', 'ブランド品', 'コレクション', '趣味の機材', '車・バイク', 'その他'),
    item_name VARCHAR(255),
    brand VARCHAR(100),
    purchase_price DECIMAL(12,0),
    storage_location VARCHAR(255),
    disposal ENUM('売る', '譲る', '捨てる', '家族に任せる'),
    disposal_person VARCHAR(100),
    item_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 11. funeral（葬儀）
-- ========================================================
CREATE TABLE funeral (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    funeral_type ENUM('一般葬', '家族葬', '直葬', '自然葬', 'その他'),
    religion VARCHAR(100),
    funeral_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 12. contacts（人間関係）
-- ========================================================
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_type ENUM('連絡してほしい人', '連絡不要な人', 'ペットの引き取り先', '趣味コミュニティ'),
    name VARCHAR(100),
    relationship VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(255),
    contact_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 13. will（遺言・形見）
-- ========================================================
CREATE TABLE will (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    will_exists ENUM('あり', 'なし', '作成予定'),
    will_location VARCHAR(255),
    keepsake_note TEXT,
    donation_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- 14. messages（引受人へのメッセージ）
-- ========================================================
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trustee_id INT NOT NULL,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trustee_id) REFERENCES trustees(id) ON DELETE CASCADE
);

-- ========================================================
-- 15. survival_log（生存確認ログ）
-- ========================================================
CREATE TABLE survival_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    method ENUM('メール', '手動'),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);