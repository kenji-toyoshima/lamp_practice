<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入履歴</title>
  <link rel="stylesheet" href="<?php print(STYLESHEET_PATH . 'admin.css'); ?>">
</head>
<body>
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
  <h1>購入明細</h1>
  <p>注文番号：<?php print $order_id; ?></p>
  <p>購入日時:<?php print($histories[0]['purchase_datetime']);?></p>
  <p>合計金額:<?php print number_format($histories[0]['total']);?>円</p>

  <div class="container">
    <?php include VIEW_PATH . 'templates/messages.php'; ?>

    <?php if(count($details) > 0){ ?>
      <table class="table table-bordered">
        <thead class="thead-light">
          <tr>
            <th>商品名</th>
            <th>購入時の商品価格</th>
            <th>購入数</th>
            <th>小計</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($details as $detail){ ?>
          <tr>
            <td><?php print($detail['name']); ?></td>
            <td><?php print number_format($detail['price']); ?>円</td>
            <td><?php print($detail['amount']); ?>個</td>
            <td><?php print number_format($detail['sub_total']); ?>円</td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>購入明細はありません。</p>
    <?php } ?> 
  </div>
</body>
</html>