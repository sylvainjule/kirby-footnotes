<?php

class Footnotes {
    public static array $footnotes = [];

    public static function convert($text, $remove = false, $without = false, $only = false, $kt = true, $startAt = 1, $unwrapped = false) {
        $text = $kt ? kirbytext($text, ['parent' => $text->parent()]) : $text;

        $matches    = null;
        $references = null;
        $notes      = null;
        $notesStr   = '';
        $notesArr   = [];

        // if there are notes
        if(preg_match_all('/\[(\^.*?)(?<!\\\)\]/s', $text, $matches)) {
            // return text without notes if needed
            if($remove) return self::remove($text, $matches);

            $references = $matches[0];
            $notes      = self::strip($matches);

            $count = $startAt;
            $order = $startAt;

            foreach($notes as $key => $note) {
                $data       = ['count' => $count, 'order' => $order, 'note' => $note];
                $text       = self::str_replace_first($references[$key], snippet(option('sylvainjule.footnotes.snippet.reference'), $data, true), $text);
                $notesStr  .= snippet(option('sylvainjule.footnotes.snippet.entry'), $data, true);
                $notesArr[] = snippet(option('sylvainjule.footnotes.snippet.entry'), $data, true);

                $count++;
                $order++;
            }

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
    public static function strip($matches) {
        return array_map(function($match) use($matches) {
          $match = preg_replace('/\[(\^(.*?))(?<!\\\)\]/s', '\2', $match);
          $match = str_replace(array('<p>','</p>'), '', kirbytext($match));
          return $match;
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
