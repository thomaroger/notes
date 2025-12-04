<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SchoolClass;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class SchoolClassCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SchoolClass::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('level', 'Niveau')
                ->setChoices([
                    'CP' => 'CP',
                    'CE1' => 'CE1',
                    'CE2' => 'CE2',
                    'CM1' => 'CM1',
                    'CM2' => 'CM2',
                ]));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('year', 'Année')
                ->setHelp('Exemple: 2025-2026'),
            ChoiceField::new('level', 'Niveau')
                ->setChoices([
                    'CP' => 'CP',
                    'CE1' => 'CE1',
                    'CE2' => 'CE2',
                    'CM1' => 'CM1',
                    'CM2' => 'CM2',
                ]),
        ];
    }
}
