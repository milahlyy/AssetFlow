<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kantin Mamah Dedeh</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
            --checkbox-bg: #f8f9fa;
            --checkbox-checked: #e8f5e9;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            line-height: 1.6;
        }

        /* center the page both vertically and horizontally */
        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .card {
            width: 400px;
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 10px 30px var(--shadow);
            padding: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), #34ce57);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px var(--shadow-hover);
        }

        h2 {
            margin: 0 0 20px 0;
            font-size: 22px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h2::before {
            content: '🍛';
            font-size: 24px;
        }

        .menu-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            font-size: 15px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .menu-list li {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid var(--border);
        }
        
        .menu-list li:last-child {
            border-bottom: none;
        }
        
        .menu-list li:hover {
            background-color: #f8f9fa;
        }
        
        .menu-list input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent);
        }
        
        .menu-list label {
            cursor: pointer;
            flex: 1;
            display: flex;
            justify-content: space-between;
        }
        
        .menu-list .price {
            color: var(--accent);
            font-weight: 600;
        }

        label.input-label {
            display: block;
            margin-top: 20px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-sizing: border-box;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #fafbfc;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            background-color: white;
        }

        .actions {
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }

        button.btn {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 14px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 6px 18px rgba(40,167,69,0.18);
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        button.btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        button.btn:hover { 
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40,167,69,0.25);
            background: var(--accent-hover);
        }
        
        button.btn:active::after {
            animation: ripple 1s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        .small {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 15px;
            text-align: center;
            line-height: 1.5;
        }

        /* responsive */
        @media (max-width: 480px) {
            .card { 
                width: 100%; 
                padding: 20px; 
                border-radius: 12px; 
            }
            
            h2 {
                font-size: 20px;
            }
            
            .menu-list li {
                padding: 10px 12px;
            }
        }
        
        @media (max-width: 360px) {
            .card { 
                padding: 15px; 
            }
            
            h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h2>Daftar Menu - Kantin Mamah Dedeh</h2>

        <form method="POST" action="proses_kantin.php">
            <ul class="menu-list">
                <li>
                    <input type="checkbox" id="m_nasi" name="menu[]" value="nasi"> 
                    <label for="m_nasi">
                        <span>Nasi</span>
                        <span class="price">Rp 3.000</span>
                    </label>
                </li>
                <li>
                    <input type="checkbox" id="m_ikan" name="menu[]" value="ikan_bakar"> 
                    <label for="m_ikan">
                        <span>Ikan Bakar</span>
                        <span class="price">Rp 12.000</span>
                    </label>
                </li>
                <li>
                    <input type="checkbox" id="m_ayam" name="menu[]" value="ayam_bakar"> 
                    <label for="m_ayam">
                        <span>Ayam Bakar</span>
                        <span class="price">Rp 15.000</span>
                    </label>
                </li>
                <li>
                    <input type="checkbox" id="m_sayur" name="menu[]" value="sayur_lodeh"> 
                    <label for="m_sayur">
                        <span>Sayur Lodeh</span>
                        <span class="price">Rp 10.000</span>
                    </label>
                </li>
                <li>
                    <input type="checkbox" id="m_tumis" name="menu[]" value="tumis_kangkung"> 
                    <label for="m_tumis">
                        <span>Tumis Kangkung</span>
                        <span class="price">Rp 8.000</span>
                    </label>
                </li>
            </ul>

            <label class="input-label">Nama Anda *</label>
            <input type="text" name="nama" required placeholder="Masukkan nama lengkap">

            <label class="input-label">Email Anda *</label>
            <input type="text" name="email" required placeholder="Masukkan alamat email">

            <div class="actions">
                <button type="submit" class="btn">Bayar Sekarang</button>
            </div>

            <div class="small">* Nama & Email wajib diisi<br>Pesanan akan diproses setelah pembayaran</div>
        </form>
    </div>
</div>
</body>
</html>