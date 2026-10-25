<?php
require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';
require 'registar-visita.php'; registarVisita($pdo, $_SESSION['user_id'], 'aluno-grupos');

$grupoId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT g.id, g.titulo, g.descricao, g.turma FROM grupos_estudo g WHERE g.id = ?');
$stmt->execute([$grupoId]);
$grupo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grupo) {
    header('Location: painel-aluno-grupos.php');
    exit;
}

// Confirma que este aluno é mesmo membro do grupo
$stmt = $pdo->prepare('SELECT id FROM grupos_membros WHERE grupo_id = ? AND aluno_id = ?');
$stmt->execute([$grupoId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Location: painel-aluno-grupos.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT u.nome, gm.entrou_em
     FROM grupos_membros gm
     JOIN utilizadores u ON u.id = gm.aluno_id
     WHERE gm.grupo_id = ?
     ORDER BY gm.entrou_em ASC"
);
$stmt->execute([$grupoId]);
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT r.id, r.texto, r.nome_ficheiro, r.criado_em, u.nome AS autor_nome
     FROM grupos_resumos r
     JOIN utilizadores u ON u.id = r.autor_id
     WHERE r.grupo_id = ?
     ORDER BY r.criado_em DESC"
);
$stmt->execute([$grupoId]);
$resumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$extensoesAudio = ['webm', 'ogg', 'mp3', 'wav', 'm4a'];

