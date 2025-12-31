<?php
require_once 'init.php';
$page_title = "Apoios Culturais";
require_once 'templates/header.php';

// Busca todos os projetos culturais
$stmt = $conn->prepare("SELECT id, nome_projeto, meta_arrecadacao, data_inicio, data_fim FROM apoios_culturais ORDER BY data_criacao DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php" class="btn btn-secondary">Voltar para o Dashboard</a>
<a href="apoio_cultural_add.php" class="btn btn-primary">Adicionar Novo Projeto</a>

<table class="table mt-4">
    <thead>
        <tr>
            <th>Nome do Projeto</th>
            <th>Meta de Arrecadação (R$)</th>
            <th>Data de Início</th>
            <th>Data de Fim</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($projeto = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($projeto['nome_projeto']); ?></td>
                    <td><?php echo number_format($projeto['meta_arrecadacao'], 2, ',', '.'); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($projeto['data_inicio'])); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($projeto['data_fim'])); ?></td>
                    <td>
                        <a href="apoio_cultural_view.php?id=<?php echo $projeto['id']; ?>" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                        <!-- Futuramente: Editar e Excluir -->
                        <!-- <a href="apoio_cultural_edit.php?id=<?php echo $projeto['id']; ?>" title="Editar"><i class="fas fa-pencil-alt"></i></a> -->
                        <!-- <a href="src/apoio_cultural_delete_handler.php?id=<?php echo $projeto['id']; ?>" onclick="return confirm('Tem certeza?');" title="Excluir"><i class="fas fa-trash-alt"></i></a> -->
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhum projeto cultural encontrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>
