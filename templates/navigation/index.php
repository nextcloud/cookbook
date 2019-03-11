<ul>
    <?php foreach($_['all_nodes'] as $node) { ?>
        <li><a href="?recipe=<?php echo $node->getId(); ?>"><?php echo basename($node->getName(), '.json'); ?></a></li>
    <?php } ?>
</ul>
