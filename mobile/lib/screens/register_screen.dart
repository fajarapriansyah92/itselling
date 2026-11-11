import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/app_config.dart';
import '../providers/auth_provider.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _passwordController = TextEditingController();
  final _passwordConfirmationController = TextEditingController();
  bool _isSubmitting = false;
  bool _obscurePassword = true;
  String? _error;

  static const Color _heroBlue = Color(0xFF6AAFFA);
  static const Color _heroBlueDark = Color(0xFF5B9DFA);
  static const Color _brandBlue = Color(0xFF3B82F6);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _placeholder = Color(0xFFB0B7C3);
  static const Color _borderLight = Color(0xFFE5E7EB);
  static const Color _focusBlue = Color(0xFF60A5FA);

  @override
  void dispose() {
    for (final controller in [
      _nameController,
      _emailController,
      _phoneController,
      _addressController,
      _passwordController,
      _passwordConfirmationController,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isSubmitting = true;
      _error = null;
    });

    try {
      await context.read<AuthProvider>().register(
            name: _nameController.text.trim(),
            email: _emailController.text.trim(),
            phone: _phoneController.text.trim(),
            address: _addressController.text.trim(),
            password: _passwordController.text,
            passwordConfirmation: _passwordConfirmationController.text,
          );

      if (mounted) {
        Navigator.of(context).pop();
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _error = error.message);
      }
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  String? _required(String? value) => (value == null || value.trim().isEmpty) ? 'Wajib diisi.' : null;

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

  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 7),
      child: Text(
        text,
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: _textDark,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _heroBlue,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: Colors.white,
        title: Row(
          children: [
            Container(
              width: 30,
              height: 30,
              decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
              child: const Center(
                child: Text(
                  'DSC',
                  style: TextStyle(color: _brandBlue, fontSize: 10, fontWeight: FontWeight.w800),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Text(
              AppConfig.appName,
              style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w700),
            ),
          ],
        ),
      ),
      body: Container(
        margin: const EdgeInsets.only(top: 8),
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
          child: ListView(
            padding: const EdgeInsets.fromLTRB(26, 28, 26, 32),
            children: [
              const Text(
                'Daftar Akun',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.w800,
                  color: _textDark,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Buat akun pelanggan untuk memesan hardware, lisensi software, dan layanan IT.',
                style: TextStyle(fontSize: 13, color: _textMuted, height: 1.5),
              ),
              const SizedBox(height: 22),

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
                        child: Text(
                          _error!,
                          style: const TextStyle(color: Color(0xFFB91C1C), fontSize: 12.5, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),
              ],

              _buildLabel('Nama Lengkap / Perusahaan'),
              TextFormField(
                controller: _nameController,
                textCapitalization: TextCapitalization.words,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                cursorColor: _brandBlue,
                decoration: _webInputDecoration(
                  hintText: 'PT Contoh Solusi Nusantara',
                  prefixIcon: Icons.person_outline_rounded,
                ),
                validator: _required,
              ),
              const SizedBox(height: 15),

              _buildLabel('Alamat Email'),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                cursorColor: _brandBlue,
                decoration: _webInputDecoration(
                  hintText: 'nama@perusahaan.com',
                  prefixIcon: Icons.mail_outline_rounded,
                ),
                validator: (value) => (value == null || !value.contains('@')) ? 'Masukkan email yang valid.' : null,
              ),
              const SizedBox(height: 15),

              _buildLabel('Nomor Telepon / WhatsApp'),
              TextFormField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                cursorColor: _brandBlue,
                decoration: _webInputDecoration(
                  hintText: '0812xxxxxxxx',
                  prefixIcon: Icons.phone_outlined,
                ),
                validator: _required,
              ),
              const SizedBox(height: 15),

              _buildLabel('Alamat Pengiriman / Instansi'),
              TextFormField(
                controller: _addressController,
                maxLines: 2,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                cursorColor: _brandBlue,
                decoration: _webInputDecoration(
                  hintText: 'Jl. Jenderal Sudirman No. 10, Jakarta',
                  prefixIcon: Icons.location_on_outlined,
                ),
              ),
              const SizedBox(height: 15),

              _buildLabel('Kata Sandi'),
              TextFormField(
                controller: _passwordController,
                obscureText: _obscurePassword,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                cursorColor: _brandBlue,
                decoration: _webInputDecoration(
                  hintText: 'Minimal 8 karakter',
                  prefixIcon: Icons.lock_outline_rounded,
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      size: 18,
                      color: const Color(0xFF9CA3AF),
                    ),
                    onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                  ),
                ),
                validator: (value) => (value == null || value.length < 8) ? 'Minimal 8 karakter.' : null,
              ),
              const SizedBox(height: 15),

              _buildLabel('Ulangi Kata Sandi'),
              TextFormField(
                controller: _passwordConfirmationController,
                obscureText: _obscurePassword,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
                cursorColor: _brandBlue,
                decoration: _webInputDecoration(
                  hintText: 'Ketik ulang kata sandi',
                  prefixIcon: Icons.lock_reset_rounded,
                ),
                validator: (value) => value != _passwordController.text ? 'Kata sandi tidak sama.' : null,
              ),
              const SizedBox(height: 24),

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
                        'Daftar Sekarang',
                        style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
