<?php
/**
 * CEPS · Fórum Escolar — processa respostas no Grémio Estudantil
 * Qualquer aluno pode responder a um tema.
 */

require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$temaId = (int)($_POST['tema_id'] ?? 0);
$texto  = trim($_POST['texto'] ?? '');

if ($temaId <= 0 || $texto === '') {
    header('Location: painel-aluno-gremio.php');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO gremio_respostas (tema_id, autor_id, texto) VALUES (?, ?, ?)');
$stmt->execute([$temaId, $_SESSION['user_id'], $texto]);

header('Location: painel-aluno-gremio.php?msg=respondido');
exit;
