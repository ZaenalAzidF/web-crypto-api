<?php

function getCryptoData(&$dataSource = null)
{
    $cacheFile = __DIR__ . '/cache.json';
    $cacheTime = 60; // Cache valid selama 60 detik

    // 1. Cek apakah file cache ada dan usianya di bawah 60 detik
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cachedData) && !empty($cachedData)) {
            if ($dataSource !== null) $dataSource = "Data Cache Lokal";
            return $cachedData;
        }
    }

    // 2. Request ke API CoinGecko
    $url = "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=10&page=1&sparkline=false";

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_errno($curl) ? curl_error($curl) : null;
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // 3. Penanganan Error & Rate Limit (HTTP 429 Too Many Requests)
    if ($err || $httpCode != 200) {
        // Coba gunakan data dari cache lokal yang pernah tersimpan sebelumnya
        if (file_exists($cacheFile)) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cachedData) && !empty($cachedData)) {
                if ($dataSource !== null) $dataSource = "Cache (API Limit 429)";
                return $cachedData;
            }
        }
        
        // Jika cache belum ada (baru pertama kali jalankan langsung kena 429), gunakan Fallback Snapshot Data
        if ($dataSource !== null) $dataSource = "Snapshot Cadangan (API Limit 429)";
        $fallbackData = getFallbackCryptoData();
        file_put_contents($cacheFile, json_encode($fallbackData, JSON_PRETTY_PRINT));
        return $fallbackData;
    }

    $data = json_decode($response, true);

    if (!$data || !is_array($data)) {
        if (file_exists($cacheFile)) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cachedData) && !empty($cachedData)) {
                if ($dataSource !== null) $dataSource = "Data Cache Lokal";
                return $cachedData;
            }
        }
        if ($dataSource !== null) $dataSource = "Snapshot Cadangan";
        $fallbackData = getFallbackCryptoData();
        return $fallbackData;
    }

    // Simpan hasil API sukses ke file cache
    if ($dataSource !== null) $dataSource = "Live CoinGecko API";
    file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));

    return $data;
}

// Data cadangan (Snapshot) apabila API CoinGecko mengalami limit (429) dan belum ada cache
function getFallbackCryptoData()
{
    return [
        [
            "id" => "bitcoin",
            "symbol" => "btc",
            "name" => "Bitcoin",
            "image" => "https://assets.coingecko.com/coins/images/1/large/bitcoin.png",
            "current_price" => 66450.00,
            "market_cap" => 1308500000000,
            "price_change_percentage_24h" => 2.45
        ],
        [
            "id" => "ethereum",
            "symbol" => "eth",
            "name" => "Ethereum",
            "image" => "https://assets.coingecko.com/coins/images/279/large/ethereum.png",
            "current_price" => 3520.50,
            "market_cap" => 422800000000,
            "price_change_percentage_24h" => 1.85
        ],
        [
            "id" => "tether",
            "symbol" => "usdt",
            "name" => "Tether",
            "image" => "https://assets.coingecko.com/coins/images/325/large/Tether.png",
            "current_price" => 1.00,
            "market_cap" => 112500000000,
            "price_change_percentage_24h" => 0.01
        ],
        [
            "id" => "binancecoin",
            "symbol" => "bnb",
            "name" => "BNB",
            "image" => "https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png",
            "current_price" => 585.20,
            "market_cap" => 89600000000,
            "price_change_percentage_24h" => -0.75
        ],
        [
            "id" => "solana",
            "symbol" => "sol",
            "name" => "Solana",
            "image" => "https://assets.coingecko.com/coins/images/4128/large/solana.png",
            "current_price" => 155.80,
            "market_cap" => 71500000000,
            "price_change_percentage_24h" => 5.20
        ],
        [
            "id" => "usd-coin",
            "symbol" => "usdc",
            "name" => "USDC",
            "image" => "https://assets.coingecko.com/coins/images/6319/large/USD_Coin_icon.png",
            "current_price" => 1.00,
            "market_cap" => 34200000000,
            "price_change_percentage_24h" => 0.00
        ],
        [
            "id" => "ripple",
            "symbol" => "xrp",
            "name" => "XRP",
            "image" => "https://assets.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png",
            "current_price" => 0.495,
            "market_cap" => 27500000000,
            "price_change_percentage_24h" => -1.10
        ],
        [
            "id" => "dogecoin",
            "symbol" => "doge",
            "name" => "Dogecoin",
            "image" => "https://assets.coingecko.com/coins/images/5/large/dogecoin.png",
            "current_price" => 0.125,
            "market_cap" => 18200000000,
            "price_change_percentage_24h" => 3.15
        ],
        [
            "id" => "the-open-network",
            "symbol" => "ton",
            "name" => "Toncoin",
            "image" => "https://assets.coingecko.com/coins/images/17980/large/ton_symbol.png",
            "current_price" => 7.40,
            "market_cap" => 17900000000,
            "price_change_percentage_24h" => 4.80
        ],
        [
            "id" => "cardano",
            "symbol" => "ada",
            "name" => "Cardano",
            "image" => "https://assets.coingecko.com/coins/images/975/large/cardano.png",
            "current_price" => 0.385,
            "market_cap" => 13800000000,
            "price_change_percentage_24h" => -2.30
        ]
    ];
}
?>