<?php
/**
 * CEPS · Fórum Escolar — processa uma nova Dúvida (Aluno)
 */

require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$alunoId          = $_SESSION['user_id'];
$professorTurmaId = (int)($_POST['professor_turma_id'] ?? 0);
$pergunta         = trim($_POST['pergunta'] ?? '');

if ($professorTurmaId <= 0 || $pergunta === '') {
    header('Location: painel-aluno-duvidas.php?erro=campos');
    exit;
}

// Confirma que a disciplina escolhida pertence mesmo à turma deste aluno
$stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
$stmt->execute([$alunoId]);
$turma = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT id FROM professor_turmas WHERE id = ? AND turma = ?');
$stmt->execute([$professorTurmaId, $turma]);
if (!$stmt->fetch()) {
    header('Location: painel-aluno-duvidas.php?erro=disciplina_invalida');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO duvidas (aluno_id, professor_turma_id, pergunta) VALUES (?, ?, ?)'
);
$stmt->execute([$alunoId, $professorTurmaId, $pergunta]);

header('Location: painel-aluno-duvidas.php?msg=enviada');
exit;
