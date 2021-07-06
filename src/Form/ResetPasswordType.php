<?php

namespace App\Form;

use App\DTO\ResetPasswordDTO;
use App\Form\FormExtension\RepeatedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface<callable> $builder
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedPasswordType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ResetPasswordDTO::class,
            'empty_data' => function (FormInterface $form) {
                return new ResetPasswordDTO(
                    $form->get('password')->getData()
                );
            }
        ]);
    }
}