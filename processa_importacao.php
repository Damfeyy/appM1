<?php
require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?erro=1");
    exit;
}

if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    die("Erro no upload do arquivo.");
}

$atualizar = isset($_POST['atualizar']) && $_POST['atualizar'] == '1';

$tmp = $_FILES['arquivo']['tmp_name'];
if (!is_uploaded_file($tmp)) {
    die("Arquivo inválido.");
}

$handle = fopen($tmp, "r");
if (!$handle) die("Não foi possível abrir o arquivo.");

$delim = ",";
$header = null;

while (($line = fgets(stream: $handle)) !== false) {

    $clean = mb_strtoupper(trim(preg_replace('/^\xEF\xBB\xBF/', '', $line)));

    if (strpos($clean, "CODIGO") !== false) {
        $header = str_getcsv($line, $delim);
        break;
    }
}

if (!$header) {
    die("Cabeçalho contendo 'CODIGO' não encontrado no arquivo CSV.");
}

$headerNorm = array_map(fn($h) => mb_strtoupper(trim((string)$h)), $header);


$codigoIdx = array_search("CODIGO", $headerNorm);
$descIdx   = array_search("DESCRICAO DO ITEM", $headerNorm);
$localIdx  = array_search("CHAO FRENTE / TRASEIRA", $headerNorm);

$codigoIdx = $codigoIdx !== false ? $codigoIdx : 0;
$descIdx   = $descIdx   !== false ? $descIdx   : 1;
$localIdx  = $localIdx  !== false ? $localIdx  : 2;

function cell($row, $i) {
    return isset($row[$i]) ? trim((string)$row[$i]) : "";
}

$insertStmt = $pdo->prepare("
    INSERT INTO retiradas (id, nome, entregador, local, tipo, data, observacao)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$existsStmt = $pdo->prepare("SELECT id FROM retiradas WHERE id = ?");
$lixeiraExistsStmt = $pdo->prepare("SELECT id FROM retiradas_lixeira WHERE id = ?");

$pdo->beginTransaction();

$inseridos = 0;
$atualizados = 0;
$pulados = 0;
$linhas = 0;

while (($row = fgetcsv($handle, 0, $delim)) !== false) {

    if (trim(implode("", $row)) === "") continue;

    $linhas++;

    $id = cell($row, $codigoIdx);
    $observacao = cell($row, $descIdx);
    $local = cell($row, $localIdx);

    if ($id === "" || !ctype_digit($id)) {
        $pulados++;
        continue;
    }

    $existsStmt->execute([$id]);
    $existe = $existsStmt->fetch(PDO::FETCH_ASSOC);

    $lixeiraExistsStmt->execute([$id]);
    $existeLixeira = $lixeiraExistsStmt->fetch(PDO::FETCH_ASSOC);

    try {
        if ($existe) {

            if ($atualizar) {
                $upd = $pdo->prepare("UPDATE retiradas SET local=?, observacao=? WHERE id=?");
                $upd->execute([$local, $observacao, $id]);
                $atualizados++;
            } else {
                $pulados++;
            }

        } elseif ($existeLixeira) {

            if ($atualizar) {
                $upd = $pdo->prepare("UPDATE retiradas_lixeira SET local=?, observacao=? WHERE id=?");
                $upd->execute([$local, $observacao, $id]);
                $atualizados++;
            } else {
                $pulados++;
            }

        } else {
            $nome = "";
            $entregador = "";
            $tipo = "";
            $data = date("Y-m-d");

            $insertStmt->execute([$id, $nome, $entregador, $local, $tipo, $data, $observacao]);
            $inseridos++;
        }

    } catch (Exception $e) {
        $pulados++;
        continue;
    }
}

fclose($handle);
$pdo->commit();

header("Location: tabela.php?importados={$inseridos}&atualizados={$atualizados}&pulados={$pulados}&linhas={$linhas}");
exit;

?>
