<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　ログインページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//POST送信されていた場合
if(!empty($_POST)) {
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save =(!empty($_POST['pass_save'])) ? true : false;
  //Emailの形式チェック
  validEmail($email);
  //Emailの最大文字数チェック
  validMaxLen($email, 'email');
  
  //パスワードの半角文字数チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass');
  
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  if(empty($err_msg)) {
    debug('バリデーションOKです。');
    //例外処理
    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'SELECT password,user_id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      //クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      debug('クエリ結果の中身:'.print_r($result,true));

      //パスワード照合
      if(!empty($result) && password_verify($pass, $result['password'])) {
        debug('パスワードがマッチしました。');

        //ログイン有効期限（デフォルトを1時間にする
        $sesLimit = 60*60;
        //最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time();

        //ログイン保持にチェックがある場合
        if($pass_save) {
          debug('ログイン保持にチェックがあります。');
          //ログイン有効期限を30日にしてセット
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          $_SESSION['login_limit'] = $sesLimit;
        }
        //ユーザーIDを格納
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['msg_success'] = SUC09;

        debug('セッション変数の中身:'.print_r($_SESSION,true));
        debug('ノートページに遷移します。');
        header('Location:note.php');

      } else {
        debug('パスワードがアンマッチです。');
        $err_msg['common'] = MSG09;
      }
    } catch(Exception $e) {
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
  
$siteTitle = 'ログイン';
require('head.php');

?>

<body>
  <!--ヘッダー-->
  <?php require('header.php'); ?>
  <!--メインコンテンツ-->
  <main class="content site-width">

    <form action="" method="post" class="main-form">
      <h2>ログイン</h2>
      <div class="msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
      </div>
      <label class="form-label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
        メールアドレス
        <input type="email" name="email" value="<?php if(!empty($email)) echo sanitize($email) ?>" class="input-box" id="js-email" required>
        <div class="msg" id="js-email-msg">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
        </div>
      </label>
      <br>
      <label class="form-label <?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
        パスワード
        <input type="password" name="pass" value="<?php if(!empty($pass)) echo sanitize($pass) ?>" class="input-box" pattern="^([a-zA-Z0-9]{6,})$" title="半角英数字6文字以上" required>
        <div class="msg">
          <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'] ?>
        </div>
      </label>
      <br>
      <label>
          <input type="checkbox" name="pass_save">次回ログインを省略する
        </label>
      <input type="submit" value="ログイン" class="btn">
      <p>※パスワードを忘れた方は<a href="passRemindSend.php">こちら</a></p><br>
      <p><a href="index.php"><i class="fas fa-arrow-left"></i>トップページに戻る</a></p>
    </form>
  </main>

  <?php require('footer.php'); ?>
