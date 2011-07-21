ModuleName : SqliteArray
Author     : do_aki
License    : MIT License
Status     : alpha (for developer)

================
これはなに？
What this.
================

SQLite をバックエンドに利用している、 PHP ネイティブ array の模倣クラス
ウリは、メモリあふれを気にせずにばんばんデータ突っ込めるくらい。
使いどころがあるのかは分からない。

A module of PHP, emulate primitive array class using SQLite.
You can add data as memory over flow doesn't warry.

sys_get_temp_dir 使ってるので、そのままだと 5.2.1 以上が必須。
回避したい場合は、オブジェクトを new する前に、setTemporaryDirectory を呼べばいい
(そうすれば 5.2.1 未満でも動くんじゃないかな)

  例) SqliteArray::setTemporaryDirectory('/tmp');

Require php version 5.2.1 because using sys_get_temp_dir function.
If you avoid this limit, call setTemporaryDirectory static function before new.
(maybe work less than version 5.2.1)

  Ex) SqliteArray::setTemporaryDirectory('/tmp');

================
使い方
Usage
================

* ほとんど、 array と同じように利用出来ます
* You can use this module like primitive array.

  $a = new SqliteArray();
  $a[] = 1;
  $a['X'] = 2;
  $a[10] = 3;
  foreach ($a as $k => $v) {
    var_dump($k, $v);
  }

* array をコンストラクタに渡して初期化することもできます
* You can construct a newly object that represents the specified primitive array.

  $a = new SqliteArray(array(0=>1, 'X'=>2, 10=>3));

* array に変換するときは、 toArray を利用します
* Call toArray method when you need primitive array.

  $a = new SqliteArray(array(1,2,3));
  var_dump($a->toArray());  // array(0=>1, 1=>2, 2=>3)

* isset / unset もちろん使えます。
* Of course you can use isset and unset.

  isset($a[0]);
  unset($a[0]);

* array_xxx 系関数の引数としては使えませんが、いくつかは対応するメソッドを用意してあります。
　(気が向いたら他の関数にも対応する)
* Cannot use array_xxx function, but SqliteArray has several corresponding methods.

  array_keys -> keys
  array_values ->values
  array_merge -> merge
  array_intersect_key -> intersectKey

* saveFile / loadFile で永続化 / 復元できます
* SqliteArray can be serialized and deserialized by methods saveFile and loadFile.

  $a = new SqliteArray(array(1, 2, 3));
  $a->saveFile($filename);
  
  $b = new SqliteArray();
  $b->loadFile($filename);
  var_dump($b->toArray());  // array(1,2,3)

================
最後に
P.S.
================
ダメダメな英語力で英訳してみました。
英語力ある人からの突っ込み求む。

Please say, if English translation was broken.

