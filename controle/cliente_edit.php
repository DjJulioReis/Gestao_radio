<?php 
require_once 'init.php';
$page_title = "Editar Cliente";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: clientes.php");
    exit();
}
$cliente = $result->fetch_assoc();

// Garantir valores padrão
$cliente['endereco']       = $cliente['endereco'] ?? '';
$cliente['credito_permuta'] = floatval($cliente['credito_permuta'] ?? 0);
?>

<h1><?php echo htmlspecialchars($page_title, ENT_QUOTES); ?></h1>
<a href="dashboard.php">Voltar para o Início</a>

<form action="src/cliente_edit_handler.php" method="post">
    <input type="hidden" name="cliente_id" value="<?php echo intval($cliente['id']); ?>">

    <div class="form-group">
        <label for="empresa">Empresa</label>
        <input type="text" name="empresa" id="empresa" value="<?php echo htmlspecialchars($cliente['empresa'], ENT_QUOTES); ?>" required>
    </div>

    <div class="form-group">
        <label for="cnpj_cpf">CNPJ/CPF</label>
        <input type="text" name="cnpj_cpf" id="cnpj_cpf" value="<?php echo htmlspecialchars($cliente['cnpj_cpf'], ENT_QUOTES); ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($cliente['email'], ENT_QUOTES); ?>" required>
    </div>

    <div class="form-group">
        <label for="telefone">Telefone</label>
        <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($cliente['telefone'], ENT_QUOTES); ?>">
    </div>

    <div class="form-group">
        <label for="endereco">Endereço</label>
        <input type="text" name="endereco" id="endereco" value="<?php echo htmlspecialchars($cliente['endereco'], ENT_QUOTES); ?>" />
    </div>

    <div class="form-group">
        <label for="credito_permuta">Crédito (Permuta)</label>
        <input type="number" step="0.01" name="credito_permuta" id="credito_permuta" value="<?php echo number_format($cliente['credito_permuta'],2,'.',''); ?>">
    </div>

    <div class="form-group">
        <label for="data_cadastro">Data de Cadastro</label>
        <input type="text" id="data_cadastro" value="<?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'])); ?>" readonly>
    </div>

    <div class="form-group">
        <label for="ativo">Status</label>
        <select name="ativo" id="ativo">
            <option value="1" <?php echo ($cliente['ativo'] == 1 ? 'selected' : ''); ?>>Ativo</option>
            <option value="0" <?php echo ($cliente['ativo'] == 0 ? 'selected' : ''); ?>>Inativo</option>
        </select>
    </div>

    <button type="submit">Salvar Alterações</button>
    <a href="clientes.php" class="cancel-link">Cancelar</a>
</form>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>

<script>
// Formatação CNPJ/CPF
document.getElementById('cnpj_cpf').addEventListener('input', function (e) {
    var value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/,'$1.$2')
                     .replace(/(\d{3})(\d)/,'$1.$2')
                     .replace(/(\d{3})(\d{1,2})$/,'$1-$2');
    } else {
        value = value.replace(/^(\d{2})(\d)/,'$1.$2')
                     .replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3')
                     .replace(/\.(\d{3})(\d)/,'.$1/$2')
                     .replace(/(\d{4})(\d)/,'$1-$2');
    }
    e.target.value = value;
});

// Formatação telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length <= 10) value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    else value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    e.target.value = value;
});

// Padronizar email
document.getElementById('email').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\s/g,'').toLowerCase();
});
</script>
