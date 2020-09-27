<?php
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

// DB利用
//itemsテーブルから$item_idに一致する各種データを取得
function get_item($db, $item_id){
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = ?
  ";
  $param = [$item_id];
  //$sqlの内容を実行し、1行だけレコードを取得 <db.phpを参照>
  return fetch_query($db, $sql, $param);
}

//itemsテーブルから各種データを取得 
//$is_open === trueの時は公開しているものを取得
function get_items($db, $is_open = false){
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  if($is_open === true){
    $sql .= '
      WHERE status = 1
    ';
  }
  //$sqlの内容を実行し、全行のレコードを取得 <db.phpを参照>
  return fetch_all_query($db, $sql);
}

//itemsテーブルから全てのデータを取得 
function get_all_items($db){
  return get_items($db);
}

//itemsテーブルから公開しているデータのみを取得 
function get_open_items($db){
  return get_items($db, true);
}

//商品登録
function regist_item($db, $name, $price, $stock, $status, $image){
  //画像のアップロードが有効だった場合、ランダムなファイル名を返す
  $filename = get_upload_filename($image);
  //$name, $price, $stock, $filename, $statusの全てがTRUEの場合、TRUEを返す
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  //トランザクションを開始し、insert_itemを実行
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}


//トランザクションを開始し、insert_itemを実行
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  //トランザクション開始
  $db->beginTransaction();
  //itemsテーブルへの各種情報を追加 ・アップロードした画像の指定のフォルダへの保存がうまくいけばコミットしTRUEを返す　失敗すればrollbackし、FALSEを返す
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
  
}

//itemsテーブルに$name, $price, $stock, $filename, $statusを追加 成功すればTRUEを返す
function insert_item($db, $name, $price, $stock, $filename, $status){
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(?,?,?,?,?);
  ";
  $param = [$name, $price, $stock, $filename, $status_value];
  //SQLを実行  うまくいけばTRUEを返す<db.php参照>
  return execute_query($db, $sql, $param);
}
//商品ステータスの変更
function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";
  $param = [$status,$item_id];
  return execute_query($db, $sql, $param);
}

//在庫数の変更　成功すればTRUEを返す
function update_item_stock($db, $item_id, $stock){
  $sql = "
    UPDATE
      items
    SET
      stock = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";
  $param = [$stock,$item_id];
  return execute_query($db, $sql, $param);
}

//商品情報と画像データを消去
function destroy_item($db, $item_id){
  //itemsテーブルから$item_idに一致する各種データを取得
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  //トランザクションを開始　
  $db->beginTransaction();
  //$item_idの商品情報を削除 成功した場合TRUEを返す
  if(delete_item($db, $item['item_id'])
    //$filenameが存在した場合、ファイルを削除　成功した場合TRUEを返す<function.php 参照>
    && delete_image($item['image'])){
    //商品情報と画像データを両方消去できたらコミット処理
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
}

//$item_idの商品情報を削除
function delete_item($db, $item_id){
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = ?
    LIMIT 1
  ";
  $param = [$item_id];
  //クエリを実行 うまくいけばTRUEを返す<db.php参照>
  return execute_query($db, $sql, $param);
}


// 非DB
//$item['status']===1であればTRUEを返す
function is_open($item){
  return $item['status'] === 1;
}

//$name, $price, $stock, $filename, $statusの全てがTRUEの場合、TRUEを返す
function validate_item($name, $price, $stock, $filename, $status){
  
  $is_valid_item_name = is_valid_item_name($name);
  $is_valid_item_price = is_valid_item_price($price);
  $is_valid_item_stock = is_valid_item_stock($stock);
  $is_valid_item_filename = is_valid_item_filename($filename);
  $is_valid_item_status = is_valid_item_status($status);

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

//item_nameが正当であればTRUEを返す
function is_valid_item_name($name){
  $is_valid = true;
  //$nameが指定の文字数であればTRUEを返す。そうでなければエラーメッセージをセッションに追加しfalseを返す。<function.phpを参照> 
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

//item_priceが正当であればTRUEを返す
function is_valid_item_price($price){
  $is_valid = true;
  //$priceが正の整数であればTRUEを返す。そうでなければエラーメッセージをセッションに追加しfalseを返す。<function.phpを参照>
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//item_stockが正当であればTRUEを返す。そうでなければエラーメッセージをセッションに追加しfalseを返す。<function.phpを参照>
function is_valid_item_stock($stock){
  $is_valid = true;
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//item_filenameが正当であればTRUEを返す。そうでなければエラーメッセージをセッションに追加しfalseを返す。<function.phpを参照>
//$filename = get_upload_filename($image);画像のアップロードが有効だった場合、ランダムなファイル名を返す
function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

//$statusの値が1か０であればTRUEを返す。そうでなければFALSEを返す。
function is_valid_item_status($status){
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}

//購入履歴テーブルへの登録
function insert_history($db, $user_id){
  $sql = "
    INSERT INTO
      purchase_history(
        user_id
      )
    VALUES(?);
  ";
  $param = [$user_id];
  return execute_query($db, $sql, $param);
}

//購入詳細テーブルへの登録
function insert_details($db, $order_id, $item_id, $price, $amount){
  $sql = "
    INSERT INTO
      purchase_details(
        order_id,
        item_id,
        price,
        amount
      )
    VALUES(?,?,?,?)
  ";
  $param = [$order_id, $item_id, $price, $amount];
  return execute_query($db, $sql, $param);
}

//購入履歴取得
function get_history($db, $user_id){
  //管理者の時
  if($user_id == 4){
    $sql = "
      SELECT 
        purchase_history.order_id,
        purchase_datetime,
        sum(price*amount) as total
      FROM
        purchase_history
        INNER JOIN
        purchase_details
      ON
        purchase_history.order_id = purchase_details.order_id
      GROUP BY 
        purchase_history.order_id
      ORDER BY  
        order_id DESC;
    ";
    //$sqlの内容を実行し、1行だけレコードを取得 <db.phpを参照>
    return fetch_all_query($db, $sql);
  } 
  //一般ユーザーの時
  else{
    $sql = "
      SELECT 
        purchase_history.order_id,
        purchase_datetime,
        sum(price*amount) as total
      FROM
        purchase_history
        INNER JOIN
        purchase_details
      ON
        purchase_history.order_id = purchase_details.order_id
      WHERE 
        user_id = ?
      GROUP BY 
        purchase_history.order_id
      ORDER BY
        order_id DESC;
    ";
    //$sqlの内容を実行し、1行だけレコードを取得 <db.phpを参照>
    return fetch_all_query($db, $sql,[$user_id]);
  } 
}


//購入明細取得
function get_details($db, $order_id){
  $sql = "
  SELECT 
    purchase_details.price,
    amount,
    purchase_details.price*amount as sub_total,
    items.name
  FROM
    purchase_details
  INNER JOIN
    items
  ON
    purchase_details.item_id = items.item_id
  WHERE
    order_id=?
  ";
  //$sqlの内容を実行し、1行だけレコードを取得 <db.phpを参照>
  return fetch_all_query($db, $sql, [$order_id]);
}
