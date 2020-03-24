<?php

class Footnotes {

    public static function convert($text, $without = false, $only = false) {
        $text = kirbytext($text);

        $matches    = null;
        $references = null;
        $notes      = null;
        $notesStr   = '';

        // if there are notes
        if(preg_match_all('/\[(\^.*?)\]/s', $text, $matches)) {
            // return text without notes if needed
            if($without) return self::remove($text, $matches);

            $references = $matches[0];
            $notes      = self::strip($matches);

            $count = 1;
            $order = 1;

            foreach($notes as $key => $note) {
                $data      = ['count' => $count, 'order' => $order, 'note' => $note];
                $text      = str_replace($references[$key], snippet('footnotes_reference', $data, true), $text);
                $notesStr .= snippet('footnotes_entry', $data, true);

                $count++;
                $order++;
            }

            $output = snippet('footnotes_container', ['footnotes' => $notesStr], true);

            return $only ? $output : $text . $output;
        }
        else {
            return $only ? '' : $text;
        }
    }

    /* Utils
    --------------------------*/

    public static function matches($text) {
        return preg_match_all('/\[(\^.*?)\]/s', $text, $matches);
    }
    public static function strip($matches) {
        return array_map(function($match) use($matches) {
          $match = preg_replace('/\[(\^(.*?))\]/s', '\2', $match);
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

}
