<?php
/**
 * CEPS · Fórum Escolar — processa temas do Grémio Estudantil
 * Só um aluno que seja delegado de alguma turma pode criar temas.
 */

require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'criar') {
    // Confirma que este aluno é mesmo delegado de alguma turma agora
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM delegados_turma WHERE aluno_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    if ((int)$stmt->fetchColumn() === 0) {
        header('Location: painel-aluno-gremio.php?erro=nao_delegado');
        exit;
    }

    $titulo   = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($titulo === '' || $mensagem === '') {
        header('Location: painel-aluno-gremio.php?erro=campos');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO gremio_temas (titulo, mensagem, autor_id) VALUES (?, ?, ?)');
    $stmt->execute([$titulo, $mensagem, $_SESSION['user_id']]);

    header('Location: painel-aluno-gremio.php?msg=criado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM gremio_temas WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    header('Location: painel-aluno-gremio.php');
    exit;
}

header('Location: painel-aluno-gremio.php');
exit;
