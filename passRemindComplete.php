<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行完了ページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

$siteTitle = 'パスワード再発行完了';
require('head.php');

?>

<body>
  <?php require('header.php'); ?>
  <main class="content site-width">
    <div class="main-form">
      <h2>パスワード再発行の完了</h2>
      <p>新しいパスワードを発行いたしました。</p><br>
      <p>ご登録のメールアドレス宛にパスワードお送りしましたので、メールをご確認いただき、新しいパスワードにてログインしてください。</p>
      <br>
      <br>
      <p class="center"><a href="login.php">ログインページはこちら</a></p>
    </div>
  </main>

  <?php require('footer.php'); ?>
