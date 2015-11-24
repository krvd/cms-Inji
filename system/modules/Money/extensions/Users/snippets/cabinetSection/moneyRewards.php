<?php

if (!Money\Reward::getCount(['where' => ['active', 1]])) {
    return false;
}
return [
    'name' => 'Партнерские вознаграждения',
    'fullWidget' => 'Money\cabinet/rewards',
];
