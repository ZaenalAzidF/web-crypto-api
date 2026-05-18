<?php
require_once "api/get_crypto.php";

$error = "";
$cryptoData = [];
$dataSource = "Live CoinGecko API";

try {
    $cryptoData = getCryptoData($dataSource);
} catch (Exception $e) {
    $error = $e->getMessage();
}

$totalCoin = count($cryptoData);

// Hitung statistik tren pasar dari top koin
$bullish = 0;
$bearish = 0;
if (!empty($cryptoData)) {
    foreach ($cryptoData as $coin) {
        if (isset($coin['price_change_percentage_24h'])) {
            if ($coin['price_change_percentage_24h'] >= 0) {
                $bullish++;
            } else {
                $bearish++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoPulse - Live Cryptocurrency Dashboard</title>

    <!-- AUTO REFRESH 30 DETIK -->
    <meta http-equiv="refresh" content="30">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Ambient Glowing Background Orbs -->
<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<div class="container">

    <!-- Header -->
    <header>
        <div class="header-title">
            <div class="logo-icon">
                <i class="fa-solid fa-cube"></i>
            </div>
            <div>
                <h1>CryptoPulse</h1>
                <div class="subtitle">
                    <span>Sumber: <strong style="color: var(--accent-glow);"><?= htmlspecialchars($dataSource); ?></strong></span>
                    <span class="live-indicator" style="<?= strpos($dataSource, 'Limit 429') !== false ? 'background: rgba(244, 63, 94, 0.15); border-color: rgba(244, 63, 94, 0.3); color: var(--rose);' : ''; ?>">
                        <span class="pulse-dot" style="<?= strpos($dataSource, 'Limit 429') !== false ? 'background-color: var(--rose); box-shadow: 0 0 10px var(--rose);' : ''; ?>"></span> 
                        <?= strpos($dataSource, 'Limit 429') !== false ? 'Mode Offline / Throttled' : 'Live (30s)'; ?>
                    </span>
                </div>
            </div>
        </div>
        <button onclick="location.reload()" class="btn-refresh" title="Segarkan Data Secara Manual">
            <i class="fa-solid fa-rotate-right"></i> Segarkan Data
        </button>
    </header>

    <!-- Error Banner -->
    <?php if ($error != "") : ?>
        <div class="error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Perhatian:</strong> <?= htmlspecialchars($error); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Summary Cards -->
    <div class="top-card">
        <div class="card card-1">
            <div class="card-content">
                <h3>Total Koin Teratas</h3>
                <p><?= $totalCoin ?> Aset</p>
            </div>
            <div class="card-icon">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="card card-2">
            <div class="card-content">
                <h3>Sentimen Pasar (24h)</h3>
                <p><?= $bullish ?> Naik / <?= $bearish ?> Turun</p>
            </div>
            <div class="card-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <div class="card card-3">
            <div class="card-content">
                <h3>Pembaruan Terakhir</h3>
                <p><?= date("H:i:s") ?></p>
            </div>
            <div class="card-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Search / Controls Bar -->
    <div class="controls-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari koin berdasarkan nama atau simbol...">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
    </div>

    <!-- Table Content -->
    <?php if (!empty($cryptoData)) : ?>
        <div class="table-wrapper">
            <table id="cryptoTable">
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">#</th>
                        <th>Aset Kripto</th>
                        <th style="text-align: right;">Harga (USD)</th>
                        <th style="text-align: right;" class="mkt-cap-col">Kapitalisasi Pasar</th>
                        <th style="text-align: center; width: 180px;">Perubahan (24 Jam)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($cryptoData as $coin) : ?>
                        <?php 
                            $isUp = ($coin['price_change_percentage_24h'] >= 0); 
                        ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-secondary); font-weight: 700;">
                                <?= $no++; ?>
                            </td>

                            <td>
                                <div class="coin-col">
                                    <img src="<?= htmlspecialchars($coin['image']); ?>" alt="<?= htmlspecialchars($coin['name']); ?>" class="coin-logo">
                                    <div>
                                        <span class="coin-name"><?= htmlspecialchars($coin['name']); ?></span>
                                        <span class="coin-symbol"><?= htmlspecialchars($coin['symbol']); ?></span>
                                    </div>
                                </div>
                            </td>

                            <td style="text-align: right;" class="price">
                                $<?= number_format($coin['current_price'], 2) ?>
                            </td>

                            <td style="text-align: right;" class="mkt-cap mkt-cap-col">
                                $<?= number_format($coin['market_cap']) ?>
                            </td>

                            <td style="text-align: center;">
                                <span class="badge-change <?= $isUp ? 'up' : 'down'; ?>">
                                    <i class="fa-solid <?= $isUp ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>"></i>
                                    <?= number_format($coin['price_change_percentage_24h'], 2) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div id="noResult" class="no-results" style="display: none;">
                <i class="fa-solid fa-box-open"></i>
                <p>Tidak ada aset mata uang kripto yang cocok dengan pencarian Anda.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const noResult = document.getElementById("noResult");
    const table = document.getElementById("cryptoTable");

    if (searchInput && table) {
        searchInput.addEventListener("keyup", function() {
            const filter = searchInput.value.toLowerCase();
            const rows = table.querySelectorAll("tbody tr");
            let hasVisibleRow = false;

            rows.forEach(row => {
                const coinName = row.querySelector(".coin-name").textContent.toLowerCase();
                const coinSymbol = row.querySelector(".coin-symbol").textContent.toLowerCase();

                if (coinName.includes(filter) || coinSymbol.includes(filter)) {
                    row.style.display = "";
                    hasVisibleRow = true;
                } else {
                    row.style.display = "none";
                }
            });

            // Show or hide empty state
            if (noResult) {
                noResult.style.display = hasVisibleRow ? "none" : "block";
                table.querySelector("thead").style.display = hasVisibleRow ? "" : "none";
            }
        });
    }
});
</script>

</body>
</html>