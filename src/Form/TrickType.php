<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'data_class' => null,
                'attr' => [
                    'placeholder' => 'Ajouter ou modifier l\'image principale du trick',
                ],
            ])
            ->add('createdAt')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title'

            ])
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false, //pour ne pa lier a la
                'required' => false
            ])
            ->add('videos', UrlType::class, [
                'required' => false,
                'mapped' => false, //pour ne pa lier a la

                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}