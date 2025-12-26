<?php
// Apenas para execução via linha de comando (CLI)
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.");
}

require_once dirname(__DIR__) . '/init.php';

echo "Iniciando o scheduler de comerciais...\n";

// Define a data para a qual os comerciais serão agendados (D+1)
$target_date = new DateTime('tomorrow');
$target_date_str = $target_date->format('Y-m-d');
$current_month_ref = date('Y-m');

echo "Agendando para a data: {$target_date_str}\n";

// 1. Buscar todos os contratos ativos para a data alvo
$sql_contratos = "SELECT id, cliente_id, plano_id FROM contratos WHERE data_inicio <= ? AND data_fim >= ?";
$stmt_contratos = $conn->prepare($sql_contratos);
$stmt_contratos->bind_param("ss", $target_date_str, $target_date_str);
$stmt_contratos->execute();
$contratos_ativos = $stmt_contratos->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_contratos->close();

if (empty($contratos_ativos)) {
    echo "Nenhum contrato ativo encontrado para a data. Finalizando.\n";
    exit;
}

echo count($contratos_ativos) . " contratos ativos encontrados.\n";

$clientes_adimplentes = [];
$clientes_inadimplentes = [];

// 2. Verificar a adimplência de cada cliente com contrato ativo
$sql_cobranca = "SELECT id FROM cobrancas WHERE cliente_id = ? AND pago = 0 AND referencia < ?";
$stmt_cobranca = $conn->prepare($sql_cobranca);

foreach ($contratos_ativos as $contrato) {
    $cliente_id = $contrato['cliente_id'];

    // Evita verificar o mesmo cliente múltiplas vezes
    if (in_array($cliente_id, $clientes_adimplentes) || in_array($cliente_id, $clientes_inadimplentes)) {
        continue;
    }

    $stmt_cobranca->bind_param("is", $cliente_id, $current_month_ref);
    $stmt_cobranca->execute();
    $result_cobranca = $stmt_cobranca->get_result();

    if ($result_cobranca->num_rows > 0) {
        // Cliente tem cobranças passadas não pagas
        $clientes_inadimplentes[] = $cliente_id;
        echo "Cliente ID {$cliente_id} está INADIMPLENTE.\n";
    } else {
        // Cliente está em dia
        $clientes_adimplentes[] = $cliente_id;
        echo "Cliente ID {$cliente_id} está ADIMPLENTE.\n";
    }
}
$stmt_cobranca->close();


// Próximos passos a serem implementados aqui:
// 3. Para cada contrato de cliente ADIMPLENTE, buscar o número de inserções do plano.
// 4. Buscar todos os comerciais ATIVOS para aquele cliente.
// 5. Distribuir as inserções no horário das 06h às 00h.
// 6. Inserir os agendamentos na tabela `agendamentos`.

echo "Verificação de adimplência concluída.\n";

// Limpa os agendamentos pendentes para a data alvo antes de gerar novos
$sql_delete = "DELETE FROM agendamentos WHERE DATE(horario_programado) = ? AND status = 'pendente'";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("s", $target_date_str);
$stmt_delete->execute();
echo $stmt_delete->affected_rows . " agendamentos pendentes removidos para {$target_date_str}.\n";
$stmt_delete->close();


// 3. Gerar agendamentos para clientes adimplentes
$horario_inicio = $target_date->setTime(6, 0, 0);
$horario_fim = $target_date->setTime(23, 59, 59);
$total_minutos_disponiveis = ($horario_fim->getTimestamp() - $horario_inicio->getTimestamp()) / 60;

$sql_plano = "SELECT insercoes_mes FROM planos WHERE id = ?";
$stmt_plano = $conn->prepare($sql_plano);

$sql_comerciais = "SELECT id FROM comerciais WHERE cliente_id = ? AND ativo = 1";
$stmt_comerciais = $conn->prepare($sql_comerciais);

$sql_insert_agendamento = "INSERT INTO agendamentos (comercial_id, horario_programado, status) VALUES (?, ?, 'pendente')";
$stmt_insert = $conn->prepare($sql_insert_agendamento);

foreach ($contratos_ativos as $contrato) {
    $cliente_id = $contrato['cliente_id'];
    if (!in_array($cliente_id, $clientes_adimplentes)) {
        continue; // Pula clientes inadimplentes
    }

    // Busca inserções do plano
    $stmt_plano->bind_param("i", $contrato['plano_id']);
    $stmt_plano->execute();
    $plano = $stmt_plano->get_result()->fetch_assoc();
    $insercoes_diarias = ceil($plano['insercoes_mes'] / 30); // Simplificação

    // Busca comerciais do cliente
    $stmt_comerciais->bind_param("i", $cliente_id);
    $stmt_comerciais->execute();
    $result_comerciais = $stmt_comerciais->get_result();
    $comerciais_cliente = $result_comerciais->fetch_all(MYSQLI_ASSOC);

    if (empty($comerciais_cliente)) {
        echo "Nenhum comercial ativo para o cliente ID {$cliente_id}. Nenhum agendamento gerado.\n";
        continue;
    }

    echo "Gerando {$insercoes_diarias} agendamentos para o cliente ID {$cliente_id}...\n";

    // Lógica de distribuição
    $intervalo = $total_minutos_disponiveis / $insercoes_diarias;
    for ($i = 0; $i < $insercoes_diarias; $i++) {
        // Seleciona um comercial aleatoriamente
        $comercial_selecionado = $comerciais_cliente[array_rand($comerciais_cliente)];

        // Calcula o horário programado
        $minuto_aleatorio = mt_rand(floor($i * $intervalo), floor(($i + 1) * $intervalo) - 1);
        $horario_programado = clone $horario_inicio;
        $horario_programado->modify("+{$minuto_aleatorio} minutes");
        $horario_programado_str = $horario_programado->format('Y-m-d H:i:s');

        // Insere no banco de dados
        $stmt_insert->bind_param("is", $comercial_selecionado['id'], $horario_programado_str);
        $stmt_insert->execute();
    }
}

$stmt_plano->close();
$stmt_comerciais->close();
$stmt_insert->close();


echo "Scheduler finalizado.\n";
$conn->close();
?>