<?php

delog('#######');
delog('ログイン認証にんしょう');



if(!empty($_SESSION['user_id'])){
  delog('セッションuser_idがあります。ログイン済です');

  if( ($_SESSION['login_date'] + $_SESSION['login_limit']) > time() ){
    delog('セッションに有効期限内です,login_dateを更新し、掲示板ページに遷移します');
    $_SESSION['login_date'] = time();
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      header("Location:forum.php");
    }
  }else{
    delog('セッションが有効期限外です..セッション削除を行います');
    session_destroy();
    header("Location:login.php");
  }

}else{
  delog('セッション変数にuser_id無し。未ログインです');
  if( basename($_SERVER['PHP_SELF']) !== 'login.php' ){
    header("Location:login.php");
  }
}

 ?>
