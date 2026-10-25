<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

// Lista de professores para o formulário
$stmt = $pdo->query("SELECT id, nome FROM utilizadores WHERE perfil = 'professor' ORDER BY nome");
$professores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Atribuições já existentes
$stmt = $pdo->query("
    SELECT pt.id, pt.turma, pt.disciplina, u.nome AS professor_nome
    FROM professor_turmas pt
    JOIN utilizadores u ON u.id = pt.professor_id
    ORDER BY u.nome, pt.turma
");
$atribuicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Professores atribuídos a cada turma (para o formulário de Diretor de Turma)
$professoresPorTurma = [];
$stmt = $pdo->query("
    SELECT DISTINCT pt.turma, pt.professor_id, u.nome
    FROM professor_turmas pt
    JOIN utilizadores u ON u.id = pt.professor_id
    ORDER BY pt.turma, u.nome
");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
    $professoresPorTurma[$linha['turma']][] = ['id' => (int)$linha['professor_id'], 'nome' => $linha['nome']];
}
$turmasDisponiveis = array_keys($professoresPorTurma);
sort($turmasDisponiveis);

// Diretores de turma já definidos
$stmt = $pdo->query("
    SELECT dt.turma, u.nome AS professor_nome
    FROM diretores_turma dt
    JOIN utilizadores u ON u.id = dt.professor_id
    ORDER BY dt.turma
");
$diretores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestão de Professores | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .tp-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .tp-voltar{ font-size:.85rem; color:var(--muted); }
    .tp-voltar:hover{ color:var(--navy); }

    .tp-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .tp-erro{ background:#fdecea; border:1px solid #f3b8b0; color:#a5423b; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }

    .tp-form{
      background:#fff; border:1px solid var(--line); border-radius:14px;
      padding:24px; display:grid; grid-template-columns:1.4fr 1fr 1fr auto; gap:14px; align-items:end;
      margin-bottom:32px;
    }
    .tp-campo label{ display:block; font-size:.78rem; font-weight:600; color:var(--ink); margin-bottom:6px; }
    .tp-campo select, .tp-campo input{
      width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px;
      font-family:var(--sans); font-size:.9rem; color:var(--ink); background:var(--cream);
    }
    .tp-form button{ height:42px; }

    .tp-lista{ display:flex; flex-direction:column; gap:12px; }
    .tp-item{
      background:#fff; border:1px solid var(--line); border-radius:12px;
      padding:16px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
    }
    .tp-nome{ font-family:var(--serif); font-weight:700; color:var(--navy); }
    .tp-detalhe{ font-size:.85rem; color:var(--muted); }
    .tp-btn-eliminar{
      background:#fdecea; color:#a5423b; border:none; border-radius:8px;
      padding:8px 16px; font-size:.78rem; font-weight:600; cursor:pointer;
    }
    .tp-btn-eliminar:hover{ background:#f6d3ce; }
    .tp-vazio{ text-align:center; color:var(--muted); padding:40px 0; }

    @media (max-width:760px){ .tp-form{ grid-template-columns:1fr; } }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Gestão de Professores</h1>
        <div class="panel-user">
          <span class="role-chip">Direção</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="tp-topo">
        <div>
          <span class="eyebrow">Gestão Escolar</span>
          <h2 style="text-align:left; margin:0;">Atribuir Turmas e Disciplinas</h2>
        </div>
        <a href="painel-direcao.php" class="tp-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'criado'): ?>
        <div class="tp-aviso">Atribuição criada com sucesso.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="tp-aviso">Atribuição removida com sucesso.</div>
      <?php endif; ?>

      <?php if ($erro === 'duplicado'): ?>
        <div class="tp-erro">Este professor já está atribuído a esta turma/disciplina.</div>
      <?php elseif ($erro === 'campos'): ?>
        <div class="tp-erro">Preencha todos os campos antes de continuar.</div>
      <?php endif; ?>

      <form class="tp-form" action="processar-turma-professor.php" method="POST">
        <input type="hidden" name="acao" value="criar">
        <div class="tp-campo">
          <label for="professor_id">Professor</label>
          <select id="professor_id" name="professor_id" required>
            <option value="">Selecione um professor</option>
            <?php foreach ($professores as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="tp-campo">
          <label for="turma">Turma</label>
          <input type="text" id="turma" name="turma" placeholder="Ex.: 10ª A" required>
        </div>
        <div class="tp-campo">
          <label for="disciplina">Disciplina</label>
          <input type="text" id="disciplina" name="disciplina" placeholder="Ex.: Matemática" required>
        </div>
        <button type="submit" class="btn btn-primary">Atribuir</button>
      </form>

      <?php if (empty($atribuicoes)): ?>
        <p class="tp-vazio">Ainda não há nenhuma turma atribuída a nenhum professor.</p>
      <?php else: ?>
        <div class="tp-lista">
          <?php foreach ($atribuicoes as $a): ?>
            <div class="tp-item">
              <div>
                <span class="tp-nome"><?= htmlspecialchars($a['professor_nome']) ?></span>
                <div class="tp-detalhe"><?= htmlspecialchars($a['disciplina']) ?> · Turma <?= htmlspecialchars($a['turma']) ?></div>
              </div>
              <form action="processar-turma-professor.php" method="POST"
                    onsubmit="return confirm('Remover esta atribuição?');">
                <input type="hidden" name="acao" value="eliminar">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button type="submit" class="tp-btn-eliminar">Remover</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <h3 style="font-family:var(--serif); color:var(--navy); margin:40px 0 16px;">Diretor de Turma</h3>
      <p style="font-size:.85rem; color:var(--muted); margin-bottom:20px;">
        O Diretor de Turma é quem escolhe o Delegado dessa turma para o Grémio Estudantil.
        Só pode escolher entre professores já atribuídos a essa turma.
      </p>

      <?php if (empty($turmasDisponiveis)): ?>
        <p class="tp-vazio">Atribua primeiro professores a alguma turma, para poder definir o Diretor.</p>
      <?php else: ?>
        <form class="tp-form" action="processar-diretor-turma.php" method="POST" style="grid-template-columns:1fr 1fr auto;">
          <input type="hidden" name="acao" value="definir">
          <div class="tp-campo">
            <label for="turma_diretor">Turma</label>
            <select id="turma_diretor" name="turma" onchange="tpAtualizarProfessores()" required>
              <?php foreach ($turmasDisponiveis as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="tp-campo">
            <label for="professor_diretor">Professor (Diretor de Turma)</label>
            <select id="professor_diretor" name="professor_id" required></select>
          </div>
          <button type="submit" class="btn btn-primary">Definir</button>
        </form>

        <?php if (empty($diretores)): ?>
          <p class="tp-vazio">Ainda não definiu nenhum Diretor de Turma.</p>
        <?php else: ?>
          <div class="tp-lista">
            <?php foreach ($diretores as $d): ?>
              <div class="tp-item">
                <div>
                  <span class="tp-nome">Turma <?= htmlspecialchars($d['turma']) ?></span>
                  <div class="tp-detalhe">Diretor: <?= htmlspecialchars($d['professor_nome']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>

  <script>
    const tpProfessoresPorTurma = <?= json_encode($professoresPorTurma, JSON_UNESCAPED_UNICODE) ?>;

    function tpAtualizarProfessores(){
      const turma = document.getElementById('turma_diretor').value;
      const lista = tpProfessoresPorTurma[turma] || [];
      const select = document.getElementById('professor_diretor');
      select.innerHTML = lista.map(p => `<option value="${p.id}">${p.nome}</option>`).join('');
    }
    document.addEventListener('DOMContentLoaded', tpAtualizarProfessores);
  </script>

</body>
</html>