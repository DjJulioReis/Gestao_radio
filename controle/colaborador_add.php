<?php
require_once 'init.php';
$page_title = "Adicionar Colaborador";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/colaborador_add_handler.php" method="post">
    <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div class="form-group">
        <label for="telefone">Telefone</label>
        <input type="text" name="telefone" id="telefone" required>
    </div>
    <div class="form-group">
        <label for="funcao">Função</label>
        <select name="funcao" id="funcao" required>
            <option value="locutor">Locutor</option>
            <option value="socio">Sócio</option>
            <option value="socio_locutor">Sócio e Locutor</option>
            <option value="parceiro">Parceiro</option>
        </select>
    </div>
    <button type="submit">Salvar</button>
    <a href="colaboradores.php" class="cancel-link">Cancelar</a>
</form>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
