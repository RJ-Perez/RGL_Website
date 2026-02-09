<?php
/**
 * Get A Quote Form Handler using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader
require '../vendor/autoload.php';

// Set plain text response header for AJAX
header('Content-Type: text/plain');

// ============================================
// SMTP CONFIGURATION - Gmail with App Password
// ============================================
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;                             // Use 587 with STARTTLS
$smtp_username = 'perezryanjohn@gmail.com';
$smtp_password = 'vrcv futp nedt ljfc';       // Gmail App Password
$receiving_email = 'info@rgl.com.ph';

// ============================================

// Validate required fields
if (empty($_POST['name']) || empty($_POST['email'])) {
    echo 'Please fill in all required fields.';
    exit;
}

// Sanitize input data
$name = htmlspecialchars(strip_tags(trim($_POST['name'])));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$phone = isset($_POST['phone']) ? htmlspecialchars(strip_tags(trim($_POST['phone']))) : '';
$departure = isset($_POST['departure']) ? htmlspecialchars(strip_tags(trim($_POST['departure']))) : '';
$delivery = isset($_POST['delivery']) ? htmlspecialchars(strip_tags(trim($_POST['delivery']))) : '';
$weight = isset($_POST['weight']) ? htmlspecialchars(strip_tags(trim($_POST['weight']))) : '';
$dimensions = isset($_POST['dimensions']) ? htmlspecialchars(strip_tags(trim($_POST['dimensions']))) : '';
$message = isset($_POST['message']) ? htmlspecialchars(strip_tags(trim($_POST['message']))) : '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Invalid email format.';
    exit;
}

// Email subject
$subject = 'RGL Website Inquiry';

// Build email body
$email_body = "You have received a new inquiry request from your website.\n\n";
// $email_body .= "City of Departure: $departure\n";
// $email_body .= "Delivery City: $delivery\n";
// $email_body .= "Total Weight (kg): $weight\n";
// $email_body .= "Dimensions (cm): $dimensions\n\n";
$email_body .= "Name: $name\n"; 
$email_body .= "Email: $email\n";
$email_body .= "Phone: $phone\n";
if (!empty($message)) {
    $email_body .= "\nMessage:\n$message\n";
}

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtp_port;

    // Recipients
    $mail->setFrom($smtp_username, 'RGL Website Inquiry');
    $mail->addAddress($receiving_email);
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body    = $email_body;

    $mail->send();
    echo 'OK';
} catch (Exception $e) {
    echo "Unable to send email. Error: {$mail->ErrorInfo}";
}
?>
