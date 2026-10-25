<?php
/**
 * CEPS · Fórum Escolar — processa Partilha Pedagógica
 * Publica (ficheiro opcional) ou elimina uma partilha.
 * Qualquer professor pode publicar; só o autor pode eliminar a sua.
 */

require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$acao = $_POST['acao'] ?? '';

$pastaUploads = __DIR__ . '/uploads/partilhas/';
if (!is_dir($pastaUploads)) {
    mkdir($pastaUploads, 0755, true);
}

$extensoesPermitidas = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
$tamanhoMaximo = 10 * 1024 * 1024; // 10 MB

if ($acao === 'publicar') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($titulo === '' || $descricao === '') {
        header('Location: painel-professor-partilha.php?erro=campos');
        exit;
    }

    $nomeOriginal = null;
    $nomeServidor = null;

    // O ficheiro é opcional nesta funcionalidade
    if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] === UPLOAD_ERR_OK) {
        $ficheiro = $_FILES['ficheiro'];

        if ($ficheiro['size'] > $tamanhoMaximo) {
            header('Location: painel-professor-partilha.php?erro=tamanho');
            exit;
        }

        $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $extensoesPermitidas, true)) {
            header('Location: painel-professor-partilha.php?erro=tipo_invalido');
            exit;
        }

        $nomeServidor = uniqid('partilha_', true) . '.' . $extensao;
        $destino = $pastaUploads . $nomeServidor;

        if (!move_uploaded_file($ficheiro['tmp_name'], $destino)) {
            header('Location: painel-professor-partilha.php?erro=campos');
            exit;
        }
        $nomeOriginal = $ficheiro['name'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO partilhas_pedagogicas (autor_id, titulo, descricao, nome_ficheiro, caminho_ficheiro)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$_SESSION['user_id'], $titulo, $descricao, $nomeOriginal, $nomeServidor]);

    header('Location: painel-professor-partilha.php?msg=publicado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);

    // Só o autor pode eliminar a sua própria partilha
    $stmt = $pdo->prepare('SELECT caminho_ficheiro FROM partilhas_pedagogicas WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    $partilha = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($partilha) {
        if (!empty($partilha['caminho_ficheiro'])) {
            $caminhoCompleto = $pastaUploads . $partilha['caminho_ficheiro'];
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
        }
        $stmt = $pdo->prepare('DELETE FROM partilhas_pedagogicas WHERE id = ?');
        $stmt->execute([$id]);
    }

    header('Location: painel-professor-partilha.php?msg=eliminado');
    exit;
}

header('Location: painel-professor-partilha.php');
exit;