$mensagem = $_GET['msg'] ?? '';
$erro     = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($grupo['titulo']) ?> | CEPS</title>
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

    .cg-layout{ display:grid; grid-template-columns:2fr 1fr; gap:24px; align-items:start; }
    @media (max-width:820px){ .cg-layout{ grid-template-columns:1fr; } }

    .cg-form{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:24px; margin-bottom:24px; }
    .cg-form textarea{
      width:100%; min-height:80px; padding:12px 14px; border:1px solid var(--line); border-radius:10px;
      font-family:var(--sans); font-size:.9rem; color:var(--ink); background:var(--cream); resize:vertical; margin-bottom:14px;
    }
    .cg-linha-audio{ display:flex; align-items:center; gap:12px; margin-bottom:14px; flex-wrap:wrap; }
    .cg-btn-gravar{ background:var(--terracotta, #a5423b); color:#fff; border:none; border-radius:999px; padding:9px 18px; font-size:.82rem; font-weight:600; cursor:pointer; }
    .cg-btn-gravar.a-gravar{ background:#7A2331; animation:cg-pulsar 1s infinite; }
    @keyframes cg-pulsar{ 0%{opacity:1;} 50%{opacity:.6;} 100%{opacity:1;} }
    .cg-audio-preview{ display:none; align-items:center; gap:10px; }
    .cg-audio-preview.visivel{ display:flex; }
    .cg-audio-preview button{ background:none; border:none; color:var(--muted); cursor:pointer; font-size:.8rem; text-decoration:underline; }

    .cg-lista{ display:flex; flex-direction:column; gap:14px; }
    .cg-item{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:18px 22px; }
    .cg-item p{ font-size:.88rem; color:var(--ink); margin-bottom:8px; }
    .cg-meta{ font-size:.72rem; color:var(--muted); margin-bottom:8px; }
    .cg-ficheiro{ font-size:.82rem; color:var(--navy); font-weight:600; text-decoration:none; }
    .cg-vazio{ text-align:center; color:var(--muted); padding:30px 0; }

    .cg-lateral{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:22px; }
    .cg-lateral h3{ font-family:var(--serif); color:var(--navy); font-size:1rem; margin-bottom:12px; }
    .cg-membro{ font-size:.85rem; color:var(--ink); padding:6px 0; border-bottom:1px solid var(--line); }
    .cg-membro:last-child{ border-bottom:none; }
  </style>
</head>
<body>

  <div class="panel-shell">
    <div class="panel-topbar">
      <div class="container panel-topbar-inner">
        <h1><?= htmlspecialchars($grupo['titulo']) ?></h1>
        <div class="panel-user">
          <span class="role-chip">Aluno</span>
          <span><strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
          <a href="logout.php" class="btn btn-pill btn-ghost">Sair</a>
        </div>
      </div>
    </div>

    <div class="container panel-body" style="text-align:left; padding-top:40px;">
      <div class="cg-topo">
        <div>
          <span class="eyebrow">Espaço do Estudante</span>
          <h2 style="text-align:left; margin:0;"><?= htmlspecialchars($grupo['descricao'] ?? 'Partilha de resumos do grupo') ?></h2>
        </div>
        <a href="painel-aluno-grupos.php" class="cg-voltar">← Voltar aos grupos</a>
      </div>

      <?php if ($mensagem === 'criado'): ?>
        <div class="cg-aviso">Grupo criado! Já pode partilhar o primeiro resumo.</div>
      <?php elseif ($mensagem === 'partilhado'): ?>
        <div class="cg-aviso">Resumo partilhado com o grupo.</div>
      <?php endif; ?>
      <?php if ($erro === 'vazio'): ?>
        <div class="cg-erro">Escreva algo ou grave um áudio antes de partilhar.</div>
      <?php elseif ($erro === 'tipo_invalido'): ?>
        <div class="cg-erro">Tipo de ficheiro não permitido.</div>
      <?php elseif ($erro === 'tamanho'): ?>
        <div class="cg-erro">O ficheiro é demasiado grande (máximo 10 MB).</div>
      <?php endif; ?>

      <div class="cg-layout">
        <div>
          <form class="cg-form" action="processar-resumo.php" method="POST" enctype="multipart/form-data" id="cgFormResumo">
            <input type="hidden" name="grupo_id" value="<?= (int)$grupoId ?>">
            <textarea name="texto" placeholder="Escreva um resumo, dúvida ou nota para o grupo..."></textarea>

            <div class="cg-linha-audio">
              <button type="button" class="cg-btn-gravar" id="cgBtnGravar">🎙️ Gravar Áudio</button>
              <div class="cg-audio-preview" id="cgAudioPreview">
                <audio id="cgAudioPlayer" controls style="height:32px;"></audio>
                <button type="button" onclick="cgRemoverAudio()">Remover</button>
              </div>
              <input type="file" name="ficheiro" id="cgInputFicheiro" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webm,.ogg,.mp3,.wav,.m4a" style="font-size:.8rem;">
            </div>

            <button type="submit" class="btn btn-primary">Partilhar com o Grupo</button>
          </form>

          <h3 style="font-family:var(--serif); color:var(--navy); margin-bottom:16px;">Resumos partilhados</h3>
          <?php if (empty($resumos)): ?>
            <p class="cg-vazio">Ainda não há nenhum resumo neste grupo.</p>
          <?php else: ?>
            <div class="cg-lista">
              <?php foreach ($resumos as $r): ?>
                <div class="cg-item">
                  <div class="cg-meta"><b><?= htmlspecialchars($r['autor_nome']) ?></b> · <?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></div>
                  <?php if (!empty($r['texto'])): ?><p><?= nl2br(htmlspecialchars($r['texto'])) ?></p><?php endif; ?>
                  <?php if (!empty($r['nome_ficheiro'])):
                    $ext = strtolower(pathinfo($r['nome_ficheiro'], PATHINFO_EXTENSION));
                  ?>
                    <?php if (in_array($ext, $extensoesAudio, true)): ?>
                      <audio controls style="height:36px; width:100%;">
                        <source src="download-resumo.php?id=<?= (int)$r['id'] ?>">
                      </audio>
                    <?php else: ?>
                      <a href="download-resumo.php?id=<?= (int)$r['id'] ?>" class="cg-ficheiro">📎 <?= htmlspecialchars($r['nome_ficheiro']) ?></a>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="cg-lateral">
          <h3>Membros (<?= count($membros) ?>)</h3>
          <?php foreach ($membros as $m): ?>
            <div class="cg-membro"><?= htmlspecialchars($m['nome']) ?></div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>

  <script>
    let cgMediaRecorder;
    let cgChunks = [];
    const cgBtn = document.getElementById('cgBtnGravar');
    const cgPreview = document.getElementById('cgAudioPreview');
    const cgPlayer = document.getElementById('cgAudioPlayer');
    const cgInputFicheiro = document.getElementById('cgInputFicheiro');
    let cgAGravar = false;

    cgBtn.addEventListener('click', async function(){
      if (!cgAGravar) {
        try {
          const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
          cgChunks = [];
          cgMediaRecorder = new MediaRecorder(stream);
          cgMediaRecorder.ondataavailable = e => cgChunks.push(e.data);
          cgMediaRecorder.onstop = function(){
            const blob = new Blob(cgChunks, { type: 'audio/webm' });
            const ficheiroAudio = new File([blob], 'audio_' + Date.now() + '.webm', { type: 'audio/webm' });
            const dt = new DataTransfer();
            dt.items.add(ficheiroAudio);
            cgInputFicheiro.files = dt.files;
            cgPlayer.src = URL.createObjectURL(blob);
            cgPreview.classList.add('visivel');
            stream.getTracks().forEach(t => t.stop());
          };
          cgMediaRecorder.start();
          cgAGravar = true;
          cgBtn.textContent = '⏹️ Parar Gravação';
          cgBtn.classList.add('a-gravar');
        } catch (err) {
          alert('Não foi possível aceder ao microfone. Verifique as permissões do browser.');
        }
      } else {
        cgMediaRecorder.stop();
        cgAGravar = false;
        cgBtn.textContent = '🎙️ Gravar Áudio';
        cgBtn.classList.remove('a-gravar');
      }
    });

    function cgRemoverAudio(){
      cgInputFicheiro.value = '';
      cgPlayer.src = '';
      cgPreview.classList.remove('visivel');
    }
  </script>
</body>
</html>
