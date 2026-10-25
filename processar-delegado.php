<?php
/**
 * CEPS · Fórum Escolar — processa Delegado de Turma
 * Só quem é Diretor daquela turma pode definir o delegado.
 */

require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'definir') {
    $turma   = trim($_POST['turma'] ?? '');
    $alunoId = (int)($_POST['aluno_id'] ?? 0);

    // Confirma que este professor é mesmo o Diretor desta turma
    $stmt = $pdo->prepare('SELECT id FROM diretores_turma WHERE turma = ? AND professor_id = ?');
    $stmt->execute([$turma, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        header('Location: painel-professor-delegado.php?erro=nao_diretor');
        exit;
    }

    // Confirma que o aluno pertence mesmo a essa turma
    $stmt = $pdo->prepare('SELECT utilizador_id FROM alunos_dados WHERE utilizador_id = ? AND turma = ?');
    $stmt->execute([$alunoId, $turma]);
    if (!$stmt->fetch()) {
        header('Location: painel-professor-delegado.php?erro=nao_diretor');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO delegados_turma (turma, aluno_id, definido_por) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE aluno_id = VALUES(aluno_id), definido_por = VALUES(definido_por)'
    );
    $stmt->execute([$turma, $alunoId, $_SESSION['user_id']]);

    header('Location: painel-professor-delegado.php?msg=definido');
    exit;
}

header('Location: painel-professor-delegado.php');
exit;
