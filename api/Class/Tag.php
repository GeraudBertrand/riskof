<?php 

/// Class Tag with name of the tag table

class Tag implements \JsonSerializable{
    
    #region Attributes
        private $id;
        private $name;
    #endregion

    public function __construct($id, $name){
        if(!empty($id)){
            $this->id = (int)$id;
        }
        if(!empty($name)){
            $this->name = (string)$name;
        }
    }

    #region Getters
        public function getId(){
            return $this->id;
        }
    
        public function getName(){
            return $this->name;
        }
        #endregion

        public function jsonSerialize()
        {
            $vars = get_object_vars($this);
    
            return $vars;
        }
}

?>