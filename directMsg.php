<?php
require('function.php');

delog('#######');
delog('ダイレクトメッセージページ');
delogStart();

require('auth.php');
require('head.php');
require('header.php');

//$msg = $_POST['msg'];
$msg = (!empty($_POST['msg'])) ? $_POST['msg'] : '';

$myId = $_SESSION['user_id'];
$otherId = $_GET['u_id'];

try{
  $dbh = dbConnect();
  $sql = 'SELECT * FROM dm_board WHERE my_id=:my_id AND other_id=:other_id OR my_id=:other_id AND other_id=:my_id';
  $data = array(':my_id'=>$myId,':other_id'=>$otherId);
  $stmt = queryPost($dbh,$sql,$data);
  $db_dm_boardData = $stmt->fetch(PDO::FETCH_ASSOC);
  $board_id = $db_dm_boardData['id'];

  if($stmt->rowCount()){
     delog('すでにdm_boardテーブルに登録されてます');
  }else{
    delog('dm_boardテーブルに登録されていません');
    $sql = 'INSERT into dm_board (my_id,other_id,create_date) VALUES (:my_id,:other_id,:create_date)';
    $data = array(':my_id'=>$myId,':other_id'=>$otherId,':create_date'=>date('Y-m-d H:i:s'));
    $stmt = queryPost($dbh,$sql,$data);
  }

}catch(Exception $e){
  error_log('エラー発生：'.$e->getMessage());
}


if(!empty($_POST)){
  delog('postされてるDMページ');
  delog($_POST);
  //$msg = $_POST['msg'];
  //$myId = $_SESSION['user_id'];
  //$otherId = $_GET['u_id'];
  $dbOtherData = getUserDataNeo($otherId);


  validMaxLen($msg,'msg');
  validEmpty($msg,'msg');

  if(empty($err_msg)){
    try{
      $dbh = dbConnect();
      $sql = 'INSERT into dm_msg (to_user,from_user,msg,create_date,board_id) VALUES (:to_user,:from_user,:msg,:create_date,:board_id)';
      $data = array(':to_user'=>$otherId,':from_user'=>$myId,':msg'=>$msg,':create_date'=>date('Y-m-d H:i:s'),':board_id'=>$db_dm_boardData['id']);
      $stmt = queryPost($dbh,$sql,$data);

      $db_dm_msgData = getDM($board_id);

    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
    }

  }

}else{
  delog('postされてないDMページ');


  $dbOtherData = getUserDataNeo($otherId);

  $db_dm_msgData = getDM($board_id);



}

 ?>

<p id="js-show-msg" style="display:none;" class="msg-slide"><?php echo getSessionFlash();?></p>

<div id="main">
<?php require('link.php'); ?>

  <div id="forum-wrapper">

    <section id="forum-main">
      <p style="font-size:13px;">ユーザーID:
      <?php echo getUserDataNeo($_SESSION['user_id'])['id'];
            echo '　名前:'.getUserDataNeo($_SESSION['user_id'])['name'];
      ?></p>
      <form method="post" action="">
        <label>
          <input type="text" name="msg" value="" placeholder="<?php echo $dbOtherData['name'];?> さんへのメッセージを入力してください">
          <input type="submit" name="submit" value="送信">
        </label>
      </form>
      <div class="area-msg">
        <?php if(!empty($err_msg['msg'])) echo $err_msg['msg'];?>
      </div>

    <div>
      <!---------------------->

      <?php foreach ($db_dm_msgData as $key => $val): ?>

        <?php if($val['from_user'] == $myId):?>
        <div style="overflow:hidden;">
        <div class="dm-my-msg">
          <img src="<?php echo get($val['from_user'])['pic1'];?>">
          <span style="background:#94d18b;"><?php echo get($val['from_user'])['name'];?></span>
          <?php echo '| '.$val['msg'].'|';?>
          <?php echo $val['create_date'];?>
        </div>
        </div>
        <div class="message-underline"></div>
      <?php endif;?>

      <?php if($val['from_user'] != $myId):?>
      <div style="overflow:hidden;">
      <div class="dm-other-msg">
        <img src="<?php echo get($val['from_user'])['pic1'];?>">
        <span style="background:#c37bb5;"><?php echo get($val['from_user'])['name'];?></span>
        <?php echo '| '.$val['msg'].'|';?>
        <?php echo $val['create_date'];?>
      </div>
      </div>
      <div class="message-underline"></div>
    <?php endif;?>
      <?php endforeach;?>
    </div>


   </section>


    <section id="forum-sidebar">
      <div class="sidebar-logout"><a href="logout.php">ログアウト</a></div>
    </section>


  </div>


</div>






 <?php require('footer.php'); ?>
