<?php
/**
 * SQLiteArray.php
 *
 * @author do_aki <do.hiroaki@gmail.com>
 * @license MIT License
 *
 * Copyright (c) 2011 do_aki <do.hiroaki@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * SQLite を用いた配列クラス
 *
 * 一時的な単一のSQLiteファイルに書き出すようになっている。
 * 基本的な動作は PHP の array を模倣。
 *
 * @author do_aki <do.hiroaki@gmail.com>
 */
class SQLiteArray implements Countable, ArrayAccess, Iterator {

    /**
     * @const string テーブル名に共通してつける接頭語
     */
    const TABLE_PREFIX = 'a_';

    /**
     * @var SqliteArray ルートオブジェクトを保持する変数
     *      ルートオブジェクトは、初めて SqliteArray を使ったときに生成され、
     *      プログラムの終了時に後始末をするためのオブジェクト
     *      このオブジェクトにより、リソースの破棄が行われる
     */
    private static $root_object;

    /**
     * @var integer SQLite 配列を生成する度にインクリメントされるカウンタ。テーブル名に利用される。
     */
    private static $object_counter = 0;

    /**
     * @var PDO SQLiteに接続するための PDO オブジェクト
     */
    private static $sqlite;

    /**
     * @var string 一時的に作成されるファイル名
     */
    private static $file_name;

    /**
     * @var string 一時ファイルを作成するディレクトリ
     */
    private static $temporary_directory;

    /**
     * @var テーブル名
     */
    private $table_name;

    /**
     * @var integer 次に挿入される数字添え字
     */
    private $current_index = 0;

    /**
     * @var integer 保持している項目数
     *         使う度、COUNT クエリを投げても良いけど、よく使うと思うのでオブジェクト側に保持する
     */
    private $count = 0;

    /**
     * @var PDOStatement イテレータとして利用されるステートメントハンドル
     */
    private $iterator;

    /**
     * @var array イテレーション時の現在値
     */
    private $iterator_current;

    /**
     * @var mixed 最後に find されたキーを保持
     */
    private $last_find_key;

    /**
     * @var mixed 最後に find されたキーの値を保持
     */
    private $last_find_value;

    /**
     * コンストラクタ
     *
     * @param array $array 初期化に用いる配列
     *
     */
    public function __construct($array=null) {
        self::_prepareObject($this);

        if (!isset($this->table_name)) {
            // ルートオブジェクトのコンストラクタ
            if (!isset(self::$temporary_directory)) {
                self::$temporary_directory = sys_get_temp_dir();
            }
            self::$file_name = tempnam(self::$temporary_directory, __CLASS__);
            self::$sqlite = new PDO("sqlite:".self::$file_name);
            self::$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$sqlite->beginTransaction();
            return;
        }

        // 通常オブジェクトのコンストラクタ
        self::$sqlite->exec("CREATE TABLE `{$this->table_name}` (`key` PRIMARY KEY, `value`)");
        if (isset($array) && is_array($array)) {
            foreach ($array as $key => $value) {
                $this->_insert($key, $value);
            }
        }
    }

    /**
     * デストラクタ
     *
     * @return void
     */
    public function __destruct() {
        if (!isset($this->table_name)) {
            // ダミーオブジェクトのデストラクタ＝プログラムの終了
            self::$sqlite = null;
            unlink(self::$file_name);
            self::$file_name = null;
            return;
        }

        // 通常オブジェクトのデストラクタ
        self::$sqlite->exec("DROP TABLE `{$this->table_name}`");
    }

    /**
     * 値を追加
     *  $array[] = $value を模倣
     *
     * @param mixed $value
     * @return integer 配列の要素数
     */
    public function push($value) {
        $this->_insert($this->_increaseIndex(), $value);
        return $this->count;
    }

    /**
     * 値を設定
     *  $array[$key] = $value を模倣
     *
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value) {
        $key = self::_convertKey($key);
        if ($this->_find($key)) {
            $this->_update($key, $value);
        } else {
            $this->_insert($key, $value);
            $this->_updateIndex($key);
        }
        return $value;
    }

    /**
     * キーのみからなる SqliteArray を返す
     *
     * @return SqliteArray
     */
    public function keys() {
        $new = new self();

        $index = 0;
        $sth = $this->_executeQuery("SELECT `{$this->table_name}`.`key` FROM `{$this->table_name}`");
        
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $new[$index++] = self::_convertKey($row[0]);
        }

