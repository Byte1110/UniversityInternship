<?php

namespace App\Dto;

class StudentDto
{
    /**
     * @var int
     * id студента
     */
    public $id;

    /**
     * @var string
     * имя студента
     */
    public $name;

    /**
     * @var int
     * id группы студента
     */
    public $groupId;

    /**
     * @var string
     * алиас студента
     */
    public $alias;

}