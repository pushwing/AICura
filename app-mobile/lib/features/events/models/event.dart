import '../../../core/util/json_parse.dart';

/// 이벤트(캠페인) 목록/상세 아이템.
///
/// 서버 transformListItem / transformDetailItem 응답에 대응한다.
class Event {
  Event({
    required this.id,
    required this.adTitle,
    required this.hospitalId,
    required this.hospitalName,
    required this.categoryId,
    required this.categoryTitle,
    required this.region,
    required this.adType,
    this.adTypeLabel,
    required this.costType,
    required this.generalCost,
    required this.discountCost,
    this.textCost,
    this.thumbnailUrl,
    this.adStartDate,
    this.adEndDate,
    this.isLiked = false,
    // 상세 전용
    this.subThumbnailUrl,
    this.detailImages = const [],
    this.adDetailInfo,
    this.hospitalAddress,
    this.hospitalPhone,
  });

  final int id;
  final String adTitle;
  final int hospitalId;
  final String hospitalName;
  final int categoryId;
  final String categoryTitle;
  final String region;
  final int adType;
  final String? adTypeLabel;
  final int costType;
  final int generalCost;
  final int discountCost;
  final String? textCost;
  final String? thumbnailUrl;
  final String? adStartDate;
  final String? adEndDate;
  final bool isLiked;

  final String? subThumbnailUrl;
  final List<String> detailImages;
  final String? adDetailInfo;
  final String? hospitalAddress;
  final String? hospitalPhone;

  /// 할인율(%) — general 대비 discount. 계산 불가 시 null.
  int? get discountRate {
    if (generalCost <= 0 || discountCost <= 0 || discountCost >= generalCost) {
      return null;
    }
    return (((generalCost - discountCost) / generalCost) * 100).round();
  }

  factory Event.fromJson(Map<String, dynamic> j) {
    int asInt(dynamic v) => parseInt(v);
    return Event(
      id: asInt(j['id']),
      adTitle: (j['ad_title'] as String?) ?? '',
      hospitalId: asInt(j['hospital_id']),
      hospitalName: (j['hospital_name'] as String?) ?? '',
      categoryId: asInt(j['category_id']),
      categoryTitle: (j['category_title'] as String?) ?? '',
      region: (j['region'] as String?) ?? '',
      adType: asInt(j['ad_type']),
      adTypeLabel: j['ad_type_label'] as String?,
      costType: asInt(j['cost_type']),
      generalCost: asInt(j['general_cost']),
      discountCost: asInt(j['discount_cost']),
      textCost: j['text_cost'] as String?,
      thumbnailUrl: j['thumbnail_url'] as String?,
      adStartDate: j['ad_start_date'] as String?,
      adEndDate: j['ad_end_date'] as String?,
      isLiked: (j['is_liked'] as bool?) ?? false,
      subThumbnailUrl: j['sub_thumbnail_url'] as String?,
      detailImages:
          (j['detail_images'] as List?)?.map((e) => e.toString()).toList() ??
              const [],
      adDetailInfo: j['ad_detail_info'] as String?,
      hospitalAddress: j['hospital_address'] as String?,
      hospitalPhone: j['hospital_phone'] as String?,
    );
  }

  /// 찜 상태만 바꾼 사본 (낙관적 업데이트용)
  Event copyWith({bool? isLiked}) {
    return Event(
      id: id,
      adTitle: adTitle,
      hospitalId: hospitalId,
      hospitalName: hospitalName,
      categoryId: categoryId,
      categoryTitle: categoryTitle,
      region: region,
      adType: adType,
      adTypeLabel: adTypeLabel,
      costType: costType,
      generalCost: generalCost,
      discountCost: discountCost,
      textCost: textCost,
      thumbnailUrl: thumbnailUrl,
      adStartDate: adStartDate,
      adEndDate: adEndDate,
      isLiked: isLiked ?? this.isLiked,
      subThumbnailUrl: subThumbnailUrl,
      detailImages: detailImages,
      adDetailInfo: adDetailInfo,
      hospitalAddress: hospitalAddress,
      hospitalPhone: hospitalPhone,
    );
  }
}
