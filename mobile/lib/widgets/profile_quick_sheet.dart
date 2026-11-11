import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../screens/edit_profile_screen.dart';
import '../screens/login_screen.dart';
import '../screens/orders_screen.dart';
import '../screens/register_screen.dart';
import '../screens/user_chat_list_screen.dart';
import 'server_settings_dialog.dart';

class ProfileQuickSheet extends StatelessWidget {
  const ProfileQuickSheet({super.key});

  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  static Future<void> show(BuildContext context) {
    return showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const ProfileQuickSheet(),
    );
  }

  Future<void> _handleLogout(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(18),
          side: const BorderSide(color: _borderLight),
        ),
        title: const Text(
          'Keluar dari Akun?',
          style: TextStyle(color: _textDark, fontWeight: FontWeight.w800),
        ),
        content: const Text(
          'Anda harus login kembali untuk melihat riwayat pesanan dan melakukan checkout.',
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
            ),
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Ya, Keluar', style: TextStyle(fontWeight: FontWeight.w800)),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      Navigator.of(context).pop();
      context.read<CartProvider>().clear();
      await context.read<AuthProvider>().logout();
    }
  }

  void _showHelpdesk(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
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
                    'Layanan Helpdesk & Engineer',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: _textDark),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              const Text(
                'Butuh bantuan perakitan hardware, tracking resi pengiriman, atau instalasi lisensi? Tim kami siap melayani.',
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
                        Text('support@digitalsolusicerdas.id',
                            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _textDark)),
                      ],
                    ),
                    SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.phone_outlined, size: 18, color: _primaryBlue),
                        SizedBox(width: 10),
                        Text('+62 812-3456-7890 (WhatsApp)',
                            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _textDark)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final userName = user?.name ?? 'Pengguna Tamu';
    final initial = userName.isNotEmpty ? userName[0].toUpperCase() : 'G';

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 44,
              height: 4,
              decoration: BoxDecoration(
                color: const Color(0xFFD1D5DB),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 18),

            // User Card Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1B64F2), Color(0xFF5B9DFA)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(18),
                boxShadow: [
                  BoxShadow(
                    color: _primaryBlue.withValues(alpha: 0.22),
                    blurRadius: 14,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                      color: Colors.white,
                    ),
                    child: Center(
                      child: Text(
                        initial,
                        style: const TextStyle(
                          color: _primaryBlue,
                          fontWeight: FontWeight.w900,
                          fontSize: 20,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                userName,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.circle, size: 6, color: Color(0xFF4ADE80)),
                                  SizedBox(width: 4),
                                  Text(
                                    'Aktif',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          user?.email ?? 'Silakan masuk untuk fitur lengkap',
                          style: TextStyle(fontSize: 12, color: Colors.white.withValues(alpha: 0.9)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        if (user?.phone != null && user!.phone!.isNotEmpty) ...[
                          const SizedBox(height: 2),
                          Text(
                            user.phone!,
                            style: TextStyle(fontSize: 11, color: Colors.white.withValues(alpha: 0.8)),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Menu Items
            if (auth.isAuthenticated) ...[
              _buildMenuItem(
                context,
                icon: Icons.badge_outlined,
                iconColor: _primaryBlue,
                title: 'Ubah Data Profil & Kontak',
                subtitle: 'Perbarui nama, telepon, dan alamat kirim',
                onTap: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const EditProfileScreen()),
                  );
                },
              ),
              const SizedBox(height: 8),
              _buildMenuItem(
                context,
                icon: Icons.receipt_long_rounded,
                iconColor: const Color(0xFF0284C7),
                title: 'Riwayat Pesanan & Resi Pengiriman',
                subtitle: 'Cek invoice, pembayaran, dan lacak kurir',
                onTap: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const OrdersScreen()),
                  );
                },
              ),
              const SizedBox(height: 8),
              _buildMenuItem(
                context,
                icon: Icons.chat_bubble_outline_rounded,
                iconColor: _primaryBlue,
                title: 'Pusat Konsultasi & Diskusi Produk',
                subtitle: 'Riwayat tanya jawab spesifikasi & chat admin',
                onTap: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const UserChatListScreen()),
                  );
                },
              ),
              const SizedBox(height: 8),
              _buildMenuItem(
                context,
                icon: Icons.dns_outlined,
                iconColor: const Color(0xFFD97706),
                title: 'Status Server & Gateway API',
                subtitle: 'Uji latensi koneksi & konfigurasi host',
                onTap: () {
                  Navigator.of(context).pop();
                  ServerSettingsDialog.show(context);
                },
              ),
              const SizedBox(height: 8),
              _buildMenuItem(
                context,
                icon: Icons.headset_mic_outlined,
                iconColor: const Color(0xFF7C3AED),
                title: 'Layanan Bantuan IT Support',
                subtitle: 'Konsultasi hardware & teknisi jaringan',
                onTap: () {
                  Navigator.of(context).pop();
                  _showHelpdesk(context);
                },
              ),
              const SizedBox(height: 12),
              const Divider(height: 1, color: _borderLight),
              const SizedBox(height: 12),
              _buildMenuItem(
                context,
                icon: Icons.logout_rounded,
                iconColor: const Color(0xFFEF4444),
                title: 'Keluar dari Akun',
                subtitle: 'Selesaikan sesi pengguna di perangkat ini',
                isDestructive: true,
                onTap: () => _handleLogout(context),
              ),
            ] else ...[
              _buildMenuItem(
                context,
                icon: Icons.login_rounded,
                iconColor: _primaryBlue,
                title: 'Masuk ke Akun',
                subtitle: 'Akses keranjang, pesanan, dan checkout',
                onTap: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                },
              ),
              const SizedBox(height: 8),
              _buildMenuItem(
                context,
                icon: Icons.person_add_alt_1_rounded,
                iconColor: const Color(0xFF0284C7),
                title: 'Daftar Akun Baru',
                subtitle: 'Bergabung untuk kemudahan pemesanan IT',
                onTap: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const RegisterScreen()),
                  );
                },
              ),
              const SizedBox(height: 8),
              _buildMenuItem(
                context,
                icon: Icons.dns_outlined,
                iconColor: const Color(0xFFD97706),
                title: 'Status Server & Gateway API',
                subtitle: 'Uji latensi koneksi & konfigurasi host',
                onTap: () {
                  Navigator.of(context).pop();
                  ServerSettingsDialog.show(context);
                },
              ),
            ],
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  Widget _buildMenuItem(
    BuildContext context, {
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    bool isDestructive = false,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: isDestructive ? const Color(0xFFFEF2F2) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDestructive ? const Color(0xFFFECACA) : _borderLight,
          width: 1,
        ),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: isDestructive
                        ? const Color(0xFFFEE2E2)
                        : iconColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, color: iconColor, size: 19),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: isDestructive ? const Color(0xFFDC2626) : _textDark,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: TextStyle(
                          fontSize: 11,
                          color: isDestructive ? const Color(0xFFEF4444) : _textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Icon(
                  Icons.chevron_right_rounded,
                  color: isDestructive ? const Color(0xFFEF4444) : const Color(0xFF9CA3AF),
                  size: 20,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
