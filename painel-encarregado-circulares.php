<?php
require 'auth_check.php';
protegerPainel('encarregado');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'enc-circulares');

$encarregadoId = $_SESSION['user_id'];

// Aluno ligado a este encarregado, e a turma desse aluno
$stmt = $pdo->prepare(
    "SELECT ed.aluno_utilizador_id, ad.turma
     FROM encarregados_dados ed
     JOIN alunos_dados ad ON ad.utilizador_id = ed.aluno_utilizador_id
     WHERE ed.utilizador_id = ?"
);
$stmt->execute([$encarregadoId]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
$alunoId = $dados['aluno_utilizador_id'] ?? null;
$turma   = $dados['turma'] ?? null;

// "Família" = o aluno + todos os encarregados ligados a esse mesmo aluno
$familiaIds = [$encarregadoId];
if ($alunoId) {
    $familiaIds[] = $alunoId;
    $stmt = $pdo->prepare('SELECT utilizador_id FROM encarregados_dados WHERE aluno_utilizador_id = ?');
    $stmt->execute([$alunoId]);
    $familiaIds = array_unique(array_merge($familiaIds, $stmt->fetchAll(PDO::FETCH_COLUMN)));
}

// Comunicados visíveis: da Direção (escola toda) + do(s) professor(es) da turma do educando
// (turma toda, ou dirigido especificamente a este encarregado)
$stmt = $pdo->prepare(
    "SELECT c.id, c.titulo, c.mensagem, c.criado_em, c.autor_perfil, u.nome AS autor_nome,
            (l.id IS NOT NULL) AS lido
     FROM comunicados c
     JOIN utilizadores u ON u.id = c.autor_id
     LEFT JOIN comunicados_leituras l ON l.comunicado_id = c.id AND l.utilizador_id = ?
     WHERE c.para_encarregados = 1
       AND (c.turma_alvo IS NULL OR c.turma_alvo = ?)
       AND (c.destinatario_id IS NULL OR c.destinatario_id = ?)
     ORDER BY c.criado_em DESC"
);
$stmt->execute([$encarregadoId, $turma, $encarregadoId]);
$avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respostas partilhadas com o aluno, só para os comunicados do professor
$respostasPorComunicado = [];
$idsProfessor = array_column(array_filter($avisos, fn($a) => $a['autor_perfil'] === 'professor'), 'id');
if (!empty($idsProfessor)) {
    $marcadores = implode(',', array_fill(0, count($idsProfessor), '?'));
    $marcadoresFamilia = implode(',', array_fill(0, count($familiaIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT r.comunicado_id, r.texto, r.resposta_rapida, r.criado_em, u.nome, u.perfil
         FROM comunicados_respostas r
         JOIN utilizadores u ON u.id = r.utilizador_id
         WHERE r.comunicado_id IN ($marcadores) AND r.utilizador_id IN ($marcadoresFamilia)
         ORDER BY r.criado_em ASC"
    );
    $stmt->execute(array_merge($idsProfessor, $familiaIds));
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $respostasPorComunicado[$r['comunicado_id']][] = $r;
    }
}

$respostasRapidas = ['Recebido / Compreendido', 'Vou tratar disso', 'Preciso de mais informação', 'Obrigado(a)'];
$mensagem = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Circulares Oficiais | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .av-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .av-voltar{ font-size:.85rem; color:var(--muted); }
    .av-voltar:hover{ color:var(--navy); }
    .av-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .av-lista{ display:flex; flex-direction:column; gap:14px; }
    .av-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; }
    .av-item.nao-lido{ border-left:4px solid var(--terracotta); }
    .av-topo-item{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .av-item h3{ font-family:var(--serif); font-size:1.05rem; color:var(--navy); margin-bottom:6px; }
    .av-item p{ font-size:.88rem; color:var(--ink); margin-bottom:10px; }
    .av-meta{ font-size:.75rem; color:var(--muted); }
    .av-estado{ font-size:.72rem; font-weight:700; padding:4px 10px; border-radius:999px; white-space:nowrap; }
    .av-estado.lido{ background:#eaf6ee; color:#2f7a45; }
    .av-estado.nao-lido{ background:#fdecea; color:#a5423b; }
    .av-btn-lido{ background:var(--navy); color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:.78rem; font-weight:600; cursor:pointer; }
    .av-btn-lido:hover{ background:var(--navy-deep); }
    .av-vazio{ text-align:center; color:var(--muted); padding:60px 0; }

    .av-respostas{ margin-top:14px; padding-top:14px; border-top:1px solid var(--line); display:flex; flex-direction:column; gap:10px; }
    .av-resposta{ background:var(--cream); border-radius:10px; padding:10px 14px; font-size:.85rem; }
    .av-resposta b{ color:var(--navy); }
    .av-resposta .quando{ font-size:.7rem; color:var(--muted); margin-left:6px; }

    .av-form-resposta{ margin-top:14px; padding-top:14px; border-top:1px solid var(--line); }
    .av-chips{ display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
    .av-chip{ background:var(--cream-deep); border:1px solid var(--line); border-radius:999px; padding:7px 14px; font-size:.78rem; color:var(--navy); cursor:pointer; }
    .av-chip:hover{ background:var(--navy); color:#fff; border-color:var(--navy); }
    .av-form-resposta textarea{
      width:100%; min-height:60px; padding:10px 12px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.85rem; color:var(--ink); background:var(--cream); resize:vertical; margin-bottom:8px;
    }
    .av-btn-enviar{ background:var(--navy); color:#fff; border:none; border-radius:8px; padding:8px 18px; font-size:.8rem; font-weight:600; cursor:pointer; }
    .av-btn-enviar:hover{ background:var(--navy-deep); }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Circulares Oficiais</h1>
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
          <h2 style="text-align:left; margin:0;">Comunicados Oficiais</h2>
        </div>
        <a href="painel-encarregado.php" class="av-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'respondido'): ?>
        <div class="av-aviso">Resposta enviada com sucesso.</div>
      <?php endif; ?>

      <?php if (empty($avisos)): ?>
        <p class="av-vazio">Ainda não há nenhum comunicado.</p>
      <?php else: ?>
        <div class="av-lista">
          <?php foreach ($avisos as $a): ?>
            <div class="av-item <?= $a['lido'] ? '' : 'nao-lido' ?>">
              <div class="av-topo-item">
                <div>
                  <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                  <p><?= nl2br(htmlspecialchars($a['mensagem'])) ?></p>
                  <div class="av-meta">
                    <?= $a['autor_perfil'] === 'direcao' ? 'Direção' : 'Professor(a)' ?> ·
                    <?= htmlspecialchars($a['autor_nome']) ?> ·
                    <?= date('d/m/Y H:i', strtotime($a['criado_em'])) ?>
                  </div>
                </div>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                  <span class="av-estado <?= $a['lido'] ? 'lido' : 'nao-lido' ?>"><?= $a['lido'] ? 'Lido' : 'Não lido' ?></span>
                  <?php if (!$a['lido']): ?>
                    <form action="processar-lido-comunicado.php" method="POST">
                      <input type="hidden" name="comunicado_id" value="<?= (int)$a['id'] ?>">
                      <input type="hidden" name="origem" value="encarregado">
                      <button type="submit" class="av-btn-lido">Marcar como lido</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>

              <?php if ($a['autor_perfil'] === 'professor'): ?>

                <?php if (!empty($respostasPorComunicado[$a['id']])): ?>
                  <div class="av-respostas">
                    <?php foreach ($respostasPorComunicado[$a['id']] as $r): ?>
                      <div class="av-resposta">
                        <b><?= htmlspecialchars($r['nome']) ?></b>
                        <span style="color:var(--muted); font-size:.75rem;">(<?= htmlspecialchars($r['perfil']) ?>)</span>
                        <span class="quando"><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></span>
                        <div style="margin-top:4px;">
                          <?php if (!empty($r['resposta_rapida'])): ?><em>"<?= htmlspecialchars($r['resposta_rapida']) ?>"</em><?php endif; ?>
                          <?php if (!empty($r['texto'])): ?><div><?= nl2br(htmlspecialchars($r['texto'])) ?></div><?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="av-form-resposta">
                  <div class="av-chips">
                    <?php foreach ($respostasRapidas as $chip): ?>
                      <form action="processar-resposta-comunicado.php" method="POST" style="display:inline;">
                        <input type="hidden" name="comunicado_id" value="<?= (int)$a['id'] ?>">
                        <input type="hidden" name="resposta_rapida" value="<?= htmlspecialchars($chip) ?>">
                        <button type="submit" class="av-chip"><?= htmlspecialchars($chip) ?></button>
                      </form>
                    <?php endforeach; ?>
                  </div>
                  <form action="processar-resposta-comunicado.php" method="POST">
                    <input type="hidden" name="comunicado_id" value="<?= (int)$a['id'] ?>">
                    <textarea name="texto" placeholder="Ou escreva a sua própria resposta..."></textarea>
                    <button type="submit" class="av-btn-enviar">Responder</button>
                  </form>
                </div>

              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>