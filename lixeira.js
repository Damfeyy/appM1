// lixeira.js v1.6
document.addEventListener("DOMContentLoaded", () => {
  const tabela = document.getElementById("tabelaLixeira");
  if (!tabela) return;
  const tbody = tabela.querySelector("tbody");
  const ths = tabela.querySelectorAll("thead th[data-campo]");

  const btnRestaurar = document.getElementById("btnRestaurarSelecao");
  const btnExcluirPermanente = document.getElementById("btnExcluirPermanente");
  const marcarTodos = document.getElementById("marcarTodos");
  const campoFiltro = document.getElementById("filtroCampo");
  const campoPesquisa = document.getElementById("pesquisa");

  let ordem = {};
  let modoSelecaoAtivo = false;

  // ---- helper: mostra / oculta coluna de checkboxes ----
  function showCheckboxes(show) {
    // header checkbox cell
    const header = tabela.querySelector("thead tr");
    const thBox = header.querySelector(".checkbox-cell");
    if (thBox) thBox.style.display = show ? "" : "none";

    // each row checkbox cell
    tbody.querySelectorAll(".checkbox-cell").forEach((td) => {
      td.style.display = show ? "" : "none";
    });

    // se escondendo, limpa seleção visual e checkboxes
    if (!show) {
      document.querySelectorAll(".chk").forEach((c) => {
        c.checked = false;
      });
      atualizarVisualLinhas();
    }
  }

  // ---- helper: atualizar visual das linhas (selected) ----
  function atualizarVisualLinhas() {
    tbody.querySelectorAll("tr").forEach((tr) => {
      const chk = tr.querySelector(".chk");
      if (chk && chk.checked) tr.classList.add("selected");
      else tr.classList.remove("selected");
    });
  }

  // initialize: hide checkboxes by default
  showCheckboxes(false);

  // -------------------- ORDENAÇÃO --------------------
  ths.forEach((th) => {
    th.style.cursor = "pointer";
    th.addEventListener("click", (e) => {
      // if clicking checkbox cell or inputs, ignore
      if (e.target.closest(".checkbox-cell")) return;
      const campo = th.dataset.campo;
      ordem[campo] = ordem[campo] === "asc" ? "desc" : "asc";
      ordenarTabela(campo, ordem[campo]);
      ths.forEach((t) => (t.querySelector(".seta").textContent = ""));
      th.querySelector(".seta").textContent =
        ordem[campo] === "asc" ? "▲" : "▼";
    });
  });

  function ordenarTabela(campo, direcao) {
    const linhas = Array.from(tbody.querySelectorAll("tr"));
    linhas.sort((a, b) => {
      const tdA = a.querySelector(`td[data-campo="${campo}"]`);
      const tdB = b.querySelector(`td[data-campo="${campo}"]`);
      let A = tdA ? tdA.innerText.trim() : "";
      let B = tdB ? tdB.innerText.trim() : "";

      // tentar numérico (remove não dígitos, mas cuidado com datas)
      const numA = Number(A.replace(/\D/g, ""));
      const numB = Number(B.replace(/\D/g, ""));
      if (!isNaN(numA) && !isNaN(numB) && String(numA) !== "") {
        return direcao === "asc" ? numA - numB : numB - numA;
      }

      // datas no formato dd/mm/YYYY
      if (campo === "data") {
        const parse = (s) => {
          if (!s) return new Date(0);
          const p = s.split("/");
          if (p.length !== 3) return new Date(s);
          return new Date(`${p[2]}-${p[1]}-${p[0]}`);
        };
        const da = parse(A),
          db = parse(B);
        if (da < db) return direcao === "asc" ? -1 : 1;
        if (da > db) return direcao === "asc" ? 1 : -1;
        return 0;
      }

      A = A.toLowerCase();
      B = B.toLowerCase();
      if (A < B) return direcao === "asc" ? -1 : 1;
      if (A > B) return direcao === "asc" ? 1 : -1;
      return 0;
    });

    // reappend
    linhas.forEach((l) => tbody.appendChild(l));
  }

  // -------------------- PESQUISA / FILTRO --------------------
  if (campoPesquisa) campoPesquisa.addEventListener("input", filtrar);
  if (campoFiltro) campoFiltro.addEventListener("change", filtrar);

  function filtrar() {
    const termo = (campoPesquisa.value || "").toLowerCase();
    const filtro = campoFiltro ? campoFiltro.value : "todos";

    tbody.querySelectorAll("tr").forEach((tr) => {
      if (!termo) {
        tr.style.display = "";
        return;
      }
      if (filtro === "todos") {
        tr.style.display = tr.innerText.toLowerCase().includes(termo)
          ? ""
          : "none";
      } else {
        const td = tr.querySelector(`td[data-campo="${filtro}"]`);
        const texto = td ? td.innerText.toLowerCase() : "";
        tr.style.display = texto.includes(termo) ? "" : "none";
      }
    });
  }

  // -------------------- MARCAR TODOS --------------------
  if (marcarTodos) {
    marcarTodos.addEventListener("change", () => {
      const checked = marcarTodos.checked;
      // ensure only visible checkboxes are toggled
      tbody.querySelectorAll(".chk").forEach((ch) => {
        // toggle only if its cell is visible
        const td = ch.closest(".checkbox-cell");
        if (!td || td.style.display === "none") return;
        ch.checked = checked;
      });
      atualizarVisualLinhas();
    });
  }

  // -------------------- LINHAS: dblclick e click --------------------
  function ativarComportamentoLinhas() {
    tbody.querySelectorAll("tr").forEach((tr) => {
      // remove antigos handlers (se existirem)
      if (tr._dblclickHandler)
        tr.removeEventListener("dblclick", tr._dblclickHandler);
      if (tr._clickHandler) tr.removeEventListener("click", tr._clickHandler);

      const dbl = (e) => {
        // se clicou em input ou link, não trate aqui
        const tag = e.target.tagName;
        if (tag === "INPUT" || tag === "A") return;
        // alterna checkbox da linha (visível)
        const chk = tr.querySelector(".chk");
        if (
          chk &&
          tr.querySelector(".checkbox-cell").style.display !== "none"
        ) {
          chk.checked = !chk.checked;
          atualizarVisualLinhas();
        }
      };

      const click = (e) => {
        // se clicou no checkbox em si, apenas atualiza visual
        if (e.target.tagName === "INPUT") {
          atualizarVisualLinhas();
          return;
        }

        // se modo seleção ativo: clique em link deve apenas alternar checkbox e impedir navegação
        if (modoSelecaoAtivo) {
          const link = tr.querySelector('td[data-campo="id"] a');
          if (link && (e.target === link || link.contains(e.target))) {
            const chk = tr.querySelector(".chk");
            if (chk) chk.checked = !chk.checked;
            atualizarVisualLinhas();
            e.preventDefault();
          }
          return;
        }

        // modo seleção não ativo: se clicou no link do ID, deixa navegador agir (abre formulario)
        // se clicou na célula id sem link, navega manualmente
        const idCell = tr.querySelector('td[data-campo="id"]');
        if (idCell && idCell.contains(e.target) && !idCell.querySelector("a")) {
          const idVal = idCell.innerText.trim();
          if (idVal)
            window.location.href = `formulario.php?id=${encodeURIComponent(
              idVal
            )}`;
        }
      };

      tr.addEventListener("dblclick", dbl);
      tr.addEventListener("click", click);
      tr._dblclickHandler = dbl;
      tr._clickHandler = click;
    });
  }

  ativarComportamentoLinhas();

  // re-activate when DOM changes (sorting, filtering)
  const observer = new MutationObserver(() => {
    ativarComportamentoLinhas();
    atualizarVisualLinhas();
    // ensure checkboxes visibility stays consistent
    showCheckboxes(modoSelecaoAtivo);
  });
  observer.observe(tbody, { childList: true, subtree: true });

  // -------------------- MODO SELEÇÃO (expose toggle) --------------------
  // Use double-click to toggle selection mode globally on the table header area:
  tabela.addEventListener("dblclick", (e) => {
    // don't toggle if double-click happened on a link or input
    if (e.target.tagName === "A" || e.target.tagName === "INPUT") return;
    modoSelecaoAtivo = !modoSelecaoAtivo;
    tabela.classList.toggle("modo-selecao", modoSelecaoAtivo);
    showCheckboxes(modoSelecaoAtivo);
  });

  // -------------------- AÇÕES (RESTAURAR / EXCLUIR) --------------------
  if (btnRestaurar)
    btnRestaurar.addEventListener("click", () => {
      const ids = coletarSelecionados();
      if (ids.length === 0) return alert("Nenhum item selecionado!");
      if (!confirm("Restaurar itens selecionados?")) return;
      window.location.href = "restaurar_varios.php?ids=" + ids.join(",");
    });

  if (btnExcluirPermanente)
    btnExcluirPermanente.addEventListener("click", () => {
      const ids = coletarSelecionados();
      if (ids.length === 0) return alert("Nenhum item selecionado!");
      if (!confirm("Excluir permanentemente? Esta ação não pode ser desfeita."))
        return;
      window.location.href =
        "excluir_definitivo_varios.php?ids=" + ids.join(",");
    });

  function coletarSelecionados() {
    return Array.from(tbody.querySelectorAll(".chk:checked")).map(
      (ch) => ch.value
    );
  }
});
