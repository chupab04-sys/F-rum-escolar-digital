<?php
/**
 * CEPS · Fórum Escolar — proteção dos painéis
 *
 * Cada painel-*.php começa com:
 *   require 'auth_check.php';
 *   protegerPainel('aluno'); // ou 'professor' / 'direcao' / 'encarregado'
 *
 * Isto garante que só quem fez login E tem o perfil certo
 * consegue ver aquela página.
 */

session_start();

function protegerPainel(string $perfilEsperado): void
{
    // Sem sessão -> volta para o login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html?erro=sessao');
        exit;
    }

    // Sessão existe mas é de outro perfil -> não deixa ver este painel
    if ($_SESSION['perfil'] !== $perfilEsperado) {
        header('Location: login.html?erro=permissao');
        exit;
    }
}
