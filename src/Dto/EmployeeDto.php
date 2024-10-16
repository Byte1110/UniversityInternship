<?php

namespace App\Dto;

class EmployeeDto
{
    /**
     * id записи сотрудника
     * @var int
     */
    public $id;

    /**
     * ставка сотрудника
     * @var float
     */
    public $rate;

    /**
     * имя сотрудника
     * @var string
     */
    public  $name;

    /**
     * @var int
     * id родителя
     */
    public  $parentId;

    /**
     * @var string
     * кафедра сотрудника
     */
    public  $chair;

    /**
     * @var string
     * алиас сотрудника
     */
    public  $alias;
}
