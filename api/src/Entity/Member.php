<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use App\Resolver\MemberResolver;
use App\Entity\Trait\GraphicTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    types: ['https://schema.org/MediaObject'],
    graphQlOperations: [
        new Query(name: "item_query"),
        new QueryCollection(name: "collection_query"),
        new Mutation(
            name: "create",
            resolver: MemberResolver::class,
            deserialize: false,
            args: [
                'name' => ['type' => 'String!'],
                'color' => ['type' => 'String'],
                'icon' => ['type' => 'String'],
                'personalizedIconFile' => ['type' => 'Upload'],
                'space' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: "update",
            resolver: MemberResolver::class,
            deserialize: false,
            args: [
                'id' => ['type' => 'ID!'],
                'name' => ['type' => 'String'],
                'color' => ['type' => 'String'],
                'icon' => ['type' => 'String'],
                'personalizedIconFile' => ['type' => 'Upload'],
                'space' => ['type' => 'String'],
            ]
        ),
        new DeleteMutation(name: "delete")
    ],
    order: ['name' => 'ASC']
)]
#[Vich\Uploadable]
class Member
{
    use TimestampableTrait;
    use GraphicTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $name = null;

    #[Vich\UploadableField(mapping: "member_icon", fileNameProperty: "personalizedIcon")]
    #[Groups(['write'])]
    public ?File $personalizedIconFile = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Groups(['read', 'write'])]
    private ?Space $space = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): static
    {
        $this->space = $space;

        return $this;
    }
}
