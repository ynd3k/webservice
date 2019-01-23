<?php
require('function.php');

delog('#######');
delog('掲示板ページ');
delogStart();

require('auth.php');
require('head.php');
require('header.php');



  if(!empty($_POST) && empty($_POST['userId'])){
    delog('post:ok');
    delog(print_r($_POST,true));

    $msg = $_POST['msg'];

    validEmpty($msg,'msg');
    validMaxLen($msg,'msg');

    if(empty($err_msg)){
      delog('メッセージバリデーションおｋです');

      try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO message (msg,user_id,create_date,pic1) VALUES (:msg,:userid,:create_date,:pic1)';
        $data = array(':msg'=>$msg,':userid'=>$_SESSION['user_id'],':create_date'=>date('Y-m-d H:i:s'),':pic1'=>'');
        $stmt = queryPost($dbh,$sql,$data);


      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }

    }
  }else{
    delog('post:none');
    delog(print_r($_POST,true));
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
          <input type="text" name="msg" value="" placeholder="メッセージを入力してください">
          <input type="submit" name="submit" value="送信">
        </label>
      </form>


      <!--一覧表示テスト -->
    <?php
    //ページネーション
    $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;


    if(!preg_match('/^[0-9]+$/',$currentPageNum)){
      delog('GETパラメータに不正な値が入りました 掲示板トップに戻ります');
      header("Location:forum.php");
    }

    //表示件数
    $listSpan = 10;
    //現在の表示レコード先頭を算出（OFFSETの値）
    $currentMinNum = ($currentPageNum-1) * $listSpan;
    //
    $tag = (isset($_GET['tag'])) ? $_GET['tag'] : '';
    $dbTagData = getTag();

    if(!empty($_GET['search'])){
      $search = $_GET['search'];delog('asss!');
      $dbMessageData = searchMessage($currentMinNum,$search,$listSpan);

    }elseif(isset($_POST['userId'])){
      //お気に入り表示ajaxでポスト送信きたら
      $dbMessageData = getFavoList($currentMinNum,$_POST['userId'],$listSpan);
      delog('$_POST[userId]入ってる、お気に入り表示おされた');
    }else{
      $search = '';delog('as!');
      $dbMessageData = getMessageList($currentMinNum,$tag,$listSpan);
      $dbUserData = getUserDataNeo($_SESSION['user_id']);
    }


      //$currentPageNum 現在のページ数
      //$listSpan = 10; 表示件数
      $minPage = 0;//ページング表示項目のうち、最小のページ番号
      $maxPage = 0;//ページング表示項目のうち、最大でのページ番号
      $pageColNum = 5;//ページング表示項目数　最大で５こ
      $totalPageNum = $dbMessageData['total_page'];//総ページ数

      $page = paging($currentPageNum,$totalPageNum,$pageColNum);
    ?>

      <div class="search-title">
        <div class="search-left">
          <span>合計<?php echo sanitize($dbMessageData['total_recode']);?></span>件のメッセージが見つかりました
        </div>

        <div class="search-right">
          <span><?php echo $currentMinNum+1;?></span> - <span><?php echo $currentMinNum+$listSpan;?>
          </span>件 / <span><?php echo sanitize($dbMessageData['total_recode']);?></span>件中
        </div>
      </div>

      <div class="message-underline"></div>


      <div class="message-list">
        <?php foreach ($dbMessageData['data'] as $key => $val):?>
          <?php echo sanitize($val['create_date']);?>: 　<a href="msgEdit.php?m_id=<?php echo $val['msg_id'];?><?php echo pagingLink('search');?><?php echo pagingLink('tag');?><?php echo pagingLink('p');?>"><?php echo sanitize($val['msg']);?></a>
           <i class="far fa-heart js-click-favo <?php if(isFavo($_SESSION['user_id'],$val['msg_id'])) echo 'active';?>" data-messageid="<?php echo sanitize($val['msg_id']);?>"></i>


             <?php echo '　|';?>
             <img src="<?php echo getNameAndPic1($val['user_id'])['pic1'];?>">
             <a href="directMsg.php?u_id=<?php echo $val['user_id'];?>"><?php echo getNameAndPic1($val['user_id'])['name'];?></a>さん

          <div class="message-underline"></div>
        <?php endforeach;?>
      </div>

      <div class="pagenation-container">
        <a href="?p=1<?php echo pagingLink('search');?><?php echo pagingLink('tag');?>" class="pagenation-toplink"><?php if($currentPageNum != 1) echo '&lt';?></a>
        <?php for($i=$page['min']; $i<=$page['max']; $i++):?>
          <a href="?p=<?php echo $i;?><?php echo pagingLink('search');?><?php echo pagingLink('tag');?>"><li class="pagenation"><?php echo $i;?></li></a>
        <?php endfor;?>
        <a href="?p=<?php echo $totalPageNum;?><?php echo pagingLink('search');?><?php echo pagingLink('tag');?>"><?php if($currentPageNum != $totalPageNum) echo '&gt';?></a>
      </div>
      <!--一覧表示テスト -->

    </section>


    <section id="forum-sidebar">
      <div class="sidebar-logout"><a href="logout.php">ログアウト</a></div>

      <form class="forum-sidebar-form" action="" method="get">
        <span style="color:white;margin:10px 10px;">タグ</span>
        <div>
          <select name="tag">
            <option value="0">選択してください</option>

            <?php foreach($dbTagData as $key => $val):?>
            <option value="<?php echo $val['id'];?>"><?php echo sanitize($val['name']);?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div class="search-msg">
          <input type="text" name="search" value="" class="sm" placeholder="検索ワード">
        </div>
        <div class="search">
          <input type="submit" name="" value="検索">
        </div>
      </form>

      <div class="favo-sidebar">
        <h1 style="">お気に入り表示</h1>
        <span style="font-size:20px;"><i class="fas fa-heart favo-side-icon" data-userid="<?php echo $_SESSION['user_id'];?>"></i></span>
        <div><i class="fas fa-heart test" data-userid="<?php echo $_SESSION['user_id'];?>"></i></div>
      </div>

      <a href="profEdit.php">プロフィール編集</a>
    </section>

  </div>


</div>






 <?php require('footer.php'); ?>
