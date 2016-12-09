<?php

namespace Selenide;

use ArrayAccess;
use Countable;
use Iterator;

class ElementsCollection implements Iterator, Countable, ArrayAccess
{
    const MODE_SINGLE_ELEMENT = 1;
    const MODE_COLLECTION_ELEMENT = 2;

    /**
     * @var Selenide;
     */
    protected $selenide = null;
    /**
     * @var Driver
     */
    protected $driver = null;
    /**
     * @var Selector[]
     */
    protected $selectorList = [];

    protected $description = '';

    protected $index = 0;


    public function __construct(Selenide $selenide, array $selectorList)
    {
        $this->selenide = $selenide;
        $this->driver = $selenide->getDriver();
        $this->selectorList = $selectorList;
    }


    /**
     * Set element description
     *
     * @param $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;
        return $this;
    }


    /**
     * Find single element
     *
     * @param $locator
     * @return ElementsCollection
     */
    public function find(By $locator)
    {
        $selector = new Selector();
        $selector->locator = $locator;
        $selector->type = Selector::TYPE_ELEMENT;
        $this->selectorList[] = $selector;
        return $this;
    }


    /**
     * Find elements collection
     *
     * @param $locator
     * @return ElementsCollection
     */
    public function findAll(By $locator)
    {
        $selector = new Selector();
        $selector->locator = $locator;
        $selector->type = Selector::TYPE_COLLECTION;
        $this->selectorList[] = $selector;
        return $this;
    }


    /**
     * Filter by condition
     *
     * @param Condition_Rule $condition
     * @return ElementsCollection
     */
    public function should(Condition_Rule $condition)
    {
        $selector = new Selector();
        $selector->condition = $condition;
        $selector->isPositive = true;
        $this->selectorList[] = $selector;
        return $this;
    }


    /**
     * Filter by Not Condition
     *
     * @param Condition_Rule $condition
     * @return ElementsCollection
     */
    public function shouldNot(Condition_Rule $condition)
    {
        $selector = new Selector();
        $selector->condition = $condition;
        $selector->isPositive = false;
        $this->selectorList[] = $selector;
        return $this;
    }


    /**
     * Assert condition
     *
     * @param Condition_Rule $condition
     * @return $this
     * @throws Exception_ElementNotFound
     */
    public function assert(Condition_Rule $condition)
    {
        $collection = $this->getWebdriverCollection();
        try {
            $condition->applyAssert($collection);
        } catch (Exception_ElementNotFound $e) {
            throw new Exception_ElementNotFound(
                $this->description .
                ': Not found element ' . $this->getLocator() . ' with condition ' .
                $condition->getLocator(),
                0,
                $e);
        }

        return $this;
    }


    /**
     * Assert not condition
     *
     * @param Condition_Rule $condition
     * @return $this
     */
    public function assertNot(Condition_Rule $condition)
    {
        $collection = $this->getWebdriverCollection();
        $condition->applyAssertNegative($collection);
        return $this;
    }


    /**
     * @param int $index
     * @return SelenideElement
     * @throws Exception_ElementNotFound
     */
    public function get($index = 0)
    {
        $collection = $this->getCollectionNotEmpty();
        if (!isset($collection[$index])) {
            throw new Exception_ElementNotFound(
                $this->description . ': Not found element ' . $this->getLocator()
            );
        }
        return $collection[$index];
    }


    public function length()
    {
        return count($this->getCollection());
    }


    /**
     * Set element value
     *
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $collection = $this->getCollectionNotEmpty();
        foreach ($collection as $element) {
            $element->setValue($value);
        }
        return $this;
    }


    /**
     * Press key enter
     *
     * @return $this
     */
    public function pressEnter()
    {
        $collection = $this->getCollectionNotEmpty();
        foreach ($collection as $element) {
            $element->pressEnter();
        }
        return $this;
    }




    /**
     * Check all elements visible
     *
     * @return bool
     */
    public function isDisplayed()
    {
        $collection = $this->getCollection();
        $counter = 0;
        foreach ($collection as $element) {
            $counter += $element->isDisplayed() ? 1 : 0;
        }
        return ((count($collection) == $counter) && ($counter > 0));
    }


    /**
     * Click all elements
     *
     * @return $this
     */
    public function click()
    {
        $collection = $this->getCollectionNotEmpty();
        foreach ($collection as $element) {
            $element->click();
        }
        return $this;
    }


