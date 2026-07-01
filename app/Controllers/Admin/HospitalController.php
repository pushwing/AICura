<?php

namespace App\Controllers\Admin;

use App\Models\DepartmentModel;
use App\Models\HospitalModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin 병원 관리 (이슈 #113)
 *
 * 병원 CRUD + 진료과(department) 다대다 매핑을 담당한다.
 */
class HospitalController extends BaseAdminController
{
    private HospitalModel $hospitalModel;
    private DepartmentModel $departmentModel;

    // 목록 페이지당 행 수
    private const PER_PAGE = 20;

    /** @var array<int, string> 병원 망 구분 라벨 (hospitals.type) */
    private const TYPE_LABELS = [
        1 => '일반',
        2 => '네트워크(모)',
        3 => '네트워크(자)',
    ];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->hospitalModel   = model(HospitalModel::class);
        $this->departmentModel = model(DepartmentModel::class);
    }

    // ──────────────────────────────────────────────
    // 목록 (AG Grid)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'name'   => (string) ($this->request->getGet('name') ?? ''),
            'status' => (string) ($this->request->getGet('status') ?? ''),
            'page'   => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'  => self::PER_PAGE,
        ];

        $result = $this->hospitalModel->getList($params);

        /** @var list<array<string, mixed>> $list */
        $list = $result['list'];

        // 진료과 배지·망 구분 라벨·KST 변환을 행에 부착
        $ids     = array_map(static fn (array $r): int => (int) $r['id'], $list);
        $deptMap = $this->departmentModel->byHospitalIds($ids);

        $hospitals = array_map(function (array $row) use ($deptMap): array {
            $id                       = (int) $row['id'];
            $row['type_label']        = self::TYPE_LABELS[(int) $row['type']] ?? '-';
            $row['departments']       = $deptMap[$id] ?? [];
            $row['department_names']  = implode(', ', array_column($deptMap[$id] ?? [], 'name'));
            $row['created_at_kst']    = !empty($row['created_at']) ? $this->toKst($row['created_at']) : '-';
            return $row;
        }, $list);

        return $this->render('admin/hospitals/index', [
            'hospitals' => $hospitals,
            'total'     => $result['total'],
            'params'    => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 등록·수정 폼
    // ──────────────────────────────────────────────

    public function newForm(): string
    {
        return $this->render('admin/hospitals/form', [
            'hospital'     => null,
            'departments'  => $this->departmentModel->allForAdmin(),
            'selectedDept' => [],
        ]);
    }

    public function edit(int $id): string
    {
        $hospital = $this->hospitalModel->find($id);
        if ($hospital === null || (int) $hospital['is_deleted'] === 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/hospitals/form', [
            'hospital'     => $hospital,
            'departments'  => $this->departmentModel->allForAdmin(),
            'selectedDept' => $this->departmentModel->idsByHospital($id),
        ]);
    }

    // ──────────────────────────────────────────────
    // 등록·수정 처리
    // ──────────────────────────────────────────────

    public function create(): ResponseInterface
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();
        $db->transBegin();

        $id = $this->hospitalModel->insert($this->formData());
        if ($id === false) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', $this->hospitalModel->errors());
        }

        $this->departmentModel->syncForHospital((int) $id, $this->postedDepartmentIds());
        $this->hospitalModel->invalidateConsumerCache();

        $db->transCommit();

        $this->submitIndexNow((int) $id);

        return redirect()->to('/admin/hospitals')->with('success', '병원이 등록되었습니다.');
    }

    public function update(int $id): ResponseInterface
    {
        $hospital = $this->hospitalModel->find($id);
        if ($hospital === null || (int) $hospital['is_deleted'] === 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();
        $db->transBegin();

        if ($this->hospitalModel->update($id, $this->formData()) === false) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', $this->hospitalModel->errors());
        }

        $this->departmentModel->syncForHospital($id, $this->postedDepartmentIds());
        $this->hospitalModel->invalidateConsumerCache();

        $db->transCommit();

        $this->submitIndexNow($id);

        return redirect()->to('/admin/hospitals')->with('success', '병원이 수정되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 삭제 (soft delete + 매핑 정리)
    // ──────────────────────────────────────────────

    public function delete(int $id): ResponseInterface
    {
        $hospital = $this->hospitalModel->find($id);
        if ($hospital === null || (int) $hospital['is_deleted'] === 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transBegin();

        $this->hospitalModel->update($id, ['is_deleted' => 1, 'status' => 'inactive']);
        $this->departmentModel->syncForHospital($id, []); // 매핑 제거
        $this->hospitalModel->invalidateConsumerCache();

        $db->transCommit();

        return redirect()->to('/admin/hospitals')->with('success', '병원이 삭제되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 헬퍼
    // ──────────────────────────────────────────────

    /** @return array<string, string> */
    private function rules(): array
    {
        return [
            'name'           => 'required|max_length[255]',
            'type'           => 'required|in_list[1,2,3]',
            'status'         => 'required|in_list[active,inactive]',
            'phone'          => 'permit_empty|max_length[20]',
            'address'        => 'permit_empty|max_length[500]',
            'departments.*'  => 'permit_empty|is_not_unique[departments.id]',
        ];
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'name'    => (string) $this->request->getPost('name'),
            'type'    => (int) $this->request->getPost('type'),
            'phone'   => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
            'status'  => (string) $this->request->getPost('status'),
        ];
    }

    /** @return list<int> */
    private function postedDepartmentIds(): array
    {
        $posted = $this->request->getPost('departments');

        return is_array($posted) ? array_map('intval', $posted) : [];
    }

    /** 활성 병원 공개 URL 을 IndexNow 에 제출 (이슈 #152). */
    private function submitIndexNow(int $id): void
    {
        if ((string) $this->request->getPost('status') === 'active') {
            service('indexNowService')->submit(base_url('hospitals/' . $id));
        }
    }
}
