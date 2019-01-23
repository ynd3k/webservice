<?php

require('function.php');
delog('##################');
delog('ユーザー登録画面');

require('head.php');
require('header.php');

if(!empty($_POST)){
  delog('post中身:'.print_r($_POST));

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $repass = $_POST['repass'];

  validEmpty($email,'email');
  validMaxLen($email,'email');
  validEmail($email,'email');
  validEmailDup($email,'email');

  validEmpty($pass,'pass');
  validMinLen($pass,'pass');
  validMaxLen($pass,'pass');
  validHalf($pass,'pass');

  validEmpty($repass,'repass');
  validMatch($pass,$repass,'repass');

  if(empty($err_msg)){
    delog('バリデーションおｋです');
    try{
      $dbh = dbConnect();
      $sql = 'INSERT INTO users (name,email,pass,create_date,zip,pic1) VALUES (:name,:email,:pass,:create_date,:zip,:pic1)';
      $data = array(':name'=>'',':email'=>$email,':pass'=>password_hash($pass,PASSWORD_DEFAULT),':create_date'=>date('Y-m-d H:i:s'),':zip'=>0,':pic1'=>'');
      $stmt = queryPost($dbh,$sql,$data);

      //=====================
      //セッションにログイン時間とタイムリミットとユーザIDを保存
      //=====================
      $sessionLimit = 60 * 60;
      $_SESSION['login_date'] = time();
      $_SESSION['login_limit'] = $sessionLimit;
      $_SESSION['user_id'] = $dbh->lastInsertId();

      delog('新規登録したセッション変数の中身:'.print_r($_SESSION,true));

      header("Location:forum.php");
    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
    }
  }
}
?>



  <div id="main">
    <section class="form-section">
      <h1>ユーザー登録</h1>

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
              パスワード再入力<br>
              <input type="password" name="repass" value="<?php echo inputHold('repass');?>">
            </label>
          </div>
          <div class="area-msg">
            <?php if(!empty($err_msg['repass'])) echo $err_msg['repass'];?>
          </div>

          <input type="submit" name="submit" value="送信">
        </form>

    </section>
  </div>















  <?php require('footer.php'); ?>
