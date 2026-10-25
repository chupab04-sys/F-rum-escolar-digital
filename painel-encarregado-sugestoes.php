<?php
require 'auth_check.php';
protegerPainel('encarregado');
$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Caixa de Sugestões | CEPS</title>
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
    .cg-form{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:32px; max-width:640px; }
    .cg-form textarea{
      width:100%; min-height:140px; padding:14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.95rem; color:var(--ink); background:var(--cream); resize:vertical; margin-bottom:16px;
    }
    .cg-nota{ font-size:.78rem; color:var(--muted); margin-bottom:20px; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Caixa de Sugestões</h1>
        <div class="panel-user">
          <span class="role-chip">Encarregado</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cg-topo">
        <div>
          <span class="eyebrow">Espaço dos Pais</span>
          <h2 style="text-align:left; margin:0;">Enviar Sugestão à Direção</h2>
        </div>
        <a href="painel-encarregado.php" class="cg-voltar">← Voltar ao painel</a>
      </div>

      <?php if ($mensagem === 'enviado'): ?>
        <div class="cg-aviso">Sugestão enviada com sucesso. Obrigado pelo seu contributo!</div>
      <?php endif; ?>
      <?php if ($erro === 'vazio'): ?>
        <div class="cg-erro">Escreva a sua sugestão antes de enviar.</div>
      <?php endif; ?>

      <form class="cg-form" action="processar-sugestao.php" method="POST">
        <p class="cg-nota">🔒 Esta sugestão é totalmente anónima — a Direção não fica a saber quem a enviou.</p>
        <textarea name="mensagem" placeholder="Escreva aqui a sua sugestão, ideia ou preocupação..." required></textarea>
        <button type="submit" class="btn btn-primary">Enviar Sugestão</button>
      </form>

    </div>
  </div>

</body>
</html>
