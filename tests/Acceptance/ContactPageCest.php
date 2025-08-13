<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\AcceptanceTester;

final class ContactPageCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->amOnPage('/contact');
    }

    public function contactPageWorks(AcceptanceTester $I): void
    {
        $I->wantTo('ensure that contact page works');
        $I->amOnPage('/contact');
        $I->seeResponseCodeIs(200);

        // Check for page title (could be h1, h2, or other heading)
        $I->see('Contact', 'h1');

        // Check for form submit button with flexible selectors
        $submitSelector = 'button[type="submit"]';
        $I->seeElement($submitSelector);
        $I->comment("Found submit button with selector: $submitSelector");
    }

    public function contactFormCanBeSubmitted(AcceptanceTester $I): void
    {
        $I->amGoingTo('submit contact form with correct data');
        $I->amOnPage('/contact');

        // Define field selectors with fallbacks
        $fieldSelectors = [
            'name' => 'input[name="ContactForm[name]"]',
            'email' => 'input[name="ContactForm[email]"]',
            'subject' => 'input[name="ContactForm[subject]"]',
            'body' => 'textarea[name="ContactForm[body]"]',
        ];

        $I->fillField($fieldSelectors['name'], 'Tester');
        $I->comment("Filled field with selector: " . $fieldSelectors['name']);
        $I->fillField($fieldSelectors['email'], 'tester@example.com');
        $I->comment("Filled field with selector: " . $fieldSelectors['email']);
        $I->fillField($fieldSelectors['subject'], 'Test Subject');
        $I->comment("Filled field with selector: " . $fieldSelectors['subject']);
        $I->fillField($fieldSelectors['body'], 'This is a test message.');
        $I->comment("Filled field with selector: " . $fieldSelectors['body']);

        $submitSelector = 'button[type="submit"]';
        $I->click($submitSelector);
        $I->comment("Form submitted with selector: $submitSelector");

        $successMessage = 'Thank you for contacting us, we\'ll get in touch with you as soon as possible.';

        $I->see($successMessage);
        $I->wantTo('See success message');
        $I->comment("Found success message: $successMessage");
    }
}
