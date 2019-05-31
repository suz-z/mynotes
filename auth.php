<?php
//////////////////////////////////////////
// ログイン認証、自動ログアウト
//////////////////////////////////////////

//ログインしている場合
if(!empty($_SESSION['login_date'])) {
  debug('ログイン済みユーザーです。');
  
  //現在日時が最終ログイン日時＋有効期限を超えていた場合
  if(($_SESSION['login_date']+$_SESSION['login_limit']) < time()) {
    debug('ログイン有効期限オーバーです。');
    //セッションを削除（ログアウト）
    session_destroy();
    
  //現在日時が有効期限内の場合
  } else {
    debug('ログイン有効期限内です。');
    //最終ログイン日時を現在日時に更新
    $_SESSION['login_date'] = time();
    
    //現在実行中のファイル名がlogin.phpの場合
    if(basename($_SERVER['PHP_SELF']) === 'login.php') {
      debug('ノートページに遷移します。');
      header('Location:note.php');
    }
  }
  
//ログインしていない場合
} else {
  debug('未ログインユーザーです。');
  //現在実行中のファイル名がlogin.phpの場合
  if(basename($_SERVER['PHP_SELF']) !== 'login.php') {
    debug('トップページに遷移します。');
    header('Location:index.php');
  }
}
