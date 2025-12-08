<?php
require_once 'init.php';
$page_title = "Adicionar Plano";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/plano_add_handler.php" method="post">
    <div class="form-group">
        <label for="nome">Nome do Plano</label>
        <input type="text" name="nome" id="nome" required>
    </div>
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao" rows="4"></textarea>
    </div>
    <div class="form-group">
        <label for="preco">Preço (R$)</label>
        <input type="number" step="0.01" name="preco" id="preco" required>
    </div>
    <div class="form-group">
        <label for="insercoes_mes">Inserções/Mês</label>
        <input type="number" name="insercoes_mes" id="insercoes_mes" required>
    </div>
    <button type="submit">Salvar</button>
    <a href="planos.php" class="cancel-link">Cancelar</a>
</form>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
