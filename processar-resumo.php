<?php
/**
 * CEPS · Fórum Escolar — processa a partilha de um Resumo
 * (texto, ficheiro ou áudio gravado) num Grupo de Estudo.
 * Só membros do grupo podem partilhar.
 */

require 'auth_check.php';
protegerPainel('aluno');
require 'config.php';

$grupoId = (int)($_POST['grupo_id'] ?? 0);
$texto   = trim($_POST['texto'] ?? '');

// Confirma que é membro do grupo
$stmt = $pdo->prepare('SELECT id FROM grupos_membros WHERE grupo_id = ? AND aluno_id = ?');
$stmt->execute([$grupoId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Location: painel-aluno-grupos.php');
    exit;
}

$pastaUploads = __DIR__ . '/uploads/resumos/';
if (!is_dir($pastaUploads)) {
    mkdir($pastaUploads, 0755, true);
}

$extensoesPermitidas = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'webm', 'ogg', 'mp3', 'wav', 'm4a'];
$tamanhoMaximo = 10 * 1024 * 1024; // 10 MB

$nomeOriginal = null;
$nomeServidor = null;

if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] === UPLOAD_ERR_OK) {
    $ficheiro = $_FILES['ficheiro'];

    if ($ficheiro['size'] > $tamanhoMaximo) {
        header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId . '&erro=tamanho');
        exit;
    }

    $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $extensoesPermitidas, true)) {
        header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId . '&erro=tipo_invalido');
        exit;
    }

    $nomeServidor = uniqid('resumo_', true) . '.' . $extensao;
    if (!move_uploaded_file($ficheiro['tmp_name'], $pastaUploads . $nomeServidor)) {
        header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId . '&erro=tipo_invalido');
        exit;
    }
    $nomeOriginal = $ficheiro['name'];
}

if ($texto === '' && $nomeOriginal === null) {
    header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId . '&erro=vazio');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO grupos_resumos (grupo_id, autor_id, texto, nome_ficheiro, caminho_ficheiro)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$grupoId, $_SESSION['user_id'], $texto ?: '(áudio)', $nomeOriginal, $nomeServidor]);

header('Location: painel-aluno-grupo-detalhe.php?id=' . $grupoId . '&msg=partilhado');
exit;
