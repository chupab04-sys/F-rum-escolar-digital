<?php
/**
 * CEPS · Fórum Escolar — download de um ficheiro da Partilha Pedagógica
 * Só Professores e Direção têm acesso a este espaço.
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['professor', 'direcao'], true)) {
    header('Location: login.html?erro=sessao');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT nome_ficheiro, caminho_ficheiro FROM partilhas_pedagogicas WHERE id = ?');
$stmt->execute([$id]);
$partilha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$partilha || empty($partilha['caminho_ficheiro'])) {
    http_response_code(404);
    die('Ficheiro não encontrado.');
}

$caminhoCompleto = __DIR__ . '/uploads/partilhas/' . $partilha['caminho_ficheiro'];
if (!file_exists($caminhoCompleto)) {
    http_response_code(404);
    die('Ficheiro não encontrado no servidor.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($partilha['nome_ficheiro']) . '"');
header('Content-Length: ' . filesize($caminhoCompleto));
readfile($caminhoCompleto);
exit;
