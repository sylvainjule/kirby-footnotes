<li id="fn-<?php echo $order ?>" value="<?php echo $order ?>">
    <?php echo $note ?>
    <?php if(option('sylvainjule.footnotes.back') && option('sylvainjule.footnotes.links')): ?>
        <?php foreach($refs as $i => $refCount): ?>
        <span class="footnotereverse">
            <a href="#fnref-<?php echo $refCount ?>" title="<?php echo option('sylvainjule.footnotes.back.title', t('footnotes.back.title')) ?> <?php echo $order ?><?php echo count($refs) > 1 ? ' ' . chr(97 + $i) : '' ?>">
                <?php echo option('sylvainjule.footnotes.back') ?><?php echo count($refs) > 1 ? '&nbsp;' . chr(97 + $i) : '' ?>
            </a>
        </span>
        <?php endforeach; ?>
    <?php endif; ?>
</li>
