import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/app_config.dart';
import '../providers/auth_provider.dart';
import '../widgets/server_settings_dialog.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isSubmitting = false;
  bool _obscurePassword = true;
  bool _rememberSession = true;
  String? _error;

  late final AnimationController _animController;
  late final Animation<double> _fadeAnim;
  late final Animation<Offset> _slideAnim;

  // Website Login Palette (resources/views/auth/login.blade.php)
  static const Color _heroBlue = Color(0xFF6AAFFA);
  static const Color _heroBlueDark = Color(0xFF5B9DFA);
  static const Color _brandBlue = Color(0xFF3B82F6);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _placeholder = Color(0xFFB0B7C3);
  static const Color _borderLight = Color(0xFFE5E7EB);
  static const Color _focusBlue = Color(0xFF60A5FA);

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(vsync: this, duration: const Duration(milliseconds: 700));
    _fadeAnim = CurvedAnimation(parent: _animController, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(begin: const Offset(0, 0.06), end: Offset.zero)
        .animate(CurvedAnimation(parent: _animController, curve: Curves.easeOut));
    _animController.forward();
  }

  @override
  void dispose() {
    _animController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSubmitting = true;
      _error = null;
    });

    try {
      await context.read<AuthProvider>().login(
            email: _emailController.text.trim(),
            password: _passwordController.text,
          );
    } on ApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  InputDecoration _webInputDecoration({
    required String hintText,
    required IconData prefixIcon,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: const TextStyle(color: _placeholder, fontSize: 14, fontWeight: FontWeight.w400),
      filled: true,
      fillColor: Colors.white,
      isDense: true,
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
      prefixIcon: Icon(prefixIcon, size: 19, color: const Color(0xFF9CA3AF)),
      suffixIcon: suffixIcon,
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: _borderLight, width: 1),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: _focusBlue, width: 1.6),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: Color(0xFFF87171), width: 1),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.6),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _heroBlue,
      body: Stack(
        children: [
          // Top Website Hero Background (#6AAFFA) & Geometric Illustrations
          Positioned.fill(
            child: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [_heroBlue, _heroBlueDark],
                ),
              ),
            ),
          ),
          // Decorative geometric shapes matching website SVG art
          Positioned(
            top: -35,
            right: -25,
            child: Container(
              width: 150,
              height: 150,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFF9CC8FB).withValues(alpha: 0.45),
              ),
            ),
          ),
          Positioned(
            top: 56,
            right: 78,
            child: Container(
              width: 24,
              height: 24,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                color: Color(0xFFF4B6C2), // Pastel Pink circle from website SVG
              ),
            ),
          ),
          Positioned(
            top: 125,
            right: 28,
            child: Container(
              width: 34,
              height: 34,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                color: Color(0xFFB8A4F5), // Pastel Lavender circle from website SVG
              ),
            ),
          ),
          Positioned(
            top: 90,
            right: 125,
            child: Transform.rotate(
              angle: -0.2,
              child: Container(
                width: 54,
                height: 12,
                decoration: BoxDecoration(
                  color: const Color(0xFFE8F1FF).withValues(alpha: 0.85),
                  borderRadius: BorderRadius.circular(6),
                ),
              ),
            ),
          ),

          // Scrollable Content
          SafeArea(
            bottom: false,
            child: CustomScrollView(
              slivers: [
                // Top Bar: Brand Identity (left) + Server Settings (right)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(24, 12, 16, 4),
                    child: FadeTransition(
                      opacity: _fadeAnim,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Website Brand Logo + Name
                          Row(
                            children: [
                              Container(
                                width: 38,
                                height: 38,
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF2563EB).withValues(alpha: 0.2),
                                      blurRadius: 12,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: const Center(
                                  child: Text(
                                    'DSC',
                                    style: TextStyle(
                                      color: _brandBlue,
                                      fontSize: 12,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: -0.5,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Text(
                                AppConfig.appName,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: -0.2,
                                ),
                              ),
                            ],
                          ),
                          // Server Settings Button
                          Container(
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.18),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: IconButton(
                              tooltip: 'Alamat server & diagnostik API',
                              icon: const Icon(Icons.dns_outlined, color: Colors.white, size: 20),
                              onPressed: () => ServerSettingsDialog.show(context),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

                // Hero Tagline & Mini Illustration Card (Matches Left Panel of Website)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(24, 12, 24, 22),
                    child: FadeTransition(
                      opacity: _fadeAnim,
                      child: SlideTransition(
                        position: _slideAnim,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 300),
                              child: const Text(
                                'Pusat operasional hardware, lisensi software, dan layanan IT terintegrasi.',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 13.5,
                                  height: 1.5,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                            // Website Hero Badge / Support Pill
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.18),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: Colors.white.withValues(alpha: 0.28)),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.verified_user_outlined, color: Colors.white, size: 15),
                                  SizedBox(width: 7),
                                  Text(
                                    'Enterprise IT Hardware, Software & Managed Services',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),

                // White Form Card (Matches Right Panel of Website)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: FadeTransition(
                    opacity: _fadeAnim,
                    child: Container(
                      padding: const EdgeInsets.fromLTRB(26, 28, 26, 28),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF2563EB).withValues(alpha: 0.25),
                            blurRadius: 36,
                            offset: const Offset(0, -8),
                          ),
                        ],
                      ),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Title & Subtitle matching website (.form-title & .form-subtitle)
                            const Text(
                              'Masuk',
                              style: TextStyle(
                                fontSize: 30,
                                fontWeight: FontWeight.w800,
                                color: _textDark,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Akses katalog produk IT, lisensi software, pesanan, dan layanan pelanggan Anda.',
                              style: TextStyle(
                                fontSize: 13,
                                color: _textMuted,
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 22),

                            // Website-style light red alert (.alert)
                            if (_error != null) ...[
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFEF2F2),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: const Color(0xFFFECACA)),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Icon(Icons.error_outline_rounded, color: Color(0xFFB91C1C), size: 18),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Text(
                                            'Gagal mengakses akun',
                                            style: TextStyle(
                                              color: Color(0xFFB91C1C),
                                              fontWeight: FontWeight.w700,
                                              fontSize: 12.5,
                                            ),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            _error!,
                                            style: const TextStyle(
                                              color: Color(0xFFB91C1C),
                                              fontSize: 12,
                                              height: 1.4,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 18),
                            ],

                            // Email Field
                            _buildLabel('Alamat Email'),
                            const SizedBox(height: 7),
                            TextFormField(
                              controller: _emailController,
                              keyboardType: TextInputType.emailAddress,
                              autofillHints: const [AutofillHints.email],
                              style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                              cursorColor: _brandBlue,
                              decoration: _webInputDecoration(
                                hintText: 'nama@itselling.test',
                                prefixIcon: Icons.mail_outline_rounded,
                              ),
                              validator: (v) => (v == null || !v.contains('@')) ? 'Masukkan alamat email yang valid.' : null,
                            ),
                            const SizedBox(height: 16),

                            // Password Field
                            _buildLabel('Kata Sandi'),
                            const SizedBox(height: 7),
                            TextFormField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              autofillHints: const [AutofillHints.password],
                              style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                              cursorColor: _brandBlue,
                              decoration: _webInputDecoration(
                                hintText: '•••• •••• ••••',
                                prefixIcon: Icons.lock_outline_rounded,
                                suffixIcon: IconButton(
                                  tooltip: 'Lihat kata sandi',
                                  icon: Icon(
                                    _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                    size: 18,
                                    color: const Color(0xFF9CA3AF),
                                  ),
                                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                                ),
                              ),
                              validator: (v) => (v == null || v.isEmpty) ? 'Kata sandi wajib diisi.' : null,
                              onFieldSubmitted: (_) => _submit(),
                            ),
                            const SizedBox(height: 12),

                            // Remember Session Checkbox (.remember on website)
                            InkWell(
                              borderRadius: BorderRadius.circular(6),
                              onTap: () => setState(() => _rememberSession = !_rememberSession),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(vertical: 4),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    SizedBox(
                                      width: 18,
                                      height: 18,
                                      child: Checkbox(
                                        value: _rememberSession,
                                        activeColor: _brandBlue,
                                        checkColor: Colors.white,
                                        side: const BorderSide(color: Color(0xFFD1D5DB), width: 1.4),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                        onChanged: (v) => setState(() => _rememberSession = v ?? true),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    const Text(
                                      'Ingat sesi di perangkat ini',
                                      style: TextStyle(fontSize: 12.5, color: _textMuted, fontWeight: FontWeight.w500),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Primary Submit Button (.btn-primary #5B9DFA)
                            FilledButton(
                              onPressed: _isSubmitting ? null : _submit,
                              style: FilledButton.styleFrom(
                                minimumSize: const Size.fromHeight(46),
                                backgroundColor: _heroBlueDark,
                                disabledBackgroundColor: _heroBlueDark.withValues(alpha: 0.6),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              ),
                              child: _isSubmitting
                                  ? const SizedBox.square(
                                      dimension: 20,
                                      child: CircularProgressIndicator(strokeWidth: 2.3, color: Colors.white),
                                    )
                                  : const Text(
                                      'Masuk Sekarang',
                                      style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
                                    ),
                            ),
                            const SizedBox(height: 18),

                            // Register Link
                            Center(
                              child: TextButton(
                                onPressed: () => Navigator.of(context).push(
                                  MaterialPageRoute(builder: (_) => const RegisterScreen()),
                                ),
                                style: TextButton.styleFrom(
                                  foregroundColor: _brandBlue,
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                ),
                                child: RichText(
                                  text: const TextSpan(
                                    text: 'Belum punya akun pelanggan? ',
                                    style: TextStyle(color: _textMuted, fontSize: 13),
                                    children: [
                                      TextSpan(
                                        text: 'Daftar sekarang',
                                        style: TextStyle(color: _brandBlue, fontWeight: FontWeight.w700),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),

                            const Spacer(),
                            const SizedBox(height: 12),

                            // Footer matching website (.form-footer & .hero-footer)
                            Center(
                              child: Text(
                                '© ${DateTime.now().year} ${AppConfig.appName} · Butuh bantuan? Hubungi Support IT',
                                textAlign: TextAlign.center,
                                style: const TextStyle(fontSize: 11.5, color: Color(0xFF9CA3AF)),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Text(
      text,
      style: const TextStyle(
        fontSize: 13,
        fontWeight: FontWeight.w600,
        color: _textDark,
      ),
    );
  }
}
