import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import 'auth_provider.dart';
import 'signup_screen.dart';

/// 로그인이 필요한 동작 직전에 호출. 이미 로그인 상태면 즉시 true,
/// 아니면 로그인 화면을 띄우고 성공 여부를 반환한다.
Future<bool> requireLogin(BuildContext context) async {
  final auth = context.read<AuthProvider>();
  if (auth.isAuthenticated) return true;
  final result = await Navigator.of(context).push<bool>(
    MaterialPageRoute<bool>(builder: (_) => const LoginScreen()),
  );
  return result ?? auth.isAuthenticated;
}

/// 로그인 화면.
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    try {
      await auth.login(_email.text.trim(), _password.text);
      // 성공 시 이 화면을 닫고 호출부로 true 를 돌려준다.
      if (mounted) Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (mounted) _showError(e.message);
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  /// 회원가입 화면을 띄우고, 가입에 성공하면 로그인 화면도 함께 닫는다.
  Future<void> _openSignup() async {
    final ok = await Navigator.of(context).push<bool>(
      MaterialPageRoute<bool>(builder: (_) => const SignupScreen()),
    );
    if (ok == true && mounted) Navigator.of(context).pop(true);
  }

  @override
  Widget build(BuildContext context) {
    final busy = context.watch<AuthProvider>().busy;
    return Scaffold(
      // 비로그인 열람 중 띄워지므로 닫고 돌아갈 수 있어야 한다.
      appBar: AppBar(backgroundColor: Colors.transparent),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    '뷰니',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFFFB2D6F),
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    '나에게 맞는 이벤트를 찾아보세요',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.black54),
                  ),
                  const SizedBox(height: 40),
                  TextFormField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: '이메일'),
                    validator: (v) => (v == null || !v.contains('@'))
                        ? '올바른 이메일을 입력해주세요'
                        : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _password,
                    obscureText: true,
                    decoration: const InputDecoration(labelText: '비밀번호'),
                    validator: (v) =>
                        (v == null || v.length < 6) ? '비밀번호를 확인해주세요' : null,
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
                        : const Text('로그인'),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: busy ? null : _openSignup,
                    child: const Text('이메일로 회원가입'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
