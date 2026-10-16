<?php
session_start();
header('Content-Type: application/json');

// Read input ONCE at the top
$rawInput = file_get_contents('php://input');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'membre') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!file_exists(__DIR__ . '/../config/config.php')) {
    echo json_encode(['error' => 'Config not found']);
    exit;
}
require_once __DIR__ . '/../config/config.php';
$input   = json_decode($rawInput, true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

$knowledgeBase = file_get_contents(__DIR__ . '/knowledge_base.txt');

$prompt = "
You are a helpful assistant for ReservApp, a room reservation system.
Answer the user's question using ONLY the information provided below.
If the answer is not in the provided information, say you don't know.
Respond in the same language the user used (French or English).
Keep your answer concise and friendly.

--- ReservApp Information ---
$knowledgeBase
--- End of Information ---

User question: $message
";

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=$apiKey";

$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);

$data   = json_decode($response, true);
$answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response.';

echo json_encode(['answer' => $answer]);