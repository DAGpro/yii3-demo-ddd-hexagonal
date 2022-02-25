<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\IdentityAccess\Auth\Form;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use Yiisoft\Form\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Required;

final class SignupForm extends FormModel
{
    private string $login = '';
    private string $password = '';
    private string $passwordVerify = '';
    private UserQueryServiceInterface $userService;
    private TranslatorInterface $translator;

    public function __construct(UserQueryServiceInterface $userService, TranslatorInterface $translator)
    {
        parent::__construct();

        $this->userService = $userService;
        $this->translator = $translator;
    }

    public function getAttributeLabels(): array
    {
        return [
            'email' => $this->translator->translate('identityAccess.form.login'),
            'password' => $this->translator->translate('identityAccess.form.password'),
            'passwordVerify' => $this->translator->translate('identityAccess.form.password-verify'),
        ];
    }

    public function getFormName(): string
    {
        return 'Signup';
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRules(): array
    {
        return [
            'login' => [Required::rule()],
            'password' => [Required::rule()],
            'passwordVerify' => $this->passwordVerifyRules(),
        ];
    }

    private function passwordVerifyRules(): array
    {
        return [
            Required::rule(),

            function (): Result {
                $result = new Result();
                if ($this->password !== $this->passwordVerify) {
                    $this->getFormErrors()->addError('password', '');
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
