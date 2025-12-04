<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Assessment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class AssessmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assessment::class;
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
            ->add(EntityFilter::new('schoolClass', 'Classe'))
            ->add(EntityFilter::new('theme', 'Thème'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            AssociationField::new('theme', 'Thème')
                ->setRequired(true),
            AssociationField::new('schoolClass', 'Classe')
                ->setRequired(true),
            IntegerField::new('maxScore', 'Notation maximale')
                ->setHelp('Entre 1 et 20')
                ->setRequired(true),
        ];
    }
}
