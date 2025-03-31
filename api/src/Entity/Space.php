<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use App\Entity\Trait\GraphicTrait;
use App\Resolver\SpaceResolver;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\SpaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: SpaceRepository::class)]
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
            resolver: SpaceResolver::class,
            deserialize: false,
            args: [
                'name' => ['type' => 'String!'],
                'status' => ['type' => 'String!'],
                'professional' => ['type' => 'Boolean'],
                'parent' => ['type' => 'String'],
                'color' => ['type' => 'String'],
                'icon' => ['type' => 'String'],
                'personalizedIconFile' => ['type' => 'Upload'],
                'members' => ['type' => '[String]'],
            ],
        ),
        new Mutation(
            name: "update",
            resolver: SpaceResolver::class,
            deserialize: false,
            args: [
                'id' => ['type' => 'ID!'],
                'name' => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'professional' => ['type' => 'Boolean'],
                'parent' => ['type' => 'String'],
                'color' => ['type' => 'String'],
                'icon' => ['type' => 'String'],
                'personalizedIconFile' => ['type' => 'Upload'],
                'members' => ['type' => '[String]'],
            ]
        ),
        new DeleteMutation(name: "delete")
    ],
    order: ['name' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact'])]
#[Vich\Uploadable]
class Space
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

    #[ORM\Column(length: 255, options: ['default' => 'open'])]
    #[Groups(['read', 'write'])]
    private string $status = 'open';

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['read', 'write'])]
    private ?bool $professional = false;

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

    #[Vich\UploadableField(mapping: "space_icon", fileNameProperty: "personalizedIcon")]
    #[Groups(['write'])]
    public ?File $personalizedIconFile = null;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'space')]
    #[Groups(['read', 'write'])]
    private Collection $members;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->members = new ArrayCollection();
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->setChildrenStatus();

        return $this;
    }

    public function isProfessional(): bool
    {
        return $this->professional;
    }

    public function setProfessional(bool $professional): static
    {
        $this->professional = $professional;
        $this->setChildrenProfessional();

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        if ($parent !== null) {
            $this->professional = $parent->isProfessional();
        }

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

    public function setChildrenProfessional(): void
    {
        foreach ($this->children as $child) {
            $child->setProfessional($this->professional);
            $child->setChildrenProfessional();
        }
    }

    public function setChildrenStatus(): void
    {
        foreach ($this->children as $child) {
            $child->setStatus($this->status);
            $child->setChildrenStatus();
        }
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setSpace($this);
        }

        return $this;
    }

    public function removeMember(Member $member): static
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getSpace() === $this) {
                $member->setSpace(null);
            }
        }

        return $this;
    }
}
