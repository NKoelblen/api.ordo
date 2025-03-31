<?php

namespace App\Resolver;

use ApiPlatform\GraphQl\Resolver\MutationResolverInterface;
use App\Entity\Member;
use App\Entity\Space;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MemberResolver implements MutationResolverInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function __invoke(?object $item, array $context): Member
    {
        $input = $context['args']['input'];

        if ($item === null) {
            $member = new Member();
        } else {
            if (!$item instanceof Member) {
                throw new \RuntimeException('Invalid Member entity');
            }
            $member = $item;
        }

        if (isset($input['name'])) {
            $member->setName($input['name']);
        }
        if (isset($input['color'])) {
            $member->setColor($input['color']);
        }
        if (isset($input['icon'])) {
            $member->setIcon($input['icon']);
        }
        if (isset($input['space'])) {
            $space = $this->entityManager->getRepository(Space::class)->find(explode('/', $input['space'])[2]);
            if ($space === null) {
                throw new \RuntimeException('Space not found');
            }
            $member->setSpace($space);
        }

        if (array_key_exists('personalizedIconFile', $input)) {
            $file = $input['personalizedIconFile'];

            if ($file === null) {
                $member->setPersonalizedIconFile(null);
            } elseif (!$file instanceof UploadedFile) {
                throw new \RuntimeException('Invalid file uploaded');
            } else {
                $member->setPersonalizedIconFile($file);
            }
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return $member;
    }
}