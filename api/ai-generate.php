<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$apiKey = $data['apiKey'] ?? '';
$prompt = $data['prompt'] ?? '';

if (empty($apiKey) || empty($prompt)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $requestData = [
        'model' => 'text-davinci-003',
        'prompt' => $prompt,
        'max_tokens' => 2000,
        'temperature' => 0.7
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200) {
        throw new Exception('AI API request failed');
    }
    
    $result = json_decode($response, true);
    
    echo json_encode([
        'success' => true,
        'titleEn' => 'Generated Title EN',
        'titleRu' => 'Generated Title RU',
        'contentEn' => $result['choices'][0]['text'],
        'contentRu' => 'Generated Content RU'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>