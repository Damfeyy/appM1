document.addEventListener("DOMContentLoaded", () => {
  const tabela = document.getElementById("tabelaRetiradas");
  if (!tabela) return;

  const tbody = tabela.querySelector("tbody");
  const filtroCampo = document.getElementById("filtroCampo");
  const pesquisaInput = document.getElementById("pesquisa");

  let btnExcluir = document.getElementById("btnExcluir");
  let btnCancelar = document.getElementById("btnCancelar");
  if (!btnExcluir) {
    btnExcluir = document.createElement("button");
    btnExcluir.id = "btnExcluir";
    btnExcluir.textContent = "Excluir Selecionados";
    btnExcluir.style.display = "none";
    document.body.appendChild(btnExcluir);
  }
  if (!btnCancelar) {
    btnCancelar = document.createElement("button");
    btnCancelar.id = "btnCancelar";
    btnCancelar.textContent = "Cancelar Seleção";
    btnCancelar.style.display = "none";
    document.body.appendChild(btnCancelar);
  }

  let modoSelecao = false;
  let selecionados = new Set();
  let registrosOriginais = [];
  let ordenacao = { coluna: null, direcao: 1 };

  function lerTabela() {
    registrosOriginais = Array.from(tbody.querySelectorAll("tr")).map((tr) => {
      const tds = tr.querySelectorAll("td");
      return {
        id: tds[0].textContent.trim(),
        nome: tds[1].textContent.trim(),
        entregador: tds[2].textContent.trim(),
        local: tds[3].textContent.trim(),
        tipo: tds[4].textContent.trim(),
        data: tds[5].textContent.trim(),
        observacao: tds[6] ? tds[6].textContent.trim() : "",
      };
    });
  }

  function renderTabela(lista) {
    tbody.innerHTML = "";
    lista.forEach((r) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="id-cell">${r.id}</td>
        <td>${escapeHtml(r.nome)}</td>
        <td>${escapeHtml(r.entregador)}</td>
        <td>${escapeHtml(r.local)}</td>
        <td>${escapeHtml(r.tipo)}</td>
        <td>${escapeHtml(r.data)}</td>
        <td>${escapeHtml(r.observacao)}</td>
      `;

      tr.querySelector(".id-cell").addEventListener("click", (ev) => {
        if (!modoSelecao) {
          window.location.href = `formulario.php?id=${encodeURIComponent(
            r.id
          )}`;
        }
      });

      tr.addEventListener("dblclick", () => {
        modoSelecao = !modoSelecao;
        atualizarInterface();
      });

      tbody.appendChild(tr);
    });

    if (modoSelecao) adicionarCheckboxes();
    atualizarSetasVisual();
  }

  function adicionarCheckboxes() {
    const header = tabela.querySelector("thead tr");
    if (!header.querySelector(".checkbox-cell")) {
      const th = document.createElement("th");
      th.classList.add("checkbox-cell");
      header.prepend(th);
    }

    tbody.querySelectorAll("tr").forEach((linha) => {
      if (linha.querySelector(".checkbox-cell")) return;
      const checkboxCell = document.createElement("td");
      checkboxCell.classList.add("checkbox-cell");

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.dataset.id = linha.querySelector(".id-cell").textContent.trim();

      checkbox.addEventListener("change", (e) => {
        const id = e.target.dataset.id;
        if (e.target.checked) selecionados.add(id);
        else selecionados.delete(id);
        atualizarInterface();
      });

      checkboxCell.appendChild(checkbox);
      linha.prepend(checkboxCell);
    });
  }

  function atualizarInterface() {
    if (modoSelecao) {
      tabela.classList.add("modo-selecao");
      btnExcluir.style.display = "inline-block";
      btnCancelar.style.display = "inline-block";
      adicionarCheckboxes();
    } else {
      tabela.classList.remove("modo-selecao");
      const header = tabela.querySelector("thead tr");
      const th = header.querySelector(".checkbox-cell");
      if (th) th.remove();
      tbody.querySelectorAll(".checkbox-cell").forEach((td) => td.remove());
      selecionados.clear();
      btnExcluir.style.display = "none";
      btnCancelar.style.display = "none";
    }
  }

  btnExcluir.addEventListener("click", () => {
    if (selecionados.size === 0) {
      alert("Nenhum registro selecionado!");
      return;
    }
    if (!confirm(`Deseja excluir ${selecionados.size} registro(s)?`)) return;

    fetch("excluir.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ids: Array.from(selecionados) }),
    })
      .then((res) => res.text())
      .then((txt) => {
        let data = null;
        try {
          data = JSON.parse(txt);
        } catch (err) {
          console.error("Resposta não é JSON. Conteúdo recebido:", txt);
          alert(
            "Erro inesperado ao excluir (resposta inválida do servidor). Verifique o console."
          );
          return;
        }
        if (data.sucesso) {
          location.reload();
        } else {
          alert("Erro ao excluir: " + (data.erro || "erro desconhecido"));
        }
      })
      .catch((err) => {
        console.error("Erro no fetch:", err);
        alert("Erro na requisição de exclusão (ver console).");
      });
  });

  btnCancelar.addEventListener("click", () => {
    modoSelecao = false;
    atualizarInterface();
  });

  pesquisaInput.addEventListener("input", aplicarFiltros);
  if (filtroCampo) filtroCampo.addEventListener("change", aplicarFiltros);

  function aplicarFiltros() {
    const termo = (pesquisaInput.value || "").toLowerCase();
    const campo = filtroCampo ? filtroCampo.value : "todos";

    let filtrados = registrosOriginais.filter((r) => {
      if (!termo) return true;
      if (campo === "todos") {
        return Object.values(r).some((v) =>
          String(v || "")
            .toLowerCase()
            .includes(termo)
        );
      }
      return String(r[campo] || "")
        .toLowerCase()
        .includes(termo);
    });

    aplicarOrdenacao(filtrados, true);
  }

  tabela.querySelectorAll("thead th[data-campo]").forEach((th) => {
    th.addEventListener("click", () => {
      const campo = th.getAttribute("data-campo");
      if (ordenacao.coluna === campo) ordenacao.direcao *= -1;
      else ordenacao = { coluna: campo, direcao: 1 };

      const termo = (pesquisaInput.value || "").toLowerCase();
      const campoFiltro = filtroCampo ? filtroCampo.value : "todos";
      let base = registrosOriginais;
      if (termo) {
        base = registrosOriginais.filter((r) => {
          if (campoFiltro === "todos")
            return Object.values(r).some((v) =>
              String(v || "")
                .toLowerCase()
                .includes(termo)
            );
          return String(r[campoFiltro] || "")
            .toLowerCase()
            .includes(termo);
        });
      }
      aplicarOrdenacao(base, true);
    });
  });

  function aplicarOrdenacao(lista, renderAfter = true) {
    if (!ordenacao.coluna) {
      if (renderAfter) renderTabela(lista);
      return;
    }

    lista.sort((a, b) => {
      let va = a[ordenacao.coluna] || "";
      let vb = b[ordenacao.coluna] || "";

      if (ordenacao.coluna === "id" && !isNaN(va) && !isNaN(vb)) {
        va = Number(va);
        vb = Number(vb);
      }

      if (ordenacao.coluna === "data") {
        const parse = (s) => {
          if (!s) return new Date(0);
          const parts = s.split("/");
          if (parts.length !== 3) return new Date(s);
          return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
        };
        va = parse(va);
        vb = parse(vb);
      } else {
        va = String(va).toLowerCase();
        vb = String(vb).toLowerCase();
      }

      if (va < vb) return -1 * ordenacao.direcao;
      if (va > vb) return 1 * ordenacao.direcao;
      return 0;
    });

    if (renderAfter) renderTabela(lista);
  }

  function atualizarSetasVisual() {
    tabela.querySelectorAll("thead th[data-campo]").forEach((th) => {
      const span = th.querySelector("span.seta");
      if (!span) return;
      const campo = th.getAttribute("data-campo");
      if (ordenacao.coluna === campo) {
        span.textContent = ordenacao.direcao === 1 ? " ↑" : " ↓";
      } else {
        span.textContent = "";
      }
    });
  }

  function escapeHtml(text) {
    if (typeof text !== "string") return text;
    return text.replace(
      /[&<>"']/g,
      (m) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        }[m])
    );
  }

  lerTabela();
  renderTabela(registrosOriginais);
});
