<?php

ini_set('log_errors','on');
ini_set('error_log','php.log') ;


define('MSG01','入力必須です');
define('MSG02','255文字以内で入力してください');
define('MSG03','emailの形式で入力してください');
define('MSG04','6文字以上で入力してください');
define('MSG05','半角英数字で入力してください');
define('MSG06','パスと再入力が一致しません');
define('MSG07','emailが重複しています');
define('MSG08','エラーが発生しました');
define('MSG09','郵便番号の形式が違います');
define('MSG10','古いパスワードが違います');
define('MSG11','古いパスワードが新しいパスワードと同じなので違うのにしてください');
define('SUC01','メッセージを編集しました');
define('SUC02','画像をアップロードしました');








session_save_path("/var/tmp/");
ini_set('session.gc_maxlifetime',60*60*24*30);
ini_set('session.cookie_lifetime',60*60*24*30);

session_start();
session_regenerate_id();

$err_msg = array();
$delog_flg = true;

function delog($str){
  global $delog_flg;
  if($delog_flg){
    error_log('デログ： '.print_r($str,true));
  }
}

function delogStart(){
  global $delog_flg;
  if($delog_flg){
    error_log('デログ: セッション変数の中身=>'.print_r($_SESSION,true));
  }
}

function validEmpty($str,$key){
  global $err_msg;
  if(empty($str)){
    $err_msg[$key] = MSG01;
  }
}

function validMaxLen($str,$key,$max=255){
  global $err_msg;
  if(mb_strlen($str) > $max){
    $err_msg[$key] = MSG02;
  }
}

function validMinLen($str,$key){
  global $err_msg;
  if(mb_strlen($str) < 6){
    $err_msg[$key] = MSG04;
  }
}

function validEmail($str,$key){
  global $err_msg;
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    $err_msg[$key] = MSG03;
  }
}

function validHalf($str,$key){
  global $err_msg;
  if(!preg_match("/^[a-zA-Z0-9]+$/",$str)){
    $err_msg[$key] = MSG05;
  }
}

function validMatch($str1,$str2,$key){
  global $err_msg;
  if($str1 !== $str2){
    $err_msg[$key] = MSG06;
  }
}

function validPass($str,$key){
  validEmpty($str,$key);
  validHalf($str,$key);
  validMaxLen($str,$key);
  validMinLen($str,$key);
}

function dbConnect(){
  $dsn = 'mysql:dbname=keyakizaka;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY =>true,
  );
  $dbh = new PDO($dsn,$user,$password,$options);
  return $dbh;
}

function queryPost($dbh,$sql,$data){
  $stmt = $dbh->prepare($sql);

  if(!$stmt->execute($data)){
    delog('クエリ失敗しました');
    global $err_msg;
    $err_msg['common'] = MSG08;
  }else {
    delog('クエリ成功しました');
    return $stmt;
  }
}

