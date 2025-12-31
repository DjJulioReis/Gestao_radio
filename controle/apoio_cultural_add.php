<?php
require_once 'init.php';
$page_title = "Adicionar Novo Projeto Cultural";
require_once 'templates/header.php';

// Apenas administradores podem adicionar projetos
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/apoio_cultural_add_handler.php" method="post">
    <div class="form-group">
        <label for="nome_projeto">Nome do Projeto</label>
        <input type="text" name="nome_projeto" id="nome_projeto" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao" rows="4" class="form-control"></textarea>
    </div>
    <div class="form-group">
        <label for="meta_arrecadacao">Meta de Arrecadação (R$)</label>
        <input type="number" step="0.01" name="meta_arrecadacao" id="meta_arrecadacao" class="form-control">
    </div>
    <div class="form-group">
        <label for="data_inicio">Data de Início</label>
        <input type="date" name="data_inicio" id="data_inicio" class="form-control">
    </div>
    <div class="form-group">
        <label for="data_fim">Data de Fim</label>
        <input type="date" name="data_fim" id="data_fim" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Salvar Projeto</button>
    <a href="apoios_culturais.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php
require_once 'templates/footer.php';
?>
