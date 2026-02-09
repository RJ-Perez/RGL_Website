<?php
/**
 * Chatbot API Handler using Google Gemini
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================
// GEMINI API CONFIGURATION
// ============================================
$gemini_api_key = 'AIzaSyDdnNWUOIfdsOxF4tWy9GzbarCXGfRaxm8';
$gemini_model = 'gemini-2.0-flash';

// ============================================
// SYSTEM CONTEXT - Company Information
// ============================================
$system_context = "You are RGL Assistant, a helpful AI chatbot for RGL Business Solutions. You help visitors learn about the company and its services.

ABOUT RGL BUSINESS SOLUTIONS:
RGL Business Solutions is a technology consulting company that bridges the gap between technology and business. The approach is grounded in partnership, clarity, and delivery excellence.

CORE SERVICES:
1. Strategic IT Consulting - Strategic Consulting, AI & Digital Transformation, Process Optimization
2. IT Outsourcing - Managed IT Services, Cloud & Cybersecurity, Operational Continuity
3. IT Training & Talent Network - Workforce Development, IT Referral & Talent Network, Custom Onboarding

LEADERSHIP TEAM:
- Ryan John Perez, Co-Founder - Email: ryanjohn.perez@rgl.com.ph, Phone: +639069673630
- Gil Ballesca, Co-Founder - Email: gil.ballesca@rgl.com.ph

CONTACT: info@rgl.com.ph | Response time: 24 hours | 350+ Projects Completed

GUIDELINES:
- Be friendly, professional, and concise
- For pricing inquiries, encourage them to fill out the inquiry form
- Keep responses brief but helpful";

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || empty(trim($input['message']))) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

$user_message = trim($input['message']);
$conversation_history = isset($input['history']) ? $input['history'] : [];

$contents = [];
foreach ($conversation_history as $msg) {
    $contents[] = [
        'role' => $msg['role'] === 'user' ? 'user' : 'model',
        'parts' => [['text' => $msg['content']]]
    ];
}
$contents[] = ['role' => 'user', 'parts' => [['text' => $user_message]]];

$api_url = "https://generativelanguage.googleapis.com/v1beta/models/{$gemini_model}:generateContent?key={$gemini_api_key}";

$request_body = [
    'contents' => $contents,
    'systemInstruction' => ['parts' => [['text' => $system_context]]],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 1024,
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// Disable SSL verification for local development (XAMPP)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);
curl_close($ch);

// Debug: Check for curl errors
if ($curl_error || $curl_errno) {
    echo json_encode(['error' => 'Connection failed: ' . $curl_error . ' (Error ' . $curl_errno . ')']);
    exit;
}

// Check if we got a response
if (empty($response)) {
    echo json_encode(['error' => 'Empty response from API']);
    exit;
}

$response_data = json_decode($response, true);

// Check JSON decode
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response']);
    exit;
}

// Debug: Check HTTP response
if ($http_code !== 200) {
    $error_msg = isset($response_data['error']['message']) ? $response_data['error']['message'] : 'API error (HTTP ' . $http_code . ')';
    echo json_encode(['error' => $error_msg]);
    exit;
}

if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['response' => $response_data['candidates'][0]['content']['parts'][0]['text']]);
} else {
    // Check if blocked by safety or other reasons
    $finish_reason = isset($response_data['candidates'][0]['finishReason']) ? $response_data['candidates'][0]['finishReason'] : '';
    if ($finish_reason === 'SAFETY') {
        echo json_encode(['response' => 'I apologize, but I cannot respond to that. How else can I help you with RGL Business Solutions?']);
    } elseif (isset($response_data['promptFeedback']['blockReason'])) {
        echo json_encode(['response' => 'I cannot process that request. Please ask me about RGL Business Solutions services.']);
    } else {
        echo json_encode(['error' => 'No response generated. Reason: ' . $finish_reason]);
    }
}
?>
