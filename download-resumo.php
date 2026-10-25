<?php
/**
 * CEPS · Fórum Escolar — download/reprodução de um ficheiro de Resumo
 * Só membros do grupo correspondente têm acesso.
 */

require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT r.nome_ficheiro, r.caminho_ficheiro, r.grupo_id
     FROM grupos_resumos r
     WHERE r.id = ?"
);
$stmt->execute([$id]);
$resumo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resumo || empty($resumo['caminho_ficheiro'])) {
    http_response_code(404);
    die('Ficheiro não encontrado.');
}

// Confirma que este aluno é membro do grupo a que o resumo pertence
$stmt = $pdo->prepare('SELECT id FROM grupos_membros WHERE grupo_id = ? AND aluno_id = ?');
$stmt->execute([$resumo['grupo_id'], $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    http_response_code(403);
    die('Sem permissão para aceder a este ficheiro.');
}

$caminhoCompleto = __DIR__ . '/uploads/resumos/' . $resumo['caminho_ficheiro'];
if (!file_exists($caminhoCompleto)) {
    http_response_code(404);
    die('Ficheiro não encontrado no servidor.');
}

$extensao = strtolower(pathinfo($resumo['nome_ficheiro'], PATHINFO_EXTENSION));
$tiposAudio = ['webm' => 'audio/webm', 'ogg' => 'audio/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'm4a' => 'audio/mp4'];

if (isset($tiposAudio[$extensao])) {
    // Áudio: reproduz diretamente no leitor, sem forçar download
    header('Content-Type: ' . $tiposAudio[$extensao]);
    header('Content-Length: ' . filesize($caminhoCompleto));
    readfile($caminhoCompleto);
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($resumo['nome_ficheiro']) . '"');
header('Content-Length: ' . filesize($caminhoCompleto));
readfile($caminhoCompleto);
exit;
