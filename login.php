<?php
/**
 * CEPS · Fórum Escolar — processa o login real
 * Recebe os dados do formulário em login.html, verifica na
 * base de dados, e redireciona para o painel certo.
 *
 * Segurança: bloqueia temporariamente depois de 5 tentativas
 * falhadas seguidas com o mesmo email, dentro de 15 minutos.
 */

session_start();
require 'config.php';

$LIMITE_TENTATIVAS = 5;
$JANELA_MINUTOS = 15;

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header('Location: login.html?erro=campos');
    exit;
}

// 1) Verifica se este email já está temporariamente bloqueado
$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM tentativas_login
     WHERE email = ? AND sucesso = 0
       AND criado_em > (NOW() - INTERVAL ? MINUTE)"
);
$stmt->execute([$email, $JANELA_MINUTOS]);
$falhasRecentes = (int)$stmt->fetchColumn();

if ($falhasRecentes >= $LIMITE_TENTATIVAS) {
    header('Location: login.html?erro=bloqueado');
    exit;
}

// 2) Procura o utilizador pelo email
$stmt = $pdo->prepare('SELECT id, nome, email, palavra_passe, perfil, ativo FROM utilizadores WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 3) Verifica se existe e se a palavra-passe bate certo
if (!$user || !password_verify($password, $user['palavra_passe'])) {
    // Regista a tentativa falhada
    $stmt = $pdo->prepare('INSERT INTO tentativas_login (email, sucesso) VALUES (?, 0)');
    $stmt->execute([$email]);

    // Se esta foi a tentativa que atingiu o limite, avisa já do bloqueio
    if ($falhasRecentes + 1 >= $LIMITE_TENTATIVAS) {
        header('Location: login.html?erro=bloqueado');
        exit;
    }

    header('Location: login.html?erro=credenciais');
    exit;
}

// 4) Verifica se a conta já foi aprovada pela Direção
if ((int)$user['ativo'] !== 1) {
    header('Location: login.html?erro=inativo');
    exit;
}

// 5) Login válido -> guarda quando foi a ÚLTIMA entrada (antes desta),
//    regista esta nova entrada, e cria a sessão
$stmt = $pdo->prepare(
    "SELECT criado_em FROM tentativas_login WHERE email = ? AND sucesso = 1 ORDER BY criado_em DESC LIMIT 1"
);
$stmt->execute([$email]);
$ultimaEntradaAnterior = $stmt->fetchColumn();

$stmt = $pdo->prepare('INSERT INTO tentativas_login (email, sucesso) VALUES (?, 1)');
$stmt->execute([$email]);

$_SESSION['user_id'] = $user['id'];
$_SESSION['nome'] = $user['nome'];
$_SESSION['email'] = $user['email'];
$_SESSION['perfil'] = $user['perfil'];
$_SESSION['ultima_entrada'] = $ultimaEntradaAnterior ?: null;

// Redireciona para o painel certo consoante o perfil
$destinos = [
    'aluno'       => 'painel-aluno.php',
    'professor'   => 'painel-professor.php',
    'direcao'     => 'painel-direcao.php',
    'encarregado' => 'painel-encarregado.php',
];

header('Location: ' . ($destinos[$user['perfil']] ?? 'login.html'));
exit;