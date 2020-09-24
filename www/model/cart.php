<?php 
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

//cartsテーブルにitemsテーブルを結合し、各種データを取得　成功したらTRUEを返す
function get_user_carts($db, $user_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
  ";
  $param = [$user_id];
  // <db.php参照>
  return fetch_all_query($db, $sql, $param);
}

//cartsテーブルにitemsテーブルを結合し、各種データを1行分だけ取得　成功したらTRUEを返す
function get_user_cart($db, $user_id, $item_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
    AND
      items.item_id = ?
  ";

  $param = [$user_id,$item_id];
  return fetch_query($db, $sql, $param);

}

//cartに商品を追加
function add_cart($db, $user_id, $item_id ) {
  //cartsテーブルにitemsテーブルを結合し、各種データを1行分だけ取得　成功したらTRUEを返す
  $cart = get_user_cart($db, $user_id, $item_id);
  //$cartが取得できなかった場合、cartsテーブルにitem_id,user_id,amount=1を追加　
  if($cart === false){
    return insert_cart($db, $user_id, $item_id);
  }
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

//cartsテーブルにitem_id,user_id,amount=1を追加　成功した場合はTRUEを返す cart_idはAUTO INCREMENT
function insert_cart($db, $user_id, $item_id, $amount = 1){
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES(?,?,?)
  ";
  $param = [$item_id,$user_id,$amount];
  return execute_query($db, $sql, $param);
}

//cartsテーブルのamountを変更　成功すればTRUEを返す
function update_cart_amount($db, $cart_id, $amount){
  $sql = "
    UPDATE
      carts
    SET
      amount = ?
    WHERE
      cart_id = ?
    LIMIT 1
  ";
  $param = [$amount,$cart_id];
  return execute_query($db, $sql, $param);
}

//cartsテーブルから引数の$cart_idに一致する商品を削除 成功すればTRUEを返す
function delete_cart($db, $cart_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = ?
    LIMIT 1
  ";
  $param = [$cart_id];
  return execute_query($db, $sql, $param);
}

//カートにある商品を購入し、cartsテーブルから購入した商品を削除
function purchase_carts($db, $carts){
  //商品購入手続きでエラーが発生しないかを確認し、エラーがある場合はfalseを返す
  if(validate_cart_purchase($carts) === false){
    return false;
  }
  foreach($carts as $cart){
    //在庫数の変更が失敗すれば
    if(update_item_stock(
        $db, 
        $cart['item_id'], 
        $cart['stock'] - $cart['amount']
      ) === false){
      //$_SESSION['__errors']配列にエラー内容を追加
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  //cartsテーブルから$cart[0]['user_id']に一致する商品を削除
  delete_user_carts($db, $carts[0]['user_id']);
}

//cartsテーブルから$user_idに一致する商品を削除
function delete_user_carts($db, $user_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = ?
  ";
  $param = [$user_id];
  execute_query($db, $sql, $param);
}

//購入合計金額を求める
function sum_carts($carts){
  $total_price = 0;
  foreach($carts as $cart){
    $total_price += $cart['price'] * $cart['amount'];
  }
  return $total_price;
}

//商品購入に手続きでエラーが発生しないかを確認
function validate_cart_purchase($carts){
  //cartに商品が入っていなければ__errors sessionに追加しfalseを返す
  if(count($carts) === 0){
    set_error('カートに商品が入っていません。');
    return false;
  }
  //$carts:cartsテーブルにitemsテーブルを結合し各種データを取得した結果　
  foreach($carts as $cart){
    //$item['status']===1であればTRUEを返す <item.phpを参照>
    if(is_open($cart) === false){
      set_error($cart['name'] . 'は現在購入できません。');
    }
    if($cart['stock'] - $cart['amount'] < 0){
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  
  //エラーセッションに値がある時はfalseを返す<function.phpを参照>
  if(has_error() === true){
    return false;
  }
  return true;
}

