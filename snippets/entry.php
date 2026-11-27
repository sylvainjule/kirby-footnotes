<?php $entryId  = $entryId ?? $count;
      $refs     = $refs ?? [$count];
      $multiple = count($refs) > 1; ?>

<li id="fn-<?php echo $entryId ?>" value="<?php echo $order ?>">
    <?php echo $note ?>
    <?php if(option('sylvainjule.footnotes.back') && option('sylvainjule.footnotes.links')): ?>
        <?php foreach($refs as $i => $refCount): ?>
        <?php $suffix = $multiple ? Footnotes::backlinkSuffix($i) : ''; ?>
        <span class="footnotereverse">
            <a href="#fnref-<?php echo $refCount ?>" title="<?php echo option('sylvainjule.footnotes.back.title', t('footnotes.back.title')) ?> <?php echo $order ?><?php echo $multiple ? ' ' . $suffix : '' ?>">
                <?php echo option('sylvainjule.footnotes.back') ?><?php echo $multiple ? '&nbsp;' . $suffix : '' ?>
            </a>
        </span>
        <?php endforeach; ?>
    <?php endif; ?>
</li>