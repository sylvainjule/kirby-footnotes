<?php if(option('sylvainjule.footnotes.links')): ?>
<sup class="footnote"><a id="fnref-<?php echo $count ?>" href="#fn-<?php echo $order ?>" aria-describedby="fn-<?php echo $order ?>"><?php echo $order ?></a></sup>
<?php else: ?>
<sup id="fnref-<?php echo $count ?>" class="footnote" data-ref="#fn-<?php echo $order ?>"><?php echo $order ?></sup>
<?php endif; ?>
