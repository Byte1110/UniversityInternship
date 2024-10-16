<?php

namespace App\Entity;

use App\Repository\UserGroupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserGroupRepository::class)
 */
class UserGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userGroups")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity=University::class, inversedBy="userGroups")
     */
    private $groups;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getGroups(): ?University
    {
        return $this->groups;
    }

    public function setGroups(?University $groups): self
    {
        $this->groups = $groups;

        return $this;
    }
}
