<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　ゲストログインページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

//ユーザーIDを格納
$_SESSION['user_id'] = GUEST;
//ログイン有効期限（デフォルトを1時間にする）
$_SESSION['login_limit'] = 60*60;
//最終ログイン日時を現在日時に
$_SESSION['login_date'] = time();

$_SESSION['msg_success'] = SUC06;
debug('ゲストとしてログインします。');
debug('セッション変数の中身:'.print_r($_SESSION,true));
debug('ノートページに遷移します。');
header('Location:note.php');

