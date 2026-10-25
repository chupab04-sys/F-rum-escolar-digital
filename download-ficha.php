<?php
/**
 * CEPS · Fórum Escolar — download seguro de uma ficha
 * Confirma quem pode aceder antes de entregar o ficheiro.
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html?erro=sessao');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT f.nome_ficheiro, f.caminho_ficheiro, pt.turma, pt.professor_id
     FROM fichas f
     JOIN professor_turmas pt ON pt.id = f.professor_turma_id
     WHERE f.id = ?"
);
$stmt->execute([$id]);
$ficha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ficha) {
    http_response_code(404);
    die('Ficha não encontrada.');
}

$perfil = $_SESSION['perfil'];
$podeAceder = false;

if ($perfil === 'direcao') {
    $podeAceder = true;
} elseif ($perfil === 'professor' && (int)$ficha['professor_id'] === (int)$_SESSION['user_id']) {
    $podeAceder = true;
} elseif ($perfil === 'aluno') {
    $stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $turmaAluno = $stmt->fetchColumn();
    $podeAceder = ($turmaAluno === $ficha['turma']);
}

if (!$podeAceder) {
    http_response_code(403);
    die('Sem permissão para aceder a esta ficha.');
}

$caminhoCompleto = __DIR__ . '/uploads/fichas/' . $ficha['caminho_ficheiro'];
if (!file_exists($caminhoCompleto)) {
    http_response_code(404);
    die('Ficheiro não encontrado no servidor.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($ficha['nome_ficheiro']) . '"');
header('Content-Length: ' . filesize($caminhoCompleto));
readfile($caminhoCompleto);
exit;
