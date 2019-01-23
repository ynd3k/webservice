
<?php
  require('function.php');
  delog('#######');
  delog('パスワード変更');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');


  if(!empty($_POST)){
    delog('パス変更のPosT中身');
    delog($_POST);
    $pass_old = $_POST['pass-old'];
    $pass_new = $_POST['pass-new'];
    $pass_new_re = $_POST['pass-new-re'];
    $dbUserData = getUserData($_SESSION['user_id']);

    validOldPassMatch($pass_old,'pass-old');
    validPassOldAndNew($pass_old,$pass_new,'pass-old');

    validPass($pass_new,'pass-new');
    validMatch($pass_new,$pass_new_re,'pass-new-re');


    if(empty($err_msg)){
      delog('ok');
      try{
        $dbh = dbConnect();
        $sql = 'UPDATE users SET pass=:pass WHERE id=:id';
        $data = array(':pass'=>password_hash($pass_new,PASSWORD_DEFAULT),':id'=>$_SESSION['user_id']);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
          $_SESSION['msg-success'] = 'パスワードの変更が完了しました';

          $username = ($dbUserData[0]['name']) ? $dbUserData[0]['name'] : '名無し';
          $from = '@.com';
          $to = 'a';
          $subject = 'パスワード変更通知';
          $comment = <<<EOT
{$username}　さん
パスワードが変更されました。身に覚えがない場合は下記までお問い合わせくださいませ。

//////////////////////
URL http://testtest.c
E-mail info@aaaa.com
//////////////////////
EOT;

          sendMail($from,$to,$subject,$comment);

          header("Location:forum.php");
        }
      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }
    }else{
      delog('no');
    }


  }

?>

 <div id="main">

  <?php require('link.php'); ?>
   <section class="form-section">
     <h1>パスワード変更</h1>

       <form class="form-group" action="" method="post">
         <div class="form-group">
           <label>
             古いパスワード<br>
             <input type="password" name="pass-old" value="">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['pass-old'])) echo $err_msg['pass-old'];?>
         </div>

         <div class="form-group">
           <label>
             新しいパスワード<br>
             <input type="password" name="pass-new" value="">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['pass-new'])) echo $err_msg['pass-new'];?>
         </div>

         <div class="form-group">
           <label>
             新しいパスワード(再入力)<br>
             <input type="password" name="pass-new-re" value="">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['pass-new-re'])) echo $err_msg['pass-new-re'];?>
         </div>

         <input type="submit" name="submit" value="変更">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
