<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

// Turmas de que este professor é Diretor de Turma
$stmt = $pdo->prepare('SELECT turma FROM diretores_turma WHERE professor_id = ? ORDER BY turma');
$stmt->execute([$_SESSION['user_id']]);
$turmasDireitas = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Alunos de cada uma dessas turmas (para escolher o delegado)
$alunosPorTurma = [];
foreach ($turmasDireitas as $t) {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.nome FROM alunos_dados ad JOIN utilizadores u ON u.id = ad.utilizador_id WHERE ad.turma = ? ORDER BY u.nome"
    );
    $stmt->execute([$t]);
    $alunosPorTurma[$t] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Delegados já definidos, só das turmas deste diretor
$delegados = [];
if (!empty($turmasDireitas)) {
    $marcadores = implode(',', array_fill(0, count($turmasDireitas), '?'));
    $stmt = $pdo->prepare(
        "SELECT dt.turma, u.nome AS aluno_nome
         FROM delegados_turma dt
         JOIN utilizadores u ON u.id = dt.aluno_id
         WHERE dt.turma IN ($marcadores)"
    );
    $stmt->execute($turmasDireitas);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
        $delegados[$d['turma']] = $d['aluno_nome'];
    }
}

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delegado de Turma | CEPS</title>
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
    .cg-bloco{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:24px; margin-bottom:18px; }
    .cg-bloco h3{ font-family:var(--serif); color:var(--navy); font-size:1.05rem; margin-bottom:6px; }
    .cg-atual{ font-size:.85rem; color:var(--muted); margin-bottom:16px; }
    .cg-atual b{ color:var(--navy); }
    .cg-linha{ display:flex; gap:12px; flex-wrap:wrap; }
    .cg-linha select{
      flex:1; min-width:200px; padding:10px 12px; border:1px solid var(--line); border-radius:8px;
      font-family:var(--sans); font-size:.9rem; color:var(--ink); background:var(--cream);
    }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Delegado de Turma</h1>
        <div class="panel-user">
          <span class="role-chip">Professor</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cg-topo">
        <div>
          <span class="eyebrow">Espaço Docente</span>
          <h2 style="text-align:left; margin:0;">Escolher o Delegado</h2>
        </div>
        <a href="painel-professor.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'definido'): ?>
        <div class="cg-aviso">Delegado definido com sucesso.</div>
      <?php endif; ?>
      <?php if ($erro === 'nao_diretor'): ?>
        <div class="cg-erro">Não é Diretor dessa turma.</div>
      <?php endif; ?>

      <?php if (empty($turmasDireitas)): ?>
        <p class="cg-vazio">Ainda não é Diretor de Turma de nenhuma turma. Esta função só fica disponível quando a Direção o designar como Diretor de uma turma, em "Gestão de Professores".</p>
      <?php else: ?>
        <?php foreach ($turmasDireitas as $t): ?>
          <div class="cg-bloco">
            <h3>Turma <?= htmlspecialchars($t) ?></h3>
            <p class="cg-atual">
              Delegado atual:
              <?= isset($delegados[$t]) ? '<b>' . htmlspecialchars($delegados[$t]) . '</b>' : 'ainda não definido' ?>
            </p>

            <?php if (empty($alunosPorTurma[$t])): ?>
              <p class="cg-vazio">Ainda não há alunos registados nesta turma.</p>
            <?php else: ?>
              <form action="processar-delegado.php" method="POST" class="cg-linha">
                <input type="hidden" name="acao" value="definir">
                <input type="hidden" name="turma" value="<?= htmlspecialchars($t) ?>">
                <select name="aluno_id" required>
                  <option value="">Selecione o aluno</option>
                  <?php foreach ($alunosPorTurma[$t] as $a): ?>
                    <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Definir Delegado</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
