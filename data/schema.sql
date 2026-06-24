SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifikasi;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS faq;
DROP TABLE IF EXISTS artikel;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS pengguna;
DROP TABLE IF EXISTS ukuran_produk;
DROP TABLE IF EXISTS ulasan_produk;
DROP TABLE IF EXISTS pengembalian_pesanan;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE pengguna (
    pengguna_id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(35) NOT NULL,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(64) NOT NULL,
    peran VARCHAR(10) DEFAULT 'user',
    telepon VARCHAR(15) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    foto_profil VARCHAR(50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk (
    produk_id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(35) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    gambar VARCHAR(50) DEFAULT NULL,
    kategori VARCHAR(30) DEFAULT NULL,
    rating DECIMAL(3, 1) DEFAULT 0.0,
    jumlah_ulasan INT DEFAULT 0,
    stok INT DEFAULT 100,
    unggulan TINYINT DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ukuran_produk (
    ukuran_id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT NOT NULL,
    ukuran_ml INT NOT NULL,
    harga DECIMAL(10, 2) DEFAULT NULL,
    stok INT DEFAULT 0,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
);

CREATE TABLE pesanan (
    pesanan_id INT AUTO_INCREMENT PRIMARY KEY,
    pengguna_id INT NOT NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    metode_pengiriman VARCHAR(20) DEFAULT 'Standard',
    metode_pembayaran VARCHAR(20) NOT NULL,
    alamat_pengiriman TEXT NOT NULL,
    bukti_pembayaran VARCHAR(50) DEFAULT NULL,
    snap_token TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE detail_pesanan (
    detail_pesanan_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_id INT NOT NULL,
    ukuran_id INT DEFAULT NULL,
    jumlah INT NOT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE,
    FOREIGN KEY (ukuran_id) REFERENCES ukuran_produk(ukuran_id) ON DELETE SET NULL
);

CREATE TABLE ulasan_produk (
    ulasan_id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT NOT NULL,
    pengguna_id INT NOT NULL,
    rating INT NOT NULL,
    ulasan TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE pengembalian_pesanan (
    pengembalian_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    pengguna_id INT NOT NULL,
    bukti_file VARCHAR(255) NOT NULL,
    alasan TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE artikel (
    artikel_id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    kutipan VARCHAR(255) DEFAULT NULL,
    konten TEXT DEFAULT NULL,
    kategori VARCHAR(30) DEFAULT NULL,
    penulis VARCHAR(35) DEFAULT NULL,
    gambar VARCHAR(50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE faq (
    faq_id INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(30) DEFAULT NULL,
    pertanyaan VARCHAR(100) NOT NULL,
    jawaban TEXT NOT NULL
);

CREATE TABLE pembayaran (
    pembayaran_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    pengguna_id INT NOT NULL,
    transaksi_id VARCHAR(50) NOT NULL,
    tanggal_pembayaran TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE kategori (
    kategori_id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(30) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifikasi (
    notifikasi_id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT DEFAULT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT DEFAULT NULL,
    dibaca TINYINT DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE SET NULL
);
