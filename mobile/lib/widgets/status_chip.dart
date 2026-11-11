import 'package:flutter/material.dart';

class StatusChip extends StatelessWidget {
  const StatusChip({super.key, required this.status, required this.label});

  final String status;
  final String label;

  @override
  Widget build(BuildContext context) {
    final (Color background, Color foreground, Color border) = switch (status) {
      'pending' => (const Color(0xFFFFFBEB), const Color(0xFFB45309), const Color(0xFFFDE68A)),
      'paid' => (const Color(0xFFEFF6FF), const Color(0xFF1B64F2), const Color(0xFFBFDBFE)),
      'processing' => (const Color(0xFFEEF2FF), const Color(0xFF4338CA), const Color(0xFFC7D2FE)),
      'shipped' || 'delivered' => (const Color(0xFFE0F2FE), const Color(0xFF0369A1), const Color(0xFFBAE6FD)),
      'completed' => (const Color(0xFFECFDF5), const Color(0xFF047857), const Color(0xFFA7F3D0)),
      'cancelled' => (const Color(0xFFFEF2F2), const Color(0xFFDC2626), const Color(0xFFFECACA)),
      _ => (const Color(0xFFF1F5F9), const Color(0xFF475569), const Color(0xFFE2E8F0)),
    };

    final displayLabel = (status == 'paid' && (label.toLowerCase().contains('diterima') || label.isEmpty || label == 'paid'))
        ? 'Pembayaran Berhasil'
        : label;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: border),
      ),
      child: Text(
        displayLabel,
        style: TextStyle(color: foreground, fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }
}
