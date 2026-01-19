<?php
require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?erro=1");
    exit;
}

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (!$ids) {
    header("Location: lixeira.php");
    exit;
}

$lista = array_filter(array_map('intval', explode(',', $ids)));

if (empty($lista)) {
    header("Location: lixeira.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $placeholders = implode(",", array_fill(0, count($lista), "?"));
    $stmt = $pdo->prepare("UPDATE retiradas SET deletado = FALSE WHERE id IN ($placeholders)");
    $stmt->execute($lista);

    $stmt2 = $pdo->prepare("DELETE FROM retiradas_lixeira WHERE id IN ($placeholders)");
    $stmt2->execute($lista);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
}

header("Location: lixeira.php");
exit;
