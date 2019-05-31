<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　編集ページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//メモIDのgetパラメータを取得
$memoId = (!empty($_GET['memo_id'])) ? $_GET['memo_id'] : '';
//DBから個別のメモデータを取得
$dbFormData = getMemoData($memoId);
$cateData = getCateData($_SESSION['user_id']);

//POST送信された場合（更新するボタンが押されたとき）
if(!empty($_POST)) {
  debug('更新するボタンが押されました。');
  $editMemo = $_POST['content'];
  $cate = $_POST['category'];
  
  if(!empty($_POST['tag'])) {
    $tags = implode(',', $_POST['tag']);
  }

  //バリデーションチェック
  validRequired($editMemo,'content');
  validMaxLen($editMemo,'content',2000);
  
  if(empty($err_msg)) {
    debug('バリデーションOKです。');
    
    //例外処理
    try {
      $dbh = dbConnect();
      debug('メモデータ更新です。');
      $sql = 'UPDATE memo SET content = :content, user_cate_no = :u_c_no, tags = :tags WHERE memo_id = :memo_id';
      $data = array(
        ':content' => $editMemo,
        ':u_c_no' => $cate,
        ':tags' => $tags,
        ':memo_id' => $memoId,
      );
      debug('SQL:'.$sql);
//      debug('登録データ:'.print_r($data,true));
      $stmt = queryPost($dbh, $sql, $data);
      
      //クエリ成功の場合
      if($stmt) {
        $_SESSION['msg_success'] = SUC01;
        debug('クエリ成功。メモデータを更新します。');
        //元のページに戻る（search_paramの値はmemoFrame.phpで入れてる）
        header("Location:".$_SESSION['search_param']);
      } else {
        debug('クエリに失敗しました。');
        $err_msg['common'] = MSG07;
      }
    } catch(Exception $e) {
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

$siteTitle = '編集';
require('head.php');

?>

<body>
  <?php require('header.php'); ?>
  <main class="content site-width">
    <!--メインコンテンツ-->
    <!--    <div class="main-content">-->
    <section class="main-section edit-section">
      <h2 class="section-title"><i class="fas fa-pencil-alt"></i>メモを編集する</h2>
      <form action="" method="post" class="local-form" id="">
        <div class="msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </div>
        <textarea name="content" class="write-text" id="js-write"><?php echo getFormData('content'); ?></textarea>
        <div class="char-limit right" id="js-write-msg">(1000文字以内)</div>
        <div class="msg">
          <?php if(!empty($err_msg['content'])) echo $err_msg['content'] ?>
        </div>
        <div class="memo-regist">
          <div class="memo-regist-option">
            カテゴリ選択：
            <select name="category" id="" class="select-category write">
              <?php foreach($cateData as $key => $val): ?>
<!-- TODO カテゴリ名とタグの入力保持-->
              <option value="<?php echo $key; ?>" <?php if($key==$dbFormData['user_cate_no']) echo 'selected' ; ?>>
                <?php echo sanitize($val['category_name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="memo-regist-option memo-regist-option-tag">
            タグ：
            <label class="tag-select">
              <input type="checkbox" name="tag[]" value="1" <?php if(strpos($dbFormData['tags'],'1') !==false) echo 'checked' ; ?>>
              <div class="tag tag1"></div>
            </label>
            <label class="tag-select">
              <input type="checkbox" name="tag[]" value="2" <?php if(strpos($dbFormData['tags'],'2') !==false) echo 'checked' ; ?>>
              <div class="tag tag2"></div>
            </label>
            <label class="tag-select">
              <input type="checkbox" name="tag[]" value="3" <?php if(strpos($dbFormData['tags'],'3') !==false) echo 'checked' ; ?>>
              <div class="tag tag3"></div>
            </label>
          </div>
          <div class="memo-regist-option">
            <input type="submit" value="更新する" class="btn btn-write">
          </div>
        </div>
        <p><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><i class="fas fa-arrow-left"></i>前の画面に戻る</a></p>
      </form>
    </section>

  </main>
  <?php require('footer.php'); ?>
