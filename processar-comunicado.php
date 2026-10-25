<?php
/**
 * CEPS · Fórum Escolar — processa comunicados (criar / eliminar)
 * Usado tanto pela Direção (comunicado geral) como pelo Professor
 * (comunicado à sua turma).
 */

require 'auth_check.php';
require 'config.php';

// Aceita Direção OU Professor — não usa protegerPainel() porque
// esse só permite um perfil de cada vez.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['direcao', 'professor'], true)) {
    header('Location: login.html?erro=sessao');
    exit;
}

$perfil = $_SESSION['perfil'];
$acao   = $_POST['acao'] ?? '';

// Para onde voltar depois (cada perfil tem a sua própria página)
$voltarPara = $perfil === 'direcao' ? 'painel-direcao-comunicado.php' : 'painel-professor-contacto.php';

if ($acao === 'criar') {
    $titulo   = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($titulo === '' || $mensagem === '') {
        header("Location: $voltarPara?erro=campos");
        exit;
    }

    if ($perfil === 'direcao') {
        // Direção: escolhe qualquer combinação de públicos, sempre para a escola toda
        $paraProfessores  = isset($_POST['para_professores']) ? 1 : 0;
        $paraAlunos       = isset($_POST['para_alunos']) ? 1 : 0;
        $paraEncarregados = isset($_POST['para_encarregados']) ? 1 : 0;
        $turmaAlvo        = null;

        if (!$paraProfessores && !$paraAlunos && !$paraEncarregados) {
            header("Location: $voltarPara?erro=publico");
            exit;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO comunicados (titulo, mensagem, autor_id, autor_perfil, turma_alvo, para_professores, para_alunos, para_encarregados)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$titulo, $mensagem, $_SESSION['user_id'], 'direcao', $turmaAlvo, $paraProfessores, $paraAlunos, $paraEncarregados]);

    } else {
        // Professor: este cartão é exclusivamente para os Encarregados
        // da sua turma (Alunos deixou de ser uma opção aqui —
        // isso passou a viver dentro de "Resposta a Dúvidas").
        $turma       = trim($_POST['turma'] ?? '');
        $modoDestino = $_POST['modo_destino'] ?? 'turma';
        $destinatarioId = null;

        if ($turma === '') {
            header("Location: $voltarPara?erro=turma");
            exit;
        }

        // Confirma que esta turma está mesmo atribuída a este professor
        $stmt = $pdo->prepare('SELECT id FROM professor_turmas WHERE professor_id = ? AND turma = ?');
        $stmt->execute([$_SESSION['user_id'], $turma]);
        if (!$stmt->fetch()) {
            header("Location: $voltarPara?erro=turma_invalida");
            exit;
        }

        if ($modoDestino === 'especifico') {
            $destinatarioId = (int)($_POST['destinatario_id'] ?? 0);

            // Confirma que este encarregado é mesmo de um aluno desta turma
            $stmt = $pdo->prepare(
                "SELECT ed.utilizador_id
                 FROM encarregados_dados ed
                 JOIN alunos_dados ad ON ad.utilizador_id = ed.aluno_utilizador_id
                 WHERE ed.utilizador_id = ? AND ad.turma = ?"
            );
            $stmt->execute([$destinatarioId, $turma]);
            if (!$stmt->fetch()) {
                header("Location: $voltarPara?erro=destinatario_invalido");
                exit;
            }
        }

        $stmt = $pdo->prepare(
            'INSERT INTO comunicados (titulo, mensagem, autor_id, autor_perfil, turma_alvo, destinatario_id, para_professores, para_alunos, para_encarregados)
             VALUES (?, ?, ?, ?, ?, ?, 0, 0, 1)'
        );
        $stmt->execute([$titulo, $mensagem, $_SESSION['user_id'], 'professor', $turma, $destinatarioId]);
    }

    header("Location: $voltarPara?msg=criado");
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);

    // Só pode eliminar comunicados que o próprio criou
    $stmt = $pdo->prepare('DELETE FROM comunicados WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);

    header("Location: $voltarPara?msg=eliminado");
    exit;
}

header("Location: $voltarPara");
exit;