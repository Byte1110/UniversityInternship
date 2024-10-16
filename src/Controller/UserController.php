<?php

namespace App\Controller;

use App\Dto\StudentDto;
use App\Dto\UserDto;
use App\Service\UserService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class  UserController extends AbstractController
{
    private $service;

    public function __construct(UserService  $service)
    {
        $this->service = $service;
    }
    /**
     * @Route("/employees/newWork", name="new_work", methods="POST")
     */
    public function newWork(Request $request): JsonResponse
    {
        $dto = new UserDto();
        $dto->id = $request->get('id');
        $dto->parentId = $request->get('departmentId');
        $dto->rate = $request->get('rate');
        return $this->json($this->service->newWork($dto));
    }
    /**
     * @Route("/employees/newEmployee", name="new_employee", methods="POST")
     */
    public function newEmployee(Request $request): JsonResponse
    {
        $dto = new UserDto();
        $dto->name = $request->get('name');
        $dto->parentId = $request->get('departmentId');
        $dto->alias = $request->get('alias');
        $dto->rate = $request->get('rate');
        return $this->json($this->service->newEmployee($dto));
    }
    /**
     * @Route("/employees/show/{id}", name="emloyee_show", methods="GET")
     */
    public function showEmployee(int $id): JsonResponse
    {
        return $this->json($this->service->showEmployee($id));
    }
    /**
     * @Route("/employees/edit", methods="PUT")
     */
    public function updateEmployee(Request $request): JsonResponse
    {
        $dto = new UserDto();
        $dto->name = $request->get('name');
        $dto->alias = $request->get('alias');
        if (is_numeric($request->get('id'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->updateEmployee($dto, $request->get('id')));
    }
    /**
     * @Route("/employees/remove/{id}", methods="DELETE")
     */
    public function removeEmployee(int $id): JsonResponse
    {
        if (is_numeric($id)){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->removeEmployee($id));
    }
    /**
     * @Route("/employees/show/info/{id}", name="show_info", methods="GET")
     */
    public function showInfo(int $id): JsonResponse
    {
        if (is_numeric($id)){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->showEmployeeDetails($id));
    }
    /**
     * @Route("/employees/show/excel/info", name="show_info_excel", methods="GET")
     */
    public function excelTable(Request $request): StreamedResponse
    {
        if (is_numeric($request->get('id')) or is_string($request->get('name'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return new StreamedResponse($this->service->excelTable($request->get('id')), Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=' . $request->get('name') . ".xlsx",
            'Cache-Control'=>'max-age=0'
        ]);
    }
    /**
     * @Route("/students/newGroup", name="new_group", methods="POST")
     */
    public function newStudentGroup(Request $request): JsonResponse
    {
        $dto = new StudentDto();
        $dto->id = $request->get('id');
        $dto->groupId = $request->get('groupId');
        return $this->json($this->service->newStudentGroup($dto));
    }
    /**
     * @Route("/students/newStudent", name="new_student", methods="POST")
     */
    public function newStudent(Request $request): JsonResponse
    {
        $dto = new StudentDto();
        $dto->name = $request->get('name');
        $dto->groupId = $request->get('groupId');
        $dto->alias = $request->get('alias');
        return $this->json($this->service->newStudent($dto));
    }
    /**
     * @Route("/students/show", name="student_show", methods="GET")
     */
    public function show(Request $request): JsonResponse
    {
        if (is_numeric($request->get('id'))){ //remove
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->showStudent($request->get('id')));
    }
    /**
     * @Route("/students/edit", methods="PUT")
     */
    public function update(Request $request): JsonResponse
    {
        if (is_numeric($request->get('id'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        $dto = new StudentDto();
        $dto->name = $request->get('name');
        $dto->groupId = $request->get('groupId');
        $dto->alias = $request->get('alias');
        return $this->json($this->service->updateStudent($request->get('id'), $dto));
    }
    /**
     * @Route("/students/remove", methods="DELETE")
     */
    public function remove(Request $request): JsonResponse
    {
        if (!gettype($request->get('id')) == 'integer'){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->removeStudent($request->get('id')));
    }
    /**
     * @Route ("/students/showGroups/{id}", methods="GET")
     */
    public function showGroups(int $id){
        if (!gettype($id) == 'integer'){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->showGroups($id));
    }

}