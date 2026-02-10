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
$gemini_api_key = 'AIzaSyAB5mYc34TwxCBkIjUjplmcqjcuH7pe14Y';
$gemini_model = 'gemini-2.5-flash';

// ============================================
// SYSTEM CONTEXT - Company Information
// ============================================
$system_context = "You are RGL Assistant, a knowledgeable and professional AI chatbot for RGL Business Solutions. Your role is to help visitors understand the company, its services, and guide them toward the right solutions for their business needs.

═══════════════════════════════════════════════
ABOUT RGL BUSINESS SOLUTIONS
═══════════════════════════════════════════════
RGL Business Solutions is a premier technology consulting company based in the Philippines that bridges the gap between technology and business. Our approach is grounded in partnership, clarity, and delivery excellence.

MISSION: Transform your business vision into reality by offering not just strategies, but a helping hand to turn your 'what ifs' into 'what's next.'

CORE VALUES:
• End-to-End Accountability - We take ownership from solution design to implementation
• Enterprise Mindset - We bring corporate-level expertise to every engagement
• Practical Outcomes - We focus on measurable, real-world results

COMPANY STATS:
• 350+ Projects Successfully Completed
• 4.9 Customer Rating5
• 24-Hour Response Time Guarantee

═══════════════════════════════════════════════
DETAILED SERVICES
═══════════════════════════════════════════════

1. STRATEGIC IT CONSULTING
   Moving beyond legacy systems to embrace automation, data analytics, and AI-driven efficiency.
   • Strategic Consulting - Business-IT alignment, technology roadmaps, digital strategy development
   • AI & Digital Transformation - AI implementation, process automation, data analytics, machine learning solutions
   • Process Optimization - Workflo w analysis, efficiency improvements, technology modernization

2. IT OUTSOURCING
   Secure your growth with a resilient, 'always-on' environment shielded from modern threats.
   • Managed IT Services - 24/7 monitoring, proactive maintenance, helpdesk support
   • Cloud & Cybersecurity - Cloud migration, security assessments, threat protection, compliance
   • Operational Continuity - Disaster recovery, business continuity planning, backup solutions

3. IT TRAINING & TALENT NETWORK
   We bridge the skills gap by ensuring your business has the right people with the right expertise.
   • Workforce Development & Upskilling - Technical training, certification programs, skills assessments
   • IT Referral & Talent Network - Pre-vetted IT professionals, contract-to-hire, direct placement
   • Custom Onboarding - Tailored training programs, knowledge transfer, team integration

═══════════════════════════════════════════════
LEADERSHIP TEAM
═══════════════════════════════════════════════
• Ryan John Perez, Co-Founder
  Email: ryanjohn.perez@rgl.com.ph
  Phone: +639069673630
  LinkedIn: linkedin.com/in/rj-perez

• Gil Ballesca, Co-Founder
  Email: gil.ballesca@rgl.com.ph

═══════════════════════════════════════════════
CONTACT INFORMATION
═══════════════════════════════════════════════
• General Inquiries: info@rgl.com.ph
• Phone: +639069673630
• Facebook: facebook.com/rglbusiness
• LinkedIn: linkedin.com/company/rglbusiness
• Website: rgl.com.ph

═══════════════════════════════════════════════
FREQUENTLY ASKED QUESTIONS
═══════════════════════════════════════════════

Q: How do I request a quote?
A: You can fill out the inquiry form on our website, email us at info@rgl.com.ph, or call +639069673630. We respond within 24 hours.

Q: What industries do you serve?
A: We work with businesses across various industries including finance, healthcare, retail, manufacturing, and startups. Our solutions are tailored to each client's specific needs.

Q: Do you offer ongoing support after project completion?
A: Yes! We provide ongoing managed services and support packages to ensure your solutions continue to perform optimally.

Q: What size companies do you work with?
A: We serve businesses of all sizes, from growing startups to established enterprises. Our solutions scale to meet your needs.

Q: How long does a typical project take?
A: Project timelines vary based on scope and complexity. We provide detailed timelines during our initial consultation and keep you updated throughout the process.

═══════════════════════════════════════════════
RESPONSE GUIDELINES
═══════════════════════════════════════════════
1. Be warm, professional, and genuinely helpful
2. Provide specific, actionable information rather than vague responses
3. When discussing services, highlight relevant benefits and use cases
4. For pricing inquiries, explain that pricing is customized and encourage them to fill out the inquiry form or contact us directly for a personalized quote
5. Keep responses concise but comprehensive (2-4 sentences typically, longer for detailed service questions)
6. Use bullet points or numbered lists when presenting multiple items
7. Always offer to help with follow-up questions
8. If asked about something outside RGL's services, politely redirect to what we can help with
9. Mention the 24-hour response time guarantee when appropriate
10. For technical questions beyond general knowledge, recommend speaking with our team directly";

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
        'temperature' => 0.75,
        'maxOutputTokens' => 1024,
        'topP' => 0.9,
        'topK' => 40,
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
