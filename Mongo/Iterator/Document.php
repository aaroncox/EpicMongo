<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Iterator_Document implements SeekableIterator, RecursiveIterator
{
	protected $_document = null;
	protected $_position = null;
	protected $_properties = array();
	protected $_init = false;
	protected $_counter = 0;

	public function __construct(Epic_Mongo_Document $document)
	{
		$this->_document = $document;
		$this->_properties = $document->getPropertyKeys();
		$this->_position = current($this->_properties);
		reset($this->_properties);
	}

	public function getDocument()
	{
		return $this->_document;
	}

	public function seek($position)
	{
		$this->_position = $position;

		if (!$this->valid()) {
			throw new OutOfBoundsException("invalid seek position ($position)");
		}
	}

	public function current()
	{
		return $this->getDocument()->getProperty($this->key());
	}

	public function key()
	{
		return $this->_position;
	}

	public function next()
	{
		next($this->_properties);
		$this->_position = current($this->_properties);
		$this->_counter = $this->_counter + 1;
	}

	public function rewind()
	{
		reset($this->_properties);
		$this->_position = current($this->_properties);
	}

	public function valid()
	{
		return in_array($this->key(), $this->_properties, true);
	}

	public function hasChildren()
	{
		return ($this->current() instanceof Epic_Mongo_Document);
	}

	public function getChildren()
	{
		return $this->current()->getIterator();
	}
}