<?php
session_start();
session_unset();
session_destroy();
header('Location: ../src/pages/signup.php?form=login');
exit;
