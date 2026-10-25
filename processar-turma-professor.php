<?php
/**
 * CEPS · Fórum Escolar — processa a atribuição de turmas/disciplinas
 * a professores. Só a Direção pode fazer isto.
 */

require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'criar') {
    $professor_id = (int)($_POST['professor_id'] ?? 0);
    $turma        = trim($_POST['turma'] ?? '');
    $disciplina   = trim($_POST['disciplina'] ?? '');

    if ($professor_id <= 0 || $turma === '' || $disciplina === '') {
        header('Location: painel-direcao-turmas.php?erro=campos');
        exit;
    }

    // Confirma que o id indicado é mesmo um professor
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE id = ? AND perfil = 'professor'");
    $stmt->execute([$professor_id]);
    if (!$stmt->fetch()) {
        header('Location: painel-direcao-turmas.php?erro=campos');
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO professor_turmas (professor_id, turma, disciplina) VALUES (?, ?, ?)'
        );
        $stmt->execute([$professor_id, $turma, $disciplina]);
        header('Location: painel-direcao-turmas.php?msg=criado');
        exit;
    } catch (PDOException $e) {
        // Viola a chave única -> já existe esta combinação
        header('Location: painel-direcao-turmas.php?erro=duplicado');
        exit;
    }
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM professor_turmas WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: painel-direcao-turmas.php?msg=eliminado');
    exit;
}

header('Location: painel-direcao-turmas.php');
exit;
