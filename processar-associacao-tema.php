<?php
/**
 * CEPS · Fórum Escolar — processa temas da Associação de Pais
 * Só Encarregados criam temas. Cada um só elimina o seu próprio.
 */

require 'auth_check.php';
protegerPainel('encarregado');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'criar') {
    $titulo   = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($titulo === '' || $mensagem === '') {
        header('Location: painel-encarregado-associacao.php?erro=campos');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO associacao_temas (titulo, mensagem, autor_id) VALUES (?, ?, ?)');
    $stmt->execute([$titulo, $mensagem, $_SESSION['user_id']]);

    header('Location: painel-encarregado-associacao.php?msg=criado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM associacao_temas WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    header('Location: painel-encarregado-associacao.php?msg=eliminado');
    exit;
}

header('Location: painel-encarregado-associacao.php');
exit;
