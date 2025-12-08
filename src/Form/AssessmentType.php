<?php

declare(strict_types=1);

// src/Form/AssessmentType.php

namespace App\Form;

use App\Entity\Assessment;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l’évaluation',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin('c.parent', 'p')
                        ->where('c.parent IS NOT NULL')
                        ->orderBy('p.name', 'ASC')
                        ->addOrderBy('c.name', 'ASC');
                },
            ])
            ->add('maxScore', IntegerType::class, [
                'label' => 'Note maximale',
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l’évaluation',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assessment::class,
        ]);
    }
}
