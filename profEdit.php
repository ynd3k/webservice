
<?php
  require('function.php');
  delog('#######');
  delog('ログイン画面★');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');

  if(!empty($_POST)){
    delog('プロフ編集画面 post されてるよ');
    delog($_FILES);
    $name = $_POST['name'];
    $zip = $_POST['zip'];
    $dbUserData = getUserData($_SESSION['user_id']);
    $dbName = $dbUserData[0]['name'];
    $dbZip = (!empty($dbUserData[0]['zip'])) ? $dbUserData[0]['zip'] : '';

    if($name !== $dbName){
      validEmpty($name,'name');
      validMaxLen($name,'name');
      if(empty($err_msg)){
        try{
          $dbh = dbConnect();
          $sql = 'UPDATE users SET name=:name WHERE id=:id';
          $data = array(':name'=>$name,':id'=>$_SESSION['user_id']);
          $stmt = queryPost($dbh,$sql,$data);
        }catch(Exception $e){
          error_log('エラー発生：'.$e->getMessage());
        }
      }
    }else{
      delog('いっち');
    }

    if($zip !== $dbZip){
      delog('ちがう');
      validZip($zip,'zip');
      if(empty($err_msg)){
        try{
          $dbh = dbConnect();
          $sql = 'UPDATE users SET zip=:zip WHERE id=:id';
          $data = array(':zip'=>$zip,':id'=>$_SESSION['user_id']);
          $stmt = queryPost($dbh,$sql,$data);
        }catch(Exception $e){
          error_log('エラー発生：'.$e->getMessage());
        }
      }
    }else{
      delog('いっちだ！');
    }

    $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';
    $pic1 = (empty($pic1) && !empty($dbUserData[0]['pic1'])) ? $dbUserData[0]['pic1'] : $pic1;

    $dbPic1 = $pic1;
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
    }
  }else{
    $dbUserData = getUserData($_SESSION['user_id']);
    $dbPic1 = $dbUserData[0]['pic1'];
  }
?>

 <div id="main">

   <?php require('link.php'); ?>
   <section class="form-section">
     <h1>プロフィール編集</h1>

       <form class="form-group" action="" method="post" enctype="multipart/form-data">
         <div class="form-group">
           <label>
             名前<br>
             <input type="text" name="name" value="<?php echo profInputHold('name');?>">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['name'])) echo $err_msg['name'];?>
         </div>

         <div class="form-group">
           <label>
             郵便番号<br>
             <input type="text" name="zip" value="<?php echo profInputHold('zip');?>">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['zip'])) echo $err_msg['zip'];?>
         </div>

         <div class="form-group">
           <label>
             ユーザー画像<br>
             <?php
             delog('afwafaw[@3[43fa]]');
              delog('$dbpic1のなかみ:'.$dbPic1);?>
             <img src="<?php if(!empty($dbPic1)) echo $dbPic1;?>" class="" width='150px'>
             <input type="file" name="pic1" class="">

           </label>
         </div>

         <input type="submit" name="submit" value="編集">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
