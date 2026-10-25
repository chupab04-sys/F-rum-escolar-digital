<?php
/**
 * CEPS · Fórum Escolar — processa Assiduidade (registo em massa por aula)
 */

require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao !== 'registar') {
    header('Location: painel-professor-notas.php');
    exit;
}

$professorTurmaId = (int)($_POST['professor_turma_id'] ?? 0);
$dataAula         = trim($_POST['data_aula'] ?? '');
$alunoIds         = $_POST['aluno_ids'] ?? [];
$presentes        = $_POST['presente'] ?? []; // array [aluno_id => 'on'] só dos marcados

if ($professorTurmaId <= 0 || $dataAula === '' || empty($alunoIds)) {
    header('Location: painel-professor-notas.php?erro=campos');
    exit;
}

// Confirma que esta turma/disciplina pertence a este professor
$stmt = $pdo->prepare('SELECT turma FROM professor_turmas WHERE id = ? AND professor_id = ?');
$stmt->execute([$professorTurmaId, $_SESSION['user_id']]);
$pt = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pt) {
    header('Location: painel-professor-notas.php?erro=atribuicao_invalida');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO assiduidade (professor_turma_id, aluno_id, data_aula, presente)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE presente = VALUES(presente)'
);

foreach ($alunoIds as $alunoId) {
    $alunoId = (int)$alunoId;

    // Confirma que este aluno pertence mesmo à turma (segurança)
    $verifica = $pdo->prepare('SELECT utilizador_id FROM alunos_dados WHERE utilizador_id = ? AND turma = ?');
    $verifica->execute([$alunoId, $pt['turma']]);
    if (!$verifica->fetch()) {
        continue; // ignora qualquer id que não pertença à turma
    }

    $presente = isset($presentes[$alunoId]) ? 1 : 0;
    $stmt->execute([$professorTurmaId, $alunoId, $dataAula, $presente]);
}

header('Location: painel-professor-notas.php?msg=assiduidade_guardada');
exit;
