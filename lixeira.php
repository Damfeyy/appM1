<?php
require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?erro=1");
    exit;
}

$stmt = $pdo->query("SELECT * FROM retiradas_lixeira ORDER BY id;");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Lixeira</title>
<link rel="stylesheet" href="tabela.css?v=1.4">
</head>

<body>

<a href="tabela.php" style="float:right; margin:10px;">Voltar</a>
<h2>Lixeira</h2>

<div class="top-menu">

    <div class="menu-buttons">
        <a href="#" id="btnRestaurarSelecao" class="menu-btn">♻ Restaurar Selecionados</a>
        <a href="#" id="btnExcluirPermanente" class="menu-btn delete">🗑 Excluir Permanentemente</a>
    </div>

    <div class="menu-search">
        <select id="filtroCampo">
            <option value="todos">Todos</option>
            <option value="id">ID</option>
            <option value="nome">Nome</option>
            <option value="entregador">Entregador</option>
            <option value="local">Local</option>
            <option value="tipo">Tipo</option>
            <option value="data">Data</option>
        </select>

        <input type="text" id="pesquisa" placeholder="🔍 Pesquisar…">
    </div>

</div>

<table id="tabelaLixeira">
<thead>
<tr>
    <th class="checkbox-cell"><input type="checkbox" id="marcarTodos"></th>

    <th data-campo="id">ID <span class="seta"></span></th>
    <th data-campo="nome">Nome <span class="seta"></span></th>
    <th data-campo="entregador">Entregador <span class="seta"></span></th>
    <th data-campo="local">Local <span class="seta"></span></th>
    <th data-campo="tipo">Tipo <span class="seta"></span></th>
    <th data-campo="data">Data <span class="seta"></span></th>
    <th data-campo="observacao">Obs <span class="seta"></span></th>
</tr>
</thead>

<tbody>
<?php foreach ($registros as $r): ?>
<tr>

    <td class="checkbox-cell">
        <input type="checkbox" class="chk" value="<?= $r['id'] ?>">
    </td>

    <td data-campo="id">
        <a href="formulario.php?id=<?= htmlspecialchars($r['id']) ?>&origem=lixeira"><?= htmlspecialchars($r['id']) ?>
        </a>

    </td>

    <td data-campo="nome"><?= htmlspecialchars($r['nome']) ?></td>
    <td data-campo="entregador"><?= htmlspecialchars($r['entregador']) ?></td>
    <td data-campo="local"><?= htmlspecialchars($r['local']) ?></td>
    <td data-campo="tipo"><?= htmlspecialchars($r['tipo']) ?></td>
    <td data-campo="data"><?= date('d/m/Y', strtotime($r['data'])) ?></td>
    <td data-campo="observacao"><?= htmlspecialchars($r['observacao']) ?></td>

</tr>
<?php endforeach; ?>
</tbody>

</table>

<script src="lixeira.js?v=1.4"></script>
</body>
</html>
