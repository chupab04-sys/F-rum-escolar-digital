<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'direcao-cadastros');

// Filtro opcional por perfil (?perfil=aluno | encarregado | todos)
$filtro = $_GET['perfil'] ?? 'todos';
$condicaoPerfil = '';
$parametros = [];

if ($filtro === 'aluno' || $filtro === 'encarregado') {
    $condicaoPerfil = 'AND u.perfil = ?';
    $parametros[] = $filtro;
}

$sql = "
    SELECT
        u.id, u.nome, u.email, u.perfil, u.ativo,
        ad.turma, ad.classe, ad.curso, ad.sala, ad.numero_aluno,
        ed.nome_aluno, ed.numero_aluno AS enc_numero_aluno, ed.parentesco, ed.telefone
    FROM utilizadores u
    LEFT JOIN alunos_dados ad ON ad.utilizador_id = u.id
    LEFT JOIN encarregados_dados ed ON ed.utilizador_id = u.id
    WHERE u.perfil IN ('aluno', 'encarregado') $condicaoPerfil
    ORDER BY u.perfil, u.nome
";
$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aprovação de Cadastros | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <style>
    .cad-topo{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:26px; }
    .cad-voltar{ font-size:.85rem; color:var(--muted); }
    .cad-voltar:hover{ color:var(--navy); }

    .cad-filtros{ display:flex; gap:8px; margin-bottom:28px; }
    .cad-filtro{
      padding:8px 18px; border-radius:999px; border:1px solid var(--line);
      font-size:.82rem; font-weight:600; color:var(--muted); background:#fff;
    }
    .cad-filtro.ativo{ background:var(--navy); color:#fff; border-color:var(--navy); }

    .cad-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }

    .cad-lista{ display:flex; flex-direction:column; gap:14px; }
    .cad-item{
      background:#fff; border:1px solid var(--line); border-radius:14px;
      padding:20px 24px; display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;
    }
    .cad-info{ display:flex; flex-direction:column; gap:4px; min-width:220px; }
    .cad-nome{ font-family:var(--serif); font-weight:700; font-size:1.05rem; color:var(--navy); }
    .cad-email{ font-size:.8rem; color:var(--muted); }
    .cad-detalhe{ font-size:.82rem; color:var(--ink); margin-top:4px; }
    .cad-detalhe b{ color:var(--navy); }

    .cad-perfil-tag{
      font-size:.68rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
      padding:4px 10px; border-radius:999px; background:var(--cream-deep); color:var(--navy);
    }

    .cad-estado{ font-size:.75rem; font-weight:700; padding:5px 12px; border-radius:999px; }
    .cad-estado.ativo{ background:#eaf6ee; color:#2f7a45; }
    .cad-estado.inativo{ background:#fdecea; color:#a5423b; }

    .cad-acoes{ display:flex; gap:8px; }
    .cad-btn{
      padding:8px 16px; border-radius:8px; border:none; cursor:pointer;
      font-size:.78rem; font-weight:600; font-family:var(--sans);
    }
    .cad-btn-toggle{ background:var(--navy); color:#fff; }
    .cad-btn-toggle:hover{ background:var(--navy-deep); }
    .cad-btn-eliminar{ background:#fdecea; color:#a5423b; }
    .cad-btn-eliminar:hover{ background:#f6d3ce; }

    .cad-vazio{ text-align:center; color:var(--muted); padding:60px 0; }

    @media (max-width:640px){
      .cad-item{ flex-direction:column; align-items:flex-start; }
      .cad-acoes{ width:100%; }
      .cad-btn{ flex:1; }
    }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Aprovação de Cadastros</h1>
        <div class="panel-user">
          <span class="role-chip">Direção</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cad-topo">
        <div>
          <span class="eyebrow">Gestão Escolar</span>
          <h2 style="text-align:left; margin:0;">Contas de Alunos e Encarregados</h2>
        </div>
        <a href="painel-direcao.php" class="cad-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'atualizado'): ?>
        <div class="cad-aviso">Conta atualizada com sucesso.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="cad-aviso">Conta eliminada com sucesso.</div>
      <?php endif; ?>

      <div class="cad-filtros">
        <a href="?perfil=todos" class="cad-filtro <?= $filtro === 'todos' ? 'ativo' : '' ?>">Todos</a>
        <a href="?perfil=aluno" class="cad-filtro <?= $filtro === 'aluno' ? 'ativo' : '' ?>">Alunos</a>
        <a href="?perfil=encarregado" class="cad-filtro <?= $filtro === 'encarregado' ? 'ativo' : '' ?>">Encarregados</a>
      </div>

      <?php if (empty($contas)): ?>
        <p class="cad-vazio">Ainda não há contas registadas nesta categoria.</p>
      <?php else: ?>
        <div class="cad-lista">
          <?php foreach ($contas as $conta): ?>
            <div class="cad-item">
              <div class="cad-info">
                <div style="display:flex; align-items:center; gap:8px;">
                  <span class="cad-nome"><?= htmlspecialchars($conta['nome']) ?></span>
                  <span class="cad-perfil-tag"><?= htmlspecialchars($conta['perfil']) ?></span>
                </div>
                <span class="cad-email"><?= htmlspecialchars($conta['email']) ?></span>

                <?php if ($conta['perfil'] === 'aluno'): ?>
                  <span class="cad-detalhe">
                    <b>Turma:</b> <?= htmlspecialchars($conta['turma'] ?? '—') ?> ·
                    <b>Classe:</b> <?= htmlspecialchars($conta['classe'] ?? '—') ?> ·
                    <b>Sala:</b> <?= htmlspecialchars($conta['sala'] ?? '—') ?> ·
                    <b>Nº:</b> <?= htmlspecialchars($conta['numero_aluno'] ?? '—') ?>
                    <?php if (!empty($conta['curso'])): ?>
                      · <b>Curso:</b> <?= htmlspecialchars($conta['curso']) ?>
                    <?php endif; ?>
                  </span>
                <?php elseif ($conta['perfil'] === 'encarregado'): ?>
                  <span class="cad-detalhe">
                    <b>Educando:</b> <?= htmlspecialchars($conta['nome_aluno'] ?? '—') ?>
                    (Nº <?= htmlspecialchars($conta['enc_numero_aluno'] ?? '—') ?>) ·
                    <b>Parentesco:</b> <?= htmlspecialchars($conta['parentesco'] ?? '—') ?> ·
                    <b>Telefone:</b> <?= htmlspecialchars($conta['telefone'] ?? '—') ?>
                  </span>
                <?php endif; ?>
              </div>

              <div style="display:flex; align-items:center; gap:16px;">
                <span class="cad-estado <?= $conta['ativo'] ? 'ativo' : 'inativo' ?>">
                  <?= $conta['ativo'] ? 'Ativo' : 'Inativo' ?>
                </span>

                <div class="cad-acoes">
                  <form action="processar-acao-cadastro.php" method="POST">
                    <input type="hidden" name="id" value="<?= (int)$conta['id'] ?>">
                    <input type="hidden" name="acao" value="alternar_ativo">
                    <input type="hidden" name="perfil_filtro" value="<?= htmlspecialchars($filtro) ?>">
                    <button type="submit" class="cad-btn cad-btn-toggle">
                      <?= $conta['ativo'] ? 'Desativar' : 'Ativar' ?>
                    </button>
                  </form>

                  <form action="processar-acao-cadastro.php" method="POST"
                        onsubmit="return confirm('Eliminar definitivamente a conta de <?= htmlspecialchars(addslashes($conta['nome'])) ?>? Esta ação não pode ser desfeita.');">
                    <input type="hidden" name="id" value="<?= (int)$conta['id'] ?>">
                    <input type="hidden" name="acao" value="eliminar">
                    <input type="hidden" name="perfil_filtro" value="<?= htmlspecialchars($filtro) ?>">
                    <button type="submit" class="cad-btn cad-btn-eliminar">Eliminar</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
