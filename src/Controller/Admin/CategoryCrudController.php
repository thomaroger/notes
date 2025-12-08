<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Catégories')
            ->setEntityLabelInSingular('Catégorie')
            ->setSearchFields(['name', 'theme.name'])
            ->setDefaultSort([
                'theme.name' => 'ASC',
                'parent.name' => 'ASC',
                'name' => 'ASC',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();

        yield TextField::new('name', 'Nom');

        yield AssociationField::new('theme', 'Thème')
            ->setRequired(true)
            ->setSortable(true);

        yield AssociationField::new('parent')
            ->setQueryBuilder(function (QueryBuilder $qb) {
                return $qb
                    ->andWhere('entity.parent IS NULL')
                    ->orderBy('entity.name', 'ASC');
            })
            ->setRequired(false)
            ->setHelp('Maximum 2 niveaux : racine → enfant');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('theme')
            ->add('parent');
    }

    /**
     * Vérifie la profondeur hiérarchique avant enregistrement.
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->validateHierarchy($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->validateHierarchy($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function validateHierarchy(Category $category): void
    {
        if (! $category->getParent()) {
            return;
        }

        if (! $category->getParent()->getParent()) {
            return;
        }

        // Plus de 2 niveaux = interdit
        throw new \RuntimeException('Une catégorie ne peut pas dépasser 2 niveaux de profondeur.');
    }
}
