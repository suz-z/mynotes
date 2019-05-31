<?php 


require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　ノートページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//var_dump($_SESSION['msg_success']);

//setting.phpの戻るボタン機能に関するセッション処理
$_SESSION['referer'] = '';

//直近5件のメモデータを取得
$searchMemoData = getSearchMemoData($_SESSION['user_id'], '', '', '', 'new', 0, 5);


//登録されているカテゴリーを取得
$cateData = getCateData($_SESSION['user_id']);

//検索ボタンおされたらsearch.phpに移動
if(!empty($_GET)) {
  debug('検索結果一覧ページに遷移します。');
  header("Location:".str_replace('note.php', 'search.php', $_SERVER["REQUEST_URI"]));
}

//カテゴリの変更ボタンを押された時の処理
if(!empty($_POST['cate-edit'])) {
  debug('カテゴリ変更ボタンが押されました。');
  $_SESSION['acd_cate'] = 'checked';
  header("Location:setting.php");
}

//同一ページ更新時のメッセージ表示につかうセッション処理
//（メモ保存時、削除時のメッセージ。getSessionFlash使うとうまくいかない）
if(!empty($_SESSION['msg_success_selfpage'])) {
  $_SESSION['msg_success'] = getSessionFlash('msg_success_selfpage');
}

//メモ欄に入力、送信されたとき
if(!empty($_POST['write'])) {
  debug('メモがPOST送信されました。');
//  debug('POST情報:'.print_r($_POST,true));
  
  $newMemo = (!empty($_POST['new-memo'])) ? $_POST['new-memo'] : '';
  $cate = $_POST['category'];
  
  if(!empty($_POST['tag'])) {
    $tags = implode(',', $_POST['tag']);
  }
  
  //未入力チェック
  validRequired($newMemo,'new-memo');
  //最大文字数チェック
  validMaxLen($newMemo,'new-memo', 1000);
  
  if(empty($err_msg)) {
    debug('バリデーションOKです。');
    
    //例外処理
    try {
      $dbh = dbConnect();
      debug('DB新規登録です。');
      $sql = 'INSERT INTO memo (content, user_id, user_cate_no, tags, create_date) VALUES (:content, :u_id, :u_c_no, :tags, :create_date)';
      $data = array(
        ':content' => $newMemo,
        ':u_id' => $_SESSION['user_id'],
        ':u_c_no' => $cate,
        ':tags' => $tags,
        ':create_date' => date('Y-m-d H:i:s')
      );
      debug('SQL:'.$sql);
      debug('登録データ:'.print_r($data,true));
      $stmt = queryPost($dbh, $sql, $data);
      
      //クエリ成功の場合
      if($stmt) {
        debug('クエリ成功。最近のメモデータを更新します。');
        $_SESSION['msg_success_selfpage'] = SUC04;
        // $_SESSION['msg_success'] = SUC04
        $searchMemoData = getSearchMemoData($_SESSION['user_id'], '', '', '', 'new', 0, 5);
        header('Location:'.$_SERVER['PHP_SELF']); //自分自身に遷移。リロードしても二重送信されない

      }
      
    } catch(Exception $e) {
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

//memoFrame.phpに移動したらうまくいかず
//削除ボタンが押された時
if(!empty($_POST['delete'])) {
  debug('削除ボタンがおされました。');
  deleteData($_POST['memo_id']);
}
//編集ボタンを押された時
if(!empty($_POST['edit'])) {
  debug('編集ボタンが押されました。');
  $memoId = $_POST['memo_id'];
  debug('編集されるメモID:'.$_POST['memo_id']);
  $_SESSION['search_param'] = $_SERVER['REQUEST_URI'];//あとからこのページに戻ってくるために
  header("Location:edit.php?memo_id=".$memoId);
}

$siteTitle = 'ノート';
require('head.php');

?>


<body>
  <?php require('header.php'); ?>
  <main class="content site-width">
    <div class="main-content">
      <!--メインコンテンツ-->
      <section class="main-section">
        <h2 class="section-title"><i class="fas fa-pencil-alt"></i>メモを書く</h2>
        <form action="" method="post" name="new-memo-form" class="local-form">
          <div class="msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          </div>
          <textarea name="new-memo" class="write-text" id="js-write" required><?php if(!empty($newMemo)) echo $newMemo; ?></textarea>
          <div class="char-limit right" id="js-write-msg">(1000文字以内)</div>
          <div class="msg">
            <?php if(!empty($err_msg['new-memo'])) echo $err_msg['new-memo'] ?>
          </div>
          <div class="memo-regist">
            <div class="memo-regist-option">
              カテゴリ選択：
              <select name="category" id="" class="select-category write">
                <?php foreach($cateData as $key => $val): ?>
<!-- TODO カテゴリ名とタグの入力保持-->
                <option value="<?php echo $key; ?>">
                  <?php echo sanitize($val['category_name']); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="memo-regist-option memo-regist-option-tag">
              タグ：
              <?php for($i = 1; $i <=3; $i++){ ?>
                <label class="tag-select">
                  <input type="checkbox" name="tag[]" value=<?php echo $i ?> class="tag-select-box">
                  <div class="tag tag<?php echo $i ?>"></div>
                </label>
              <?php } ?>
            </div>
            <div class="memo-regist-option">
              <input type="submit" name="write" value="書き込む" class="btn btn-write">
            </div>
          </div>
        </form>
        <form action="" method="post">
          ※カテゴリの変更はこちら：<input type="submit" name="cate-edit" value="カテゴリ変更" class="btn-cate-edit">
        </form>
        
      </section>
      <section class="main-section">
        <h2 class="section-title"><i class="far fa-copy"></i>最近のメモ</h2>
        <div class="msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </div>
        <?php if(empty($searchMemoData['total'])) { ?>
          <br>
          <p>登録されているメモはありません。</p>
        <?php } else { ?>
        <?php require('memoFrame.php'); ?>
        <br>
        <p class="right"><a href="search.php"><i class="fas fa-arrow-right"></i>すべてのメモを見る</a></p>
        <?php } ?>
      </section>
      <div id="js-msg" class="suc-msg">
        <?php echo getSessionFlash('msg_success'); ?>
      </div>

    </div>

    <?php require('sidebar.php'); ?>

  </main>
  <?php require('footer.php'); ?>
