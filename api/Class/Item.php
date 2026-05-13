<?php
/**
 * Classe miroir de la table `item` (post-migration 001).
 * Hydratée depuis une ligne de PDO en mode FETCH_ASSOC.
 */
class Item implements \JsonSerializable {

    public ?int    $id                 = null;
    public ?string $game_internal_name = null;
    public ?string $name               = null;
    public ?int    $rarity             = null;
    public ?int    $orig               = null;
    public ?string $description        = null;   // pickup / quote (court)
    public ?string $description_html   = null;   // version stylisée du desc long
    public ?string $lore               = null;
    public ?string $image              = null;
    public ?string $effect             = null;   // desc long plain text
    public ?string $unlock_requirement = null;
    public ?string $corrupt_pair       = null;
    public bool    $is_hidden          = false;

    public ?string $base1  = null;
    public ?string $stack1 = null;
    public ?string $base2  = null;
    public ?string $stack2 = null;
    public ?string $base3  = null;
    public ?string $stack3 = null;

    public function __construct(array $row) {
        foreach ($row as $col => $val) {
            if (!property_exists($this, $col)) continue;

            if ($val === null) {
                $this->$col = null;
                continue;
            }

            switch ($col) {
                case 'id':
                case 'rarity':
                case 'orig':
                    $this->$col = (int)$val;
                    break;
                case 'is_hidden':
                    $this->$col = (bool)$val;
                    break;
                default:
                    $this->$col = (string)$val;
            }
        }
    }

    public function getId():                ?int    { return $this->id; }
    public function getName():              ?string { return $this->name; }
    public function getDescription():       ?string { return $this->description; }
    public function getDescriptionHtml():   ?string { return $this->description_html; }
    public function getLore():              ?string { return $this->lore; }
    public function getEffect():            ?string { return $this->effect; }
    public function getImage():             ?string { return $this->image; }
    public function getRarity():            ?int    { return $this->rarity; }
    public function getOrig():              ?int    { return $this->orig; }
    public function getBase1():             ?string { return $this->base1; }
    public function getStack1():            ?string { return $this->stack1; }
    public function getBase2():             ?string { return $this->base2; }
    public function getStack2():            ?string { return $this->stack2; }
    public function getBase3():             ?string { return $this->base3; }
    public function getStack3():            ?string { return $this->stack3; }
    public function getUnlockRequirement(): ?string { return $this->unlock_requirement; }
    public function getCorruptPair():       ?string { return $this->corrupt_pair; }
    public function getGameInternalName():  ?string { return $this->game_internal_name; }
    public function isHidden():             bool    { return $this->is_hidden; }

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}