function validEmailDup($email,$key){
  global $err_msg;

  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email=:email AND delete_flg=0';
    $data = array(':email'=>$email);
    $stmt = queryPost($dbh,$sql,$data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    if(  !empty( array_shift($result) )  ){
      $err_msg[$key] = MSG07;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG08;
  }
}

function inputHold($key){
  if(!empty($_POST[$key])){
    return sanitize($_POST[$key]);
  }
}

function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

function getMessageList($currentMinNum=1,$tag,$span){
  try{
    $dbh = dbConnect();
    //総レコード数、総ページ数取得
    $sql = 'SELECT msg_id FROM message';
    $data = array();

    if(!empty($tag)){
      $sql .= ' WHERE tag_id=:tagid';
      $data = array(':tagid'=>$tag);
    }
    $stmt = queryPost($dbh,$sql,$data);
    $rst['total_recode'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total_recode']/$span);


    //ページング用
    $sql ='SELECT * FROM message';
    $data = array();

    if(!empty($tag)){
      $sql = 'SELECT * FROM message WHERE tag_id=:tagid';
      $data = array(':tagid'=>$tag);
    }
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;

    delog('sql文: '.$sql);
    $stmt = queryPost($dbh,$sql,$data);

    if(!$stmt){
      delog('no statement object');
      return false;
    }else{
      delog('in! sitatement object');
      delog($stmt);
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function searchMessage($currentMinNum,$search_msg,$span){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT msg,create_date FROM message WHERE msg=:search_msg';
    $data = array(':search_msg'=>$search_msg);
    $stmt = queryPost($dbh,$sql,$data);
    $rst['total_recode'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total_recode'] / $span);

    $sql = 'SELECT msg,create_date,msg_id FROM message WHERE msg=:search_msg';
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array(':search_msg'=>$search_msg);
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function getFavoList($currentMinNum,$favo_user_id,$span){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT msg,message.create_date,favorite.msg_id FROM message INNER JOIN favorite ON message.msg_id=favorite.msg_id WHERE favorite.favo_user_id=:favo_u_id';
    $data = array(':favo_u_id'=>$favo_user_id);
    $stmt = queryPost($dbh,$sql,$data);
    $rst['total_recode'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total_recode'] / $span);

    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum ;
    $stmt = queryPost($dbh,$sql,$data);
    if($stmt){
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function paging($currentPageNum,$totalPageNum,$pageColNum){
  if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPage = $currentPageNum -4;
    $maxPage = $totalPageNum;
    $page['min']=$minPage;
    $page['max'] = $maxPage;
    return $page;
  }elseif($currentPageNum == $totalPageNum -1 && $totalPageNum >= $pageColNum){
    $minPage = $currentPageNum -3;
    $maxPage = $currentPageNum +1;
    $page['min']=$minPage;
    $page['max'] = $maxPage;
    return $page;
  }elseif($currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPage = 1;
    $maxPage = $pageColNum;
    $page['min']=$minPage;
    $page['max'] = $maxPage;
    return $page;
  }elseif($currentPageNum == 1 && $totalPageNum >= $pageColNum){
    $minPage = 1;
    $maxPage = $pageColNum;
    $page['min']=$minPage;
    $page['max'] = $maxPage;
    return $page;
  }elseif($totalPageNum < $pageColNum){
    $minPage = 1;
    $maxPage = $totalPageNum;
    $page['min']=$minPage;
    $page['max'] = $maxPage;
    return $page;
  }else{
    $minPage = $currentPageNum -2;
    $maxPage = $currentPageNum +2;
    $page['min']=$minPage;
    $page['max'] = $maxPage;
    return $page;
  }
}


function getTag(){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT id,name FROM tag';
    $data = array();
    $stmt = queryPost($dbh,$sql,$data);
    $rst = $stmt->fetchAll();
    return $rst;
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function pagingLink($key){
  if(isset($_GET[$key])){
    return '&'.$key.'='.$_GET[$key];
  }else{
    return '';
  }
}

function isLogin(){
  if(!empty($_SESSION['login_date'])){

    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) <time() ){
      delog('有効期限おーばー！ isLogin関数によりセッション削除を行います');
      session_destroy();
      return false;
    }else{
      delog('有効期限いないだよ');
      return true;
    }
  }else{
    delog('未ログインだよ');
    return false;
  }
}

function isFavo($u_id,$m_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite WHERE msg_id=:m_id AND favo_user_id=:u_id';
    $data = array(':m_id'=>$m_id,':u_id'=>$u_id);
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt->rowCount()){
      //delog('お気に入りです！');
      return true;
    }else{
      //delog('お気に入りじゃない・・');
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function getUserData($user_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id=:u_id';
    $data = array(':u_id'=>$user_id);
    $stmt = queryPost($dbh,$sql,$data);

    return $stmt->fetchAll();

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function getUserDataNeo($user_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id=:u_id';
    $data = array(':u_id'=>$user_id);
    $stmt = queryPost($dbh,$sql,$data);

    return $stmt->fetch(PDO::FETCH_ASSOC);

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function profInputHold($key){
  $value = getUserData($_SESSION['user_id']);

  if(!empty($_POST)){
    //POST送信されてたら
    if($_POST[$key] !== $value[0][$key] && !empty($_POST[$key])){
      //delog('POSTされていて、DBとPOSTの値が違う');
      return $_POST[$key];
    }else{

      //delog('POSTのされてるが変更がない');
      if(empty($_POST['zip'])){
        $value[0]['zip'] = '';
        return $value[0][$key];
      }else{
        return $value[0][$key];
      }

    }

  }else{
    //POST送信されてない（初めて開いた時）DB情報を表示
    //zipがデフォルトの０だったら空文字にする
    if(empty($value[0]['zip'])){
      $value[0]['zip'] = '';
      return $value[0][$key];
    }else{
      return $value[0][$key];
    }
  }

}

function validZip($str,$key){
  if(!preg_match("/\d{7}$/",$str)){
    global $err_msg;
    $err_msg[$key] = MSG09;
  }
}

function validOldPassMatch($pass_old,$key){

    $dbUserData = getUserData($_SESSION['user_id']);
    if(password_verify($pass_old, $dbUserData[0]['pass'])){
      delog('パス変更で古いパスとDBのパスが一致！');
    }else{
      delog('パス変更で古いパスとDBのパスが一致しません');
      global $err_msg;
      $err_msg[$key] = MSG10;
    }

}

function validPassOldAndNew($pass_old,$pass_new,$key){
  $dbUserData = getUserData($_SESSION['user_id']);
  if($pass_old === $pass_new){
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}

function getSessionFlash(){
  if(!empty($_SESSION['msg-success'])){
    $data = $_SESSION['msg-success'];
    $_SESSION['msg-success'] = "";
    return $data;
  }
}

function sendMail($from,$to,$subject,$comment){
  if(!empty($to) && !empty($subject) && $comment){
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $result = mb_send_mail($to,$subject,$comment,"From: ".$from);
    if($result){
      delog('メール送信しました');
    }else{
      delog('メール送信に失敗しました・・');
    }
  }
}

function makeRandKey($length=8){
  $chars = 'abcdefghijkemlopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for($i=0; $i<$length; $i++){
    $str .= $chars[mt_rand(0,61)];
  }
  return $str;
}

function getMsgOne($msg_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT msg,tag_id,pic1 FROM message WHERE msg_id=:m_id';
    $data = array(':m_id'=>$msg_id);
    $stmt = queryPost($dbh,$sql,$data);

    return  $stmt->fetch(PDO::FETCH_ASSOC);


  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function msgEdit($postMsg,$dbMsg,$key,$msg_id,$postTag,$dbTag,$p,$search,$tag){
  //メッセージ編集されててタグはデフォルト０
  if($postMsg !== $dbMsg && $postTag === $dbTag){
    delog('メッセージ編集されててタグはそのまま');

    validEmpty($postMsg,$key);
    validMaxLen($postMsg,$key);

    if(empty($err_msg)){
      try{
        $dbh = dbConnect();
        $sql = 'UPDATE message SET msg=:msg WHERE msg_id=:m_id';
        $data = array(':msg'=>$postMsg,':m_id'=>$msg_id);
        $stmt = queryPost($dbh,$sql,$data);

        $_SESSION['msg-success'] = SUC01;

        header("Location:forum.php?p=".$p."&search=".$search."&tag=".$tag);

      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }
    }

  }elseif($postMsg === $dbMsg && $postTag !== $dbTag){
    delog('メッセージ編集されてなくてタグだけ変わってた場合');
    try{
      $dbh = dbConnect();
      $sql = 'UPDATE message SET tag_id=:t_id WHERE msg_id=:m_id';
      $data = array(':t_id'=>$postTag,':m_id'=>$msg_id);
      $stmt = queryPost($dbh,$sql,$data);
      $_SESSION['msg-success'] = SUC01;
      header("Location:forum.php?p=".$p."&search=".$search."&tag=".$tag);


    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
    }

  }elseif($postMsg !== $dbMsg && $postTag !== $dbTag){
    delog('メッセージもタグも変わっている');
    validEmpty($postMsg,$key);
    validMaxLen($postMsg,$key);

    if(empty($err_msg)){
      try{
        $dbh = dbConnect();
        $sql = 'UPDATE message SET msg=:msg WHERE msg_id=:m_id';
        $data = array(':msg'=>$postMsg,':m_id'=>$msg_id);
        $stmt = queryPost($dbh,$sql,$data);

        $sql = 'UPDATE message SET tag_id=:t_id WHERE msg_id=:m_id';
        $data = array(':t_id'=>$postTag,':m_id'=>$msg_id);
        $stmt = queryPost($dbh,$sql,$data);
        $_SESSION['msg-success'] = SUC01;
        header("Location:forum.php?p=".$p."&search=".$search."&tag=".$tag);


      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }
    }

  }else{
    delog('何も変わってない');
  }
}

function s($msg_id){
  try{

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function uploadImg($file,$key){
  delog($file);

  if(isset($file['error']) && is_int($file['error'])){
    try{
      switch ($file['error']){
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
            throw new RuntimeException('その他のエラーが発生しました');
      }

      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG],true)){
        throw new RuntimeException('画像形式が未対応です');
      }

      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if(!move_uploaded_file($file['tmp_name'],$path)){
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }

      chmod($path,0644);

      delog('正常にアップロードされました');
      delog($path);
      return $path;

    }catch(RuntimeException $e){
      delog($e->getMessage());
    }
  }
}

function msgIconPic1($pic1){
  if(!empty($pic1)){
    echo '<img src='.$pic1.'>';
  }
}

function getNameAndPic1($msg_user_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT name,pic1 FROM users WHERE id=:msg_user_id';
    $data = array(':msg_user_id'=>$msg_user_id);
    $stmt = queryPost($dbh,$sql,$data);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  }catch(Exception $e){
    error_log($e->getMessage());
  }
}

function getDM($board_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM dm_msg WHERE board_id=:b_id';
    $data = array(':b_id'=>$board_id);
    $stmt = queryPost($dbh,$sql,$data);
    $result = $stmt->fetchAll();
    return $result;
  }catch(Exception $e){
    error_log($e->getMessage());
  }
}

function get($from_user){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id=:f_user';
    $data = array(':f_user'=>$from_user);
    $stmt = queryPost($dbh,$sql,$data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //delog($result);
    return $result;
  }catch(Exception $e){
    error_log($e->getMessage());
  }
}
?>
