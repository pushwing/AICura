import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../auth/auth_provider.dart';
import '../auth/login_screen.dart';
import '../booking/models/booking.dart';
import '../events/event_detail_screen.dart';
import '../hospital/hospital_detail_screen.dart';
import '../system/settings_provider.dart';
import 'models/call_request_item.dart';
import 'models/like_item.dart';
import 'my_page_provider.dart';

/// 마이페이지 — 프로필·내 찜·내 상담신청. 로그인 필요.
class MyPageScreen extends StatefulWidget {
  const MyPageScreen({super.key});

  @override
  State<MyPageScreen> createState() => _MyPageScreenState();
}

class _MyPageScreenState extends State<MyPageScreen> {
  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final provider = context.watch<MyPageProvider>();

    // 인증 상태와 데이터 로드 상태를 동기화 (build 중 setState 회피)
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (auth.isAuthenticated && !provider.loaded && !provider.loading) {
        provider.load();
      } else if (!auth.isAuthenticated && provider.loaded) {
        provider.clear();
      }
    });

    return Scaffold(
      appBar: AppBar(
        title: const Text('마이페이지'),
        actions: [
          if (auth.isAuthenticated)
            IconButton(
              tooltip: '로그아웃',
              onPressed: () async {
                await context.read<AuthProvider>().logout();
                if (context.mounted) context.read<MyPageProvider>().clear();
              },
              icon: const Icon(Icons.logout),
            ),
        ],
      ),
      body: auth.isAuthenticated
          ? _buildAuthed(provider)
          : const _LoginPrompt(),
    );
  }

  Widget _buildAuthed(MyPageProvider provider) {
    if (provider.loading && !provider.loaded) {
      return const Center(child: CircularProgressIndicator());
    }
    if (provider.error != null && !provider.loaded) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(provider.error!),
            const SizedBox(height: 12),
            OutlinedButton(
              onPressed: provider.load,
              child: const Text('다시 시도'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: provider.load,
      child: ListView(
        children: [
          _ProfileHeader(provider: provider),
          const SizedBox(height: 8),
          _SectionTitle('내 찜 ${provider.likes.length}'),
          if (provider.likes.isEmpty)
            const _EmptyHint('찜한 이벤트가 없습니다')
          else
            ...provider.likes.map((e) => _LikeTile(item: e)),
          const Divider(height: 24),
          _SectionTitle('내 상담신청 ${provider.callRequests.length}'),
          if (provider.callRequests.isEmpty)
            const _EmptyHint('상담 신청 내역이 없습니다')
          else
            ...provider.callRequests.map((e) => _CallRequestTile(item: e)),
          const Divider(height: 24),
          _SectionTitle('내 예약 ${provider.bookings.length}'),
          if (provider.bookings.isEmpty)
            const _EmptyHint('예약 내역이 없습니다')
          else
            ...provider.bookings.map((e) => _BookingTile(item: e)),
          const Divider(height: 24),
          const _AppInfoLinks(),
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

/// 약관·개인정보 등 앱 정보 링크 (설정에서 URL 로드).
class _AppInfoLinks extends StatelessWidget {
  const _AppInfoLinks();

  @override
  Widget build(BuildContext context) {
    final s = context.watch<SettingsProvider>().settings;
    return Column(
      children: [
        _LinkTile(label: '이용약관', url: s.termsUrl),
        _LinkTile(label: '개인정보 처리방침', url: s.privacyUrl),
      ],
    );
  }
}

class _LinkTile extends StatelessWidget {
  const _LinkTile({required this.label, required this.url});

  final String label;
  final String url;

  Future<void> _open(BuildContext context) async {
    if (url.isEmpty) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('준비 중입니다')));
      return;
    }
    final uri = Uri.tryParse(url);
    if (uri == null ||
        !await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (context.mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('링크를 열 수 없습니다')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(label),
      trailing: const Icon(Icons.open_in_new, size: 18),
      onTap: () => _open(context),
    );
  }
}

/// 비로그인 안내 + 로그인 버튼
class _LoginPrompt extends StatelessWidget {
  const _LoginPrompt();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.account_circle_outlined,
                size: 64, color: Colors.black26,),
            const SizedBox(height: 12),
            const Text('로그인하고 내 정보를 확인하세요',
                style: TextStyle(color: Colors.black54),),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: () => requireLogin(context),
              child: const Text('로그인'),
            ),
            const SizedBox(height: 24),
            const _AppInfoLinks(),
          ],
        ),
      ),
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  const _ProfileHeader({required this.provider});

  final MyPageProvider provider;

  @override
  Widget build(BuildContext context) {
    final p = provider.profile;
    if (p == null) return const SizedBox.shrink();
    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.all(20),
      child: Row(
        children: [
          CircleAvatar(
            radius: 28,
            backgroundColor: const Color(0xFF0F6E56),
            backgroundImage: (p.picture != null && p.picture!.isNotEmpty)
                ? NetworkImage(p.picture!)
                : null,
            child: (p.picture == null || p.picture!.isEmpty)
                ? Text(
                    p.username.isNotEmpty ? p.username.characters.first : '?',
                    style: const TextStyle(color: Colors.white, fontSize: 22),
                  )
                : null,
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  p.username.isNotEmpty ? p.username : '사용자',
                  style: const TextStyle(
                      fontSize: 18, fontWeight: FontWeight.bold,),
                ),
                const SizedBox(height: 2),
                Text(p.email,
                    style: const TextStyle(color: Colors.black54, fontSize: 13),),
                const SizedBox(height: 6),
                Text('헬스포인트 ${p.healthPoint}',
                    style: const TextStyle(
                        color: Color(0xFF1D9E75),
                        fontWeight: FontWeight.w600,),),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
      child: Text(text,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),),
    );
  }
}

