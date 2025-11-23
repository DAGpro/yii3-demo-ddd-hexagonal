<?php

declare(strict_types=1);

use Yiisoft\Bootstrap5\Carousel;
use Yiisoft\Bootstrap5\CarouselItem;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var TranslatorInterface $translator
 * @var WebView $this
 */

$this->setTitle('Home');

echo Carousel::widget()
    ->items(
        CarouselItem::to(
            content: '<div class="d-block w-100 bg-primary" style="height: 200px"></div>',
            caption: $translator->translate('view-site.caption.slide1'),
            captionPlaceholder: $translator->translate('view-site.caption.slide1Placeholder'),
        ),
        CarouselItem::to(
            content: '<div class="d-block w-100 bg-danger" style="height: 200px"></div>',
            caption: $translator->translate('view-site.caption.slide2'),
            captionPlaceholder: $translator->translate('view-site.caption.slide2Placeholder'),
        ),
        CarouselItem::to(
            content: '<div class="d-block w-100 bg-warning" style="height: 200px"></div>',
            caption: $translator->translate('view-site.caption.slide3'),
            captionPlaceholder: $translator->translate('view-site.caption.slide3Placeholder'),
        ),
    );
?>


<div class="mt-3 col-md-8 ">
    <h2><?= $translator->translate('view-site.console') ?></h2>
    <?php
    $binPath = str_replace('/', DIRECTORY_SEPARATOR, './yii'); ?>
    <div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button
                    class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home"
                    type="button" role="tab" aria-controls="nav-home" aria-selected="true"
                >
                    <?= $translator->translate('view-site.home') ?>
                </button>
                <button
                    class="nav-link" id="nav-migration-tab" data-bs-toggle="tab" data-bs-target="#nav-migration"
                    type="button" role="tab" aria-controls="nav-home" aria-selected="true"
                >
                    <?= $translator->translate('view-site.migrations') ?>
                </button>
                <button
                    class="nav-link" id="nav-rbac-tab" data-bs-toggle="tab" data-bs-target="#nav-rbac" type="button"
                    role="tab" aria-controls="nav-profile" aria-selected="false"
                >
                    <?= $translator->translate('view-site.rbac') ?>
                </button>
                <button
                    class="nav-link" id="nav-user-tab" data-bs-toggle="tab" data-bs-target="#nav-user" type="button"
                    role="tab" aria-controls="nav-profile" aria-selected="false"
                >
                    <?= $translator->translate('view-site.users') ?>
                </button>
                <button
                    class="nav-link" id="nav-db-schema-tab" data-bs-toggle="tab" data-bs-target="#nav-db-schema"
                    type="button" role="tab" aria-controls="nav-contact" aria-selected="false"
                >
                    <?= $translator->translate('view-site.db.schema') ?>
                </button>
                <button
                    class="nav-link" id="nav-other-tab" data-bs-toggle="tab" data-bs-target="#nav-other"
                    type="button" role="tab" aria-controls="nav-contact" aria-selected="false"
                >
                    <?= $translator->translate('view-site.other') ?>
                </button>
            </div>
        </nav>
        <div class="tab-content border border-1 border-top-0 card-body p-3" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                <h4 class="card-title text-muted mt-2 mb-1"><?= $translator->translate('view-site.add.demo.access.rights',
                ) ?></h4>
                <div>
                    <code><?= "{$binPath} fixture:addAccess" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.add.random.content') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} fixture:add [count = 10]" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.create.user') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} user:create &lt;login&gt; &lt;password&gt; [isAdmin = 0]" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.add.admin.backend') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} assign:addRole &lt;userId&gt; admin" ?></code>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-migration" role="tabpanel" aria-labelledby="nav-profile-tab">
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.migrations') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} migrate:create" ?></code>
                    <br><code><?= "{$binPath} migrate:generate" ?></code>
                    <br><code><?= "{$binPath} migrate:up" ?></code>
                    <br><code><?= "{$binPath} migrate:down" ?></code>
                    <br><code><?= "{$binPath} migrate:list" ?></code>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-rbac" role="tabpanel" aria-labelledby="nav-contact-tab">
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.assignments.list') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} assignments:list" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.access.rights.list') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} access:list" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.user.assignments') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} assignments:user &lt;userId&gt;" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.view.role') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} access:viewRole &lt;role&gt;" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.access.rights.management') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} access:addRole &lt;role&gt;" ?></code>
                    <br><code><?= "{$binPath} access:AddPermission &lt;permission&gt;" ?></code>
                    <br><code><?= "{$binPath} access:removeRole &lt;role&gt;" ?></code>
                    <br><code><?= "{$binPath} access:removePermission &lt;permission&gt;" ?></code>
                    <br><code><?= "{$binPath} access:addChildRole &lt;parentRole&gt; &lt;childRole&gt;" ?></code>
                    <br><code><?= "{$binPath} access:addChildPermission &lt;parentRole&gt; &lt;childPermission&gt;" ?></code>
                    <br><code><?= "{$binPath} access:removeChildRole &lt;parentRole&gt; &lt;childRole&gt;" ?></code>
                    <br><code><?= "{$binPath} access:removeChildPermission &lt;parentRole&gt; &lt;childPermission&gt;" ?></code>
                    <br><code><?= "{$binPath} access:addAllChildPermission &lt;parentRole&gt;" ?></code>
                    <br><code><?= "{$binPath} access:removeAll" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.assign.and.revoke.access.rights') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} assign:assignRole &lt;userId&gt; &lt;role&gt;" ?></code>
                    <br><code><?= "{$binPath} assign:assignPermission &lt;userId&gt; &lt;permission&gt;" ?></code>
                    <br><code><?= "{$binPath} assign:revokeRole &lt;userId&gt; &lt;permission&gt;" ?></code>
                    <br><code><?= "{$binPath} assign:revokePermission &lt;userId&gt; &lt;permission&gt;" ?></code>
                    <br><code><?= "{$binPath} assign:assignAllPermission &lt;userId&gt;" ?></code>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-user" role="tabpanel" aria-labelledby="nav-contact-tab">
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.create.and.delete.user') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} user:create &lt;login&gt; &lt;password&gt; [isAdmin = 0]" ?></code>
                    <br><code><?= "{$binPath} user:delete &lt;login&gt;" ?></code>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-db-schema" role="tabpanel" aria-labelledby="nav-contact-tab">
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.db.schema') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} cycle:schema" ?></code>
                    <br><code><?= "{$binPath} cycle:schema:php" ?></code>
                    <br><code><?= "{$binPath} cycle:schema:clear" ?></code>
                    <br><code><?= "{$binPath} cycle:schema:rebuild" ?></code>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-other" role="tabpanel" aria-labelledby="nav-contact-tab">
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.list.of.routes') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} router:list" ?></code>
                </div>
                <h4 class="card-title text-muted mt-2 mb-1">
                    <?= $translator->translate('view-site.translate') ?>
                </h4>
                <div>
                    <code><?= "{$binPath} translator:translate &lt;locale&gt;" ?></code>
                </div>
            </div>
        </div>
    </div>
</div>
