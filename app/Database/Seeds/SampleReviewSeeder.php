<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * AI 후기 신뢰성 분석 기능 테스트용 샘플 후기 20건 (이슈 #74)
 *
 *   php spark db:seed SampleReviewSeeder
 *   php spark reviews:analyze            # 적재 후 분석 실행
 *
 * 정상·광고성·과장·의학적과장·가짜·중복 등 다양한 패턴을 섞어 플래깅/점수 분포를
 * 눈으로 확인할 수 있게 구성했다. 모델 allowedFields에 작성자·유형 컬럼이 없어
 * Query Builder로 직접 적재한다. ai_status는 PENDING(1)으로 넣어 reviews:analyze가
 * 바로 소비하도록 한다.
 */
class SampleReviewSeeder extends Seeder
{
    public function run(): void
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($this->samples() as $i => $sample) {
            $rows[] = [
                'user_id'        => 20000000001 + $i,
                'user_name'      => $sample['user_name'],
                'type'           => $sample['type'],
                'target_id'      => 100 + $i,
                'subject'        => $sample['subject'],
                'contents'       => $sample['contents'],
                'rate_sum'       => $sample['rate'],
                'like_count'     => $sample['like'],
                'complain_count' => $sample['complain'],
                'is_list'        => 1,
                'is_delete'      => 0,
                'ai_status'      => 1, // PENDING — reviews:analyze가 소비
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        $this->db->table('boards')->insertBatch($rows);
    }

    /**
     * @return array<int, array{user_name: string, type: int, subject: string, contents: string, rate: float, like: int, complain: int}>
     */
    private function samples(): array
    {
        return [
            // ── 정상·신뢰 가능 후기 ───────────────────────────────
            ['user_name'   => '김O은', 'type' => 1, 'rate' => 4.5, 'like' => 12, 'complain' => 0,
                'subject'  => '코 재수술 3개월 경과 후기',
                'contents' => '5년 전 다른 곳에서 한 코가 휘어서 재수술 했어요. 상담 때 CT 찍고 휜 연골 교정이 필요하다고 설명 들었습니다. 수술 후 2주는 붓기가 있었고 한 달쯤 지나니 자연스러워졌어요. 지금 3개월 차인데 옆모습 라인이 만족스럽습니다. 다만 회복 기간은 사람마다 다를 수 있어요.'],
            ['user_name'   => '이O희', 'type' => 1, 'rate' => 4.0, 'like' => 8, 'complain' => 0,
                'subject'  => '눈매교정+쌍수 후기 (붓기 경과 포함)',
                'contents' => '앞트임 없이 눈매교정이랑 쌍꺼풀만 했습니다. 수술 당일은 많이 부었고 실밥 풀고 2주 지나니 티가 많이 줄었어요. 자연스러운 라인 원했는데 원장님이 디자인 잘 잡아주셨습니다. 한 달째 아직 약간 붓기 남아있어요.'],
            ['user_name'   => '박O수', 'type' => 2, 'rate' => 4.5, 'like' => 5, 'complain' => 0,
                'subject'  => '치아교정 6개월 중간 후기',
                'contents' => '덧니 때문에 메탈교정 시작했어요. 처음 일주일은 통증 때문에 죽만 먹었습니다. 6개월 지나니 앞니가 많이 정렬됐어요. 한 달에 한 번 조정하러 가는데 매번 사진으로 변화 보여주셔서 좋습니다.'],
            ['user_name'   => '최O진', 'type' => 2, 'rate' => 3.5, 'like' => 3, 'complain' => 0,
                'subject'  => '피부 레이저토닝 5회 솔직 후기',
                'contents' => '기미 때문에 토닝 5회 받았는데 아주 드라마틱하진 않아요. 옅어지긴 했습니다. 시술 자체는 따끔한 정도고 다운타임 거의 없어요. 자외선 차단 꾸준히 해야 효과 유지된다고 하네요.'],
            ['user_name'   => '정O아', 'type' => 1, 'rate' => 4.0, 'like' => 7, 'complain' => 0,
                'subject'  => '복부 지방흡입 회복 과정 기록',
                'contents' => '복부랑 옆구리 지방흡입 했습니다. 압박복 3주 착용했고 멍은 2주 정도 갔어요. 처음엔 뭉침이 있었는데 마사지 받으면서 풀렸습니다. 무리한 다이어트 없이 라인 정리된 게 만족스러워요.'],

            // ── 광고성 (advertisement) ────────────────────────────
            ['user_name'   => '관리자', 'type' => 1, 'rate' => 5.0, 'like' => 0, 'complain' => 2,
                'subject'  => '★이번 달 한정★ 코성형 50% 할인 이벤트',
                'contents' => '○○성형외과 여름 이벤트! 코성형 정상가 300만원→150만원 파격 할인! 눈성형 패키지 동시 진행 시 추가 20% 할인까지! 지금 바로 전화 예약 010-1234-5678 / 카톡 상담 @clinic 선착순 마감 임박!!'],
            ['user_name'   => '홍보팀', 'type' => 2, 'rate' => 5.0, 'like' => 0, 'complain' => 1,
                'subject'  => '최저가 보장 시술 정보 공유',
                'contents' => '강남 최저가 보장! 보톡스 9900원, 필러 이벤트가 더보기 ☞ http://bit.ly/promo-link 무료 상담 신청하면 사은품 증정. 자세한 내용은 링크 클릭 http://event.example.com 후기 이벤트 참여하고 적립금 받으세요.'],

            // ── 과장 (exaggeration) ───────────────────────────────
            ['user_name'   => 'happy', 'type' => 1, 'rate' => 5.0, 'like' => 1, 'complain' => 0,
                'subject'  => '인생이 완전히 바뀌었어요!!!',
                'contents' => '진짜 인생이 통째로 바뀌었습니다!!! 100% 만족 그 자체!! 세상에서 제일 예뻐졌고 자신감 폭발이에요!!! 무조건 무조건 강력 추천합니다 안 하면 후회해요 인생 최고의 선택!!!!!'],
            ['user_name'   => '별빛', 'type' => 1, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '기적이 일어났습니다 완벽 그 자체',
                'contents' => '이건 기적입니다. 완벽 그 자체예요. 살면서 이렇게 만족스러운 적이 없었어요. 모든 게 완벽하고 단점이 1도 없습니다. 천사 같은 원장님 덕분에 새 삶을 얻었어요!!'],

            // ── 의학적 과대표현 (medical_overclaim) ────────────────
            ['user_name'   => '믿음', 'type' => 2, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '부작용 전혀 없는 안전한 시술',
                'contents' => '여기는 부작용이 전혀 없어요. 통증도 제로, 흉터도 절대 안 남습니다. 100% 안전하고 무조건 성공 보장하는 곳이에요. 회복도 필요 없이 바로 일상생활 가능합니다. 누구나 해도 완벽한 결과 나와요.'],
            ['user_name'   => '확신', 'type' => 1, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '실패 확률 0% 보장 시술',
                'contents' => '원장님이 실패 확률 0%라고 보장해주셨어요. 부작용 걱정 전혀 안 해도 되고 평생 유지된다고 합니다. 어떤 체질이든 무조건 효과 보장이라 안심하고 받았습니다.'],

            // ── 가짜·내용 부실 (fake) ──────────────────────────────
            ['user_name'   => 'ㅇㅇ', 'type' => 1, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '좋아요',
                'contents' => '좋아요 추천합니다 만족해요 굿굿'],
            ['user_name'   => 'user01', 'type' => 1, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '추천',
                'contents' => '여기 진짜 잘해요 강추 강추 다들 여기로 가세요 후회 안 함'],

            // ── 중복·정형 문장 (duplicate) ─────────────────────────
            ['user_name'   => '방문객A', 'type' => 2, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '친절하고 깔끔한 병원',
                'contents' => '친절하고 깔끔한 병원입니다. 원장님이 자세히 설명해주시고 직원분들도 모두 친절합니다. 시설도 청결하고 대기 시간도 짧아요. 강력 추천합니다.'],
            ['user_name'   => '방문객B', 'type' => 2, 'rate' => 5.0, 'like' => 0, 'complain' => 0,
                'subject'  => '친절하고 깔끔한 곳이에요',
                'contents' => '친절하고 깔끔한 병원입니다. 원장님이 자세히 설명해주시고 직원분들도 모두 친절합니다. 시설도 청결하고 대기 시간도 짧아요. 강력 추천합니다!'],

            // ── 스팸 (spam) ───────────────────────────────────────
            ['user_name'   => 'spammer', 'type' => 1, 'rate' => 0.0, 'like' => 0, 'complain' => 3,
                'subject'  => '재택 부업 월 500 보장',
                'contents' => '집에서 하루 2시간 투자로 월 500 보장! 후기와 상관없지만 좋은 정보라 공유해요. 관심 있으면 텔레그램 @money_bot 으로 연락주세요. 초기 비용 없이 시작 가능합니다.'],

            // ── 중립·정보성 (neutral) ──────────────────────────────
            ['user_name'   => '나무', 'type' => 3, 'rate' => 3.0, 'like' => 2, 'complain' => 0,
                'subject'  => '상담만 받고 온 후기',
                'contents' => '시술은 아직 안 했고 상담만 받았어요. 비용이랑 회복 기간 안내받았습니다. 가격은 평균적인 수준인 것 같고 결정은 좀 더 알아보고 하려고요. 상담은 무료였습니다.'],

            // ── 부정적이지만 신뢰 가능 (negative, trustworthy) ─────
            ['user_name'   => '솔직', 'type' => 1, 'rate' => 2.0, 'like' => 9, 'complain' => 0,
                'subject'  => '기대보다 별로였던 솔직 후기',
                'contents' => '실밥 풀고 나서 양쪽 눈 짝짝이가 좀 보였어요. 원장님은 붓기 빠지면 괜찮아진다고 했는데 두 달째 미세하게 비대칭입니다. 상담 때 들었던 것보다 다운타임도 길었어요. 재수술 상담 예정입니다.'],
            ['user_name'   => '현실', 'type' => 2, 'rate' => 2.5, 'like' => 4, 'complain' => 0,
                'subject'  => '필러 만족도 보통이에요',
                'contents' => '팔자주름 필러 맞았는데 처음엔 좋았다가 두 달 지나니 거의 흡수됐어요. 지속력이 생각보다 짧네요. 시술은 아팠지만 견딜 만했습니다. 가성비는 글쎄요, 사람마다 다를 듯해요.'],

            // ── 과장+광고 혼합 (복합 플래그) ───────────────────────
            ['user_name'   => 'VIP고객', 'type' => 1, 'rate' => 5.0, 'like' => 0, 'complain' => 1,
                'subject'  => '인생 역전! 지금 예약하면 할인까지',
                'contents' => '인생 역전했어요!!! 100% 완벽 부작용 제로!! 게다가 지금 예약하면 30% 할인에 사은품까지!! 전화 010-9999-8888 안 하면 진짜 손해예요 무조건 강추 기적의 병원!!!'],
        ];
    }
}
