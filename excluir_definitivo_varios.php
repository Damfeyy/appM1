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
    $placeholders = implode(",", array_fill(0, count($lista), "?"));

    $stmt = $pdo->prepare("DELETE FROM retiradas_lixeira WHERE id IN ($placeholders)");
    $stmt->execute($lista);

} catch (PDOException $e) {
    
}

header("Location: lixeira.php");
exit;
