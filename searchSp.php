<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　検索ページ（スマホ専用）');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//setting.phpの戻るボタン機能に関するセッション処理
$_SESSION['referer'] = '';


//検索ボタンおされたらsearch.phpに移動
if(!empty($_GET)) {
  debug('検索結果一覧ページに遷移します。');
  header("Location:search.php");
}


$siteTitle = '検索(SP)';
require('head.php');

?>


<body>
  <?php require('header.php'); ?>
  


  <main class="content site-width">

    <?php require('sidebar.php'); ?>

  </main>
  <?php require('footer.php'); ?>
