<?php
session_start();
require 'conexao.php';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados && password_verify($senha, $dados['senha'])) {
        $_SESSION['usuario'] = $dados['usuario'];
        header("Location: tabela.php");
        exit;
    } else {
        header("Location: index.php?erro=1");
        exit;
    }

} catch (PDOException $e) {
    header("Location: index.php?erro=1");
    exit;
}
?>
