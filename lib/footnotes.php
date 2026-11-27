<?php

class Footnotes {
    public static array $footnotes = [];

    protected static ?array $collection = null;
    protected static array $diagnostics = [];

    public static function convert($text, $remove = false, $without = false, $only = false, $kt = true, $startAt = 1, $unwrapped = false) {
        $text = $kt ? kirbytext($text, ['parent' => $text->parent()]) : $text;

        if(!preg_match_all('/\[(\^.*?)(?<!\\\)\]/s', $text, $matches)) {
            return $only ? '' : $text;
        }
        if($remove) {
            return self::remove($text, $matches);
        }

        $collect = $without && !$only && option('sylvainjule.footnotes.pinning', false);
        $state   = $collect ? (self::$collection ?? self::newState($startAt)) : self::newState($startAt);

        if($collect) {
            $state['count'] = max($state['count'], $startAt);
            $state['next']  = max($state['next'], $startAt);
        }

        $references = self::resolve(self::parse($matches), $state);
        $text       = self::renderReferences($text, $references, $state);
        $notesArr   = self::renderEntries($state);

        self::report($state);

        if($without && !$only) {
            if($collect) {
                self::$collection = $state;
                self::$footnotes  = $notesArr;
            }
            else {
                self::$footnotes = array_merge(self::$footnotes, $notesArr);
            }

            return $text;
        }

        $output = $unwrapped ? $notesArr : snippet(option('sylvainjule.footnotes.snippet.container'), ['footnotes' => implode('', $notesArr)], true);

        return $only ? $output : $text . $output;
    }

    public static function footnotes($purge = true, $unwrapped = false) {
        $footnotes = self::$footnotes;

        if($purge) {
            self::$footnotes   = [];
            self::$collection  = null;
            self::$diagnostics = [];
        }

        if($unwrapped) {
            return $footnotes;
        }

        return snippet(option('sylvainjule.footnotes.snippet.container'), ['footnotes' => join('', $footnotes)], true);
    }


    /* Utils
    --------------------------*/

    public static function matches($text) {
        return preg_match_all('/\[(\^.*?)\]/s', $text, $matches);
    }

    protected static function newState($startAt = 1) {
        return [
            'count'       => $startAt,
            'next'        => $startAt,
            'reserved'    => [],
            'definitions' => [],
            'pins'        => [],
            'entries'     => [],
            'problems'    => [],
        ];
    }

    /**
     * Extracts each note's raw content and, if present, an explicit pin
     * number written as `[^N: Text]`. Notes without that prefix stay
     * unpinned (`pin` is null) and get auto-numbered by `convert()`.
     */

    protected static function rawNote($match) {
        return substr($match, 2, -1);
    }

    protected static function renderNote($raw) {
        return str_replace(['<p>', '</p>'], '', kirbytext($raw));
    }

    public static function strip($matches) {
        return array_map(function($match) {
            return self::renderNote(self::rawNote($match));
        }, $matches[0]);
    }

    protected static function parse($matches) {
        $pinning = option('sylvainjule.footnotes.pinning', false);

        return array_map(function($match) use($pinning) {
            $raw = self::rawNote($match);
            $pin = null;

            if($pinning && preg_match('/^([1-9][0-9]*):\s*(.*)$/s', $raw, $parts)) {
                $number = filter_var($parts[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if($number !== false) {
                    $pin = $number;
                    $raw = $parts[2];
                }
            }

            return [
                'pin'  => $pin,
                'note' => self::renderNote($raw),
            ];
        }, $matches[0]);
    }

    public static function collect($text, $kt = true) {
        $start = option('sylvainjule.footnotes.pinning', false) ? 1 : count(self::$footnotes) + 1;

        return self::convert($text, false, true, false, $kt, $start);
    }
    protected static function reserve(array $notes, array &$state) {
        foreach($notes as $note) {
            if($note['pin'] === null) { continue; }

            $pin     = $note['pin'];
            $content = $note['note'];

            $state['reserved'][$pin] = true;

            if(!isset($state['definitions'][$pin]) || trim($state['definitions'][$pin]) === '') {
                $state['definitions'][$pin] = $content;
            }
            elseif(trim($content) !== '' && $state['definitions'][$pin] !== $content) {
                $state['problems']['conflict-' . $pin] = "Conflicting definitions for footnote {$pin}.";
            }
        }
    }
    protected static function nextOrder(array &$state) {
        while(isset($state['reserved'][$state['next']]) || isset($state['entries'][$state['next']])) {
            $state['next']++;
        }

        return $state['next']++;
    }
    protected static function resolve(array $notes, array &$state) {
        self::reserve($notes, $state);

        $references = [];

        foreach($notes as $note) {
            $pin   = $note['pin'];
            $count = $state['count']++;

            if($pin === null) {
                $order = self::nextOrder($state);
                $content = $note['note'];
            }
            else {
                if(!isset($state['pins'][$pin])) {
                    if(isset($state['entries'][$pin])) {
                        $state['pins'][$pin] = self::nextOrder($state);
                        $state['problems']['collision-' . $pin] = "Footnote {$pin} was already assigned.";
                    }
                    else {
                        $state['pins'][$pin] = $pin;
                    }
                }

                $order = $state['pins'][$pin];
                $content = $state['definitions'][$pin];
            }

            if(!isset($state['entries'][$order])) {
                $state['entries'][$order] = [
                    'count'   => $count,
                    'order'   => $order,
                    'entryId' => $count,
                    'note'    => $content,
                    'refs'    => [],
                ];
            }

            $state['entries'][$order]['note'] = $content;
            $state['entries'][$order]['refs'][] = $count;

            $references[] = [
                'count' => $count,
                'order' => $order,
            ];
        }

        return $references;
    }
    protected static function renderEntries(array $state) {
        $entries = $state['entries'];

        ksort($entries, SORT_NUMERIC);

        return array_map(function($entry) {
            return snippet(option('sylvainjule.footnotes.snippet.entry'), $entry, true);
        }, array_values($entries));
    }
    protected static function renderReferences($text, array $references, array $state) {
        $index = 0;

        return preg_replace_callback('/\[(\^.*?)(?<!\\\)\]/s', function($match) use(&$index, $references, $state) {
                $reference = $references[$index++];
                $entry    = $state['entries'][$reference['order']];

                $data = array_merge($entry, [
                    'count' => $reference['count'],
                ]);

                return snippet(option('sylvainjule.footnotes.snippet.reference'), $data, true);
            }, $text);
    }


    public static function backlinkSuffix($index) {
        $suffix = '';

        do {
            $suffix = chr(97 + ($index % 26)) . $suffix;
            $index = intdiv($index, 26) - 1;
        } while($index >= 0);

        return $suffix;
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


    /* Debug
    --------------------------*/

    protected static function report(array $state) {
        $problems = $state['problems'];

        foreach($state['pins'] as $pin => $order) {
            if(trim($state['definitions'][$pin]) === '') {
                $problems['missing-' . $pin] = "Missing definition for footnote {$pin}.";
            }
        }

        self::$diagnostics = array_values($problems);
    }
    public static function diagnostics() {
        return self::$diagnostics;
    }

}
