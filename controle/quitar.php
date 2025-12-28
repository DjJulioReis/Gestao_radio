<?php
require_once 'init.php';
require_once 'email_config.php';

$id = intval($_GET['id']);

$sql = "SELECT cb.*, cl.credito_permuta, cl.email, cl.empresa, cl.id AS cliente_id
        FROM cobrancas cb
        INNER JOIN clientes cl ON cb.cliente_id = cl.id
        WHERE cb.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();
$cobranca = $stmt->get_result()->fetch_assoc();

if(!$cobranca){ header("Location: financeiro.php"); exit(); }

$valor = $cobranca['valor'];
$permuta = $cobranca['credito_permuta'];
$cliente_id = $cobranca['cliente_id'];
$referencia = $cobranca['referencia'];

// Abater permuta
if($permuta > 0){
    if($permuta >= $valor){
        $novoPermuta = $permuta - $valor;
        $valor = 0;
    } else {
        $valor -= $permuta;
        $novoPermuta = 0;
    }
    $stmt2 = $conn->prepare("UPDATE clientes SET credito_permuta=? WHERE id=?");
    $stmt2->bind_param("di",$novoPermuta,$cliente_id);
    $stmt2->execute();
}

// Sempre quitar a cobrança, independentemente da permuta
$stmt3 = $conn->prepare("UPDATE cobrancas SET pago=1, data_pagamento=NOW() WHERE id=?");
$stmt3->bind_param("i",$id);
$stmt3->execute();

// Enviar e-mail de confirmação apenas se o pagamento foi efetivamente processado
if ($stmt3->affected_rows > 0) {
    // Lógica para reinvestir comissão de sócio-locutor
    $sql_colab = "
        SELECT
            col.id AS colaborador_id,
            col.funcao,
            cc.percentual_comissao,
            s.id AS socio_data_id,
            s.reinvestir_comissao
        FROM cliente_colaboradores cc
        JOIN colaboradores col ON cc.colaborador_id = col.id
        LEFT JOIN socios s ON col.id = s.colaborador_id
        WHERE cc.cliente_id = ?
    ";
    $stmt_colab = $conn->prepare($sql_colab);
    $stmt_colab->bind_param("i", $cliente_id);
    $stmt_colab->execute();
    $result_colab = $stmt_colab->get_result();

    if ($result_colab->num_rows > 0) {
        $colaborador = $result_colab->fetch_assoc();

        // Verifica se é sócio-locutor e se o reinvestimento está ativo
        if ($colaborador['funcao'] === 'socio_locutor' && $colaborador['reinvestir_comissao'] == 1) {
            $valor_cobranca = $cobranca['valor'];
            $percentual = $colaborador['percentual_comissao'];
            $valor_comissao = ($valor_cobranca * $percentual) / 100;

            if ($valor_comissao > 0) {
                // 1. Inserir em investimentos_socios
                // A coluna `socio_id` na tabela `investimentos_socios` refere-se ao ID da tabela `socios`
                $descricao_investimento = "Comissão reinvestida da cobrança #{$id} ({$cobranca['empresa']})";
                $sql_insert_invest = "INSERT INTO investimentos_socios (socio_id, tipo, valor, data, descricao) VALUES (?, 'investimento', ?, NOW(), ?)";
                $stmt_insert_invest = $conn->prepare($sql_insert_invest);
                $stmt_insert_invest->bind_param("ids", $colaborador['socio_data_id'], $valor_comissao, $descricao_investimento);
                $stmt_insert_invest->execute();
                $stmt_insert_invest->close();

                // 2. Atualizar saldo em socios
                $sql_update_saldo = "UPDATE socios SET saldo_investido = saldo_investido + ? WHERE colaborador_id = ?";
                $stmt_update_saldo = $conn->prepare($sql_update_saldo);
                $stmt_update_saldo->bind_param("di", $valor_comissao, $colaborador['colaborador_id']);
                $stmt_update_saldo->execute();
                $stmt_update_saldo->close();
            }
        }
    }
    $stmt_colab->close();


    $assunto = "Pagamento Confirmado – $referencia";
    $mensagem = "
        <h2>Olá, {$cobranca['empresa']}!</h2>
        <p>Recebemos o pagamento da sua fatura referente a <strong>$referencia</strong>.</p>
        <p>Valor quitado: R$ ".number_format($cobranca['valor'],2,",",".")."</p>
        <p>Agradecemos a parceria!</p>
        <p><strong>Nova FM 87.5</strong></p>
    ";
    enviarEmail($cobranca['email'], $assunto, $mensagem);
}

header("Location: financeiro.php?ok=1");
exit();
