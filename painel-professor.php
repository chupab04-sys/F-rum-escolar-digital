<?php
require 'auth_check.php';
protegerPainel('professor');
require 'config.php';
require 'contadores.php';
$novidades = obterContadores($pdo, $_SESSION['user_id'], 'professor');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel do Professor | CEPS</title>
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
        <h1>Painel do Professor</h1>
        <div class="panel-user">
          <span class="role-chip">Professor</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body">
      <span class="eyebrow">Espaço Docente</span>
      <h2>Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>!</h2>
      <p>Estrutura planeada para o Painel do Professor, focada em produtividade pedagógica e gestão de turmas.</p>

      <div class="cp-grid">

        <a href="painel-professor-partilha.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['prof-partilha'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['prof-partilha'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="0" y="94" width="400" height="3" fill="#C6A15B" opacity="0.55"/>
            <g>
              <rect x="30" y="34" width="14" height="60" fill="#E4CD9A"/>
              <rect x="46" y="24" width="12" height="70" fill="#7A2331"/>
              <rect x="60" y="40" width="14" height="54" fill="#C6A15B"/>
              <rect x="180" y="20" width="13" height="74" fill="#B9C2D6" opacity="0.5"/>
              <rect x="195" y="34" width="12" height="60" fill="#E4CD9A" opacity="0.85"/>
              <rect x="330" y="30" width="13" height="64" fill="#7A2331" opacity="0.9"/>
              <rect x="345" y="44" width="12" height="50" fill="#C6A15B"/>
            </g>
            <path d="M150 94 L150 62 Q166 54 182 62 L182 94 Z" fill="#17284A" stroke="#C6A15B" stroke-width="1.2"/>
            <path d="M182 94 L182 62 Q198 54 214 62 L214 94 Z" fill="#17284A" stroke="#C6A15B" stroke-width="1.2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Partilha Pedagógica</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"O seu melhor teste pode ser a aula de outro professor amanhã."</p>
            <p class="cp-desc">Troca de materiais de apoio, testes e metodologias.</p>
            <div class="cp-cta">Aceder ao espaço <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-avisos.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['prof-avisos'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['prof-avisos'] ?></span><?php endif; ?>
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
            <h3>Avisos da Coordenação</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Uma linha da coordenação vale mais do que dez e-mails perdidos."</p>
            <p class="cp-desc">Recados exclusivos da direção para o corpo docente.</p>
            <div class="cp-cta">Ver avisos <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-fichas.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M150 40 h70 a10 10 0 0 1 10 10 v22 a10 10 0 0 1 -10 10 h-40 l-16 16 v-16 h-14 a10 10 0 0 1 -10 -10 v-22 a10 10 0 0 1 10 -10 Z" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <path d="M200 84 L200 62 M190 72 L200 60 L210 72" stroke="#C6A15B" stroke-width="2.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="150" y="30" width="6" height="24" fill="#7A2331" opacity="0.7"/>
            <rect x="250" y="24" width="6" height="20" fill="#7A2331" opacity="0.5"/>
          </svg></div>
          <div class="cp-body">
            <h3>Upload de Fichas</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Publique hoje o que os seus alunos vão precisar amanhã."</p>
            <p class="cp-desc">Publicação de materiais e fichas de exercícios por turma.</p>
            <div class="cp-cta">Publicar ficha <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-duvidas.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['prof-duvidas'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['prof-duvidas'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <path d="M150 40 h70 a10 10 0 0 1 10 10 v22 a10 10 0 0 1 -10 10 h-40 l-16 16 v-16 h-14 a10 10 0 0 1 -10 -10 v-22 a10 10 0 0 1 10 -10 Z" fill="#C6A15B" opacity="0.85"/>
            <path d="M225 60 h60 a9 9 0 0 1 9 9 v18 a9 9 0 0 1 -9 9 h-14 l0 14 l-16 -14 h-30 a9 9 0 0 1 -9 -9 v-18 a9 9 0 0 1 9 -9 Z" fill="#17284A" stroke="#E4CD9A" stroke-width="1.6"/>
            <text x="248" y="86" font-family="Playfair Display, serif" font-size="20" fill="#E4CD9A">?</text>
          </svg></div>
          <div class="cp-body">
            <h3>Resposta a Dúvidas</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Cada dúvida respondida é uma aula que não precisa repetir."</p>
            <p class="cp-desc">Painel centralizado com as perguntas deixadas pelos alunos.</p>
            <div class="cp-cta">Responder agora <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-contacto.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['prof-contacto'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['prof-contacto'] ?></span><?php endif; ?>
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <circle cx="175" cy="55" r="16" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <path d="M150 96 q25 -26 50 0" fill="none" stroke="#E4CD9A" stroke-width="2.2"/>
            <circle cx="230" cy="60" r="13" fill="none" stroke="#C6A15B" stroke-width="2.2"/>
            <path d="M210 96 q20 -20 40 0" fill="none" stroke="#C6A15B" stroke-width="2.2"/>
            <path d="M196 66 h18" stroke="#7A2331" stroke-width="2" stroke-dasharray="2 3"/>
          </svg></div>
          <div class="cp-body">
            <h3>Contacto com os Encarregados</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"A confiança da família começa numa boa conversa."</p>
            <p class="cp-desc">Mensagens e agendamento de reuniões sobre o aproveitamento.</p>
            <div class="cp-cta">Iniciar conversa <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-reunioes.php" class="cp-card" style="text-decoration:none;">
          <?php if (($novidades['prof-reunioes'] ?? 0) > 0): ?><span class="cp-badge"><?= $novidades['prof-reunioes'] ?></span><?php endif; ?>
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
            <p class="cp-tagline">"A sua opinião também constrói a escola."</p>
            <p class="cp-desc">Participe nos debates internos lançados pela Direção.</p>
            <div class="cp-cta">Entrar na sala <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-sugestoes.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="160" y="45" width="90" height="45" rx="4" fill="none" stroke="#E4CD9A" stroke-width="2"/>
            <rect x="185" y="35" width="40" height="10" rx="2" fill="#C6A15B"/>
            <path d="M175 60 l30 18 l30 -18" fill="none" stroke="#7A2331" stroke-width="2"/>
          </svg></div>
          <div class="cp-body">
            <h3>Caixa de Sugestões</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Uma ideia anónima também pode mudar a escola."</p>
            <p class="cp-desc">Envie sugestões ou preocupações à Direção, de forma anónima.</p>
            <div class="cp-cta">Enviar sugestão <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-notas.php" class="cp-card" style="text-decoration:none;">
          <div class="cp-visual"><svg viewBox="0 0 400 120" preserveAspectRatio="xMidYMid slice">
            <rect width="400" height="120" fill="#17284A"/>
            <rect x="170" y="72" width="14" height="22" fill="#B9C2D6" opacity="0.5"/>
            <rect x="190" y="62" width="14" height="32" fill="#E4CD9A"/>
            <rect x="210" y="68" width="14" height="26" fill="#7A2331" opacity="0.85"/>
            <circle cx="248" cy="55" r="15" fill="none" stroke="#C6A15B" stroke-width="2.2"/>
            <path d="M240 55 l6 6 l11 -13" stroke="#C6A15B" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          </svg></div>
          <div class="cp-body">
            <h3>Notas e Assiduidade</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Cada nota lançada é um passo documentado no percurso do aluno."</p>
            <p class="cp-desc">Lance notas e registe a assiduidade da sua turma.</p>
            <div class="cp-cta">Lançar notas <span>→</span></div>
          </div>
        </a>

        <a href="painel-professor-delegado.php" class="cp-card" style="text-decoration:none;">
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
            <h3>Delegado de Turma</h3>
            <div class="cp-rule"></div>
            <p class="cp-tagline">"Um representante, escolhido com confiança."</p>
            <p class="cp-desc">Escolha o delegado da sua turma para o Grémio Estudantil.</p>
            <div class="cp-cta">Escolher delegado <span>→</span></div>
          </div>
        </a>

      </div>

      <?php include 'rodape-institucional.php'; ?>

    </div>
  </div>

</body>
</html>