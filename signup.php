<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//POST送信されていた場合
if(!empty($_POST)) {
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  
  if(empty($err_msg)) {
    //Emailの形式チェック
    validEmail($email);
    //Emailの最大文字数チェック
    validMaxLen($email, 'email');
    //Emailの重複チェック
    validEmailDup($email);
    
    //パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    //パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    //パスワードの最小文字数チェック
    validMinLen($pass, 'pass');
    
    //パスワードとパスワード再入力が合致するかチェック
    validMatch($pass, $pass_re, 'pass_re');
    
    if(empty($err_msg)) {
      //例外処理
      try {
        $dbh = dbConnect();
        //ユーザー情報を登録
        $sql1 = 'INSERT INTO users (email,password,login_time,create_date) VALUES(:email,:pass,:login_time,:create_date)';
        $data1 = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
        $stmt1 = queryPost($dbh, $sql1, $data1);
        
        //クエリ成功の場合（ユーザー登録完了したらそのままログイン状態になる処理）
        if($stmt1) {
          debug('ユーザー情報を登録しました。');
          //ログイン有効期限（デフォルトを1時間とする）
          $sesLimit = 60*60;
          $_SESSION['login_date'] = time();
          $_SESSION['login_limit'] = $sesLimit;
          $_SESSION['user_id'] = $dbh->lastInsertId();
          debug('セッション変数の中身:'.print_r($_SESSION,true));
          
          //デフォルトカテゴリデータを登録
          $sql2 = 'INSERT INTO category (category_name, user_id, user_cate_no, create_date) VALUES (:cate0, :u_id, 0, :date), (:cate1, :u_id, 1, :date), (:cate2, :u_id, 2, :date), (:cate3, :u_id, 3, :date), (:cate4, :u_id, 4, :date)';
          $data2 = array(':cate0' => 'カテゴリ1', ':cate1' => 'カテゴリ2', ':cate2' => 'カテゴリ3', ':cate3' => 'カテゴリ4', ':cate4' => 'カテゴリ5', ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
          $stmt2 = queryPost($dbh, $sql2, $data2);
          if($stmt2) {
            $_SESSION['msg_success'] = SUC09;
            debug('デフォルトカテゴリデータを登録しました。');
            debug('ログインしてノートページに遷移します。');
            header('Location:note.php'); 
          } else {
            debug('クエリが失敗しました。');
            $err_msg['common'] = MSG07;
          }
        }
      } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

$siteTitle = 'ユーザー登録';
require('head.php');

?>


<body>
  <?php require('header.php'); ?>
  <main class="content site-width">
    <form action="" method="post" class="main-form">
      <h2>ユーザー登録</h2>
      <div class="msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
      </div>
      <label class="form-label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
        メールアドレス
        <input type="email" name="email" value="<?php if(!empty($email)) echo sanitize($email) ?>" class="input-box" required>
        <div class="msg">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
          <br>
        </div>
      </label>
      <label class="form-label <?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
        パスワード　<span class="small">※半角英数字６文字以上</span>
        <input type="password" name="pass" value="<?php if(!empty($pass)) echo sanitize($pass) ?>" class="input-box" pattern="^([a-zA-Z0-9]{6,})$" title="半角英数字6文字以上" required>
        <div class="msg">
          <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'] ?>
          <br>
        </div>
      </label>
      <label class="form-label <?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
        パスワード（再入力）
        <input type="password" name="pass_re" value="<?php if(!empty($pass_re)) echo sanitize($pass_re) ?>" class="input-box" pattern="^([a-zA-Z0-9]{6,})$" title="半角英数字6文字以上" required>
        <div class="msg">
          <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'] ?>
        </div>
      </label>
      <input type="submit" value="登録する" class="btn">
      <br>
      <p><a href="index.php"><i class="fas fa-arrow-left"></i>トップページに戻る</a></p>
    </form>
  </main>

  <?php require('footer.php'); ?>
