<?php
/**
 * CEPS · Fórum Escolar — processa Diretor de Turma
 */

require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'definir') {
    $turma       = trim($_POST['turma'] ?? '');
    $professorId = (int)($_POST['professor_id'] ?? 0);

    if ($turma === '' || $professorId <= 0) {
        header('Location: painel-direcao-turmas.php?erro=campos');
        exit;
    }

    // Confirma que este professor está mesmo atribuído a esta turma
    $stmt = $pdo->prepare('SELECT id FROM professor_turmas WHERE professor_id = ? AND turma = ?');
    $stmt->execute([$professorId, $turma]);
    if (!$stmt->fetch()) {
        header('Location: painel-direcao-turmas.php?erro=campos');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO diretores_turma (turma, professor_id) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE professor_id = VALUES(professor_id)'
    );
    $stmt->execute([$turma, $professorId]);

    header('Location: painel-direcao-turmas.php?msg=criado');
    exit;
}

header('Location: painel-direcao-turmas.php');
exit;
