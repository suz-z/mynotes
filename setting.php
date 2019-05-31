<?php 


require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　マイメニューページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//setting.phpの戻るボタン機能に関するセッション処理
//setting.php内でPOST送信するとHTTP_REFERERが上書きされてしまうのを防ぐ。
if(empty($_SESSION['referer'])) {
  $_SESSION['referer'] = $_SERVER["HTTP_REFERER"];
}

//var_dump($_SESSION['msg_success_selfpage']);
//var_dump($_SESSION['msg_success']);


//DBに登録されている情報を取得
$cateData = getCateData($_SESSION['user_id']); //配列
$userData = getUserData($_SESSION['user_id']);

//DBに登録されている情報を出力できる形式に
for($i = 0; $i <= 4; $i++) {
  $formOutput[$i] = sanitize($cateData[$i]['category_name']);
}
$email = $userData['email'];

//////////////////////////////
//カテゴリの登録・変更
//////////////////////////////

//同一ページ更新時のメッセージ表示につかうセッション処理
if(!empty($_SESSION['msg_success_selfpage'])) {
  $_SESSION['msg_success'] = $_SESSION['msg_success_selfpage'];
  $_SESSION['msg_success_selfpage'] = '';
}

if(!empty($_POST['cate'])) {
  debug('カテゴリーの登録、変更ボタンが押されました。');
  $_SESSION['acd_cate'] = 'checked'; 
  
  $categories = $_POST['cate']; //配列
  
  //POST送信されたカテゴリー名を出力形式に
  for($i = 0; $i <= 4; $i++) {
    $formOutput[$i] = sanitize($categories[$i]);
  }

  //最大文字数チェック
  for($i = 0; $i < 5; $i++) {
    validMaxLen($categories[$i],'cate'.$i, 20);
  }
  
  if(empty($err_msg)) {
    debug('バリデーションOKです。');
    
    //例外処理
    try {
      $dbh = dbConnect();
      for($i = 0; $i < 5; $i++) {
        $sql = 'UPDATE category SET category_name = :cate'.$i.' WHERE user_id = :u_id AND user_cate_no ='.$i;
        $data = array(":cate{$i}" => $categories[$i], ':u_id' => $_SESSION['user_id']);
        $stmt = queryPost($dbh, $sql, $data);
      }
      if($stmt) {
        debug('クエリ成功。カテゴリ名を登録します。');
        $_SESSION['msg_success'] = SUC03;
        $cateData = getCateData($_SESSION['user_id']);
      }
    } catch(Exception $e) {
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

//////////////////////////////
//メールアドレスの変更
//////////////////////////////
if(!empty($_POST['mail'])) {
  debug('メールアドレスの変更ボタンが押されました。');
  $_SESSION['acd_mail'] = 'checked';
  
  $email = $_POST['mail'];
  //未入力チェック
  validRequired($email, 'email');
  
  if(empty($err_msg)) {
    
    //Emailの形式チェック
    validEmail($email);
    //Emailの最大文字数チェック
    validMaxLen($email, 'email');
    //Emailの重複チェック
    validEmailDup($email);
    
    if(empty($err_msg)) {
      
      //例外処理
      try {
        $dbh = dbConnect();
        //メールアドレスを更新
        $sql = 'UPDATE users SET email = :email WHERE user_id = :u_id';
        $data = array(':email' => $email, ':u_id' => $_SESSION['user_id']);
        $stmt = queryPost($dbh, $sql, $data);
        
        //クエリ成功の場合
        if($stmt) {
          debug('メールアドレスを変更しました。');
          $_SESSION['msg_success'] = SUC07;
        }
      } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

////////////////////////////////////////
//パスワードの変更  submitボタンが押された時
///////////////////////////////////////
if(!empty($_POST['pass-update'])) {
  debug('パスワードの変更ボタンが押されました。');
  $_SESSION['acd_pass'] = 'checked';
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  //未入力チェック
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  
  if(empty($err_msg)) {
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
        //メールアドレスを更新
        $sql = 'UPDATE users SET password = :pass WHERE user_id = :u_id';
        $data = array(':pass' => password_hash($pass, PASSWORD_DEFAULT), ':u_id' => $_SESSION['user_id']);
        $stmt = queryPost($dbh, $sql, $data);
        
        //クエリ成功の場合
        if($stmt) {
          debug('パスワードを変更しました。');
          $_SESSION['msg_success'] = SUC08;
        }
      } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

////////////////////////////////////////
//  退会処理
///////////////////////////////////////
if(!empty($_POST['withdraw'])) {
  debug('退会ボタンが押されました。');
  //例外処理
  try {
    $dbh = dbConnect();
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE user_id = :u_id';
    $sql2 = 'UPDATE memo SET delete_flg = 1 WHERE user_id = :u_id';
    $sql3 = 'UPDATE category SET delete_flg = 1 WHERE memo.user_id = :u_id AND memo.category_id = category.category_id';  //自信ない
    $data = array(':u_id' => $_SESSION['user_id']);
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql1, $data);
    $stmt3 = queryPost($dbh, $sql1, $data);
    
    //クエリ実行成功の場合（userテーブルのみ削除チェック）
    if($stmt1){
      session_destroy();
      debug('セッション変数の中身:'.print_r($_SESSION,true));
      debug('退会処理完了。トップページへ遷移します。');
      header('Location:index.php');
    } 
  } catch(Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

$siteTitle = 'マイメニュー';
require('head.php');

?>


<body>
  <?php require('header.php'); ?>
  <script>
    //退会ボタンを押した時の確認ダイアログ
    function submitWith() {
      // 確認ダイアログ表示
      var flag = confirm("本当に退会しますか？\n\n退会するとすべてのメモが削除されます。");
      // flag が TRUEなら送信、FALSEなら送信しない
      return flag;
    }
  </script>
  <main class="content site-width">
    <div class="main-content setting-content">
      <p><a href="<?php echo $_SESSION['referer']; ?>"><i class="fas fa-arrow-left"></i>前の画面に戻る</a></p>
      <br>
      <section class="main-section acd-menu">
        <input type="checkbox" id="acd-check1" class="acd-check" <?php echo getSessionFlash('acd_cate'); ?>>
        <label for="acd-check1" class="acd-label">
          <h2 class="section-title acd-title" id="cate-edit">
            <i class="fas fa-chevron-right"></i>
            <i class="fas fa-chevron-down"></i>
            カテゴリの登録・変更
          </h2>
        </label>
        <form action="" method="post" class="acd-form">
          <p>カテゴリを5つまで登録できます。カテゴリ名を下記に入力してください。</p>
          <br>
          <div class="msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          </div>

          <?php for($i = 0; $i <= 4; $i++) { ?>
          <label class="form-label <?php if(!empty($err_msg['cate'.$i])) echo 'err'; ?>">
            <div class="msg">
              <?php if(!empty($err_msg['cate'.$i])) echo $err_msg['cate'.$i]; ?>
            </div>
            <input type="text" name="cate[<?php echo $i?>]" value="<?php echo $formOutput[$i]; ?>" class="input-box input-box-narrow js-cate">
            <div class="char-limit right js-cate-msg">(20文字以内)</div>
          </label>
          <?php } ?>

          <div class="msg">
            <?php if(!empty($err_msg['cate'])) echo $err_msg['cate'] ?>
          </div>
          <input class="btn" type="submit" value="登録・変更する">
        </form>
      </section>

      <section class="main-section acd-menu">
        <input type="checkbox" id="acd-check2" class="acd-check" <?php echo getSessionFlash('acd_mail'); ?>>
        <label for="acd-check2" class="acd-label">
          <h2 class="section-title acd-title" id="mail-edit">
            <i class="fas fa-chevron-right"></i>
            <i class="fas fa-chevron-down"></i>
            メールアドレス変更
          </h2>
        </label>
        <form action="" method="post" class="acd-form">
          <p>新しいメールアドレスを下記に入力してください。</p><br>
          
          <?php if(($_SESSION['user_id']) === GUEST) { ?>
            <p class="caution">※ゲストユーザーはこの機能を利用できません（ボタンを押しても送信されません）。</p><br>
          <?php }; ?>
          
          <div class="msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          </div>
          <label class="form-label <?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            新しいメールアドレス
            <input type="email" name="mail" value="<?php echo sanitize($email); ?>" class="input-box" required>
            <div class="msg">
              <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
            </div>
          </label>
          <input class="btn <?php if($_SESSION['user_id'] === GUEST) echo 'btn-disabled'; ?>" type="submit" value="変更する" <?php if($_SESSION['user_id'] === GUEST) echo 'disabled'?>>
        </form>
      </section>

      <section class="main-section acd-menu">
        <input type="checkbox" id="acd-check3" class="acd-check" <?php echo getSessionFlash('acd_pass'); ?>>
        <label for="acd-check3" class="acd-label">
          <h2 class="section-title acd-title" id="pass-edit">
            <i class="fas fa-chevron-right"></i>
            <i class="fas fa-chevron-down"></i>
            パスワード変更
          </h2>
        </label>
        <form action="" method="post" class="acd-form">
          <p>新しいパスワードを下記に入力してください。</p><br>
          
          <?php if(($_SESSION['user_id']) === GUEST) { ?>
            <p class="caution">※ゲストユーザーはこの機能を利用できません（ボタンを押しても送信されません）。</p><br>
          <?php }; ?>
          
          <div class="msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          </div>
          <label class="form-label <?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
            新しいパスワード　<span class="small">※半角英数字６文字以上</span>
            <input type="password" name="pass" value="<?php if(!empty($pass)) echo sanitize($pass) ?>" class="input-box" pattern="^([a-zA-Z0-9]{6,})$" title="半角英数字6文字以上" required>
            <div class="msg">
              <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'] ?>
            </div>
          </label>
          <br>
          <label class="form-label <?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
            新しいパスワード（再入力）
            <input type="password" name="pass_re" value="<?php if(!empty($pass_re)) echo sanitize($pass_re) ?>" class="input-box" pattern="^([a-zA-Z0-9]{6,})$" title="半角英数字6文字以上" required>
            <div class="msg">
              <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'] ?>
            </div>
          </label>
          <input class="btn <?php if($_SESSION['user_id'] === GUEST) echo 'btn-disabled'; ?>" type="submit" value="変更する" name="pass-update" <?php if($_SESSION['user_id'] === GUEST) echo 'disabled'?>>
        </form>
      </section>

      <section class="main-section acd-menu">
        <input type="checkbox" id="acd-check4" class="acd-check">
        <label for="acd-check4" class="acd-label">
          <h2 class="section-title acd-title" id="withdraw">
            <i class="fas fa-chevron-right"></i>
            <i class="fas fa-chevron-down"></i>
            退会
          </h2>
        </label>
        <form action="" method="post" class="acd-form" onsubmit="return submitWith()">
          <p>退会(会員登録を削除)される方は下記のボタンを押してください。<br>
            退会されると、すべてのメモは削除されます。</p><br>
            
          <?php if(($_SESSION['user_id']) === GUEST) { ?>
            <p class="caution">※ゲストユーザーはこの機能を利用できません（ボタンを押しても送信されません）。</p><br>
          <?php }; ?>

          <div class="msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          </div>
          <input type="submit" value="退会する" name="withdraw" class="btn <?php if($_SESSION['user_id'] === GUEST) echo 'btn-disabled'; ?>" <?php if($_SESSION['user_id'] === GUEST) echo 'disabled'?>>
        </form>
      </section>
      <p><a href="<?php echo $_SESSION['referer']; ?>"><i class="fas fa-arrow-left"></i>前の画面に戻る</a></p>
      <div id="js-msg" class="suc-msg">
        <?php echo getSessionFlash('msg_success'); ?>
      </div>
    </div>
  </main>

  <?php require('footer.php'); ?>
