<?php
require_once __DIR__ . '/../config/db.php';

// Clear existing data
$pdo->exec("DELETE FROM products");
$pdo->exec("DELETE FROM blog_posts");
$pdo->exec("DELETE FROM faqs");
$pdo->exec("DELETE FROM users");

// Seed Products
$products = [
    [
        'name' => 'Madu Multiflora',
        'description' => 'Madu harian kaya nutrisi dari nektar aneka bunga desa.',
        'price' => 125000,
        'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA4I6nFTYqxXo45c8u1CEmaBYpN4Hv3u2vJm7WPjdPVMZmm8eYyquy_lIZ67tqGm9y0Mou_isVlSd59l0EjFHpnyEug93zuG0KWbdtQwjvUkqpWgLDsHIyfSSwoGso24jVe9C5t9AqrpROKZBSpM8HKOsx8DmxvDJHpWiHf2i-dsxmgp5b2aZudz9gelQFmowm87HQS_M0JDBVIRhQ_eg17Y85DVKLBNI-P2BfAvBYtco5ljHOWo5uhqS9XWaDO8cFx5cUiQihoeiU',
        'category' => 'Multiflora',
        'rating' => 4.9,
        'review_count' => 120,
        'is_featured' => 1
    ],
    [
        'name' => 'Madu Kaliandra',
        'description' => 'Memiliki aroma wangi yang khas dan tekstur lembut.',
        'price' => 145000,
        'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAn_c5tzBD3qX5lz2CcF4QTQOIRPgzRo6Im3bCzd-z8VDakpZO7vFKrD_aR80iR1B_fPSU64ywGfSNCgp37-by73MSSGpPuldlABQEJzX0E14ra2aOlIj7yPp83TEgSwrQEDLF1MqmXUA9RGafoMYeDv_2-R6YhPcehQPjZI3c0PHCgnziaZq6yJgdwbQN9cduFFzmz4kpGKSIIraWBWBVRTmq4uh5t1dKb-Pczn4bBVh0_fWU4fm1ru3NjLXo6bVVFdZCYoeBNh68',
        'category' => 'Kaliandra',
        'rating' => 4.8,
        'review_count' => 85,
        'is_featured' => 1
    ],
    [
        'name' => 'Madu Hutan Liar',
        'description' => 'Madu murni dari lebah liar di kedalaman hutan pegunungan.',
        'price' => 160000,
        'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuACZMWbOxoSjb-u7hs1OJSs0hVRjEAyJuU9gYsjpUI_s-yFbXrcFz75GauF15vllRlWMS9gLfELeXgyYw9a2f53Rb6ppCi6u6NGlG7N45-zQyvhEX3VRUxMDuoIQl9EFrNkqTPSv9xyt4QFm9xMrDRGxJhm6aRAjmylcaJdwBJ6M1mCy3VotkuxX0AvYUxAe0Bps4lfGw5n5vIf9pJ0p_wMoI5Qxm5y2e7Neiw6oEIQeCoZaq_xeTQVD7ufyu5DXj3X2RbS1_zL10U',
        'category' => 'Hutan',
        'rating' => 5.0,
        'review_count' => 210,
        'is_featured' => 1
    ],
    [
        'name' => 'Madu Kelengkeng',
        'description' => 'Nektar bunga kelengkeng memberikan rasa buah yang dominan.',
        'price' => 135000,
        'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBLvHoGVkV7QUJbmWhzCFDApD_X7r0YbbSt6UfenYXzrtGAcAPKM5zjEsq1kVp4IqjaESMN8abHI9sOT-uoHqV7GxlWLhDoqh3fneCMleTlqBspOU9s14ogdvA94EyczllS6gdy3BzW0IhBP4EBSYiZEFMjMssbcKSQwg6GHtQLSI9q3zrmOP9ao3VK9C_7eRPjXn-cdTdV65Y4v3RYeAu20b-9kgY8n18l91RZfilmdNmPrSbzo3xvEiI7hHZmtIJiZzn7x-w5tsY',
        'category' => 'Kelengkeng',
        'rating' => 4.7,
        'review_count' => 94,
        'is_featured' => 1
    ]
];

$stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category, rating, review_count, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($products as $p) {
    $stmt->execute([$p['name'], $p['description'], $p['price'], $p['image'], $p['category'], $p['rating'], $p['review_count'], $p['is_featured']]);
}

// Seed Blog Posts
$posts = [
    [
        'title' => 'The Sacred Morning: Harvesting Wild Honey in Batu Meka',
        'excerpt' => 'Deep in the verdant cliffs of our village, the ancient practice of sustainable harvesting begins at dawn.',
        'category' => 'Harvesting',
        'author' => 'Admin',
        'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBKqpfafedmr7W0662aojVMgKjm2Qev-NYdlPdrXqhWl9Epdj_vQBhTbsRz4BwGGeOkMKbN1xFOS6HL6cbQsXsjo_oymbGDdPx7fiTW4c99JSoz9rHJ3pHKZRtSnXRVPUq9-kSbqFlboRdrm_7GsBVV93-JW9ce2d7eb7ltZdif3-Zpeg-xlfTRjCnz44Ivz2e_TLRLcs4GyFkwf74PyearqGJRtsqeok8tXz9l_A4rtPj3kujyc8Mq4EIeuseHRuiNerqZ0WdS5qI'
    ]
];

$stmt = $pdo->prepare("INSERT INTO blog_posts (title, excerpt, category, author, image) VALUES (?, ?, ?, ?, ?)");
foreach ($posts as $post) {
    $stmt->execute([$post['title'], $post['excerpt'], $post['category'], $post['author'], $post['image']]);
}

// Seed FAQs
$faqs = [
    [
        'category' => 'Shipping',
        'question' => 'How long does shipping take?',
        'answer' => 'Standard shipping usually takes 3-5 business days within the region.'
    ],
    [
        'category' => 'Storage',
        'question' => 'My honey has crystallized. Is it still safe to eat?',
        'answer' => 'Absolutely! Crystallization is a natural process and a sign of pure, raw honey.'
    ]
];

$stmt = $pdo->prepare("INSERT INTO faqs (category, question, answer) VALUES (?, ?, ?)");
foreach ($faqs as $faq) {
    $stmt->execute([$faq['category'], $faq['question'], $faq['answer']]);
}

// Seed a Default User
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address, points, membership) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['Wayan', 'wayan@example.com', password_hash('password', PASSWORD_DEFAULT), 'user', '+62 812 3456 789', 'Batu Meka Village, Bali', 1240, 'Silver Member']);

echo "Database seeded successfully!\n";
?>