        $sth->closeCursor();
        return $new;
    }

    /**
     * 値のみからなる SqliteArray を返す
     *
     * @return SqliteArray
     */
    public function values() {
        $new = new self();

        $index = 0;
        $sth = $this->_executeQuery("SELECT `{$this->table_name}`.`value` FROM `{$this->table_name}`");
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $new[$index++] = unserialize($row[0]);
        }

        $sth->closeCursor();
        return $new;
    }

    /**
     * 値を取得
     *
     * @param mixed $key
     * @return mixed
     */
    public function get($key) {
        $key = self::_convertKey($key);
        $val = $this->_find($key);
        if ($val) {
            return $val[0];
        }

        return null;
    }

    /**
     * 値を削除
     *
     * SQLite は、削除行については削除されたマークをつけるだけなので、
     * この後に挿入したとしても順序が問題になることはない
     * オートバキュームは、駄目、絶対.
     *
     * @param $key
     * @return boolean 削除した場合
     */
    public function remove($key) {
        $this->_delete(self::_convertKey($key));
    }

    /**
     * array_merge の挙動を模倣
     * 但し、破壊的
     *
     * @param $array
     * @return SqliteArray 自身を返す
     */
    public function merge($array) {
        foreach ($array as $key => $value) {
            if (self::_isNumericKey($key)) {
                $this->push($value);
            } else {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * + 演算子の挙動を模倣
     * 但し、破壊的
     *
     * @param $array
     * @return void
     */
    public function plus($array) {
           foreach ($array as $key => $value) {
            if (!$this->_find($key)) {
                $this->_insert($key, $value);
            }
        }
    }

    /**
     * 値で配列を検索し、見つかった場合、対応するキーを返す
     *
     * array_search の模倣
     * 但し、 strict検索のみしか行えない
     *
     * @param $value
     * @return mixed 配列のキーまたは false
     */
    public function searchValue($value) {
        $sql = "SELECT `key` FROM `{$this->table_name}` WHERE `value` = ? LIMIT 1";
        $sth = $this->_executeQuery($sql, array(serialize($value)));
        $ret = $sth->fetch(PDO::FETCH_NUM);
        $sth->closeCursor();
        if ($ret) {
            return self::_convertKey($ret[0]);
        }
        return false;
    }

    /**
     * キーを基準に、共通する項目のSQLite 配列を返す
     *
     * array_intersect_key っぽいもの
     * 非破壊。
     *
     * @param SqliteArray or array $rhs
     * @return SqliteArray
     */
    public function intersectKey($rhs) {
        if ($rhs instanceof self) {
            return $this->_intersectKey($rhs);
        } elseif (is_array($rhs)) {
            return $this->_intersectKey_array($rhs);
        }
        return null;
    }

    /**
     * SQLite配列の中身（テーブル）の全てを array にして返す
     * 量が多い場合はメモリに乗り切らない可能性もあるので注意
     *
     * @return array
     */
    public function toArray() {
        $array = array();
        $sth = $this->_executeQuery("SELECT `key`,`value` FROM `{$this->table_name}`");
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $array[$row[0]] = unserialize($row[1]);
        }
        $sth->closeCursor();
        return $array;
    }

    /**
     * toArray の別名
     *
     * @return array
     */
    public function getArray() {
        return $this->toArray();
    }

    /**
     * LoadFile で復元できるようにファイルに保存
     *
     * @param $file_name  保存先ファイル名
     * @param $table_name 保存先テーブル名（任意）
     * @return void
     */
    public function saveFile($file_name, $table_name=__CLASS__) {
        $sqlite = new PDO("sqlite:{$file_name}");
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sqlite->exec("CREATE TABLE `{$table_name}` (`key` PRIMARY KEY, `value`)");
        $sqlite->beginTransaction();
        $insert_sth = $sqlite->prepare("INSERT INTO `{$table_name}` (`key`,`value`) VALUES (?,?)");

        $select_sth = $this->_executeQuery("SELECT `key`,`value` FROM `{$this->table_name}`");
        while ($row = $select_sth->fetch(PDO::FETCH_NUM)) {
            $insert_sth->execute(array($row[0], $row[1]));
        }
        $select_sth->closeCursor();

        $sqlite->commit();
        $sqlite = null;
    }

    /**
     * SaveFile で保存したファイルから復元
     *
     * @param $file_name   保存されているファイル名
     * @param $table_name  保存先テーブル名（SaveFile メソッドで指定した場合は必要）
     * @return void
     */
    public function loadFile($file_name, $table_name=__CLASS__) {
        $this->_clear();

        $sqlite = new PDO("sqlite:{$file_name}");
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $select_sth = $sqlite->query("SELECT `key`,`value` FROM `{$table_name}`");

        $insert_sth = self::$sqlite->prepare("INSERT INTO `{$this->table_name}` (`key`,`value`) VALUES (?,?)");

        while ($row = $select_sth->fetch(PDO::FETCH_NUM)) {
            $insert_sth->execute(array($row[0], $row[1]));
        }
        $select_sth->closeCursor();

        $sqlite = null;
    }

    /**
     * Countable の実装
     *
     * @return integer 配列要素数
     */
    public function count() {
        return $this->count;
    }

    /**
     * ArrayAccess の実装 (存在確認)
     *
     * @param $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        $val = $this->_find(self::_convertKey($offset));
        return ($val) ? $val[0] : null;
    }

    /**
     * ArrayAccess の実装 （値取得）
     *
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * ArrayAccess の実装 （値設定）
     *
     * $array[null] も、 $array[] も 区別できない(どちらの場合も、$offset は null で渡ってくる)
     * ネイティブ array に対する null 添字は 空文字と同義。なので、 $array[null] のときだけはネイティブ array と挙動が異なる事に注意。
     * 今後どうなるんだろうねぇ。
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        if (null === $offset) {
            $this->push($value);
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * ArrayAccess の実装 （値削除）
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    /**
     * Iterator の実装(現在の値を取得)
     *
     * @return mixed 現在の値
     */
    public function current() {
        return unserialize($this->iterator_current['value']);
    }

    /**
     * Iterator の実装 （イテレータを次へ進める）
     *
     * @return void
     */
    public function next() {
        $this->iterator_current = $this->iterator->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Iterator の実装 （現在のキーを取得）
     *
     * @return mixed 現在のキー
     */
    public function key() {
        return self::_convertKey($this->iterator_current['key']);
    }

    /**
     * Iterator の実装 （次に取得する値が有効かどうか）
     *
     * @return boolean 次に取得する値が有効なら true, そうでないなら false
     */
    public function valid() {
        if (!$this->iterator_current) {
            $this->iterator->closeCursor();
            $this->iterator = null;
            return false;
        }
        return true;
    }

    /**
     * Iterator の実装 （イテレータを最初に戻す）
     */
    public function rewind() {
        if (isset($this->iterator)) {
            $this->iterator->closeCursor();
        }

        $this->iterator = $this->_executeQuery("SELECT `key`,`value` FROM `{$this->table_name}`");
        $this->iterator_current = $this->iterator->fetch(PDO::FETCH_ASSOC);
    }

    /*
     * 一時ファイルを格納するディレクトリを指定
     *
     * 最初コンストラクタが実行される前のみ有効
     *
     * @param string $dirname 一時ファイルを格納するディレクトリ
     * @return void
     */
    public static function setTemporaryDirectory($dirname) {
        self::$temporary_directory = $dirname;
    }

    /**
     * 対 SQLite 配列版 intersectKey
     *
     * @param SqliteArray $rhs
     * @return SqliteArray
     */
    private function _intersectKey($rhs) {
        $new = new self();

        $this->_executeQuery(
            "INSERT INTO {$new->table_name} (`key`,`value`)"
            ." SELECT `{$this->table_name}`.`key`, `{$this->table_name}`.`value` FROM `{$this->table_name}`, `{$rhs->table_name}`"
            ." WHERE `{$this->table_name}`.`key` = `{$rhs->table_name}`.`key`"
        );

        return $new;
    }

    /**
     * 対 array 版 intersectKey
     * @param array $rhs
     * @return SqliteArray
     */
    private function _intersectKey_array($rhs) {
        $new = new self();

        $rhs_keys = array();
        foreach ($rhs as $k => $v) {
            $rhs_keys[] = self::_convertKey($k);
        }

        $this->_executeQuery(
            "INSERT INTO {$new->table_name} (`key`,`value`)"
            ." SELECT `key`, `value` FROM `{$this->table_name}`"
            ." WHERE `key` IN (". implode(',', array_fill(0, count($rhs_keys), '?')) .")"
            , $rhs_keys
        );

        return $new;
    }

    /**
     * クエリの実行
     *
     * @param string $sql    実行するクエリ
     * @param array  $params バインディングパラメタ
     * @return PDOStatement 実行済みステートメントハンドル
     */
    private function _executeQuery($sql, $params = null) {
        $sth = self::$sqlite->prepare($sql);
        $ret = $sth->execute($params);
        return $sth;
    }

    /**
     * 次に挿入すべき数添え字を返してインクリメント
     *
     * @return integer 次に挿入すべき数添え字
     */
    private function _increaseIndex() {
        return $this->current_index++;
    }

    /**
     * $key をキーとして追加するとして、数添え字を更新する
     *
     * @param $key
     * @return void
     */
    private function _updateIndex($key) {
        if (is_int($key) && $this->current_index < $key) {
            $this->current_index = 1+$key;
        }
    }

    /**
     * キーに対応する値を返す
     * null値にも対応するため、値を配列でラップしたものを返す
     *
     * @param mixed $key キー
     * @return 値 がある場合、 array(値) 無い場合は false
     */
    private function _find($key) {
        if ($key === $this->last_find_key) {
            return $this->last_find_value;
        }

        $sth = $this->_executeQuery("SELECT `value` FROM `{$this->table_name}` WHERE `key` = ? LIMIT 1", array($key));
        $result = $sth->fetch(PDO::FETCH_NUM);
        if ($result) {
            $result = array(unserialize($result[0]));
        }
        $sth->closeCursor();

        $this->last_find_key   = $key;
        $this->last_find_value = $result;
        return $result;
    }

    /**
     * 値を追加
     *
     * @param $key
     * @param $value
     * @return void
     */
    private function _insert($key, $value) {
        $value = serialize($value);
        $this->_executeQuery("INSERT INTO `{$this->table_name}` (`key`,`value`) VALUES (?,?)", array($key, $value));
        ++$this->count;

        $this->last_find_key = null;
    }

    /**
     * 値を更新
     *
     * @param $key
     * @param $value
     * @return void
     */
    private function _update($key, $value) {
        $value = serialize($value);
        $this->_executeQuery("UPDATE `{$this->table_name}` SET `value`= ? WHERE `key` = ?", array($value, $key));

        $this->last_find_key = null;
    }

    /**
     * 値を削除
     *
     * @param $key
     * @param $value
     * @return void
     */
    private function _delete($key) {
        $this->_executeQuery("DELETE FROM `{$this->table_name}` WHERE `key` = ?", array($key));
        --$this->count;

        $this->last_find_key = null;
    }

    /**
     * 全データを削除(TRUNCATEに相当)
     *
     * @return void
     */
    private function _clear() {
        // SQLite には TRUNCATE が存在しないので、 DELETE で代用する
        $this->_executeQuery("DELETE FROM `{$this->table_name}`");
        $this->count = 0;

        $this->last_find_key = null;
    }

    /**
     * オブジェクト生成の準備を行う
     *
     * !コンストラクタで必ず呼ばなければならない!
     *  テーブル名の設定と、プログラムの最後にリソースを回収するための仕掛け(ルートオブジェクトの保持)を施している
     *
     * @param SqliteArray $self
     * @return void
     */
    private static function _prepareObject($self) {
        if (!isset(self::$root_object)) {
            self::$root_object = false;
            new self(); // ルートオブジェクトの生成 (コンストラクタを介して (*1) へ)
        } elseif (!self::$root_object) {
            // ルートオブジェクトの登録 (*1)
            self::$root_object = $self;
            return;
        }

        // 通常オブジェクトとしての登録
        $self->table_name = self::TABLE_PREFIX . ++self::$object_counter;
    }

    /**
     * 配列の添え字として有効なキーを返す
     *
     * @param $key
     * @return integer or string
     */
    private static function _convertKey($key) {
        if (self::_isNumericKey($key)) {
            $key = intval($key);
        }
        return $key;
    }

    /**
     * 数添え字として見なせるか判別
     *
     * @param mixed $key 添え字
     * @return boolean
     */
    private static function _isNumericKey($key) {
        return is_int($key) || is_bool($key) || is_float($key) || preg_match('/\A(:?0|[1-9][0-9]*)\Z/', $key);
    }
}
