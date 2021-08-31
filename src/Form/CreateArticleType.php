<?php


namespace App\Form;


use App\DTO\CreateArticleDTO;
use App\Entity\Category;
use App\EventSubscriber\CreateArticleSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class, [
                "label" => "Titre de l'article",
                "required" => true
            ])
            ->add("content", TextareaType::class, [
                "label" => "Le contenu de l'article",
                "required" => true
            ])
            ->add("categories", EntityType::class, [
                "class" => Category::class,
                "by_reference" => false,
                "choice_label" => "name",
                "multiple" => true,
                "expanded" => true,
                "required" => true,
                "label" => "Catégories de l'article",
            ])
            ->add("picture", FileType::class, [
                "label" => "Image de l'article",
                "required" => true
            ])
            ->add("publish", SubmitType::class, [
                "label" => "Publier l'article",
                "attr" => [
                    "class" => "btn btn-success"
            ]])
            ->addEventSubscriber(new CreateArticleSubscriber())
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateArticleDTO::class,
            'empty_data' => function(FormInterface $form){
                return new CreateArticleDTO(
                    $form->get('title')->getData(),
                    $form->get('content')->getData(),
                    $form->get('categories')->getData(),
                    $form->get('picture')->getData()
                );
            },
            'user_role'=> []
        ]);
    }
}