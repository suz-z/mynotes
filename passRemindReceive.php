<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行認証キー入力ページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//SESSIONに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])){
  header('Location:passRemindSend.php');
}

//POST送信されていた場合
if(!empty($_POST)) {
  debug('POST送信があります。');
  $auth_key = $_POST['token'];
  
  //未入力チェック
  validRequired($auth_key, 'token');
  
  if(empty($err_msg)) {
    debug('未入力チェックOK。');
    
//    //固定長チェック
//    validLength($auth_key, 'token', 8);
//    //半角チェック
//    vaslidHalf($auth_key, 'token');
//    
//    debug('バリデーションチェックOK。');
    
    if($auth_key !== $_SESSION['auth_key']){
      $err_msg['token'] = MSG11;
    }
    if(time() > $_SESSION['auth_key_limit']){
      $err_msg['token'] = MSG12;
    }
    
    if(empty($err_msg)) {
      debug('認証OK。');
      //パスワード生成
      $pass = makeRandKey();
      //例外処理
      try {
        $dbh = dbConnect();
        $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        //クエリ成功の場合
        if($stmt){
          debug('クエリ成功。');
          
          //メールを送信
          $from = 'MyNotes<info@oh-mynotes.work>';
          $to = $_SESSION['auth_email'];
          $subject = '【パスワード再発行完了】｜MyNotes';
          $comment = <<<EOT
本メールアドレス宛にパスワードを再発行いたしました。
下記のURLにて再発行パスワードご入力いただき、ログインしてください。

ログインページ
https://oh-mynotes.work/login.php
再発行パスワード：{$pass}

※ログイン後、パスワードのご変更をお願いいたします。


///////////////////////////////////
シンプルなメモアプリ MyNotes
URL     https://oh-mynotes.work
E-mail  info@oh-mynotes.work
///////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          
          //セッション削除
          session_unset();
          $_SESSION['msg_success'] = SUC05;
          debug('セッション変数の中身:'.print_r($_SESSION,true));
          
          header('Location:passRemindComplete.php');
          
        } else {
          debug('クエリに失敗しました。');
          $err_msg['common'] =  MSG07;
        }

      } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

$siteTitle = 'パスワード再発行認証';
require('head.php');

?>

<body>
  <?php require('header.php'); ?>
  <main class="content site-width">
    <form action="" method="post" class="main-form">
      <h2>パスワード再発行の認証</h2>
      <p>ご登録のメールアドレス宛に【パスワード再発行認証】メールをお送りしました。</p><br>
      <p>メールをご確認いただき、メール内にある「認証キー」をご入力ください。</p>
      <br>

      <label class="form-label <?php if(!empty($err_msg['token'])) echo 'err'; ?>">
        <div class="msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </div>
        認証キー
        <!--  フォーム入力保持、サニタイズ必要！　getFormData関数作った方がいいかも -->
        <input type="text" name="token" value="<?php if(!empty($email)) echo $email ?>" class="input-box">
        <div class="msg">
          <?php if(!empty($err_msg['token'])) echo $err_msg['token'] ?>
        </div>
      </label>
      
      <input type="submit" value="再発行する" class="btn" required>
      <br>
      <p><a href="passRemindSend.php"><i class="fas fa-arrow-left"></i>パスワード再発行メールを再度送信する</a></p>
    </form>
  </main>

  <?php require('footer.php'); ?>
