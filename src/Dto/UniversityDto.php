<?php

namespace App\Dto;

class UniversityDto
{
    /**
     * @var int
     * id элемента
     */
    public $id;

    /**
     * @var string
     * название элемента
     */
    public $name;

    /**
     * @var int
     * id родителя
     */
    public $parentId;

    /**
     * @var string
     * алиас элемента университета
     */
    public $alias;
}