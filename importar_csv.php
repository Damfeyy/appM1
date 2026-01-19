<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Importar CSV</title>
<link rel="stylesheet" href="formulario.css">
</head>
<body>

<h2>Importar Arquivo CSV</h2>

<div class="container">

    <form class="formulario" action="processa_importacao.php" method="POST" enctype="multipart/form-data">

        <label>Selecione o arquivo CSV:</label>
        <input type="file" name="arquivo" id="arquivo" accept=".csv" required>

        <label>Atualizar registros existentes?</label>
        <select name="atualizar">
            <option value="0">Não — pular registros existentes</option>
            <option value="1">Sim — atualizar registros existentes</option>
        </select>

        <label>Delimitador do arquivo:</label>
        <select name="delimitador" id="delimitador">
            <option value="auto">Detectar automaticamente</option>
            <option value=",">Vírgula</option>
            <option value=";">Ponto e vírgula</option>
        </select>

        <button type="submit">Importar</button>
        <a href="tabela.php">
            <button type="button">Cancelar</button>
        </a>
    </form>


    <h3>Pré-visualização do Arquivo</h3>
    <p class="msg-preview">Primeiras 10 linhas do CSV.</p>

    <table class="preview-table" id="previewTable" style="display:none;"></table>

</div>

<script>
document.getElementById("arquivo").addEventListener("change", function() {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();

    reader.onload = function(e) {
        const text = e.target.result;
        const delim = document.getElementById("delimitador").value;

        let detectedDelim = ",";
        if (delim === "auto") {
            const firstLine = text.split("\n")[0];
            const commaCount = (firstLine.match(/,/g) || []).length;
            const semiCount = (firstLine.match(/;/g) || []).length;
            detectedDelim = semiCount > commaCount ? ";" : ",";
        } else {
            detectedDelim = delim;
        }

        const lines = text.split("\n").slice(0, 10); 
        const table = document.getElementById("previewTable");
        table.innerHTML = "";
        table.style.display = "table";

        lines.forEach((line, idx) => {
            if (!line.trim()) return;

            const row = document.createElement("tr");
            const cols = line.split(detectedDelim);

            cols.forEach(col => {
                const cell = document.createElement(idx === 0 ? "th" : "td");
                cell.textContent = col.trim();
                row.appendChild(cell);
            });

            table.appendChild(row);
        });
    };

    reader.readAsText(file);
});
</script>

</body>
</html>
