-- 購入履歴テーブル
CREATE TABLE purchase_history (
  order_id INT(11) AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  purchase_datetime DATETIME,
  primary key(order_id)
);

-- 購入明細テーブル
CREATE TABLE purchase_details (
  order_id INT(11) NOT NULL,
  item_id INT(11) NOT NULL,
  price INT(11) NOT NULL,
  amount INT(11) NOT NULL,
);