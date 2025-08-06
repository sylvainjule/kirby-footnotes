# Kirby Footnotes

This plugin extends [Kirby 3, 4 and 5](http://getkirby.com) with some basic, extremely easy and unopinionated footnote functionalities.

![footnotes-screenshot](https://user-images.githubusercontent.com/14079751/76997929-79cf0080-6954-11ea-87ce-bcb86b9d959f.jpg)

## Overview

> This plugin is completely free and published under the MIT license. However, if you are using it in a commercial project and want to help me keep up with maintenance, you can consider [making a donation of your choice](https://www.paypal.me/sylvainjl).

- [1. Installation](#1-installation)
- [2. Basic usage](#2-basic-usage)
- [3. Advanced usage](#3-advanced-usage)
  * [3.1. Collect notes from multiple fields](#31-collect-notes-from-multiple-fields)
  * [3.2. Usage with blocks](#32-usage-with-blocks)
- [4. Frontend customization](#4-frontend-customization)
- [5. Options](#5-options)
- [6. Methods](#6-methods)
- [7. License](#7-license)
- [8. Credits](#8-credits)

<br/>

## 1. Installation

Download and copy this repository to ```/site/plugins/footnotes```

Alternatively, you can install it with composer: ```composer require sylvainjule/footnotes```

<br/>

## 2. Basic usage

Use the footnotes method on your field: `$page->text()->footnotes()` or `$page->text()->ft()` (no need to call `->kirbytext()` before or after, this method will take care of it).

Adding footnotes to your Kirbytext field is simple. Just type them inline in your post in square brackets like this:

```
[^This is a footnote.]
[^ This is another.]
```

Each footnote must start with a caret (`^`) and will be numbered automatically. Footnotes can contain anything you want including links or images, but please note that **you should not include unescaped square brackets [] inside a footnote.**

If you add square brackets in a note (`This is a truncated […] quote` for example), you must escape the closing bracket belonging to the note:

```
[^ This is a truncated […\] quote]
```

For example, with the default setup this text:

> This is a footnote.[^Right here!] Here is a test with a footnote that contains a link.[^ Yes, there is indeed (link: https://getkirby.com text: a link.)] And, well, just to be sure things are working I'm throwing a third footnote in here.[^ All good!]

Will output:

![Footnotes example](https://user-images.githubusercontent.com/14079751/76996677-32e00b80-6952-11ea-8ed5-870981fd0305.jpg)

<br/>

## 3. Advanced usage

### 3.1. Collect notes from multiple fields

If you have multiple fields on your page you'd like to collect footnotes from, an output a signle container at the end of your page, instead of using the `footnotes()` method, you can use the `collectFootnotes()` one.

This method will return the text with footnotes references, no footnotes container, and store the footnotes container for later use.

For example:

```php
// somewhere in your template
echo $page->text1()->collectFootnotes();

// somewhere else in your template
echo $page->text2()->collectFootnotes();

// at the end of your template,
// echo the footnotes container with all collected footnotes
echo Footnotes::footnotes();
```

### 3.2. Usage with blocks

The plugins provides a `collectFootnotes()` blocks method, intended to collect all footnotes found in the converted blocks (you need to chain it after the `toBlocks()` method). 

```php
// collect the footnotes and return the html with footnotes references
echo $page->blocks->toBlocks()->collectFootnotes();

// at the end of your template,
// echo the footnotes container with all collected footnotes
echo Footnotes::footnotes();
```

<br/>

## 4. Frontend customization

As you can see with the raw output above, the plugin is completely unopinionated. It doesn't ship with any CSS or JS code but provides the markup to adjust its styling to suit your website.

Here is a reference of the outputted markup and classes to grab for styling:

```html
<p>
    This is a footnote.<sup class="footnote"><a id="fnref-1" href="#fn-1" aria-describedby="fn-1">1</a></sup> Here is a test with a footnote that contains a link.<sup class="footnote"><a id="fnref-2" href="#fn-2" aria-describedby="fn-2">2</a></sup> And, well, just to be sure things are working I'm throwing a third footnote in here.<sup class="footnote"><a id="fnref-3" href="#fn-3" aria-describedby="fn-3">3</a></sup>
</p>
<div id="footnotes" class="footnotes-container">
    <ol class="footnotes-list">
        <li id="fn-1" value="1">
            Right here! <span class="footnotereverse"><a href="#fnref-1" title="Back to content 1">↩</a></span>
        </li>
        <li id="fn-2" value="2">
            Yes, there is indeed <a href="https://getkirby.com">a link.</a><span class="footnotereverse"><a href="#fnref-2" title="Back to content 2">↩</a></span>
        </li>
        <li id="fn-3" value="3">
            All good! <span class="footnotereverse"><a href="#fnref-3" title="Back to content 3">↩</a></span>
        </li>
    </ol>
</div>
```

In your stylesheet:

```css
sup.footnote {}         /* Footnote reference within text */
.footnotes-container {} /* Footnotes container */
.footnotes-list {}      /* Footnotes ordered list */
.footnotes-list li {}   /* Footnote entry */
.footnotereverse {}     /* Footnote back link ↩ */
```

<br/>

## 5. Options

There are a few options:

### 5.1. Wrapper

For semantic purposes, you can manually set which HTML tag to use as footnotes container, `aside`, `footer`, etc. (default is a simple `div`)

```php
'sylvainjule.footnotes.wrapper'  => 'div'
```

### 5.2. Back

The string displayed at the end of a footnote, linking to its reference within the text. Default is `&#8617;` / ↩.

```php
'sylvainjule.footnotes.back'  => '&#8617;'
```

If you don't want any return link to appear, set this value to `false`.

### 5.3. Back title

The title attribute set on the return link for accessibility purposes. Default is `Back to content {{index}}` (in english, see [this folder](https://github.com/sylvainjule/kirby-footnotes/tree/master/lib/languages) for all available translations — don’t hesitate to PR one if missing). You custom title will be suffixed with the `{{index}}` of the footnote.

```php
'sylvainjule.footnotes.back.title'  => 'Back to content', // -> 'Back to content 1', 'Back to content 2', etc.
'sylvainjule.footnotes.back.title'  => 'Custom title',    // -> 'Custom title 1', 'Custom title 2', etc.
```

If you don't want any return link to appear, set this value to `false`.

### 5.4. Links

If you don't want the footnote references and footnotes to be links, for example if you are displaying them as sidenotes instead of footnotes, set this to `false`. Default is `true`.

```php
'sylvainjule.footnotes.links'  => false
```

If set to `false`, the footnote's _back_ link won't be appended to the footnote, and the syntax of the footnote reference within the text changes :

```html
<!-- from -->
<sup class="footnote">
    <a id="fnref-1" href="#fn-1">1</a>
</sup>

<!-- to -->
<sup id="fnref-1" class="footnote" data-ref="#fn-1">1</sup>
```

### 5.5. Snippets

If you want to overwrite one of [the default snippets](https://github.com/sylvainjule/kirby-footnotes/tree/master/snippets), you can place a file sharing the same name in your `site/snippets` folder.
If you want to register a custom name / path for one of these 3 snippets, you can do so from your config file:

```php
'sylvainjule.footnotes.snippet.container' => 'footnotes_container',
'sylvainjule.footnotes.snippet.entry'     => 'footnotes_entry',
'sylvainjule.footnotes.snippet.reference' => 'footnotes_reference'

↓

'sylvainjule.footnotes.snippet.container' => 'custom/path/to/footnotes_container',
'sylvainjule.footnotes.snippet.entry'     => 'custom/path/to/footnotes_entry',
'sylvainjule.footnotes.snippet.reference' => 'custom/path/to/footnotes_reference'
```


<br/>

## 6. Methods

```php
// returns the text with footnotes references and a bottom footnote container
echo $page->text()->footnotes();

// returns the text without footnotes references nor bottom footnotes container
echo $page->text()->removeFootnotes();

// returns the text with footnotes references but no bottom footnotes container
echo $page->text()->withoutFootnotes();

// returns only the footnotes container
echo $page->text()->onlyFootnotes();

// returns the text with footnotes references, no footnotes container, but stores the footnotes container for later use
echo $page->text1()->collectFootnotes();
echo $page->text2()->collectFootnotes();

// returns the footnotes container with all collected footnotes and clears the memory
echo Footnotes::footnotes();
```

<br/>

## 7. License

MIT

<br/>

## 8. Credits

This plugin has been built on top of the K2 version by [@distantnative](https://github.com/distantnative/footnotes). 🙏
