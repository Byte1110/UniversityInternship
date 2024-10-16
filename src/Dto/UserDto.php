<?php

namespace App\Dto;

class UserDto
{
    /**
     * id
     * @var int
     */
    public $id;

    /**
     * ставка сотрудника
     * @var float|null
     */
    public $rate;

    /**
     * имя сотрудника
     * @var string
     */
    public  $name;

    /**
     * id родителя
     * @var int
     */
    public  $parentId;

    /**
     * кафедра сотрудника
     * @var string|null
     */
    public  $chair;

    /**
     * алиас
     * @var int
     */
    public  $alias;
}
