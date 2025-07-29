<?php
include '../includes/base.php';

if (is_post()) {
    $email = req('email');
    $subject = req('subject');
    $body = req('body');
    $html = req('html');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Validate: subject
    if ($subject == '') {
        $_err['subject'] = 'Required';
    } else if (strlen($subject) > 100) {
        $_err['subject'] = 'Maximum 100 characters';
    }

    // Validate: body
    if ($body == '') {
        $_err['body'] = 'Required';
    } else if (strlen($body) > 500) {
        $_err['body'] = 'Maximum 500 characters';
    }

    // Send email
    if (!$_err) {
        // TODO
        $m = get_mail();
        $m->addAddress($email);
        $m->Subject = $subject;
        $m->Body = $body;
        $m->isHTML($html);
        //$m->addAttachment('secret.pdf');
        $m->send();

        temp('info', 'Email sent');
        redirect();
    }
}

$_title = 'Demo';
include '../includes/headers.php';
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <title>ByteMeTech.com | Login</title>
        <?php include '../includes/headers.php'; ?>
    </head>
    
    <body class="bg-light">
        <?php include '../includes/navbar.php'; ?>
        <div class="container bg-dark">
            <h1 class="white-text">Test Email</h1>
        </div>
        
        <div class="container">
            <?php if ($msg = temp('info')): ?>
                <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <form class="login-form" method="post">
                <label for="email">Email</label>
                <?= err('email') ?>
                <?= html_text('email', 'maxlength="100"') ?>
                

                <label for="subject">Subject</label>
                <?= err('subject') ?>
                <?= html_text('subject', 'maxlength="100"') ?>
                

                <label for="body">Body</label>
                <?= err('body') ?>
                <?= html_textarea('body', 'maxlength="500"') ?>
                

                <label></label>
                <?= html_checkbox('html', 'HTML Content') ?>
                <br>

                <section>
                    <button class="btn btn-blue">Send</button>
                    <button type="reset" class="btn btn-yellow">Reset</button>
                </section>
            </form>
        </div>
        

        <?php include '../includes/footer.php'; ?>
    </body>
</html>
