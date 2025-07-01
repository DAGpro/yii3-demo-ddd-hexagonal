<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\P;

/** @var string $content */

echo P::tag()
    ->content(Html::encode($content));
