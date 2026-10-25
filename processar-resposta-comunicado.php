<?php
/**
 * CEPS · Fórum Escolar — processa a resposta de um Aluno ou
 * Encarregado a um comunicado do Professor.
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['aluno', 'encarregado'], true)) {
    header('Location: login.html?erro=sessao');
    exit;
}

$comunicadoId   = (int)($_POST['comunicado_id'] ?? 0);
$texto          = trim($_POST['texto'] ?? '');
$respostaRapida = trim($_POST['resposta_rapida'] ?? '');

$voltarPara = $_SESSION['perfil'] === 'aluno' ? 'painel-aluno-avisos.php' : 'painel-encarregado-circulares.php';

if ($comunicadoId <= 0 || ($texto === '' && $respostaRapida === '')) {
    header("Location: $voltarPara?erro=resposta_vazia");
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO comunicados_respostas (comunicado_id, utilizador_id, texto, resposta_rapida)
     VALUES (?, ?, ?, ?)'
);
$stmt->execute([
    $comunicadoId,
    $_SESSION['user_id'],
    $texto !== '' ? $texto : null,
    $respostaRapida !== '' ? $respostaRapida : null,
]);

header("Location: $voltarPara?msg=respondido");
exit;
