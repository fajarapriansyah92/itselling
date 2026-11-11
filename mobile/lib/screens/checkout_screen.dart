import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/formatters.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../services/api_service.dart';
import 'order_detail_screen.dart';

// ---------------------------------------------------------------------------
// Payment Method Types
// ---------------------------------------------------------------------------

enum PaymentMethodType { virtualAccount, creditCard }

class _Bank {
  const _Bank(this.name, this.code, this.color, this.iconLetter, this.logoUrl);
  final String name;
  final String code;
  final Color color;
  final String iconLetter;
  final String logoUrl;
}

const _banks = [
  _Bank('BCA', 'BCA', Color(0xFF0066AE), 'B',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/bca@2x.png'),
  _Bank('Mandiri', 'MDR', Color(0xFF003D79), 'M',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/mandiri@2x.png'),
  _Bank('BRI', 'BRI', Color(0xFF00529B), 'R',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/bri@2x.png'),
  _Bank('BNI', 'BNI', Color(0xFFFF6600), 'N',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/bni@2x.png'),
  _Bank('CIMB Niaga', 'CMB', Color(0xFF8B0000), 'C',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/cimb-niaga@2x.png'),
  _Bank('Permata', 'PMT', Color(0xFF5C2D91), 'P',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/permata@2x.png'),
  _Bank('Danamon', 'DNN', Color(0xFFE31837), 'D',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/danamon@2x.png'),
  _Bank('Jenius', 'JNS', Color(0xFF00BFA5), 'J',
      'https://cdn.jsdelivr.net/npm/idn-finlogos@2.5.0/dist/png/jenius@2x.png'),
];

// ---------------------------------------------------------------------------
// Card Number Formatter
// ---------------------------------------------------------------------------

class _CardNumberFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue old, TextEditingValue newVal) {
    final digits = newVal.text.replaceAll(' ', '');
    if (digits.length > 16) return old;
    final buffer = StringBuffer();
    for (int i = 0; i < digits.length; i++) {
      if (i > 0 && i % 4 == 0) buffer.write(' ');
      buffer.write(digits[i]);
    }
    final text = buffer.toString();
    return TextEditingValue(text: text, selection: TextSelection.collapsed(offset: text.length));
  }
}

class _ExpiryFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue old, TextEditingValue newVal) {
    final digits = newVal.text.replaceAll('/', '');
    if (digits.length > 4) return old;
    final buffer = StringBuffer();
    for (int i = 0; i < digits.length; i++) {
      if (i == 2) buffer.write('/');
      buffer.write(digits[i]);
    }
    final text = buffer.toString();
    return TextEditingValue(text: text, selection: TextSelection.collapsed(offset: text.length));
  }
}

// ---------------------------------------------------------------------------
// Main Checkout Screen
// ---------------------------------------------------------------------------

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();

  // Palette matching Catalog & Website
  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  // Contact
  late final TextEditingController _nameController;
  late final TextEditingController _phoneController;
  late final TextEditingController _addressController;
  final _notesController = TextEditingController();

  // Credit Card
  final _cardNumberController = TextEditingController();
  final _expiryController = TextEditingController();
  final _cvvController = TextEditingController();
  final _cardHolderController = TextEditingController();
  bool _obscureCvv = true;

  // Payment method
  PaymentMethodType _selectedMethod = PaymentMethodType.virtualAccount;
  _Bank? _selectedBank = _banks[0];

  bool _isSubmitting = false;
  String? _error;

  late final AnimationController _animCtrl;
  late final Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _nameController = TextEditingController(text: user?.name);
    _phoneController = TextEditingController(text: user?.phone);
    _addressController = TextEditingController(text: user?.address);
    _animCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 350));
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _animCtrl.forward();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _notesController.dispose();
    _cardNumberController.dispose();
    _expiryController.dispose();
    _cvvController.dispose();
    _cardHolderController.dispose();
    _animCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          behavior: SnackBarBehavior.floating,
          backgroundColor: const Color(0xFF991B1B),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          content: const Row(
            children: [
              Icon(Icons.warning_amber_rounded, color: Colors.white, size: 20),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Mohon periksa dan lengkapi data pembelian yang wajib diisi.',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      );
      return;
    }

    final cart = context.read<CartProvider>();
    final navigator = Navigator.of(context);

    setState(() {
      _isSubmitting = true;
      _error = null;
    });

    try {
      final isAllDigital = cart.items.isNotEmpty && cart.items.every((item) => item.product.type == 'software');
      final user = context.read<AuthProvider>().user;

      final notes = _notesController.text.trim();

      final rawAddress = _addressController.text.trim();
      final finalAddress = isAllDigital
          ? (rawAddress.isNotEmpty
              ? 'Lisensi Digital (Registrasi: $rawAddress)'
              : 'Produk Digital (Dikirim ke Akun: ${user?.email ?? "-"})')
          : rawAddress;

      final String paymentMethod;
      final String? paymentChannel;
      switch (_selectedMethod) {
        case PaymentMethodType.virtualAccount:
          paymentMethod = 'bank_transfer';
          paymentChannel = _selectedBank?.name ?? 'BCA';
          break;
        case PaymentMethodType.creditCard:
          paymentMethod = 'credit_card';
          paymentChannel = 'Kartu Kredit';
          break;
      }

      final order = await ApiService.placeOrder(
        items: cart.items.map((item) => {'product_id': item.product.id, 'quantity': item.quantity}).toList(),
        recipientName: _nameController.text.trim(),
        recipientPhone: _phoneController.text.trim(),
        shippingAddress: finalAddress,
        notes: notes.isNotEmpty ? notes : null,
        paymentMethod: paymentMethod,
        paymentChannel: paymentChannel,
      );

      cart.clear();
      navigator.pushReplacement(MaterialPageRoute(
        builder: (_) => OrderDetailScreen(orderNumber: order.orderNumber, justPlaced: true),
      ));
    } on ApiException catch (error) {
      if (mounted) {
        setState(() {
          _error = error.message;
          _isSubmitting = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            behavior: SnackBarBehavior.floating,
            backgroundColor: const Color(0xFF991B1B),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            content: Row(
              children: [
                const Icon(Icons.error_outline, color: Colors.white, size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    error.message,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                  ),
                ),
              ],
            ),
          ),
        );
      }
    }
  }

  String? _required(String? v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi.' : null;

  void _switchMethod(PaymentMethodType m) {
    setState(() => _selectedMethod = m);
    _animCtrl
      ..reset()
      ..forward();
  }

  InputDecoration _inputDec(String label, IconData icon, {String? hintText, Widget? suffixIcon}) {
    return InputDecoration(
      labelText: label,
      hintText: hintText,
      labelStyle: const TextStyle(color: _textMuted, fontSize: 13),
      hintStyle: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      prefixIcon: Icon(icon, size: 20, color: const Color(0xFF9CA3AF)),
      suffixIcon: suffixIcon,
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: _borderLight),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: _primaryBlue, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: Color(0xFFEF4444)),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Build
  // ---------------------------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    return Scaffold(
      backgroundColor: _bgPage,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: _textDark,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: _primaryBlue.withValues(alpha: 0.08),
        leading: const BackButton(color: _textDark),
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Checkout Pesanan', style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: _textDark)),
            Text('Lengkapi informasi pengiriman & pembayaran', style: TextStyle(fontSize: 11, color: _textMuted, fontWeight: FontWeight.w500)),
          ],
        ),
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16),
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.lock_outline_rounded, color: _primaryBlue, size: 18),
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          children: [
            if (_error != null) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFECACA)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline_rounded, color: Color(0xFFB91C1C), size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _error!,
                        style: const TextStyle(color: Color(0xFFB91C1C), fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],
            Builder(
              builder: (context) {
                final isAllDigital = cart.items.isNotEmpty && cart.items.every((item) => item.product.type == 'software');
                final isAllService = cart.items.isNotEmpty && cart.items.every((item) => item.product.type == 'service');

                return _buildSection(
                  number: '1',
                  title: isAllDigital
                      ? 'Informasi Pemegang Lisensi'
                      : (isAllService ? 'Informasi Klien & Lokasi Pengerjaan' : 'Informasi Pengiriman Barang'),
                  icon: isAllDigital
                      ? Icons.verified_user_outlined
                      : (isAllService ? Icons.engineering_outlined : Icons.local_shipping_outlined),
                  child: _buildContactFields(isAllDigital: isAllDigital, isAllService: isAllService),
                );
              },
            ),
            const SizedBox(height: 16),
            _buildSection(
              number: '2',
              title: 'Metode Pembayaran',
              icon: Icons.payment_outlined,
              child: _buildPaymentSection(),
            ),
            const SizedBox(height: 16),
            _buildOrderSummary(cart),
            const SizedBox(height: 100),
          ],
        ),
      ),
      bottomNavigationBar: _buildPayButton(cart),
    );
  }

  // ---------------------------------------------------------------------------
  // Section Wrapper
  // ---------------------------------------------------------------------------

  Widget _buildSection({required String number, required String title, required IconData icon, required Widget child}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _borderLight),
        boxShadow: [
          BoxShadow(
            color: _primaryBlue.withValues(alpha: 0.05),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
            child: Row(
              children: [
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(color: _primaryBlue, borderRadius: BorderRadius.circular(8)),
                  alignment: Alignment.center,
                  child: Text(number, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13)),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: _textDark)),
                ),
              ],
            ),
          ),
          Padding(padding: const EdgeInsets.all(16), child: child),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Contact Fields
  // ---------------------------------------------------------------------------

  Widget _buildContactFields({required bool isAllDigital, required bool isAllService}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (isAllDigital) ...[
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: const Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.bolt_rounded, color: _primaryBlue, size: 22),
                SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Produk Digital / Lisensi Instan',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _primaryBlue),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Tidak memerlukan alamat fisik. Kode lisensi akan otomatis aktif di aplikasi dan tersimpan di akun Anda setelah pembayaran diverifikasi.',
                        style: TextStyle(fontSize: 11.5, color: Color(0xFF1E40AF), height: 1.35),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
        ],
        TextFormField(
          controller: _nameController,
          validator: _required,
          style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
          decoration: _inputDec(
            isAllDigital ? 'Nama Pemegang Lisensi / PIC' : (isAllService ? 'Nama Klien / Penanggung Jawab' : 'Nama Penerima / PIC'),
            Icons.person_outline,
          ),
        ),
        const SizedBox(height: 12),
        TextFormField(
          controller: _phoneController,
          keyboardType: TextInputType.phone,
          validator: _required,
          style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
          decoration: _inputDec('No. Telepon / WhatsApp', Icons.phone_outlined),
        ),
        const SizedBox(height: 12),
        if (isAllDigital)
          TextFormField(
            controller: _addressController,
            style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
            decoration: _inputDec(
              'Perusahaan / Instansi Pemegang Lisensi (opsional)',
              Icons.business_outlined,
              hintText: 'Contoh: PT Teknologi Maju / Tim Dev',
            ),
          )
        else
          TextFormField(
            controller: _addressController,
            maxLines: 3,
            validator: _required,
            style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
            decoration: _inputDec(
              isAllService ? 'Lokasi Kantor / Pengerjaan Jasa IT' : 'Alamat Lengkap Pengiriman (Kurir)',
              isAllService ? Icons.business_outlined : Icons.location_on_outlined,
              hintText: isAllService
                  ? 'Alamat kantor / tempat server yang akan dikerjakan'
                  : 'Jalan, nomor rumah, RT/RW, kelurahan, kota, kode pos',
            ),
          ),
        const SizedBox(height: 12),
        TextFormField(
          controller: _notesController,
          maxLines: 2,
          style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w500),
          decoration: _inputDec(
            isAllDigital ? 'Catatan Lisensi (opsional)' : 'Catatan Tambahan (opsional)',
            Icons.notes_outlined,
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // Payment Method Section
  // ---------------------------------------------------------------------------

  Widget _buildPaymentSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            _methodTab(PaymentMethodType.virtualAccount, Icons.account_balance_outlined, 'Virtual Account'),
            const SizedBox(width: 10),
            _methodTab(PaymentMethodType.creditCard, Icons.credit_card, 'Kartu Kredit'),
          ],
        ),
        const SizedBox(height: 20),
        FadeTransition(
          opacity: _fadeAnim,
          child: switch (_selectedMethod) {
            PaymentMethodType.virtualAccount => _buildBankSelector(),
            PaymentMethodType.creditCard => _buildCreditCardForm(),
          },
        ),
      ],
    );
  }

  Widget _methodTab(PaymentMethodType type, IconData icon, String label) {
    final selected = _selectedMethod == type;
    return Expanded(
      child: GestureDetector(
        onTap: () => _switchMethod(type),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: selected ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: selected ? _primaryBlue : _borderLight, width: selected ? 1.5 : 1),
          ),
          child: Column(
            children: [
              Icon(icon, size: 22, color: selected ? _primaryBlue : _textMuted),
              const SizedBox(height: 4),
              Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: selected ? _primaryBlue : _textMuted)),
            ],
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Credit Card Form
  // ---------------------------------------------------------------------------

  Widget _buildCreditCardForm() {
    return Column(
      children: [
        _buildCardPreview(),
        const SizedBox(height: 20),
        TextFormField(
          controller: _cardNumberController,
          keyboardType: TextInputType.number,
          style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w600),
          inputFormatters: [FilteringTextInputFormatter.digitsOnly, _CardNumberFormatter()],
          validator: (v) {
            if (v == null || v.isEmpty) return 'Nomor kartu wajib diisi.';
            if (v.replaceAll(' ', '').length < 16) return 'Nomor kartu tidak valid.';
            return null;
          },
          decoration: _inputDec(
            'Nomor Kartu',
            Icons.credit_card,
            hintText: '1234 5678 9012 3456',
            suffixIcon: Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _cardBrandLogo('VISA', const Color(0xFF1A1F71)),
                  const SizedBox(width: 4),
                  _cardBrandLogo('MC', const Color(0xFFEB001B)),
                ],
              ),
            ),
          ),
          onChanged: (_) => setState(() {}),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: TextFormField(
                controller: _expiryController,
                keyboardType: TextInputType.number,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w600),
                inputFormatters: [FilteringTextInputFormatter.digitsOnly, _ExpiryFormatter()],
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Wajib diisi.';
                  if (v.length < 5) return 'Format: MM/TT';
                  return null;
                },
                decoration: _inputDec('Exp. Date', Icons.calendar_today_outlined, hintText: 'MM/TT'),
                onChanged: (_) => setState(() {}),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: TextFormField(
                controller: _cvvController,
                obscureText: _obscureCvv,
                keyboardType: TextInputType.number,
                style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w600),
                inputFormatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(3)],
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Wajib diisi.';
                  if (v.length < 3) return 'CVV 3 digit.';
                  return null;
                },
                decoration: _inputDec(
                  'CVV',
                  Icons.lock_outline,
                  hintText: '•••',
                  suffixIcon: IconButton(
                    icon: Icon(_obscureCvv ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 18, color: _textMuted),
                    onPressed: () => setState(() => _obscureCvv = !_obscureCvv),
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        TextFormField(
          controller: _cardHolderController,
          textCapitalization: TextCapitalization.characters,
          style: const TextStyle(color: _textDark, fontSize: 14, fontWeight: FontWeight.w600),
          validator: _required,
          decoration: _inputDec('Nama di Kartu', Icons.person_outline, hintText: 'NAMA SESUAI KARTU'),
          onChanged: (_) => setState(() {}),
        ),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFF0FDF4),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: const Color(0xFFBBF7D0)),
          ),
          child: const Row(
            children: [
              Icon(Icons.lock, color: Color(0xFF16A34A), size: 16),
              SizedBox(width: 8),
              Expanded(child: Text('Data kartu Anda dienkripsi & aman 256-bit SSL', style: TextStyle(fontSize: 12, color: Color(0xFF166534)))),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildCardPreview() {
    final cardNum = _cardNumberController.text.isEmpty ? '•••• •••• •••• ••••' : _cardNumberController.text.padRight(19, '•').substring(0, 19).replaceAll('', ' ').trim();
    final expiry = _expiryController.text.isEmpty ? 'MM/TT' : _expiryController.text;
    final holder = _cardHolderController.text.isEmpty ? 'NAMA PEMEGANG KARTU' : _cardHolderController.text.toUpperCase();

    return Container(
      height: 180,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1B64F2), Color(0xFF5B9DFA)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [BoxShadow(color: _primaryBlue.withValues(alpha: 0.3), blurRadius: 20, offset: const Offset(0, 8))],
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('CREDIT CARD', style: TextStyle(color: Colors.white70, fontSize: 10, letterSpacing: 2, fontWeight: FontWeight.w600)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(6)),
                child: const Text('VISA', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 12, letterSpacing: 1)),
              ),
            ],
          ),
          const Spacer(),
          Text(cardNum, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700, letterSpacing: 2)),
          const SizedBox(height: 16),
          Row(
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('NAMA PEMEGANG', style: TextStyle(color: Colors.white70, fontSize: 9, letterSpacing: 1)),
                  const SizedBox(height: 2),
                  Text(holder, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
                ],
              ),
              const Spacer(),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Text('BERLAKU S/D', style: TextStyle(color: Colors.white70, fontSize: 9, letterSpacing: 1)),
                  const SizedBox(height: 2),
                  Text(expiry, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _cardBrandLogo(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4)),
      child: Text(text, style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w900)),
    );
  }

  // ---------------------------------------------------------------------------
  // Bank Selector
  // ---------------------------------------------------------------------------

  Widget _buildBankSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Pilih Bank Virtual Account', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _textDark)),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 4,
            mainAxisSpacing: 10,
            crossAxisSpacing: 10,
            childAspectRatio: 0.85,
          ),
          itemCount: _banks.length,
          itemBuilder: (_, i) {
            final bank = _banks[i];
            final selected = _selectedBank?.code == bank.code;
            return GestureDetector(
              onTap: () => setState(() => _selectedBank = bank),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                decoration: BoxDecoration(
                  color: selected ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: selected ? _primaryBlue : _borderLight, width: selected ? 2 : 1),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 50,
                      height: 30,
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: _borderLight),
                      ),
                      child: Image.network(
                        bank.logoUrl,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => Container(
                          decoration: BoxDecoration(color: bank.color.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(6)),
                          alignment: Alignment.center,
                          child: Text(bank.iconLetter, style: TextStyle(color: bank.color, fontWeight: FontWeight.w900, fontSize: 13)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      bank.name,
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: selected ? _primaryBlue : _textDark,
                      ),
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            );
          },
        ),
        if (_selectedBank != null) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Row(
              children: [
                Container(
                  width: 52,
                  height: 38,
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: _borderLight),
                  ),
                  child: Image.network(
                    _selectedBank!.logoUrl,
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => Container(
                      decoration: BoxDecoration(color: _selectedBank!.color, borderRadius: BorderRadius.circular(8)),
                      alignment: Alignment.center,
                      child: Text(_selectedBank!.iconLetter, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 18)),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('${_selectedBank!.name} Virtual Account', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: _textDark)),
                      const SizedBox(height: 2),
                      const Text('Nomor Virtual Account otomatis digenerate via Midtrans', style: TextStyle(fontSize: 11, color: _primaryBlue, fontWeight: FontWeight.w600)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // Order Summary
  // ---------------------------------------------------------------------------

  Widget _buildOrderSummary(CartProvider cart) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _borderLight),
        boxShadow: [
          BoxShadow(
            color: _primaryBlue.withValues(alpha: 0.05),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
            child: Row(
              children: [
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(color: _primaryBlue, borderRadius: BorderRadius.circular(8)),
                  alignment: Alignment.center,
                  child: const Text('3', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13)),
                ),
                const SizedBox(width: 10),
                const Text('Ringkasan Pesanan', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: _textDark)),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                for (final item in cart.items)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 24,
                          height: 24,
                          decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                          alignment: Alignment.center,
                          child: Text('${item.quantity}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: _primaryBlue)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(child: Text(item.product.name, style: const TextStyle(fontSize: 13, color: _textDark, fontWeight: FontWeight.w600))),
                        const SizedBox(width: 8),
                        Text(formatRupiah(item.subtotal), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: _primaryBlue)),
                      ],
                    ),
                  ),
                const Divider(color: _borderLight),
                Row(
                  children: [
                    const Expanded(child: Text('Total Pembayaran', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: _textDark))),
                    Text(formatRupiah(cart.totalPrice), style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: _primaryBlue)),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Pay Button
  // ---------------------------------------------------------------------------

  Widget _buildPayButton(CartProvider cart) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: const Border(top: BorderSide(color: _borderLight)),
        boxShadow: [
          BoxShadow(
            color: _primaryBlue.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(16, 12, 16, MediaQuery.of(context).padding.bottom + 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.security_rounded, size: 14, color: _primaryBlue),
              SizedBox(width: 5),
              Text('Transaksi Anda aman & terenkripsi 256-bit SSL', style: TextStyle(fontSize: 11, color: _textMuted, fontWeight: FontWeight.w500)),
            ],
          ),
          const SizedBox(height: 10),
          FilledButton.icon(
            onPressed: _isSubmitting || cart.isEmpty ? null : _submit,
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(50),
              backgroundColor: _primaryBlue,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            icon: _isSubmitting
                ? const SizedBox.square(dimension: 18, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                : const Icon(Icons.lock_outline_rounded, size: 18),
            label: Text(
              _isSubmitting ? 'Memproses Transaksi...' : 'Bayar Sekarang  •  ${formatRupiah(cart.totalPrice)}',
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
            ),
          ),
        ],
      ),
    );
  }
}
