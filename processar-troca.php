<?php
/**
 * CEPS · Fórum Escolar — processa anúncios do Espaço de Trocas
 */

require 'auth_check.php';
protegerPainel('encarregado');
require 'config.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'publicar') {
    $titulo     = trim($_POST['titulo'] ?? '');
    $categoria  = trim($_POST['categoria'] ?? '');
    $telefone   = trim($_POST['telefone_contacto'] ?? '');
    $descricao  = trim($_POST['descricao'] ?? '');

    $categoriasValidas = ['Manual', 'Farda', 'Material', 'Outro'];

    if ($titulo === '' || !in_array($categoria, $categoriasValidas, true) || $telefone === '' || $descricao === '') {
        header('Location: painel-encarregado-trocas.php?erro=campos');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO trocas_anuncios (autor_id, titulo, descricao, categoria, telefone_contacto)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$_SESSION['user_id'], $titulo, $descricao, $categoria, $telefone]);

    header('Location: painel-encarregado-trocas.php?msg=publicado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM trocas_anuncios WHERE id = ? AND autor_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    header('Location: painel-encarregado-trocas.php?msg=eliminado');
    exit;
}

header('Location: painel-encarregado-trocas.php');
exit;
