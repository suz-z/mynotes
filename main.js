'use strict';

const MSG_TEXTAREA_MAX = '(1000文字以内)';
const MSG_TEXTAREA_MAX_ERR = '1000文字以内で入力してください';
const MSG_TEXT_MAX_ERR = '255文字以内で入力してください';
const MSG_CATE_MAX = '(20文字以内)';
const MSG_CATE_MAX_ERR = '20文字以内で入力してください';

//処理完了時のメッセージ表示
let jsMsg = document.getElementById('js-msg');

//.js-msgに文字が入っていれば表示
if (jsMsg && jsMsg.innerHTML.trim()) {
  jsMsg.style.display = 'block';
  setTimeout('jsMsg.style.display = "none"', 2900);
} else {
  // console.log('false');
}


//バリデーションチェック（setting.phpのカテゴリー名）
for(let i = 0; i <= 4; i++) {
  let cate = document.getElementsByClassName('js-cate');
  let cateMsg = document.getElementsByClassName('js-cate-msg');
  // console.dir(cate);
  if (cate[i]) {
    cate[i].addEventListener('keyup', function (e) {
      let cateLength = this.value.length;
      if (cateLength <= 20) {
        cateMsg[i].textContent = MSG_CATE_MAX;
        cateMsg[i].classList.remove('err');
      } else {
        cateMsg[i].textContent = MSG_CATE_MAX_ERR;
        cateMsg[i].classList.add('err');
      };
    }, false);
  }

}

//バリデーションチェック(note.php、edit.phpのメモ内容)
let write = document.getElementById('js-write');
let writeMsg = document.getElementById('js-write-msg');
if (write) {
  write.addEventListener('keyup', function (e) {
    let writeLength = write.value.length;
    if (writeLength <= 1000) {
      writeMsg.textContent = MSG_TEXTAREA_MAX;
      writeMsg.classList.remove('err');
    } else {
      writeMsg.textContent = MSG_TEXTAREA_MAX_ERR;
      writeMsg.classList.add('err');
    };
  }, false);
}


