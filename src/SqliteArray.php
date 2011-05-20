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
 * SQLite ���Ѥ������󥯥饹
 *
 * ���Ū��ñ���SQLite�ե�����˽񤭽Ф��褦�ˤʤäƤ��롣
 * ����Ū��ư��� PHP �� array �����
 *
 * @author do_aki <do.hiroaki@gmail.com>
 */
class SQLiteArray implements Countable, ArrayAccess, Iterator {

    /**
     * @const string �ơ��֥�̾�˶��̤��ƤĤ�����Ƭ��
     */
    const TABLE_PREFIX = 'a_';

    /**
     * @var SqliteArray �롼�ȥ��֥������Ȥ��ݻ������ѿ�
     *      �롼�ȥ��֥������Ȥϡ����� SqliteArray ��Ȥä��Ȥ����������졢
     *      �ץ����ν�λ���˸�����򤹤뤿��Υ��֥�������
     *      ���Υ��֥������Ȥˤ�ꡢ�꥽�������˴����Ԥ���
     */
    private static $root_object;

    /**
     * @var integer SQLite ��������������٤˥��󥯥���Ȥ���륫���󥿡��ơ��֥�̾�����Ѥ���롣
     */
    private static $object_counter = 0;

    /**
     * @var PDO SQLite����³���뤿��� PDO ���֥�������
     */
    private static $sqlite;

    /**
     * @var string ���Ū�˺��������ե�����̾
     */
    private static $file_name;

    /**
     * @var string ����ե�������������ǥ��쥯�ȥ�
     */
    private static $temporary_directory;

    /**
     * @var �ơ��֥�̾
     */
    private $table_name;

    /**
     * @var integer ����������������ź����
     */
    private $current_index = 0;

    /**
     * @var integer �ݻ����Ƥ�����ܿ�
     *         �Ȥ��١�COUNT ��������ꤲ�Ƥ��ɤ����ɡ��褯�Ȥ��Ȼפ��Τǥ��֥�������¦���ݻ�����
     */
    private $count = 0;

    /**
     * @var PDOStatement ���ƥ졼���Ȥ������Ѥ���륹�ơ��ȥ��ȥϥ�ɥ�
     */
    private $iterator;

    /**
     * @var array ���ƥ졼�������θ�����
     */
    private $iterator_current;

    /**
     * @var mixed �Ǹ�� find ���줿�������ݻ�
     */
    private $last_find_key;

    /**
     * @var mixed �Ǹ�� find ���줿�������ͤ��ݻ�
     */
    private $last_find_value;

    /**
     * ���󥹥ȥ饯��
     *
     * @param array $array ��������Ѥ�������
     *
     */
    public function __construct($array=null) {
        self::_prepareObject($this);

        if (!isset($this->table_name)) {
            // �롼�ȥ��֥������ȤΥ��󥹥ȥ饯��
            if (!isset(self::$temporary_directory)) {
                self::$temporary_directory = sys_get_temp_dir();
            }
            self::$file_name = tempnam(self::$temporary_directory, __CLASS__);
            self::$sqlite = new PDO("sqlite:".self::$file_name);
            self::$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$sqlite->beginTransaction();
            return;
        }

        // �̾索�֥������ȤΥ��󥹥ȥ饯��
        self::$sqlite->exec("CREATE TABLE `{$this->table_name}` (`key` PRIMARY KEY, `value`)");
        if (isset($array) && is_array($array)) {
            foreach ($array as $key => $value) {
                $this->_insert($key, $value);
            }
        }
    }

    /**
     * �ǥ��ȥ饯��
     *
     * @return void
     */
    public function __destruct() {
        if (!isset($this->table_name)) {
            // ���ߡ����֥������ȤΥǥ��ȥ饯����ץ����ν�λ
            self::$sqlite = null;
            unlink(self::$file_name);
            self::$file_name = null;
            return;
        }

        // �̾索�֥������ȤΥǥ��ȥ饯��
        self::$sqlite->exec("DROP TABLE `{$this->table_name}`");
    }

