<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\Widget;

use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Widget\Widget;
use Yiisoft\Bootstrap5\Alert;

final class FlashMessage extends Widget
{
    public function __construct(private readonly FlashInterface $flash)
    {
    }

    #[\Override]
    public function render(): string
    {
        $flashes = $this->flash->getAll();

        $html = [];
        foreach ($flashes as $type => $data) {
            foreach ($data as $message) {
                $html[] = Alert::widget()
                    ->addAttributes(['class' => "alert-{$type} shadow"])
                    ->body($message['body'])
                ;
            }
        }

        return implode('', $html);
    }
}
