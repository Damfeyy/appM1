<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['usuario'])) {
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['ids']) || !is_array($data['ids'])) {
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'IDs inválidos']);
    exit;
}

require 'conexao.php';

$ids = array_filter(array_map('intval', $data['ids']));

if (count($ids) === 0) {
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum ID válido']);
    exit;
}

try {
    $pdo->beginTransaction();

    $sqlInsert = "
        INSERT INTO retiradas_lixeira (id, nome, entregador, local, tipo, data, observacao)
        SELECT id, nome, entregador, local, tipo, data, observacao
        FROM retiradas
        WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
        AND deletado = false
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute($ids);

    $sqlUpdate = "
        UPDATE retiradas
        SET deletado = true
        WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute($ids);

    $pdo->commit();

    ob_end_clean();
    echo json_encode([
        'sucesso' => true,
        'movidos' => $stmtInsert->rowCount(),
        'marcados_deletados' => $stmtUpdate->rowCount()
    ]);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    exit;
}
