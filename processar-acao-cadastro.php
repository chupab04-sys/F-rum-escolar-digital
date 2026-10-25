<?php
/**
 * CEPS · Fórum Escolar — ações sobre contas (Aprovação de Cadastros)
 * Só a Direção pode ativar/desativar ou eliminar contas de
 * Alunos e Encarregados.
 */

require 'auth_check.php';
protegerPainel('direcao');
require 'config.php';

$id            = (int)($_POST['id'] ?? 0);
$acao          = $_POST['acao'] ?? '';
$perfilFiltro  = $_POST['perfil_filtro'] ?? 'todos';

if ($id <= 0 || !in_array($acao, ['alternar_ativo', 'eliminar'], true)) {
    header('Location: painel-direcao-cadastros.php?perfil=' . urlencode($perfilFiltro));
    exit;
}

// Nunca permitir esta ação sobre contas de Professor ou Direção
// (este painel só gere Aluno/Encarregado)
$stmt = $pdo->prepare("SELECT id, perfil, ativo FROM utilizadores WHERE id = ? AND perfil IN ('aluno','encarregado')");
$stmt->execute([$id]);
$conta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conta) {
    header('Location: painel-direcao-cadastros.php?perfil=' . urlencode($perfilFiltro));
    exit;
}

if ($acao === 'alternar_ativo') {
    $novoEstado = $conta['ativo'] ? 0 : 1;
    $stmt = $pdo->prepare('UPDATE utilizadores SET ativo = ? WHERE id = ?');
    $stmt->execute([$novoEstado, $id]);
    $msg = 'atualizado';
}

if ($acao === 'eliminar') {
    // As tabelas alunos_dados / encarregados_dados têm
    // ON DELETE CASCADE, por isso os dados associados saem juntos.
    $stmt = $pdo->prepare('DELETE FROM utilizadores WHERE id = ?');
    $stmt->execute([$id]);
    $msg = 'eliminado';
}

header('Location: painel-direcao-cadastros.php?perfil=' . urlencode($perfilFiltro) . '&msg=' . $msg);
exit;
