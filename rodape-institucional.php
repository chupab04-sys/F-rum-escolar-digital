<?php
/**
 * CEPS · Fórum Escolar — rodapé institucional dos painéis
 *
 * Como usar: em cada painel-*.php principal, ANTES de incluir isto,
 * garanta que $pdo e $_SESSION['email'] já existem (já é o caso,
 * já que este bloco vem depois do require 'config.php').
 *
 * Logo antes do </div> que fecha .panel-body, adicione:
 *
 *   <?php include 'rodape-institucional.php'; ?>
 */

// Login anterior a este (não o de agora), para mostrar "Última entrada"
$ultimaEntrada = null;
if (isset($pdo, $_SESSION['email'])) {
    $stmt = $pdo->prepare(
        'SELECT criado_em FROM tentativas_login
         WHERE email = ? AND sucesso = 1
         ORDER BY criado_em DESC
         LIMIT 1 OFFSET 1'
    );
    $stmt->execute([$_SESSION['email']]);
    $ultimaEntrada = $stmt->fetchColumn();
}
?>
<div class="iv-resumo">
  <div class="iv-resumo-item" style="flex:none; min-width:220px;">
    <div class="iv-resumo-num" style="font-size:1.1rem;">
      <?= $ultimaEntrada ? date('d/m/Y \à\s H:i', strtotime($ultimaEntrada)) : 'Esta é a sua primeira entrada' ?>
    </div>
    <div class="iv-resumo-lbl">Última entrada</div>
  </div>
</div>

<div class="iv-citacao">
  <p>"Cada aluno que aprende aqui, leva a escola consigo para o resto da vida."</p>
</div>

<footer class="iv-rodape">
  <div class="iv-rodape-nome">CEPS <span>·</span> Complexo Escolar Privado Sanjaylise</div>
  <div class="iv-rodape-sub">Luanda, Angola — desde 1999</div>
</footer>

<style>
  .iv-resumo{
    display:flex; gap:16px; flex-wrap:wrap;
    margin-top:48px; padding-top:32px; border-top:1px solid var(--line, #e0dacb);
  }
  .iv-resumo-item{
    flex:1; min-width:150px; background:#fff; border:1px solid var(--line, #e0dacb);
    border-radius:12px; padding:18px 22px; text-align:center;
  }
  .iv-resumo-num{
    font-family:'Playfair Display',serif; font-weight:700; font-size:1.8rem; color:#0E1F3B;
    line-height:1;
  }
  .iv-resumo-lbl{
    font-size:.72rem; color:#8a90a3; text-transform:uppercase; letter-spacing:.05em; margin-top:6px;
  }

  .iv-citacao{
    margin-top:28px; padding:36px 24px; text-align:center;
    background: linear-gradient(160deg, #0E1F3B 0%, #17284A 100%);
    border-radius:14px;
  }
  .iv-citacao p{
    font-family:'Playfair Display',serif; font-style:italic; font-weight:600;
    color:#E4CD9A; font-size:1.05rem; max-width:52ch; margin:0 auto; line-height:1.5;
  }

  .iv-rodape{
    margin-top:28px; padding:26px 0 8px; text-align:center;
  }
  .iv-rodape-nome{
    font-family:'Playfair Display',serif; font-weight:700; font-size:.95rem; color:#0E1F3B;
  }
  .iv-rodape-nome span{ color:#C6A15B; margin:0 4px; }
  .iv-rodape-sub{
    font-size:.72rem; color:#8a90a3; letter-spacing:.04em; margin-top:4px;
  }

  :root[data-tema="escuro"] .iv-resumo-item{ background:#1a2030 !important; border-color:rgba(255,255,255,.12) !important; }
  :root[data-tema="escuro"] .iv-resumo-num{ color:#e8ecf3 !important; }
  :root[data-tema="escuro"] .iv-rodape-nome{ color:#e8ecf3 !important; }
</style>