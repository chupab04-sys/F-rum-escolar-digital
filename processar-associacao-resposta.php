<?php
/**
 * CEPS · Fórum Escolar — processa respostas na Associação de Pais
 * Encarregados e Direção podem responder.
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['encarregado', 'direcao'], true)) {
    header('Location: login.html?erro=sessao');
    exit;
}

$voltarPara = $_SESSION['perfil'] === 'direcao' ? 'painel-direcao-associacao.php' : 'painel-encarregado-associacao.php';

$temaId = (int)($_POST['tema_id'] ?? 0);
$texto  = trim($_POST['texto'] ?? '');

if ($temaId <= 0 || $texto === '') {
    header("Location: $voltarPara");
    exit;
}

$stmt = $pdo->prepare('INSERT INTO associacao_respostas (tema_id, autor_id, texto) VALUES (?, ?, ?)');
$stmt->execute([$temaId, $_SESSION['user_id'], $texto]);

header("Location: $voltarPara?msg=respondido");
exit;
