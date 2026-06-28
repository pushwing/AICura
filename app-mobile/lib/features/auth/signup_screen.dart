import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import 'auth_provider.dart';

/// 회원가입 화면 (이메일/비밀번호 + 선택 프로필).
///
/// 서버 규칙: email(형식), password(8~72자) 필수.
class SignupScreen extends StatefulWidget {
  const SignupScreen({super.key});

  @override
  State<SignupScreen> createState() => _SignupScreenState();
}

class _SignupScreenState extends State<SignupScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _passwordConfirm = TextEditingController();
  final _username = TextEditingController();
  final _phone = TextEditingController();

  String? _emailServerError;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    _passwordConfirm.dispose();
    _username.dispose();
    _phone.dispose();
    super.dispose();
  }

  /// 이메일 중복 확인 — 포커스가 빠질 때 서버에 질의한다.
  Future<void> _checkEmail() async {
    final email = _email.text.trim();
    if (!email.contains('@')) return;
    try {
      final ok = await context.read<AuthProvider>().checkEmail(email);
      setState(() => _emailServerError = ok ? null : '이미 사용 중인 이메일입니다');
    } on ApiException {
      // 중복확인 실패는 가입 제출 시 서버 검증으로 재확인되므로 무시
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_emailServerError != null) return;

    final auth = context.read<AuthProvider>();
    try {
      await auth.register(
        email: _email.text.trim(),
        password: _password.text,
        username: _username.text.trim(),
        phone: _phone.text.trim(),
      );
      // 성공 시 토큰이 저장되고 AuthGate 가 홈으로 전환한다.
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final busy = context.watch<AuthProvider>().busy;
    return Scaffold(
      appBar: AppBar(title: const Text('회원가입')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                TextFormField(
                  controller: _email,
                  keyboardType: TextInputType.emailAddress,
                  decoration: InputDecoration(
                    labelText: '이메일 *',
                    errorText: _emailServerError,
                  ),
                  onEditingComplete: _checkEmail,
                  validator: (v) => (v == null || !v.contains('@'))
                      ? '올바른 이메일을 입력해주세요'
                      : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _password,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: '비밀번호 * (8자 이상)',
                  ),
                  validator: (v) => (v == null || v.length < 8)
                      ? '비밀번호는 8자 이상이어야 합니다'
                      : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _passwordConfirm,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: '비밀번호 확인 *'),
                  validator: (v) =>
                      v != _password.text ? '비밀번호가 일치하지 않습니다' : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _username,
                  decoration: const InputDecoration(labelText: '닉네임 (선택)'),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _phone,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(labelText: '휴대폰 번호 (선택)'),
                ),
                const SizedBox(height: 24),
                FilledButton(
                  onPressed: busy ? null : _submit,
                  child: busy
                      ? const SizedBox(
                          height: 22,
                          width: 22,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('가입하고 시작하기'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
