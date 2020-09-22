<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';

session_start();

//POSTで送られたtokenを取得
$token = get_post('token');

// トークンのチェック<function.php参照>
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです。');
  redirect_to(LOGIN_URL);
}

//CSRFセッションを空にする
set_session('csrf_token', array());

if(is_logined() === true){
  redirect_to(HOME_URL);
}

$name = get_post('name');
$password = get_post('password');

$db = get_db_connect();

//$nameと$passwordが合っていればセッションにuser_idを追加し$userを返す　失敗すればfalseを返す <user.phpを参照>
$user = login_as($db, $name, $password);
//user情報を取得できなかった時
if( $user === false){
  set_error('ログインに失敗しました。');
  redirect_to(LOGIN_URL);
}

set_message('ログインしました。');
//管理者であれば管理用ページにリダイレクト
if ($user['type'] === USER_TYPE_ADMIN){
  redirect_to(ADMIN_URL);
}
//一般userであればショッピングページにリダイレクト
redirect_to(HOME_URL);