<?php 
/// Class Item with all the attributes of the item table

class Item implements \JsonSerializable {
    
    #region Attributes
        private $id;
        private $name;
        private $rarity;
        private $description;
        private $image;
        private $effect;
        private $base1;
        private $stack1;
        private $base2;
        private $stack2;
        private $base3;
        private $stack3;
    #endregion

    public function __construct($id, $name, $rarity, $description, $image, $effect, $base1, $stack1, $base2, $stack2, $base3, $stack3){
        if(!empty($id)){
            $this->id = (int)$id;
        }

        if(!empty($name)){
            $this->name = (string)$name;
        }

        if(!empty($rarity)){
            $this->rarity = (int)$rarity;
        }

        if(!empty($description)){
            $this->description = (string)$description;
        }

        if(!empty($image)){
            $this->image = base64_encode($image);
        }

        if(!empty($effect)){
            $this->effect = (string)$effect;
        }

        if(!empty($base1)){
            $this->base1 = (float)$base1;
        }

        if(!empty($stack1)){
            $this->stack1 = (float)$stack1;
        }

        if(!empty($base2)){
            $this->base2 = (float)$base2;
        }

        if(!empty($stack2)){
            $this->stack2 = (float)$stack2;
        }

        if(!empty($base3)){
            $this->base3 = (float)$base3;
        }

        if(!empty($stack3)){
            $this->stack3 = (float)$stack3;
        }
    }

    #region Getters
    
    /**
    * @return int
    */
    public function getId(){
        return $this->id;
    }

    /**
    * @return string
    */
    public function getName(){
        return $this->name;
    }

    /**
    * @return string
    */
    public function getDescription(){
        return $this->description;
    }

    /**
    * @return string
    */
    public function getEffect(){
        return $this->effect;
    }

    public function getImage(){
        return $this->image;
    }

    public function getRarity(){
        return $this->rarity;
    }

    /**
    * @return float
    */
    public function getBase1(){
        return $this->base1;
    }

    /**
    * @return float
    */
    public function getStack1(){
        return $this->stack1;
    }

    /**
    * @return float
    */
    public function getBase2(){
        return $this->base2;
    }

    /**
    * @return float
    */
    public function getStack2(){
        return $this->stack2;
    }

    /**
    * @return float
    */
    public function getBase3(){
        return $this->base3;
    }

    /**
    * @return float
    */
    public function getStack3(){
        return $this->stack3;
    }
    #endregion

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

?>