<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
    echo json_encode(["success" => false, "data" => []]);
    exit;
}

$apiKey = "17d92b2aea934ef1b50fbd7c0390a5ae";
$url = "https://api.aimlapi.com/v1/chat/completions";

$data = [
    "model" => "google/gemini-2.5-flash",
    "messages" => [["role"=>"user","content"=>$prompt]],
    "max_tokens"=>1500,
    "temperature"=>0.7
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// Extract AI message
$aiText = $result['choices'][0]['message']['content'] ?? "";

// Remove code fences or markdown
$aiText = preg_replace('/```json|```/i', '', $aiText);
$aiText = trim($aiText);

// Try to decode JSON array
$questions = json_decode($aiText, true);

// Ensure it's an array for JS
if (!is_array($questions)) {
    $questions = [];
}

echo json_encode([
    "success" => true,
    "data" => $questions
]);
?>