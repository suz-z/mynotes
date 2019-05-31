<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行メール送信ページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//POST送信されていた場合
if(!empty($_POST)) {
  debug('POST送信があります。');
  $email = $_POST['email'];
  
  //未入力チェック
  validRequired($email, 'email');
  
  if(empty($err_msg)) {
    debug('未入力チェックOK。');
    
    //Emailの形式チェック
    validEmail($email);
    //Emailの最大文字数チェック
    validMaxLen($email, 'email');
    
    debug('バリデーションチェックOK。');
    
    if(empty($err_msg)) {
      //例外処理
      try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        //クエリ成功の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //Emailがデータベースに登録されている場合
        if($stmt && array_shift($result)){
          debug('クエリ成功。DB登録あり。');
          $_SESSION['msg_success'] = SUC05;
          
          //認証キー生成
          $auth_key = makeRandKey();
          
          //メールを送信
          $from = 'MyNotes<info@oh-mynotes.work>';
          $to = $email;
          $subject = '【パスワード再発行認証】｜MyNotes';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がございました。
下記のURLにて認証キーをご入力いただくとパスワードが再発行されます。

パスワード再発行認証キー入力ページ
https://oh-mynotes.work/passRemindReceive.php
認証キー：{$auth_key}

※認証キーの有効期限は30分となります。

※認証キーを再発行されたい場合は、下記ページより再発行をお願いいたします。
https://oh-mynotes.work/passRemindSend.php

///////////////////////////////////
シンプルなメモアプリ MyNotes
URL     https://oh-mynotes.work
E-mail  info@oh-mynotes.work
///////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          
          //認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time()+(60*30); //現在より30分後のタイムスタンプ
          debug('セッション変数の中身:'.print_r($_SESSION,true));
          
          header('Location:passRemindReceive.php');
          
        } else {
          debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
          $err_msg['common'] =  MSG07;
        }

      } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

$siteTitle = 'パスワード再発行メール送信';
require('head.php');

?>

<body>
  <?php require('header.php'); ?>
  <main class="content site-width">
    <form action="" method="post" class="main-form">
      <h2>パスワードの再発行</h2>
      <p>ご登録のメールアドレス宛にパスワード再発行用のURLと認証キーをお送りします。</p>
      <br>
      <div class="msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
      </div>
      <label class="form-label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
        メールアドレス
        <!--  フォーム入力保持、サニタイズ必要！　getFormData関数作った方がいい? -->
        <input type="email" name="email" value="<?php if(!empty($email)) echo sanitize($email) ?>" class="input-box" required>
      </label>
      <div class="msg">
        <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
      </div>
      <input type="submit" value="送信する" class="btn">
      <br>
      <p><a href="login.php"><i class="fas fa-arrow-left"></i>ログインページに戻る</a></p>
    </form>
  </main>

  <?php require('footer.php'); ?>
