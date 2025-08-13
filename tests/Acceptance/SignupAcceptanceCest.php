<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\AcceptanceTester;

final class SignupAcceptanceCest
{
    public function testSignupPage(AcceptanceTester $I): void
    {
        $I->amGoingTo('go to the register page.');
        $I->amOnPage('/signup');

        $I->expectTo('see register page.');
        $I->see('Signup');
    }

    public function testRegisterSuccess(AcceptanceTester $I): void
    {
        $I->amGoingTo('go to the register page.');
        $I->amOnPage('/signup');

        $I->fillField('#signupform-login', 'admin');
        $I->fillField('#signupform-password', '123456');
        $I->fillField('#signupform-passwordverify', '123456');

        $I->click('Submit', '#signupForm');

        $I->expectTo('see register success message.');
        $I->see('The user is registered, now you can log in!');
    }

    public function testRegisterEmptyData(AcceptanceTester $I): void
    {
        $I->amGoingTo('go to the register page.');
        $I->amOnPage('/signup');

        $I->fillField('#signupform-login', '');
        $I->fillField('#signupform-password', '');
        $I->fillField('#signupform-passwordverify', '');

        $I->click('Submit', '#signupForm');

        $I->expectTo('see registration register validation.');
        $I->see('Login cannot be blank.');
        $I->see('Password cannot be blank.');
        $I->see('PasswordVerify cannot be blank.');
        $I->see('Submit', 'button[type="submit"]');
    }

    public function testRegisterUsernameExistData(AcceptanceTester $I): void
    {
        $I->amGoingTo('go to the register page.');
        $I->amOnPage('/signup');

        $I->fillField('#signupform-login', 'admin');
        $I->fillField('#signupform-password', '123456');
        $I->fillField('#signupform-passwordverify', '123456');

        $I->click('Submit', '#signupForm');

        $I->expectTo('see registration register validation.');
        $I->see('User with this login already exists');

        $I->see('Submit', 'button[type="submit"]');
    }

    public function testRegisterWrongPassword(AcceptanceTester $I): void
    {
        $I->amGoingTo('go to the register page.');
        $I->amOnPage('/signup');

        $I->fillField('#signupform-login', 'admin1');
        $I->fillField('#signupform-password', '123456');
        $I->fillField('#signupform-passwordverify', '12345');

        $I->click('Submit', '#signupForm');

        $I->expectTo('see registration register validation.');
        $I->see('Passwords do not match');
    }
}
