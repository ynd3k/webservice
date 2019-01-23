<?php

require('function.php') ;
delog('#####');
delog('ログアウト');
delogStart();

session_destroy();

header("Location:login.php");
 ?>
