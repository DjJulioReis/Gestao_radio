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
$data_cadastro   = $_POST['data_cadastro']; // formato YYYY-MM-DD (ou conforme seu formulário)
$ativo           = isset($_POST['ativo']) ? 1 : 0;
$plano_id        = intval($_POST['plano_id']);
$data_vencimento = intval($_POST['data_vencimento']);   // 1, 10 ou 20

// 2️⃣ Inserir cliente (inclui saldo_permuta inicial igual ao credito_permuta)
$stmt = $conn->prepare("
    INSERT INTO clientes
    (empresa, cnpj_cpf, email, telefone, endereco, credito_permuta, saldo_permuta, data_cadastro, ativo, plano_id, data_vencimento)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    die("Erro prepare insert cliente: " . $conn->error);
}
$stmt->bind_param(
    "sssssddsiii",
    $empresa,
    $cnpj_cpf,
    $email,
    $telefone,
    $endereco,
    $credito_permuta,
    $credito_permuta,      // saldo_permuta inicial
    $data_cadastro,
    $ativo,
    $plano_id,
    $data_vencimento
);

if (!$stmt->execute()) {
    die("Erro ao cadastrar cliente: " . $stmt->error);
}

$cliente_id = $stmt->insert_id;
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

// 4️⃣ Criar contrato inicial (1 mês padrão)
$data_inicio = $data_cadastro;
$data_fim    = date("Y-m-d", strtotime("+1 month", strtotime($data_inicio)));

