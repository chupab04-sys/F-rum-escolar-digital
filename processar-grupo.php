<?php
/**
 * CEPS · Fórum Escolar — processa Grupos de Estudo (criar / aderir)
 */

require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$acao = $_POST['acao'] ?? '';

$stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$turma = $stmt->fetchColumn();

if ($acao === 'criar') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($titulo === '') {
        header('Location: painel-aluno-grupos.php?erro=campos');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO grupos_estudo (turma, titulo, descricao, criador_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$turma, $titulo, $descricao ?: null, $_SESSION['user_id']]);
    $grupoId = $pdo->lastInsertId();

    // O criador entra automaticamente como membro
    $stmt = $pdo->prepare('INSERT INTO grupos_membros (grupo_id, aluno_id) VALUES (?, ?)');
    $stmt->execute([$grupoId, $_SESSION['user_id']]);

    header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId . '&msg=criado');
    exit;
}

if ($acao === 'aderir') {
    $grupoId = (int)($_POST['grupo_id'] ?? 0);

    // Confirma que o grupo pertence mesmo à turma deste aluno
    $stmt = $pdo->prepare('SELECT id FROM grupos_estudo WHERE id = ? AND turma = ?');
    $stmt->execute([$grupoId, $turma]);
    if (!$stmt->fetch()) {
        header('Location: painel-aluno-grupos.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO grupos_membros (grupo_id, aluno_id) VALUES (?, ?)');
        $stmt->execute([$grupoId, $_SESSION['user_id']]);
    } catch (PDOException $e) {
        // já era membro — sem problema
    }

    header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId);
    exit;
}

header('Location: painel-aluno-grupos.php');
exit;
