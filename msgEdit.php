
<?php
  require('function.php');
  delog('#######');
  delog('メッセージ編集');
  delogStart();

  require('auth.php');

  require('head.php');
  require('header.php');

$msg_id = $_GET['m_id'];
$p = (!empty($_GET['p'])) ? $_GET['p'] : 1;
$search = (!empty($_GET['search'])) ? $_GET['search'] : '';
$tag = (!empty($_GET['tag'])) ? $_GET['tag'] : 0;


if(!empty($_POST)){

  delog('msg編集でpostされている');
  delog($_POST);
  $msg = $_POST['msg'];
  $postTag = $_POST['tag'];

  $dbMessageData = getMsgOne($msg_id);

  $dbTag_idOne = $dbMessageData['tag_id'];
  $dbMsgOne = $dbMessageData['msg'];

  msgEdit($msg,$dbMsgOne,'msg',$msg_id,$postTag,$dbTag_idOne,$p,$search,$tag);

  $msgOfInput = $msg;

  

}else{
  delog('msg編集でpost無し');
  $dbMessageData = getMsgOne($msg_id);
  $dbTag_idOne = $dbMessageData['tag_id'];
  $dbMsgOne = $dbMessageData['msg'];

  $msgOfInput = $dbMsgOne;

}


?>


 <div id="main">

<?php require('link.php'); ?>

<?php

$dbTagData = getTag();
?>
   <section class="form-section-msg-edit">
     <h1>メッセージ編集</h1>

       <form class="form-group" action="" method="post">
         <div class="form-group">
           <label>
             メッセージ編集<br>
             <input type="text" name="msg" value="<?php echo $msgOfInput;?>">
           </label>
         </div>
         <div class="area-msg">
           <?php if(!empty($err_msg['msg'])) echo $err_msg['msg'];?>
         </div>

         <div class="form-group">
           <select class="" name="tag">
             <option value="0">タグを選択してください</option>
             <?php foreach ($dbTagData as $key => $val):?>
               <option value="<?php echo $val['id'];?>"
                 <?php if(!empty($_POST)){
                   if($val['id'] == $postTag) echo 'selected';
                 }else{
                   if($val['id'] == $dbTag_idOne) echo 'selected';
                 }
                 ?>><?php echo $val['name'];?></option>
             <?php endforeach;?>
           </select>
         </div>

         <input type="submit" name="submit" value="編集">
       </form>

   </section>
 </div>




  <?php require('footer.php'); ?>
