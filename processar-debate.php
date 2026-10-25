<?php
/**
 * CEPS · Fórum Escolar — processa temas da Sala de Reuniões Virtual
 * Só a Direção pode criar ou eliminar temas.
 */

require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'criar') {
    $titulo   = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($titulo === '' || $mensagem === '') {
        header('Location: painel-direcao-reunioes.php?erro=campos');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO debates (titulo, mensagem, autor_id) VALUES (?, ?, ?)');
    $stmt->execute([$titulo, $mensagem, $_SESSION['user_id']]);

    header('Location: painel-direcao-reunioes.php?msg=criado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM debates WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    header('Location: painel-direcao-reunioes.php?msg=eliminado');
    exit;
}

header('Location: painel-direcao-reunioes.php');
exit;
