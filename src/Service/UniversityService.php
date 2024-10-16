<?php

namespace App\Service;

use App\Dto\UniversityDto;
use App\Entity\University;
use App\Repository\UniversityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class UniversityService
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, UniversityRepository $repository){
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }
    public function newElem(UniversityDto $dto)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $elem = new University();
            $elem->setName($dto->name);
            $elem->setParentId($dto->parentId);
            $elem->setAlias($dto->alias);

            $this->entityManager->persist($elem);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return (['success' => true, 'msg' => 'Saved new elem with id '.$elem->getId()]);
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function showElem(int $id)
    {
        $elem = $this->repository->find($id);

        if (!$elem instanceof University) {
            throw new Exception(json_encode(['success'=> false, 'msg' => 'No elem of university found']), 404);
        }
        return ['success' => true, 'name' => $elem->getName(), 'alias' => $elem->getAlias()];
    }
    public function update($dto, int $id)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $elem = $this->repository->find($id);

            if (!$elem instanceof University) {
                throw new Exception(json_encode(['success'=> false, 'msg' => 'No elem of university found']), 404);
            }

            $elem->setName($dto->name);
            $elem->setParentId($dto->parentId);
            $elem->setAlias($dto->alias);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->showElem($id);
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function remove(int $id)
    {
        $elem = $this->repository->find($id);

        if (!$elem instanceof University) {
            throw new Exception(json_encode(['success'=> false, 'msg' => 'No elem of university found']), 404);
        }

        $this->entityManager->remove($elem);
        $this->entityManager->flush();

        return ['success' => true, 'msg' => 'deleted elem ' . $elem->getName()];
    }
    public function getObjects($objects){
        $result = [];
        foreach ($objects as $object) {
            $result = array_merge($result, $object->getUserGroups() );
        }
        return $result;
    }
    public function userShow(int $facultyId, $alias)
    {
        $chairs = $this->repository->findBy(['parentId' => $facultyId]);
        $chairsId = array_map(function($object){
            $item['parentId'] = $object->getId();
            return $item;
        }, $chairs);
        $dtos = [];
        if ($alias == 'student') {
            $groups = [];
            foreach ($chairsId as $chairId) {
                $group = array_map(function ($object) {
                    $item[(string)$object->getId()] = $object->getId();
                    return $item;
                }, $this->repository->findBy($chairId));
                $groups = array_merge($group, $group);
            }
            $students = $this->getObjects($groups);

            foreach ($students as $student) {
                $dto = $student->getUserId()->getName();
                $dtos[] = $dto;
            }
        } else{
            $employees = [];
            foreach ($chairsId as $chair){
                $employees = array_merge($employees, $chair->getWorks());
            }
            foreach ($employees as $employee){
                $dto = $employee->getName();
                $dtos[] = $dto;
            }
        }
        return $dtos;
    }
    public function groupShow(string $name){
        $group = $this->repository->findOneBy(['name' => $name]);
        $students = $group->getUserGroups();
        $department = $this->repository->findOneBy(['id' => $group->getParentId()]);
        $faculty = $this->repository->findOneBy(['id' => $department->getParentId()]);
        return ['success' => true, 'msg' => ['numberOfStudents' => count($students), 'department' => $department->getName(), 'faculty' => $faculty->getName()]];
    }
    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function groupShowExcel(string $name){
        $group = $this->repository->findOneBy(['name' => $name]);
        if (!$group instanceof University){
            throw new Exception(json_encode(['success'=> false, 'msg' => 'No group found']), 404);
        }
        $students = $group->getUserGroups();
        $department = $this->repository->findOneBy(['id' => $group->getParentId()]);
        $faculty = $this->repository->findOneBy(['id' => $department->getParentId()]);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'number of students');
        $sheet->setCellValue('B1', count($students));
        $sheet->setCellValue('A2', 'department');
        $sheet->setCellValue('B2', $department->getName());
        $sheet->setCellValue('A3', 'faculty');
        $sheet->setCellValue('B3', $faculty->getName());
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return function () use ($writer) { $writer->save('php://output'); };

    }
}