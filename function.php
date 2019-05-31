<?php
//////////////////////////////////////////
// ログ
//////////////////////////////////////////
//ログを取る
ini_set('log_errors', 'on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');

//////////////////////////////////////////
// デバッグ
//////////////////////////////////////////
//デバッグフラグ（開発中のみtrueにする）
$debug_flg = true;
//デバッグログ関数
function debug($str) {
  global $debug_flg;
  if($debug_flg){  //emptyじゃなくてもいい気がするが
    error_log('デバッグ:'.$str);
  }
}

//////////////////////////////////////////
// セッション準備、セッション有効期限
//////////////////////////////////////////
//セッションファイルの行き場変更（/var/tmp/以下に置くと30日削除されない）
session_save_path('/var/tmp');
//ガーベージコレクションが削除するセッションの有効期限を設定
//(30日以上経っているものに対してだけ1/100の確率で削除)
ini_set('session.gc_maxlifetime', 60*60*24*30);
//クッキーの有効期限を伸ばす（ブラウザを閉じても削除されないように）
ini_set('session.cookie_lifetime', 60*60*24*30);
//セッションを使う
session_start();
//新しく生成したセッションIDと置き換える
session_regenerate_id();

//////////////////////////////////////////
// 画面表示処理開始ログの吐き出し
//////////////////////////////////////////
function debugLogStart() {
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始');
  debug('セッションID:'.session_id());
  debug('セッション変数の中身:'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ:'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ:'.($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//////////////////////////////////////////
// 定数
//////////////////////////////////////////
define('URL', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
define('GUEST', 4); //ゲストユーザーID

define('MSG01', '入力必須です');
define('MSG02', 'メールアドレスの形式で入力してください');
define('MSG03', '文字以内で入力してください');
define('MSG04', '6文字以上で入力してください');
define('MSG05', '半角英数字で入力してください');
define('MSG06', 'パスワード（再入力）が合っていません');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'このメールアドレスはすでに登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '検索条件に一致するメモが見つかりません');
define('MSG11', '正しくありません');
define('MSG12', '有効期限が切れています');
define('MSG13', '文字で入力してください。');

define('SUC01', 'メモを更新しました');
define('SUC02', 'メモを削除しました');
define('SUC03', 'カテゴリ名を変更しました');
define('SUC04', '新しいメモを保存しました');
define('SUC05', 'メールを送信しました');
define('SUC06', 'ゲストとしてログインしました');
define('SUC07', 'メールアドレスを変更しました');
define('SUC08', 'パスワードを変更しました');
define('SUC09', 'ログインしました');

//////////////////////////////////////////
// バリデーション
//////////////////////////////////////////

//エラーメッセージ格納用の配列
$err_msg = array();

//未入力チェック
function validRequired($str, $key) {
  if(empty($str)) {                       //空文字でOK？　emptyの方がいいか？
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//Email形式チェック
function validEmail($email) {
  if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $email)) {
    global $err_msg;
    $err_msg['email'] = MSG02; //メールアドレスの形式で入力してください
  }
}
//最大文字数チェック
function validMaxLen($str, $key, $max = 255) {
  if(mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = $max.MSG03; //$max文字以内で入力してください
  }
}
//最小文字数チェック
function validMinLen($str, $key, $min = 6) {
  if(mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG04; //6文字以上で入力してください
  }
}
//半角英数字チェック
function validHalf($str, $key){
  if(!preg_match('/^[a-zA-Z0-9]+$/', $str)) {
    global $err_msg;
    $err_msg[$key] = MSG05; //半角英数字で入力してください
  }
}
//同値チェック
function validMatch($str1, $str2, $key) {
  if($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG06; //パスワード（再入力）が合っていません
  }
}

//固定長チェック
//function validLength($str, $key, $len){
//  if(mb_strlen($str) !== $len) {
//    global $err_msg;
//    $err_msg[$key] = $len.MSG13;
//  }
//}


//Emailの重複チェック
function validEmailDup($email){
  global $err_msg;
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty(array_shift($result))) {
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//////////////////////////////////////////
// データベース接続
//////////////////////////////////////////
// 非公開情報を読み込む
require('protectionConfig.php');

//DB接続関数
function dbConnect() {
 $dsn = DBDSN;
 $user = DBUSER;
 $password = DBPASS;
 
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,//warningで本当によいか？
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

//SQL実行関数
function queryPost($dbh, $sql, $data) {
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットしSQL文実行
  if(!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL:'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  } else {
    debug('クエリ成功。');
    return $stmt;
  }
}

//////////////////////////////////////////
// 検索データ取得関数
//////////////////////////////////////////
function getSearchMemoData($user_id, $keyword, $category, $tags, $sort, $min, $item_count) {  //$tags $sort を付け加える
  debug('検索に合致するメモデータを取得します。');
  debug('ユーザーID:'.$user_id);
  debug('キーワード:'.$keyword);
  debug('カテゴリー:'.$category);
  debug('タグ:'.$tags);
  debug('並び順:'.$sort);
  debug('表示の先頭:'.$min);
  debug('表示件数:'.$item_count);
  
  //例外処理
  try {
    $dbh = dbConnect();
    
    $sql = 'SELECT m.memo_id, m.content, m.tags, m.create_date, m.update_date, c.category_name FROM memo AS m LEFT JOIN category AS c ON m.user_cate_no = c.user_cate_no AND m.user_id = c.user_id WHERE m.user_id = :u_id AND content LIKE :keyword AND m.delete_flg = 0';
    
    
//    $sql = 'SELECT m.memo_id, m.content, m.tags, m.create_date, m.update_date, c.category_name FROM memo AS m LEFT JOIN category AS c ON m.user_cate_no = c.user_cate_no WHERE m.user_id = :u_id AND content LIKE :keyword AND m.delete_flg = 0';
    
  //  if($category !== 'all') $sql .= ' AND m.user_cate_no = '.$category;
    if($category != '') $sql .= ' AND m.user_cate_no = '.$category;
    
    //（for文使えるが、わかりやすさのためにこう記述)  
    if($tags === '1') $sql .= ' AND m.tags LIKE "%1%"';
    if($tags === '2') $sql .= ' AND m.tags LIKE "%2%"';
    if($tags === '3') $sql .= ' AND m.tags LIKE "%3%"';
    
    if($tags === '1,2') $sql .= ' AND (m.tags LIKE "%1%" OR m.tags LIKE "%2%")';
    if($tags === '2,3') $sql .= ' AND (m.tags LIKE "%2%" OR m.tags LIKE "%3%")';
    if($tags === '1,3') $sql .= ' AND (m.tags LIKE "%1%" OR m.tags LIKE "%3%")';
    
    if($sort === 'old') {
      $sql .= ' ORDER BY m.create_date ASC';
    } else {
      $sql .= ' ORDER BY m.create_date DESC';
    }
    $data = array(':u_id' => $user_id, ':keyword' => '%'.$keyword.'%');
    debug('完成したSQl文（検索結果すべてを取得）:'.$sql);
    $stmt = queryPost($dbh, $sql, $data);
    $result['total'] = $stmt->rowCount(); //検索に一致するメモの件数
    $result['total_page'] = ceil($result['total']/$item_count); //総ページ数
    if(!$stmt) {
      return false;
    }
    
    //検索結果をページごとに分けて表示
    $sql .= " LIMIT $item_count OFFSET $min";
//  $data = array(':u_id' => $user_id, ':keyword' => '%'.$keyword.'%');
    debug('完成したSQl文（検索結果をページごとに取得）:'.$sql);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      $result['data'] = $stmt->fetchAll();
      return $result;
    } else {
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
  }
}

//////////////////////////////////////////
// 個別のメモデータ取得関数
//////////////////////////////////////////
function getMemoData($memo_id) {
  debug('1件のメモデータを取得します。');
  
  //例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT m.content, m.tags, m.create_date, m.update_date, m.user_cate_no, c.category_name FROM memo AS m LEFT JOIN category AS c ON m.user_cate_no = c.user_cate_no WHERE m.memo_id = :memo_id AND m.delete_flg = 0';
    $data = array(':memo_id' => $memo_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;  //これって必要？
    }
  } catch(Exception $e) {
      error_log('エラー発生:'.$e->getMessage());
  }
}

//////////////////////////////////////////
// カテゴリ情報取得関数
//////////////////////////////////////////
function getCateData($user_id) {
  debug('カテゴリ情報を取得します。');
  debug('ユーザーID:'.$user_id);
  //例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT user_cate_no,category_name FROM category WHERE user_id = :u_id ORDER BY user_cate_no ASC LIMIT 5';
    $data = array(':u_id' => $user_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt) {
      $result = $stmt->fetchAll();
      debug('取得したカテゴリ情報'.print_r($result,true));
      return $result;
    } else {
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
  }
}

//////////////////////////////////////////
// ユーザー情報取得関数
//////////////////////////////////////////
function getUserData($user_id) {
  debug('ユーザー情報を取得します。');
  debug('ユーザーID'.$user_id);
  //例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT email, create_date, update_date FROM users WHERE user_id = :u_id';
    $data = array(':u_id' => $user_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt) {
      $result = $stmt->fetchALL();
      debug('取得したユーザー情報'.print_r($result,true));
      return $result;
    } else {
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
  }
}

//////////////////////////////////////////
// フォーム入力保持
//////////////////////////////////////////
function getFormData($str) {
  global $dbFormData;
  //ユーザーデータがある場合
  if(!empty($dbFormData)) {
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])) {
      //POSTされたデータがある場合
      if(isset($_POST[$str])) {
        return sanitize($_POST[$str]);
      } else {
      //ない場合（基本的にはありえないが）
        return sanitize($dbFormData[$str]);
      }
    } else {
      //POSTされたデータがあり、DBの情報と異なる場合
      if(isset($_POST[$str]) && $_POST[$str] !== $dbFormData[$str]) {
        return sanitize($_POST[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if(isset($_POST[$str])) {
      return sanitize($_POST['$str']);
    }
  }
}


//////////////////////////////////////////
// メモの削除処理
//////////////////////////////////////////
function deleteData($memo_id) {
  debug('削除するメモID:'.$memo_id);
  echo '<script>delConfirm('.$memo_id.')</script>';
  try {
    $dbh = dbConnect();
    $sql = 'UPDATE memo SET delete_flg = 1 WHERE memo_id = :memo_id';
    $data = array(':memo_id' => $_POST['memo_id']);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      debug('メモを削除します。');
      $_SESSION['msg_success_selfpage'] = SUC02;
      header("Location:".$_SERVER['REQUEST_URI']);//現在と同じ画面に遷移
      //↑最終ページにある1件を削除すると、エラーにはならないが空白ページが表示されてしまう
    }
  } catch(Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//////////////////////////////////////////
// ページネーション
//////////////////////////////////////////
//$currentPageNum:現在のページ、$totalPageNum:総ページ数、$param:検索用GETパラメータリンク
//$displayItemCount:1ページに表示する項目の数
function pagination($currentPageNum, $totalPageNum, $param, $displayItemCount) {
  //総ページ数が表示ページ項目数以下のとき □ / □□ / □□□ / □□□□ / □□□□□
  if($totalPageNum <= $displayItemCount) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  //総ページ数が表示ページ項目数よりも多いとき □□□□□
  } else {
    switch($currentPageNum) {
      case 1:  // ■□□□□
      case 2:  // □■□□□
        $minPageNum = 1;
        $maxPageNum = 5;
        $lastPageNum = $totalPageNum;
        break;
      case ($totalPageNum-1):  // □□□■□
        $minPageNum = $currentPageNum - 3;
        $maxPageNum = $currentPageNum + 1;
        $firstPageNum = 1;
        break;
      case $totalPageNum:  // □□□□■
        $minPageNum = $currentPageNum - 4;
        $maxPageNum = $currentPageNum;
        $firstPageNum = 1;
        break;
      default:  // □□■□□
        $minPageNum = $currentPageNum - 2;
        $maxPageNum = $currentPageNum + 2;
        $minPageNum > 1 ? $firstPageNum = 1 : '';
        $maxPageNum < $totalPageNum ? $lastPageNum = $totalPageNum : '';
    }
  }
  
  if(!empty($firstPageNum)) {
    echo '<li class="page"><a href="'.$param.'&p=1">1</a></li>';
    echo '<li class="page ellipsis">...</li>';
  }
  for($i = $minPageNum; $i <= $maxPageNum; $i++) {
    echo '<li class="page';
    if($currentPageNum == $i) {
      echo ' active">'.$i.'</li>';
    } else {
      echo '"><a href="'.$param.'&p='.$i.'">'.$i.'</a></li>';
    }
  }
    if(!empty($lastPageNum)) {
    echo '<li class="page ellipsis">...</li>';
    echo '<li class="page"><a href="'.$param.'&p='.$lastPageNum.'">'.$lastPageNum.'</a></li>';
  }
  //$param.'&p='.$lastPageNum.のところ、appendGetParam関数つかう
}

//////////////////////////////////////////
// メール送信
//////////////////////////////////////////
function sendMail($from, $to, $subject, $comment) {
  if(!empty($to) && !empty($subject) && !empty($comment)) {
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");
    
    //メールを送信
    $result = mb_send_mail($to, $subject, $comment, "From:".$from);
    //送信結果を判定
    if($result) {
      debug('メールを送信しました。');
    } else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}

//////////////////////////////////////////
// その他の関数
//////////////////////////////////////////

//サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

//セッションを一回取得してすぐ空にする関数(JSでのメッセージ表示に使用)
function getSessionFlash($key) {
  if(!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//GETパラーメータ付与
function appendGetParam($arr_del_key = array()) {
  if(!empty($_GET)) {
    $str = '?';
    foreach($_GET as $key => $val) {
      //配列に値がない場合
      if(!in_array($key, $arr_del_key, true)) {
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
 }
}

//今日の日付を表示
function displayToday() {
  $w = date('w'); //曜日番号を取得
  $dayOfTheWeek = array('日','月','火','水','木','金','土');
  echo date('Y年n月j日').$dayOfTheWeek[$w].'曜日';
}

//認証キー生成
function makeRandKey($length = 8) {
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}