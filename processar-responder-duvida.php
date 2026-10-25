<?php
/**
 * CEPS · Fórum Escolar — processa a resposta do Professor a uma Dúvida
 */

require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$duvidaId = (int)($_POST['duvida_id'] ?? 0);
$resposta = trim($_POST['resposta'] ?? '');

if ($duvidaId <= 0 || $resposta === '') {
    header('Location: painel-professor-duvidas.php');
    exit;
}

// Só permite responder a uma dúvida que seja mesmo de uma disciplina/turma
// atribuída a este professor
$stmt = $pdo->prepare(
    "SELECT d.id
     FROM duvidas d
     JOIN professor_turmas pt ON pt.id = d.professor_turma_id
     WHERE d.id = ? AND pt.professor_id = ?"
);
$stmt->execute([$duvidaId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Location: painel-professor-duvidas.php');
    exit;
}

$stmt = $pdo->prepare('UPDATE duvidas SET resposta = ?, respondido_em = NOW() WHERE id = ?');
$stmt->execute([$resposta, $duvidaId]);

header('Location: painel-professor-duvidas.php?msg=respondida');
exit;
