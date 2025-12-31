<?php
    require_once __DIR__ . '/../mailer/PHPMailer.php';
    require_once __DIR__ . '/../mailer/Exception.php';
    require_once __DIR__ . '/../mailer/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

function enviarEmail($destinatario, $assunto, $mensagemHtml) {
    $mail = new PHPMailer(true);

    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host = "mail.novafm875.com.br";
        $mail->SMTPAuth = true;
        $mail->Username = "no-reply@novafm875.com.br";
        $mail->Password = "Nf9jxjaxf2sf24TfquaQ";
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        // Remetente
        $mail->setFrom("no-reply@novafm875.com.br", "Nova FM 87.5 – Financeiro");

        // Destinatário
        $mail->addAddress($destinatario);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagemHtml;

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}
