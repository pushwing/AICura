<?php

namespace App\Controllers\Admin;

use App\Models\DepartmentModel;
use App\Models\HospitalModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;

/**
 * Admin 진료과 마스터 관리 (이슈 #113)
 *
 * 진료과 코드 집합(departments)의 추가·수정·삭제를 담당한다.
 * 코드는 병원 진료과 필터(filter[department])의 기준이 된다.
 */
class DepartmentController extends BaseAdminController
{
    private DepartmentModel $departmentModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->departmentModel = model(DepartmentModel::class);
    }

    public function index(): string
    {
        $departments = $this->departmentModel->allForAdmin();
        $counts      = $this->departmentModel->hospitalCounts();

        $departments = array_map(static function (array $row) use ($counts): array {
            $row['hospital_count'] = $counts[(int) $row['id']] ?? 0;

            return $row;
        }, $departments);

        return $this->render('admin/departments/index', ['departments' => $departments]);
    }

    public function create(): ResponseInterface
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = strtolower(trim((string) $this->request->getPost('code')));
        if ($this->departmentModel->codeExists($code)) {
            return redirect()->back()->withInput()->with('errors', ['code' => '이미 사용 중인 진료과 코드입니다.']);
        }

        $this->departmentModel->insert([
            'code'      => $code,
            'name'      => (string) $this->request->getPost('name'),
            'sort'      => (int) $this->request->getPost('sort'),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ]);

        model(HospitalModel::class)->invalidateConsumerCache();

        return redirect()->to('/admin/departments')->with('success', '진료과가 추가되었습니다.');
    }

    public function update(int $id): ResponseInterface
    {
        $department = $this->departmentModel->find($id);
        if ($department === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = strtolower(trim((string) $this->request->getPost('code')));
        if ($this->departmentModel->codeExists($code, $id)) {
            return redirect()->back()->withInput()->with('errors', ['code' => '이미 사용 중인 진료과 코드입니다.']);
        }

        $this->departmentModel->update($id, [
            'code'      => $code,
            'name'      => (string) $this->request->getPost('name'),
            'sort'      => (int) $this->request->getPost('sort'),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ]);

        model(HospitalModel::class)->invalidateConsumerCache();

        return redirect()->to('/admin/departments')->with('success', '진료과가 수정되었습니다.');
    }

    public function delete(int $id): ResponseInterface
    {
        $department = $this->departmentModel->find($id);
        if ($department === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 병원에 매핑된 진료과는 삭제 차단 — 비활성화로 안내
        if ($this->departmentModel->hasHospitalMappings($id)) {
            return redirect()->to('/admin/departments')
                ->with('error', '병원에 매핑된 진료과는 삭제할 수 없습니다. 비활성으로 변경하세요.');
        }

        $this->departmentModel->delete($id);

        return redirect()->to('/admin/departments')->with('success', '진료과가 삭제되었습니다.');
    }

    /**
     * @return array<string, string>
     */
    private function rules(): array
    {
        return [
            'code' => 'required|max_length[30]|regex_match[/^[a-z][a-z0-9_]*$/]',
            'name' => 'required|max_length[100]',
            'sort' => 'permit_empty|is_natural',
        ];
    }
}
