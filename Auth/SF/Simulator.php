 
<?PHP
// set API Endpoint, access key, required parameters
$endpoint = 'convert';
$access_key = 'f597a26a563422840e0ef7d77805bc72';

$from = 'USD';
$to = 'XAF';
$amount = 1;
// initialize CURL:
$ch = curl_init('https://api.exchangerate.host/'.$endpoint.'?access_key='.$access_key.'&from='.$from.'&to='.$to.'&amount='.$amount.'');   
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// get the (still encoded) JSON data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$conversionResult = json_decode($json, true);

// access the conversion result
echo $conversionResult['result'];
?>