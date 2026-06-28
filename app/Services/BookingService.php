<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Exceptions\NotFoundException;
use App\Models\BookingModel;
use App\Models\CallRequestModel;
use App\Models\HospitalModel;

/**
 * 외부(소비자) 앱 예약 서비스 (이슈 #101)
 *
 * 병원 예약 생성·조회·변경·취소를 담당한다. 모든 조작은 본인 소유 건으로 한정한다.
 */
class BookingService
{
    private BookingModel $bookings;
    private HospitalModel $hospitals;
    private CallRequestModel $callRequests;

    public function __construct(
        ?BookingModel $bookings = null,
        ?HospitalModel $hospitals = null,
        ?CallRequestModel $callRequests = null,
    ) {
        $this->bookings     = $bookings     ?? model(BookingModel::class);
        $this->hospitals    = $hospitals    ?? model(HospitalModel::class);
        $this->callRequests = $callRequests ?? model(CallRequestModel::class);
    }

    /**
     * 예약 생성
     *
     * @param array<string, mixed> $input hospital_id·name·phone·book_date·call_request_id
     * @return array<string, mixed>
     * @throws NotFoundException 노출 불가 병원·타인 상담건
     */
    public function create(int $userId, array $input): array
    {
        $hospitalId = (int) $input['hospital_id'];
        if (!$this->hospitals->isVisible($hospitalId)) {
            throw NotFoundException::of('예약할 수 있는 병원이 아닙니다.');
        }

        // call_request 연동 시 본인 소유 검증
        $callRequestId = isset($input['call_request_id']) ? (int) $input['call_request_id'] : null;
        if ($callRequestId !== null && $callRequestId > 0
            && $this->callRequests->findOwned($callRequestId, $userId) === null) {
            throw NotFoundException::of('연결할 상담 신청을 찾을 수 없습니다.');
        }

        $id = $this->bookings->createBooking([
            'user_id'         => $userId,
            'hospital_id'     => $hospitalId,
            'call_request_id' => $callRequestId,
            'name'            => $this->nullableString($input['name'] ?? null),
            'phone'           => $this->nullableString($input['phone'] ?? null),
            'book_date'       => $this->normalizeDate($input['book_date'] ?? null),
        ]);

        return $this->detail($userId, $id);
    }

    /**
     * 본인 예약 상세
     *
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function detail(int $userId, int $id): array
    {
        $row = $this->bookings->getOwnedDetail($id, $userId);
        if ($row === null) {
            throw NotFoundException::of('예약을 찾을 수 없습니다.');
        }

        return $this->transform($row);
    }

    /**
     * 본인 예약 변경 — 취소된 예약은 변경 불가.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     * @throws NotFoundException|BookingException
     */
    public function update(int $userId, int $id, array $input): array
    {
        $booking = $this->bookings->findOwned($id, $userId);
        if ($booking === null) {
            throw NotFoundException::of('예약을 찾을 수 없습니다.');
        }
        if ((int) $booking['status'] === BookingModel::STATUS_CANCELLED) {
            throw BookingException::alreadyCancelled();
        }

        $data = [];
        if (array_key_exists('name', $input)) {
            $data['name'] = $this->nullableString($input['name']);
        }
        if (array_key_exists('phone', $input)) {
            $data['phone'] = $this->nullableString($input['phone']);
        }
        if (array_key_exists('book_date', $input)) {
            $data['book_date'] = $this->normalizeDate($input['book_date']);
        }

        $this->bookings->updateOwned($id, $data);

        return $this->detail($userId, $id);
    }

    /**
     * 본인 예약 취소
     *
     * @throws NotFoundException|BookingException
     */
    public function cancel(int $userId, int $id): void
    {
        $booking = $this->bookings->findOwned($id, $userId);
        if ($booking === null) {
            throw NotFoundException::of('예약을 찾을 수 없습니다.');
        }
        if ((int) $booking['status'] === BookingModel::STATUS_CANCELLED) {
            throw BookingException::alreadyCancelled();
        }

        $this->bookings->cancel($id);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transform(array $row): array
    {
        $status = (int) $row['status'];

        return [
            'id'              => (int) $row['id'],
            'hospital_id'     => (int) $row['hospital_id'],
            'hospital_name'   => $row['hospital_name'],
            'call_request_id' => $row['call_request_id'] !== null ? (int) $row['call_request_id'] : null,
            'status'          => $status,
            'status_label'    => BookingModel::STATUSES[$status] ?? null,
            'name'            => $row['name'],
            'phone'           => $row['phone'],
            'book_date'       => $row['book_date'],
            'confirm_date'    => $row['confirm_date'],
            'created_at'      => $row['created_at'],
        ];
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $ts = strtotime($value);

        return $ts === false ? null : date('Y-m-d H:i:s', $ts);
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
