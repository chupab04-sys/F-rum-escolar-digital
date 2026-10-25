<?php
/**
 * CEPS · Fórum Escolar — ligação à base de dados
 * Todos os ficheiros PHP do site incluem este ficheiro.
 */

$host = 'localhost';
$dbname = 'ceps_forum';
$user = 'root';   // utilizador padrão do XAMPP
$pass = '';       // o XAMPP vem sem palavra-passe por padrão

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erro de ligação à base de dados: ' . $e->getMessage());
}
