<?php
require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?erro=1");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    header("Location: lixeira.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM retiradas_lixeira WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {

        $insert = $pdo->prepare("
            INSERT INTO retiradas (produto, quantidade, motivo, data_retirada, hora_retirada)
            VALUES (?, ?, ?, ?, ?)
        ");

        $insert->execute([
            $item['produto'],
            $item['quantidade'],
            $item['motivo'],
            $item['data_retirada'],
            $item['hora_retirada']
        ]);

        $delete = $pdo->prepare("DELETE FROM retiradas_lixeira WHERE id = ?");
        $delete->execute([$id]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
}

header("Location: lixeira.php");
exit;
