<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistema de Retiradas</title>
  <link rel="stylesheet" href="formulario.css">
</head>
<body>
  <form action="validar_login.php" method="POST">
    <h2>Login</h2>

    <label for="usuario">Usuário:</label>
    <input type="text" id="usuario" name="usuario" required>

    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="senha" required>

    <button type="submit">Entrar</button>
  </form>
</body>
</html>
