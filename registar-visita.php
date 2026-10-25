<?php


if (!function_exists('registarVisita')) {
    function registarVisita(PDO $pdo, int $utilizadorId, string $cartao): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO paineis_visitas (utilizador_id, cartao, ultima_visita)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE ultima_visita = NOW()'
        );
        $stmt->execute([$utilizadorId, $cartao]);
    }
}