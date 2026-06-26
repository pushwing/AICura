<?php
define('DEFAULT_SEPERATOR', '|');

/**
 * 설문용 클래스
 *
 * $survey = new Survey(2);
 * $survey->initFromString('2|1|3');
 * $survey->vote(1);
 * $survey->vote(1);
 * $survey->vote(2);
 * echo $survey."\n";
 * $survey = new Survey(2);
 * $survey->initFromString('2|1|3');
 * echo $survey->vote(1)->vote(1)->vote(2)."\n";
 */
class Survey {
    private $itemCount = 0;
    private $seperator = DEFAULT_SEPERATOR;
    private $votes;

    public function __construct(int $itemCount = 2, string $seperator = DEFAULT_SEPERATOR) {
        assert($itemCount >= 2, '설문항목은 2개 이상이어야합니다.');

        $this->itemCount = $itemCount;
        $this->seperator = $seperator[0];

        $this->votes = [];

        for($i=0; $i < $itemCount; $i++) {
            $this->votes[$i] = 0;
        }
    }

    /**
     * 설문항목 초기화
     */
    public function initFromString(string $data, string $seperator = DEFAULT_SEPERATOR) {
        $this->seperator = $seperator;
        $this->votes = explode($seperator, $data);
        return $this;
    }

    /**
     * 지정한 항목에 투표
     * 1번 인덱스부터 시작
     * @param $itemIndex
     */
    public function vote(int $itemIndex = 1) {
        assert($itemIndex >= 1, '투표할 인덱스는 1 이상이어야합니다.');
        assert($itemIndex <= count($this->votes), '투표항목이 인덱스보다 적습니다.');

        $this->votes[$itemIndex-1]++;

        return $this;
    }

    public function against(int $itemIndex = 1) {
        assert($itemIndex >= 1, '투표할 인덱스는 1 이상이어야합니다.');
        assert($itemIndex <= count($this->votes), '투표항목이 인덱스보다 적습니다.');

        $this->votes[$itemIndex-1]--;

        return $this;

    }

    public function __toString():string {
        return join($this->seperator, $this->votes);
    }
}