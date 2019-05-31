<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　TOPページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();



if(!empty($_SESSION['user_id'])) {
  header('Location:note.php');
}

$siteTitle = 'トップ';
require('head.php');

?>

<body>
  <?php require('header.php'); ?>
  <main class="site-width">
      <div class="top-section">
        <div class="top-img">
          <p class="top-phrase"><span class="top-phrase-keyword font-noto">MyNotes</span> は、<br>ちょっとしたメモをサッと記録、分類、検索できる<span class="under-line">シンプルなメモ帳アプリ</span>です。</p>
          <img class="note-img" src="images/blue-note.png" alt="">
        </div>
        <div class="top-btn-group">
          <div class="btn btn-twin"><a href="signup.php">ユーザー登録</a></div>
          <div class="btn btn-twin btn-trans"><a href="login.php">ログイン</a></div>
        </div>
        <br>
        <div class="btn btn-trans"><a href="guestLogin.php">ゲストログイン</a></div>
        <p class="center">※ゲストログイン機能を使いますと、ユーザー登録なしでお試しいただけます。</p>
      </div>
  </main>

  <?php require('footer.php'); ?>
