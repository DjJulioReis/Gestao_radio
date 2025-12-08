<?php
// após inserir a cobrança no banco
$cliente = $conn->query("SELECT email, empresa FROM clientes WHERE id={$cliente_id}")->fetch_assoc();

$assunto = "Nova Fatura – $referencia";
$mensagem = "
<h2>Olá, {$cliente['empresa']}!</h2>
<p>Sua fatura referente a <strong>$referencia</strong> foi gerada.</p>
<p>Valor: R$ ".number_format($valor,2,",",".")."</p>
<p>Atenciosamente, Nova FM 87.5</p>
";

enviarEmail($cliente['email'], $assunto, $mensagem);
