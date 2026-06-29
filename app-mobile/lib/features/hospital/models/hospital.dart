import '../../../core/util/json_parse.dart';

/// 병원 (목록/상세 공용).
class Hospital {
  Hospital({
    required this.id,
    required this.name,
    required this.type,
    this.typeLabel,
    this.phone,
    this.address,
    required this.rating,
    this.reviewCount = 0,
    this.isLiked = false,
  });

  final int id;
  final String name;
  final int type;
  final String? typeLabel;
  final String? phone;
  final String? address;
  final double rating;
  final int reviewCount;
  final bool isLiked;

  factory Hospital.fromJson(Map<String, dynamic> j) {
    // 상세는 review_summary{rating,count}, 목록은 rating 평탄 필드를 준다.
    final summary = j['review_summary'];
    double rating;
    int reviewCount = 0;
    if (summary is Map) {
      rating = _toDouble(summary['rating']);
      reviewCount = parseInt(summary['count']);
    } else {
      rating = _toDouble(j['rating']);
    }
    return Hospital(
      id: parseInt(j['id']),
      name: (j['name'] ?? '').toString(),
      type: parseInt(j['type']),
      typeLabel: j['type_label'] as String?,
      phone: j['phone'] as String?,
      address: j['address'] as String?,
      rating: rating,
      reviewCount: reviewCount,
      isLiked: (j['is_liked'] as bool?) ?? false,
    );
  }

  Hospital copyWith({bool? isLiked}) {
    return Hospital(
      id: id,
      name: name,
      type: type,
      typeLabel: typeLabel,
      phone: phone,
      address: address,
      rating: rating,
      reviewCount: reviewCount,
      isLiked: isLiked ?? this.isLiked,
    );
  }

  static double _toDouble(dynamic v) {
    if (v is num) return v.toDouble();
    if (v is String) return double.tryParse(v) ?? 0;
    return 0;
  }
}
