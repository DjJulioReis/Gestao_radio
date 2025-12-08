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
    header("Location: ../clientes.php");
    exit();
}

// 1️⃣ Receber dados do formulário
$cliente_id      = intval($_POST['cliente_id'] ?? 0);
$empresa         = trim($_POST['empresa']);
$cnpj_cpf        = trim($_POST['cnpj_cpf']);
$email           = trim($_POST['email']);
$telefone        = trim($_POST['telefone']);
$endereco        = trim($_POST['endereco']);
$credito_permuta = floatval($_POST['credito_permuta']);
$ativo           = isset($_POST['ativo']) ? 1 : 0;
$plano_id        = intval($_POST['plano_id']);
$data_vencimento = intval($_POST['data_vencimento']); // 1, 10 ou 20

// 2️⃣ Verifica se cliente existe
$stmtCheck = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmtCheck->bind_param("i", $cliente_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$clienteAntigo = $resultCheck->fetch_assoc();
$stmtCheck->close();

if (!$clienteAntigo) {
    die("Cliente não encontrado.");
}

// 3️⃣ Atualizar cliente
$stmt = $conn->prepare("
    UPDATE clientes SET 
        empresa = ?, 
        cnpj_cpf = ?, 
        email = ?, 
        telefone = ?, 
        endereco = ?, 
        credito_permuta = ?, 
        ativo = ?, 
        plano_id = ?, 
        data_vencimento = ?
    WHERE id = ?
");

if (!$stmt) {
    die("Erro prepare update cliente: " . $conn->error);
}

// bind_param correto: 10 variáveis
$stmt->bind_param(
    "sssssdiiii",
    $empresa,
    $cnpj_cpf,
    $email,
    $telefone,
    $endereco,
    $credito_permuta,
    $ativo,
    $plano_id,
    $data_vencimento,
    $cliente_id
);

if (!$stmt->execute()) {
    die("Erro ao atualizar cliente: " . $stmt->error);
}
$stmt->close();

// 4️⃣ Comparar alterações para enviar email
$alteracoes = [];
foreach (['empresa','cnpj_cpf','email','telefone','endereco','credito_permuta','ativo','plano_id','data_vencimento'] as $campo) {
    $antigo = $clienteAntigo[$campo];
    $novo   = $$campo; // variável dinâmica
    if ($antigo != $novo) {
        $alteracoes[$campo] = ['antigo' => $antigo, 'novo' => $novo];
    }
}

// 5️⃣ Enviar email ao cliente se houver alterações
if (!empty($alteracoes)) {
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

        $body = "<h2>Olá, " . htmlspecialchars($empresa) . "!</h2>";
        $body .= "<p>Seu cadastro na Rádio Nova FM 87.5 foi atualizado. Segue o resumo das alterações:</p>";
        $body .= "<ul>";
        foreach ($alteracoes as $campo => $valores) {
            $body .= "<li><strong>" . ucfirst($campo) . "</strong>: de <em>" . htmlspecialchars($valores['antigo']) . "</em> para <em>" . htmlspecialchars($valores['novo']) . "</em></li>";
        }
        $body .= "</ul>";
        $body .= "<p>Qualquer dúvida, entre em contato pelo WhatsApp 41 98877-1752.</p>";

        $mail->Subject = "Atualização no seu cadastro - Rádio Nova FM";
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar email de atualização para $email: " . $mail->ErrorInfo);
    }
}

// 6️⃣ Redirecionar
header("Location: ../clientes.php?success=1");
exit();
