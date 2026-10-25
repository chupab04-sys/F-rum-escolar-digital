<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'aluno-duvidas');

$alunoId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
$stmt->execute([$alunoId]);
$turma = $stmt->fetchColumn();

// Disciplinas/professores disponíveis para a turma deste aluno
$stmt = $pdo->prepare(
    "SELECT pt.id, pt.disciplina, u.nome AS professor_nome
     FROM professor_turmas pt
     JOIN utilizadores u ON u.id = pt.professor_id
     WHERE pt.turma = ?
     ORDER BY pt.disciplina"
);
$stmt->execute([$turma]);
$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dúvidas já colocadas por este aluno
$stmt = $pdo->prepare(
    "SELECT d.id, d.pergunta, d.resposta, d.criado_em, d.respondido_em, pt.disciplina, u.nome AS professor_nome
     FROM duvidas d
     JOIN professor_turmas pt ON pt.id = d.professor_turma_id
     JOIN utilizadores u ON u.id = pt.professor_id
     WHERE d.aluno_id = ?
     ORDER BY d.criado_em DESC"
);
$stmt->execute([$alunoId]);
$duvidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dúvidas das Matérias | CEPS</title>
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
    .cg-campo select, .cg-campo textarea{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-campo textarea{ min-height:90px; resize:vertical; }

    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; }
    .cg-topo-item{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .cg-tags{ display:flex; gap:6px; flex-wrap:wrap; }
    .cg-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--navy); padding:3px 10px; border-radius:999px; }
    .cg-estado{ font-size:.72rem; font-weight:700; padding:4px 10px; border-radius:999px; white-space:nowrap; }
    .cg-estado.pendente{ background:#fdecea; color:#a5423b; }
    .cg-estado.respondida{ background:#eaf6ee; color:#2f7a45; }
    .cg-pergunta{ font-size:.9rem; color:var(--ink); margin:10px 0; }
    .cg-resposta{ background:var(--cream); border-radius:10px; padding:12px 14px; font-size:.88rem; color:var(--ink); margin-top:10px; }
    .cg-resposta b{ color:var(--navy); }
    .cg-data{ font-size:.72rem; color:var(--muted); margin-top:8px; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Dúvidas das Matérias</h1>
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
          <h2 style="text-align:left; margin:0;">Colocar uma Dúvida</h2>
        </div>
        <a href="painel-aluno.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'enviada'): ?>
        <div class="cg-aviso">Dúvida enviada ao professor.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="cg-erro">Escolha a disciplina e escreva a sua dúvida.</div>
      <?php elseif ($erro === 'disciplina_invalida'): ?>
        <div class="cg-erro">Essa disciplina não está disponível para a sua turma.</div>
      <?php endif; ?>

      <?php if (empty($disciplinas)): ?>
        <p class="cg-vazio">A sua turma ainda não tem professores atribuídos pela Direção — ainda não é possível colocar dúvidas.</p>
      <?php else: ?>
        <form class="cg-form" action="processar-duvida.php" method="POST">
          <div class="cg-campo">
            <label for="professor_turma_id">Disciplina</label>
            <select id="professor_turma_id" name="professor_turma_id" required>
              <?php foreach ($disciplinas as $d): ?>
                <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['disciplina']) ?> — Prof. <?= htmlspecialchars($d['professor_nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="cg-campo">
            <label for="pergunta">A sua dúvida</label>
            <textarea id="pergunta" name="pergunta" required></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Enviar Dúvida</button>
        </form>
      <?php endif; ?>

      <h3 style="font-family:var(--serif); color:var(--navy); margin-bottom:16px;">As minhas dúvidas</h3>

      <?php if (empty($duvidas)): ?>
        <p class="cg-vazio">Ainda não colocou nenhuma dúvida.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($duvidas as $d): ?>
            <div class="cg-item">
              <div class="cg-topo-item">
                <div class="cg-tags">
                  <span class="cg-tag"><?= htmlspecialchars($d['disciplina']) ?></span>
                  <span class="cg-tag">Prof. <?= htmlspecialchars($d['professor_nome']) ?></span>
                </div>
                <span class="cg-estado <?= $d['resposta'] ? 'respondida' : 'pendente' ?>">
                  <?= $d['resposta'] ? 'Respondida' : 'Pendente' ?>
                </span>
              </div>
              <p class="cg-pergunta"><?= nl2br(htmlspecialchars($d['pergunta'])) ?></p>
              <?php if ($d['resposta']): ?>
                <div class="cg-resposta">
                  <b>Resposta do professor:</b><br>
                  <?= nl2br(htmlspecialchars($d['resposta'])) ?>
                </div>
              <?php endif; ?>
              <div class="cg-data">
                Perguntado em <?= date('d/m/Y H:i', strtotime($d['criado_em'])) ?>
                <?php if ($d['respondido_em']): ?> · Respondido em <?= date('d/m/Y H:i', strtotime($d['respondido_em'])) ?><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
