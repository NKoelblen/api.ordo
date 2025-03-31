<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\File\File;


trait GraphicTrait
{
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read', 'write'])]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read', 'write'])]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[ApiProperty(writable: false)]
    public ?string $personalizedIcon = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'], writable: false)]
    #[Groups(['read'])]
    public ?string $personalizedIconUrl = null;

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
    public function getPersonalizedIcon(): ?string
    {
        return $this->personalizedIcon;
    }

    public function setPersonalizedIcon(?string $personalizedIcon): static
    {
        $this->personalizedIcon = $personalizedIcon;

        return $this;
    }

    public function getPersonalizedIconFile(): ?File
    {
        return $this->personalizedIconFile;
    }

    public function setPersonalizedIconFile(?File $file = null): void
    {
        if ($file === null) {
            $this->setPersonalizedIcon(null);
        }
        $this->personalizedIconFile = $file;
        $this->setUpdatedAtValue();
    }

    public function setPersonalizedIconUrl(?string $url): void
    {
        $this->personalizedIconUrl = $url;
    }

    public function getPersonalizedIconUrl(): ?string
    {
        return $this->personalizedIconUrl;
    }
}
