<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';
require 'contadores.php';
$novidades = obterContadores($pdo, $_SESSION['user_id'], 'direcao');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel da Direção | CEPS</title>
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
        <h1>Painel da Direção</h1>
        <div class="panel-user">
          <span class="role-chip">Direção</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body">
      <span class="eyebrow">Gestão Escolar</span>
      <h2>Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>!</h2>
      <p>Estrutura planeada para o Painel da Direção — o centro de controlo administrativo e pedagógico da escola.</p>

      <div class="cp-grid">

        <a href="painel-direcao-comunicado.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M150 40 L230 60 L230 84 L150 96 Z" fill="#E4CD9A"/>
            <rect x="130" y="52" width="22" height="34" rx="4" fill="#C6A15B"/>
            <circle cx="235" cy="72" r="4" fill="#7A2331"/>
            <path d="M245 55 Q262 60 245 75" stroke="#C6A15B" stroke-width="2.4" fill="none" opacity="0.9"/>
            <path d="M255 45 Q280 60 255 90" stroke="#C6A15B" stroke-width="2.4" fill="none" opacity="0.6"/>
            <path d="M265 35 Q300 60 265 100" stroke="#C6A15B" stroke-width="2.4" fill="none" opacity="0.35"/>
          </svg></div>
          <div class="cp-body">
            <h3>Comunicado Geral</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Uma mensagem, entregue a toda a escola."</p>
            <p class="cp-desc">Escreva e escolha o público: Professores, Alunos e/ou Encarregados.</p>
            <div class="cp-cta">Emitir comunicado <span>→</span></div>
          </div>
        </a>

        <a href="painel-direcao-agenda.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="165" y="42" width="70" height="52" rx="3" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <line x1="165" y1="58" x2="235" y2="58" stroke="#E4CD9A" stroke-width="2"/>
            <path d="M200 30 q0 -12 12 -12 q12 0 12 12 q0 14 -12 22 q-12 -8 -12 -22 Z" fill="#7A2331" opacity="0.85"/>
            <circle cx="212" cy="30" r="4" fill="#F6F3EC"/>
          </svg></div>
          <div class="cp-body">
            <h3>Agenda Oficial</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"O calendário que rege todo o ano letivo."</p>
            <p class="cp-desc">Reuniões gerais, feriados e conselhos de notas.</p>
            <div class="cp-cta">Ver agenda <span>→</span></div>
          </div>
        </a>

        <a href="painel-direcao-cadastros.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['direcao-cadastros'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['direcao-cadastros'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="155" y="38" width="90" height="60" rx="6" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <circle cx="180" cy="60" r="9" fill="none" stroke="#C6A15B" stroke-width="2"/>
            <line x1="196" y1="56" x2="228" y2="56" stroke="#C6A15B" stroke-width="2"/>
            <line x1="196" y1="66" x2="220" y2="66" stroke="#C6A15B" stroke-width="2" opacity="0.6"/>
            <circle cx="245" cy="80" r="16" fill="#17284A" stroke="#7A2331" stroke-width="2"/>
            <path d="M238 80 l5 5 l9 -11" stroke="#7A2331" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          </svg></div>
          <div class="cp-body">
            <h3>Aprovação de Cadastros</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Cada acesso à plataforma, validado com cuidado."</p>
            <p class="cp-desc">Validação de novos alunos, encarregados e turmas.</p>
            <div class="cp-cta">Rever cadastros <span>→</span></div>
          </div>
        </a>

        <a href="painel-direcao-turmas.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <g>
              <circle cx="165" cy="42" r="7" fill="#E4CD9A"/><rect x="153" y="52" width="24" height="16" rx="2" fill="#E4CD9A" opacity="0.8"/>
              <circle cx="200" cy="42" r="7" fill="#C6A15B"/><rect x="188" y="52" width="24" height="16" rx="2" fill="#C6A15B" opacity="0.8"/>
              <circle cx="235" cy="42" r="7" fill="#B9C2D6" opacity="0.7"/><rect x="223" y="52" width="24" height="16" rx="2" fill="#B9C2D6" opacity="0.5"/>
              <circle cx="182" cy="80" r="7" fill="#7A2331"/><rect x="170" y="90" width="24" height="14" rx="2" fill="#7A2331" opacity="0.8"/>
              <circle cx="218" cy="80" r="7" fill="#E4CD9A" opacity="0.8"/><rect x="206" y="90" width="24" height="14" rx="2" fill="#E4CD9A" opacity="0.6"/>
            </g>
          </svg></div>
          <div class="cp-body">
            <h3>Gestão de Professores</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Uma equipa bem coordenada ensina melhor."</p>
            <p class="cp-desc">Atribuição de turmas e disciplinas a cada professor.</p>
            <div class="cp-cta">Gerir atribuições <span>→</span></div>
          </div>
        </a>

        <a href="painel-direcao-reunioes.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['direcao-reunioes'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['direcao-reunioes'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <circle cx="200" cy="60" r="26" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <circle cx="200" cy="30" r="6" fill="#C6A15B"/>
            <circle cx="228" cy="46" r="6" fill="#C6A15B"/>
            <circle cx="228" cy="76" r="6" fill="#C6A15B"/>
            <circle cx="200" cy="92" r="6" fill="#C6A15B"/>
            <circle cx="172" cy="76" r="6" fill="#C6A15B"/>
            <circle cx="172" cy="46" r="6" fill="#C6A15B"/>
          </svg></div>
          <div class="cp-body">
            <h3>Sala de Reuniões Virtual</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Decisões importantes, discutidas em conjunto."</p>
            <p class="cp-desc">Debate interno sobre desempenho e planeamento.</p>
            <div class="cp-cta">Entrar na sala <span>→</span></div>
          </div>
        </a>

        <a href="painel-direcao-sugestoes.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['direcao-sugestoes'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['direcao-sugestoes'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="160" y="45" width="90" height="45" rx="4" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <rect x="185" y="35" width="40" height="10" rx="2" fill="#C6A15B"/>
            <path d="M175 60 l30 18 l30 -18" fill="none" stroke="#7A2331" stroke-width="2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Caixa de Sugestões</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Toda a escola tem voz — e é ouvida aqui."</p>
            <p class="cp-desc">Receção e triagem de feedback da comunidade escolar.</p>
            <div class="cp-cta">Ver sugestões <span>→</span></div>
          </div>
        </a>

        <a href="painel-direcao-associacao.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['direcao-associacao'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['direcao-associacao'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M160 60 L188 60 L200 48" fill="none" stroke="#E4CD9A" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M240 60 L212 60 L200 72" fill="none" stroke="#C6A15B" stroke-width="2.4" stroke-linecap="round"/>
            <circle cx="200" cy="60" r="5" fill="#7A2331"/>
          </svg></div>
          <div class="cp-body">
            <h3>Associação de Pais</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Ouvir os pais é construir uma escola melhor."</p>
            <p class="cp-desc">Acompanhe e responda às discussões dos Encarregados.</p>
            <div class="cp-cta">Ver discussões <span>→</span></div>
          </div>
        </a>

      </div>

      <?php include 'rodape-institucional.php'; ?>

    </div>
  </div>

</body>
</html>