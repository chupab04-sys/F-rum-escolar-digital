<?php
/**
 * CEPS · Fórum Escolar — aplica o tema (escuro/contraste/texto)
 *
 * Como usar: em CADA página (.php), dentro do <head>, logo a seguir
 * ao <link rel="stylesheet" href="css/style.css">, adicione UMA linha:
 *
 *   <?php include 'tema-aplicar.php'; ?>
 *
 * Nada mais é preciso — este ficheiro já traz o CSS e o script.
 * (Em páginas .html puras, como login.html, não é possível usar
 * include — nesse caso mantenha o bloco de <link>+<script> direto.)
 */
?>
<link rel="stylesheet" href="css/tema.css">
<script>
  (function(){
    var tema = localStorage.getItem('ceps_tema');
    var contraste = localStorage.getItem('ceps_contraste');
    var escala = localStorage.getItem('ceps_escala');
    if (tema === 'escuro') document.documentElement.setAttribute('data-tema', 'escuro');
    if (contraste === 'alto') document.documentElement.setAttribute('data-contraste', 'alto');
    if (escala) document.documentElement.style.setProperty('--escala-texto', escala);
  })();
</script>
