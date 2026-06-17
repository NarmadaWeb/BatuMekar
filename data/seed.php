<?php
require_once __DIR__ . '/../config/db.php';

try {
    // Clear existing data in correct order due to foreign keys
    $pdo->exec("DELETE FROM detail_pesanan");
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        $pdo->exec("DELETE FROM pembayaran");
    } else {
        // SQLite
        $pdo->exec("DELETE FROM pembayaran");
    }
    $pdo->exec("DELETE FROM notifikasi");
    $pdo->exec("DELETE FROM pesanan");
    $pdo->exec("DELETE FROM produk");
    $pdo->exec("DELETE FROM kategori");
    $pdo->exec("DELETE FROM pengguna");
    $pdo->exec("DELETE FROM artikel");
    $pdo->exec("DELETE FROM faq");

    // Hash passwords (BCRYPT is 60 characters, which fits into password VARCHAR(64))
    $admin_password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    $user_password = password_hash('user123', PASSWORD_BCRYPT, ['cost' => 12]);

    // Seed Pengguna
    $stmt = $pdo->prepare("INSERT INTO pengguna (nama, username, password, peran, telepon, alamat) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Admin
    $stmt->execute([
        'Admin BatuMekar',
        'admin',
        $admin_password,
        'admin',
        '+6281234567890',
        'Kantor Desa BatuMekar, Lombok Barat'
    ]);

    // Customer
    $stmt->execute([
        'I Gede Sukarsa',
        'gede',
        $user_password,
        'user',
        '+6287865432109',
        'Jl. Raya BatuMekar No. 42, Lombok Barat'
    ]);

    // Seed Produk
    $products = [
        [
            'nama' => 'Madu Multiflora',
            'deskripsi' => 'Madu harian kaya nutrisi dari nektar aneka bunga desa.',
            'harga' => 125000,
            'gambar' => 'assets/img/produk/multiflora.jpg',
            'kategori' => 'Multiflora',
            'rating' => 4.9,
            'jumlah_ulasan' => 120,
            'stok' => 100,
            'unggulan' => 1
        ],
        [
            'nama' => 'Madu Kaliandra',
            'deskripsi' => 'Memiliki aroma wangi yang khas dan tekstur lembut.',
            'harga' => 145000,
            'gambar' => 'assets/img/produk/kaliandra.jpg',
            'kategori' => 'Kaliandra',
            'rating' => 4.8,
            'jumlah_ulasan' => 85,
            'stok' => 100,
            'unggulan' => 1
        ],
        [
            'nama' => 'Madu Hutan Liar',
            'deskripsi' => 'Madu murni dari lebah liar di kedalaman hutan pegunungan.',
            'harga' => 160000,
            'gambar' => 'assets/img/produk/hutan.jpg',
            'kategori' => 'Hutan',
            'rating' => 5.0,
            'jumlah_ulasan' => 210,
            'stok' => 100,
            'unggulan' => 1
        ],
        [
            'nama' => 'Madu Kelengkeng',
            'deskripsi' => 'Nektar bunga kelengkeng memberikan rasa buah yang dominan.',
            'harga' => 135000,
            'gambar' => 'assets/img/produk/kelengkeng.jpg',
            'kategori' => 'Kelengkeng',
            'rating' => 4.7,
            'jumlah_ulasan' => 94,
            'stok' => 100,
            'unggulan' => 1
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO produk (nama, deskripsi, harga, gambar, kategori, rating, jumlah_ulasan, stok, unggulan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute([$p['nama'], $p['deskripsi'], $p['harga'], $p['gambar'], $p['kategori'], $p['rating'], $p['jumlah_ulasan'], $p['stok'], $p['unggulan']]);
    }

    // Seed Kategori
    $kategori_list = ['Multiflora', 'Kaliandra', 'Hutan', 'Kelengkeng', 'Rambutan'];
    $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
    foreach ($kategori_list as $k) {
        $stmt->execute([$k, 'Kategori ' . $k]);
    }

    // Seed Artikel
    $posts = [
        [
            'judul' => 'Panen Madu Hutan Lestari di BatuMekar',
            'kutipan' => 'Di tebing hijau desa kami, praktik pemanenan berkelanjutan dimulai saat fajar.',
            'konten' => 'Proses pemanenan madu dilakukan secara tradisional dengan menjaga kelestarian koloni lebah liar.',
            'kategori' => 'Panen',
            'penulis' => 'Admin',
            'gambar' => 'assets/img/blog/panen.jpg'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO artikel (judul, kutipan, konten, kategori, penulis, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($posts as $post) {
        $stmt->execute([$post['judul'], $post['kutipan'], $post['konten'], $post['kategori'], $post['penulis'], $post['gambar']]);
    }

    // Seed FAQ
    $faqs = [
        [
            'kategori' => 'Pengiriman',
            'pertanyaan' => 'Berapa lama waktu pengiriman?',
            'jawaban' => 'Pengiriman standar biasanya memakan waktu 3-5 hari kerja di wilayah Indonesia.'
        ],
        [
            'kategori' => 'Penyimpanan',
            'pertanyaan' => 'Madu saya mengkristal. Apakah masih aman dikonsumsi?',
            'jawaban' => 'Tentu saja! Kristalisasi adalah proses alami dan merupakan tanda madu murni tanpa campuran.'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO faq (kategori, pertanyaan, jawaban) VALUES (?, ?, ?)");
    foreach ($faqs as $faq) {
        $stmt->execute([$faq['kategori'], $faq['pertanyaan'], $faq['jawaban']]);
    }

    echo "Seeding completed successfully!\n";

} catch (Exception $e) {
    die("Seeding failed: " . $e->getMessage() . "\n");
}
?>
