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
$password_confirmation = get_post('password_confirmation');

$db = get_db_connect();

//ユーザー情報の登録
try{
  //ユーザー登録の結果を代入　<user.php参照>
  $result = regist_user($db, $name, $password, $password_confirmation);
  if($result=== false){
    set_error('ユーザー登録に失敗しました。');
    redirect_to(SIGNUP_URL);
  }
}catch(PDOException $e){
  set_error('ユーザー登録に失敗しました。');
  redirect_to(SIGNUP_URL);
}

set_message('ユーザー登録が完了しました。');

//$nameと$passwordが合っていればセッションにuser_idを追加し$userを返す　失敗すればfalseを返す
login_as($db, $name, $password);

redirect_to(HOME_URL);