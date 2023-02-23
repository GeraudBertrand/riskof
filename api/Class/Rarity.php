<?php 

/// Class Rarity with name and color of the rarity table

class Rarity implements \JsonSerializable{
    
    #region Attributes
        private $id;
        private $name;
        private $color;
    #endregion

    public function __construct($id, $name, $color){
        if(!empty($id)){
            $this->id = (int)$id;
        }
        if(!empty($name)){
            $this->name = (string)$name;
        }
        if(!empty($color)){
            $this->color = (string)$color;
        }
    }

    #region Getters
    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function getColor(){
        return $this->color;
    }
    #endregion
    
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}
?>