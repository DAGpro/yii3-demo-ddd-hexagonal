<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\IdentityAccess\Auth\Form;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use Yiisoft\Form\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Required;

final class LoginForm extends FormModel
{
    private string $login = '';
    private string $password = '';
    private bool $rememberMe = false;
    private TranslatorInterface $translator;
    private UserQueryServiceInterface $userService;

    public function __construct(
        UserQueryServiceInterface $userService,
        TranslatorInterface $translator
    ) {
        parent::__construct();

        $this->translator = $translator;
        $this->userService = $userService;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getAttributeLabels(): array
    {
        return [
            'login' => $this->translator->translate('identityAccess.form.login'),
            'password' => $this->translator->translate('identityAccess.form.password'),
            'rememberMe' => $this->translator->translate('identityAccess.form.remember'),
        ];
    }

    public function getFormName(): string
    {
        return 'Login';
    }

    public function getRules(): array
    {
        return [
            'login' => [Required::rule()],
            'password' => $this->passwordRules(),
        ];
    }

    private function passwordRules(): array
    {
        return [
            Required::rule(),
            function (): Result {
                $result = new Result();

                $user = $this->userService->findByLogin($this->login);

                if ($user === null || !$user->validatePassword($this->password)) {
                    $result->addError($this->translator->translate('validator.invalid.login.password'));
                }

                return $result;
            },
        ];
    }
}
