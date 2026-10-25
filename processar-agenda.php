<?php
/**
 * CEPS · Fórum Escolar — processa a Agenda Oficial (criar / eliminar)
 */

require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'criar') {
    $titulo      = trim($_POST['titulo'] ?? '');
    $tipo        = trim($_POST['tipo'] ?? '');
    $dataEvento  = trim($_POST['data_evento'] ?? '');
    $turmaAlvo   = trim($_POST['turma_alvo'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');

    $tiposValidos = ['Feriado', 'Reunião', 'Conselho de Notas', 'Prova', 'Evento'];

    if ($titulo === '' || !in_array($tipo, $tiposValidos, true) || $dataEvento === '') {
        header('Location: painel-direcao-agenda.php?erro=campos');
        exit;
    }

    $paraProfessores  = isset($_POST['para_professores']) ? 1 : 0;
    $paraAlunos       = isset($_POST['para_alunos']) ? 1 : 0;
    $paraEncarregados = isset($_POST['para_encarregados']) ? 1 : 0;

    if (!$paraProfessores && !$paraAlunos && !$paraEncarregados) {
        header('Location: painel-direcao-agenda.php?erro=publico');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO agenda_eventos (titulo, descricao, tipo, data_evento, autor_id, turma_alvo, para_professores, para_alunos, para_encarregados)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $titulo,
        $descricao ?: null,
        $tipo,
        $dataEvento,
        $_SESSION['user_id'],
        $turmaAlvo ?: null,
        $paraProfessores,
        $paraAlunos,
        $paraEncarregados,
    ]);

    header('Location: painel-direcao-agenda.php?msg=criado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM agenda_eventos WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    header('Location: painel-direcao-agenda.php?msg=eliminado');
    exit;
}

header('Location: painel-direcao-agenda.php');
exit;
