<?php
require_once 'init.php';
require_once 'email_config2.php';

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
