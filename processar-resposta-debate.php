<?php
/**
 * CEPS · Fórum Escolar — processa respostas na Sala de Reuniões Virtual
 * Direção e Professores podem responder.
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['direcao', 'professor'], true)) {
    header('Location: login.html?erro=sessao');
    exit;
}

$voltarPara = $_SESSION['perfil'] === 'direcao' ? 'painel-direcao-reunioes.php' : 'painel-professor-reunioes.php';

$debateId = (int)($_POST['debate_id'] ?? 0);
$texto    = trim($_POST['texto'] ?? '');

if ($debateId <= 0 || $texto === '') {
    header("Location: $voltarPara?erro=resposta_vazia");
    exit;
}

$stmt = $pdo->prepare('INSERT INTO debates_respostas (debate_id, autor_id, texto) VALUES (?, ?, ?)');
$stmt->execute([$debateId, $_SESSION['user_id'], $texto]);

header("Location: $voltarPara?msg=respondido");
exit;
