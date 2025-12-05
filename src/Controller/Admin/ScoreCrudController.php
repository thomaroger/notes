<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Score;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ScoreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Score::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('child', 'Élève'))
            ->add(EntityFilter::new('assessment', 'Évaluation'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('child', 'Élève')
                ->setRequired(true),
            AssociationField::new('assessment', 'Évaluation')
                ->setRequired(true),
            NumberField::new('score', 'Note')
                ->setHelp('Entre 0 et la notation maximale de l\'évaluation')
                ->setRequired(true)
                ->setNumDecimals(2),
            BooleanField::new('absent', 'Absent')
                ->setRequired(true)
                ->setHelp('Si l\'élève est absent, la note est nulle'),
        ];
    }
}
