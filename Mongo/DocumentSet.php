<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_DocumentSet extends Epic_Mongo_Document
{
	const DYNAMIC_INDEX = "$";

	protected function getConfigForProperty($key, $data) {
		$config = parent::getConfigForProperty(Epic_Mongo_DocumentSet::DYNAMIC_INDEX, $data);
		$config['parentIsSet'] = $this;
		if(array_key_exists('pathToDocument',$config)) {
			$basePath = substr($config['pathToDocument'],0,-2);
			$config['pathToDocument'] = $basePath . "." . $key;
		}
		return $config;
	}

	public function doc($key = null, $data = null) {
		return parent::doc($key, $data);
	}

	public function hasRequirement($key, $value) {
		return parent::hasRequirement(Epic_Mongo_DocumentSet::DYNAMIC_INDEX, $value);
	}

	public function getRequirement($key, $requirement) {
		return parent::getRequirement(Epic_Mongo_DocumentSet::DYNAMIC_INDEX, $requirement);
	}

	public function getRequirements($key = null) {
		if (is_null($key)) {
			return parent::getRequirements();
		}
		return parent::getRequirements('$.');
	}

	public function setProperty($key,$value) {
		$new = is_null($key);
		if (!$new && !is_numeric($key)) {
			throw new Epic_Mongo_Exception('DocumentSets must only contain numeric keys');
		}
		if(!(is_null($value) || $value instanceOf Epic_Mongo_Document)) {
			throw new Epic_Mongo_Exception('DocumentSets must only contain documents');
		}
		if($new) {
			$keys = $this->getPropertyKeys();
			if(empty($keys)) {
				$key = 0;
			} else {
				$key = max($keys) + 1;
			}
		}
		return parent::setProperty($key,$value);
	}

}