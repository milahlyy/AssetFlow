<?php include "config.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Pembelian - Kantin Mamah Dedeh</title>
    <style>
        :root {
            --bg: #f4f6f8;
            --card: #ffffff;
            --accent: #28a745;
            --accent-hover: #218838;
            --text: #222;
            --text-light: #666;
            --border: #e1e5e9;
            --shadow: rgba(18,38,63,0.08);
            --shadow-hover: rgba(18,38,63,0.15);
        }
        
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .box {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 10px 30px var(--shadow);
            padding: 30px;
            width: 90%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), #34ce57);
        }
        
        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px var(--shadow-hover);
        }
        
        h1 {
            color: var(--accent);
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        h2 {
            color: var(--text);
            margin: 15px 0;
            font-size: 20px;
        }
        
        h3 {
            color: var(--text);
            margin: 12px 0;
            font-size: 18px;
        }
        
        p {
            margin: 10px 0;
            color: var(--text);
        }
        
        .bonus-highlight {
            background-color: #e8f5e9;
            padding: 12px 15px;
            border-radius: 10px;
            border-left: 4px solid var(--accent);
            margin: 15px 0;
        }
        
        .price-highlight {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid var(--border);
        }
        
        .total-price {
            font-size: 22px;
            font-weight: bold;
            color: var(--accent);
        }
        
        a {
            display: inline-block;
            background: var(--accent);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(40,167,69,0.18);
        }
        
        a:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(40,167,69,0.25);
        }
        
        @media (max-width: 480px) {
            .box {
                padding: 20px;
                border-radius: 12px;
            }
            
            h1 {
                font-size: 22px;
            }
            
            h2 {
                font-size: 18px;
            }
            
            h3 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="box">

<?php
// Harga menu
$menu_harga = [
    'nasi' => 3000,
    'ikan_bakar' => 12000,
    'ayam_bakar' => 15000,
    'sayur_lodeh' => 10000,
    'tumis_kangkung' => 8000
];

$nama = $_POST['nama'];
$email = $_POST['email'];
$menu_dipilih = isset($_POST['menu']) ? $_POST['menu'] : [];
$total_harga = 0;

// Hitung total harga
foreach ($menu_dipilih as $menu) {
    $total_harga += $menu_harga[$menu];
}

// Tentukan bonus & diskon
$bonus = "-";
$diskon = 0;
$harga_setelah_diskon = $total_harga;

if ($total_harga >= 13000) {
    $bonus = "Jus Alpukat";
    $diskon = $total_harga * 0.10;
    $harga_setelah_diskon = $total_harga - $diskon;
} elseif ($total_harga >= 7000) {
    $bonus = "Teh Manis Dingin";
}

// Simpan ke database
$list_menu = implode(", ", $menu_dipilih);

$query = "INSERT INTO transaksi (nama, email, menu, total, bonus, diskon)
          VALUES ('$nama', '$email', '$list_menu', '$total_harga', '$bonus', '$diskon')";

mysqli_query($conn, $query);
?>

<h1>🍛 Terima Kasih, <?= $nama ?>!</h1>

<div class="price-highlight">
    <p>Total Harga:</p>
    <p class="total-price">Rp <?= number_format($total_harga) ?></p>
</div>

<h2>Detail Pesanan</h2>
<p><strong>Email:</strong> <?= $email ?></p>
<p><strong>Menu yang dipilih:</strong> <?= $list_menu ?></p>

<?php if ($bonus != "-") { ?>
    <div class="bonus-highlight">
        <h3>🎁 Bonus: <?= $bonus ?></h3>
    </div>
<?php } ?>

<?php if ($diskon > 0) { ?>
    <div class="price-highlight">
        <p>Diskon: Rp <?= number_format($diskon) ?></p>
        <p class="total-price">Harga Setelah Diskon: Rp <?= number_format($harga_setelah_diskon) ?></p>
    </div>
<?php } ?>

<h3>Selamat Menikmati Makanan Anda! 🍽️</h3>

<br>
<a href="kantin.php">Kembali ke Menu</a>

</div>

</body>
</html>