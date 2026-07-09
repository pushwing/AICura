<?php

namespace App\Controllers\Admin;

use App\Models\PaymentModel;
use App\Models\RefundModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class PaymentController extends BaseAdminController
{
    private PaymentModel $paymentModel;
    private RefundModel $refundModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->paymentModel = model(PaymentModel::class);
        $this->refundModel  = model(RefundModel::class);
    }

    // ──────────────────────────────────────────────
    // 목록 (AG Grid)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'status'        => $this->request->getGet('status') ?? '',
            'hospital_name' => $this->request->getGet('hospital_name') ?? '',
            'date_from'     => $this->request->getGet('date_from') ?? '',
            'date_to'       => $this->request->getGet('date_to') ?? '',
            'page'          => (int) ($this->request->getGet('page') ?? 1),
            'limit'         => 20,
        ];

        $result = $this->paymentModel->getPaymentList($params);

        return $this->render('admin/payments/index', [
            'payments'     => $result['list'],
            'total'        => $result['total'],
            'params'       => $params,
            'statuses'     => PaymentModel::STATUSES,
            'paymentTypes' => PaymentModel::PAYMENT_TYPES,
        ]);
    }

    // ──────────────────────────────────────────────
    // 상세
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $payment = $this->paymentModel->getPaymentDetail($id);
        if ($payment === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $refundedTotal = $this->refundModel->getRefundedTotal($id);

        return $this->render('admin/payments/show', [
            'payment'         => $payment,
            'refunds'         => $this->refundModel->getListByPayment($id),
            'refundedTotal'   => $refundedTotal,
            'remainingAmount' => max(0, (int) $payment['amount'] - $refundedTotal),
            'statuses'        => PaymentModel::STATUSES,
            'paymentTypes'    => PaymentModel::PAYMENT_TYPES,
        ]);
    }

    // ──────────────────────────────────────────────
    // 환불 처리 (POST)
    // ──────────────────────────────────────────────

    public function refund(int $id): ResponseInterface
    {
        $payment = $this->paymentModel->getPaymentDetail($id);
        if ($payment === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($payment['status'] === 'refunded') {
            return redirect()->back()->with('error', '이미 전액 환불된 결제입니다.');
        }

        $rules = [
            'refund_type'   => 'required|in_list[2,5]',
            'refund_amount' => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $refundType   = (int) $this->request->getPost('refund_type');
        $refundAmount = (int) $this->request->getPost('refund_amount');

        $remaining = (int) $payment['amount'] - $this->refundModel->getRefundedTotal($id);
        if ($refundAmount > $remaining) {
            return redirect()->back()->with(
                'error',
                '환불 금액이 잔여 환불 가능액(' . number_format($remaining) . '원)을 초과할 수 없습니다.',
            );
        }

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $userId   = (int) ($authUser['id'] ?? 0);

        if ($userId === 0) {
            return redirect()->back()->with('error', '인증 정보를 확인할 수 없습니다.');
        }

        try {
            $this->paymentModel->processRefund($id, $refundAmount, $refundType, $userId);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Throwable) {
            return redirect()->back()->with('error', '환불 처리 중 오류가 발생했습니다.');
        }

        return redirect()->to('/admin/payments/' . $id)
            ->with('success', '환불이 처리되었습니다.');
    }
}
