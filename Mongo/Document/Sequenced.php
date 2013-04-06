<?php

class Epic_Mongo_Document_Sequenced extends Epic_Mongo_Document {

	protected $_sequenceCollection = 'epic_sequences';

	public function save($wholeDocument = false) {
		if($this->_sequenceKey) {
			if(!$this->id) {
				$this->id = $this->getNextSequence($this->_sequenceKey);
			}
		} else {
			throw new Epic_Mongo_Exception("In order to extend Epic_Mongo_Document_Sequenced, your Document Class needs a protected _sequenceKey value specifying the key the sequence will be stored as.");
		}
		return parent::save($wholeDocument);
	}
	  
  public function getNextSequence($sequenceId) {
    $update = array(
  		'findandmodify' => $this->_sequenceCollection,
  		'query' => array('id' => $sequenceId),
  		'update' => array('$inc' => array('sequence' => 1)),
  		'new' => TRUE,
  		'upsert' => TRUE
  	);
  	$result = $this->getSchema()->getMongoDb()->command($update);
 	  return $result['value']['sequence'];				
  }

	public function setNextSequence($sequenceId, $value) {
		$update = array(
  		'findandmodify' => $this->_sequenceCollection,
  		'query' => array('id' => $sequenceId),
  		'update' => array('$set' => array('sequence' => $value)),
  		'new' => TRUE,
  		'upsert' => TRUE
  	);
  	$result = $this->getSchema()->getMongoDb()->command($update);
 	  return $result['value']['sequence'];				
	}
}