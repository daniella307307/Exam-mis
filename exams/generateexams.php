<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
    echo json_encode(["success" => false, "error" => "No prompt provided", "data" => []]);
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
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$ai_failed = false;

if ($error) {
    $ai_failed = true;
    $error_msg = "API Connection Error: $error";
} elseif ($httpCode !== 200) {
    $ai_failed = true;
    $error_msg = "API Error (HTTP $httpCode): " . substr($response, 0, 200);
}

// If AI API fails, use FALLBACK SYSTEM to generate basic questions
if ($ai_failed) {
    error_log("AI API failed: $error_msg - Using fallback system");
    
    // Extract info from prompt to generate fallback questions
    $questions = generateFallbackQuestions($prompt);
    
    echo json_encode([
        "success" => true,
        "data" => $questions,
        "warning" => "Using fallback question generator (API unavailable)"
    ]);
    exit;
}

$result = json_decode($response, true);

// Extract AI message
$aiText = $result['choices'][0]['message']['content'] ?? "";

if (empty($aiText)) {
    // Fallback if AI returns empty
    error_log("AI returned empty response");
    $questions = generateFallbackQuestions($prompt);
    
    echo json_encode([
        "success" => true,
        "data" => $questions,
        "warning" => "Using fallback questions (AI returned empty)"
    ]);
    exit;
}

// Remove code fences or markdown
$aiText = preg_replace('/```json|```/i', '', $aiText);
$aiText = trim($aiText);

// Try to decode JSON array
$questions = json_decode($aiText, true);

// Ensure it's an array for JS
if (!is_array($questions)) {
    error_log("Failed to parse AI response as JSON");
    $questions = generateFallbackQuestions($prompt);
    
    echo json_encode([
        "success" => true,
        "data" => $questions,
        "warning" => "Using fallback questions (JSON parsing failed)"
    ]);
    exit;
}

// Process questions to clean up option formatting
$processed_questions = [];
foreach ($questions as $q) {
    if (is_array($q) && isset($q['options'])) {
        // Clean option text - remove "A. ", "B. ", "C. ", "D. " prefixes if they exist
        $cleaned_options = [];
        foreach ($q['options'] as $option) {
            // Remove "A. ", "B. ", "C. ", "D. " prefixes
            $cleaned = preg_replace('/^[A-D]\.\s*/', '', (string)$option);
            $cleaned_options[] = $cleaned;
        }
        $q['options'] = $cleaned_options;
    }
    $processed_questions[] = $q;
}

echo json_encode([
    "success" => true,
    "data" => $processed_questions
]);