    /**
     * �ͤ��ɲ�
     *  $array[] = $value ������
     *
     * @param mixed $value
     * @return integer ��������ǿ�
     */
    public function push($value) {
        $this->_insert($this->_increaseIndex(), $value);
        return $this->count;
    }

    /**
     * �ͤ�����
     *  $array[$key] = $value ������
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
     * �����Τߤ���ʤ� SqliteArray ���֤�
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
     * �ͤΤߤ���ʤ� SqliteArray ���֤�
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
     * �ͤ����
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
     * �ͤ���
     *
     * SQLite �ϡ�����ԤˤĤ��ƤϺ�����줿�ޡ�����Ĥ�������ʤΤǡ�
     * ���θ�����������Ȥ��Ƥ���������ˤʤ뤳�ȤϤʤ�
     * �����ȥХ��塼��ϡ����ܡ�����.
     *
     * @param $key
     * @return boolean ����������
     */
    public function remove($key) {
        $this->_delete(self::_convertKey($key));
    }

    /**
     * array_merge �ε�ư������
     * â�����˲�Ū
     *
     * @param $array
     * @return SqliteArray ���Ȥ��֤�
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
     * + �黻�Ҥε�ư������
     * â�����˲�Ū
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
     * �ͤ�����򸡺��������Ĥ��ä���硢�б����륭�����֤�
     *
     * array_search ������
     * â���� strict�����Τߤ����Ԥ��ʤ�
     *
     * @param $value
     * @return mixed ����Υ����ޤ��� false
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
     * ��������ˡ����̤�����ܤ�SQLite ������֤�
     *
     * array_intersect_key �äݤ����
     * ���˲���
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
     * SQLite�������ȡʥơ��֥�ˤ����Ƥ� array �ˤ����֤�
     * �̤�¿�����ϥ���˾���ڤ�ʤ���ǽ���⤢��Τ����
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
     * toArray ����̾
     *
     * @return array
     */
    public function getArray() {
        return $this->toArray();
    }

    /**
     * LoadFile �������Ǥ���褦�˥ե��������¸
     *
     * @param $file_name  ��¸��ե�����̾
     * @param $table_name ��¸��ơ��֥�̾��Ǥ�ա�
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
     * SaveFile ����¸�����ե����뤫������
     *
     * @param $file_name   ��¸����Ƥ���ե�����̾
     * @param $table_name  ��¸��ơ��֥�̾��SaveFile �᥽�åɤǻ��ꤷ������ɬ�ס�
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
     * Countable �μ���
     *
     * @return integer �������ǿ�
     */
    public function count() {
        return $this->count;
    }

    /**
     * ArrayAccess �μ��� (¸�߳�ǧ)
     *
     * @param $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        $val = $this->_find(self::_convertKey($offset));
        return ($val) ? $val[0] : null;
    }

    /**
     * ArrayAccess �μ��� ���ͼ�����
     *
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * ArrayAccess �μ��� ���������
     *
     * $array[null] �⡢ $array[] �� ���̤Ǥ��ʤ�(�ɤ���ξ��⡢$offset �� null ���ϤäƤ���)
     * �ͥ��ƥ��� array ���Ф��� null ź���� ��ʸ����Ʊ�����ʤΤǡ� $array[null] �ΤȤ������ϥͥ��ƥ��� array �ȵ�ư���ۤʤ������ա�
     * ����ɤ��ʤ������ͤ���
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
     * ArrayAccess �μ��� ���ͺ����
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    /**
     * Iterator �μ���(���ߤ��ͤ����)
     *
     * @return mixed ���ߤ���
     */
    public function current() {
        return unserialize($this->iterator_current['value']);
    }

