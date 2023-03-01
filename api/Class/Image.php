<?php

/**
 * Class Image with all the attributes of the item_image table
 */
class Image implements \JsonSerializable{

    #region Attributes
        private $id;
        private $name;
        // is a blob in the database
        private $image;
    #endregion

    public function __construct($id, $name, $image){
        if(!empty($id)){
            $this->id = (int)$id;
        }

        if(!empty($name)){
            $this->name = (string)$name;
        }

        if(!empty($image)){
            $this->image = (string)$image;
        }
    }

    public function jsonSerialize(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image
        ];
    }

    #region Getters
        public function getId(){
            return $this->id;
        }

        public function getName(){
            return $this->name;
        }

        public function getimage(){
            return $this->image;
        }
    #endregion

    #region Setters
        public function setId($id){
            $this->id = $id;
        }

        public function setName($name){
            $this->name = $name;
        }

        public function setimage($image){
            $this->image = $image;
        }
    #endregion

}

?>