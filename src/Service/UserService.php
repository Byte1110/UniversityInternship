<?php

namespace App\Service;

use App\Dto\StudentDto;
use App\Dto\UserDto;
use App\Entity\UserGroup;
use App\Entity\User;
use App\Entity\Work;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use App\Repository\UserGroupRepository;
use App\Repository\WorkRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class UserService
{
    private $entityManager;
    private $userRepository;
    private $universityRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $UserRepository, UniversityRepository $universityRepository,
                                WorkRepository $workRepository, UserGroupRepository $userGroupRepository)
    {
        $this->userRepository = $UserRepository;
        $this->universityRepository = $universityRepository;
        $this->entityManager = $entityManager;
        $this->workRepository = $workRepository;
        $this->userGroupRepository = $userGroupRepository;
    }
    public function newWork(UserDto $dto)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $employee = $this->userRepository->find($dto->id);
            $works = $employee->getWorks();
            $department = $this->universityRepository->find($dto->parentId);
            $sumRate = 0;
            foreach ($works as $work) {
                $sumRate += $work->getRate();
            }
            if ($sumRate + $dto->rate > 1) {
                throw new Exception(json_encode(['success' => false, 'msg' => "the rate exceeds 1"]), 406);
            }

            $work = new Work();
            $work->setRate($dto->rate);
            $work->setName($employee->getName());
            $employee->addWork($work);
            $department->addWork($work);

            $this->entityManager->persist($employee);
            $this->entityManager->persist($work);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return ['success' => true, 'msg' => ' Saved new work'];
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function newEmployee(UserDto $dto)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $department = $this->universityRepository->find($dto->parentId);
            $elem = new User();
            $elem->setName($dto->name);
            $elem->setAlias($dto->alias);

            $work = new Work();
            $work->setRate($dto->rate);
            $work->setName($dto->name);
            $work->setDepartment($department);
            $elem->addWork($work);

            $employee = new Work();
            $employee->setRate($dto->rate);
            $employee->setName($dto->name);
            $department->addWork($employee);

            $this->entityManager->persist($employee);
            $this->entityManager->persist($elem);
            $this->entityManager->persist($work);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return ['success' => true, 'msg' => ' Saved new employee with id ' . $elem->getId()];
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function showEmployee(int $id)
    {
        $employee = $this->userRepository->find($id);

        if (!$employee instanceof User) {
            throw new Exception(json_encode(['success' => false, 'msg' => 'No employee found']), 404);
        }
        return ['success' => true, 'name' => $employee->getName(), 'alias' => $employee->getAlias()];
    }

    public function updateEmployee($dto, int $id)
    {
        $employee = $this->userRepository->find($id);

        if (!$employee instanceof User) {
            throw new Exception(json_encode(['success' => false, 'msg' => 'No employee found']), 404);
        }

        try{
            $this->entityManager->getConnection()->beginTransaction();
            $employee->setName($dto->name);
            $employee->setAlias($dto->alias);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->showEmployee($id);
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function removeEmployee(int $id)
    {
        $elem = $this->userRepository->find($id);

        if (!$elem instanceof User) {
            throw new Exception(json_encode(['success' => false, 'msg' => 'No employee found']), 404);
        }
        try{
            $this->entityManager->getConnection()->beginTransaction();
            foreach ($elem->getWorks() as $work){
                $elem->removeWork($work);
            }
            $this->entityManager->remove($elem);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return ['success' => true, 'msg' => 'deleted employee ' . $elem->getName()];
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function showEmployeeDetails(int $id)
    {
        $employee = $this->userRepository->find($id);
        if (!$employee instanceof User) {
            throw new Exception(json_encode(['success' => false, 'msg' => 'No employee found']), 404);
        }
        $works = $employee->getWorks();

        $result = ['id' => $employee->getId(), 'alias' => $employee->getAlias(), 'name' => $employee->getName(), 'children' => []];
        foreach ($works as $work) {
            $result['children'][] = ['departmentId' => $work->getDepartment()->getName(), 'rate' => $work->getRate()];
        }
        return $result;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function excelTable(int $id): callable
    {
        $employee = $this->userRepository->find($id);
        if (!$employee instanceof User) {
            throw new Exception(json_encode(['success' => false, 'msg' => 'No employee found']), 404);
        }
        $works = $employee->getWorks();
        $faculty = $this->universityRepository->find($this->universityRepository->find($works[0]->getDepartment())->getParentId());
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'name');
        $sheet->setCellValue('A2', $employee->getName());
        $sheet->setCellValue('B1', 'faculty');
        $sheet->setCellValue('B2', $faculty->getName());
        $sheet->setCellValue('C1', 'department');
        $sheet->setCellValue('D1', 'rate');

        for ($i = 0; $i < count($works); $i++) {
            $sheet->setCellValue('C' . ($i + 2), $works[$i]->getDepartmentId());
            $sheet->setCellValue('D' . ($i + 2), $works[$i]->getRate());
        }
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return function () use ($writer) {
            $writer->save('php://output');
        };
    }
    public function newStudent(StudentDto $dto)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $group = $this->universityRepository->find($dto->groupId);
            if (!empty($group)){
                $student = new User();
                $student->setName($dto->name);
                $student->setAlias($dto->alias);

                $Group = new UserGroup();
                $Group->setGroups($group);
                $student->addUserGroup($Group);

                $user = new UserGroup();
                $user->setUserId($student);
                $group->addUserGroup($user);

                $this->entityManager->persist($Group);
                $this->entityManager->persist($user);
                $this->entityManager->persist($student);
                $this->entityManager->flush();
                $this->entityManager->commit();

                return (['success' => true, 'msg' => ' Saved new student with id '.$student->getId()]);
            } else {
                return (['success' => false, 'msg' => 'group not found']);
            }
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function newStudentGroup(StudentDto $dto)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $student = $this->userRepository->find($dto->id);
            $group = $this->universityRepository->find($dto->groupId);
            if (!empty($group)){
                $Group = new UserGroup();
                $Group->setGroups($group);
                $student->addUserGroup($Group);

                $this->entityManager->persist($Group);
                $this->entityManager->persist($student);
                $this->entityManager->flush();
                $this->entityManager->commit();

                return (['success' => true, 'msg' => ' Saved new group with id '.$group->getId()]);
            } else {
                return (['success' => false, 'msg' => 'group not found']);
            }
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function showStudent(int $id)
    {
        $elem = $this->userRepository->find($id);
        if (!$elem instanceof User) {
            throw new Exception(json_encode(['success' => false, 'msg' => 'No employee found']), 404);
        }

        return ['success' => true, 'name' => $elem->getName(),  'alias' => $elem->getAlias()];
    }
    public function updateStudent(int $id, $dto)
    {
        try{
            $this->entityManager->getConnection()->beginTransaction();
            $student = $this->userRepository->find($id);

            if (!$student instanceof User) {
                throw new Exception(json_encode(['success'=> false, 'msg'=>'No student found']), 404);
            }
            $student->setName($dto->name);
            $student->setAlias($dto->alias);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->showStudent($id);
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function removeStudent(int $id)
    {
        $student = $this->userRepository->find($id);

        if (!$student instanceof User) {
            throw new Exception(json_encode(['success'=> false, 'msg'=>'No student found']), 404);
        }
        try{
            $this->entityManager->getConnection()->beginTransaction();
            foreach ($student->getUserGroups() as $group){
                $group->getGroups()->removeUserGroup($group);
                $student->removeUserGroup($group);
            }
            $this->entityManager->remove($student);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return ['success' => true, 'msg' => 'deleted student ' . $student->getName()];
        } catch (Exception $e){
            $this->entityManager->rollback();
            throw $e;
        }
    }
    public function showGroups($id){
        $student = $this->userRepository->find($id);
        $groups = [];
        foreach ($student->getUserGroups() as $group){
            $groups[(string)$group->getGroups()->getId()]=$group->getGroups()->getName();
        }
        return ['success' => true, 'msg' => $groups];
    }
}