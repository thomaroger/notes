<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Stat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stat::class;
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

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('entityType', 'Type d\'entité')
                ->setRequired(true),
            IntegerField::new('entityId', 'Id de l\'entité')
                ->setRequired(true),
            DatetimeField::new('computedAt', 'Calculé le')
                ->setRequired(true),
            TextAreaField::new('dataJsonField', 'Donnée'),
        ];
    }
}
