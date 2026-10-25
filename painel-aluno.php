<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';
require 'contadores.php';
$novidades = obterContadores($pdo, $_SESSION['user_id'], 'aluno');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel do Aluno | CEPS</title>
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
        <h1>Painel do Aluno</h1>
        <div class="panel-user">
          <span class="role-chip">Aluno</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body">
      <span class="eyebrow">Espaço do Estudante</span>
      <h2>Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>!</h2>
      <p>Esta é a estrutura planeada para o Painel do Aluno. Cada secção abaixo será construída na próxima fase, ligada à base de dados.</p>

      <div class="cp-grid">

        <a href="painel-aluno-avisos.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['aluno-oficial'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['aluno-oficial'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="150" y="30" width="100" height="70" rx="3" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <circle cx="200" cy="30" r="5" fill="#7A2331"/>
            <line x1="165" y1="50" x2="235" y2="50" stroke="#C6A15B" stroke-width="2"/>
            <line x1="165" y1="64" x2="220" y2="64" stroke="#C6A15B" stroke-width="2" opacity="0.7"/>
            <line x1="165" y1="78" x2="228" y2="78" stroke="#C6A15B" stroke-width="2" opacity="0.5"/>
          </svg></div>
          <div class="cp-body">
            <h3>Painel Oficial</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"A informação oficial, sem intermediários."</p>
            <p class="cp-desc">Leitura de comunicados da direção e dos professores.</p>
            <div class="cp-cta">Ver comunicados <span>→</span></div>
          </div>
        </a>

        <a href="painel-aluno-calendario.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['aluno-calendario'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['aluno-calendario'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="160" y="32" width="80" height="66" rx="4" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <line x1="160" y1="50" x2="240" y2="50" stroke="#E4CD9A" stroke-width="2"/>
            <line x1="178" y1="26" x2="178" y2="38" stroke="#C6A15B" stroke-width="2.4"/>
            <line x1="222" y1="26" x2="222" y2="38" stroke="#C6A15B" stroke-width="2.4"/>
            <rect x="204" y="66" width="16" height="16" fill="#7A2331"/>
          </svg></div>
          <div class="cp-body">
            <h3>Calendário Escolar</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Nenhuma data importante passa despercebida."</p>
            <p class="cp-desc">Datas de provas, entrega de trabalhos e eventos.</p>
            <div class="cp-cta">Ver calendário <span>→</span></div>
          </div>
        </a>

        <a href="painel-aluno-duvidas.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['aluno-duvidas'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['aluno-duvidas'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M155 40 h90 a12 12 0 0 1 12 12 v22 a12 12 0 0 1 -12 12 h-52 l-20 18 v-18 h-18 a12 12 0 0 1 -12 -12 v-22 a12 12 0 0 1 12 -12 Z" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <text x="192" y="80" font-family="Playfair Display, serif" font-size="26" fill="#C6A15B">?</text>
          </svg></div>
          <div class="cp-body">
            <h3>Dúvidas das Matérias</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Não fique com a dúvida guardada até ao teste."</p>
            <p class="cp-desc">Perguntas sobre Matemática, Língua Portuguesa, Informática, etc.</p>
            <div class="cp-cta">Colocar dúvida <span>→</span></div>
          </div>
        </a>

        <a href="painel-aluno-grupos.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['aluno-grupos'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['aluno-grupos'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <circle cx="180" cy="55" r="14" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <path d="M160 92 q20 -24 40 0" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <circle cx="215" cy="45" r="12" fill="none" stroke="#C6A15B" stroke-width="2.2"/>
            <path d="M198 78 q17 -20 34 0" fill="none" stroke="#C6A15B" stroke-width="2.2"/>
            <circle cx="245" cy="58" r="11" fill="none" stroke="#7A2331" stroke-width="2.2"/>
            <path d="M230 88 q15 -18 30 0" fill="none" stroke="#7A2331" stroke-width="2.2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Grupos de Estudo</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Juntos, o trabalho rende mais."</p>
            <p class="cp-desc">Organização de trabalhos em equipa e partilha de resumos.</p>
            <div class="cp-cta">Ver grupos <span>→</span></div>
          </div>
        </a>

        <a href="painel-aluno-gremio.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['aluno-gremio'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['aluno-gremio'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M165 55 L200 32 L235 55" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <line x1="165" y1="55" x2="235" y2="55" stroke="#E4CD9A" stroke-width="2.2"/>
            <line x1="172" y1="55" x2="172" y2="88" stroke="#C6A15B" stroke-width="2.2"/>
            <line x1="188" y1="55" x2="188" y2="88" stroke="#C6A15B" stroke-width="2.2"/>
            <line x1="212" y1="55" x2="212" y2="88" stroke="#C6A15B" stroke-width="2.2"/>
            <line x1="228" y1="55" x2="228" y2="88" stroke="#C6A15B" stroke-width="2.2"/>
            <line x1="160" y1="88" x2="240" y2="88" stroke="#E4CD9A" stroke-width="2.4"/>
          </svg></div>
          <div class="cp-body">
            <h3>Grêmio Estudantil</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"A voz dos alunos, organizada."</p>
            <p class="cp-desc">Debates e propostas dos representantes dos alunos.</p>
            <div class="cp-cta">Participar <span>→</span></div>
          </div>
        </a>

        <a href="painel-aluno-materiais.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['aluno-materiais'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['aluno-materiais'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="150" y="34" width="70" height="52" rx="3" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <rect x="164" y="24" width="70" height="52" rx="3" fill="#17284A" stroke="#C6A15B" stroke-width="2"/>
            <line x1="176" y1="40" x2="222" y2="40" stroke="#C6A15B" stroke-width="2"/>
            <line x1="176" y1="52" x2="222" y2="52" stroke="#C6A15B" stroke-width="2" opacity="0.7"/>
            <line x1="176" y1="64" x2="208" y2="64" stroke="#C6A15B" stroke-width="2" opacity="0.5"/>
            <path d="M245 60 v20 M235 70 h20" stroke="#7A2331" stroke-width="2.4" stroke-linecap="round"/>
          </svg></div>
          <div class="cp-body">
            <h3>Materiais de Estudo</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Tudo o que precisa para estudar, num só sítio."</p>
            <p class="cp-desc">Fichas e materiais publicados pelos professores, organizados por disciplina.</p>
            <div class="cp-cta">Ver materiais <span>→</span></div>
          </div>
        </a>

        <a href="painel-aluno-sugestoes.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="160" y="45" width="90" height="45" rx="4" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <rect x="185" y="35" width="40" height="10" rx="2" fill="#C6A15B"/>
            <path d="M175 60 l30 18 l30 -18" fill="none" stroke="#7A2331" stroke-width="2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Caixa de Sugestões</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"A sua opinião também constrói a escola."</p>
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