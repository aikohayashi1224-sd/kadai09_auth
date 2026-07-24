<?php
session_start();
session_destroy();
header('Location: /kadai09/index.php');
exit;