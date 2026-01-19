<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: tabela.php");
    exit;
}

$erro = isset($_GET['erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - appM</title>
  <link rel="stylesheet" href="formulario.css">
</head>
<body>
  <form method="POST" action="validar_login.php">
    <h2>Login do Sistema</h2>

    <?php if ($erro): ?>
      <div class="erro">⚠️ Usuário ou senha incorretos.</div>
    <?php endif; ?>

    <label>Usuário:</label>
    <input type="text" name="usuario" required>

    <label>Senha:</label>
    <input type="password" name="senha" required>

    <button type="submit">Entrar</button>
  </form>
</body>
</html>
