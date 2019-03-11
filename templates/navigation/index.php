<ul>
    <?php foreach($_['all_nodes'] as $node) { ?>
        <li>
            <img src="/index.php/apps/cookbook/image?recipe=<? echo $node->getId(); ?>&size=thumb">
            <a href="?recipe=<?php echo $node->getId(); ?>"><?php echo basename($node->getName(), '.json'); ?></a>
        </li>
    <?php } ?>
</ul>
