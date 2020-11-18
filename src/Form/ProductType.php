<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormTypeInterface;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class)
            ->add('summary',TextType::class)
            ->add('price',MoneyType::class)
            ->add('description',TextType::class)
            ->add('created_at',DateTimePickerType::class)
            ->add('image_form', FileType::class, [
                'data_class' => null,
                'required' => false
            ])
            ->add('quantity',IntegerType::class)
            ->add('category',EntityType::class,
                ['class'=>'App\Entity\Category'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
