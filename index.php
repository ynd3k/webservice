<?php

require('function.php');
delog('##################');
delog('トップページ');
delogStart();

require('head.php');
require('header.php');
?>



    <div id="main">
      <section>
        <p><a href="forum.php">掲示板</a></p>
        <div id="top-wrapper">
          <div id="top-baner">

          </div>
          <div id="top-2button">
            <button onClick="location.href='signup.php'">登録</button>
            <button onClick="location.href='login.php'">ログイン</button>
          </div>
        </div>
      </section>


    </div>

    <?php require('footer.php'); ?>
