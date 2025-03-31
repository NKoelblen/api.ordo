<?php

namespace App\Resolver;

use ApiPlatform\GraphQl\Resolver\MutationResolverInterface;
use App\Entity\Space;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SpaceResolver implements MutationResolverInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function __invoke(?object $item, array $context): Space
    {
        $input = $context['args']['input'];

        if ($item === null) {
            $space = new Space();
        } else {
            if (!$item instanceof Space) {
                throw new \RuntimeException('Invalid Space entity');
            }
            $space = $item;
        }

        if (isset($input['name'])) {
            $space->setName($input['name']);
        }
        if (isset($input['status'])) {
            $space->setStatus($input['status']);
        }
        if (isset($input['professional'])) {
            $space->setProfessional($input['professional']);
        }
        if (isset($input['color'])) {
            $space->setColor($input['color']);
        }
        if (isset($input['icon'])) {
            $space->setIcon($input['icon']);
        }
        if (isset($input['parent'])) {
            error_log('Parent Space ID: ' . json_encode(explode('/', $input['parent'])));
            $parent = $this->entityManager->getRepository(Space::class)->find(explode('/', $input['parent'])[2]);
            if ($parent === null) {
                throw new \RuntimeException('Parent Space not found');
            }
            $space->setParent($parent);
        }

        if (array_key_exists('personalizedIconFile', $input)) {
            $file = $input['personalizedIconFile'];

            if ($file === null) {
                $space->setPersonalizedIconFile(null);
            } elseif (!$file instanceof UploadedFile) {
                throw new \RuntimeException('Invalid file uploaded');
            } else {
                $space->setPersonalizedIconFile($file);
            }
        }

        $this->entityManager->persist($space);
        $this->entityManager->flush();

        return $space;
    }
}