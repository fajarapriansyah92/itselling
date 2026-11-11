import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/app_config.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../widgets/server_settings_dialog.dart';
import 'edit_profile_screen.dart';
import 'user_chat_list_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  Future<void> _logout(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(18),
          side: const BorderSide(color: _borderLight),
        ),
        title: const Text('Keluar dari Akun?', style: TextStyle(color: _textDark, fontWeight: FontWeight.w800)),
        content: const Text(
          'Anda perlu login kembali untuk mengakses riwayat pesanan dan keranjang.',
          style: TextStyle(color: _textMuted, fontSize: 13.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Batal', style: TextStyle(color: _textMuted, fontWeight: FontWeight.w700)),
          ),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Keluar', style: TextStyle(fontWeight: FontWeight.w800)),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      context.read<CartProvider>().clear();
      await context.read<AuthProvider>().logout();
    }
  }

  void _showHelpDialog(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(24),
        child: SafeArea(
          top: false,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: const Icon(Icons.headset_mic_rounded, color: _primaryBlue, size: 24),
                  ),
                  const SizedBox(width: 14),
                  const Text(
                    'Layanan Pelanggan & Helpdesk',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: _textDark),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              const Text(
                'Butuh bantuan terkait instalasi hardware, konfigurasi jaringan/firewall, atau kendala transaksi? Tim engineer IT kami siap melayani.',
                style: TextStyle(fontSize: 13, height: 1.5, color: _textMuted),
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: _borderLight),
                ),
                child: const Column(
                  children: [
                    Row(
                      children: [
                        Icon(Icons.email_outlined, size: 18, color: _primaryBlue),
                        SizedBox(width: 10),
                        Text('support@digitalsolusicerdas.id', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _textDark)),
                      ],
                    ),
                    SizedBox(height: 10),
                    Row(
                      children: [
                        Icon(Icons.access_time_rounded, size: 18, color: _primaryBlue),
                        SizedBox(width: 10),
                        Text('Senin - Sabtu: 08.00 - 18.00 WIB', style: TextStyle(fontSize: 13, color: _textMuted)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: () {
                  Navigator.of(context).pop();
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      behavior: SnackBarBehavior.floating,
                      backgroundColor: Colors.white,
                      elevation: 6,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                        side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
                      ),
                      content: const Text(
                        'Menghubungkan ke WhatsApp Helpdesk IT...',
                        style: TextStyle(color: _textDark, fontWeight: FontWeight.w700),
                      ),
                    ),
                  );
                },
                style: FilledButton.styleFrom(
                  minimumSize: const Size.fromHeight(48),
                  backgroundColor: _primaryBlue,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: const Icon(Icons.chat_bubble_rounded, size: 18),
                label: const Text('Hubungi via WhatsApp', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    if (user == null) {
      return const SizedBox.shrink();
    }

    return Scaffold(
      backgroundColor: _bgPage,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: _textDark,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: _primaryBlue.withValues(alpha: 0.08),
        title: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Icon(Icons.person_rounded, color: _primaryBlue, size: 18),
            ),
            const SizedBox(width: 10),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Akun & Profil',
                  style: TextStyle(color: _textDark, fontSize: 16, fontWeight: FontWeight.w800),
                ),
                Text(
                  'Kelola informasi akun & pengaturan',
                  style: TextStyle(color: _textMuted, fontSize: 11, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ],
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
        children: [
          // Enterprise Profile Header (Royal Blue Gradient matching Website & Catalog)
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1B64F2), Color(0xFF5B9DFA)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: _primaryBlue.withValues(alpha: 0.28),
                  blurRadius: 18,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Row(
              children: [
                Stack(
                  alignment: Alignment.center,
                  children: [
                    Container(
                      width: 62,
                      height: 62,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 2.2),
                        color: Colors.white,
                      ),
                      child: Center(
                        child: Text(
                          user.name.isEmpty ? '?' : user.name[0].toUpperCase(),
                          style: const TextStyle(color: _primaryBlue, fontSize: 24, fontWeight: FontWeight.w900),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: Container(
                        width: 15,
                        height: 15,
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981),
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user.name,
                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        user.email,
                        style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 13),
                      ),
                      const SizedBox(height: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                        ),
                        child: const Text(
                          'ENTERPRISE CLIENT',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.6,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Client Contact & Address Details
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: _borderLight),
              boxShadow: [
                BoxShadow(
                  color: _primaryBlue.withValues(alpha: 0.05),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Padding(
                  padding: EdgeInsets.fromLTRB(16, 14, 16, 6),
                  child: Text(
                    'INFORMASI KONTAK & ALAMAT',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: _textMuted, letterSpacing: 0.6),
                  ),
                ),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.phone_rounded, color: _primaryBlue, size: 20),
                  ),
                  title: const Text('No. WhatsApp / Telepon', style: TextStyle(fontSize: 12, color: _textMuted)),
                  subtitle: Text(
                    user.phone ?? '-',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: _textDark),
                  ),
                ),
                const Divider(height: 1, indent: 64, color: _borderLight),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.location_on_rounded, color: _primaryBlue, size: 20),
                  ),
                  title: const Text('Alamat Kantor / Domisili', style: TextStyle(fontSize: 12, color: _textMuted)),
                  subtitle: Text(
                    user.address ?? '-',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: _textDark),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Action Menu Card
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: _borderLight),
              boxShadow: [
                BoxShadow(
                  color: _primaryBlue.withValues(alpha: 0.05),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.person_outline_rounded, color: _primaryBlue, size: 20),
                  ),
                  title: const Text('Ubah Profil Diri', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: _textDark)),
                  trailing: const Icon(Icons.chevron_right_rounded, color: Color(0xFF9CA3AF)),
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const EditProfileScreen())),
                ),
                const Divider(height: 1, indent: 64, color: _borderLight),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.chat_bubble_outline_rounded, color: _primaryBlue, size: 20),
                  ),
                  title: const Text('Pusat Konsultasi & Chat Produk', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: _textDark)),
                  subtitle: const Text('Riwayat diskusi spesifikasi teknis & tanya admin', style: TextStyle(fontSize: 12, color: _textMuted)),
                  trailing: const Icon(Icons.chevron_right_rounded, color: Color(0xFF9CA3AF)),
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const UserChatListScreen())),
                ),
                const Divider(height: 1, indent: 64, color: _borderLight),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.headset_mic_outlined, color: _primaryBlue, size: 20),
                  ),
                  title: const Text('Bantuan & Hubungi Kami', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: _textDark)),
                  trailing: const Icon(Icons.chevron_right_rounded, color: Color(0xFF9CA3AF)),
                  onTap: () => _showHelpDialog(context),
                ),
                const Divider(height: 1, indent: 64, color: _borderLight),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.dns_outlined, color: _primaryBlue, size: 20),
                  ),
                  title: const Text('Pengaturan Alamat Server', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: _textDark)),
                  subtitle: Text(ApiClient.instance.baseUrl, style: const TextStyle(fontSize: 12, color: _textMuted)),
                  trailing: const Icon(Icons.chevron_right_rounded, color: Color(0xFF9CA3AF)),
                  onTap: () => ServerSettingsDialog.show(context),
                ),
                const Divider(height: 1, indent: 64, color: _borderLight),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.logout_rounded, color: Color(0xFFEF4444), size: 20),
                  ),
                  title: const Text('Keluar dari Akun', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFFEF4444))),
                  onTap: () => _logout(context),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),
          Center(
            child: Text(
              '${AppConfig.appName} · Versi 1.2.0',
              style: const TextStyle(fontSize: 12, color: _textMuted, fontWeight: FontWeight.w500),
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
