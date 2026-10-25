<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

// Lista de turmas conhecidas (dos alunos já registados + turmas atribuídas
// a professores), para evitar erros de digitação ao escolher uma turma.
$stmt = $pdo->query(
    "SELECT DISTINCT turma FROM (
        SELECT turma FROM alunos_dados
        UNION
        SELECT turma FROM professor_turmas
     ) t
     ORDER BY turma"
);
$turmas = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare(
    "SELECT id, titulo, descricao, tipo, data_evento, turma_alvo, para_professores, para_alunos, para_encarregados
     FROM agenda_eventos
     WHERE autor_id = ?
     ORDER BY data_evento DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda Oficial | CEPS</title>
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
    .cg-campo input[type=text], .cg-campo input[type=date], .cg-campo textarea, .cg-campo select{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-campo textarea{ min-height:80px; resize:vertical; }
    .cg-linha{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
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
    .cg-tag.tipo{ background:var(--navy); color:#fff; }
    .cg-data{ font-size:.78rem; color:var(--muted); margin-top:8px; font-weight:600; }
    .cg-btn-eliminar{ background:#fdecea; color:#a5423b; border:none; border-radius:8px; padding:8px 16px; font-size:.78rem; font-weight:600; cursor:pointer; white-space:nowrap; }
    .cg-btn-eliminar:hover{ background:#f6d3ce; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:40px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Agenda Oficial</h1>
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
          <h2 style="text-align:left; margin:0;">Reuniões, Feriados e Provas</h2>
        </div>
        <a href="painel-direcao.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'criado'): ?>
        <div class="cg-aviso">Evento criado com sucesso.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="cg-aviso">Evento eliminado.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="cg-erro">Preencha o título, tipo e data.</div>
      <?php elseif ($erro === 'publico'): ?>
        <div class="cg-erro">Escolha pelo menos um público-alvo.</div>
      <?php endif; ?>

      <form class="cg-form" action="processar-agenda.php" method="POST">
        <input type="hidden" name="acao" value="criar">

        <div class="cg-linha">
          <div class="cg-campo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" required>
          </div>
          <div class="cg-campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo" required>
              <option value="Feriado">Feriado</option>
              <option value="Reunião">Reunião</option>
              <option value="Conselho de Notas">Conselho de Notas</option>
              <option value="Prova">Prova</option>
              <option value="Evento">Evento</option>
            </select>
          </div>
        </div>

        <div class="cg-linha">
          <div class="cg-campo">
            <label for="data_evento">Data</label>
            <input type="date" id="data_evento" name="data_evento" required>
          </div>
          <div class="cg-campo">
            <label for="turma_alvo">Turma (opcional)</label>
            <select id="turma_alvo" name="turma_alvo">
              <option value="">Escola toda</option>
              <?php foreach ($turmas as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="cg-campo">
          <label for="descricao">Descrição (opcional)</label>
          <textarea id="descricao" name="descricao"></textarea>
        </div>

        <label style="display:block; font-size:.82rem; font-weight:600; color:var(--ink); margin-bottom:10px;">Público-alvo</label>
        <div class="cg-publico">
          <label><input type="checkbox" name="para_professores"> Professores</label>
          <label><input type="checkbox" name="para_alunos"> Alunos</label>
          <label><input type="checkbox" name="para_encarregados"> Encarregados</label>
        </div>

        <button type="submit" class="btn btn-primary">Criar Evento</button>
      </form>

      <h3 style="font-family:var(--serif); color:var(--navy); margin-bottom:16px;">Eventos criados</h3>

      <?php if (empty($eventos)): ?>
        <p class="cg-vazio">Ainda não criou nenhum evento.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($eventos as $e): ?>
            <div class="cg-item">
              <div class="cg-item-topo">
                <div>
                  <h3><?= htmlspecialchars($e['titulo']) ?></h3>
                  <?php if (!empty($e['descricao'])): ?><p><?= nl2br(htmlspecialchars($e['descricao'])) ?></p><?php endif; ?>
                  <div class="cg-tags">
                    <span class="cg-tag tipo"><?= htmlspecialchars($e['tipo']) ?></span>
                    <span class="cg-tag"><?= $e['turma_alvo'] ? 'Turma ' . htmlspecialchars($e['turma_alvo']) : 'Escola toda' ?></span>
                    <?php if ($e['para_professores']): ?><span class="cg-tag">Professores</span><?php endif; ?>
                    <?php if ($e['para_alunos']): ?><span class="cg-tag">Alunos</span><?php endif; ?>
                    <?php if ($e['para_encarregados']): ?><span class="cg-tag">Encarregados</span><?php endif; ?>
                  </div>
                  <div class="cg-data">📅 <?= date('d/m/Y', strtotime($e['data_evento'])) ?></div>
                </div>
                <form action="processar-agenda.php" method="POST" onsubmit="return confirm('Eliminar este evento?');">
                  <input type="hidden" name="acao" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                  <button type="submit" class="cg-btn-eliminar">Eliminar</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
