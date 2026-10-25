<?php
require 'auth_check.php';
protegerPainel('encarregado');
require 'config.php';
require 'contadores.php';
$novidades = obterContadores($pdo, $_SESSION['user_id'], 'encarregado');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel do Encarregado | CEPS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/panel.css">
  <?php include 'tema-aplicar.php'; ?>
  <style>
    /* ---- Cartões estilo placa institucional (CEPS) ---- */
    .cp-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:22px; margin-top:28px; }
    .cp-card{
      position:relative; background:#0E1F3B; border-radius:6px; color:#F6F3EC;
      box-shadow:0 16px 34px -16px rgba(14,31,59,0.5); overflow:hidden; display:flex; flex-direction:column;
      transition: box-shadow 0.28s ease, transform 0.28s ease; cursor:pointer;
    }
    .cp-card:hover, .cp-card:focus-within{
      box-shadow:
        0 0 0 1.5px rgba(255,255,255,0.9),
        0 0 18px 3px rgba(255,255,255,0.55),
        0 0 42px 10px rgba(255,255,255,0.22),
        0 16px 34px -16px rgba(14,31,59,0.5);
      transform: translateY(-3px);
    }
    .cp-card::before{
      content:""; position:absolute; top:0; left:0; right:0; height:4px;
      background:linear-gradient(90deg,#E4CD9A,#C6A15B 40%,#E4CD9A 100%); z-index:3;
    }
    .cp-visual{ position:relative; height:120px; overflow:hidden; }
    .cp-visual svg{ width:100%; height:100%; display:block; }
    .cp-visual::after{ content:""; position:absolute; inset:0; background:linear-gradient(180deg,rgba(14,31,59,0.1) 0%, #0E1F3B 96%); }
    .cp-body{ padding:20px 24px 24px; display:flex; flex-direction:column; flex:1; }
    .cp-body h3{ font-family:'Playfair Display',serif; font-weight:700; font-size:20px; line-height:1.2; margin:0 0 4px; color:#fff; }
    .cp-rule{ width:28px; height:2px; background:#C6A15B; margin:12px 0 12px; }
    .cp-tagline{ font-family:'Playfair Display',serif; font-style:italic; font-weight:600; font-size:13.5px; line-height:1.5; color:#E4CD9A; margin:0 0 12px; }
    .cp-desc{ font-size:12.5px; line-height:1.6; color:#B9C2D6; margin:0 0 18px; flex:1; }
    .cp-cta{ display:flex; align-items:center; gap:8px; font-size:11.5px; font-weight:600; letter-spacing:1.1px; text-transform:uppercase; color:#fff; border-top:1px solid rgba(255,255,255,0.15); padding-top:14px; }
    .cp-cta span{ color:#C6A15B; font-size:14px; }

    /* ---- Indicador de novidades ---- */
    .cp-badge{
      position:absolute; top:12px; right:12px; z-index:4;
      min-width:22px; height:22px; padding:0 6px; border-radius:999px;
      background:#a5423b; color:#fff; font-family:'Inter',sans-serif; font-weight:700; font-size:12px;
      display:flex; align-items:center; justify-content:center;
      box-shadow:0 2px 6px rgba(0,0,0,.35);
    }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1>Painel do Encarregado</h1>
        <div class="panel-user">
          <span class="role-chip">Encarregado</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body">
      <span class="eyebrow">Espaço dos Pais</span>
      <h2>Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>!</h2>
      <p>Estrutura planeada para o Painel dos Encarregados de Educação, focada no acompanhamento escolar dos filhos.</p>

      <div class="cp-grid">

        <a href="painel-encarregado-circulares.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['enc-circulares'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['enc-circulares'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="165" y="42" width="70" height="48" rx="2" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <path d="M165 42 L200 68 L235 42" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <path d="M248 40 Q262 45 248 57" stroke="#C6A15B" stroke-width="2.2" fill="none"/>
            <path d="M256 32 Q278 45 256 64" stroke="#C6A15B" stroke-width="2.2" fill="none" opacity="0.6"/>
          </svg></div>
          <div class="cp-body">
            <h3>Circulares Oficiais</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Tudo o que a escola comunica, num só lugar."</p>
            <p class="cp-desc">Todas as notas e orientações emitidas pela direção.</p>
            <div class="cp-cta">Ver circulares <span>→</span></div>
          </div>
        </a>

        <a href="painel-encarregado-calendario.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['enc-calendario'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['enc-calendario'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="160" y="34" width="66" height="56" rx="3" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <line x1="160" y1="50" x2="226" y2="50" stroke="#E4CD9A" stroke-width="2"/>
            <path d="M238 70 L255 58 L272 70 V90 H238 Z" fill="none" stroke="#C6A15B" stroke-width="2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Calendário da Família</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"As datas da escola, também no seu calendário."</p>
            <p class="cp-desc">Reuniões de pais, entrega de notas e eventos.</p>
            <div class="cp-cta">Ver calendário <span>→</span></div>
          </div>
        </a>

        <a href="painel-encarregado-rendimento.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['enc-rendimento'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['enc-rendimento'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="170" y="72" width="14" height="22" fill="#B9C2D6" opacity="0.5"/>
            <rect x="190" y="62" width="14" height="32" fill="#E4CD9A"/>
            <rect x="210" y="68" width="14" height="26" fill="#7A2331" opacity="0.85"/>
            <circle cx="248" cy="55" r="15" fill="none" stroke="#C6A15B" stroke-width="2.2"/>
            <path d="M240 55 l6 6 l11 -13" stroke="#C6A15B" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          </svg></div>
          <div class="cp-body">
            <h3>Rendimento e Notas</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"O percurso do seu educando, sempre à vista."</p>
            <p class="cp-desc">Progresso académico e assiduidade do educando.</p>
            <div class="cp-cta">Consultar notas <span>→</span></div>
          </div>
        </a>

        <a href="painel-encarregado-associacao.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['enc-associacao'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['enc-associacao'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M160 60 L188 60 L200 48" fill="none" stroke="#E4CD9A" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M240 60 L212 60 L200 72" fill="none" stroke="#C6A15B" stroke-width="2.4" stroke-linecap="round"/>
            <circle cx="200" cy="60" r="5" fill="#7A2331"/>
          </svg></div>
          <div class="cp-body">
            <h3>Associação de Pais</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Famílias unidas, escola mais forte."</p>
            <p class="cp-desc">Discussão de melhorias e organização de atividades.</p>
            <div class="cp-cta">Participar <span>→</span></div>
          </div>
        </a>

        <a href="painel-encarregado-trocas.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['enc-trocas'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['enc-trocas'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M170 50 h60 l-12 -12" fill="none" stroke="#E4CD9A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M230 50 l-12 12" fill="none" stroke="#E4CD9A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M230 78 h-60 l12 12" fill="none" stroke="#C6A15B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M170 78 l12 -12" fill="none" stroke="#C6A15B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg></div>
          <div class="cp-body">
            <h3>Espaço de Trocas</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Um manual usado pode começar outro ano letivo."</p>
            <p class="cp-desc">Doação ou troca de manuais, fardas e materiais usados.</p>
            <div class="cp-cta">Ver trocas <span>→</span></div>
          </div>
        </a>

        <a href="painel-encarregado-sugestoes.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="160" y="45" width="90" height="45" rx="4" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <rect x="185" y="35" width="40" height="10" rx="2" fill="#C6A15B"/>
            <path d="M175 60 l30 18 l30 -18" fill="none" stroke="#7A2331" stroke-width="2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Caixa de Sugestões</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"A voz da família, ouvida com atenção."</p>
            <p class="cp-desc">Envie sugestões ou preocupações à Direção, de forma anónima.</p>
            <div class="cp-cta">Enviar sugestão <span>→</span></div>
          </div>
        </a>

      </div>

      <?php include 'rodape-institucional.php'; ?>

    </div>
  </div>

</body>
</html>