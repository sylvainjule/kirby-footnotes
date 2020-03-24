<li id="fn-<?php echo $count ?>" value="<?php echo $order ?>">
    <?php echo $note ?>
    <?php if(option('sylvainjule.footnotes.back')): ?>
        <span class="footnotereverse">
            <a href="#fnref-<?php echo $count ?>">
                <?php echo option('sylvainjule.footnotes.back') ?>
            </a>
        </span>
    <?php endif; ?>
</li>
