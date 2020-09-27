<?php

// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み。
require_once MODEL_PATH . 'item.php';
// cartデータに関する関数ファイルを読み込み。
require_once MODEL_PATH . 'cart.php';

//ログインチェックを行うため、セッションを開始
session_start();

//ログインチェック用関数を利用
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}
//PDOを取得
$db = get_db_connect();

//PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);


//購入履歴取得
$histories = get_history($db, $user['user_id']);

// 特殊文字をHTMLエンティティに変換
$histories = entity_assoc_array($histories);

// CSRFトークンの生成<function.php参照>
$token= get_csrf_token();

include_once '../view/history_view.php';