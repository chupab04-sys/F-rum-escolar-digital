<?php
/**
 * CEPS · Fórum Escolar — gerador de dados fictícios para testes
 *
 * Cria professores, alunos e atribuições de turma/disciplina fictícios,
 * com a palavra-passe "123456" para todos (já encriptada corretamente,
 * porque este script usa o password_hash() do seu próprio PHP).
 *
 * COMO USAR:
 *   1. Coloque este ficheiro na pasta do projeto (ceps-forum).
 *   2. Abra no browser: localhost/ceps-forum/gerar_dados_teste.php
 *   3. Veja a lista de contas criadas (email + password).
 *   4. IMPORTANTE: apague este ficheiro depois de usar — não deve
 *      ficar disponível num servidor real.
 */

require 'config.php';

$passwordHash = password_hash('123456', PASSWORD_DEFAULT);

function obterOuCriarUtilizador(PDO $pdo, string $nome, string $email, string $perfil, string $passwordHash): int
{
    $stmt = $pdo->prepare('SELECT id FROM utilizadores WHERE email = ?');
    $stmt->execute([$email]);
    $existente = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existente) {
        return (int)$existente['id'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO utilizadores (nome, email, palavra_passe, perfil, ativo) VALUES (?, ?, ?, ?, 1)'
    );
    $stmt->execute([$nome, $email, $passwordHash, $perfil]);
    return (int)$pdo->lastInsertId();
}

// ---------------------------------------------------------
// 1) Professores + as suas turmas/disciplinas
// ---------------------------------------------------------
$professores = [
    [
        'nome' => 'Ana Silva',
        'email' => 'ana.silva@ceps.test',
        'turmas' => [
            ['turma' => '10ª A', 'disciplina' => 'Matemática'],
            ['turma' => '10ª B', 'disciplina' => 'Matemática'],
        ],
    ],
    [
        'nome' => 'Carlos Mendes',
        'email' => 'carlos.mendes@ceps.test',
        'turmas' => [
            ['turma' => '10ª A', 'disciplina' => 'Língua Portuguesa'],
            ['turma' => '11ª A', 'disciplina' => 'Física'],
        ],
    ],
    [
        'nome' => 'Beatriz Costa',
        'email' => 'beatriz.costa@ceps.test',
        'turmas' => [
            ['turma' => '10ª A', 'disciplina' => 'Inglês'],
            ['turma' => '10ª B', 'disciplina' => 'Inglês'],
            ['turma' => '11ª A', 'disciplina' => 'Inglês'],
        ],
    ],
];

$resumo = [];

