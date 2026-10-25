<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$stmt = $pdo->prepare(
    "SELECT c.id, c.titulo, c.mensagem, c.criado_em, u.nome AS autor_nome,
            (l.id IS NOT NULL) AS lido
     FROM comunicados c
     JOIN utilizadores u ON u.id = c.autor_id
     LEFT JOIN comunicados_leituras l ON l.comunicado_id = c.id AND l.utilizador_id = ?
     WHERE c.para_professores = 1
     ORDER BY c.criado_em DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$comunicadosLista = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT e.id, e.titulo, e.descricao, e.tipo, e.data_evento, u.nome AS autor_nome
     FROM agenda_eventos e
     JOIN utilizadores u ON u.id = e.autor_id
     WHERE e.para_professores = 1
     ORDER BY e.data_evento DESC"
);
$stmt->execute();
$eventosLista = $stmt->fetchAll(PDO::FETCH_ASSOC);

$avisos = [];
foreach ($comunicadosLista as $c) {
    $c['origem'] = 'comunicado';
    $c['data_ordenacao'] = $c['criado_em'];
    $avisos[] = $c;
}
foreach ($eventosLista as $e) {
    $e['origem'] = 'evento';
    $e['data_ordenacao'] = $e['data_evento'];
    $avisos[] = $e;
}
usort($avisos, fn($a, $b) => strtotime($b['data_ordenacao']) <=> strtotime($a['data_ordenacao']));

