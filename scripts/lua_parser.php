<?php
/**
 * Mini parseur Lua pour les modules Module:Items/Data et Module:Equipment/Data
 * du wiki Fandom Risk of Rain 2.
 *
 * Sous-ensemble supporté (suffisant pour ces modules) :
 *   - strings "..." (avec échappements \" et \\) et '...'
 *   - nombres entiers et flottants
 *   - booléens true / false / nil
 *   - tables { ... } : liste, dict (clé identifiant ou ["chaîne"]), mixte
 *   - commentaires -- ligne, --[[ bloc ]]
 *   - assignations top-level : items["X"] = { ... }  /  equipment["X"] = { ... }
 *
 * Sortie : tableau associatif PHP ['NomAffiché' => ['Rarity'=>..., 'Stats'=>[...], ...]].
 */

class LuaParseError extends RuntimeException {}

class LuaParser {
    private string $src;
    private int $pos = 0;
    private int $len;

    public function __construct(string $src) {
        $this->src = $src;
        $this->len = strlen($src);
    }

    /**
     * Parse l'ensemble du module, retourne uniquement les entrées de la
     * forme `<varName>[<stringKey>] = <table>`.
     *
     * @param string $varName  Nom de la variable Lua (ex: "items", "equipment")
     * @return array<string, array>
     */
    public function parseModule(string $varName): array {
        $out = [];
        $needle = $varName . '[';
        $vlen = strlen($needle);

        while ($this->pos < $this->len) {
            $idx = strpos($this->src, $needle, $this->pos);
            if ($idx === false) break;

            $this->pos = $idx + $vlen;
            $this->skipWs();
            $key = $this->readString();
            $this->skipWs();
            $this->expect(']');
            $this->skipWs();
            $this->expect('=');
            $this->skipWs();
            $val = $this->readValue();

            if (is_array($val)) {
                $out[$key] = $val;
            }
        }

        return $out;
    }

    // ----------------------------------------------------------------
    // Internals
    // ----------------------------------------------------------------

    private function peek(int $offset = 0): string {
        $p = $this->pos + $offset;
        return $p < $this->len ? $this->src[$p] : '';
    }

    private function expect(string $c): void {
        if ($this->peek() !== $c) {
            throw new LuaParseError("expected '$c' at offset {$this->pos}, got '" . $this->peek() . "'");
        }
        $this->pos++;
    }

    private function skipWs(): void {
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if ($c === ' ' || $c === "\t" || $c === "\n" || $c === "\r") {
                $this->pos++;
                continue;
            }
            // Commentaire ?
            if ($c === '-' && $this->peek(1) === '-') {
                $this->pos += 2;
                if ($this->peek() === '[' && $this->peek(1) === '[') {
                    // Bloc
                    $this->pos += 2;
                    $end = strpos($this->src, ']]', $this->pos);
                    $this->pos = $end === false ? $this->len : $end + 2;
                } else {
                    // Ligne
                    $nl = strpos($this->src, "\n", $this->pos);
                    $this->pos = $nl === false ? $this->len : $nl + 1;
                }
                continue;
            }
            break;
        }
    }

    private function readString(): string {
        $quote = $this->peek();
        if ($quote !== '"' && $quote !== "'") {
            throw new LuaParseError("expected string at offset {$this->pos}");
        }
        $this->pos++;
        $buf = '';
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if ($c === '\\') {
                $next = $this->peek(1);
                switch ($next) {
                    case 'n':  $buf .= "\n"; break;
                    case 't':  $buf .= "\t"; break;
                    case 'r':  $buf .= "\r"; break;
                    case '"':  $buf .= '"';  break;
                    case "'":  $buf .= "'";  break;
                    case '\\': $buf .= '\\'; break;
                    default:   $buf .= $next; break;
                }
                $this->pos += 2;
                continue;
            }
            if ($c === $quote) {
                $this->pos++;
                return $buf;
            }
            $buf .= $c;
            $this->pos++;
        }
        throw new LuaParseError("unterminated string");
    }

    /**
     * @return mixed
     */
    private function readValue() {
        $this->skipWs();
        $c = $this->peek();

        if ($c === '"' || $c === "'") return $this->readString();
        if ($c === '{')               return $this->readTable();
        if ($c === '-' || ctype_digit($c)) return $this->readNumber();

        // Identifier : true/false/nil/...
        if (ctype_alpha($c) || $c === '_') {
            $start = $this->pos;
            while ($this->pos < $this->len) {
                $cc = $this->src[$this->pos];
                if (ctype_alnum($cc) || $cc === '_' || $cc === '.') {
                    $this->pos++;
                } else {
                    break;
                }
            }
            $word = substr($this->src, $start, $this->pos - $start);
            if ($word === 'true')  return true;
            if ($word === 'false') return false;
            if ($word === 'nil')   return null;
            return $word;
        }

        throw new LuaParseError("unexpected char '$c' at offset {$this->pos}");
    }

    private function readNumber() {
        $start = $this->pos;
        if ($this->peek() === '-') $this->pos++;
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if (ctype_digit($c) || $c === '.') {
                $this->pos++;
            } else {
                break;
            }
        }
        $raw = substr($this->src, $start, $this->pos - $start);
        return strpos($raw, '.') === false ? (int)$raw : (float)$raw;
    }

    private function readTable(): array {
        $this->expect('{');
        $this->skipWs();
        $out = [];
        $listIdx = 1;

        while ($this->peek() !== '}') {
            $this->skipWs();
            $c = $this->peek();

            if ($c === '[') {
                // [key] = value
                $this->pos++;
                $this->skipWs();
                $key = $this->readString();
                $this->skipWs();
                $this->expect(']');
                $this->skipWs();
                $this->expect('=');
                $out[$key] = $this->readValue();
            } else {
                // Identifier = value  OU  value (list)
                $savedPos = $this->pos;
                $isAssign = false;

                if (ctype_alpha($c) || $c === '_') {
                    $idStart = $this->pos;
                    while ($this->pos < $this->len) {
                        $cc = $this->src[$this->pos];
                        if (ctype_alnum($cc) || $cc === '_') $this->pos++;
                        else break;
                    }
                    $id = substr($this->src, $idStart, $this->pos - $idStart);
                    $this->skipWs();
                    if ($this->peek() === '=' && $this->peek(1) !== '=') {
                        $this->pos++;
                        $isAssign = true;
                        $out[$id] = $this->readValue();
                    } else {
                        $this->pos = $savedPos;
                    }
                }

                if (!$isAssign) {
                    $out[$listIdx++] = $this->readValue();
                }
            }

            $this->skipWs();
            // Séparateur , ou ; (optionnel avant '}')
            if ($this->peek() === ',' || $this->peek() === ';') {
                $this->pos++;
                $this->skipWs();
            }
        }

        $this->expect('}');
        return $out;
    }
}

/**
 * Helper public : parse un module Lua complet.
 */
function lua_parse_module(string $src, string $varName): array {
    return (new LuaParser($src))->parseModule($varName);
}
