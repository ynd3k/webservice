
<?php
  require('function.php');
  delog('#######');
  delog('画像UPページ');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');


  $dbUserData = getUserDataNeo($_SESSION['user_id']);

  if(!empty($_POST)){
    delog('POST画像うｐページ'.print_r($_POST,true));
    delog('FILEs画像うｐページ'.print_r($_FILES,true));


    $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';
    $pic1 = (empty($pic1) && !empty($dbUserData['pic1'])) ? $dbUserData['pic1'] : $pic1;

    if(!empty($pic1)){
    try{
      $dbh = dbConnect();
      $sql = 'UPDATE users SET pic1=:pic1 WHERE id=:id';
      $data = array(':pic1'=>$pic1,':id'=>$_SESSION['user_id']);
      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        $_SESSION['msg-success'] = SUC02;
      }
    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
    }
}//end if(!empty($pic1))


  }



?>

<p id="js-show-msg" style="display:none;" class="msg-slide"><?php echo getSessionFlash();?></p>

 <div id="main">

   <?php require('link.php'); ?>
   <section class="form-section">
     <h1>画像アップロード</h1>

       <form class="form-group" action="" method="post" enctype="multipart/form-data">

           <div class="form-group">
             画像
             <label class="area-drop">
               <input type="file" name="pic1" class="input-file">
               <img src="" alt="" class="prev-img">
             </label>
           </div>

         <div class="area-msg">
           <?php if(!empty($err_msg['pic1'])) echo $err_msg['pic1'];?>
         </div>



         <input type="submit" name="submit" value="送信">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
