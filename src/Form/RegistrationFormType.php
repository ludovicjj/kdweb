<?php

namespace App\Form;

use App\DTO\RegistrationDTO;
use App\Form\FormExtension\HoneyPotType;
use App\Form\FormExtension\RepeatedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface<callable> $builder
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                "label" => 'Email',
                "required" => true,
                "attr" => [
                    "autofocus" => true
                ]
            ])
            ->add('password', RepeatedPasswordType::class)
            ->add('agreeTerms', CheckboxType::class, [
                "label" => "J'accepte les conditions d'utilisation de se site",
                'mapped' => false,
                'constraints' => [
                    new isTrue([
                        'message' => "Vous devez accepter les conditions d'utilisation de se site pour vous inscrire.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationDTO::class,
            'empty_data' => function(FormInterface $form){
                return new RegistrationDTO(
                    $form->get('email')->getData(),
                    $form->get('password')->getData()
                );
            }
        ]);
    }

    public function getParent()
    {
        return HoneyPotType::class;
    }
}
