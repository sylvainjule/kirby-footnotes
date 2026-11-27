<?php $entryId = $entryId ?? $count; ?>

<?php if(option('sylvainjule.footnotes.links')): ?>
<sup class="footnote"><a id="fnref-<?php echo $count ?>" href="#fn-<?php echo $entryId ?>" aria-describedby="fn-<?php echo $entryId ?>"><?php echo $order ?></a></sup>
<?php else: ?>
<sup id="fnref-<?php echo $count ?>" class="footnote" data-ref="#fn-<?php echo $entryId ?>"><?php echo $order ?></sup>
<?php endif; ?>