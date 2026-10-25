<?php
require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$acao = $_POST['acao'] ?? '';
$id   = (int)($_POST['id'] ?? 0);
$filtro = $_POST['perfil_filtro'] ?? 'todos';

if ($acao === 'marcar_lida') {
    $stmt = $pdo->prepare('UPDATE sugestoes SET lida = 1 WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: painel-direcao-sugestoes.php?perfil=' . urlencode($filtro) . '&msg=atualizado');
    exit;
}

if ($acao === 'eliminar') {
    $stmt = $pdo->prepare('DELETE FROM sugestoes WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: painel-direcao-sugestoes.php?perfil=' . urlencode($filtro) . '&msg=eliminado');
    exit;
}

header('Location: painel-direcao-sugestoes.php');
exit;
