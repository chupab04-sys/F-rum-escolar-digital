<?php
/**
 * CEPS · Fórum Escolar — marca um comunicado como lido
 * Funciona para qualquer perfil (professor, aluno, encarregado).
 */

require 'auth_check.php';
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html?erro=sessao');
    exit;
}

$comunicadoId = (int)($_POST['comunicado_id'] ?? 0);
$origem       = $_POST['origem'] ?? '';

$destinos = [
    'professor'   => 'painel-professor-avisos.php',
    'aluno'       => 'painel-aluno-avisos.php',
    'encarregado' => 'painel-encarregado-circulares.php',
];
$voltarPara = $destinos[$origem] ?? 'login.html';

if ($comunicadoId > 0) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO comunicados_leituras (comunicado_id, utilizador_id) VALUES (?, ?)'
        );
        $stmt->execute([$comunicadoId, $_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Já estava marcado como lido — sem problema, ignora.
    }
}

header("Location: $voltarPara");
exit;
