<?php

use Kirby\Cms\App as Kirby;

require_once __DIR__ . '/lib/footnotes.php';

Kirby::plugin('sylvainjule/footnotes', [
    'options' => array(
        'wrapper'  => 'div',
        'back'     => '&#8617;',
        'links'    => true,
    ),
    'fieldMethods' => [
        'footnotes' => function($field) {
            return Footnotes::convert($field->text());
        },
        'ft' => function($field) {
            return $field->footnotes();
        },
        'collectFootnotes' => function($field) {
            $start = count(Footnotes::$footnotes) + 1;
            return Footnotes::convert($field->text(), false, true, false, true, $start);
        },
        'removeFootnotes' => function($field) {
            return Footnotes::convert($field->text(), true);
        },
        'withoutFootnotes' => function($field) {
            return Footnotes::convert($field->text(), false, true);
        },
        'onlyFootnotes' => function($field) {
            return Footnotes::convert($field->text(), false, false, true);
        },
    ],
    'blocksMethods' => [
        'collectFootnotes' => function() {
            $start = count(Footnotes::$footnotes) + 1;
            return Footnotes::convert($this->toHtml(), false, true, false, false, $start);
        },
    ],
    'snippets'     => [
        'footnotes_container' => __DIR__ . '/snippets/container.php',
        'footnotes_entry'     => __DIR__ . '/snippets/entry.php',
        'footnotes_reference' => __DIR__ . '/snippets/reference.php'
    ]
]);
