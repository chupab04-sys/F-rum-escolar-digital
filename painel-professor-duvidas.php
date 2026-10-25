<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$stmt = $pdo->prepare(
    "SELECT d.id, d.pergunta, d.resposta, d.criado_em, d.respondido_em,
            pt.turma, pt.disciplina, u.nome AS aluno_nome
     FROM duvidas d
     JOIN professor_turmas pt ON pt.id = d.professor_turma_id
     JOIN utilizadores u ON u.id = d.aluno_id
     WHERE pt.professor_id = ?
     ORDER BY (d.resposta IS NULL) DESC, d.criado_em DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$duvidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pendentes = count(array_filter($duvidas, fn($d) => $d['resposta'] === null));

$mensagem = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resposta a Dúvidas | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <?php include 'tema-aplicar.php'; ?>
  <style>
    :root{
      --iv-navy:#0E1F3B; --iv-navy-2:#17284A;
      --iv-brass:#C6A15B; --iv-brass-light:#E4CD9A;
      --iv-wine:#7A2331;
    }
    body{ background: var(--cream-deep); }

    .iv-hero{ background: linear-gradient(160deg, var(--iv-navy) 0%, var(--iv-navy-2) 100%); position: relative; overflow: hidden; padding: 38px 0 46px; }
    .iv-hero::before{ content:""; position:absolute; top:0; left:0; right:0; height:4px; background: linear-gradient(90deg, var(--iv-brass-light), var(--iv-brass) 40%, var(--iv-brass-light) 100%); }
    .iv-hero::after{ content:""; position:absolute; right:-60px; top:-60px; width:220px; height:220px; border:1px solid rgba(198,161,91,0.18); border-radius:50%; }
    .iv-hero-inner{ position:relative; z-index:2; }
    .iv-voltar{ display:inline-flex; align-items:center; gap:6px; font-size:.8rem; color:var(--iv-brass-light); text-decoration:none; margin-bottom:18px; opacity:.85; transition:opacity .15s ease; }
    .iv-voltar:hover{ opacity:1; }
    .iv-eyebrow{ font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--iv-brass); margin-bottom:10px; }
    .iv-hero h1{ font-family:'Playfair Display',serif; font-weight:700; font-size:2rem; color:#fff; margin-bottom:8px; }
    .iv-hero p{ color:#B9C2D6; font-size:.92rem; max-width:56ch; }
    .iv-stats{ display:flex; gap:14px; margin-top:22px; flex-wrap:wrap; }
    .iv-stat{ background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:12px 20px; backdrop-filter:blur(4px); }
    .iv-stat .num{ font-family:'Playfair Display',serif; font-weight:700; font-size:1.5rem; color:var(--iv-brass-light); line-height:1; }
    .iv-stat .lbl{ font-size:.68rem; color:#B9C2D6; text-transform:uppercase; letter-spacing:.06em; margin-top:4px; }

    .iv-corpo{ padding: 34px 0 60px; }

    .iv-lista{ display:flex; flex-direction:column; gap:14px; }
    .iv-item{
      background:#fff; border-radius:12px; padding:22px 26px;
      border-left:4px solid var(--iv-brass); box-shadow:0 8px 24px -14px rgba(14,31,59,0.25);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .iv-item.iv-pendente{ border-left-color:var(--iv-wine); }
    .iv-item:hover{ transform:translateY(-2px); box-shadow:0 14px 30px -14px rgba(14,31,59,0.35); }
    .iv-item h3{ font-family:'Playfair Display',serif; font-size:1.02rem; color:var(--iv-navy); margin-bottom:6px; }
    .iv-item p{ font-size:.88rem; color:#3a4258; line-height:1.6; margin-bottom:8px; }
    .iv-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--iv-navy); padding:3px 10px; border-radius:999px; }
    .iv-estado{ font-size:.7rem; font-weight:700; padding:5px 12px; border-radius:999px; white-space:nowrap; }
    .iv-estado.pendente{ background:#fdecea; color:#a5423b; }
    .iv-estado.respondida{ background:#eaf6ee; color:#2f7a45; }
    .iv-meta{ font-size:.75rem; color:#8a90a3; margin-top:6px; }
    .iv-resposta-ja{ margin-top:14px; padding:14px 16px; background:var(--cream-deep); border-radius:10px; font-size:.88rem; color:#2f3b57; }
    .iv-form-resposta{ margin-top:14px; display:flex; gap:10px; }
    .iv-form-resposta textarea{
      flex:1; min-height:60px; padding:10px 12px; border:1px solid #e0dacb; border-radius:10px;
      font-family:'Inter',sans-serif; font-size:.88rem; color:#24262b; background:#faf8f3; resize:vertical;
    }
    .iv-btn-responder{ background:var(--iv-navy); color:#fff; border:none; border-radius:8px; padding:0 20px; font-size:.82rem; font-weight:600; cursor:pointer; }
    .iv-btn-responder:hover{ background:var(--iv-navy-2); }
    .iv-vazio{ text-align:center; color:#8a90a3; padding:60px 0; }
    .iv-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
  </style>
</head>
<body>

  <div class="iv-hero">
    <div class="container iv-hero-inner">
      <a href="painel-professor.php" class="iv-voltar">← Voltar ao painel</a>
      <div class="iv-eyebrow">Espaço Docente</div>
      <h1>Resposta a Dúvidas</h1>
      <p>As perguntas deixadas pelos alunos das suas disciplinas — cada resposta é uma aula que não precisa de se repetir.</p>
      <div class="iv-stats">
        <div class="iv-stat"><div class="num"><?= count($duvidas) ?></div><div class="lbl">Total</div></div>
        <div class="iv-stat"><div class="num"><?= $pendentes ?></div><div class="lbl">Pendentes</div></div>
      </div>
    </div>
  </div>

  <div class="iv-corpo">
    <div class="container">

      <?php if ($mensagem === 'respondido'): ?>
        <div class="iv-aviso">Resposta enviada com sucesso.</div>
      <?php endif; ?>

      <?php if (empty($duvidas)): ?>
        <p class="iv-vazio">Ainda não há nenhuma dúvida colocada pelos alunos.</p>
      <?php else: ?>
        <div class="iv-lista">
          <?php foreach ($duvidas as $d): ?>
            <div class="iv-item <?= $d['resposta'] === null ? 'iv-pendente' : '' ?>">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                <div>
                  <h3><?= htmlspecialchars($d['aluno_nome']) ?></h3>
                  <p><?= nl2br(htmlspecialchars($d['pergunta'])) ?></p>
                  <span class="iv-tag"><?= htmlspecialchars($d['turma']) ?> · <?= htmlspecialchars($d['disciplina']) ?></span>
                  <div class="iv-meta"><?= date('d/m/Y H:i', strtotime($d['criado_em'])) ?></div>
                </div>
                <span class="iv-estado <?= $d['resposta'] === null ? 'pendente' : 'respondida' ?>">
                  <?= $d['resposta'] === null ? 'Pendente' : 'Respondida' ?>
                </span>
              </div>

              <?php if ($d['resposta'] !== null): ?>
                <div class="iv-resposta-ja">
                  <b>A sua resposta:</b> <?= nl2br(htmlspecialchars($d['resposta'])) ?>
                </div>
              <?php else: ?>
                <form class="iv-form-resposta" action="processar-responder-duvida.php" method="POST">
                  <input type="hidden" name="duvida_id" value="<?= (int)$d['id'] ?>">
                  <textarea name="resposta" placeholder="Escreva a sua resposta..." required></textarea>
                  <button type="submit" class="iv-btn-responder">Responder</button>
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