    /**
     * Iterator �μ��� �ʥ��ƥ졼���򼡤ؿʤ���
     *
     * @return void
     */
    public function next() {
        $this->iterator_current = $this->iterator->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Iterator �μ��� �ʸ��ߤΥ����������
     *
     * @return mixed ���ߤΥ���
     */
    public function key() {
        return self::_convertKey($this->iterator_current['key']);
    }

    /**
     * Iterator �μ��� �ʼ��˼��������ͤ�ͭ�����ɤ�����
     *
     * @return boolean ���˼��������ͤ�ͭ���ʤ� true, �����Ǥʤ��ʤ� false
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
     * Iterator �μ��� �ʥ��ƥ졼����ǽ���᤹��
     */
    public function rewind() {
        if (isset($this->iterator)) {
            $this->iterator->closeCursor();
        }

        $this->iterator = $this->_executeQuery("SELECT `key`,`value` FROM `{$this->table_name}`");
        $this->iterator_current = $this->iterator->fetch(PDO::FETCH_ASSOC);
    }

    /*
     * ����ե�������Ǽ����ǥ��쥯�ȥ�����
     *
     * �ǽ饳�󥹥ȥ饯�����¹Ԥ�������Τ�ͭ��
     *
     * @param string $dirname ����ե�������Ǽ����ǥ��쥯�ȥ�
     * @return void
     */
    public static function setTemporaryDirectory($dirname) {
        self::$temporary_directory = $dirname;
    }

    /**
     * �� SQLite ������ intersectKey
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
     * �� array �� intersectKey
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
     * ������μ¹�
     *
     * @param string $sql    �¹Ԥ��륯����
     * @param array  $params �Х���ǥ��󥰥ѥ�᥿
     * @return PDOStatement �¹ԺѤߥ��ơ��ȥ��ȥϥ�ɥ�
     */
    private function _executeQuery($sql, $params = null) {
        $sth = self::$sqlite->prepare($sql);
        $ret = $sth->execute($params);
        return $sth;
    }

    /**
     * �����������٤���ź�������֤��ƥ��󥯥����
     *
     * @return integer �����������٤���ź����
     */
    private function _increaseIndex() {
        return $this->current_index++;
    }

    /**
     * $key �򥭡��Ȥ����ɲä���Ȥ��ơ���ź�����򹹿�����
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
     * �������б������ͤ��֤�
     * null�ͤˤ��б����뤿�ᡢ�ͤ�����ǥ�åפ�����Τ��֤�
     *
     * @param mixed $key ����
     * @return �� �������硢 array(��) ̵������ false
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
     * �ͤ��ɲ�
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
     * �ͤ򹹿�
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
     * �ͤ���
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
     * ���ǡ�������(TRUNCATE������)
     *
     * @return void
     */
    private function _clear() {
        // SQLite �ˤ� TRUNCATE ��¸�ߤ��ʤ��Τǡ� DELETE �����Ѥ���
        $this->_executeQuery("DELETE FROM `{$this->table_name}`");
        $this->count = 0;

        $this->last_find_key = null;
    }

    /**
     * ���֥������������ν�����Ԥ�
     *
     * !���󥹥ȥ饯����ɬ���ƤФʤ���Фʤ�ʤ�!
     *  �ơ��֥�̾������ȡ��ץ����κǸ�˥꥽�����������뤿��λųݤ�(�롼�ȥ��֥������Ȥ��ݻ�)��ܤ��Ƥ���
     *
     * @param SqliteArray $self
     * @return void
     */
    private static function _prepareObject($self) {
        if (!isset(self::$root_object)) {
            self::$root_object = false;
            new self(); // �롼�ȥ��֥������Ȥ����� (���󥹥ȥ饯����𤷤� (*1) ��)
        } elseif (!self::$root_object) {
            // �롼�ȥ��֥������Ȥ���Ͽ (*1)
            self::$root_object = $self;
            return;
        }

        // �̾索�֥������ȤȤ��Ƥ���Ͽ
        $self->table_name = self::TABLE_PREFIX . ++self::$object_counter;
    }

    /**
     * �����ź�����Ȥ���ͭ���ʥ������֤�
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
     * ��ź�����Ȥ��Ƹ��ʤ��뤫Ƚ��
     *
     * @param mixed $key ź����
     * @return boolean
     */
    private static function _isNumericKey($key) {
        return is_int($key) || is_bool($key) || is_float($key) || preg_match('/\A(:?0|[1-9][0-9]*)\Z/', $key);
    }
}
