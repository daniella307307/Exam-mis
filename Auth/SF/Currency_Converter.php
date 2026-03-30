<?php
include('../../db.php');

$endpoint   = 'convert';
$access_key = 'f597a26a563422840e0ef7d77805bc72';
$from       = 'USD';
$amount     = 1;

// Get all active countries
$Select_countries = mysqli_query($conn, "SELECT * FROM countries WHERE Country_status = 'Active'");

while ($find_currencies = mysqli_fetch_assoc($Select_countries)) {
    $raw_to     = $find_currencies['Country_currency_code'] ?? '';
    $country_id = (int)$find_currencies['id'];

    // ✅ Sanitize and validate currency code
    $to = strtoupper(trim(preg_replace('/[^A-Za-z]/', '', $raw_to)));
    if (!preg_match('/^[A-Z]{3}$/', $to)) {
        echo "⚠️ Skipping invalid code '{$raw_to}' (Cleaned: '{$to}') for Country ID: {$country_id}<br>";
        continue;
    }

    // ✅ Build proper URL (keeping your endpoint)
    $url = "https://api.exchangerate.host/{$endpoint}?access_key={$access_key}&from={$from}&to={$to}&amount={$amount}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $json = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        echo "❌ CURL Error fetching rate for {$to} (Country ID: {$country_id}): {$curlErr}<br>";
        continue;
    }

    $conversionResult = json_decode($json, true);

    // ✅ Validate API response
    if (isset($conversionResult['result']) && is_numeric($conversionResult['result'])) {
        $currency_usd = (float)$conversionResult['result'];

        // ✅ Update database safely
        $stmt = mysqli_prepare($conn, "UPDATE countries SET currency_usd = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "di", $currency_usd, $country_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "✅ Updated {$to}: 1 {$from} = {$currency_usd} {$to} (Country ID: {$country_id})<br>";
    } else {
        $error_info = isset($conversionResult['error'])
            ? json_encode($conversionResult['error'])
            : (is_string($json) ? htmlspecialchars(substr($json, 0, 200)) : 'Unknown error');

        echo "❌ Failed to fetch rate for {$to} (Country ID: {$country_id})<br>Error: {$error_info}<br>";
    }

    // Sleep 0.25s between requests
    usleep(250000);
}
?>
