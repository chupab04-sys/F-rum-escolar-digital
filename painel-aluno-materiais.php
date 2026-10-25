<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'aluno-materiais');

$stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$turma = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT f.id, f.titulo, f.descricao, f.nome_ficheiro, f.criado_em, pt.disciplina, u.nome AS professor_nome
     FROM fichas f
     JOIN professor_turmas pt ON pt.id = f.professor_turma_id
     JOIN utilizadores u ON u.id = pt.professor_id
     WHERE pt.turma = ?
     ORDER BY pt.disciplina, f.criado_em DESC"
);
$stmt->execute([$turma]);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por disciplina, para ficar mais fácil de navegar
$fichasPorDisciplina = [];
foreach ($fichas as $f) {
    $fichasPorDisciplina[$f['disciplina']][] = $f;
}
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Materiais de Estudo | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .av-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .av-voltar{ font-size:.85rem; color:var(--muted); }
    .av-voltar:hover{ color:var(--navy); }
    .av-disciplina{ font-family:var(--serif); color:var(--navy); font-size:1.1rem; margin:28px 0 14px; }
    .av-lista{ display:flex; flex-direction:column; gap:14px; }
    .av-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .av-item h3{ font-family:var(--serif); font-size:1.02rem; color:var(--navy); margin-bottom:6px; }
    .av-item p{ font-size:.85rem; color:var(--ink); margin-bottom:8px; }
    .av-meta{ font-size:.75rem; color:var(--muted); }
    .av-btn-download{ background:var(--navy); color:#fff; border:none; border-radius:8px; padding:10px 18px; font-size:.8rem; font-weight:600; text-decoration:none; white-space:nowrap; }
    .av-btn-download:hover{ background:var(--navy-deep); }
    .av-vazio{ text-align:center; color:var(--muted); padding:60px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Materiais de Estudo</h1>
        <div class="panel-user">
          <span class="role-chip">Aluno</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="av-topo">
        <div>
          <span class="eyebrow">Espaço do Estudante</span>
          <h2 style="text-align:left; margin:0;">Materiais e Fichas da Turma</h2>
        </div>
        <a href="painel-aluno.php" class="av-voltar">← Voltar ao painel</a>
      </div>

      <?php if (empty($fichasPorDisciplina)): ?>
        <p class="av-vazio">Ainda não há nenhum material publicado para a sua turma.</p>
      <?php else: ?>
        <?php foreach ($fichasPorDisciplina as $disciplina => $itens): ?>
          <h3 class="av-disciplina"><?= htmlspecialchars($disciplina) ?></h3>
          <div class="av-lista">
            <?php foreach ($itens as $f): ?>
              <div class="av-item">
                <div>
                  <h3><?= htmlspecialchars($f['titulo']) ?></h3>
                  <?php if (!empty($f['descricao'])): ?><p><?= nl2br(htmlspecialchars($f['descricao'])) ?></p><?php endif; ?>
                  <div class="av-meta">
                    <?= htmlspecialchars($f['professor_nome']) ?> · <?= date('d/m/Y', strtotime($f['criado_em'])) ?> ·
                    📎 <?= htmlspecialchars($f['nome_ficheiro']) ?>
                  </div>
                </div>
                <a href="download-ficha.php?id=<?= (int)$f['id'] ?>" class="av-btn-download">Descarregar</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
