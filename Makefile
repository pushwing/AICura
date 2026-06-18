.PHONY: sync-assets serve migrate analyse check

## ui/aicura.css → public/assets/css/ 동기화
## ui/aicura.css 수정 후 반드시 실행하세요.
sync-assets:
	cp ui/aicura.css public/assets/css/aicura.css

serve:
	frankenphp run --config Caddyfile

migrate:
	php spark migrate

analyse:
	composer analyse

check:
	composer check
