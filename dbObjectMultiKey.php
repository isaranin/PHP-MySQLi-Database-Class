<?

namespace MysqliDb;

/**
 * Mysqli Model wrapper
 *
 * @category  Database Access
 * @package   MysqliDb
 * @author    Ivan Saranin <ivan@saranin.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      http://github.com/isaranin/PHP-MySQLi-Database-Class 
 * @version   1.0-master
 */

class dbObjectMultiKey extends dbObject {
	/**
     * Primary key for an object. 'id' is a default value.
     *
     * @var array
     */
    protected $primaryKey = [];
	
	/**
     * @return mixed insert id or false in case of failure
     */
    public function insert () {
        if (!empty ($this->timestamps) && in_array ("createdAt", $this->timestamps))
            $this->createdAt = date("Y-m-d H:i:s");
        $sqlData = $this->prepareData ();
        if (!$this->validate ($sqlData))
            return false;

        $this->db->insert ($this->dbTable, $sqlData);
        
        $this->isNew = false;

        return true;
    }

	private function primaryKeysExist( $data) {
		$res = true;
		foreach($this->primaryKey as $key) {
			if (!array_key_exists($key, $data)) {
				$res = false;
				break;
			}
		}
		return $res;
	}
	
	private function setPrimaryKeyWhere() {
		foreach($this->primaryKey as $key) {
			$this->db->where ($key, $this->data[$key]);
		}
	}
    /**
     * @param array $data Optional update data to apply to the object
     */
    public function update ($data = null) {
        if (empty ($this->dbFields))
            return false;

        if (!$this->primaryKeysExist($this->data))
            return false;

        if ($data) {
            foreach ($data as $k => $v)
                $this->$k = $v;
        }

        if (!empty ($this->timestamps) && in_array ("updatedAt", $this->timestamps))
            $this->updatedAt = date("Y-m-d H:i:s");

        $sqlData = $this->prepareData ();
        if (!$this->validate ($sqlData))
            return false;
		
		$this->setPrimaryKeyWhere();
        return $this->db->update ($this->dbTable, $sqlData);
    }

    /**
     * Save or Update object
     *
     * @return mixed insert id or false in case of failure
     */
    public function save ($data = null) {
        if ($this->isNew)
            return $this->insert();
        return $this->update ($data);
    }

    /**
     * Delete method. Works only if object primaryKey is defined
     *
     * @return boolean Indicates success. 0 or 1.
     */
    public function delete () {
        if (!$this->primaryKeysExist($this->data))
            return false;

        $this->setPrimaryKeyWhere();
        return $this->db->delete ($this->dbTable);
    }

    /**
     * Get object by primary key.
     *
     * @access public
     * @param array $ids array of primary key ['key1'=>'value', 'key2' => 'value']
     * @param array|string $fields Array or coma separated list of fields to fetch
     *
     * @return dbObject|array
     */
    protected function byId ($ids, $fields = null) {
		foreach($this->primaryKey as $key) {
			$this->db->where (MysqliDb::$prefix . $this->dbTable . '.' . $key, $ids[$key]);
		}
       
        return $this->getOne ($fields);
    }
}
