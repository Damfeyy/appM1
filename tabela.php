<?php
require 'conexao.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?erro=1");
    exit;
}

$stmt = $pdo->query("SELECT * FROM retiradas WHERE deletado = false ORDER BY id;");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Tabela de Retiradas</title>
  <link rel="stylesheet" href="tabela.css">
</head>
<body>
  <a href="logout.php" class="logout">Sair</a>

  <h2 class="page-title">Tabela de Retiradas</h2>

  <!-- ========== MENU SUPERIOR ========== -->
  <div class="top-menu">

    <div class="menu-buttons">
      <a href="formulario.php" class="menu-btn">+ Adicionar</a>
      <a href="importar_csv
      .php" class="menu-btn">📥 Importar Excel</a>
      <a href="lixeira.php" class="menu-btn delete">🗑 Lixeira</a>

    </div>

    <div class="menu-search">
      <select id="filtroCampo" name="filtroCampo">
        <option value="todos">Todos os campos</option>
        <option value="id">ID</option>
        <option value="nome">Nome</option>
        <option value="entregador">Entregador</option>
        <option value="local">Local</option>
        <option value="tipo">Tipo</option>
        <option value="data">Data</option>
      </select>

      <input type="text" id="pesquisa" placeholder="🔍 Pesquisar..." />
    </div>

  </div>
  <!-- ==================================== -->

  <table id="tabelaRetiradas">
    <thead>
      <tr>
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
          <td data-campo="id"><a href="formulario.php?id=<?= htmlspecialchars($r['id']) ?>"><?= htmlspecialchars($r['id']) ?></a></td>
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

  <script src="select.js"></script>
</body>
</html>
