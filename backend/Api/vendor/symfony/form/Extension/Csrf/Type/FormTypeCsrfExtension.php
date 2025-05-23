<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Csrf\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Csrf\EventListener\CsrfValidationListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Util\ServerParams;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormTypeCsrfExtension extends AbstractTypeExtension
{
    public function __construct(
        private CsrfTokenManagerInterface $defaultTokenManager,
        private bool $defaultEnabled = true,
        private string $defaultFieldName = '_token',
        private ?TranslatorInterface $translator = null,
        private ?string $translationDomain = null,
        private ?ServerParams $serverParams = null,
        private array $fieldAttr = [],
        private string|array|null $defaultTokenId = null,
    ) {
    }

    /**
     * Adds a CSRF field to the form when the CSRF protection is enabled.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['csrf_protection']) {
            return;
        }

        $csrfTokenId = $options['csrf_token_id']
            ?: $this->defaultTokenId[$builder->getType()->getInnerType()::class]
            ?? $builder->getName()
            ?: $builder->getType()->getInnerType()::class;
        $builder->setAttribute('csrf_token_id', $csrfTokenId);

        $builder
            ->addEventSubscriber(new CsrfValidationListener(
                $options['csrf_field_name'],
                $options['csrf_token_manager'],
                $csrfTokenId,
                $options['csrf_message'],
                $this->translator,
                $this->translationDomain,
                $this->serverParams
            ))
        ;
    }

    /**
     * Adds a CSRF field to the root form view.
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['csrf_protection'] && !$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getFormFactory();
            $tokenId = $form->getConfig()->getAttribute('csrf_token_id');
            $data = (string) $options['csrf_token_manager']->getToken($tokenId);

            $csrfForm = $factory->createNamed($options['csrf_field_name'], HiddenType::class, $data, [
                'block_prefix' => 'csrf_token',
                'mapped' => false,
                'attr' => $this->fieldAttr,
            ]);

            $view->children[$options['csrf_field_name']] = $csrfForm->createView($view);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        if (\is_string($defaultTokenId = $this->defaultTokenId) && $defaultTokenId) {
            $defaultTokenManager = $this->defaultTokenManager;
            $defaultTokenId = static fn (Options $options) => $options['csrf_token_manager'] === $defaultTokenManager ? $defaultTokenId : null;
        } else {
            $defaultTokenId = null;
        }

        $resolver->setDefaults([
            'csrf_protection' => $this->defaultEnabled,
            'csrf_field_name' => $this->defaultFieldName,
            'csrf_message' => 'The CSRF token is invalid. Please try to resubmit the form.',
            'csrf_token_manager' => $this->defaultTokenManager,
            'csrf_token_id' => $defaultTokenId,
        ]);

        $resolver->setAllowedTypes('csrf_protection', 'bool');
        $resolver->setAllowedTypes('csrf_field_name', 'string');
        $resolver->setAllowedTypes('csrf_message', 'string');
        $resolver->setAllowedTypes('csrf_token_manager', CsrfTokenManagerInterface::class);
        $resolver->setAllowedTypes('csrf_token_id', ['null', 'string']);
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
