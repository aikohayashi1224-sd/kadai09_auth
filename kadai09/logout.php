<?php
session_start();
session_destroy();
header('Location: /gs_kadai/kadai09/index.php');
exit;