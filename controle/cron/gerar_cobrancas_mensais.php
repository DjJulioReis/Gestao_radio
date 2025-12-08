<?php
require_once 'init.php';
require_once 'email_config.php'; // PHPMailer config

$logFile = __DIR__ . '/cron_log.txt';
$dataHora = date("Y-m-d H:i:s");

// Buscar clientes ativos cujo contrato vence em até 5 dias
$sql = "
    SELECT 
        c.id AS cliente_id,
        c.empresa,
        c.email,
        c.saldo_permuta,
        c.ativo,
        p.id AS plano_id,
        p.nome AS plano_nome,
        p.preco AS plano_valor,
        ct.id AS contrato_id,
        ct.data_fim
    FROM clientes c
    INNER JOIN planos p ON c.plano_id = p.id
    INNER JOIN contratos ct ON ct.cliente_id = c.id
    WHERE c.ativo = 1
      AND DATEDIFF(ct.data_fim, CURDATE()) <= 5
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    while ($cliente = $result->fetch_assoc()) {

        $cliente_id     = $cliente['cliente_id'];
        $empresa        = $cliente['empresa'];
        $email          = $cliente['email'];
        $saldo_permuta  = floatval($cliente['saldo_permuta']);
        $plano_id       = $cliente['plano_id'];
        $plano_nome     = $cliente['plano_nome'];
        $plano_valor    = floatval($cliente['plano_valor']);
        $contrato_id    = $cliente['contrato_id'];
        $vencimento     = $cliente['data_fim'];

        // Referência do mês atual
        $referencia = date("Y-m");

        // Verificar se já existe cobrança no mês
        $checkSql = $conn->prepare("
            SELECT id FROM cobrancas 
            WHERE cliente_id = ? AND referencia = ?
        ");
        $checkSql->bind_param("is", $cliente_id, $referencia);
        $checkSql->execute();
        $checkRes = $checkSql->get_result();

        if ($checkRes->num_rows == 0) {

            // Cálculo do valor a pagar considerando saldo de permuta
            $valorCobrar = $plano_valor;
            $permuta_usada = 0.00;

            if ($saldo_permuta > 0) {
                
                if ($saldo_permuta >= $plano_valor) {
                    $permuta_usada = $plano_valor;
                    $valorCobrar = 0.00;
                    $pago = 1;
                } else {
                    $permuta_usada = $saldo_permuta;
                    $valorCobrar -= $saldo_permuta;
                    $pago = 0;
                }

                // Atualizar o saldo de permuta
                $stmtUpd = $conn->prepare("
                    UPDATE clientes 
                    SET saldo_permuta = saldo_permuta - ? 
                    WHERE id = ?
                ");
                $stmtUpd->bind_param("di", $permuta_usada, $cliente_id);
                $stmtUpd->execute();
                $stmtUpd->close();

            } else {
                $pago = 0;
            }

            // Inserir a cobrança
            $stmtIns = $conn->prepare("
                INSERT INTO cobrancas 
                (contrato_id, cliente_id, plano_id, referencia, valor, pago)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtIns->bind_param(
                "iiisdi",
                $contrato_id,
                $cliente_id,
                $plano_id,
                $referencia,
                $valorCobrar,
                $pago
            );
            $stmtIns->execute();
            $stmtIns->close();

            // Escrever no log
            $mensagem = "$dataHora - Cobrança criada para $empresa | Plano: $plano_nome | Valor Plano: R$ $plano_valor | Permuta Usada: R$ $permuta_usada | Valor a Pagar: R$ $valorCobrar | Vencimento: $vencimento\n";
            file_put_contents($logFile, $mensagem, FILE_APPEND);

            // Enviar e-mail somente se houver valor a pagar
            if ($valorCobrar > 0) {
                $assunto = "Cobrança – Rádio Nova FM de Pontal do Paraná";
                $mensagemEmail = "
                    <h2>Olá, " . htmlspecialchars($empresa) . "!</h2>
                    <p>Plano contratado: <strong>$plano_nome</strong></p>
                    <p>Valor a pagar: <strong>R$ " . number_format($valorCobrar, 2, ",", ".") . "</strong></p>
                    <p>Data de vencimento: <strong>" . date("d/m/Y", strtotime($vencimento)) . "</strong></p>
                    <p>Qualquer dúvida estamos no WhatsApp 41 98877-1752.</p>
                ";
                enviarEmail($email, $assunto, $mensagemEmail);
            }
        }
    }
}

$conn->close();
?>
