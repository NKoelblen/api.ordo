<?php

namespace App\Resolver;

use ApiPlatform\GraphQl\Resolver\MutationResolverInterface;
use App\Entity\Category;
use App\Entity\Space;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CategoryResolver implements MutationResolverInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function __invoke(?object $item, array $context): Category
    {
        $input = $context['args']['input'];

        if ($item === null) {
            $category = new Category();
        } else {
            if (!$item instanceof Category) {
                throw new \RuntimeException('Invalid Category entity');
            }
            $category = $item;
        }

        if (isset($input['name'])) {
            $category->setName($input['name']);
        }

        if (isset($input['color'])) {
            $category->setColor($input['color']);
        }
        if (isset($input['icon'])) {
            $category->setIcon($input['icon']);
        }
        if (array_key_exists('personalizedIconFile', $input)) {
            $file = $input['personalizedIconFile'];

            if ($file === null) {
                $category->setPersonalizedIconFile(null);
            } elseif (!$file instanceof UploadedFile) {
                throw new \RuntimeException('Invalid file uploaded');
            } else {
                $category->setPersonalizedIconFile($file);
            }
        }

        if (isset($input['space'])) {
            $space = $this->entityManager->getRepository(Space::class)->find(explode('/', $input['space'])[2]);
            if ($space === null) {
                throw new \RuntimeException('Space not found');
            }
            $category->setSpace($space);
        }

        if (isset($input['parent'])) {
            $parent = $this->entityManager->getRepository(Category::class)->find(explode('/', $input['parent'])[2]);
            if ($parent === null) {
                throw new \RuntimeException('Parent Category not found');
            }
            $category->setParent($parent);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }
}