class _EmptyHint extends StatelessWidget {
  const _EmptyHint(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Text(text, style: const TextStyle(color: Colors.black45)),
    );
  }
}

class _LikeTile extends StatelessWidget {
  const _LikeTile({required this.item});

  final LikeItem item;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: _thumb(),
      ),
      title: Text(item.title, maxLines: 1, overflow: TextOverflow.ellipsis),
      subtitle: Text(item.hospitalName),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => EventDetailScreen(eventId: item.id),
        ),
      ),
    );
  }

  Widget _thumb() {
    const size = 48.0;
    if (item.thumbnailUrl == null || item.thumbnailUrl!.isEmpty) {
      return Container(
        width: size,
        height: size,
        color: const Color(0xFFEDEFEF),
        child: const Icon(Icons.image_outlined, color: Colors.black26),
      );
    }
    return Image.network(
      item.thumbnailUrl!,
      width: size,
      height: size,
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(
        width: size,
        height: size,
        color: const Color(0xFFEDEFEF),
        child: const Icon(Icons.broken_image_outlined, color: Colors.black26),
      ),
    );
  }
}

class _BookingTile extends StatelessWidget {
  const _BookingTile({required this.item});

  final Booking item;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(item.hospitalName,
          maxLines: 1, overflow: TextOverflow.ellipsis,),
      subtitle: Text(item.bookDate ?? item.createdAt ?? ''),
      trailing: Chip(
        label: Text(item.label),
        backgroundColor: const Color(0xFFEAF5F0),
        labelStyle: const TextStyle(color: Color(0xFF0F6E56), fontSize: 12),
        visualDensity: VisualDensity.compact,
      ),
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => HospitalDetailScreen(hospitalId: item.hospitalId),
        ),
      ),
    );
  }
}

class _CallRequestTile extends StatelessWidget {
  const _CallRequestTile({required this.item});

  final CallRequestItem item;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(item.campaignTitle,
          maxLines: 1, overflow: TextOverflow.ellipsis,),
      subtitle: Text(item.createdAt ?? ''),
      trailing: Chip(
        label: Text(item.statusLabel),
        backgroundColor: const Color(0xFFEAF5F0),
        labelStyle: const TextStyle(color: Color(0xFF0F6E56), fontSize: 12),
        visualDensity: VisualDensity.compact,
      ),
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => EventDetailScreen(eventId: item.campaignId),
        ),
      ),
    );
  }
}
