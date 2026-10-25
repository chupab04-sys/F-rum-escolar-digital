<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$turma = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT g.id, g.titulo, g.descricao, g.criado_em, u.nome AS criador_nome,
            (SELECT COUNT(*) FROM grupos_membros gm WHERE gm.grupo_id = g.id) AS total_membros,
            (SELECT COUNT(*) FROM grupos_membros gm2 WHERE gm2.grupo_id = g.id AND gm2.aluno_id = ?) AS sou_membro
     FROM grupos_estudo g
     JOIN utilizadores u ON u.id = g.criador_id
     WHERE g.turma = ?
     ORDER BY g.criado_em DESC"
);
$stmt->execute([$_SESSION['user_id'], $turma]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grupos de Estudo | CEPS</title>
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
    .cg-campo input[type=text], .cg-campo textarea{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-campo textarea{ min-height:80px; resize:vertical; }
    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .cg-item h3{ font-family:var(--serif); font-size:1.05rem; color:var(--navy); margin-bottom:6px; }
    .cg-item p{ font-size:.88rem; color:var(--ink); margin-bottom:8px; }
    .cg-meta{ font-size:.75rem; color:var(--muted); }
    .cg-btn{ padding:9px 18px; border-radius:8px; border:none; cursor:pointer; font-size:.8rem; font-weight:600; font-family:var(--sans); white-space:nowrap; text-decoration:none; display:inline-block; }
    .cg-btn-aderir{ background:var(--navy); color:#fff; }
    .cg-btn-aderir:hover{ background:var(--navy-deep); }
    .cg-btn-abrir{ background:var(--cream-deep); color:var(--navy); }
    .cg-btn-abrir:hover{ background:var(--line); }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Grupos de Estudo</h1>
        <div class="panel-user">
          <span class="role-chip">Aluno</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cg-topo">
        <div>
          <span class="eyebrow">Espaço do Estudante</span>
          <h2 style="text-align:left; margin:0;">Grupos da Turma <?= htmlspecialchars($turma) ?></h2>
        </div>
        <a href="painel-aluno.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'criado'): ?>
        <div class="cg-aviso">Grupo criado com sucesso.</div>
      <?php elseif ($mensagem === 'aderiu'): ?>
        <div class="cg-aviso">Já faz parte deste grupo.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="cg-erro">Preencha o título do grupo.</div>
      <?php endif; ?>

      <form class="cg-form" action="processar-grupo.php" method="POST">
        <input type="hidden" name="acao" value="criar">
        <div class="cg-campo">
          <label for="titulo">Título do grupo</label>
          <input type="text" id="titulo" name="titulo" required>
        </div>
        <div class="cg-campo">
          <label for="descricao">Descrição (opcional)</label>
          <textarea id="descricao" name="descricao"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Criar Grupo</button>
      </form>

      <h3 style="font-family:var(--serif); color:var(--navy); margin-bottom:16px;">Grupos existentes</h3>

      <?php if (empty($grupos)): ?>
        <p class="cg-vazio">Ainda não há nenhum grupo de estudo nesta turma.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($grupos as $g): ?>
            <div class="cg-item">
              <div>
                <h3><?= htmlspecialchars($g['titulo']) ?></h3>
                <?php if (!empty($g['descricao'])): ?><p><?= nl2br(htmlspecialchars($g['descricao'])) ?></p><?php endif; ?>
                <div class="cg-meta">Criado por <?= htmlspecialchars($g['criador_nome']) ?> · <?= (int)$g['total_membros'] ?> membro(s)</div>
              </div>
              <?php if ($g['sou_membro']): ?>
                <a href="painel-aluno-grupo-detalhe.php?id=<?= (int)$g['id'] ?>" class="cg-btn cg-btn-abrir">Abrir Grupo</a>
              <?php else: ?>
                <form action="processar-grupo.php" method="POST">
                  <input type="hidden" name="acao" value="aderir">
                  <input type="hidden" name="grupo_id" value="<?= (int)$g['id'] ?>">
                  <button type="submit" class="cg-btn cg-btn-aderir">Aderir</button>
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