    /**
     * DoubleClick all elements
     *
     * @return $this
     */
    public function doubleClick()
    {
        $collection = $this->getCollectionNotEmpty();
        foreach ($collection as $element) {
            $element->dbclick();
        }
        return $this;
    }


    /**
     * Check all elements exists
     *
     * @return bool
     */
    public function exists()
    {
        $collection = $this->getCollection();
        $counter = 0;
        foreach ($collection as $element) {
            $counter += $element->exists() ? 1 : 0;
        }
        return (count($collection) == $counter) && ($counter > 0);
    }


    /**
     * Check all elements checked
     *
     * @return bool
     */
    public function checked()
    {
        $collection = $this->getCollectionNotEmpty();
        $counter = 0;
        foreach ($collection as $element) {
            $counter += $element->checked() ? 1 : 0;
        }
        return (count($collection) == $counter) && ($counter > 0);
    }


    /**
     * Get element list values
     *
     * @return string
     */
    public function val()
    {
        $collection = $this->getCollectionNotEmpty();
        $this->selenide->getReport()->addChildEvent('Read value');
        $valueList = [];
        foreach ($collection as $element) {
            $valueList[] = $element->val();
        }
        return $this->sendResult($valueList);
    }



    /**
     * Get all elements attribute with name
     *
     * @return array
     */
    public function attribute($name)
    {
        $collection = $this->getCollectionNotEmpty();
        $attrList = [];
        foreach ($collection as $element) {
            $attrList[] = $element->attribute($name);
        }
        return $this->sendResult($attrList);
    }


    /**
     * Get element list values
     *
     * @return string
     */
    public function text()
    {
        $collection = $this->getCollectionNotEmpty();
        $this->selenide->getReport()->addChildEvent('Read value');
        $valueList = [];
        foreach ($collection as $element) {
            $valueList[] = $element->text();
        }
        return $this->sendResult($valueList);
    }


    /**
     * Get path for element
     *
     * @return string
     */
    public function getLocator()
    {
        return Util::selectorAsText($this->selectorList);
    }


    /**
     * @return \WebDriver_Element[]
     */
    protected function getWebdriverCollection()
    {
        $elementList = $this->driver->search($this->selectorList);
        $stateText = empty($elementList) ?
            'Not found elements' : ('Found elements ' . count($elementList));
        $this->selenide->getReport()->addChildEvent($stateText);
        return $elementList;
    }


    /**
     * @return SelenideElement[]
     */
    public function getCollection()
    {
        $elementList = $this->getWebdriverCollection();
        $resultList = [];
        foreach ($elementList as $wdElement) {
            $resultList[] = new SelenideElement($this->selenide, $wdElement);
        }
        return $resultList;
    }


    /**
     * Alias for getCollection with check for not empty
     *
     * @return SelenideElement[]
     * @throws Exception_ElementNotFound
     */
    public function getCollectionNotEmpty()
    {
        $collection = $this->getCollection();
        if (empty($collection)) {
            throw new Exception_ElementNotFound(
                $this->description .
                ': Not found element ' . $this->getLocator()
            );
        }
        return $collection;

    }


    protected function sendResult(array $result)
    {
        $mode = self::MODE_COLLECTION_ELEMENT;
        foreach ($this->selectorList as $selector) {
            switch ($selector->type) {
                case Selector::TYPE_ELEMENT:
                    $mode = self::MODE_SINGLE_ELEMENT;
                    break;
                case Selector::TYPE_COLLECTION:
                    $mode = self::MODE_COLLECTION_ELEMENT;
                    break;
            }
        }

        if ($mode != self::MODE_COLLECTION_ELEMENT) {
            $result = isset($result[0]) ? $result[0] : null;
        }
        return $result;
    }


    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return SelenideElement Can return any type.
     * @since 5.0.0
     */
    public function current(): SelenideElement
    {
        return $this->get($this->index);
    }


    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->index++;
    }


    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->index;
    }


    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return array_key_exists($this->index, $this->getCollection());
    }


    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->index = 0;
    }


    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->length();
    }


    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->getCollection());
    }


    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }


    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @throws Exception_CollectionMethodNotImplamented
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception_CollectionMethodNotImplamented();
    }


    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @throws Exception_CollectionMethodNotImplamented
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new Exception_CollectionMethodNotImplamented();
    }
}
