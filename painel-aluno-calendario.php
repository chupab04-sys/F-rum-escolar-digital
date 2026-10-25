<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'aluno-calendario');

$stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$turma = $stmt->fetchColumn();

// Eventos da Agenda Oficial (Direção) para Alunos: escola toda ou da sua turma
$stmt = $pdo->prepare(
    "SELECT titulo, descricao, tipo, data_evento
     FROM agenda_eventos
     WHERE para_alunos = 1 AND (turma_alvo IS NULL OR turma_alvo = ?)"
);
$stmt->execute([$turma]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trabalhos com data de entrega, publicados pelos professores da sua turma
$stmt = $pdo->prepare(
    "SELECT f.titulo, f.descricao, f.data_entrega, pt.disciplina
     FROM fichas f
     JOIN professor_turmas pt ON pt.id = f.professor_turma_id
     WHERE pt.turma = ? AND f.data_entrega IS NOT NULL"
);
$stmt->execute([$turma]);
$trabalhos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Junta tudo numa só lista, ordenada por data
$itens = [];
foreach ($eventos as $e) {
    $itens[] = [
        'titulo' => $e['titulo'], 'descricao' => $e['descricao'],
        'tipo' => $e['tipo'], 'data' => $e['data_evento'],
    ];
}
foreach ($trabalhos as $t) {
    $itens[] = [
        'titulo' => $t['titulo'], 'descricao' => $t['descricao'],
        'tipo' => 'Trabalho (' . $t['disciplina'] . ')', 'data' => $t['data_entrega'],
    ];
}
usort($itens, fn($a, $b) => strtotime($a['data']) <=> strtotime($b['data']));
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendário Escolar | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .av-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .av-voltar{ font-size:.85rem; color:var(--muted); }
    .av-voltar:hover{ color:var(--navy); }
    .av-lista{ display:flex; flex-direction:column; gap:14px; }
    .av-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; display:flex; gap:18px; align-items:flex-start; }
    .av-data-bloco{ background:var(--navy); color:#fff; border-radius:10px; padding:10px 14px; text-align:center; min-width:64px; }
    .av-data-bloco .dia{ font-family:var(--serif); font-size:1.3rem; font-weight:700; line-height:1; }
    .av-data-bloco .mes{ font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; }
    .av-item h3{ font-family:var(--serif); font-size:1.02rem; color:var(--navy); margin-bottom:6px; }
    .av-item p{ font-size:.85rem; color:var(--ink); margin-bottom:6px; }
    .av-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--navy); padding:3px 10px; border-radius:999px; }
    .av-vazio{ text-align:center; color:var(--muted); padding:60px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Calendário Escolar</h1>
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
          <h2 style="text-align:left; margin:0;">Datas de Provas, Trabalhos e Eventos</h2>
        </div>
        <a href="painel-aluno.php" class="av-voltar">← Voltar ao painel</a>
      </div>

      <?php if (empty($itens)): ?>
        <p class="av-vazio">Ainda não há nenhuma data marcada.</p>
      <?php else: ?>
        <div class="av-lista">
          <?php foreach ($itens as $i): ?>
            <div class="av-item">
              <div class="av-data-bloco">
                <div class="dia"><?= date('d', strtotime($i['data'])) ?></div>
                <div class="mes"><?= date('M', strtotime($i['data'])) ?></div>
              </div>
              <div>
                <span class="av-tag"><?= htmlspecialchars($i['tipo']) ?></span>
                <h3 style="margin-top:8px;"><?= htmlspecialchars($i['titulo']) ?></h3>
                <?php if (!empty($i['descricao'])): ?><p><?= nl2br(htmlspecialchars($i['descricao'])) ?></p><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
