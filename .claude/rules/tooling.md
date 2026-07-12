# 언어 서버 (LSP) 설정

## PHP 언어 서버 (Intelephense LSP)

Claude Code 가 PHP 코드를 심볼 단위(정의 이동·참조 찾기·자동완성)로 정확히 다루도록 **Intelephense LSP** 를 연동한다. PHPStan 이 "타입 오류 검사"라면 Intelephense 는 "코드 구조 이해" 역할로 상호 보완한다.

> 이 연동은 **Claude Code CLI 세션 전용**이다. VS Code·JetBrains 확장에서 쓰는 Intelephense 와는 별개 인스턴스이므로 에디터에는 에디터대로 따로 설치한다.

### 설치 (최초 1회)

```bash
# 1. 바이너리 설치 (Node.js + npm 필요)
npm install -g intelephense

# 2. 로컬 LSP 플러그인 생성 (~/.claude/skills/ 하위 → 전 프로젝트 공용)
mkdir -p ~/.claude/skills/php-lsp-intelephense/.claude-plugin

cat > ~/.claude/skills/php-lsp-intelephense/.claude-plugin/plugin.json << 'EOF'
{
  "name": "php-lsp-intelephense",
  "description": "Intelephense PHP 언어 서버",
  "version": "1.0.0"
}
EOF

cat > ~/.claude/skills/php-lsp-intelephense/.lsp.json << 'EOF'
{
  "php": {
    "command": "intelephense",
    "args": ["--stdio"],
    "extensionToLanguage": { ".php": "php" }
  }
}
EOF
```

> ⚠️ 공식 `php-lsp@claude-plugins-official` 플러그인은 `.lsp.json` 이 누락되어 동작하지 않는다([이슈 #444](https://github.com/anthropics/claude-plugins-official/issues/444)). 위처럼 로컬 플러그인을 직접 만든다.

### 활성화·확인

- **활성화**: 새 Claude Code 세션을 시작하거나, 대화형 세션에서 `/reload-plugins` 실행 (플러그인은 세션 시작 시 로드된다)
- **확인**: `/help` 의 "Installed plugins" 에 `php-lsp-intelephense` 표시
- **동작 점검**: `intelephense --version` 은 플래그 미지원으로 에러를 뱉으니 정상 판정 근거로 쓰지 말 것. 실제 기동은 `--stdio` 모드의 `initialize` 응답으로 확인한다.

### 사용

개발자가 직접 실행하는 명령이 아니라, Claude 가 PHP 코드를 다룰 때 뒤에서 참조한다. "이 메서드 쓰는 곳 전부 찾아줘", "정의로 가줘" 같은 요청을 텍스트 grep 대신 심볼 단위로 정확히 처리한다.

- **무료 범위**: 정의 이동·참조 찾기·자동완성·심볼 검색 (충분)
- **프리미엄($25/년)**: 워크스페이스 전역 rename·고급 리팩토링

## Dart/Flutter LSP (`app-mobile/`)

`app-mobile/` Flutter 코드용 LSP. Dart SDK 에 언어 서버가 내장되어 **별도 설치가 없다**(플러그인 파일만 만들면 된다). PHP 쪽과 동일한 방식이며, 정의 이동·참조 찾기·call hierarchy·code action 을 제공한다.

```bash
mkdir -p ~/.claude/skills/dart-lsp/.claude-plugin

cat > ~/.claude/skills/dart-lsp/.claude-plugin/plugin.json << 'EOF'
{
  "name": "dart-lsp",
  "description": "Dart/Flutter 언어 서버 (analysis server)",
  "version": "1.0.0"
}
EOF

cat > ~/.claude/skills/dart-lsp/.lsp.json << 'EOF'
{
  "dart": {
    "command": "dart",
    "args": ["language-server", "--protocol=lsp"],
    "extensionToLanguage": { ".dart": "dart" }
  }
}
EOF
```

- **활성화·확인**: PHP LSP 와 동일 (`/reload-plugins` → `/help` 에 `dart-lsp` 표시)
- **동작 점검**: `--protocol=lsp` 모드의 `initialize` 응답으로 확인
