<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use App\Entity\Trait\GraphicTrait;
use App\Resolver\CategoryResolver;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write'], 'disable_type_enforcement' => true],
    types: ['https://schema.org/MediaObject'],
    graphQlOperations: [
        new Query(name: "item_query"),
        new QueryCollection(name: "collection_query"),
        new Mutation(
            name: "create",
            resolver: CategoryResolver::class,
            deserialize: false,
            args: [
                'name' => ['type' => 'String!'],
                'parent' => ['type' => 'String'],
                'color' => ['type' => 'String'],
                'icon' => ['type' => 'String'],
                'personalizedIconFile' => ['type' => 'Upload'],
                'space' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: "update",
            resolver: CategoryResolver::class,
            deserialize: false,
            args: [
                'id' => ['type' => 'ID!'],
                'name' => ['type' => 'String'],
                'parent' => ['type' => 'String'],
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
class Category
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

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Groups(['read', 'write'])]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Groups(['read'])]
    private Collection $children;

    #[Vich\UploadableField(mapping: "category_icon", fileNameProperty: "personalizedIcon")]
    #[Groups(['write'])]
    public ?File $personalizedIconFile = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Groups(['read', 'write'])]
    private ?Space $space = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

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
