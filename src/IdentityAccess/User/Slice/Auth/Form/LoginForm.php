<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\Auth\Form;

use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RulesProviderInterface;

final class LoginForm extends FormModel implements RulesProviderInterface
{
    private string $login = '';
    private string $password = '';
    private bool $rememberMe = false;

    public function __construct(
        private readonly UserQueryServiceInterface $userService,
        private readonly TranslatorInterface $translator,
    ) {}

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    #[Override]
    public function getPropertyLabels(): array
    {
        return [
            'login' => $this->translator->translate('identityAccess.form.login'),
            'password' => $this->translator->translate('identityAccess.form.password'),
            'rememberMe' => $this->translator->translate('identityAccess.form.remember'),
        ];
    }

    #[Override]
    public function getFormName(): string
    {
        return 'Login';
    }

    #[Override]
    public function getRules(): iterable
    {
        return [
            'login' => [new Required()],
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * @return iterable<int, RuleInterface|callable>
     */
    private function passwordRules(): iterable
    {
        return [
            new Required(),
            function (): Result {
                $result = new Result();

                $user = $this->userService->findByLogin($this->login);

                if ($user === null || !$user->validatePassword($this->password)) {
                    $result->addError(
                        $this->translator->translate('validator.invalid.login.password'),
                        valuePath: ['login'],
                    );
                }

                return $result;
            },
        ];
    }
}
