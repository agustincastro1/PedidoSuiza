<?php
session_start();
session_destroy();
header('Location: views/pedidos.php');
exit;
