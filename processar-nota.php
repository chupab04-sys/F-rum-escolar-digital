<?php
/**
 * CEPS · Fórum Escolar — processa Notas (lançar / eliminar)
 */

require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'lancar') {
    $professorTurmaId = (int)($_POST['professor_turma_id'] ?? 0);
    $alunoId          = (int)($_POST['aluno_id'] ?? 0);
    $periodo          = trim($_POST['periodo'] ?? '');
    $tipoAvaliacao    = trim($_POST['tipo_avaliacao'] ?? '');
    $nota             = trim($_POST['nota'] ?? '');

    if ($professorTurmaId <= 0 || $alunoId <= 0 || $periodo === '' || $tipoAvaliacao === '' || $nota === '') {
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

    // Confirma que o aluno pertence mesmo a essa turma
    $stmt = $pdo->prepare('SELECT utilizador_id FROM alunos_dados WHERE utilizador_id = ? AND turma = ?');
    $stmt->execute([$alunoId, $pt['turma']]);
    if (!$stmt->fetch()) {
        header('Location: painel-professor-notas.php?erro=aluno_invalido');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO notas (professor_turma_id, aluno_id, tipo_avaliacao, periodo, nota)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$professorTurmaId, $alunoId, $tipoAvaliacao, $periodo, $nota]);

    header('Location: painel-professor-notas.php?msg=nota_lancada');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);

    // Só permite eliminar uma nota que pertença a uma turma/disciplina deste professor
    $stmt = $pdo->prepare(
        "DELETE n FROM notas n
         JOIN professor_turmas pt ON pt.id = n.professor_turma_id
         WHERE n.id = ? AND pt.professor_id = ?"
    );
    $stmt->execute([$id, $_SESSION['user_id']]);

    header('Location: painel-professor-notas.php?msg=nota_eliminada');
    exit;
}

header('Location: painel-professor-notas.php');
exit;
