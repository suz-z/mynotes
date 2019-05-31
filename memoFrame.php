<?php

$cateData = getCateData($_SESSION['user_id']);

//削除ボタンが押された時
if(!empty($_POST['delete'])) {
  debug('削除ボタンがおされました。');
  deleteData($_POST['memo_id']);
}
//編集ボタンを押された時
if(!empty($_POST['edit'])) {
  debug('編集ボタンが押されました。');
  $memoId = $_POST['memo_id'];
  debug('編集されるメモID:'.$_POST['memo_id']);
  
  $_SESSION['search_param'] = $_SERVER['REQUEST_URI'];//あとから同じページに戻ってくるために
  
  header("Location:edit.php?memo_id=".$memoId);
}

?>

 <script>
  //削除ボタンを押した時の確認ダイアログ
    function submitDel() {
      // 確認ダイアログ表示
      var flag = confirm("本当に削除しますか？\n\n削除したくない場合は【キャンセル】ボタンを押して下さい");
      // flg が TRUEなら送信、FALSEなら送信しない
      return flag;
    }
</script>

<div class="memo-container">
  <?php foreach($searchMemoData['data'] as $val): ?>
  <div class="memo-frame-main">
    <div class="memo-frame-header">
      <div class="category <?php if($val['category_name'] === '') echo 'no-category'; ?>">
        <?php
          if(($val['category_name']) !== '') {
            echo sanitize($val['category_name']);
          } else {
            echo 'カテゴリなし';
          }
        ?>
      </div>
      <div class="tags">
        <?php if(strpos($val['tags'], '1') !== false) echo '<div class="tag tag1"></div>'; ?>
        <?php if(strpos($val['tags'], '2') !== false) echo '<div class="tag tag2"></div>'; ?>
        <?php if(strpos($val['tags'], '3') !== false) echo '<div class="tag tag3"></div>'; ?>
      </div>
    </div>
    <p class="content-output"><?php echo sanitize($val['content']); ?></p>
    <p class="display-date">
      <?php echo '作成:'.sanitize($val['create_date']).' / 更新:'.sanitize($val['update_date']); ?>
    </p>
  </div>
  <div class="memo-frame-sub">

    <form action="" method="post">
      <input type="hidden" name="memo_id" class="memo-id" value="<?php if(!empty($val['memo_id'])) echo sanitize($val['memo_id']); ?>">
      <input type="submit" name="edit" class="memo-btn edit" value="編集">
    </form>
    <form action="" method="post" onsubmit="return submitDel()">
      <input type="hidden" name="memo_id" class="memo-id" value="<?php echo sanitize($val['memo_id']); ?>">
      <input type="submit" name="delete" class="memo-btn delete" value="削除">
    </form>
  </div>
  <?php endforeach; ?>
</div>