$stmtContrato = $conn->prepare("
    INSERT INTO contratos (cliente_id, plano_id, data_inicio, data_fim)
    VALUES (?, ?, ?, ?)
");
$stmtContrato->bind_param("iiss", $cliente_id, $plano_id, $data_inicio, $data_fim);
if (!$stmtContrato->execute()) {
    // opcional: remover cliente criado em caso de erro no contrato
    error_log("Erro ao criar contrato: " . $stmtContrato->error);
}
$contrato_id = $stmtContrato->insert_id;
$stmtContrato->close();

// -----------------------------
// 5️⃣ LÓGICA DE COBRANÇA
// -----------------------------
// Definir dia atual
$hojeDT = new DateTime($data_cadastro);
$hojeDia = intval($hojeDT->format('d'));

// Normalizar dia de vencimento (1,10,20)
$vencDia = intval($data_vencimento);
if (!in_array($vencDia, [1,10,20])) {
    $vencDia = 10; // default safe
}

// Decidir fluxo
$criarProporcional = false;
$valorCobrarAgora = 0.00;
$referenciaAgora = $hojeDT->format("Y-m"); // padrão
$referenciaProxima = date("Y-m", strtotime("first day of next month")); // referência para cobrança cheia futura

if ($hojeDia < $vencDia) {
    // Gerar proporcional até o vencimento deste mês
    $criarProporcional = true;
    $diasProporcionais = $vencDia - $hojeDia;

    // Usar dias no mês atual para cálculo (melhor precisão)
    $diasNoMes = cal_days_in_month(CAL_GREGORIAN, intval($hojeDT->format('m')), intval($hojeDT->format('Y')));
    $valorDiario = $plano_valor / $diasNoMes;
    $valorProporcional = round($valorDiario * $diasProporcionais, 2);

    $valorCobrarAgora = $valorProporcional;
    $referenciaAgora = $hojeDT->format("Y-m"); // cobrança proporcional ainda neste mês

    // próxima cobrança (valor cheio) será no mês seguinte
    $referenciaNormal = date("Y-m", strtotime("first day of next month"));

} elseif ($hojeDia == $vencDia) {
    // Cobrança cheia hoje
    $criarProporcional = false;
    $valorCobrarAgora = $plano_valor;
    $referenciaAgora = $hojeDT->format("Y-m");
    // próxima cobrança será mês seguinte
    $referenciaNormal = date("Y-m", strtotime("first day of next month"));

} else { // $hojeDia > $vencDia
    // Sem proporcional agora — criar apenas a cobrança cheia para o próximo mês
    $criarProporcional = false;
    $valorCobrarAgora = 0.00; // nada a cobrar agora
    $referenciaAgora = null;
    $referenciaNormal = date("Y-m", strtotime("first day of next month"));
}

// -----------------------------
// Aplicar permuta (saldo) sobre a cobrança que será criada AGORA (se houver)
// -----------------------------
$pagoAgora = 0;
$novo_saldo = null;

if ($criarProporcional || ($hojeDia == $vencDia)) {
    // buscar saldo atual (poderia já ter sido alterado, buscar novamente)
    $stmtSaldo = $conn->prepare("SELECT saldo_permuta FROM clientes WHERE id = ?");
    $stmtSaldo->bind_param("i", $cliente_id);
    $stmtSaldo->execute();
    $res = $stmtSaldo->get_result()->fetch_assoc();
    $saldo = isset($res['saldo_permuta']) ? floatval($res['saldo_permuta']) : 0.00;
    $stmtSaldo->close();

    // aplica permuta
    if ($saldo > 0 && $valorCobrarAgora > 0) {
        if ($saldo >= $valorCobrarAgora) {
            $pagoAgora = 1;
            $novo_saldo = $saldo - $valorCobrarAgora;
            $valorCobrarAgora = 0.00;
        } else {
            $valorCobrarAgora = round($valorCobrarAgora - $saldo, 2);
            $novo_saldo = 0.00;
            $pagoAgora = 0;
        }

        // atualiza saldo_permuta no cliente
        $stmtAtualizaSaldo = $conn->prepare("UPDATE clientes SET saldo_permuta = ? WHERE id = ?");
        $stmtAtualizaSaldo->bind_param("di", $novo_saldo, $cliente_id);
        $stmtAtualizaSaldo->execute();
        $stmtAtualizaSaldo->close();
    } else {
        $pagoAgora = 0;
    }
}

// -----------------------------
// Inserir cobrança AGORA (se aplicável)
// -----------------------------
if (($criarProporcional && $valorCobrarAgora >= 0) || ($hojeDia == $vencDia && $valorCobrarAgora > 0) || ($criarProporcional && $valorCobrarAgora == 0 && $pagoAgora == 1)) {
    // Se criarProporcional==true => inserir cobrança proporcional
    // Se hoje == vencDia => inserir cobrança cheia
    $stmtCobranca = $conn->prepare("
        INSERT INTO cobrancas (contrato_id, cliente_id, plano_id, referencia, valor, pago)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if (!$stmtCobranca) {
        error_log("Erro prepare insert cobranca agora: " . $conn->error);
    } else {
        $stmtCobranca->bind_param(
            "iiisdi",
            $contrato_id,
            $cliente_id,
            $plano_id,
            $referenciaAgora,
            $valorCobrarAgora,
            $pagoAgora
        );
        $stmtCobranca->execute();
        $stmtCobranca->close();
    }
}

// -----------------------------
// Inserir cobrança normal do próximo mês (sempre criada para agendamento)
// -----------------------------
// Regra: se hoje < vencDia ou hoje == vencDia ou hoje > vencDia -> sempre criar a cobrança do próximo mês (valor cheio)
// Observação: se hoje==vencDia e você não quiser criar a próxima mês agora, pode remover.
// Aqui criamos para garantir que a cobrança futura já existe.
$referenciaNormal = $referenciaNormal ?? date("Y-m", strtotime("first day of next month"));
$stmtCobranca2 = $conn->prepare("
    INSERT INTO cobrancas (contrato_id, cliente_id, plano_id, referencia, valor, pago)
    VALUES (?, ?, ?, ?, ?, 0)
");
if ($stmtCobranca2) {
    $stmtCobranca2->bind_param(
        "iiisd",
        $contrato_id,
        $cliente_id,
        $plano_id,
        $referenciaNormal,
        $plano_valor
    );
    $stmtCobranca2->execute();
    $stmtCobranca2->close();
} else {
    error_log("Erro prepare insert cobranca futura: " . $conn->error);
}

// -----------------------------
// 6️⃣ Enviar e-mail de boas-vindas (com valores corretos)
// -----------------------------
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

    // Montar mensagem explicativa
    if ($criarProporcional && $valorCobrarAgora > 0) {
        $valorMsg = "Foi gerada uma cobrança proporcional no valor de <strong>R$ ".number_format($valorCobrarAgora,2,",",".")."</strong> (referência: {$referenciaAgora}).";
    } elseif ($criarProporcional && $valorCobrarAgora == 0 && isset($pagoAgora) && $pagoAgora == 1 && $credito_permuta > 0) {
        $valorMsg = "A primeira cobrança proporcional foi totalmente coberta pelo seu saldo de permuta. Seu novo saldo de permuta é <strong>R$ ".number_format($novo_saldo,2,",",".")."</strong>.";
    } elseif ($hojeDia == $vencDia) {
        $valorMsg = "Foi gerada a cobrança mensal no valor de <strong>R$ ".number_format($valorCobrarAgora,2,",",".")."</strong> (referência: {$referenciaAgora}).";
    } else {
        // hoje > vencDia e não geramos proporcional
        $valorMsg = "Sua primeira cobrança será gerada em <strong>{$referenciaNormal}</strong> no valor de <strong>R$ ".number_format($plano_valor,2,",",".")."</strong>.";
    }

    $mensagemSaldo = ($credito_permuta > 0 && isset($novo_saldo) && $novo_saldo > 0)
        ? "Seu saldo de permuta restante é <strong>R$ ".number_format($novo_saldo,2,",",".")."</strong>."
        : "";

    $mail->Subject = "Bem-vindo(a) à Rádio Nova FM de Pontal do Paraná!";
    $mail->Body = "
        <h2>Olá, ".htmlspecialchars($empresa)."!</h2>
        <p>Bem-vindo(a) à <strong>Rádio Nova FM 87.5</strong>.</p>
        <p>Plano contratado: <strong>".htmlspecialchars($plano_nome)."</strong></p>
        <p>Dia de vencimento escolhido: <strong>".str_pad($data_vencimento,2,'0',STR_PAD_LEFT)."</strong></p>
        <p>{$valorMsg}</p>
        <p>{$mensagemSaldo}</p>
        <p>Qualquer dúvida, estamos no WhatsApp 41 98877-1752.</p>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Erro ao enviar e-mail para $email: " . $mail->ErrorInfo);
}

// 7️⃣ Finaliza
header("Location: ../clientes.php?success=1");
exit();
