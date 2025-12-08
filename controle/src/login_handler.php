<?php
// Inclui o arquivo de inicialização
require_once '../init.php';

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a consulta SQL para buscar o usuário
    $stmt = $conn->prepare("SELECT id, nome, senha, nivel_acesso FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o usuário foi encontrado e a senha está correta
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_level'] = $user['nivel_acesso'];

            // Redireciona para o dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Senha inválida
            header("Location: ../login.php?error=1");
            exit();
        }
    } else {
        // Usuário não encontrado
        header("Location: ../login.php?error=1");
        exit();
    }
} else {
    // Se não for uma requisição POST, redireciona para o login
    header("Location: ../login.php");
    exit();
}
