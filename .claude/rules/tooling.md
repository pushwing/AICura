# 언어 서버 (LSP) 설정 — AICura 전용

> PHP 언어 서버(Intelephense) 설치·사용은 전역 [`~/.claude/rules/php-lsp.md`](~/.claude/rules/php-lsp.md) 참조. 이 문서는 AICura 의 Flutter 앱 전용 Dart LSP 만 정의한다.

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
