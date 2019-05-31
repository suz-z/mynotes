<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　ログアウト');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

debug('ログアウトします。');

//セッションを削除（ログアウトする）
session_destroy();

debug('トップページへ遷移します。');
//トップページへ繊維
header('Location:index.php');