<?php
require_once "../init.php";
require_once "../email_config.php"; // PHPMailer

// Buscar clientes inadimplentes (somente ativos)
$sql = "
    SELECT 
        c.id AS cliente_id,
        c.empresa,
        c.plano_valor,
        l.nome AS locutor,
        l.email AS locutor_email,
        cm.referencia AS data_vencimento
    FROM cobrancas cm
    JOIN clientes c ON c.id = cm.cliente_id
    JOIN clientes_locutores cl ON cl.cliente_id = c.id
    JOIN locutores l ON l.id = cl.locutor_id
    WHERE cm.pago = 0
      AND cm.referencia < DATE_FORMAT(CURDATE(), '%Y-%m')
      AND c.ativo = 1
";

$res = $conn->query($sql);

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {

        $valor = number_format($row['plano_valor'], 2, ",", ".");

        $mensagem = "
            Olá {$row['locutor']},<br><br>

            O cliente <strong>{$row['empresa']}</strong> está com pagamento atrasado.<br>
            Vencimento: <strong>{$row['data_vencimento']}</strong><br>
            Valor: <strong>R$ {$valor}</strong><br><br>

            Atenciosamente,<br>
            Sistema de Cobranças
        ";

        // Usando PHPMailer
        enviarEmail($row['locutor_email'], "Cliente inadimplente", $mensagem);
    }
}

echo "ok";
?>
