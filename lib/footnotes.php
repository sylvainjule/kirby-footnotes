<?php

class Footnotes {
    public static array $footnotes = [];

    public static function convert($text, $remove = false, $without = false, $only = false, $kt = true, $startAt = 1, $unwrapped = false) {
        $text = $kt ? kirbytext($text, ['parent' => $text->parent()]) : $text;

        $matches = null;

        // if there are notes
        if(preg_match_all('/\[(\^.*?)(?<!\\\)\]/s', $text, $matches)) {
            // return text without notes if needed
            if($remove) return self::remove($text, $matches);

            $references = $matches[0];
            $notes      = self::strip($matches);

            // Pass 1: resolve every explicitly pinned number ([^N: Text]) to
            // its footnote text. The first non-empty text for a given pin
            // wins; later occurrences of the same pin are references to
            // that same footnote (see pass 2), letting a manually pinned
            // note be reused multiple times in the text.
            $reserved    = [];
            $definitions = [];
            foreach($notes as $note) {
                if($note['pin'] === null) {
                    continue;
                }

                $order = $note['pin'];
                if(!in_array($order, $reserved, true)) {
                    $reserved[] = $order;
                }

                $hasDefinition = array_key_exists($order, $definitions) && trim($definitions[$order]) !== '';
                if(!$hasDefinition) {
                    $definitions[$order] = $note['note'];
                }
            }

            // Pass 2: walk the notes in text order. `count` is a plain
            // sequential index, used only for unique reference/backlink
            // anchor ids. `order` is the number that's actually displayed
            // and can be pinned out of sequence; auto-numbered notes are
            // assigned the next free number, skipping reserved pins.
            // Every reference id that resolves to the same `order` is
            // collected, so a reused pin renders as a single footnote entry
            // with one backlink per place it was referenced from.
            $count       = $startAt;
            $next        = $startAt;
            $refsByOrder = [];

            foreach($notes as $key => $note) {
                if($note['pin'] !== null) {
                    $order = $note['pin'];
                } else {
                    while(in_array($next, $reserved, true) || array_key_exists($next, $definitions)) {
                        $next++;
                    }
                    $order = $next;
                    $definitions[$order] = $note['note'];
                }

                $refsByOrder[$order][] = $count;

                $data = ['count' => $count, 'order' => $order];
                $text = self::str_replace_first($references[$key], snippet(option('sylvainjule.footnotes.snippet.reference'), $data, true), $text);

                $count++;
            }

            // The footnotes list is always sorted by displayed number, even
            // when pins reorder it relative to the text.
            ksort($definitions);

            $notesByOrder = [];
            foreach($definitions as $order => $noteText) {
                $refs = $refsByOrder[$order] ?? [$order];
                $data = ['count' => $refs[0], 'refs' => $refs, 'order' => $order, 'note' => $noteText];
                $notesByOrder[$order] = snippet(option('sylvainjule.footnotes.snippet.entry'), $data, true);
            }

            $notesArr = array_values($notesByOrder);
            $notesStr = implode('', $notesArr);

            $output = $unwrapped ? $notesArr : snippet(option('sylvainjule.footnotes.snippet.container'), ['footnotes' => $notesStr], true);

            if($only) { // return only the footnotes
                return $output;
            }
            elseif($without) { // return only the text with footnotes' numbers
                self::$footnotes = array_merge(self::$footnotes, $notesArr);
                return $text;
            }
            else {
                return $text . $output;
            }
        }
        else {
            return $only ? '' : $text;
        }
    }

    public static function footnotes($purge = true, $unwrapped = false) {
        $footnotes = self::$footnotes;
        if($purge) self::$footnotes = [];

        if($unwrapped) {
            return $footnotes;
        } else {
            return snippet(option('sylvainjule.footnotes.snippet.container'), ['footnotes' => join('', $footnotes)], true);
        }
    }

    /* Utils
    --------------------------*/

    public static function matches($text) {
        return preg_match_all('/\[(\^.*?)\]/s', $text, $matches);
    }

    /**
     * Extracts each note's raw content and, if present, an explicit pin
     * number written as `[^N: Text]`. Notes without that prefix stay
     * unpinned (`pin` is null) and get auto-numbered by `convert()`.
     */
    public static function strip($matches) {
        return array_map(function($match) use($matches) {
          $raw = preg_replace('/\[(\^(.*?))(?<!\\\)\]/s', '\2', $match);

          $pin = null;
          if(preg_match('/^(\d+):\s*(.*)$/s', $raw, $pinMatch)) {
              $pin = (int) $pinMatch[1];
              $raw = $pinMatch[2];
          }

          $note = str_replace(array('<p>','</p>'), '', kirbytext($raw));
          return ['pin' => $pin, 'note' => $note];
        }, $matches[0]);
    }
    public static function remove($text, $matches) {
        foreach($matches as $note) {
            $text = str_replace($note, '', $text);
        }
        return $text;
    }
    public static function str_replace_first($search, $replace, $str) {
        $pos = strpos($str, $search);
        if ($pos !== false) {
            return substr_replace($str, $replace, $pos, strlen($search));
        }
        return $str;
    }

}
