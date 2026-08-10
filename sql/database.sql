-- Sistem Peminjaman Inventaris v2
-- Database: db_inventaris

CREATE DATABASE IF NOT EXISTS db_inventaris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_inventaris;

-- =====================================================
-- TABEL MASTER KARYAWAN
-- =====================================================
CREATE TABLE mEmployee (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Status TINYINT DEFAULT 1 COMMENT '0=tidak aktif, 1=aktif',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABEL MASTER USER
-- =====================================================
CREATE TABLE mUser (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UserName VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    EmployeeID INT,
    Access TINYINT DEFAULT 1 COMMENT '0=Admin, 1=Karyawan',
    Status TINYINT DEFAULT 1 COMMENT '0=tidak aktif, 1=aktif',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- TABEL MASTER INVENTARIS (14 TABEL)
-- =====================================================

CREATE TABLE mNotebook (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mProcessor (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mMotherboard (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mMemory (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mBaterry (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mHardisknb (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mLcdnb (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mNetwork (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mCam (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mAdaptor (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mHardiskext (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mFlashdisk (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mBook (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mOther (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255) NOT NULL,
    Notes TEXT,
    Status TINYINT DEFAULT 0 COMMENT '0=idle, 1=rent, 2=broken, 3=repair',
    RentID INT,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABEL TRANSAKSI
-- =====================================================

CREATE TABLE tRent (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    DocDate DATE NOT NULL,
    DocNumber VARCHAR(50) NOT NULL UNIQUE,
    ReffDocID INT,
    ReffDocNumber VARCHAR(50),
    EmployeeID INT,
    DocNotes TEXT,
    Void TINYINT DEFAULT 0 COMMENT '0=oke, 1=dibatalkan',
    Return TINYINT DEFAULT 0 COMMENT '0=belum kembali, 1=dikembalikan',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tRentTrans (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    DocID INT NOT NULL,
    DocDate DATE,
    DocNumber VARCHAR(50),
    ReffTransID INT,
    ReffDocNumber VARCHAR(50),
    ReturnDocDate DATE,
    ReturnDocID INT,
    ReturnDocNumber VARCHAR(50),
    Return TINYINT DEFAULT 0 COMMENT '0=belum kembali, 1=dikembalikan',
    EmployeeID INT,
    ItemRentID INT NOT NULL,
    ItemRentDesc VARCHAR(255),
    ItemQty INT DEFAULT 1,
    Void TINYINT DEFAULT 0 COMMENT '0=oke, 1=dibatalkan',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (DocID) REFERENCES tRent(ID) ON DELETE CASCADE,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tRentReturn (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    DocDate DATE NOT NULL,
    DocNumber VARCHAR(50) NOT NULL UNIQUE,
    ReffDocID INT,
    ReffDocNumber VARCHAR(50),
    EmployeeID INT,
    DocNotes TEXT,
    Void TINYINT DEFAULT 0 COMMENT '0=oke, 1=dibatalkan',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tRentReturnTrans (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    DocID INT NOT NULL,
    DocDate DATE,
    DocNumber VARCHAR(50),
    ReffTransID INT,
    ReffDocNumber VARCHAR(50),
    EmployeeID INT,
    ItemRentID INT NOT NULL,
    ItemRentDesc VARCHAR(255),
    ItemQty INT DEFAULT 1,
    Void TINYINT DEFAULT 0 COMMENT '0=oke, 1=dibatalkan',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (DocID) REFERENCES tRentReturn(ID) ON DELETE CASCADE,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tRequestRent (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    DocDate DATE NOT NULL,
    DocNumber VARCHAR(50) NOT NULL UNIQUE,
    EmployeeID INT,
    DocNotes TEXT,
    Void TINYINT DEFAULT 0 COMMENT '0=oke, 1=dibatalkan',
    Rent TINYINT DEFAULT 0 COMMENT '0=belum diproses, 1=diproses pinjaman',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tRequestRentTrans (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    DocID INT NOT NULL,
    DocDate DATE,
    DocNumber VARCHAR(50),
    RentDocDate DATE,
    RentDocID INT,
    RentDocNumber VARCHAR(50),
    Rent TINYINT DEFAULT 0 COMMENT '0=belum diproses, 1=diproses pinjaman',
    EmployeeID INT,
    ItemRentID INT NOT NULL,
    ItemRentDesc VARCHAR(255),
    ItemQty INT DEFAULT 1,
    Void TINYINT DEFAULT 0 COMMENT '0=oke, 1=dibatalkan',
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    EditDate DATETIME,
    EditBy VARCHAR(100),
    PostDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (DocID) REFERENCES tRequestRent(ID) ON DELETE CASCADE,
    FOREIGN KEY (EmployeeID) REFERENCES mEmployee(ID) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- INSERT DEFAULT DATA (PASSWORD HASH YANG BENAR)
-- =====================================================
INSERT INTO mEmployee (Code, Description, Status, CreatedBy) VALUES
('EMP001', 'Admin System', 1, 'System'),
('EMP002', 'Budi Santoso', 1, 'System'),
('EMP003', 'Ani Wijaya', 1, 'System');

-- Password: admin123
INSERT INTO mUser (UserName, Password, EmployeeID, Access, Status, CreatedBy) VALUES
('admin', '$2b$12$XAza5Z6jP1L54xFCXu.usOth3diKf7a0PtlpmcGL5N/qzuqfsuNNa', 1, 0, 1, 'System');

-- Password: user123
INSERT INTO mUser (UserName, Password, EmployeeID, Access, Status, CreatedBy) VALUES
('user1', '$2b$12$QoF0sl7edQJvCEqscGntoOtiuUhQGegsnCjJXDFiMrMOhngt0c78m', 2, 1, 1, 'System');