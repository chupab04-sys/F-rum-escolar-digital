<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

// Turmas/disciplinas atribuídas a este professor
$stmt = $pdo->prepare('SELECT id, turma, disciplina FROM professor_turmas WHERE professor_id = ? ORDER BY turma, disciplina');
$stmt->execute([$_SESSION['user_id']]);
$atribuicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alunos de cada turma (para preencher dinamicamente via JS)
$alunosPorAtribuicao = [];
foreach ($atribuicoes as $a) {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.nome
         FROM alunos_dados ad
         JOIN utilizadores u ON u.id = ad.utilizador_id
         WHERE ad.turma = ?
         ORDER BY u.nome"
    );
    $stmt->execute([$a['turma']]);
    $alunosPorAtribuicao[$a['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Notas já lançadas por este professor
$stmt = $pdo->prepare(
    "SELECT n.id, n.tipo_avaliacao, n.periodo, n.nota, n.criado_em, pt.turma, pt.disciplina, u.nome AS aluno_nome
     FROM notas n
     JOIN professor_turmas pt ON pt.id = n.professor_turma_id
     JOIN utilizadores u ON u.id = n.aluno_id
     WHERE pt.professor_id = ?
     ORDER BY n.criado_em DESC
     LIMIT 30"
);
$stmt->execute([$_SESSION['user_id']]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lançamento de Notas | CEPS</title>
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

    .cg-abas{ display:flex; gap:8px; margin-bottom:24px; }
    .cg-aba{ padding:10px 20px; border-radius:999px; border:1px solid var(--line); background:#fff; font-size:.85rem; font-weight:600; color:var(--muted); cursor:pointer; }
    .cg-aba.ativa{ background:var(--navy); color:#fff; border-color:var(--navy); }
    .cg-painel-aba{ display:none; }
    .cg-painel-aba.ativa{ display:block; }

    .cg-form{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:28px; margin-bottom:36px; }
    .cg-campo{ margin-bottom:18px; }
    .cg-campo label{ display:block; font-size:.82rem; font-weight:600; color:var(--ink); margin-bottom:6px; }
    .cg-campo input, .cg-campo select{
      width:100%; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream);
    }
    .cg-linha{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }

    .cg-lista-alunos{ display:flex; flex-direction:column; gap:8px; max-height:320px; overflow-y:auto; border:1px solid var(--line); border-radius:10px; padding:12px; }
    .cg-aluno-linha{ display:flex; align-items:center; justify-content:space-between; padding:6px 8px; border-radius:8px; }
    .cg-aluno-linha:hover{ background:var(--cream); }
    .cg-aluno-linha label{ display:flex; align-items:center; gap:10px; font-size:.88rem; color:var(--ink); cursor:pointer; }
    .cg-aluno-linha input{ width:18px; height:18px; }

    .cg-lista{ display:flex; flex-direction:column; gap:10px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; }
    .cg-tag{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--cream-deep); color:var(--navy); padding:3px 10px; border-radius:999px; }
    .cg-nota-valor{ font-family:var(--serif); font-weight:700; font-size:1.2rem; color:var(--navy); }
    .cg-btn-eliminar{ background:#fdecea; color:#a5423b; border:none; border-radius:8px; padding:6px 14px; font-size:.75rem; font-weight:600; cursor:pointer; }
    .cg-btn-eliminar:hover{ background:#f6d3ce; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:30px 0; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Lançamento de Notas</h1>
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
          <h2 style="text-align:left; margin:0;">Notas e Assiduidade</h2>
        </div>
        <a href="painel-professor.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'nota_lancada'): ?>
        <div class="cg-aviso">Nota lançada com sucesso.</div>
      <?php elseif ($mensagem === 'nota_eliminada'): ?>
        <div class="cg-aviso">Nota eliminada.</div>
      <?php elseif ($mensagem === 'assiduidade_guardada'): ?>
        <div class="cg-aviso">Assiduidade registada com sucesso.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="cg-erro">Preencha todos os campos.</div>
      <?php elseif ($erro === 'atribuicao_invalida'): ?>
        <div class="cg-erro">Essa turma/disciplina não está atribuída a si.</div>
      <?php elseif ($erro === 'aluno_invalido'): ?>
        <div class="cg-erro">Esse aluno não pertence à turma selecionada.</div>
      <?php endif; ?>

      <?php if (empty($atribuicoes)): ?>
        <p class="cg-vazio">Ainda não tem nenhuma turma/disciplina atribuída pela Direção.</p>
      <?php else: ?>

        <div class="cg-abas">
          <div class="cg-aba ativa" onclick="cgMudarAba('nota')">Lançar Nota</div>
          <div class="cg-aba" onclick="cgMudarAba('assiduidade')">Registar Assiduidade</div>
        </div>

        <!-- ===== Lançar Nota ===== -->
        <div id="aba-nota" class="cg-painel-aba ativa">
          <form class="cg-form" action="processar-nota.php" method="POST">
            <input type="hidden" name="acao" value="lancar">

            <div class="cg-campo">
              <label for="pt_nota">Turma — Disciplina</label>
              <select id="pt_nota" name="professor_turma_id" onchange="cgAtualizarAlunosNota()" required>
                <?php foreach ($atribuicoes as $a): ?>
                  <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['turma']) ?> — <?= htmlspecialchars($a['disciplina']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="cg-campo">
              <label for="aluno_id">Aluno</label>
              <select id="aluno_id" name="aluno_id" required></select>
            </div>

            <div class="cg-linha">
              <div class="cg-campo">
                <label for="periodo">Período</label>
                <select id="periodo" name="periodo" required>
                  <option value="1º Trimestre">1º Trimestre</option>
                  <option value="2º Trimestre">2º Trimestre</option>
                  <option value="3º Trimestre">3º Trimestre</option>
                </select>
              </div>
              <div class="cg-campo">
                <label for="tipo_avaliacao">Tipo de Avaliação</label>
                <input type="text" id="tipo_avaliacao" name="tipo_avaliacao" placeholder="Ex.: 1ª Avaliação, Exame" required>
              </div>
            </div>

            <div class="cg-campo">
              <label for="nota">Nota (0 a 20)</label>
              <input type="number" id="nota" name="nota" min="0" max="20" step="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary">Lançar Nota</button>
          </form>
        </div>

        <!-- ===== Registar Assiduidade ===== -->
        <div id="aba-assiduidade" class="cg-painel-aba">
          <form class="cg-form" action="processar-assiduidade.php" method="POST">
            <input type="hidden" name="acao" value="registar">

            <div class="cg-linha">
              <div class="cg-campo">
                <label for="pt_assiduidade">Turma — Disciplina</label>
                <select id="pt_assiduidade" name="professor_turma_id" onchange="cgAtualizarAlunosAssiduidade()" required>
                  <?php foreach ($atribuicoes as $a): ?>
                    <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['turma']) ?> — <?= htmlspecialchars($a['disciplina']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="cg-campo">
                <label for="data_aula">Data da aula</label>
                <input type="date" id="data_aula" name="data_aula" required>
              </div>
            </div>

            <label style="display:block; font-size:.82rem; font-weight:600; color:var(--ink); margin-bottom:10px;">Alunos (desmarque quem faltou)</label>
            <div id="cgListaAlunosAssiduidade" class="cg-lista-alunos"></div>

            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Guardar Assiduidade</button>
          </form>
        </div>

      <?php endif; ?>

      <h3 style="font-family:var(--serif); color:var(--navy); margin-bottom:16px;">Últimas notas lançadas</h3>
      <?php if (empty($notas)): ?>
        <p class="cg-vazio">Ainda não lançou nenhuma nota.</p>
      <?php else: ?>
        <div class="cg-lista">
          <?php foreach ($notas as $n): ?>
            <div class="cg-item">
              <div>
                <span class="cg-tag"><?= htmlspecialchars($n['turma']) ?> · <?= htmlspecialchars($n['disciplina']) ?></span>
                <div style="margin-top:6px; font-size:.9rem;"><b><?= htmlspecialchars($n['aluno_nome']) ?></b> — <?= htmlspecialchars($n['tipo_avaliacao']) ?> (<?= htmlspecialchars($n['periodo']) ?>)</div>
              </div>
              <div style="display:flex; align-items:center; gap:14px;">
                <span class="cg-nota-valor"><?= number_format((float)$n['nota'], 2) ?></span>
                <form action="processar-nota.php" method="POST" onsubmit="return confirm('Eliminar esta nota?');">
                  <input type="hidden" name="acao" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                  <button type="submit" class="cg-btn-eliminar">Eliminar</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <script>
    const cgAlunosPorAtribuicao = <?= json_encode($alunosPorAtribuicao, JSON_UNESCAPED_UNICODE) ?>;

    function cgMudarAba(nome){
      document.querySelectorAll('.cg-aba').forEach(a => a.classList.remove('ativa'));
      document.querySelectorAll('.cg-painel-aba').forEach(p => p.classList.remove('ativa'));
      event.currentTarget.classList.add('ativa');
      document.getElementById('aba-' + nome).classList.add('ativa');
    }

    function cgAtualizarAlunosNota(){
      const ptId = document.getElementById('pt_nota').value;
      const alunos = cgAlunosPorAtribuicao[ptId] || [];
      const select = document.getElementById('aluno_id');
      select.innerHTML = alunos.map(a => `<option value="${a.id}">${a.nome}</option>`).join('');
    }

    function cgAtualizarAlunosAssiduidade(){
      const ptId = document.getElementById('pt_assiduidade').value;
      const alunos = cgAlunosPorAtribuicao[ptId] || [];
      const container = document.getElementById('cgListaAlunosAssiduidade');
      if (alunos.length === 0) {
        container.innerHTML = '<p style="color:var(--muted); font-size:.85rem;">Nenhum aluno nesta turma.</p>';
        return;
      }
      container.innerHTML = alunos.map(a => `
        <div class="cg-aluno-linha">
          <label>
            <input type="checkbox" name="presente[${a.id}]" checked>
            ${a.nome}
          </label>
        </div>
      `).join('') + alunos.map(a => `<input type="hidden" name="aluno_ids[]" value="${a.id}">`).join('');
    }

    // Preenche logo ao carregar a página
    document.addEventListener('DOMContentLoaded', function(){
      cgAtualizarAlunosNota();
      cgAtualizarAlunosAssiduidade();
    });
  </script>
</body>
</html>
