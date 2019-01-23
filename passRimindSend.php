
<?php
  require('function.php');
  delog('#######');
  delog('パスワード再発行');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');


  if(!empty($_POST)){
    $email = $_POST['email'];
    $dbUserData = getUserData($_SESSION['user_id']);
    //if($email !== $dbUserData[0]['email']){
      //global $err_msg;
      //$err_msg['email'] = 'Emailが違います';
    //}else{
      //emailがあっていれば
      $_SESSION['email'] = $email;
      $_SESSION['auth_key'] = makeRandKey();
      $_SESSION['auth_limit'] = time() + (60*30);


      $username = $dbUserData[0]['name'];
      $from = '@gmail.com';
      $to = '';
      $subject = 'パスワード再発行認証';
      $comment = <<<EOT
{$username} さん
パスワード再発行認証キー入力してね
認証キー：{$_SESSION['auth_key']}
認証ページ： http://localhost:8888/forum/passRimindRecieve.php
EOT;
      sendMail($from,$to,$subject,$comment);
    //}
  }

?>

 <div id="main">

<?php require('link.php'); ?>
   <section class="form-section">
     <h1>パスワード再発行</h1>

       <form class="form-group" action="" method="post">
         <div class="form-group">
           <label>
             Email入力<br>
             <input type="text" name="email" value="">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['email'])) echo $err_msg['email'];?>
         </div>


         <input type="submit" name="submit" value="送信">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
