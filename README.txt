ModuleName : SqliteArray
Author     : do_aki
License    : MIT License
Status     : alpha (for developer)

================
これはなに？
================

SQLite をバックエンドに利用している、 PHP ネイティブ array の模倣クラス
ウリは、メモリあふれを気にせずにばんばんデータ突っ込めるくらい。
使いどころがあるのかは分からない。

sys_get_temp_dir 使ってるので、そのままだと 5.2.1 以上が必須。
5.2.1 未満の場合は、オブジェクトを new する前に、

  SqliteArray::setTemporaryDirectory('/tmp');

とかするといいんじゃないかな。

================
使い方
================

* ほとんど、 array と同じように利用出来ます

  $a = new SqliteArray();
  $a[] = 1;
  $a['X'] = 2;
  $a[10] = 3;
  foreach ($a as $k => $v) {
    var_dump($k, $v);
  }

* array をコンストラクタに渡して初期化することもできます

  $a = new SqliteArray(array(0=>1, 'X'=>2, 10=>3));

* array に変換するときは、 toArray を利用します

  $a = new SqliteArray(array(1,2,3));
  var_dump($a->toArray());  // array(0=>1, 1=>2, 2=>3)

* isset / unset もちろん使えます。

  isset($a[0]);
  unset($a[0]);

* array_xxx 系関数の引数としては使えませんが、いくつかは対応するメソッドを用意してあります。
　気が向いたら他の関数にも対応する

  array_keys -> keys
  array_values ->values
  array_merge -> merge
  array_intersect_key -> intersectKey

* saveFile / loadFile で永続化できます

  $a = new SqliteArray(array(1, 2, 3));
  $a->saveFile($filename);
  
  $b = new SqliteArray();
  $b->loadFile($filename);
  var_dump($b->toArray());  // array(1,2,3)


