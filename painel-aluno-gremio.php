<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'aluno-gremio');

// Confirma se este aluno é delegado de alguma turma neste momento
$stmt = $pdo->prepare('SELECT COUNT(*) FROM delegados_turma WHERE aluno_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$souDelegado = (int)$stmt->fetchColumn() > 0;

$stmt = $pdo->query(
    "SELECT t.id, t.titulo, t.mensagem, t.criado_em, u.nome AS autor_nome, t.autor_id
     FROM gremio_temas t
     JOIN utilizadores u ON u.id = t.autor_id
     ORDER BY t.criado_em DESC"
);
$temas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$respostasPorTema = [];
if (!empty($temas)) {
    $ids = array_column($temas, 'id');
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT r.tema_id, r.texto, r.criado_em, u.nome
         FROM gremio_respostas r
         JOIN utilizadores u ON u.id = r.autor_id
         WHERE r.tema_id IN ($marcadores)
         ORDER BY r.criado_em ASC"
    );
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $respostasPorTema[$r['tema_id']][] = $r;
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
  <title>Grémio Estudantil | CEPS</title>
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
    .cg-nota{ background:#fff8e6; border:1px solid #f0dca0; color:#8a6d1a; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .cg-form{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:28px; margin-bottom:36px; }
    .cg-campo{ margin-bottom:18px; }
    .cg-campo label{ display:block; font-size:.82rem; font-weight:600; color:var(--ink); margin-bottom:6px; }
    .cg-campo input[type=text], .cg-campo textarea{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-campo textarea{ min-height:90px; resize:vertical; }
    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; }
    .cg-item h3{ font-family:var(--serif); font-size:1.05rem; color:var(--navy); margin-bottom:6px; }
    .cg-item p{ font-size:.88rem; color:var(--ink); margin-bottom:10px; }
    .cg-data{ font-size:.72rem; color:var(--muted); }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
    .cg-respostas{ margin-top:14px; padding-top:14px; border-top:1px solid var(--line); display:flex; flex-direction:column; gap:10px; }
    .cg-resposta{ background:var(--cream); border-radius:10px; padding:10px 14px; font-size:.85rem; }
    .cg-resposta b{ color:var(--navy); }
    .cg-resposta .quando{ font-size:.7rem; color:var(--muted); margin-left:6px; }
    .cg-form-resposta{ margin-top:14px; padding-top:14px; border-top:1px solid var(--line); display:flex; gap:10px; }
    .cg-form-resposta textarea{
      flex:1; min-height:44px; padding:10px 12px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.85rem; color:var(--ink); background:var(--cream); resize:vertical;
    }
    .cg-btn-enviar{ background:var(--navy); color:#fff; border:none; border-radius:8px; padding:0 18px; font-size:.8rem; font-weight:600; cursor:pointer; }
    .cg-btn-enviar:hover{ background:var(--navy-deep); }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Grémio Estudantil</h1>
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
          <h2 style="text-align:left; margin:0;">Debates e Propostas</h2>
        </div>
        <a href="painel-aluno.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'criado'): ?>
        <div class="cg-aviso">Tema lançado com sucesso.</div>
      <?php elseif ($mensagem === 'respondido'): ?>
        <div class="cg-aviso">Resposta enviada.</div>
      <?php endif; ?>
      <?php if ($erro === 'nao_delegado'): ?>
        <div class="cg-erro">Só os delegados de turma podem lançar temas.</div>
      <?php elseif ($erro === 'campos'): ?>
        <div class="cg-erro">Preencha o título e a mensagem.</div>
      <?php endif; ?>

      <?php if ($souDelegado): ?>
        <form class="cg-form" action="processar-gremio-tema.php" method="POST">
          <input type="hidden" name="acao" value="criar">
          <div class="cg-campo">
            <label for="titulo">Título do tema</label>
            <input type="text" id="titulo" name="titulo" required>
          </div>
          <div class="cg-campo">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Lançar Tema</button>
        </form>
      <?php else: ?>
        <div class="cg-nota">Só os delegados de turma podem lançar novos temas — mas qualquer aluno pode participar respondendo abaixo.</div>
      <?php endif; ?>

      <?php if (empty($temas)): ?>
        <p class="cg-vazio">Ainda não há nenhum tema no Grémio Estudantil.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($temas as $t): ?>
            <div class="cg-item">
              <h3><?= htmlspecialchars($t['titulo']) ?></h3>
              <p><?= nl2br(htmlspecialchars($t['mensagem'])) ?></p>
              <div class="cg-data">Delegado <?= htmlspecialchars($t['autor_nome']) ?> · <?= date('d/m/Y H:i', strtotime($t['criado_em'])) ?></div>

              <?php if (!empty($respostasPorTema[$t['id']])): ?>
                <div class="cg-respostas">
                  <?php foreach ($respostasPorTema[$t['id']] as $r): ?>
                    <div class="cg-resposta">
                      <b><?= htmlspecialchars($r['nome']) ?></b>
                      <span class="quando"><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></span>
                      <div style="margin-top:4px;"><?= nl2br(htmlspecialchars($r['texto'])) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <form class="cg-form-resposta" action="processar-gremio-resposta.php" method="POST">
                <input type="hidden" name="tema_id" value="<?= (int)$t['id'] ?>">
                <textarea name="texto" placeholder="Escreva a sua opinião..." required></textarea>
                <button type="submit" class="cg-btn-enviar">Responder</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
