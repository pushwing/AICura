<?php

namespace App\Controllers\Admin;

use Override;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\AdvertiserModel;
use App\Models\HospitalModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AdvertiserController extends BaseAdminController
{
    private AdvertiserModel $advertiserModel;
    private HospitalModel $hospitalModel;
    private UserModel $userModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->advertiserModel = model(AdvertiserModel::class);
        $this->hospitalModel   = model(HospitalModel::class);
        $this->userModel       = model(UserModel::class);
    }

    // ──────────────────────────────────────────────
    // 광고주 목록
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'hospital_name' => $this->request->getGet('hospital_name') ?? '',
            'status'        => $this->request->getGet('status') ?? '',
            'is_network'    => $this->request->getGet('is_network') ?? '',
            'page'          => (int) ($this->request->getGet('page') ?? 1),
            'limit'         => 20,
        ];

        $result = $this->advertiserModel->getList($params);

        // Fix #6: toKst() 헬퍼로 KST 변환 통합
        $advertisers = array_map(function (array $row): array {
            $row['created_at_kst'] = empty($row['created_at']) ? '-' : $this->toKst($row['created_at']);
            return $row;
        }, $result['list']);

        return $this->render('admin/advertisers/index', [
            'advertisers' => $advertisers,
            'total'       => $result['total'],
            'params'      => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 상세 (계약 목록 + KPI 포함)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $advertiser = $this->advertiserModel->getDetail($id);
        if ($advertiser === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 연결된 광고주 본인 계정 이메일 (있으면)
        $advertiser['owner_email'] = null;
        if (!empty($advertiser['owner_user_id'])) {
            $owner = $this->userModel->select('email')->find((int) $advertiser['owner_user_id']);
            $advertiser['owner_email'] = is_array($owner) ? $owner['email'] : null;
        }

        // Fix #6: toKst() 헬퍼로 KST 변환 통합
        $advertiser['created_at_kst'] = empty($advertiser['created_at']) ? '-' : $this->toKst($advertiser['created_at']);

        /** @var list<array<string, mixed>> $contracts */
        $contracts = is_array($advertiser['contracts'] ?? null) ? $advertiser['contracts'] : [];
        $advertiser['contracts'] = array_map(function (array $c): array {
            $c['created_at_kst'] = empty($c['created_at']) ? '-' : $this->toKst($c['created_at']);
            return $c;
        }, $contracts);

        return $this->render('admin/advertisers/show', ['advertiser' => $advertiser]);
    }

    // ──────────────────────────────────────────────
    // 광고주 등록 폼
    // ──────────────────────────────────────────────

    // Fix #4: new() → newForm() (new는 PHP 예약어)
    public function newForm(): string
    {
        return $this->render('admin/advertisers/form', [
            'advertiser'        => null,
            'ownerUser'         => null,
            'hospitals'         => $this->hospitalModel->getActiveList(),
            'parentAdvertisers' => $this->advertiserModel->select('id, hospital_name')
                ->where('is_network', 1)
                ->where('status', 1)
                ->orderBy('hospital_name', 'ASC')
                ->findAll(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 등록 처리
    // ──────────────────────────────────────────────

    public function create(): ResponseInterface
    {
        $createAccount = $this->request->getPost('create_account') === '1';

        // Fix #3: hospital_id 존재 여부 DB 검증 추가
        $rules = [
            'hospital_id'   => 'required|integer|greater_than[0]|is_not_unique[hospitals.id]',
            'hospital_name' => 'required|max_length[255]',
            'contact_email' => 'permit_empty|valid_email',
            'is_network'    => 'required|in_list[0,1,2]',
            'status'        => 'required|in_list[1,2,3]',
        ];

        // 로그인 계정 동시 생성 시 추가 규칙
        if ($createAccount) {
            $rules['login_email']    = 'required|valid_email|max_length[255]';
            $rules['login_password'] = 'required|min_length[8]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $isNetwork = (int) $this->request->getPost('is_network');

        // Fix #2: is_network === 2 일 때만 parent 설정, 미선택(0·빈값)은 null
        $networkParentId = null;
        if ($isNetwork === 2) {
            $raw             = (int) $this->request->getPost('network_parent_id');
            $networkParentId = $raw > 0 ? $raw : null;
        }

        // owner(광고주 본인) 계정 해석 — 기존 병원 계정 연결 또는 신규 생성
        $ownerUserId = null;
        if ($createAccount) {
            $resolved = $this->resolveOwnerUser();
            if (is_string($resolved)) {
                return redirect()->back()->withInput()->with('errors', ['login_email' => $resolved]);
            }
            $ownerUserId = $resolved;
        }

        $data = [
            'hospital_id'       => (int) $this->request->getPost('hospital_id'),
            'hospital_name'     => $this->request->getPost('hospital_name'),
            'contact_name'      => $this->request->getPost('contact_name') ?: null,
            'contact_email'     => $this->request->getPost('contact_email') ?: null,
            'contact_phone'     => $this->request->getPost('contact_phone') ?: null,
            'business_no'       => $this->request->getPost('business_no') ?: null,
            'is_network'        => $isNetwork,
            'network_parent_id' => $networkParentId,
            'status'            => (int) $this->request->getPost('status'),
            'owner_user_id'     => $ownerUserId,
        ];

        // user 생성 + advertiser 저장을 원자적으로 처리 — 광고주 저장 실패 시 신규 계정 롤백
        $db = db_connect();
        $db->transBegin();

        $newUserId = null;
        if ($createAccount && $ownerUserId === null) {
            $newUserId = $this->userModel->createHospitalOwner(
                (string) $this->request->getPost('login_email'),
                (string) $this->request->getPost('login_password'),
                $this->request->getPost('contact_name') ?: null,
                $this->request->getPost('contact_phone') ?: null
            );

            if ($newUserId === false) {
                $db->transRollback();

                return redirect()->back()->withInput()
                    ->with('errors', ['login_email' => '로그인 계정 생성에 실패했습니다.']);
            }

            $data['owner_user_id'] = $newUserId;
        }

        $id = $this->advertiserModel->insert($data);
        if ($id === false) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', $this->advertiserModel->errors());
        }

        $db->transCommit();

        $msg = $createAccount
            ? '광고주가 등록되고 로그인 계정이 연결되었습니다.'
            : '광고주가 등록되었습니다.';

        return redirect()->to('/admin/advertisers/' . $id)->with('success', $msg);
    }

    /**
     * 로그인 계정 연결 대상(owner_user_id) 해석
     *
     * 반환값:
     *   - int   : 기존 병원 계정을 연결 (해당 user id)
     *   - null  : 연결할 기존 계정 없음 → 신규 생성 필요
     *   - string: 오류 메시지 (이미 연결된 계정·중복 이메일 등)
     */
    private function resolveOwnerUser(): int|string|null
    {
        $loginEmail = (string) $this->request->getPost('login_email');

        // 병원 유형 계정이 이미 있으면 연결 대상으로 사용
        $existing = $this->userModel->findHospitalUserByEmail($loginEmail);
        if ($existing !== null) {
            // owner는 1:1 — 이미 다른 광고주에 연결되어 있으면 거부
            if ($this->advertiserModel->findByOwner((int) $existing['id']) !== null) {
                return '해당 계정은 이미 다른 광고주에 연결되어 있습니다.';
            }

            return (int) $existing['id'];
        }

        // 병원 유형은 아니지만 이메일이 이미 사용 중이면 신규 생성 불가
        if ($this->userModel->emailExists($loginEmail)) {
            return '해당 이메일은 이미 사용 중입니다.';
        }

        return null; // 신규 생성 진행
    }

    // ──────────────────────────────────────────────
    // 광고주 수정 폼
    // ──────────────────────────────────────────────

    public function edit(int $id): string
    {
        $advertiser = $this->advertiserModel->find($id);
        if ($advertiser === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 연결된 광고주 본인 계정 (있으면 수정 폼에 표시)
        $ownerUser = null;
        if (!empty($advertiser['owner_user_id'])) {
            $ownerUser = $this->userModel
                ->select('id, email, username')
                ->find((int) $advertiser['owner_user_id']);
        }

        return $this->render('admin/advertisers/form', [
            'advertiser'        => $advertiser,
            'ownerUser'         => $ownerUser,
            'hospitals'         => $this->hospitalModel->getActiveList(),
            'parentAdvertisers' => $this->advertiserModel->select('id, hospital_name')
                ->where('is_network', 1)
                ->where('status', 1)
                ->orderBy('hospital_name', 'ASC')
                ->findAll(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 수정 처리
    // ──────────────────────────────────────────────

    public function update(int $id): ResponseInterface
    {
        $advertiser = $this->advertiserModel->find($id);
        if ($advertiser === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 아직 owner 미연결인 경우에만 계정 연결 허용
        $canLink       = empty($advertiser['owner_user_id']);
        $createAccount = $canLink && $this->request->getPost('create_account') === '1';

        $rules = [
            'hospital_name' => 'required|max_length[255]',
            'contact_email' => 'permit_empty|valid_email',
            'is_network'    => 'required|in_list[0,1,2]',
            'status'        => 'required|in_list[1,2,3]',
        ];

        if ($createAccount) {
            $rules['login_email']    = 'required|valid_email|max_length[255]';
            $rules['login_password'] = 'required|min_length[8]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $isNetwork = (int) $this->request->getPost('is_network');

        // Fix #2: is_network === 2 일 때만 parent 설정, 미선택(0·빈값)은 null
        $networkParentId = null;
        if ($isNetwork === 2) {
            $raw             = (int) $this->request->getPost('network_parent_id');
            $networkParentId = $raw > 0 ? $raw : null;
        }

        $updateData = [
            'hospital_name'     => $this->request->getPost('hospital_name'),
            'contact_name'      => $this->request->getPost('contact_name') ?: null,
            'contact_email'     => $this->request->getPost('contact_email') ?: null,
            'contact_phone'     => $this->request->getPost('contact_phone') ?: null,
            'business_no'       => $this->request->getPost('business_no') ?: null,
            'is_network'        => $isNetwork,
            'network_parent_id' => $networkParentId,
            'status'            => (int) $this->request->getPost('status'),
        ];

        // 계정 연결 해석 (미연결 + 연결 요청 시)
        if ($createAccount) {
            $resolved = $this->resolveOwnerUser();
            if (is_string($resolved)) {
                return redirect()->back()->withInput()->with('errors', ['login_email' => $resolved]);
            }
            $updateData['owner_user_id'] = $resolved; // int(기존 연결) 또는 null(신규 생성 예정)
        }

        $db = db_connect();
        $db->transBegin();

        if ($createAccount && ($updateData['owner_user_id'] ?? null) === null) {
            $newUserId = $this->userModel->createHospitalOwner(
                (string) $this->request->getPost('login_email'),
                (string) $this->request->getPost('login_password'),
                $this->request->getPost('contact_name') ?: null,
                $this->request->getPost('contact_phone') ?: null
            );

            if ($newUserId === false) {
                $db->transRollback();

                return redirect()->back()->withInput()
                    ->with('errors', ['login_email' => '로그인 계정 생성에 실패했습니다.']);
            }

            $updateData['owner_user_id'] = $newUserId;
        }

        if ($this->advertiserModel->update($id, $updateData) === false) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', $this->advertiserModel->errors());
        }

        $db->transCommit();

        $msg = $createAccount ? '수정되고 로그인 계정이 연결되었습니다.' : '수정되었습니다.';

        return redirect()->to('/admin/advertisers/' . $id)->with('success', $msg);
    }
}
