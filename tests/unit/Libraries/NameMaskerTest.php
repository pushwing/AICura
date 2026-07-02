<?php

use App\Libraries\Seo\NameMasker;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * 작성자명 마스킹 단위 테스트 (이슈 #144)
 *
 * @internal
 */
final class NameMaskerTest extends CIUnitTestCase
{
    /**
     * @dataProvider maskCases
     */
    public function testMask(string $input, string $expected): void
    {
        $this->assertSame($expected, NameMasker::mask($input));
    }

    /** @return array<string, array{string, string}> */
    public static function maskCases(): array
    {
        return [
            '빈 문자열 → 익명'   => ['', '익명'],
            '공백만 → 익명'      => ['   ', '익명'],
            '1자 → 그대로'       => ['김', '김'],
            '2자 → 첫+별'        => ['홍길', '홍*'],
            '3자 → 첫+별+끝'     => ['홍길동', '홍*동'],
            '4자'                => ['남궁민수', '남**수'],
            '영문 닉네임'        => ['alice', 'a***e'],
        ];
    }

    /** 마스킹 결과에는 원본 가운데 글자가 남지 않는다 */
    public function testMiddleHidden(): void
    {
        $this->assertStringNotContainsString('길', NameMasker::mask('홍길동'));
    }
}
