<?php
require 'conexao.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM retiradas_lixeira WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: lixeira.php");
exit;
