# appM1 – Controle de Retiradas

Sistema web desenvolvido em **PHP** para controle de retiradas de produtos, com cadastro manual, edição, exclusão, lixeira e importação via arquivo **CSV**.
Projeto criado com foco em estudo e prática de desenvolvimento web, banco de dados e organização de fluxo CRUD.

##Tecnologias utilizadas
- PHP 8.x
- MySQL / PostgreSQL (via PDO)
- HTML5
- CSS3
- JavaScript (Vanilla)
- Apache (XAMPP)
- Git & GitHub

---

## Funcionalidades
- Listagem de retiradas em tabela
- Cadastro manual de novas retiradas
- Edição de registros existentes
- Exclusão com envio para lixeira
- Restauração a partir da lixeira
- Filtro e pesquisa dinâmica na tabela
- Ordenação por colunas
- Importação de dados via arquivo CSV
- Controle de acesso por sessão (login)

## Estrutura básica do projeto
```text
appM1/
├── css/
│   ├── formulario.css
│   └── tabela.css
├── js/
│   └── tabela.js
├── conexao.php
├── index.php
├── tabela.php
├── formulario.php
├── lixeira.php
├── processa_importacao.php
├── excluir.php
├── README.md
