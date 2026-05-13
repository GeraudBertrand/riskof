<?php
/**
 * Classe miroir de la table `equipment`.
 */
class Equipment implements \JsonSerializable {

    public ?int    $id                 = null;
    public ?string $game_internal_name = null;
    public ?string $name               = null;
    public ?string $description        = null;
    public ?string $description_html   = null;
    public ?string $lore               = null;
    public ?string $image              = null;
    public ?int    $cooldown_seconds   = null;
    public ?float  $duration_seconds   = null;
    public ?string $rarity_label       = null;
    public ?string $unlock_requirement = null;
    public ?int    $orig               = null;
    public bool    $is_hidden          = false;

    public function __construct(array $row) {
        foreach ($row as $col => $val) {
            if (!property_exists($this, $col)) continue;

            if ($val === null) {
                $this->$col = null;
                continue;
            }

            switch ($col) {
                case 'id':
                case 'cooldown_seconds':
                case 'orig':
                    $this->$col = (int)$val;
                    break;
                case 'duration_seconds':
                    $this->$col = (float)$val;
                    break;
                case 'is_hidden':
                    $this->$col = (bool)$val;
                    break;
                default:
                    $this->$col = (string)$val;
            }
        }
    }

    public function getId():               ?int    { return $this->id; }
    public function getName():             ?string { return $this->name; }
    public function getDescription():      ?string { return $this->description; }
    public function getDescriptionHtml():  ?string { return $this->description_html; }
    public function getLore():             ?string { return $this->lore; }
    public function getImage():            ?string { return $this->image; }
    public function getCooldown():         ?int    { return $this->cooldown_seconds; }
    public function getDuration():         ?float  { return $this->duration_seconds; }
    public function getRarityLabel():      ?string { return $this->rarity_label; }
    public function getOrig():             ?int    { return $this->orig; }
    public function isHidden():            bool    { return $this->is_hidden; }

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}