$totalNaoLidos = count(array_filter($avisos, fn($a) => $a['origem'] === 'comunicado' && !$a['lido']));
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Avisos da Coordenação | CEPS</title>
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

    /* ---- Faixa de topo institucional ---- */
    .iv-hero{
      background: linear-gradient(160deg, var(--iv-navy) 0%, var(--iv-navy-2) 100%);
      position: relative;
      overflow: hidden;
      padding: 38px 0 46px;
    }
    .iv-hero::before{
      content:""; position:absolute; top:0; left:0; right:0; height:4px;
      background: linear-gradient(90deg, var(--iv-brass-light), var(--iv-brass) 40%, var(--iv-brass-light) 100%);
    }
    .iv-hero::after{
      content:""; position:absolute; right:-60px; top:-60px; width:220px; height:220px;
      border:1px solid rgba(198,161,91,0.18); border-radius:50%;
    }
    .iv-hero-inner{ position:relative; z-index:2; }
    .iv-voltar{
      display:inline-flex; align-items:center; gap:6px;
      font-size:.8rem; color:var(--iv-brass-light); text-decoration:none; margin-bottom:18px;
      opacity:.85; transition:opacity .15s ease;
    }
    .iv-voltar:hover{ opacity:1; }
    .iv-eyebrow{
      font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase;
      color:var(--iv-brass); margin-bottom:10px;
    }
    .iv-hero h1{
      font-family:'Playfair Display',serif; font-weight:700; font-size:2rem;
      color:#fff; margin-bottom:8px;
    }
    .iv-hero p{ color:#B9C2D6; font-size:.92rem; max-width:56ch; }
    .iv-stats{ display:flex; gap:14px; margin-top:22px; flex-wrap:wrap; }
    .iv-stat{
      background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12);
      border-radius:12px; padding:12px 20px; backdrop-filter:blur(4px);
    }
    .iv-stat .num{ font-family:'Playfair Display',serif; font-weight:700; font-size:1.5rem; color:var(--iv-brass-light); line-height:1; }
    .iv-stat .lbl{ font-size:.68rem; color:#B9C2D6; text-transform:uppercase; letter-spacing:.06em; margin-top:4px; }

    /* ---- Corpo ---- */
    .iv-corpo{ padding: 34px 0 60px; }
    .iv-lista{ display:flex; flex-direction:column; gap:14px; }
    .iv-item{
      background:#fff; border-radius:12px; padding:22px 26px;
      border-left:4px solid var(--iv-brass); box-shadow:0 8px 24px -14px rgba(14,31,59,0.25);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .iv-item:hover{ transform:translateY(-2px); box-shadow:0 14px 30px -14px rgba(14,31,59,0.35); }
    .iv-item.iv-evento{ border-left-color:var(--iv-wine); background:#fdf9f3; }
    .iv-item-topo{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .iv-item h3{ font-family:'Playfair Display',serif; font-size:1.08rem; color:var(--iv-navy); margin-bottom:6px; }
    .iv-item p{ font-size:.88rem; color:#3a4258; line-height:1.6; margin-bottom:10px; }
    .iv-tag{
      font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
      padding:4px 12px; border-radius:999px; display:inline-block;
    }
    .iv-tag-tipo{ background:var(--iv-navy); color:#fff; }
    .iv-meta{ font-size:.75rem; color:#8a90a3; margin-top:8px; }
    .iv-estado{ font-size:.7rem; font-weight:700; padding:5px 12px; border-radius:999px; white-space:nowrap; }
    .iv-estado.lido{ background:#eaf6ee; color:#2f7a45; }
    .iv-estado.nao-lido{ background:#fdecea; color:#a5423b; }
    .iv-btn-lido{
      background:var(--iv-navy); color:#fff; border:none; border-radius:8px;
      padding:9px 18px; font-size:.78rem; font-weight:600; cursor:pointer; margin-top:8px;
    }
    .iv-btn-lido:hover{ background:var(--iv-navy-2); }
    .iv-vazio{ text-align:center; color:#8a90a3; padding:60px 0; }
  </style>
</head>
<body>

  <div class="iv-hero">
    <div class="container iv-hero-inner">
      <a href="painel-professor.php" class="iv-voltar">← Voltar ao painel</a>
      <div class="iv-eyebrow">Espaço Docente</div>
      <h1>Avisos da Coordenação</h1>
      <p>Comunicados oficiais e eventos partilhados pela Direção — reuniões, feriados e conselhos de notas, tudo num só lugar.</p>
      <div class="iv-stats">
        <div class="iv-stat"><div class="num"><?= count($avisos) ?></div><div class="lbl">Total</div></div>
        <div class="iv-stat"><div class="num"><?= $totalNaoLidos ?></div><div class="lbl">Por ler</div></div>
      </div>
    </div>
  </div>

  <div class="iv-corpo">
    <div class="container">

      <?php if (empty($avisos)): ?>
        <p class="iv-vazio">Ainda não há nenhum aviso da coordenação.</p>
      <?php else: ?>
        <div class="iv-lista">
          <?php foreach ($avisos as $a): ?>
            <?php if ($a['origem'] === 'comunicado'): ?>
              <div class="iv-item">
                <div class="iv-item-topo">
                  <div>
                    <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($a['mensagem'])) ?></p>
                    <div class="iv-meta">Comunicado · Direção · <?= htmlspecialchars($a['autor_nome']) ?> · <?= date('d/m/Y H:i', strtotime($a['criado_em'])) ?></div>
                  </div>
                  <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                    <span class="iv-estado <?= $a['lido'] ? 'lido' : 'nao-lido' ?>"><?= $a['lido'] ? 'Lido' : 'Não lido' ?></span>
                    <?php if (!$a['lido']): ?>
                      <form action="processar-lido-comunicado.php" method="POST">
                        <input type="hidden" name="comunicado_id" value="<?= (int)$a['id'] ?>">
                        <input type="hidden" name="origem" value="professor">
                        <button type="submit" class="iv-btn-lido">Marcar como lido</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="iv-item iv-evento">
                <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                <?php if (!empty($a['descricao'])): ?><p><?= nl2br(htmlspecialchars($a['descricao'])) ?></p><?php endif; ?>
                <span class="iv-tag iv-tag-tipo"><?= htmlspecialchars($a['tipo']) ?></span>
                <div class="iv-meta">📅 <?= date('d/m/Y', strtotime($a['data_evento'])) ?> · Direção · <?= htmlspecialchars($a['autor_nome']) ?></div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>