<?php 
require_once 'init.php';
$page_title = "Adicionar Cliente";

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

require_once 'templates/header.php';
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Inicio</a>

<form action="src/cliente_add_handler.php" method="post">

    <div class="form-group">
        <label for="empresa">Empresa</label>
        <input type="text" name="empresa" id="empresa" required>
    </div>

    <div class="form-group">
        <label for="cnpj_cpf">CNPJ/CPF</label>
        <input type="text" name="cnpj_cpf" id="cnpj_cpf" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div class="form-group">
        <label for="telefone">Telefone</label>
        <input oninput="mascaraTelefone(this)"  type="text" name="telefone" id="telefone">
    </div>

    <div class="form-group">
        <label for="endereco">Endereço</label>
        <input type="text" name="endereco" id="endereco">
    </div>

    <div class="form-group">
        <label for="credito_permuta">Crédito (Permuta)</label>
        <input type="number" step="0.01" name="credito_permuta" id="credito_permuta" value="0.00">
    </div>

    <div class="form-group">
        <label for="data_cadastro">Data de Cadastro</label>
        <input type="date" name="data_cadastro" id="data_cadastro" required>
    </div>

    <!-- NOVO CAMPO: DATA DE VENCIMENTO -->
    <div class="form-group">
        <label for="data_vencimento">Dia de vencimento</label>
        <select name="data_vencimento" id="data_vencimento" required>
            <?php 
                $datas = [1, 10, 20];
                foreach ($datas as $d) {
                    $label = str_pad($d, 2, '0', STR_PAD_LEFT);
                    $sel = ($d == 10) ? "selected" : "";
                    echo "<option value='$d' $sel>$label</option>";
                }
            ?>
        </select>
    </div>

    <?php
    $planos = $conn->query("SELECT id, nome FROM planos ORDER BY nome");
    ?>

    <div class="form-group">
        <label for="plano_id">Plano</label>
        <select name="plano_id" id="plano_id" required>
            <option value="">Selecione um plano</option>
            <?php while ($p = $planos->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>">
                    <?php echo htmlspecialchars($p['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <button type="submit">Salvar</button>
    <a href="clientes.php" class="cancel-link">Cancelar</a>

</form>

<?php
require_once 'templates/footer.php';
?>
<script>
document.getElementById('cnpj_cpf').addEventListener('input', function (e) {
    var value = e.target.value;
    var rawValue = value.replace(/\D/g, ''); // Remove tudo que não for dígito
    var formattedValue;

    if (rawValue.length <= 11) {
        // Formato CPF: 000.000.000-00
        formattedValue = rawValue.replace(/(\d{3})(\d)/, '$1.$2')
                                .replace(/(\d{3})(\d)/, '$1.$2')
                                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        // Formato CNPJ: 00.000.000/0000-00
        formattedValue = rawValue.replace(/^(\d{2})(\d)/, '$1.$2')
                                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                                .replace(/(\d{4})(\d)/, '$1-$2');
    }

    e.target.value = formattedValue;
});

document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // só números
    if (value.length > 11) value = value.slice(0, 11); // limita máximo 11 dígitos

    if (value.length <= 10) {
        // formato (00) 0000-0000
        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else {
        // formato (00) 00000-0000
        value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    }

    e.target.value = value;
});

document.getElementById('email').addEventListener('input', function(e) {
    let value = e.target.value;
    // remove espaços e deixa tudo minúsculo
    value = value.replace(/\s/g, '').toLowerCase();
    e.target.value = value;
});

</script>