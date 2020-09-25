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

//POSTで送られたtokenを取得
$token = get_post('token');

// トークンのチェック<function.php参照>
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです。');
  redirect_to(LOGIN_URL);
}

//CSRFセッションを空にする
set_session('csrf_token', array());

//ログインチェック用関数を利用
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}
//PDOを取得
$db = get_db_connect();

//PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);


//cartsテーブルにitemsテーブルを結合し、各種データを取得 <cart.phpを参照>
$carts = get_user_carts($db, $user['user_id']);


//購入履歴、購入明細テーブルへの登録および購入処理
$db->beginTransaction();
try{
  //購入履歴テーブルに登録
  if(insert_history($db, $user['user_id']) === FALSE){
    set_error('購入履歴を更新できませんでした。');
  }
  $order_id = $db->lastInsertId('order_id');

  foreach($carts as $cart){
    $item_id = $cart['item_id'];
    $amount = $cart['amount'];
    //itemsテーブルから$item_idに一致する各種データを取得
    $item = get_item($db, $item_id);
    $price = $item['price'];
    $price = $item['price'];
    //購入明細テーブルに登録
    if(insert_details($db, $order_id, $item_id, $price, $amount) === false){
      set_error('購入明細を更新できませんでした。');
    }
  }  
  //購入処理
  if(purchase_carts($db, $carts) === false){
    set_error('商品が購入できませんでした。');
  } 
  //エラーがない時はコミット
  if(has_error() === FALSE){
    $db->commit();
  }
}
catch(PDOException $e){
  $dbh->rollback();
  echo 'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
  redirect_to(CART_URL);
}

//カートに入っている合計金額を算出
$total_price = sum_carts($carts);

//htmlエスケープ処理 <function.php参照>
$carts = entity_assoc_array($carts);

include_once '../view/finish_view.php';