import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import 'hospital_detail_screen.dart';
import 'hospital_repository.dart';
import 'models/hospital.dart';

/// 병원 목록 화면 (비로그인 열람).
class HospitalListScreen extends StatefulWidget {
  const HospitalListScreen({super.key});

  @override
  State<HospitalListScreen> createState() => _HospitalListScreenState();
}

class _HospitalListScreenState extends State<HospitalListScreen> {
  List<Hospital> _items = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final page = await context.read<HospitalRepository>().list();
      if (!mounted) return;
      setState(() {
        _items = page.items;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('병원')),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: _load, child: const Text('다시 시도')),
          ],
        ),
      );
    }
    if (_items.isEmpty) {
      return const Center(child: Text('표시할 병원이 없습니다'));
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        itemCount: _items.length,
        separatorBuilder: (_, __) =>
            const Divider(height: 1, indent: 16, endIndent: 16),
        itemBuilder: (context, i) => _HospitalTile(hospital: _items[i]),
      ),
    );
  }
}

class _HospitalTile extends StatelessWidget {
  const _HospitalTile({required this.hospital});

  final Hospital hospital;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      leading: const CircleAvatar(
        backgroundColor: Color(0xFFEAF5F0),
        child: Icon(Icons.local_hospital_outlined,
            color: Color(0xFF0F6E56),),
      ),
      title: Text(hospital.name,
          maxLines: 1, overflow: TextOverflow.ellipsis,),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (hospital.typeLabel != null)
            Text(hospital.typeLabel!,
                style: const TextStyle(fontSize: 12, color: Colors.black54),),
          if (hospital.address != null && hospital.address!.isNotEmpty)
            Text(hospital.address!,
                maxLines: 1, overflow: TextOverflow.ellipsis,),
        ],
      ),
      trailing: hospital.rating > 0
          ? Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.star, size: 16, color: Colors.amber),
                const SizedBox(width: 2),
                Text(hospital.rating.toStringAsFixed(1)),
              ],
            )
          : null,
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => HospitalDetailScreen(hospitalId: hospital.id),
        ),
      ),
    );
  }
}
