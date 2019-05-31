<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　検索結果表示ページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//setting.phpの戻るボタン機能に関するセッション処理
$_SESSION['referer'] = '';

//削除メッセージを出すのに使うセッション処理
if(!empty($_SESSION['msg_success_selfpage'])) {
  $_SESSION['msg_success'] = $_SESSION['msg_success_selfpage'];
  $_SESSION['msg_success_selfpage'] = '';
}

//get送信されている場合（search.phpで検索するボタンが押された時）
if(!empty($_GET)) {
  $searchCate = (isset($_GET['search_cate'])) ? $_GET['search_cate'] : '';
  $searchKey = (!empty($_GET['search_key'])) ? $_GET['search_key'] : '';
  $searchTag = (!empty($_GET['search_tag'])) ? implode(',', $_GET['search_tag']) : '';
  $searchSort = (!empty($_GET['search_sort'])) ? $_GET['search_sort'] : '';
  $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
} else {
  //get送信されていない時、他ページ（note.phpなど）で指定された検索条件を引き継ぐ
  $searchNoteData = getSessionFlash('search_result');
  $searchCate = $searchNoteData['search_cate'];
  $searchKey = $searchNoteData['search_key'];
  $searchTag = $searchNoteData['search_tag'];
  $searchSort = $searchNoteData['search_sort'];
  $currentPageNum = 1;
}
debug($_SESSION['search_result']);
debug($searchNoteData);
debug($searchKey);
//$origParam = $_SESSION['search_param'];

$param = '?search_key='.$searchKey.'&search_cate='.$searchCate.'&search_tag='.$searchTag.'&search_sort='.$searchSort;

debug('$param:'.$param);
debug('現在のページ:'.$currentPageNum);

//1ページの表示件数
$displayItemCount = 5;
//現在の表示レコード先頭-1
$currentMinNum = ($currentPageNum-1)*$displayItemCount;

//DBから検索で指定されたメモデータを取得
$searchMemoData = getSearchMemoData($_SESSION['user_id'], $searchKey, $searchCate, $searchTag, $searchSort, $currentMinNum, $displayItemCount);
//URLの表示パラメータをセッションに保存（edit.phpにて編集終了後に元のページに戻るため）
$_SESSION['search_param'] = $_SERVER['REQUEST_URI'];


$siteTitle = '検索';
require('head.php');

?>


<body>
  <?php require('header.php'); ?>
  


  <main class="content site-width">
    <div class="main-content">
     <p><a href="note.php"><i class="fas fa-arrow-left"></i>ノートに戻る</a></p>
      <section class="main-section">
        <h2 class="section-title"><i class="far fa-copy"></i>メモの検索結果</h2>
        <div class="msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </div>
        <?php if(!empty($searchMemoData['total'])): ?>
          <p>検索条件に一致する
            <?php echo sanitize($searchMemoData['total']); ?>件のメモのうち、
            <?php echo sanitize($currentMinNum+1); ?>〜
            <?php echo sanitize($currentMinNum+count($searchMemoData['data'])); ?>件のメモを表示します。</p>
          <br>
          <?php require('memoFrame.php'); ?>
          
        <?php else: ?>
        <p>検索結果に一致するメモはありません。</p>
        <?php endif; ?>
        <div class="pagination">
          <ul class="page-list">
            <?php echo pagination($currentPageNum, $searchMemoData['total_page'], $param, $displayItemCount); ?>
          </ul>
        </div>
      </section>
      <p><a href="note.php"><i class="fas fa-arrow-left"></i>ノートに戻る</a></p>
      <div id="js-msg" class="suc-msg">
        <?php echo getSessionFlash('msg_success'); ?>
      </div>
    </div>

    <?php require('sidebar.php'); ?>

  </main>
  <?php require('footer.php'); ?>
