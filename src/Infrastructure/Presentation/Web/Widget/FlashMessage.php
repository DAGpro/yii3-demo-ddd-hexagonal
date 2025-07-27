<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\Widget;

use Override;
use Yiisoft\Bootstrap5\Alert;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Widget\Widget;

final class FlashMessage extends Widget
{
    public function __construct(
        private readonly FlashInterface $flash,
    ) {
    }

    #[Override]
    public function render(): string
    {
        $flashes = $this->getFlashes();
        $html = [];

        foreach ($flashes as $type => $messages) {
            foreach ($messages as $message) {
                if (isset($message['body'])) {
                    $html[] = Alert::widget()
                        ->addAttributes(['class' => "alert-{$type} shadow"])
                        ->body($message['body'])
                        ->render();
                }
            }
        }

        return implode('', $html);
    }

    /**
     * @return array<string, array<array-key, array{body: string}>>
     */
    private function getFlashes(): array
    {
        /** @var array<string, array<array-key, array{body: string}>> $flashes */
        $flashes = $this->flash->getAll();
        return $flashes;
    }
}
