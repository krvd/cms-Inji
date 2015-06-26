<?php

$block = \TextBlocks\Block::get($params[0], 'code');
echo "<div class = 'fastEdit' data-model='\TextBlocks\Block' data-col='text' data-key='{$block->id}'>";
echo $block->text;
echo '</div>';
