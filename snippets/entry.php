<li id="fn-<?php echo $count ?>" value="<?php echo $order ?>">
    <?php echo $note ?>
    <?php if(option('sylvainjule.footnotes.back') && option('sylvainjule.footnotes.links')): ?>
        <span class="footnotereverse">
            <a href="#fnref-<?php echo $count ?>" title="<?php echo option('sylvainjule.footnotes.back.title', t('footnotes.back.title')) ?> <?php echo $count ?>">
                <?php echo option('sylvainjule.footnotes.back') ?>
            </a>
        </span>
    <?php endif; ?>
</li>
