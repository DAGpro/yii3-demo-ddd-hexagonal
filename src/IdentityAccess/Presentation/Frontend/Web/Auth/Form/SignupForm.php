<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Frontend\Web\Auth\Form;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RulesProviderInterface;
use Yiisoft\Validator\ValidatorInterface;

/**
 * @psalm-import-type RawRules from ValidatorInterface
 */
final class SignupForm extends FormModel implements RulesProviderInterface
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public string $login = '' {
        get {
            return $this->login;
        }
    }

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public string $password = '' {
        get {
            return $this->password;
        }
    }
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public string $passwordVerify = '' {
        get {
            return $this->passwordVerify;
        }
    }

    public function __construct(
        private readonly UserQueryServiceInterface $userService,
        private readonly TranslatorInterface $translator,
    ) {}

    #[Override]
    public function getPropertyLabels(): array
    {
        return [
            'login' => $this->translator->translate('identityAccess.form.login'),
            'password' => $this->translator->translate('identityAccess.form.password'),
            'passwordVerify' => $this->translator->translate('identityAccess.form.password-verify'),
        ];
    }

    #[Override]
    public function getRules(): iterable
    {
        return [
            'login' => [new Required()],
            'password' => [new Required()],
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
                    $result->addError(
                        $this->translator->translate('validator.password.not.match'),
                        valuePath: ['passwordVerify'],
                    );
                }

                if ($result->getErrors() === [] && null !== $this->userService->findByLogin($this->login)) {
                    $result->addError($this->translator->translate('validator.user.exist'), valuePath: ['login']);
                }

                return $result;
            },
        ];
    }
}
