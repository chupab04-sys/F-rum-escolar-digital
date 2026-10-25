<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$filtro = $_GET['perfil'] ?? 'todos';
$condicao = '';
$parametros = [];
if (in_array($filtro, ['professor', 'aluno', 'encarregado'], true)) {
    $condicao = 'WHERE perfil_autor = ?';
    $parametros[] = $filtro;
}

$stmt = $pdo->prepare("SELECT id, perfil_autor, mensagem, lida, criado_em FROM sugestoes $condicao ORDER BY criado_em DESC");
$stmt->execute($parametros);
$sugestoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Caixa de Sugestões | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .cg-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .cg-voltar{ font-size:.85rem; color:var(--muted); }
    .cg-voltar:hover{ color:var(--navy); }
    .cg-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .cg-filtros{ display:flex; gap:8px; margin-bottom:28px; }
    .cg-filtro{ padding:8px 18px; border-radius:999px; border:1px solid var(--line); font-size:.82rem; font-weight:600; color:var(--muted); background:#fff; }
    .cg-filtro.ativo{ background:var(--navy); color:#fff; border-color:var(--navy); }
    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .cg-item.nao-lida{ border-left:4px solid var(--terracotta); }
    .cg-item p{ font-size:.9rem; color:var(--ink); margin-bottom:8px; }
    .cg-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--navy); padding:3px 10px; border-radius:999px; }
    .cg-data{ font-size:.72rem; color:var(--muted); margin-top:6px; }
    .cg-acoes{ display:flex; gap:8px; }
    .cg-btn{ padding:8px 16px; border-radius:8px; border:none; cursor:pointer; font-size:.78rem; font-weight:600; font-family:var(--sans); white-space:nowrap; }
    .cg-btn-lida{ background:var(--navy); color:#fff; }
    .cg-btn-lida:hover{ background:var(--navy-deep); }
    .cg-btn-eliminar{ background:#fdecea; color:#a5423b; }
    .cg-btn-eliminar:hover{ background:#f6d3ce; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Caixa de Sugestões</h1>
        <div class="panel-user">
          <span class="role-chip">Direção</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cg-topo">
        <div>
          <span class="eyebrow">Gestão Escolar</span>
          <h2 style="text-align:left; margin:0;">Feedback da Comunidade Escolar</h2>
        </div>
        <a href="painel-direcao.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'atualizado'): ?>
        <div class="cg-aviso">Sugestão marcada como lida.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="cg-aviso">Sugestão eliminada.</div>
      <?php endif; ?>

      <div class="cg-filtros">
        <a href="?perfil=todos" class="cg-filtro <?= $filtro === 'todos' ? 'ativo' : '' ?>">Todos</a>
        <a href="?perfil=professor" class="cg-filtro <?= $filtro === 'professor' ? 'ativo' : '' ?>">Professores</a>
        <a href="?perfil=aluno" class="cg-filtro <?= $filtro === 'aluno' ? 'ativo' : '' ?>">Alunos</a>
        <a href="?perfil=encarregado" class="cg-filtro <?= $filtro === 'encarregado' ? 'ativo' : '' ?>">Encarregados</a>
      </div>

      <?php if (empty($sugestoes)): ?>
        <p class="cg-vazio">Ainda não há nenhuma sugestão nesta categoria.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($sugestoes as $s): ?>
            <div class="cg-item <?= $s['lida'] ? '' : 'nao-lida' ?>">
              <div>
                <span class="cg-tag"><?= htmlspecialchars(ucfirst($s['perfil_autor'])) ?></span>
                <p style="margin-top:10px;"><?= nl2br(htmlspecialchars($s['mensagem'])) ?></p>
                <div class="cg-data"><?= date('d/m/Y H:i', strtotime($s['criado_em'])) ?></div>
              </div>
              <div class="cg-acoes">
                <?php if (!$s['lida']): ?>
                  <form action="processar-sugestao-direcao.php" method="POST">
                    <input type="hidden" name="acao" value="marcar_lida">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <input type="hidden" name="perfil_filtro" value="<?= htmlspecialchars($filtro) ?>">
                    <button type="submit" class="cg-btn cg-btn-lida">Marcar como lida</button>
                  </form>
                <?php endif; ?>
                <form action="processar-sugestao-direcao.php" method="POST" onsubmit="return confirm('Eliminar esta sugestão?');">
                  <input type="hidden" name="acao" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                  <input type="hidden" name="perfil_filtro" value="<?= htmlspecialchars($filtro) ?>">
                  <button type="submit" class="cg-btn cg-btn-eliminar">Eliminar</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
