<?php


namespace App\Form;

use App\DTO\EditArticleDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove("picture")
            ->add("picture", FileType::class, [
                "label" => "Image de l'article",
                'required' => false
            ]);
    }

    public function getParent()
    {
        return CreateArticleType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EditArticleDTO::class,
            'empty_data' => function(FormInterface $form){
                return new EditArticleDTO(
                    $form->get('title')->getData(),
                    $form->get('content')->getData(),
                    $form->get('categories')->getData(),
                    $form->get('picture')->getData()
                );
            }
        ]);
    }
}