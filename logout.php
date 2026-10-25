<?php
/**
 * CEPS · Fórum Escolar — termina a sessão
 */
session_start();
session_unset();
session_destroy();
header('Location: login.html');
exit;
