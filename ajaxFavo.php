<?php
require('function.php');
delog('####Ajax####');
delogStart();

if(isset($_POST['messageId']) && isset($_SESSION['user_id']) && isLogin()){
  delog('post送信があります');
  $m_id = $_POST['messageId'];
  delog('メッセージID:'.$m_id);

  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite WHERE msg_id=:m_id AND favo_user_id=:u_id';
    $data = array(':m_id'=>$m_id,':u_id'=>$_SESSION['user_id']);
    $stmt = queryPost($dbh,$sql,$data);
    $result = $stmt->rowCount();
    delog($result);
    if(!empty($result)){
      $sql = 'DELETE FROM favorite WHERE msg_id=:m_id AND favo_user_id=:u_id';
      $data = array(':m_id'=>$m_id,':u_id'=>$_SESSION['user_id']);
      $stmt = queryPost($dbh,$sql,$data);
    }else{
      $sql = 'INSERT INTO favorite (msg_id,favo_user_id,create_date) VALUES (:m_id,:u_id,:date)';
      $data = array(':m_id'=>$m_id,':u_id'=>$_SESSION['user_id'],':date'=>date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh,$sql,$data);
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
delog('## ajax終了###');
 ?>
