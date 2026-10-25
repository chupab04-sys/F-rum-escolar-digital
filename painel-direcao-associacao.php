<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'direcao-associacao');

$stmt = $pdo->query(
    "SELECT t.id, t.titulo, t.mensagem, t.criado_em, u.nome AS autor_nome, u.perfil AS autor_perfil
     FROM associacao_temas t
     JOIN utilizadores u ON u.id = t.autor_id
     ORDER BY t.criado_em DESC"
);
$temas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$respostasPorTema = [];
if (!empty($temas)) {
    $ids = array_column($temas, 'id');
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT r.tema_id, r.texto, r.criado_em, u.nome, u.perfil
         FROM associacao_respostas r
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
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Associação de Pais | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .cg-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .cg-voltar{ font-size:.85rem; color:var(--muted); }
    .cg-voltar:hover{ color:var(--navy); }
    .cg-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .cg-nota{ background:#fff8e6; border:1px solid #f0dca0; color:#8a6d1a; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
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
        <h1>Associação de Pais</h1>
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
          <h2 style="text-align:left; margin:0;">Acompanhar os Encarregados</h2>
        </div>
        <a href="painel-direcao.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'respondido'): ?>
        <div class="cg-aviso">Resposta enviada.</div>
      <?php endif; ?>

      <div class="cg-nota">Os temas são propostos pelos Encarregados. A Direção pode acompanhar e responder, mas não cria temas novos aqui.</div>

      <?php if (empty($temas)): ?>
        <p class="cg-vazio">Ainda não há nenhum tema proposto pelos Encarregados.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($temas as $t): ?>
            <div class="cg-item">
              <h3><?= htmlspecialchars($t['titulo']) ?></h3>
              <p><?= nl2br(htmlspecialchars($t['mensagem'])) ?></p>
              <div class="cg-data"><?= htmlspecialchars($t['autor_nome']) ?> (Encarregado) · <?= date('d/m/Y H:i', strtotime($t['criado_em'])) ?></div>

              <?php if (!empty($respostasPorTema[$t['id']])): ?>
                <div class="cg-respostas">
                  <?php foreach ($respostasPorTema[$t['id']] as $r): ?>
                    <div class="cg-resposta">
                      <b><?= htmlspecialchars($r['nome']) ?></b>
                      <span style="color:var(--muted); font-size:.75rem;">(<?= $r['perfil'] === 'direcao' ? 'Direção' : 'Encarregado' ?>)</span>
                      <span class="quando"><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></span>
                      <div style="margin-top:4px;"><?= nl2br(htmlspecialchars($r['texto'])) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <form class="cg-form-resposta" action="processar-associacao-resposta.php" method="POST">
                <input type="hidden" name="tema_id" value="<?= (int)$t['id'] ?>">
                <textarea name="texto" placeholder="Responder como Direção..." required></textarea>
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
