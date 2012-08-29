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
		$config['parentIsSet'] = true;
		if(array_key_exists('pathToDocument',$config)) {
			$basePath = substr($config['pathToDocument'],0,-2);
			if($key==Epic_Mongo_DocumentSet::DYNAMIC_INDEX){
				$config['pathToDocument'] = $basePath;
			} else {
				$config['pathToDocument'] = $basePath . "." . $key;
			}
		}
		return $config;
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