<?php
require 'auth_check.php';
protegerPainel('encarregado');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'enc-rendimento');

$stmt = $pdo->prepare('SELECT aluno_utilizador_id, nome_aluno FROM encarregados_dados WHERE utilizador_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
$alunoId = $dados['aluno_utilizador_id'] ?? null;
$nomeAluno = $dados['nome_aluno'] ?? '';

$notas = [];
$assiduidade = [];

if ($alunoId) {
    $stmt = $pdo->prepare(
        "SELECT n.tipo_avaliacao, n.periodo, n.nota, n.criado_em, pt.disciplina
         FROM notas n
         JOIN professor_turmas pt ON pt.id = n.professor_turma_id
         WHERE n.aluno_id = ?
         ORDER BY pt.disciplina, n.periodo, n.criado_em"
    );
    $stmt->execute([$alunoId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
        $notas[$n['disciplina']][] = $n;
    }

    $stmt = $pdo->prepare(
        "SELECT pt.disciplina, COUNT(*) AS total_aulas, SUM(a.presente = 0) AS faltas
         FROM assiduidade a
         JOIN professor_turmas pt ON pt.id = a.professor_turma_id
         WHERE a.aluno_id = ?
         GROUP BY pt.disciplina"
    );
    $stmt->execute([$alunoId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $assiduidade[$a['disciplina']] = $a;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rendimento e Notas | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .av-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .av-voltar{ font-size:.85rem; color:var(--muted); }
    .av-voltar:hover{ color:var(--navy); }
    .av-disciplina-bloco{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:24px; margin-bottom:18px; }
    .av-disciplina-bloco h3{ font-family:var(--serif); color:var(--navy); font-size:1.15rem; margin-bottom:14px; }
    .av-assiduidade-resumo{ display:flex; gap:18px; margin-bottom:16px; flex-wrap:wrap; }
    .av-stat{ background:var(--cream); border-radius:10px; padding:10px 16px; text-align:center; min-width:110px; }
    .av-stat .valor{ font-family:var(--serif); font-weight:700; font-size:1.3rem; color:var(--navy); }
    .av-stat .rotulo{ font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
    .av-tabela{ width:100%; border-collapse:collapse; }
    .av-tabela th, .av-tabela td{ text-align:left; padding:8px 10px; font-size:.85rem; border-bottom:1px solid var(--line); }
    .av-tabela th{ color:var(--muted); font-weight:600; font-size:.75rem; text-transform:uppercase; }
    .av-nota{ font-family:var(--serif); font-weight:700; color:var(--navy); }
    .av-vazio{ text-align:center; color:var(--muted); padding:60px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Rendimento e Notas</h1>
        <div class="panel-user">
          <span class="role-chip">Encarregado</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="av-topo">
        <div>
          <span class="eyebrow">Espaço dos Pais</span>
          <h2 style="text-align:left; margin:0;">Progresso de <?= htmlspecialchars($nomeAluno) ?></h2>
        </div>
        <a href="painel-encarregado.php" class="av-voltar">← Voltar ao painel</a>
      </div>

      <?php if (empty($notas) && empty($assiduidade)): ?>
        <p class="av-vazio">Ainda não há notas ou registos de assiduidade para o seu educando.</p>
      <?php else: ?>
        <?php
        $disciplinas = array_unique(array_merge(array_keys($notas), array_keys($assiduidade)));
        sort($disciplinas);
        ?>
        <?php foreach ($disciplinas as $disciplina): ?>
          <div class="av-disciplina-bloco">
            <h3><?= htmlspecialchars($disciplina) ?></h3>

            <?php if (isset($assiduidade[$disciplina])): ?>
              <?php
                $total = (int)$assiduidade[$disciplina]['total_aulas'];
                $faltas = (int)$assiduidade[$disciplina]['faltas'];
                $percentagem = $total > 0 ? round((($total - $faltas) / $total) * 100) : 0;
              ?>
              <div class="av-assiduidade-resumo">
                <div class="av-stat"><div class="valor"><?= $total ?></div><div class="rotulo">Aulas registadas</div></div>
                <div class="av-stat"><div class="valor"><?= $faltas ?></div><div class="rotulo">Faltas</div></div>
                <div class="av-stat"><div class="valor"><?= $percentagem ?>%</div><div class="rotulo">Assiduidade</div></div>
              </div>
            <?php endif; ?>

            <?php if (!empty($notas[$disciplina])): ?>
              <table class="av-tabela">
                <thead>
                  <tr><th>Período</th><th>Avaliação</th><th>Nota</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($notas[$disciplina] as $n): ?>
                    <tr>
                      <td><?= htmlspecialchars($n['periodo']) ?></td>
                      <td><?= htmlspecialchars($n['tipo_avaliacao']) ?></td>
                      <td class="av-nota"><?= number_format((float)$n['nota'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
