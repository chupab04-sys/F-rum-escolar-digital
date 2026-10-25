<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'prof-contacto');

// Turmas atribuídas a este professor (para escolher no formulário)
$stmt = $pdo->prepare('SELECT DISTINCT turma FROM professor_turmas WHERE professor_id = ? ORDER BY turma');
$stmt->execute([$_SESSION['user_id']]);
$turmas = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Encarregados de cada turma (para o modo "encarregado específico")
$encarregadosPorTurma = [];
if (!empty($turmas)) {
    $marcadores = implode(',', array_fill(0, count($turmas), '?'));
    $stmt = $pdo->prepare(
        "SELECT ad.turma, ed.utilizador_id AS encarregado_id, u.nome AS encarregado_nome, ed.nome_aluno
         FROM alunos_dados ad
         JOIN encarregados_dados ed ON ed.aluno_utilizador_id = ad.utilizador_id
         JOIN utilizadores u ON u.id = ed.utilizador_id
         WHERE ad.turma IN ($marcadores)
         ORDER BY ad.turma, ed.nome_aluno"
    );
    $stmt->execute($turmas);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $encarregadosPorTurma[$linha['turma']][] = [
            'id'    => (int)$linha['encarregado_id'],
            'label' => $linha['encarregado_nome'] . ' — encarregado(a) de ' . $linha['nome_aluno'],
        ];
    }
}

