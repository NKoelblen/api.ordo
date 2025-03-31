<?php

namespace App\Serializer;

use App\Entity\Member;
use App\Entity\Space;
use Vich\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UploadedFileNormalizer implements NormalizerInterface
{

    private const ALREADY_CALLED = 'UPLOADED_FILE_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.graphql.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        $object->setPersonalizedIconUrl($this->storage->resolveUri($object, 'personalizedIconFile'));

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Space | $data instanceof Member;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Space::class => true,
            Member::class => true
        ];
    }
}