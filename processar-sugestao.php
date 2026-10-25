<?php
/**
 * CEPS · Fórum Escolar — processa o envio de uma sugestão anónima
 * Não guarda user_id nenhum — só o perfil de quem enviou.
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['professor', 'aluno', 'encarregado'], true)) {
    header('Location: login.html?erro=sessao');
    exit;
}

$perfil = $_SESSION['perfil'];
$destinos = [
    'professor'   => 'painel-professor-sugestoes.php',
    'aluno'       => 'painel-aluno-sugestoes.php',
    'encarregado' => 'painel-encarregado-sugestoes.php',
];
$voltarPara = $destinos[$perfil];

$mensagem = trim($_POST['mensagem'] ?? '');

if ($mensagem === '') {
    header("Location: $voltarPara?erro=vazio");
    exit;
}

// Nota: propositadamente não gravamos $_SESSION['user_id'] em lado nenhum
// aqui — só o perfil, para manter a sugestão anónima.
$stmt = $pdo->prepare('INSERT INTO sugestoes (perfil_autor, mensagem) VALUES (?, ?)');
$stmt->execute([$perfil, $mensagem]);

header("Location: $voltarPara?msg=enviado");
exit;
