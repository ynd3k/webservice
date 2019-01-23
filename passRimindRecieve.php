
<?php
  require('function.php');
  delog('#######');
  delog('パスワード再発行認証キー入力ページ');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');


  if(!empty($_POST)){
    if($_POST['auth'] === $_SESSION['auth_key'] && $_SESSION['auth_limit'] > time()){
      delog('key一致');
      $pass = makeRandKey();

      try{
        $dbh = dbConnect();
        $sql = 'UPDATE users SET pass=:pass WHERE id=:id';
        $data = array(':pass'=>password_hash($pass,PASSWORD_DEFAULT),':id'=>$_SESSION['user_id']);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
          $username = 'a';
          $from = '@gmail.com';
          $to = '';
          $subject = '再発行パスです';
          $comment = <<<EOT
再発行パスでログインしてください
パス：　{$pass}
EOT;
        sendMail($from,$to,$subject,$comment);

        session_unset();
        header("Location:login.php");
        }
      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }
    }
  }


?>

 <div id="main">

<?php require('link.php'); ?>
   <section class="form-section">
     <h1>認証キー入力</h1>

       <form class="form-group" action="" method="post">
         <div class="form-group">
           <label>
             認証キー入力<br>
             <input type="text" name="auth" value="">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['auth'])) echo $err_msg['auth'];?>
         </div>


         <input type="submit" name="submit" value="送信">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
