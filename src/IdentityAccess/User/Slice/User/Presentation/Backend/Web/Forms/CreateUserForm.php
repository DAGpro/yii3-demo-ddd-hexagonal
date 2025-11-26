<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User\Presentation\Backend\Web\Forms;

use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RulesProviderInterface;

final class CreateUserForm extends FormModel implements RulesProviderInterface
{
    private string $login = '';
    private string $password = '';
    private string $passwordVerify = '';

    public function __construct(
        private readonly UserQueryServiceInterface $userService,
        private readonly TranslatorInterface $translator,
    ) {}

    public function getAttributeLabels(): array
    {
        return [
            'email' => $this->translator->translate('identityAccess.form.login'),
            'password' => $this->translator->translate('identityAccess.form.password'),
            'passwordVerify' => $this->translator->translate('identityAccess.form.password-verify'),
        ];
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    #[Override]
    public function getRules(): iterable
    {
        return [
            'login' => [
                new Required(),
                new Length(min: 3),
            ],
            'password' => [
                new Required(),
                new Length(min: 3),
            ],
            'passwordVerify' => $this->passwordVerifyRules(),
        ];
    }

    /**
     * @return iterable<int, RuleInterface|callable>
     */
    private function passwordVerifyRules(): iterable
    {
        return [
            new Required(),
            function (): Result {
                $result = new Result();
                if ($this->password !== $this->passwordVerify) {
                    $result->addError($this->translator->translate('validator.password.not.match'));
                }

                if ($result->getErrors() === [] && null !== $this->userService->findByLogin($this->login)) {
                    $result->addError($this->translator->translate('validator.user.exist'));
                }

                return $result;
            },
        ];
    }
}
