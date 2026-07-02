<?php

namespace App\Commands;

use Throwable;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OpenApi\Generator;

class SwaggerGenerate extends BaseCommand
{
    protected $group       = 'AICura';
    protected $name        = 'swagger:generate';
    protected $description = 'OpenAPI 스펙 JSON을 생성합니다. (public/swagger.json)';
    protected $usage       = 'swagger:generate [options]';
    protected $options     = [
        '--output' => '출력 경로 (기본값: public/swagger.json)',
    ];

    public function run(array $params): void
    {
        $output = $params['output'] ?? $params[0] ?? FCPATH . 'swagger.json';

        CLI::write('OpenAPI 스펙 생성 중...', 'yellow');

        try {
            $openapi = new Generator()->generate([APPPATH . 'Controllers/Api']);
            $json    = $openapi->toJson();

            file_put_contents($output, $json);

            CLI::write("완료: {$output}", 'green');
            CLI::write('엔드포인트 수: ' . count($openapi->paths ?? []), 'cyan');
        } catch (Throwable $e) {
            CLI::error('생성 실패: ' . $e->getMessage());
        }
    }
}
