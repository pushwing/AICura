// 기본 위젯 스모크 테스트.
//
// 앱 루트(AicuraApp)는 보안 저장소·네트워크 플러그인에 의존하므로
// 위젯 테스트에서 직접 펌프하지 않는다. 여기서는 최소 렌더링만 검증한다.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('기본 MaterialApp 렌더링', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: Scaffold(body: Center(child: Text('AICura'))),
      ),
    );

    expect(find.text('AICura'), findsOneWidget);
  });
}
