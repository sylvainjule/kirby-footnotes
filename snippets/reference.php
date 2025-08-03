<?php if(option('sylvainjule.footnotes.links')): ?>
<sup class="footnote"><a id="fnref-<?php echo $count ?>" href="#fn-<?php echo $count ?>" aria-describedby="fn-<?php echo $count ?>"><?php echo $order ?></a></sup>
<?php else: ?>
<sup id="fnref-<?php echo $count ?>" class="footnote" data-ref="#fn-<?php echo $count ?>"><?php echo $order ?></sup>
<?php endif; ?>
