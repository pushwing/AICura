<?php

namespace App\Controllers\Portal;

use App\Models\AdvertiserModel;
use App\Models\AdvertiserOwnerInviteModel;
use App\Models\HospitalModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 대행사 광고주 관리 (이슈 #32)
 *
 * 대행사는 자신이 소유한(agency_user_id = 로그인 계정) 광고주만 조회·등록·관리한다.
 */
class AdvertiserController extends BasePortalController
{
    private AdvertiserModel $advertiserModel;
    private AdvertiserOwnerInviteModel $inviteModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->advertiserModel = model(AdvertiserModel::class);
        $this->inviteModel     = model(AdvertiserOwnerInviteModel::class);
    }

    public function index(): string
    {
        $this->requireAgency();

        $params = [
            'hospital_name' => $this->request->getGet('hospital_name') ?? '',
            'status'        => $this->request->getGet('status') ?? '',
            'page'          => (int) ($this->request->getGet('page') ?? 1),
            'limit'         => 20,
        ];

        $result = $this->advertiserModel->getListByAgency($this->userId(), $params);

        $advertisers = array_map(function (array $row): array {
            $row['created_at_kst'] = !empty($row['created_at']) ? $this->toKst($row['created_at']) : '-';
            return $row;
        }, $result['list']);

        return $this->render('portal/advertisers/index', [
            'pageTitle'   => '광고주 관리',
            'advertisers' => $advertisers,
            'total'       => $result['total'],
            'params'      => $params,
        ]);
    }

    public function show(int $id): string
    {
        $this->requireAgency();

        $advertiser = $this->advertiserModel->findOwnedByAgency($id, $this->userId());
        if ($advertiser === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $advertiser['created_at_kst']        = !empty($advertiser['created_at']) ? $this->toKst($advertiser['created_at']) : '-';
        $advertiser['contract_agreed_at_kst'] = !empty($advertiser['contract_agreed_at']) ? $this->toKst($advertiser['contract_agreed_at']) : '';

        return $this->render('portal/advertisers/show', [
            'pageTitle'    => '광고주 상세',
            'advertiser'   => $advertiser,
            'hasInvite'    => $this->inviteModel->hasPendingForAdvertiser($id),
        ]);
    }

    public function newForm(): string
    {
        $this->requireAgency();

        return $this->render('portal/advertisers/form', [
            'pageTitle' => '광고주 등록',
            'hospitals' => model(HospitalModel::class)->getActiveList(),
        ]);
    }

    public function create(): ResponseInterface
    {
        $this->requireAgency();

        $rules = [
            'hospital_id'   => 'required|integer|greater_than[0]|is_not_unique[hospitals.id]|is_unique[advertisers.hospital_id]',
            'hospital_name' => 'required|max_length[255]',
            'contact_name'  => 'permit_empty|max_length[100]',
            'contact_phone' => 'permit_empty|max_length[30]',
            'contact_email' => 'permit_empty|valid_email|max_length[255]',
            'owner_email'   => 'permit_empty|valid_email|max_length[255]',
        ];
        $messages = [
            'hospital_id' => [
                'is_unique'     => '이미 등록된 병원입니다.',
                'is_not_unique' => '존재하지 않는 병원입니다.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 광고주 계정(owner) 연결 — 즉시 바인딩하지 않고 초대를 생성한다 (이슈 #38).
        // owner_user_id 는 당사자가 로그인 후 초대를 수락해야 확정된다.
        // 이메일이 입력된 경우 등록 전에 초대 대상(병원유형·미연결 계정)을 먼저 검증한다.
        $ownerEmail   = trim((string) $this->request->getPost('owner_email'));
        $inviteeId    = null;
        if ($ownerEmail !== '') {
            $resolved = $this->resolveInvitee($ownerEmail);
            if (isset($resolved['error'])) {
                return redirect()->back()->withInput()->with('errors', ['owner_email' => $resolved['error']]);
            }
            $inviteeId = $resolved['userId'];
        }

        $data = [
            'hospital_id'        => (int) $this->request->getPost('hospital_id'),
            'hospital_name'      => $this->request->getPost('hospital_name'),
            'contact_name'       => $this->request->getPost('contact_name') ?: null,
            'contact_email'      => $this->request->getPost('contact_email') ?: null,
            'contact_phone'      => $this->request->getPost('contact_phone') ?: null,
            'business_no'        => $this->request->getPost('business_no') ?: null,
            'is_network'         => 0,
            'status'             => 1,
            'agency_user_id'     => $this->userId(),
            'owner_user_id'      => null,
            'contract_agreed_at' => null,
        ];

        $id = $this->advertiserModel->insert($data);
        if ($id === false) {
            return redirect()->back()->withInput()->with('errors', $this->advertiserModel->errors());
        }

        if ($inviteeId !== null) {
            $this->inviteModel->createInvite((int) $id, $this->userId(), $inviteeId, $ownerEmail);

            return redirect()->to('/portal/advertisers/' . $id)
                ->with('success', '광고주가 등록되었습니다. 광고주 계정에 연결 초대를 보냈으며, 당사자 수락 후 연결이 확정됩니다.');
        }

        return redirect()->to('/portal/advertisers/' . $id)
            ->with('success', '광고주가 등록되었습니다. 광고주의 계약 동의 후 사용 가능 상태가 됩니다.');
    }

    /**
     * 기존 광고주에 owner 연결 초대 발송 (이슈 #38)
     */
    public function invite(int $id): RedirectResponse
    {
        $this->requireAgency();

        $advertiser = $this->advertiserModel->findOwnedByAgency($id, $this->userId());
        if ($advertiser === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $back = redirect()->to('/portal/advertisers/' . $id);

        if (!empty($advertiser['owner_user_id'])) {
            return $back->with('error', '이미 광고주 계정이 연결되어 있습니다.');
        }
        if ($this->inviteModel->hasPendingForAdvertiser($id)) {
            return $back->with('error', '이미 응답 대기 중인 초대가 있습니다.');
        }

        // create()와 동일한 검증 경로 통일
        if (!$this->validate(['owner_email' => 'required|valid_email|max_length[255]'])) {
            return $back->with('error', '올바른 이메일을 입력해주세요.');
        }

        $ownerEmail = trim((string) $this->request->getPost('owner_email'));
        $resolved   = $this->resolveInvitee($ownerEmail);
        if (isset($resolved['error'])) {
            return $back->with('error', $resolved['error']);
        }

        $this->inviteModel->createInvite($id, $this->userId(), $resolved['userId'], $ownerEmail);

        return $back->with('success', '연결 초대를 보냈습니다. 당사자 수락 후 연결이 확정됩니다.');
    }

    /**
     * 초대 대상 계정 해석 — 병원유형·미연결 계정만 허용
     *
     * @return array{userId: int}|array{error: string}
     */
    private function resolveInvitee(string $email): array
    {
        $owner = model(UserModel::class)->findHospitalUserByEmail($email);
        if ($owner === null) {
            return ['error' => '해당 이메일의 광고주 계정을 찾을 수 없습니다.'];
        }
        $ownerUserId = (int) $owner['id'];

        // 광고주 계정은 1:1 — 이미 다른 광고주에 연결된 계정인지 검사
        $alreadyLinked = $this->advertiserModel
            ->where('owner_user_id', $ownerUserId)
            ->countAllResults() > 0;
        if ($alreadyLinked) {
            return ['error' => '이미 다른 광고주에 연결된 계정입니다.'];
        }

        return ['userId' => $ownerUserId];
    }
}
