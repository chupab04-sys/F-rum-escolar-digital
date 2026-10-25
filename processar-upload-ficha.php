<?php
/**
 * CEPS · Fórum Escolar — processa Upload de Fichas
 * Publica (com ficheiro) ou elimina uma ficha, sempre confirmando
 * que a turma/disciplina pertence mesmo ao professor autenticado.
 */

require 'auth_check.php';
protegerPainel('professor');
require 'config.php';

$acao = $_POST['acao'] ?? '';

// Pasta onde os ficheiros ficam guardados (fora de vista direta,
// mas ainda dentro do projeto para simplicidade no XAMPP)
$pastaUploads = __DIR__ . '/uploads/fichas/';
if (!is_dir($pastaUploads)) {
    mkdir($pastaUploads, 0755, true);
}

$extensoesPermitidas = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
$tamanhoMaximo = 10 * 1024 * 1024; // 10 MB

if ($acao === 'publicar') {
    $professorTurmaId = (int)($_POST['professor_turma_id'] ?? 0);
    $titulo           = trim($_POST['titulo'] ?? '');
    $descricao        = trim($_POST['descricao'] ?? '');
    $dataEntrega      = trim($_POST['data_entrega'] ?? '');

    if ($professorTurmaId <= 0 || $titulo === '') {
        header('Location: painel-professor-fichas.php?erro=campos');
        exit;
    }

    // Confirma que esta turma/disciplina pertence mesmo a este professor
    $stmt = $pdo->prepare('SELECT id FROM professor_turmas WHERE id = ? AND professor_id = ?');
    $stmt->execute([$professorTurmaId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        header('Location: painel-professor-fichas.php?erro=atribuicao_invalida');
        exit;
    }

    if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK) {
        header('Location: painel-professor-fichas.php?erro=ficheiro');
        exit;
    }

    $ficheiro = $_FILES['ficheiro'];

    if ($ficheiro['size'] > $tamanhoMaximo) {
        header('Location: painel-professor-fichas.php?erro=tamanho');
        exit;
    }

    $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $extensoesPermitidas, true)) {
        header('Location: painel-professor-fichas.php?erro=tipo_invalido');
        exit;
    }

    // Nome único no servidor, para nunca sobrepor ficheiros
    $nomeServidor = uniqid('ficha_', true) . '.' . $extensao;
    $destino = $pastaUploads . $nomeServidor;

    if (!move_uploaded_file($ficheiro['tmp_name'], $destino)) {
        header('Location: painel-professor-fichas.php?erro=ficheiro');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO fichas (professor_turma_id, titulo, descricao, data_entrega, nome_ficheiro, caminho_ficheiro)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$professorTurmaId, $titulo, $descricao ?: null, $dataEntrega ?: null, $ficheiro['name'], $nomeServidor]);

    header('Location: painel-professor-fichas.php?msg=publicado');
    exit;
}

if ($acao === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);

    // Só permite eliminar uma ficha que pertença a uma turma/disciplina deste professor
    $stmt = $pdo->prepare(
        "SELECT f.caminho_ficheiro
         FROM fichas f
         JOIN professor_turmas pt ON pt.id = f.professor_turma_id
         WHERE f.id = ? AND pt.professor_id = ?"
    );
    $stmt->execute([$id, $_SESSION['user_id']]);
    $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ficha) {
        $caminhoCompleto = $pastaUploads . $ficha['caminho_ficheiro'];
        if (file_exists($caminhoCompleto)) {
            unlink($caminhoCompleto);
        }
        $stmt = $pdo->prepare('DELETE FROM fichas WHERE id = ?');
        $stmt->execute([$id]);
    }

    header('Location: painel-professor-fichas.php?msg=eliminado');
    exit;
}

header('Location: painel-professor-fichas.php');
exit;