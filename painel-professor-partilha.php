<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$stmt = $pdo->prepare(
    "SELECT p.id, p.titulo, p.descricao, p.nome_ficheiro, p.criado_em, p.autor_id, u.nome AS autor_nome
     FROM partilhas_pedagogicas p
     JOIN utilizadores u ON u.id = p.autor_id
     ORDER BY p.criado_em DESC"
);
$stmt->execute();
$partilhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Partilha Pedagógica | CEPS</title>
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

    .iv-hero{
      background: linear-gradient(160deg, var(--iv-navy) 0%, var(--iv-navy-2) 100%);
      position: relative; overflow: hidden; padding: 38px 0 46px;
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
    .iv-eyebrow{ font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--iv-brass); margin-bottom:10px; }
    .iv-hero h1{ font-family:'Playfair Display',serif; font-weight:700; font-size:2rem; color:#fff; margin-bottom:8px; }
    .iv-hero p{ color:#B9C2D6; font-size:.92rem; max-width:56ch; }
    .iv-stats{ display:flex; gap:14px; margin-top:22px; flex-wrap:wrap; }
    .iv-stat{ background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:12px 20px; backdrop-filter:blur(4px); }
    .iv-stat .num{ font-family:'Playfair Display',serif; font-weight:700; font-size:1.5rem; color:var(--iv-brass-light); line-height:1; }
    .iv-stat .lbl{ font-size:.68rem; color:#B9C2D6; text-transform:uppercase; letter-spacing:.06em; margin-top:4px; }

    .iv-corpo{ padding: 34px 0 60px; }

    .iv-form{
      background:#fff; border-radius:14px; padding:28px; margin-bottom:32px;
      box-shadow:0 8px 24px -14px rgba(14,31,59,0.2);
    }
    .iv-form-titulo{ font-family:'Playfair Display',serif; color:var(--iv-navy); font-size:1.1rem; margin-bottom:18px; }
    .iv-campo{ margin-bottom:16px; }
    .iv-campo label{ display:block; font-size:.82rem; font-weight:600; color:#3a4258; margin-bottom:6px; }
    .iv-campo input[type=text], .iv-campo textarea, .iv-campo input[type=file]{
      width:100%; padding:12px 14px; border:1px solid #e0dacb; border-radius:10px;
      font-family:'Inter',sans-serif; font-size:.95rem; color:#24262b; background:#faf8f3;
    }
    .iv-campo textarea{ min-height:90px; resize:vertical; }
    .iv-dica{ font-size:.78rem; color:#8a90a3; margin-top:6px; }
    .iv-btn{
      background:var(--iv-navy); color:#fff; border:none; border-radius:999px;
      padding:12px 26px; font-size:.88rem; font-weight:600; cursor:pointer;
    }
    .iv-btn:hover{ background:var(--iv-navy-2); }

    .iv-lista{ display:flex; flex-direction:column; gap:14px; }
    .iv-item{
      background:#fff; border-radius:12px; padding:22px 26px;
      border-left:4px solid var(--iv-brass); box-shadow:0 8px 24px -14px rgba(14,31,59,0.25);
      transition: transform .18s ease, box-shadow .18s ease;
      display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;
    }
    .iv-item:hover{ transform:translateY(-2px); box-shadow:0 14px 30px -14px rgba(14,31,59,0.35); }
    .iv-item h3{ font-family:'Playfair Display',serif; font-size:1.08rem; color:var(--iv-navy); margin-bottom:6px; }
    .iv-item p{ font-size:.88rem; color:#3a4258; line-height:1.6; margin-bottom:8px; }
    .iv-meta{ font-size:.75rem; color:#8a90a3; }
    .iv-ficheiro{
      display:inline-block; margin-top:8px; font-size:.82rem; color:var(--iv-navy);
      font-weight:600; text-decoration:none; background:var(--cream-deep); padding:8px 16px; border-radius:999px;
    }
    .iv-ficheiro:hover{ background:#e0dacb; }
    .iv-btn-eliminar{
      background:#fdecea; color:#a5423b; border:none; border-radius:8px;
      padding:8px 16px; font-size:.78rem; font-weight:600; cursor:pointer; white-space:nowrap;
    }
    .iv-btn-eliminar:hover{ background:#f6d3ce; }
    .iv-vazio{ text-align:center; color:#8a90a3; padding:60px 0; }
    .iv-aviso{ background:#eaf6ee; border:1px solid #b8e0c4; color:#2f7a45; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
    .iv-erro{ background:#fdecea; border:1px solid #f3b8b0; color:#a5423b; padding:12px 16px; border-radius:10px; font-size:.85rem; margin-bottom:22px; }
  </style>
</head>
<body>

  <div class="iv-hero">
    <div class="container iv-hero-inner">
      <a href="painel-professor.php" class="iv-voltar">← Voltar ao painel</a>
      <div class="iv-eyebrow">Espaço Docente</div>
      <h1>Partilha Pedagógica</h1>
      <p>Materiais de apoio, testes e metodologias partilhados entre professores — o seu melhor recurso pode ajudar um colega amanhã.</p>
      <div class="iv-stats">
        <div class="iv-stat"><div class="num"><?= count($partilhas) ?></div><div class="lbl">Partilhas no total</div></div>
      </div>
    </div>
  </div>

  <div class="iv-corpo">
    <div class="container">

      <?php if ($mensagem === 'publicado'): ?>
        <div class="iv-aviso">Partilha publicada com sucesso.</div>
      <?php elseif ($mensagem === 'eliminado'): ?>
        <div class="iv-aviso">Partilha eliminada.</div>
      <?php endif; ?>
      <?php if ($erro === 'campos'): ?>
        <div class="iv-erro">Preencha o título e a descrição.</div>
      <?php elseif ($erro === 'tipo_invalido'): ?>
        <div class="iv-erro">Tipo de ficheiro não permitido. Use PDF, Word, PowerPoint, Excel ou imagem.</div>
      <?php elseif ($erro === 'tamanho'): ?>
        <div class="iv-erro">O ficheiro é demasiado grande (máximo 10 MB).</div>
      <?php endif; ?>

      <form class="iv-form" action="processar-partilha.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="publicar">
        <div class="iv-form-titulo">Nova Partilha</div>

        <div class="iv-campo">
          <label for="titulo">Título</label>
          <input type="text" id="titulo" name="titulo" required>
        </div>

        <div class="iv-campo">
          <label for="descricao">Descrição / Metodologia</label>
          <textarea id="descricao" name="descricao" required></textarea>
        </div>

        <div class="iv-campo">
          <label for="ficheiro">Ficheiro (opcional)</label>
          <input type="file" id="ficheiro" name="ficheiro" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png">
          <p class="iv-dica">Só se quiser anexar um material. Formatos aceites: PDF, Word, PowerPoint, Excel, JPG, PNG. Máximo 10 MB.</p>
        </div>

        <button type="submit" class="iv-btn">Partilhar com os Colegas</button>
      </form>

      <?php if (empty($partilhas)): ?>
        <p class="iv-vazio">Ainda não há nenhuma partilha pedagógica.</p>
      <?php else: ?>
        <div class="iv-lista">
          <?php foreach ($partilhas as $p): ?>
            <div class="iv-item">
              <div>
                <h3><?= htmlspecialchars($p['titulo']) ?></h3>
                <p><?= nl2br(htmlspecialchars($p['descricao'])) ?></p>
                <div class="iv-meta">Por <?= htmlspecialchars($p['autor_nome']) ?> · <?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></div>
                <?php if (!empty($p['nome_ficheiro'])): ?>
                  <a href="download-partilha.php?id=<?= (int)$p['id'] ?>" class="iv-ficheiro">📎 <?= htmlspecialchars($p['nome_ficheiro']) ?></a>
                <?php endif; ?>
              </div>
              <?php if ((int)$p['autor_id'] === (int)$_SESSION['user_id']): ?>
                <form action="processar-partilha.php" method="POST" onsubmit="return confirm('Eliminar esta partilha?');">
                  <input type="hidden" name="acao" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="iv-btn-eliminar">Eliminar</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>