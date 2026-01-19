<?php
require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?erro=1");
    exit;
}

$id = $_GET['id'] ?? null;
$origem = $_GET['origem'] ?? "retiradas";

$registro = null;

if ($id) {
    $tabela = $origem === "lixeira" ? "retiradas_lixeira" : "retiradas";

    $stmt = $pdo->prepare("SELECT * FROM {$tabela} WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];
    $origem = $_POST['origem']; 
    $tabela = $origem === "lixeira" ? "retiradas_lixeira" : "retiradas";

    $nome = $_POST['nome'];
    $entregador = $_POST['entregador'];
    $local = $_POST['local'];
    $tipo = $_POST['tipo'];
    $data = $_POST['data'];
    $observacao = $_POST['observacao'] ?? null;

    $check = $pdo->prepare("SELECT id FROM {$tabela} WHERE id = ?");
    $check->execute([$id]);
    $existe = $check->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        
        $stmt = $pdo->prepare("
            UPDATE {$tabela}
            SET nome=?, entregador=?, local=?, tipo=?, data=?, observacao=?
            WHERE id=?
        ");
        $stmt->execute([$nome, $entregador, $local, $tipo, $data, $observacao, $id]);
    } else {
        
        $stmt = $pdo->prepare("
            INSERT INTO {$tabela} (id, nome, entregador, local, tipo, data, observacao)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id, $nome, $entregador, $local, $tipo, $data, $observacao]);
    }

    if ($tabela === "retiradas_lixeira") {
        header("Location: lixeira.php?salvo=1");
    } else {
        header("Location: tabela.php?salvo=1");
    }
    exit;
}


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar Retirada' : 'Nova Retirada' ?></title>
    <link rel="stylesheet" href="formulario.css">
</head>
<body>

    <h2><?= $id ? 'Editar Retirada' : 'Nova Retirada' ?></h2>

    <form method="POST">

        <input type="hidden" name="origem" value="<?= htmlspecialchars($origem) ?>">

        <?php if ($registro): ?>

            <label>ID:</label>
            <input type="hidden" name="id" value="<?= htmlspecialchars($registro['id']) ?>">
            <strong><?= htmlspecialchars($registro['id']) ?></strong><br>
            <?php else: ?>

            <label>ID:</label>
            <input type="number" name="id" required><br>
        <?php endif; ?>



        <label>Nome:</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($registro['nome'] ?? '') ?>"><br>

        <label>Entregador:</label>
        <input type="text" name="entregador" value="<?= htmlspecialchars($registro['entregador'] ?? '') ?>"><br>

        <label>Local:</label>
        <input type="text" name="local" value="<?= htmlspecialchars($registro['local'] ?? '') ?>"><br>

        <label>Tipo:</label>
        <input type="text" name="tipo" value="<?= htmlspecialchars($registro['tipo'] ?? '') ?>"><br>

        <label>Data:</label>
        <input type="date" name="data" required value="<?= htmlspecialchars($registro['data'] ?? '') ?>"><br>

        <label>Observações:</label>
        <textarea name="observacao"><?= htmlspecialchars($registro['observacao'] ?? '') ?></textarea><br>

        <button type="submit">Salvar</button>
        <a href="<?= $origem === 'lixeira' ? 'lixeira.php' : 'tabela.php' ?>">
            <button type="button">Cancelar</button>
        </a>
    </form>
</body>
</html>
