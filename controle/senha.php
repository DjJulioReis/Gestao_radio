<?php
// Senha original
$senha = "paulo!@#";

// Criptografa a senha usando bcrypt
$senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

// Exibe a senha criptografada
echo "Senha criptografada: " . $senha_criptografada;
?>