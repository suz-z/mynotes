<?php

$cateData = getCateData($_SESSION['user_id']);

//get送信された場合
if(!empty($_GET)) {
  $searchCate = (isset($_GET['search_cate'])) ? $_GET['search_cate'] : '';
  $searchKey = (!empty($_GET['search_key'])) ? $_GET['search_key'] : '';
  $searchTag = (!empty($_GET['search_tag'])) ? implode(',', $_GET['search_tag']) : '';
  $searchSort = (!empty($_GET['search_sort'])) ? $_GET['search_sort'] : '';
  $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;

  //検索結果の元のページに戻るためのセッション処理
  $_SESSION['search_result'] = array('search_cate' => $searchCate, 'search_key' => $searchKey, 'search_tag' => $searchTag, 'search_sort' => $searchSort);
  
}


?>

<div class="sub-content">
  <div class="main-section date">
    <p class="display-today">
      <?php displayToday(); ?>
    </p>
  </div>
  <div class="main-section sidebar-fixed" id="search">
    <h2 class="section-title"><i class="fas fa-search"></i>メモを探す</h2>
    <form action="" medthod="get" class="local-form">
      <input type="text" name="search_key" placeholder="キーワードを入力" value="<?php if(!empty($searchKey)) echo $searchKey; ?>" class="input-box">
      <br>
      カテゴリ選択
      <select name="search_cate" id="" class="select-category sidebar-select-category">
        <option value="">すべてのカテゴリ</option>
        <?php foreach($cateData as $key => $val): ?>
        <option value="<?php echo $key; ?>" <?php if(($searchCate !='' ) && ($searchCate==$key)) echo 'selected' ; ?>>
          <?php echo sanitize($val['category_name']); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <br>
      <fieldset class="search-fieldset">
        <legend>タグ</legend>
        <div class="search-tags">
          <label class="tag-select">
            <input type="checkbox" class="tag-select-box" name="search_tag[]" value="1" <?php if(strpos($searchTag,'1') !==false) echo 'checked' ; ?>>
            <div class="tag tag1"></div>
          </label>
          <label class="tag-select">
            <input type="checkbox" class="tag-select-box" name="search_tag[]" value="2" <?php if(strpos($searchTag,'2') !==false) echo 'checked' ; ?>>
            <div class="tag tag2"></div>
          </label>
          <label class="tag-select">
            <input type="checkbox" class="tag-select-box" name="search_tag[]" value="3" <?php if(strpos($searchTag,'3') !==false) echo 'checked' ; ?>>
            <div class="tag tag3"></div>
          </label>
        </div>
      </fieldset>
      <fieldset class="search-fieldset">
        <legend>並び順</legend>
        <label><input type="radio" name="search_sort" value="new" <?php if($searchSort !=='old' ) echo 'checked' ; ?>>日付の新しい順</label><br>
        <label><input type="radio" name="search_sort" value="old" <?php if($searchSort==='old' ) echo 'checked' ; ?>>日付の古い順</label>
      </fieldset>
      <input type="submit" value="検索する" class="btn">
    </form>
  </div>

</div>
