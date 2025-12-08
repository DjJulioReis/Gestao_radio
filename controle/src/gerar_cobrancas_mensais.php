<?php
require_once '../init.php';
require_once '../email_config.php'; // PHPMailer config

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
$data_cadastro   = $_POST['data_cadastro'];
$ativo           = isset($_POST['ativo']) ? 1 : 0;
$plano_id        = intval($_POST['plano_id']);

// 2️⃣ Inserir cliente com saldo_permuta inicial
$stmt = $conn->prepare("
    INSERT INTO clientes
    (empresa, cnpj_cpf, email, telefone, endereco, credito_permuta, saldo_permuta, data_cadastro, ativo, plano_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "ssssddssi",
    $empresa,
    $cnpj_cpf,
    $email,
    $telefone,
    $endereco,
    $credito_permuta,
    $credito_permuta, // saldo_permuta inicial igual ao crédito
    $data_cadastro,
    $ativo,
    $plano_id
);

if (!$stmt->execute()) {
    die("Erro ao cadastrar cliente: " . $stmt->error);
}

$cliente_id = $conn->insert_id;
$stmt->close();

// 3️⃣ Buscar informações do plano
$stmtPlano = $conn->prepare("SELECT nome, preco FROM planos WHERE id = ?");
$stmtPlano->bind_param("i", $plano_id);
$stmtPlano->execute();
$resultPlano = $stmtPlano->get_result();
$plano = $resultPlano->fetch_assoc();
$plano_nome  = $plano['nome'];
$plano_valor = floatval($plano['preco']);
$stmtPlano->close();

// 4️⃣ Criar contrato (1 mês como padrão)
$data_inicio = $data_cadastro;
$data_fim    = date("Y-m-d", strtotime("+1 month", strtotime($data_inicio)));

$stmtContrato = $conn->prepare("
    INSERT INTO contratos (cliente_id, plano_id, data_inicio, data_fim)
    VALUES (?, ?, ?, ?)
");
$stmtContrato->bind_param("iiss", $cliente_id, $plano_id, $data_inicio, $data_fim);
$stmtContrato->execute();
$contrato_id = $conn->insert_id;
$stmtContrato->close();

// 5️⃣ Criar primeira cobrança considerando permuta
// Abate do saldo_permuta
$stmtSaldo = $conn->prepare("SELECT saldo_permuta FROM clientes WHERE id = ?");
$stmtSaldo->bind_param("i", $cliente_id);
$stmtSaldo->execute();
$saldo = $stmtSaldo->get_result()->fetch_assoc()['saldo_permuta'];
$stmtSaldo->close();

$valorCobrar = $plano_valor;
if ($saldo > 0) {
    if ($saldo >= $plano_valor) {
        $valorCobrar = 0.00;
        $novo_saldo = $saldo - $plano_valor;
        $pago = 1;
    } else {
        $valorCobrar = $plano_valor - $saldo;
        $novo_saldo = 0.00;
        $pago = 0;
    }

    // Atualiza saldo_permuta do cliente
    $stmtAtualizaSaldo = $conn->prepare("UPDATE clientes SET saldo_permuta = ? WHERE id = ?");
    $stmtAtualizaSaldo->bind_param("di", $novo_saldo, $cliente_id);
    $stmtAtualizaSaldo->execute();
    $stmtAtualizaSaldo->close();
} else {
    $pago = 0;
}

// Referência do mês atual
$referencia = date("Y-m");

// Inserir cobrança
$stmtCobranca = $conn->prepare("
    INSERT INTO cobrancas (contrato_id, cliente_id, plano_id, referencia, valor, pago)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmtCobranca->bind_param("iiisdi", $contrato_id, $cliente_id, $plano_id, $referencia, $valorCobrar, $pago);
$stmtCobranca->execute();
$stmtCobranca->close();

// 6️⃣ Enviar e-mail de boas-vindas
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
    $mail->Subject = "Bem-vindo(a) à Rádio Nova FM de Pontal do Paraná!";

    $mensagemSaldo = $novo_saldo > 0 
        ? "Seu saldo de permuta de R$ ".number_format($novo_saldo,2,",",".")." será usado para abater suas próximas cobranças."
        : "Em breve você receberá um e-mail com a cobrança referente ao débito no valor de R$ ".number_format($valorCobrar,2,",",".");

    $mail->Body = "
        <h2>Olá, ".htmlspecialchars($empresa)."!</h2>
        <p>Seja bem-vindo(a) à <strong>Rádio Nova FM de Pontal do Paraná</strong>.</p>
        <p>Plano contratado: <strong>".htmlspecialchars($plano_nome)."</strong></p>
        <p>".$mensagemSaldo."</p>
        <p>Qualquer dúvida, entre em contato conosco pelo Whatsapp 41 98877-1752.</p>
    ";

    $mail->send();

} catch (Exception $e) {
    error_log("Erro ao enviar e-mail de boas-vindas para $email: " . $mail->ErrorInfo);
}

// 7️⃣ Redireciona para clientes.php
header("Location: ../clientes.php?success=1");
exit();
