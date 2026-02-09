<?php
/**
 * Contact Form Handler using PHPMailer
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
// $smtp_host = 'smtp.gmail.com';
// $smtp_port = 587;                             // Use 587 with STARTTLS
// $smtp_username = 'perezryanjohn@gmail.com';
// $smtp_password = 'vrcv futp nedt ljfc';    // Replace with 16-char App Password!
// $receiving_email = 'perezryanjohn@gmail.com';
$smtp_host = 'smtp-mail.outlook.com';
$smtp_port = 587;                             // Use 587 with STARTTLS
$smtp_username = 'ryanjohn.perez';
$smtp_password = '@fgVk8^1';    // Replace with 16-char App Password!
$receiving_email = 'ryanjohn.perez@rgl.com.ph';
// ============================================

// Validate required fields
if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['subject']) || empty($_POST['message'])) {
    echo 'Please fill in all required fields.';
    exit;
}

// Sanitize input data
$name = htmlspecialchars(strip_tags(trim($_POST['name'])));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(strip_tags(trim($_POST['subject'])));
$message = htmlspecialchars(strip_tags(trim($_POST['message'])));
$phone = isset($_POST['phone']) ? htmlspecialchars(strip_tags(trim($_POST['phone']))) : '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Invalid email format.';
    exit;
}

// Build email body
$email_body = "You have received a new message from your website contact form.\n\n";
$email_body .= "From: $name\n";
$email_body .= "Email: $email\n";
if (!empty($phone)) {
    $email_body .= "Phone: $phone\n";
}
$email_body .= "\nMessage:\n$message\n";

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
    $mail->setFrom($smtp_username, 'Website Contact Form');
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
