<?php

namespace App\Controllers\Portal;

use App\Models\AdvertiserModel;
use App\Models\HospitalModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 대행사 광고주 관리 (이슈 #32)
 *
 * 대행사는 자신이 소유한(agency_user_id = 로그인 계정) 광고주만 조회·등록·관리한다.
 */
class AdvertiserController extends BasePortalController
{
    private AdvertiserModel $advertiserModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->advertiserModel = model(AdvertiserModel::class);
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
            'pageTitle'  => '광고주 상세',
            'advertiser' => $advertiser,
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

        // 선택적 광고주 계정(owner) 연결 — 이메일이 입력된 경우 병원 유형 계정만 허용
        $ownerUserId = null;
        $ownerEmail  = trim((string) $this->request->getPost('owner_email'));
        if ($ownerEmail !== '') {
            $owner = model(UserModel::class)->findHospitalUserByEmail($ownerEmail);
            if ($owner === null) {
                return redirect()->back()->withInput()
                    ->with('errors', ['owner_email' => '해당 이메일의 광고주 계정을 찾을 수 없습니다.']);
            }
            $ownerUserId = (int) $owner['id'];

            // 광고주 계정은 1:1 — 이미 다른 광고주에 연결된 계정인지 검사
            $alreadyLinked = $this->advertiserModel
                ->where('owner_user_id', $ownerUserId)
                ->countAllResults() > 0;
            if ($alreadyLinked) {
                return redirect()->back()->withInput()
                    ->with('errors', ['owner_email' => '이미 다른 광고주에 연결된 계정입니다.']);
            }
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
            'owner_user_id'      => $ownerUserId,
            'contract_agreed_at' => null,
        ];

        $id = $this->advertiserModel->insert($data);
        if ($id === false) {
            return redirect()->back()->withInput()->with('errors', $this->advertiserModel->errors());
        }

        return redirect()->to('/portal/advertisers/' . $id)
            ->with('success', '광고주가 등록되었습니다. 광고주의 계약 동의 후 사용 가능 상태가 됩니다.');
    }
}
