<?php

namespace Daniels\FuelLogger\Application\Model;

class ListModel
{
    /**
     * Array of objects (some object list).
     *
     * @var array $_aArray
     */
    protected array $array = [];

    /**
     * Flag if array is ok or not
     *
     * @var boolean $valid
     */
    private bool $valid = true;

    /** @var BaseModel */
    private $baseObject = null;

    /**
     * Save the state, that active element was unset
     * needed for proper foreach iterator functionality
     *
     * @var bool $removedActive
     */
    protected bool $removedActive = false;

    /**
     * offsetExists for SPL
     *
     * @param string $offset SPL array offset
     *
     * @return boolean
     */
    public function offsetExists(string $offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * offsetGet for SPL
     *
     * @param string $offset SPL array offset
     *
     * @return BaseModel|false
     */
    public function offsetGet(string $offset): BaseModel|bool
    {
        if ($this->offsetExists($offset)) {
            return $this->array[$offset];
        }

        return false;
    }

    /**
     * offsetSet for SPL
     *
     * @param string $offset SPL array offset
     * @param BaseModel $oBase  Array element
     */
    public function offsetSet(string $offset, BaseModel $oBase)
    {
        $this->array[$offset] = & $oBase;
    }

    /**
     * offsetUnset for SPL
     *
     * @param string $offset SPL array offset
     */
    public function offsetUnset(string $offset)
    {
        if (strcmp($offset, $this->key()) === 0) {
            // #0002184: active element removed, next element will be prev / first
            $this->removedActive = true;
        }

        unset($this->array[$offset]);
    }

    /**
     * Returns SPL array keys
     *
     * @return array
     */
    public function arrayKeys(): array
    {
        return array_keys($this->array);
    }

    /**
     * rewind for SPL
     */
    public function rewind()
    {
        $this->removedActive = false;
        $this->valid = (false !== reset($this->array));
    }

    /**
     * current for SPL
     *
     * @return null;
     */
    public function current()
    {
        return current($this->array);
    }

    /**
     * key for SPL
     *
     * @return string
     */
    public function key(): string
    {
        return key($this->array);
    }

    /**
     * previous / first array element
     *
     * @return BaseModel
     */
    public function prev(): BaseModel
    {
        $oVar = prev($this->array);
        if ($oVar === false) {
            // the first element, reset pointer
            $oVar = reset($this->array);
        }
        $this->removedActive = false;

        return $oVar;
    }

    /**
     * next for SPL
     */
    public function next()
    {
        if ($this->removedActive === true && current($this->array)) {
            $oVar = $this->prev();
        } else {
            $oVar = next($this->array);
        }

        $this->valid = (false !== $oVar);
    }

    /**
     * valid for SPL
     *
     * @return boolean
     */
    public function valid(): bool
    {
        return $this->valid;
    }

    /**
     * count for SPL
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->array);
    }

    /**
     * clears/destroys list contents
     */
    public function clear()
    {
        $this->array = [];
    }

    /**
     * copies a given array over the objects internal array (something like old $myList->aList = $aArray)
     *
     * @param array $array array of list items
     */
    public function assign(array $array)
    {
        $this->array = $array;
    }

    /**
     * returns the array reversed, the internal array remains untouched
     *
     * @return array
     */
    public function reverse(): array
    {
        return array_reverse($this->array);
    }

    /**
     * List Object class name
     *
     * @var string
     */
    protected string $objectsInListName = BaseModel::class;



    /**
     * Initializes or returns existing list template object.
     *
     * @return BaseModel
     */
    public function getBaseObject(): BaseModel
    {
        if (!$this->baseObject) {
            $this->baseObject = new $this->objectsInListName;
        }

        return $this->baseObject;
    }

    public function selectString($sql, array $parameters = [])
    {
        $this->clear();

        $conn = DBConnection::getConnection();
        $result = $conn->executeQuery($sql, $parameters)->fetchAllAssociative();

        if (count($result)) {
            $oSaved = clone $this->getBaseObject();

            foreach ($result as $item) {
                $baseObject = clone $oSaved;
                $baseObject->assign($item);
                $this->offsetSet($baseObject->getId(), $baseObject);
            }
        }
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }
}