// Comunicados já enviados por este professor
$stmt = $pdo->prepare(
    "SELECT c.id, c.titulo, c.mensagem, c.turma_alvo, c.destinatario_id, c.criado_em,
            ud.nome AS destinatario_nome
     FROM comunicados c
     LEFT JOIN utilizadores ud ON ud.id = c.destinatario_id
     WHERE c.autor_id = ? AND c.autor_perfil = 'professor'
     ORDER BY c.criado_em DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$comunicados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respostas recebidas a esses comunicados
$respostasPorComunicado = [];
if (!empty($comunicados)) {
    $ids = array_column($comunicados, 'id');
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT r.comunicado_id, r.texto, r.resposta_rapida, r.criado_em, u.nome, u.perfil
         FROM comunicados_respostas r
         JOIN utilizadores u ON u.id = r.utilizador_id
         WHERE r.comunicado_id IN ($marcadores)
         ORDER BY r.criado_em ASC"
    );
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $respostasPorComunicado[$r['comunicado_id']][] = $r;
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
  <title>Contacto com os Encarregados | CEPS</title>
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
    .cg-campo input[type=text], .cg-campo textarea, .cg-campo select{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-campo textarea{ min-height:110px; resize:vertical; }
    .cg-publico{ display:flex; gap:22px; flex-wrap:wrap; margin-bottom:22px; }
    .cg-publico label{ display:flex; align-items:center; gap:8px; font-size:.88rem; color:var(--ink); cursor:pointer; }
    .cg-publico input{ width:16px; height:16px; }
    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 24px; }
    .cg-item-topo{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .cg-item h3{ font-family:var(--serif); font-size:1.05rem; color:var(--navy); margin-bottom:6px; }
    .cg-item p{ font-size:.88rem; color:var(--ink); margin-bottom:10px; }
    .cg-tags{ display:flex; gap:6px; flex-wrap:wrap; }
    .cg-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--navy); padding:3px 10px; border-radius:999px; }
    .cg-data{ font-size:.72rem; color:var(--muted); margin-top:8px; }
    .cg-btn-eliminar{ background:#fdecea; color:#a5423b; border:none; border-radius:8px; padding:8px 16px; font-size:.78rem; font-weight:600; cursor:pointer; white-space:nowrap; }
    .cg-btn-eliminar:hover{ background:#f6d3ce; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }

    .cg-respostas{ margin-top:14px; padding-top:14px; border-top:1px solid var(--line); display:flex; flex-direction:column; gap:10px; }
    .cg-resposta{ background:var(--cream); border-radius:10px; padding:10px 14px; font-size:.85rem; }
    .cg-resposta b{ color:var(--navy); }
    .cg-resposta .quando{ font-size:.7rem; color:var(--muted); margin-left:6px; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Contacto com os Encarregados</h1>
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
          <h2 style="text-align:left; margin:0;">Comunicar com a minha turma</h2>
        </div>
        <a href="painel-professor.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'criado'): ?>
        <div class="cg-aviso">Comunicado enviado com sucesso.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="cg-aviso">Comunicado eliminado.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="cg-erro">Preencha o título e a mensagem.</div>
      <?php elseif ($erro === 'turma'): ?>
        <div class="cg-erro">Selecione a turma.</div>
      <?php elseif ($erro === 'turma_invalida'): ?>
        <div class="cg-erro">Essa turma não está atribuída a si.</div>
      <?php endif; ?>

      <?php if (empty($turmas)): ?>
        <p class="cg-vazio">Ainda não tem nenhuma turma atribuída pela Direção. Contacte a Direção para lhe atribuírem uma turma antes de enviar comunicados.</p>
      <?php else: ?>

        <form class="cg-form" action="processar-comunicado.php" method="POST">
          <input type="hidden" name="acao" value="criar">

          <div class="cg-campo">
            <label for="turma">Turma</label>
            <select id="turma" name="turma" required>
              <?php foreach ($turmas as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="cg-campo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" required>
          </div>

          <div class="cg-campo">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" required></textarea>
          </div>

          <label style="display:block; font-size:.82rem; font-weight:600; color:var(--ink); margin-bottom:10px;">Destinatário</label>
          <div class="cg-publico" style="margin-bottom:16px;">
            <label><input type="radio" name="modo_destino" value="turma" checked onchange="cgAtualizarModo()"> Turma toda</label>
            <label><input type="radio" name="modo_destino" value="especifico" onchange="cgAtualizarModo()"> Um encarregado específico</label>
          </div>

          <div class="cg-campo" id="cgCampoEspecifico" style="display:none;">
            <label for="destinatario_id">Encarregado</label>
            <select id="destinatario_id" name="destinatario_id">
              <option value="">Selecione a turma primeiro</option>
            </select>
          </div>

          <p style="font-size:.82rem; color:var(--muted); margin-bottom:22px;">
            Este comunicado será enviado aos <b>Encarregados</b> — da turma toda, ou só do encarregado escolhido acima.
          </p>

          <button type="submit" class="btn btn-primary">Enviar Comunicado</button>
        </form>

      <?php endif; ?>

      <script>
        const cgEncarregadosPorTurma = <?= json_encode($encarregadosPorTurma, JSON_UNESCAPED_UNICODE) ?>;

        function cgAtualizarModo(){
          const especifico = document.querySelector('input[name="modo_destino"]:checked').value === 'especifico';
          document.getElementById('cgCampoEspecifico').style.display = especifico ? 'block' : 'none';
          document.getElementById('destinatario_id').required = especifico;
          if (especifico) cgPreencherEncarregados();
        }

        function cgPreencherEncarregados(){
          const turma = document.getElementById('turma').value;
          const select = document.getElementById('destinatario_id');
          const lista = cgEncarregadosPorTurma[turma] || [];
          select.innerHTML = '';
          if (lista.length === 0) {
            select.innerHTML = '<option value="">Nenhum encarregado registado nesta turma</option>';
            return;
          }
          select.innerHTML = '<option value="">Selecione um encarregado</option>' +
            lista.map(function(e){ return `<option value="${e.id}">${e.label}</option>`; }).join('');
        }

        document.getElementById('turma')?.addEventListener('change', function(){
          if (document.querySelector('input[name="modo_destino"]:checked').value === 'especifico') {
            cgPreencherEncarregados();
          }
        });
      </script>

      <h3 style="font-family:var(--serif); color:var(--navy); margin-bottom:16px;">Comunicados enviados</h3>

      <?php if (empty($comunicados)): ?>
        <p class="cg-vazio">Ainda não enviou nenhum comunicado.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($comunicados as $c): ?>
            <div class="cg-item">
              <div class="cg-item-topo">
                <div>
                  <h3><?= htmlspecialchars($c['titulo']) ?></h3>
                  <p><?= nl2br(htmlspecialchars($c['mensagem'])) ?></p>
                  <div class="cg-tags">
                    <span class="cg-tag">Turma <?= htmlspecialchars($c['turma_alvo']) ?></span>
                    <?php if ($c['destinatario_id']): ?>
                      <span class="cg-tag">Só: <?= htmlspecialchars($c['destinatario_nome']) ?></span>
                    <?php else: ?>
                      <span class="cg-tag">Encarregados (turma toda)</span>
                    <?php endif; ?>
                  </div>
                  <div class="cg-data"><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></div>
                </div>
                <form action="processar-comunicado.php" method="POST" onsubmit="return confirm('Eliminar este comunicado?');">
                  <input type="hidden" name="acao" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button type="submit" class="cg-btn-eliminar">Eliminar</button>
                </form>
              </div>

              <?php if (!empty($respostasPorComunicado[$c['id']])): ?>
                <div class="cg-respostas">
                  <?php foreach ($respostasPorComunicado[$c['id']] as $r): ?>
                    <div class="cg-resposta">
                      <b><?= htmlspecialchars($r['nome']) ?></b>
                      <span style="color:var(--muted); font-size:.75rem;">(<?= htmlspecialchars($r['perfil']) ?>)</span>
                      <span class="quando"><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></span>
                      <div style="margin-top:4px;">
                        <?php if (!empty($r['resposta_rapida'])): ?>
                          <em>"<?= htmlspecialchars($r['resposta_rapida']) ?>"</em>
                        <?php endif; ?>
                        <?php if (!empty($r['texto'])): ?>
                          <div><?= nl2br(htmlspecialchars($r['texto'])) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
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