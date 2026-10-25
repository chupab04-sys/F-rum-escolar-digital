<?php
/**
 * CEPS · Fórum Escolar — processa o registo do Aluno
 * Recebe os dados do formulário em registo-aluno.html,
 * cria a conta em "utilizadores" e os dados extra em "alunos_dados".
 */

session_start();
require 'config.php';

$nome           = trim($_POST['nome'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = trim($_POST['password'] ?? '');
$password2      = trim($_POST['password_confirmar'] ?? '');
$turma          = trim($_POST['turma'] ?? '');
$classe         = trim($_POST['classe'] ?? '');
$curso          = trim($_POST['curso'] ?? '');   // opcional — só ensino técnico/médio
$sala           = trim($_POST['sala'] ?? '');
$numero_aluno   = trim($_POST['numero_aluno'] ?? '');

// 1) Campos obrigatórios (curso fica de fora — é opcional)
if ($nome === '' || $email === '' || $password === '' || $password2 === ''
    || $turma === '' || $classe === '' || $sala === '' || $numero_aluno === '') {
    header('Location: registo-aluno.html?erro=campos');
    exit;
}

// 2) As duas palavras-passe têm de coincidir
if ($password !== $password2) {
    header('Location: registo-aluno.html?erro=senhas');
    exit;
}

// 3) Palavra-passe com tamanho mínimo
if (strlen($password) < 6) {
    header('Location: registo-aluno.html?erro=curto');
    exit;
}

// 4) O e-mail ainda não pode existir na base de dados
$stmt = $pdo->prepare('SELECT id FROM utilizadores WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: registo-aluno.html?erro=email_existe');
    exit;
}

// 5) Cria a conta em "utilizadores"
//    ativo = 1 -> o aluno já pode entrar imediatamente após o registo
$palavra_passe_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO utilizadores (nome, email, palavra_passe, perfil, ativo)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$nome, $email, $palavra_passe_hash, 'aluno', 1]);

$utilizador_id = $pdo->lastInsertId();

// 6) Guarda os dados específicos do aluno em "alunos_dados"
//    curso fica NULL quando o aluno é do ensino geral (campo em branco)
$curso_para_guardar = ($curso === '') ? null : $curso;

$stmt = $pdo->prepare(
    'INSERT INTO alunos_dados (utilizador_id, turma, classe, curso, sala, numero_aluno)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$utilizador_id, $turma, $classe, $curso_para_guardar, $sala, $numero_aluno]);

// 7) Regista logo a sessão, tal como o login.php faz,
//    para o aluno entrar diretamente no seu painel
$_SESSION['user_id'] = $utilizador_id;
$_SESSION['nome']    = $nome;
$_SESSION['email']   = $email;
$_SESSION['perfil']  = 'aluno';

header('Location: painel-aluno.php');
exit;