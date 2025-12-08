<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Récupère toutes les catégories d’un thème, triées alphabétiquement.
     */
    public function findByTheme(Theme $theme): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les catégories sans parent (racines).
     */
    public function findRootsByTheme(Theme $theme): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.theme = :theme')
            ->andWhere('c.parent IS NULL')
            ->setParameter('theme', $theme)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les enfants directs d’une catégorie.
     */
    public function findChildren(Category $parent): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent = :parent')
            ->setParameter('parent', $parent)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Renvoie un arbre de catégories pour un thème.
     * Structure :
     * [
     *     rootCategory => [
     *         'category' => Category,
     *         'children' => [
     *             childCat => [
     *                 'category' => Category,
     *                 'children' => [...]
     *             ]
     *         ]
     *     ]
     * ]
     */
    public function getCategoryTreeForTheme(Theme $theme): array
    {
        $roots = $this->findRootsByTheme($theme);

        $tree = [];

        foreach ($roots as $root) {
            $tree[] = $this->buildTree($root);
        }

        return $tree;
    }

    /**
     * Retourne toutes les catégories triées par thème puis par nom.
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.theme', 't')
            ->addSelect('t')
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Construit récursivement un arbre de catégories.
     */
    private function buildTree(Category $category): array
    {
        $children = $this->findChildren($category);

        $node = [
            'category' => $category,
            'children' => [],
        ];

        foreach ($children as $child) {
            $node['children'][] = $this->buildTree($child);
        }

        return $node;
    }
}
