DROP TABLE IF EXISTS notifikasi;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS faq;
DROP TABLE IF EXISTS artikel;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS pengguna;

CREATE TABLE pengguna (
    pengguna_id INTEGER PRIMARY KEY AUTOINCREMENT,
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
    produk_id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama VARCHAR(35) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    harga REAL NOT NULL,
    gambar VARCHAR(50) DEFAULT NULL,
    kategori VARCHAR(30) DEFAULT NULL,
    rating REAL DEFAULT 0.0,
    jumlah_ulasan INTEGER DEFAULT 0,
    stok INTEGER DEFAULT 100,
    unggulan INTEGER DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pesanan (
    pesanan_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pengguna_id INTEGER NOT NULL,
    total_harga REAL NOT NULL,
    status VARCHAR(15) DEFAULT 'Pending',
    metode_pengiriman VARCHAR(20) DEFAULT 'Standard',
    metode_pembayaran VARCHAR(20) NOT NULL,
    alamat_pengiriman TEXT NOT NULL,
    bukti_pembayaran VARCHAR(50) DEFAULT NULL,
    snap_token TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE detail_pesanan (
    detail_pesanan_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pesanan_id INTEGER NOT NULL,
    produk_id INTEGER NOT NULL,
    ukuran_id INTEGER DEFAULT NULL,
    jumlah INTEGER NOT NULL,
    harga REAL NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE,
    FOREIGN KEY (ukuran_id) REFERENCES ukuran_produk(ukuran_id) ON DELETE SET NULL
);

CREATE TABLE ukuran_produk (
    ukuran_id INTEGER PRIMARY KEY AUTOINCREMENT,
    produk_id INTEGER NOT NULL,
    ukuran_ml INTEGER NOT NULL,
    harga REAL DEFAULT NULL,
    stok INTEGER DEFAULT 0,
    FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
);

CREATE TABLE artikel (
    artikel_id INTEGER PRIMARY KEY AUTOINCREMENT,
    judul VARCHAR(100) NOT NULL,
    kutipan VARCHAR(255) DEFAULT NULL,
    konten TEXT DEFAULT NULL,
    kategori VARCHAR(30) DEFAULT NULL,
    penulis VARCHAR(35) DEFAULT NULL,
    gambar VARCHAR(50) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE faq (
    faq_id INTEGER PRIMARY KEY AUTOINCREMENT,
    kategori VARCHAR(30) DEFAULT NULL,
    pertanyaan VARCHAR(100) NOT NULL,
    jawaban TEXT NOT NULL
);

CREATE TABLE pembayaran (
    pembayaran_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pesanan_id INTEGER NOT NULL,
    pengguna_id INTEGER NOT NULL,
    transaksi_id VARCHAR(50) NOT NULL,
    tanggal_pembayaran TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
    FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
);

CREATE TABLE kategori (
    kategori_id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama_kategori VARCHAR(30) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifikasi (
    notifikasi_id INTEGER PRIMARY KEY AUTOINCREMENT,
    pesanan_id INTEGER DEFAULT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT DEFAULT NULL,
    dibaca INTEGER DEFAULT 0,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE SET NULL
);
