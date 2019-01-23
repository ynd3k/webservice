<?php
  require('function.php');
  delog('#######');
  delog('ログイン画面★');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');




    if(!empty($_POST)){
      delog('post中身:'.print_r($_POST,true));

      $email = $_POST['email'];
      $pass = $_POST['pass'];
      $login_save_flg = (!empty($_POST['login_save'])) ? true:false;

      validEmpty($email,'email');
      validMaxLen($email,'email');
      validEmail($email,'email');

      validEmpty($pass,'pass');
      validMinLen($pass,'pass');
      validMaxLen($pass,'pass');
      validHalf($pass,'pass');

      if(empty($err_msg)){
        delog('バリデーションおｋです');

        try {
          $dbh = dbConnect();
          $sql = 'SELECT pass,id FROM users WHERE email=:email AND delete_flg=0';
          $data = array(':email'=>$email);
          $stmt = queryPost($dbh,$sql,$data);
          $result = $stmt->fetch(PDO::FETCH_ASSOC);

          delog('クエリ結果の中身:'.print_r($result,true));

          if(!empty($result) && password_verify($pass,array_shift($result))){
            delog('パスワードがマッチしました');

            $sessionLimit = 60*60;
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = ($login_save_flg) ? $sessionLimit*24*30 : $sessionLimit;
            $_SESSION['user_id'] = array_shift($result);

            delog('新規ログイン時のセッション変数：'.print_r($_SESSION,true));
            header("Location:forum.php");

          }else{
            delog('パスが不一致..');
          }
        }catch(Exception $e){
          error_log('エラー発生：'.$e->getMessage());
        }
      }

    }else{
      delog('post無し'.print_r($_POST,true));
    }
?>

 <div id="main">
   <section class="form-section">
     <h1>ログイン画面</h1>

       <form class="form-group" action="" method="post">
         <div class="form-group">
           <label>
             Email<br>
             <input type="text" name="email" value="<?php echo inputHold('email');?>">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['email'])) echo $err_msg['email'];?>
         </div>

         <div class="form-group">
           <label>
             パスワード<br>
             <input type="password" name="pass" value="<?php echo inputHold('pass');?>">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?>
         </div>

         <div class="form-group">
           <label>
             <input type="checkbox" name="login_save">
             ログインを維持
           </label>
         </div>

         <input type="submit" name="submit" value="送信">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
