<?php
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado a partir da linha de comando.");
}

require_once __DIR__ . '/init.php'; // init.php agora cuida da conexão

echo "Conexão estabelecida.\n";

$conn->query("SET foreign_key_checks = 0");
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $conn->query("DROP TABLE IF EXISTS `" . $row[0] . "`");
    }
    echo "Tabelas antigas removidas.\n";
}
$conn->query("SET foreign_key_checks = 1");

$sql = file_get_contents(__DIR__ . '/config/schema.sql');
if ($conn->multi_query($sql)) {
    while ($conn->next_result()) {;}
    echo "Esquema do banco de dados criado.\n";
} else {
    echo "Erro ao criar esquema: " . $conn->error . "\n";
    exit(1);
}

$nome = 'Admin User';
$email = 'admin@test.com';
$senha_plana = 'password';
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);
$nivel_acesso = 'admin';

$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $email, $senha_hash, $nivel_acesso);

if ($stmt->execute()) {
    echo "Usuário admin criado.\n";
} else {
    echo "Erro ao criar usuário: " . $stmt->error . "\n";
}
$stmt->close();

// Inserir dados de amostra para clientes
$clientes_amostra = [
    ['Padaria Pão Quente', '11.222.333/0001-44', 'contato@paoquente.com', '11987654321', 'Rua das Flores, 123', 0, 1],
    ['Supermercado Preço Bom', '22.333.444/0001-55', 'compras@precobom.com', '11912345678', 'Avenida Principal, 456', 500.00, 1],
];

$stmt_cliente = $conn->prepare("INSERT INTO clientes (empresa, cnpj_cpf, email, telefone, endereco, credito_permuta, data_cadastro, ativo) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
foreach ($clientes_amostra as $cliente) {
    $stmt_cliente->bind_param("sssssdi", $cliente[0], $cliente[1], $cliente[2], $cliente[3], $cliente[4], $cliente[5], $cliente[6]);
    $stmt_cliente->execute();
}
echo "Clientes de amostra inseridos.\n";
$stmt_cliente->close();

// Inserir dados de amostra para planos
$planos_amostra = [
    ['Plano Essencial', 'Pacote básico de anúncios rotativos.', 150.00, 30],
    ['Plano Plus', 'Mais visibilidade e horários preferenciais.', 300.00, 60],
    ['Plano Premium', 'Destaque total na programação e eventos.', 500.00, 100],
];

$stmt_plano = $conn->prepare("INSERT INTO planos (nome, descricao, preco, insercoes_mes) VALUES (?, ?, ?, ?)");
foreach ($planos_amostra as $plano) {
    $stmt_plano->bind_param("ssdi", $plano[0], $plano[1], $plano[2], $plano[3]);
    $stmt_plano->execute();
}
echo "Planos de amostra inseridos.\n";
$stmt_plano->close();

$conn->close();

echo "Setup concluído!\n";
