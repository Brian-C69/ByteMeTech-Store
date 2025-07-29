<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

$firstName = $lastName = $email = $message = "";
$firstName_err = $lastName_err = $email_err = $message_err = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    // Validation
    if (empty($firstName))
        $firstName_err = "Please enter your first name.";
    if (empty($lastName))
        $lastName_err = "Please enter your last name.";
    if (empty($email)) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email.";
    }
    if (empty($message))
        $message_err = "Please enter your message.";

    // Proceed if no errors
    if (!$firstName_err && !$lastName_err && !$email_err && !$message_err) {
        $stmt = $pdo->prepare("
            INSERT INTO contact_form (CONTACT_FIRSTNAME, CONTACT_LASTNAME, CONTACT_EMAIL, CONTACT_MESSAGE, CONTACT_IP_ADDRESS)
            VALUES (:firstName, :lastName, :email, :message, :ip_address)
        ");
        $stmt->execute([
            ":firstName" => $firstName,
            ":lastName" => $lastName,
            ":email" => $email,
            ":message" => $message,
            ":ip_address" => $ip_address
        ]);

        // Format email body
        $timestamp = date("F j, Y \a\\t g:i a");
        $source_url = $_SERVER['HTTP_REFERER'] ?? 'N/A';

        $body = <<<EOD
        <strong>First Name:</strong> {$firstName}<br><br>
        <strong>Last Name:</strong> {$lastName}<br><br>
        <strong>Email:</strong> {$email}<br><br>
        <strong>Message:</strong><br>
        <pre style="white-space: pre-wrap;">{$message}</pre><br>
        <strong>Consent:</strong> Yes<br><br>
        <strong>Time:</strong> {$timestamp}<br>
        <strong>IP Address:</strong> {$ip_address}<br>
        <strong>Source URL:</strong> {$source_url}<br><br>
        <em>Sent by an unverified visitor to your site.</em>
        EOD;

        // Send email to customer service
        try {
            $mail = get_mail();
            $mail->addAddress("bchoong1@gmail.com");
            $mail->Subject = "New Contact Form Submission from $firstName $lastName";
            $mail->Body = $body;
            $mail->isHTML(true);
            $mail->send();
            $success_message = "Your message has been sent successfully!";
            $firstName = $lastName = $email = $message = ""; // Clear form
        } catch (Exception $e) {
            $error_message = "Message saved but email failed to send. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>ByteMeTech.com | Contact Us</title>
        <?php include '../includes/headers.php'; ?>
    </head>
    <body class="bg-light">
        <?php include '../includes/navbar.php'; ?>

        <div class="container bg-dark">
            <h1 class="white-text">Contact Us</h1>
        </div>

        <div class="container">
            <h1>Let's Talk</h1>
            <p>We'd love to hear from you. We're here to answer your questions and listen to your suggestions.</p>

            <?php if ($error_message): ?><div class="alert"><?= $error_message ?></div><?php endif; ?>
            <?php if ($success_message): ?><div class="alert-success"><?= $success_message ?></div><?php endif; ?>

            <form action="contact.php" method="post" class="login-form">
                <label for="firstName">First Name</label>
                <?php if ($firstName_err) echo "<div class='alert'>$firstName_err</div>"; ?>
                <input type="text" id="firstName" name="firstName" class="input-field" value="<?= htmlspecialchars($firstName) ?>">

                <label for="lastName">Last Name</label>
                <?php if ($lastName_err) echo "<div class='alert'>$lastName_err</div>"; ?>
                <input type="text" id="lastName" name="lastName" class="input-field" value="<?= htmlspecialchars($lastName) ?>">

                <label for="email">Email</label>
                <?php if ($email_err) echo "<div class='alert'>$email_err</div>"; ?>
                <input type="text" id="email" name="email" class="input-field" value="<?= htmlspecialchars($email) ?>">

                <label for="message">Message</label>
                <?php if ($message_err) echo "<div class='alert'>$message_err</div>"; ?>
                <textarea id="message" name="message" class="input-field"><?= htmlspecialchars($message) ?></textarea>

                <p>By submitting this form, you agree to our processing of your data in accordance with our <a href="privacy-policy.php" target="_blank">Privacy Policy</a>.</p>
                <button type="submit" class="btn btn-green">Send Message</button>
            </form>
        </div>

        <div class="container">
            <h2>Our Store Location</h2>
            <iframe 
                src="https://www.google.com/maps?q=W-09-11+Menara+Melawangi,+Amcorp+Mall,+Petaling+Jaya,+Malaysia&output=embed"
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>


        <?php include '../includes/footer.php'; ?>
    </body>
</html>
