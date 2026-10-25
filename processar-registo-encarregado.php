<?php
/**
 * CEPS · Fórum Escolar — processa o registo do Encarregado
 * Recebe os dados do formulário em registo-encarregado.html,
 * confirma que o aluno indicado existe (pelo número), cria a conta
 * em "utilizadores" e liga o encarregado ao aluno em "encarregados_dados".
 */

session_start();
require 'config.php';

$nome            = trim($_POST['nome'] ?? '');
$email           = trim($_POST['email'] ?? '');
$password        = trim($_POST['password'] ?? '');
$password2       = trim($_POST['password_confirmar'] ?? '');
$parentesco      = trim($_POST['parentesco'] ?? '');
$telefone        = trim($_POST['telefone'] ?? '');
$nome_aluno      = trim($_POST['nome_aluno'] ?? '');
$numero_aluno    = trim($_POST['numero_aluno'] ?? '');

// 1) Todos os campos são obrigatórios
if ($nome === '' || $email === '' || $password === '' || $password2 === ''
    || $parentesco === '' || $telefone === '' || $nome_aluno === '' || $numero_aluno === '') {
    header('Location: registo-encarregado.html?erro=campos');
    exit;
}

// 2) As duas palavras-passe têm de coincidir
if ($password !== $password2) {
    header('Location: registo-encarregado.html?erro=senhas');
    exit;
}

// 3) Palavra-passe com tamanho mínimo
if (strlen($password) < 6) {
    header('Location: registo-encarregado.html?erro=curto');
    exit;
}

// 4) O e-mail do encarregado ainda não pode existir
$stmt = $pdo->prepare('SELECT id FROM utilizadores WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: registo-encarregado.html?erro=email_existe');
    exit;
}

// 5) Confirma que o aluno indicado existe, através do número do aluno
//    (o sistema verifica automaticamente a que turma/curso ele pertence)
$stmt = $pdo->prepare(
    'SELECT u.id AS utilizador_id, u.nome, ad.turma, ad.curso, ad.sala
     FROM alunos_dados ad
     JOIN utilizadores u ON u.id = ad.utilizador_id
     WHERE ad.numero_aluno = ?'
);
$stmt->execute([$numero_aluno]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    header('Location: registo-encarregado.html?erro=aluno_nao_encontrado');
    exit;
}

// 6) Confere se o nome indicado corresponde ao aluno desse número
//    (comparação sem sensibilidade a maiúsculas/espaços extra)
$nome_normalizado       = mb_strtolower(trim($nome_aluno));
$nome_aluno_normalizado = mb_strtolower(trim($aluno['nome']));

if ($nome_normalizado !== $nome_aluno_normalizado) {
    header('Location: registo-encarregado.html?erro=nome_nao_coincide');
    exit;
}

// 7) Cria a conta do encarregado em "utilizadores"
//    ativo = 1 -> pode entrar imediatamente após o registo
$palavra_passe_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO utilizadores (nome, email, palavra_passe, perfil, ativo)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$nome, $email, $palavra_passe_hash, 'encarregado', 1]);

$utilizador_id = $pdo->lastInsertId();

// 8) Liga o encarregado ao aluno encontrado, em "encarregados_dados"
//    (a turma e o curso do aluno já ficam disponíveis via JOIN sempre que precisar,
//    não é necessário duplicá-los aqui)
$stmt = $pdo->prepare(
    'INSERT INTO encarregados_dados (utilizador_id, aluno_utilizador_id, numero_aluno, nome_aluno, parentesco, telefone)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$utilizador_id, $aluno['utilizador_id'], $numero_aluno, $nome_aluno, $parentesco, $telefone]);

// 9) Regista logo a sessão, tal como o login.php faz
$_SESSION['user_id'] = $utilizador_id;
$_SESSION['nome']    = $nome;
$_SESSION['email']   = $email;
$_SESSION['perfil']  = 'encarregado';

header('Location: painel-encarregado.php');
exit;
