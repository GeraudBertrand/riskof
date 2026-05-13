<?php

require_once __DIR__ . '/../Class/Item.php';
require_once __DIR__ . '/../Class/Equipment.php';
require_once __DIR__ . '/../Class/Rarity.php';
require_once __DIR__ . '/../Class/Tag.php';

class Model{

    private static $instance = null;

    private $bdd;

    private function __construct() {
        require __DIR__ . '/credential.php';
        try {
            $this->bdd = new PDO($dns, $login, $mdp);
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->bdd->exec('SET CHARACTER SET utf8mb4');
        } catch (PDOException $e) {
            $msg = 'Erreur BDD : ' . $e->getMessage();
            // Web : renvoie un JSON propre si rien n'a encore été émis.
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(500);
                die(json_encode(['error' => $msg]));
            }
            // CLI ou output déjà commencé (streaming, import script) :
            // on relance l'exception → l'appelant gère la sortie.
            throw new RuntimeException($msg, 0, $e);
        }
    }

    public static function getModel(): Model {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Expose le PDO pour les scripts d'admin / import qui ont besoin d'écrire
     * directement (transactions, upserts en masse, etc.).
     */
    public function getPdo(): PDO {
        return $this->bdd;
    }

    // ============================================================
    //  Items
    // ============================================================

    /** @return Item[]|null */
    public function get_items() {
        $rq = $this->bdd->prepare('SELECT * FROM `item` ORDER BY `name`;');
        $rq->execute();
        $rows = $rq->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;
        return array_map(fn($r) => new Item($r), $rows);
    }

    public function get_item(int $id): ?Item {
        $rq = $this->bdd->prepare('SELECT * FROM `item` WHERE `id` = :id;');
        $rq->bindValue(':id', $id, PDO::PARAM_INT);
        $rq->execute();
        $row = $rq->fetch(PDO::FETCH_ASSOC);
        return $row ? new Item($row) : null;
    }

    /** @return Item[]|null */
    public function get_items_rarity($rarity) {
        $rq = $this->bdd->prepare('SELECT * FROM `item` WHERE `rarity` = :r ORDER BY `name`;');
        $rq->bindValue(':r', (int)$rarity, PDO::PARAM_INT);
        $rq->execute();
        $rows = $rq->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;
        return array_map(fn($r) => new Item($r), $rows);
    }

    /** @return Item[]|null */
    public function get_item_with_tag(int $idTag) {
        $rq = $this->bdd->prepare(
            'SELECT * FROM `item`
              WHERE `id` IN (SELECT `id_item` FROM `link_item_tag` WHERE `id_tag` = :idTag)
              ORDER BY `name`;'
        );
        $rq->bindValue(':idTag', $idTag, PDO::PARAM_INT);
        $rq->execute();
        $rows = $rq->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;
        return array_map(fn($r) => new Item($r), $rows);
    }

    // ============================================================
    //  Equipments
    // ============================================================

    /** @return Equipment[]|null */
    public function get_equipments() {
        $rq = $this->bdd->prepare('SELECT * FROM `equipment` ORDER BY `name`;');
        $rq->execute();
        $rows = $rq->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;
        return array_map(fn($r) => new Equipment($r), $rows);
    }

    public function get_equipment(int $id): ?Equipment {
        $rq = $this->bdd->prepare('SELECT * FROM `equipment` WHERE `id` = :id;');
        $rq->bindValue(':id', $id, PDO::PARAM_INT);
        $rq->execute();
        $row = $rq->fetch(PDO::FETCH_ASSOC);
        return $row ? new Equipment($row) : null;
    }

    // ============================================================
    //  Rarities / Tags
    // ============================================================

    /** @return Rarity[]|null */
    public function get_rarities() {
        $rq = $this->bdd->prepare('SELECT * FROM `rarity`;');
        $rq->execute();
        $rows = $rq->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;
        $out = [];
        foreach ($rows as $v) $out[] = new Rarity($v['id'], $v['name'], $v['color']);
        return $out;
    }

    public function get_rarity(int $id): ?Rarity {
        $rq = $this->bdd->prepare('SELECT * FROM `rarity` WHERE `id` = :id;');
        $rq->bindValue(':id', $id, PDO::PARAM_INT);
        $rq->execute();
        $v = $rq->fetch(PDO::FETCH_ASSOC);
        return $v ? new Rarity($v['id'], $v['name'], $v['color']) : null;
    }

    /** @return Tag[]|null */
    public function get_tags() {
        $rq = $this->bdd->prepare('SELECT * FROM `tag`;');
        $rq->execute();
        $rows = $rq->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;
        $out = [];
        foreach ($rows as $v) $out[] = new Tag($v['id'], $v['name']);
        return $out;
    }

    public function get_tag(int $id): ?Tag {
        $rq = $this->bdd->prepare('SELECT * FROM `tag` WHERE `id` = :id;');
        $rq->bindValue(':id', $id, PDO::PARAM_INT);
        $rq->execute();
        $v = $rq->fetch(PDO::FETCH_ASSOC);
        return $v ? new Tag($v['id'], $v['name']) : null;
    }

    // ============================================================
    //  Extensions (DLC)
    // ============================================================

    /**
     * @param bool $withVanilla true → inclut les extensions sans icon (Vanilla)
     * @return array<int, array<string, mixed>>
     */
    public function get_extensions(bool $withVanilla = false): array {
        $sql = 'SELECT `id`, `name`, `short_code`, `icon` FROM `extension`';
        if (!$withVanilla) $sql .= ' WHERE `icon` IS NOT NULL';
        $sql .= ' ORDER BY `id`';
        $rq = $this->bdd->prepare($sql);
        $rq->execute();
        return $rq->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // ============================================================
    //  Legacy
    // ============================================================

    public function insert_image(int $id, $image): void {
        $rq = $this->bdd->prepare('INSERT INTO `item_image` (`item_id`, `image`) VALUES (:id, :image);');
        $rq->bindValue(':id', $id, PDO::PARAM_INT);
        $rq->bindValue(':image', $image, PDO::PARAM_STR);
        $rq->execute();
    }
}
