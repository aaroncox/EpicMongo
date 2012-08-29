<?php

/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/

class Epic_Mongo_Iterator_Export implements OuterIterator
{
	protected $_innerIterator = null;

	public function __construct(Iterator $iterator)
	{
		$this->_innerIterator = $iterator;
	}

	public function getInnerIterator()
	{
		return $this->_innerIterator;
	}

	/**
	 * Get the current value
	 *
	 * @return mixed
	 */
	public function current()
	{
		$current = $this->getInnerIterator()->current();
		// document we are iterating
		$document = $this->getInnerIterator()->getDocument();

		if ($current instanceof Epic_Mongo_Document) {
			if ($document instanceOf Epic_Mongo_DocumentSet) {
				$key = Epic_Mongo_DocumentSet::DYNAMIC_INDEX;
			} else {
				$key = $this->key();
			}
			if ($document->hasRequirement($key, 'ref')) {
				$export = $current->createReference();
			} else {
				$export = $current->export();
			}

			if (empty($export)) {
				return null;
			}
			return $export;
		}
		return $current;
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
}