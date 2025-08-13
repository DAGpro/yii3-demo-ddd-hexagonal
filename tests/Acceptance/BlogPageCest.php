<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\AcceptanceTester;
use Exception;

final class BlogPageCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->wantTo('blog page works.');
        $I->amOnPage('/blog');
    }

    public function testBlogPage(AcceptanceTester $I): void
    {
        $I->amGoingTo('check that blog page loads correctly');
        $I->amOnPage('/blog');
        $I->seeResponseCodeIs(200);
        $I->see('Blog', 'h1');

        // Check if there are blog posts or not
        try {
            // If there are posts, check for post elements
            $I->seeElement('.post-preview');
            $I->seeElement('.pagination');
        } catch (Exception $e) {
            // If no posts found, check for 'no records' message
            $I->see('No records', '.empty');
        }
    }
}