foreach ($professores as $p) {
    $id = obterOuCriarUtilizador($pdo, $p['nome'], $p['email'], 'professor', $passwordHash);
    $resumo[] = "Professor: {$p['nome']} — {$p['email']} — password: 123456";

    foreach ($p['turmas'] as $t) {
        $stmt = $pdo->prepare('SELECT id FROM professor_turmas WHERE professor_id = ? AND turma = ? AND disciplina = ?');
        $stmt->execute([$id, $t['turma'], $t['disciplina']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare('INSERT INTO professor_turmas (professor_id, turma, disciplina) VALUES (?, ?, ?)');
            $stmt->execute([$id, $t['turma'], $t['disciplina']]);
        }
    }
}

// ---------------------------------------------------------
// 2) Alunos (turma, classe, curso opcional, sala, número)
// ---------------------------------------------------------
$alunos = [
    ['nome' => 'João Pedro',      'email' => 'joao.pedro@ceps.test',      'turma' => '10ª A', 'classe' => '10ª Classe', 'curso' => null, 'sala' => 'Sala 5',  'numero' => '101'],
    ['nome' => 'Maria Fernandes', 'email' => 'maria.fernandes@ceps.test', 'turma' => '10ª A', 'classe' => '10ª Classe', 'curso' => null, 'sala' => 'Sala 5',  'numero' => '102'],
    ['nome' => 'Sofia Almeida',   'email' => 'sofia.almeida@ceps.test',   'turma' => '10ª A', 'classe' => '10ª Classe', 'curso' => null, 'sala' => 'Sala 5',  'numero' => '103'],
    ['nome' => 'Lucas Tavares',   'email' => 'lucas.tavares@ceps.test',   'turma' => '10ª A', 'classe' => '10ª Classe', 'curso' => null, 'sala' => 'Sala 5',  'numero' => '104'],
    ['nome' => 'Rita Gonçalves',  'email' => 'rita.goncalves@ceps.test',  'turma' => '10ª B', 'classe' => '10ª Classe', 'curso' => null, 'sala' => 'Sala 6',  'numero' => '105'],
    ['nome' => 'Miguel Santos',   'email' => 'miguel.santos@ceps.test',   'turma' => '10ª B', 'classe' => '10ª Classe', 'curso' => null, 'sala' => 'Sala 6',  'numero' => '106'],
    ['nome' => 'Beatriz Rocha',   'email' => 'beatriz.rocha@ceps.test',   'turma' => '11ª A', 'classe' => '11ª Classe', 'curso' => 'Ciências Físicas e Biológicas', 'sala' => 'Sala 8', 'numero' => '107'],
    ['nome' => 'Tiago Ferreira',  'email' => 'tiago.ferreira@ceps.test',  'turma' => '11ª A', 'classe' => '11ª Classe', 'curso' => 'Ciências Físicas e Biológicas', 'sala' => 'Sala 8', 'numero' => '108'],
];

foreach ($alunos as $a) {
    $id = obterOuCriarUtilizador($pdo, $a['nome'], $a['email'], 'aluno', $passwordHash);
    $resumo[] = "Aluno: {$a['nome']} ({$a['turma']}) — {$a['email']} — password: 123456 — nº aluno: {$a['numero']}";

    $stmt = $pdo->prepare('SELECT id FROM alunos_dados WHERE utilizador_id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare(
            'INSERT INTO alunos_dados (utilizador_id, turma, classe, curso, sala, numero_aluno) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$id, $a['turma'], $a['classe'], $a['curso'], $a['sala'], $a['numero']]);
    }
}

// ---------------------------------------------------------
// 3) Um Encarregado de exemplo, já ligado ao primeiro aluno
// ---------------------------------------------------------
$idEncarregado = obterOuCriarUtilizador($pdo, 'Manuel Pedro', 'manuel.pedro@ceps.test', 'encarregado', $passwordHash);
$resumo[] = "Encarregado: Manuel Pedro — manuel.pedro@ceps.test — password: 123456 (educando: João Pedro)";

$stmt = $pdo->prepare('SELECT id FROM utilizadores WHERE email = ?');
$stmt->execute(['joao.pedro@ceps.test']);
$idJoao = $stmt->fetchColumn();

if ($idJoao) {
    $stmt = $pdo->prepare('SELECT id FROM encarregados_dados WHERE utilizador_id = ?');
    $stmt->execute([$idEncarregado]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare(
            'INSERT INTO encarregados_dados (utilizador_id, aluno_utilizador_id, numero_aluno, nome_aluno, parentesco, telefone)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$idEncarregado, $idJoao, '101', 'João Pedro', 'Pai', '923000000']);
    }
}

?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <title>Dados de Teste Criados | CEPS</title>
  <style>
    body{ font-family: Arial, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 20px; color:#24262b; }
    h1{ font-size: 1.4rem; }
    .aviso{ background:#fdecea; border:1px solid #f3b8b0; color:#a5423b; padding:14px 18px; border-radius:8px; margin-bottom:24px; font-size:.9rem; }
    ul{ line-height: 1.8; }
    li{ font-size: .92rem; }
  </style>
</head>
<body>
  <h1>✅ Dados fictícios criados com sucesso</h1>
  <div class="aviso">⚠️ Apague este ficheiro (<code>gerar_dados_teste.php</code>) do servidor agora que já correu — não deve ficar acessível publicamente.</div>
  <ul>
    <?php foreach ($resumo as $linha): ?>
      <li><?= htmlspecialchars($linha) ?></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
