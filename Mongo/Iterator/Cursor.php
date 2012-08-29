<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Iterator_Cursor implements Iterator, Countable
{
	protected $_cursor = null;
	protected $_config = array();

	public function __construct(MongoCursor $cursor, $config = array())
	{
		$this->_cursor = $cursor;
		$this->_config = $config;
	}

	public function count()
	{
		return $this->_cursor->count();
	}

	/**
	 * Get the inter iterator
	 *
	 * @return MongoCursor
	 */
	public function getInnerIterator()
	{
		return $this->_cursor;
	}

	/**
	 * Get the collection name
	 *
	 * @return string
	 */
	public function getCollection()
	{
		if(array_key_exists('collection', $this->_config)) {
			return $this->_config['collection'];
		}
		return null;
	}

	/**
	 * Get the document class
	 *
	 * @return string
	 */
	public function getDocumentClass()
	{
		if(isset($this->_config['documentClass'])) {
			return $this->_config['documentClass'];
		}
		return 'Epic_Mongo_Document';
	}

	/**
	 * Export all data
	 *
	 * @return array
	 */
	public function export()
	{
		$this->rewind();
		return iterator_to_array($this->getInnerIterator());
	}

	/**
	 * Get the current value
	 *
	 * @return mixed
	 */
	public function current()
	{
		$data = $this->getInnerIterator()->current();

		if(is_null($data)) {
			return null;
		}

		$config = array();
		$config['hasKey'] = true;
		$config['collection'] = $this->getCollection();

		$documentClass = $this->getDocumentClass();

		// TODO - Check _type of data

		return new $documentClass($data, $config);
	}

	public function key()
	{
		return $this->getInnerIterator()->key();
	}

	public function next()
	{
		return $this->getInnerIterator()->next();
	}

	public function rewind()
	{
		return $this->getInnerIterator()->rewind();
	}

	public function valid()
	{
		return $this->getInnerIterator()->valid();
	}

	public function __call($method, $arguments)
	{
		call_user_func_array(array($this->getInnerIterator(),$method), $arguments);
		return $this;
	}
}