// ============================================================================
// FALLBACK QUESTION GENERATOR - Used when AI API is unavailable
// ============================================================================
function generateFallbackQuestions($prompt) {
    // Extract number of questions from prompt
    preg_match('/exactly\s+(\d+)\s+quiz\s+questions/i', $prompt, $matches);
    $numQ = isset($matches[1]) ? (int)$matches[1] : 10;
    
    // Extract topic - try multiple patterns
    $topic = "General Knowledge";
    if (preg_match('/Subject\/Topic:\s*([^\n]+)/i', $prompt, $matches)) {
        $topic = trim($matches[1]);
    } elseif (preg_match('/Topic\/Subject:\s*([^\n]+)/i', $prompt, $matches)) {
        $topic = trim($matches[1]);
    } elseif (preg_match('/Topic:\s*([^\n]+)/i', $prompt, $matches)) {
        $topic = trim($matches[1]);
    }
    
    // Extract grade level
    $grade = "Grade 10";
    if (preg_match('/Grade Level:\s*([^\n]+)/i', $prompt, $matches)) {
        $grade = trim($matches[1]);
    } elseif (preg_match('/for:\s*(Grade\s*\d+|Beginner|Intermediate|Advanced)/i', $prompt, $matches)) {
        $grade = trim($matches[1]);
    }
    
    // Extract difficulty
    preg_match('/Difficulty[^:]*:\s*([A-Z][A-Za-z]+)/i', $prompt, $matches);
    $difficulty = isset($matches[1]) ? strtolower(trim($matches[1])) : "medium";
    
    // Adjust points based on difficulty
    $points_map = [
        'easy' => 5,
        'medium' => 10,
        'hard' => 15
    ];
    $points = $points_map[$difficulty] ?? 10;
    
    // Question templates with correct format (text, type, options, correctAnswer)
    $templates = [
        // Multiple choice questions
        [
            "text" => "What is the primary focus of $topic?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Understanding fundamentals", "Advanced techniques", "Historical context", "Practical application"],
            "correctAnswer" => 0
        ],
        [
            "text" => "Which of the following best describes $topic?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["A core principle", "A supplementary concept", "An outdated idea", "A theoretical framework"],
            "correctAnswer" => 0
        ],
        [
            "text" => "In the context of $topic, what is most important?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Accuracy and precision", "Speed of execution", "Cost efficiency", "User experience"],
            "correctAnswer" => 0
        ],
        [
            "text" => "Which statement about $topic is correct?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["It is widely accepted", "It is controversial", "It is emerging", "It is declining"],
            "correctAnswer" => rand(0, 3)
        ],
        [
            "text" => "What is an example of $topic?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["First example", "Second example", "Third example", "Fourth example"],
            "correctAnswer" => rand(0, 3)
        ],
        [
            "text" => "How does $topic relate to modern practices?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Directly relevant", "Somewhat relevant", "Rarely used", "Obsolete"],
            "correctAnswer" => rand(0, 1)
        ],
        [
            "text" => "Which of these is NOT related to $topic?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Related concept A", "Related concept B", "Related concept C", "Unrelated concept"],
            "correctAnswer" => 3
        ],
        [
            "text" => "According to $topic principles, what should be prioritized?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Quality", "Efficiency", "Innovation", "Cost"],
            "correctAnswer" => rand(0, 3)
        ],
        [
            "text" => "What is a common misconception about $topic?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Misconception 1", "Misconception 2", "Misconception 3", "Reality"],
            "correctAnswer" => 3
        ],
        [
            "text" => "Which factor is most critical in $topic?",
            "type" => "multiple",
            "points" => $points,
            "options" => ["Experience", "Knowledge", "Tools", "Resources"],
            "correctAnswer" => rand(0, 3)
        ],
        // True/False questions
        [
            "text" => "$topic is a well-established and widely accepted field.",
            "type" => "true-false",
            "points" => ($points / 2),
            "correctAnswer" => true
        ],
        [
            "text" => "$topic is considered an emerging discipline.",
            "type" => "true-false",
            "points" => ($points / 2),
            "correctAnswer" => false
        ],
        [
            "text" => "$topic is obsolete in modern practice.",
            "type" => "true-false",
            "points" => ($points / 2),
            "correctAnswer" => false
        ],
        [
            "text" => "Understanding $topic is essential for professionals.",
            "type" => "true-false",
            "points" => ($points / 2),
            "correctAnswer" => true
        ],
        // Short answer questions
        [
            "text" => "Name a key principle of $topic.",
            "type" => "short-answer",
            "points" => $points,
            "correctAnswer" => "See instructor notes"
        ],
        [
            "text" => "Provide an example of $topic in practice.",
            "type" => "short-answer",
            "points" => $points,
            "correctAnswer" => "Contextual example"
        ],
        [
            "text" => "Explain the importance of $topic.",
            "type" => "short-answer",
            "points" => $points,
            "correctAnswer" => "See instructor notes"
        ]
    ];
    
    // Shuffle and select the required number of questions
    shuffle($templates);
    $questions = [];
    for ($i = 0; $i < min($numQ, count($templates)); $i++) {
        $questions[] = $templates[$i];
    }
    
    return $questions;
}
?>