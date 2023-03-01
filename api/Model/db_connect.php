<?php 

require_once './Class/Item.php';
require_once './Class/Rarity.php';
require_once './Class/Tag.php';

class Model{

    private static $instance = null;
  
    private $bdd;
  
    /**
     * Constructor
     */
    private function __construct() {
        require ('credential.php');
        try {
            $this->bdd = new PDO($dns, $login, $mdp);
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->bdd->exec('SET CHARACTER SET utf8');
        } catch (PDOException $e) {
            die('<p>Erreur acces BD : '.$e->getCode().' : '.$e->getMessage().'</p>');
        }
    }

    /**
     * Get the instance of the model
     *
     * @return Model
     */
    public static function getModel(){
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Get all the items
     * @return Item[]|null
     */
    public function get_items() {
        $requete = $this->bdd->prepare('SELECT `id`, `name`, `rarity`, `description`, `image`, `effect`, `base1`, `stack1`, `base2`, `stack2`, `base3`, `stack3` From `item`;');
        $requete->execute();
        $count = $requete->rowCount();
        if($count > 0)
        {
            $tab = $requete->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            foreach($tab as $value)
            {
                $item = new Item($value['id'], $value['name'], $value['rarity'], $value['description'], $value['image'], $value['effect'], $value['base1'], $value['stack1'], $value['base2'], $value['stack2'], $value['base3'], $value['stack3']);
                array_push($res, $item);
            }
            return $res;
        }
        return null;
    }

    /**
     * Get item on an id
     * @param int $id
     * @return Item|null
     */
    public function get_item(int $id) {
        $requete = $this->bdd->prepare('SELECT * From `item` WHERE `id` = :id ;');
        $requete->bindValue(':id', $id, PDO::PARAM_INT);
        $requete->execute();
        $tab = $requete->fetch(PDO::FETCH_ASSOC);
        if($tab != null || $tab != false)
        {
            return new Item($tab['id'], $tab['name'], $tab['rarity'], $tab['description'], $tab['image'], $tab['effect'], $tab['base1'], $tab['stack1'], $tab['base2'], $tab['stack2'], $tab['base3'], $tab['stack3']);
        }
        return null;
    }

    /**
     * Get all the items by rarity
     * @param int $rarity
     * @return Item[]|null
     */
    public function get_items_rarity(string $rarity) {
        $requete = $this->bdd->prepare('SELECT `id`, `name`, `rarity`, `description`, `image`, `effect`, `base1`, `stack1`, `base2`, `stack2`, `base3`, `stack3` From `item` WHERE `rarity` = :rarity ;');
        $requete->bindValue(':rarity', $rarity, PDO::PARAM_INT);
        $requete->execute();
        $count = $requete->rowCount();
        if($count > 0)
        {
            $tab = $requete->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            foreach($tab as $value)
            {
                $item = new Item($value['id'], $value['name'], $value['rarity'], $value['description'], $value['image'], $value['effect'], $value['base1'], $value['stack1'], $value['base2'], $value['stack2'], $value['base3'], $value['stack3']);
                array_push($res, $item);
            }
            return $res;
        }
        return null;
    }



    /**
     * Get all the rarities
     * @return Rarity[]|null
     */
    public function get_rarities() {
        $requete = $this->bdd->prepare('SELECT * From `rarity` ;');
        $requete->execute();
        $count = $requete->rowCount();
        if($count > 0)
        {
            $tab = $requete->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            foreach($tab as $value)
            {
                $rarity = new Rarity($value['id'], $value['name'], $value['color']);
                array_push($res, $rarity);
            }
            return $res;
        }
        return null;
    }

    /**
     * Get rarity on an id
     * @param int $id
     * @return Rarity|null
     */
    public function get_rarity(int $id) {
        $requete = $this->bdd->prepare('SELECT * From `rarity` WHERE `id` = :id ;');
        $requete->bindValue(':id', $id, PDO::PARAM_INT);
        $requete->execute();
        $tab = $requete->fetch(PDO::FETCH_ASSOC);
        if($tab != null || $tab != false)
        {
            return new Rarity($tab['id'], $tab['name'], $tab['color']);
        }
        return null;
    }

    /**
     * Get all the tags
     * @return Tag[]|null
     */
    public function get_tags() {
        $requete = $this->bdd->prepare('SELECT * From `tag` ;');
        $requete->execute();
        $count = $requete->rowCount();
        if($count > 0)
        {
            $tab = $requete->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            foreach($tab as $value)
            {
                $tag = new Tag($value['id'], $value['name']);
                array_push($res, $tag);
            }
            return $res;
        }
        return null;
    }

    /**
     * Get tag on an id
     * @param int $id
     * @return Tag|null
     */
    public function get_tag(int $id) {
        $requete = $this->bdd->prepare('SELECT * From `tag` WHERE `id` = :id ;');
        $requete->bindValue(':id', $id, PDO::PARAM_INT);
        $requete->execute();
        $tab = $requete->fetch(PDO::FETCH_ASSOC);
        if($tab != null || $tab != false)
        {
            return new Tag($tab['id'], $tab['name']);
        }
        return null;
    }


    /**
     * Get all the items with a tag
     * @param int $idTag
     * @return Item[]|null
     */
    public function get_item_with_tag(int $idTag){
        $requete = $this->bdd->prepare('SELECT `id`, `name`, `rarity`, `description`, `image`, `effect`, `base1`, `stack1`, `base2`, `stack2`, `base3`, `stack3` From `item` WHERE `id` IN (SELECT `id_item` FROM `link_item_tag` WHERE `id_tag` = :idTag) ;');
        $requete->bindValue(':idTag', $idTag, PDO::PARAM_INT);
        $requete->execute();
        $count = $requete->rowCount();
        if($count > 0)
        {
            $tab = $requete->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            foreach($tab as $value)
            {
                $item = new Item($value['id'], $value['name'], $value['rarity'], $value['description'], $value['image'], $value['effect'], $value['base1'], $value['stack1'], $value['base2'], $value['stack2'], $value['base3'], $value['stack3']);
                array_push($res, $item);
            }
            return $res;
        }
        return null;
    }


    /**
     * Insert a new image into the database with the item_id and item_image
     * item_image is a blob
     * @param int $id
     * @param string $image
     */
    public function insert_image(int $id, $image) {
        $requete = $this->bdd->prepare('INSERT INTO `item_image` (`item_id`, `image`) VALUES (:id, :image);');
        $requete->bindValue(':id', $id, PDO::PARAM_INT);
        $requete->bindValue(':image', $image, PDO::PARAM_STR);
        $requete->execute();
    }
}
?>