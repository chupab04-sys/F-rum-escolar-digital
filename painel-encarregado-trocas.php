<?php
require 'auth_check.php';
protegerPainel('encarregado');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'enc-trocas');

$filtro = $_GET['categoria'] ?? 'todos';
$condicao = '';
$parametros = [];
if (in_array($filtro, ['Manual', 'Farda', 'Material', 'Outro'], true)) {
    $condicao = 'WHERE t.categoria = ?';
    $parametros[] = $filtro;
}

$stmt = $pdo->prepare(
    "SELECT t.id, t.titulo, t.descricao, t.categoria, t.telefone_contacto, t.criado_em, t.autor_id, u.nome AS autor_nome
     FROM trocas_anuncios t
     JOIN utilizadores u ON u.id = t.autor_id
     $condicao
     ORDER BY t.criado_em DESC"
);
$stmt->execute($parametros);
$anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Espaço de Trocas | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .cg-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .cg-voltar{ font-size:.85rem; color:var(--muted); }
    .cg-voltar:hover{ color:var(--navy); }
    .cg-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .cg-erro{ background:#fdecea; border:1px solid #f3b8b0; color:#a5423b; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .cg-form{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:28px; margin-bottom:36px; }
    .cg-campo{ margin-bottom:18px; }
    .cg-campo label{ display:block; font-size:.82rem; font-weight:600; color:var(--ink); margin-bottom:6px; }
    .cg-campo input[type=text], .cg-campo textarea, .cg-campo select{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-campo textarea{ min-height:80px; resize:vertical; }
    .cg-linha{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }

    .cg-filtros{ display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
    .cg-filtro{ padding:8px 18px; border-radius:999px; border:1px solid var(--line); font-size:.82rem; font-weight:600; color:var(--muted); background:#fff; text-decoration:none; }
    .cg-filtro.ativo{ background:var(--navy); color:#fff; border-color:var(--navy); }

    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .cg-item h3{ font-family:var(--serif); font-size:1.05rem; color:var(--navy); margin-bottom:6px; }
    .cg-item p{ font-size:.88rem; color:var(--ink); margin-bottom:8px; }
    .cg-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--navy); padding:3px 10px; border-radius:999px; }
    .cg-contacto{ font-size:.85rem; color:var(--navy); font-weight:600; margin-top:8px; }
    .cg-meta{ font-size:.72rem; color:var(--muted); margin-top:6px; }
    .cg-btn-eliminar{ background:#fdecea; color:#a5423b; border:none; border-radius:8px; padding:8px 16px; font-size:.78rem; font-weight:600; cursor:pointer; white-space:nowrap; }
    .cg-btn-eliminar:hover{ background:#f6d3ce; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Espaço de Trocas</h1>
        <div class="panel-user">
          <span class="role-chip">Encarregado</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cg-topo">
        <div>
          <span class="eyebrow">Espaço dos Pais</span>
          <h2 style="text-align:left; margin:0;">Doações e Trocas</h2>
        </div>
        <a href="painel-encarregado.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'publicado'): ?>
        <div class="cg-aviso">Anúncio publicado com sucesso.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="cg-aviso">Anúncio eliminado.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="cg-erro">Preencha todos os campos.</div>
      <?php endif; ?>

      <form class="cg-form" action="processar-troca.php" method="POST">
        <input type="hidden" name="acao" value="publicar">

        <div class="cg-campo">
          <label for="titulo">Título</label>
          <input type="text" id="titulo" name="titulo" placeholder="Ex.: Farda tamanho 12, boa condição" required>
        </div>

        <div class="cg-linha">
          <div class="cg-campo">
            <label for="categoria">Categoria</label>
            <select id="categoria" name="categoria" required>
              <option value="Manual">Manual</option>
              <option value="Farda">Farda</option>
              <option value="Material">Material</option>
              <option value="Outro">Outro</option>
            </select>
          </div>
          <div class="cg-campo">
            <label for="telefone_contacto">Telefone de contacto</label>
            <input type="text" id="telefone_contacto" name="telefone_contacto" placeholder="Ex.: 923 000 000" required>
          </div>
        </div>

        <div class="cg-campo">
          <label for="descricao">Descrição</label>
          <textarea id="descricao" name="descricao" placeholder="Estado, tamanho, condições de entrega..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Publicar Anúncio</button>
      </form>

      <div class="cg-filtros">
        <a href="?categoria=todos" class="cg-filtro <?= $filtro === 'todos' ? 'ativo' : '' ?>">Todos</a>
        <a href="?categoria=Manual" class="cg-filtro <?= $filtro === 'Manual' ? 'ativo' : '' ?>">Manuais</a>
        <a href="?categoria=Farda" class="cg-filtro <?= $filtro === 'Farda' ? 'ativo' : '' ?>">Fardas</a>
        <a href="?categoria=Material" class="cg-filtro <?= $filtro === 'Material' ? 'ativo' : '' ?>">Materiais</a>
        <a href="?categoria=Outro" class="cg-filtro <?= $filtro === 'Outro' ? 'ativo' : '' ?>">Outros</a>
      </div>

      <?php if (empty($anuncios)): ?>
        <p class="cg-vazio">Ainda não há nenhum anúncio nesta categoria.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($anuncios as $a): ?>
            <div class="cg-item">
              <div>
                <span class="cg-tag"><?= htmlspecialchars($a['categoria']) ?></span>
                <h3 style="margin-top:8px;"><?= htmlspecialchars($a['titulo']) ?></h3>
                <p><?= nl2br(htmlspecialchars($a['descricao'])) ?></p>
                <div class="cg-contacto">📞 <?= htmlspecialchars($a['telefone_contacto']) ?></div>
                <div class="cg-meta">Publicado por <?= htmlspecialchars($a['autor_nome']) ?> · <?= date('d/m/Y', strtotime($a['criado_em'])) ?></div>
              </div>
              <?php if ((int)$a['autor_id'] === (int)$_SESSION['user_id']): ?>
                <form action="processar-troca.php" method="POST" onsubmit="return confirm('Eliminar este anúncio?');">
                  <input type="hidden" name="acao" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                  <button type="submit" class="cg-btn-eliminar">Eliminar</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
