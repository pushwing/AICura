import 'package:flutter/material.dart';

/// 뷰니(BEAUNI) 디자인 시스템 색상 토큰.
///
/// 모든 강조 요소(부위 배지·할인율·찜·탭 활성·기본 버튼·선택 칩·틴트 배경)는
/// [accent] 에서 파생한다. 핑크가 기본이며 블루로 시드만 바꾸면 전체 테마가 전환된다.
class AppColors {
  AppColors._();

  // --- Accent (단일 시드) ---
  static const Color accent = Color(0xFFFB2D6F); // 핑크(기본)
  static const Color accentBlue = Color(0xFF2F6BFF); // 블루(대안)

  /// 가격 카드·선택 칩 배경 (accent 12% over white)
  static Color get accentTint =>
      Color.alphaBlend(accent.withValues(alpha: 0.12), Colors.white);

  /// 시트 배경 (accent 8% over white)
  static Color get accentTintSoft =>
      Color.alphaBlend(accent.withValues(alpha: 0.08), Colors.white);

  // --- 중립 / 시맨틱 ---
  static const Color ink = Color(0xFF17171C); // 제목
  static const Color ink2 = Color(0xFF3A3A42); // 본문 진한
  static const Color ink3 = Color(0xFF6B6B76); // 본문
  static const Color muted = Color(0xFF8A8A94); // 병원·지역·캡션
  static const Color faint = Color(0xFFB8B8C0); // 원가 취소선·비활성
  static const Color surface = Color(0xFFFFFFFF); // 카드
  static const Color bg = Color(0xFFF5F6F8); // 마이페이지·구분
  static const Color band = Color(0xFFF4F5F7); // 카드 사이 두꺼운 구분
  static const Color field = Color(0xFFF4F5F7); // 검색·입력 bg
  static const Color line = Color(0xFFF1F1F4); // 구분선(약)
  static const Color lineStrong = Color(0xFFE6E7EB); // 입력 보더
  static const Color star = Color(0xFFFFB020); // 평점
  static const Color tabInactive = Color(0xFFB3B3BD);

  /// 부위별 썸네일 그라데이션 (140deg A→B). 실제 카테고리와 무관하게
  /// 인덱스로 순환 적용해 카드에 파스텔 변화를 준다.
  static const List<List<Color>> thumbGradients = [
    [Color(0xFFFFD9E1), Color(0xFFFFB3C6)], // 눈
    [Color(0xFFFCE3D0), Color(0xFFF6C6A0)], // 코
    [Color(0xFFE9DDFF), Color(0xFFCDB6FF)], // 안면윤곽
    [Color(0xFFFFE0EC), Color(0xFFFFBBD6)], // 가슴
    [Color(0xFFD5F0EA), Color(0xFFA9DED2)], // 지방흡입
    [Color(0xFFFFE6D6), Color(0xFFFFC4A0)], // 리프팅
    [Color(0xFFE0EEFF), Color(0xFFBBD6FF)], // 피부·쁘띠
  ];

  /// 인덱스(또는 id)로 그라데이션 선택
  static LinearGradient thumbGradient(int seed) {
    final pair = thumbGradients[seed.abs() % thumbGradients.length];
    return LinearGradient(
      begin: const Alignment(-0.7, -1),
      end: const Alignment(0.7, 1),
      colors: pair,
    );
  }

  /// 프로모 배너 그라데이션
  static LinearGradient get bannerGradient => LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [
          accent,
          Color.alphaBlend(accent.withValues(alpha: 0.5), Colors.white),
        ],
      );
}
