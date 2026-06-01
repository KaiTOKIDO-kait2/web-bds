<?php

class Mailer {
    public function sendResetLink($toEmail, $subject, $message) {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            error_log('PHPMailer autoload not found. Run composer install.');
            return false;
        }

        require_once $autoload;

        $mailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
        if (!class_exists($mailerClass)) {
            error_log('PHPMailer class not available after autoload.');
            return false;
        }

        try {
            $mail = new $mailerClass(true);
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: '';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?: '';
            $mail->Password = getenv('SMTP_PASS') ?: '';
            $mail->Port = (int) (getenv('SMTP_PORT') ?: 587);

            $secure = strtolower((string) (getenv('SMTP_SECURE') ?: 'tls'));
            if ($secure === 'ssl') {
                $mail->SMTPSecure = 'ssl';
            } elseif ($secure === 'tls') {
                $mail->SMTPSecure = 'tls';
            }

            $mail->CharSet = 'UTF-8';
            $mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: (getenv('SMTP_USER') ?: 'no-reply@example.com'), getenv('MAIL_FROM_NAME') ?: 'Real Estate');
            $mail->addAddress($toEmail);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;

            return $mail->send();
        } catch (\Exception $e) {
            error_log('SMTP send failed: ' . $e->getMessage());
            return false;
        }
    }
}