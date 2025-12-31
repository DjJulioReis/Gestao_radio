<?php
require_once '../init.php';
require_once '../email_config.php';

require_once __DIR__ . '/../../mailer/PHPMailer.php';
require_once __DIR__ . '/../../mailer/Exception.php';
require_once __DIR__ . '/../../mailer/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../cliente_add.php");
    exit();
}

// 1️⃣ Recebe dados do formulário
$empresa         = trim($_POST['empresa']);
$cnpj_cpf        = trim($_POST['cnpj_cpf']);
$email           = trim($_POST['email']);
$telefone        = trim($_POST['telefone']);
$endereco        = trim($_POST['endereco']);
$credito_permuta = floatval($_POST['credito_permuta']);
$data_cadastro   = $_POST['data_cadastro']; // formato YYYY-MM-DD
$ativo           = 1; // Clientes são criados como ativos por padrão

// 2️⃣ Inserir cliente
// O saldo_permuta inicial é igual ao credito_permuta
$stmt = $conn->prepare("
    INSERT INTO clientes
    (empresa, cnpj_cpf, email, telefone, endereco, credito_permuta, saldo_permuta, data_cadastro, ativo)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}
$stmt->bind_param(
    "sssssddsi",
    $empresa,
    $cnpj_cpf,
    $email,
    $telefone,
    $endereco,
    $credito_permuta,
    $credito_permuta, // saldo_permuta inicial
    $data_cadastro,
    $ativo
);

if (!$stmt->execute()) {
    // Em um ambiente de produção, logar o erro em vez de usar 'die'
    die("Erro ao cadastrar cliente: " . $stmt->error);
}

$cliente_id = $stmt->insert_id;
$stmt->close();

// 3️⃣ Enviar e-mail de boas-vindas simplificado
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = "mail.novafm875.com.br";
    $mail->SMTPAuth   = true;
    $mail->Username   = "no-reply@novafm875.com.br";
    $mail->Password   = "Nf9jxjaxf2sf24TfquaQ";
    $mail->Port       = 587;
    $mail->CharSet    = "UTF-8";

    $mail->setFrom("no-reply@novafm875.com.br", "Nova FM 87.5 – Financeiro");
    $mail->addAddress($email, $empresa);
    $mail->isHTML(true);

    $mail->Subject = "Cadastro realizado na Rádio Nova FM!";
    $mail->Body = "
        <h2>Olá, " . htmlspecialchars($empresa) . "!</h2>
        <p>Seu cadastro em nosso sistema foi realizado com sucesso.</p>
        <p>Em breve, nossa equipe entrará em contato para formalizar os contratos e planos.</p>
        <p>Qualquer dúvida, estamos no WhatsApp 41 98877-1752.</p>
    ";

    $mail->send();
} catch (Exception $e) {
    // Logar o erro em um ambiente de produção
    error_log("Erro ao enviar e-mail de boas-vindas para $email: " . $mail->ErrorInfo);
}

// 4️⃣ Finaliza e redireciona
header("Location: ../clientes.php?success=1");
exit();
