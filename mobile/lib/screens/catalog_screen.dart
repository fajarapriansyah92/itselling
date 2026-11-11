import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/app_config.dart';
import '../core/formatters.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../services/api_service.dart';
import '../widgets/error_banner.dart';
import '../widgets/notifications_sheet.dart';
import '../widgets/product_image.dart';
import '../widgets/profile_quick_sheet.dart';
import '../widgets/server_settings_dialog.dart';
import 'cart_screen.dart';
import 'product_detail_screen.dart';
import 'user_chat_list_screen.dart';

enum MainFilterType { all, product, service }

enum SortOption { newest, priceLow, priceHigh, nameAsc }

class CatalogScreen extends StatefulWidget {
  const CatalogScreen({super.key});

  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  final _scrollController = ScrollController();
  final _searchController = TextEditingController();
  final List<Product> _products = [];
  List<Category> _categories = [];

  String? _selectedCategory;
  MainFilterType _mainFilter = MainFilterType.all;
  SortOption _sortOption = SortOption.newest;
  bool _onlyAvailable = false;

  int _page = 0;
  int _requestId = 0;
  bool _hasMore = true;
  bool _isLoading = false;
  bool _hasUnreadNotifs = true;
  int _unreadChatCount = 0;
  String? _error;
  Timer? _liveRefreshTimer;

  // Website & Medlio Light/Royal Blue Palette
  static const Color _bgPage = Color(0xFFF3F6FC);
  static const Color _primaryBlue = Color(0xFF1B64F2);
  static const Color _softBlue = Color(0xFF5B9DFA);
  static const Color _textDark = Color(0xFF111827);
  static const Color _textMuted = Color(0xFF6B7280);
  static const Color _borderLight = Color(0xFFE5E7EB);

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadCategories();
    _refresh();
    _checkUnreadChatCount();
    _liveRefreshTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (mounted) {
        _checkUnreadChatCount();
        if (!_isLoading && _searchController.text.trim().isEmpty) {
          _silentRefreshProducts();
        }
      }
    });
  }

  Future<void> _checkUnreadChatCount() async {
    final count = await ApiService.fetchUserUnreadChatCount();
    if (mounted) {
      setState(() => _unreadChatCount = count);
    }
  }

  @override
  void dispose() {
    _liveRefreshTimer?.cancel();
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    final position = _scrollController.position;
    if (_error == null && position.pixels >= position.maxScrollExtent - 300) {
      _loadMore();
    }
  }

  Future<void> _loadCategories({bool silent = false}) async {
    try {
      final categories = await ApiService.fetchCategories(silent: silent);
      if (mounted) {
        setState(() => _categories = categories);
      }
    } on ApiException {
      // Optional category loading
    }
  }

  Future<void> _silentRefreshProducts() async {
    try {
      _loadCategories(silent: true);
      final result = await ApiService.fetchProducts(
        page: 1,
        category: _selectedCategory,
        search: _searchController.text.trim().isEmpty
            ? null
            : _searchController.text.trim(),
        silent: true,
      );
      if (mounted) {
        setState(() {
          _products.clear();
          _products.addAll(result.items);
        });
      }
    } catch (_) {}
  }

  Future<void> _refresh() async {
    _requestId++;
    setState(() {
      _products.clear();
      _page = 0;
      _hasMore = true;
      _isLoading = false;
      _error = null;
    });

    _loadCategories();

    await _loadMore();
  }

  Future<void> _loadMore() async {
    if (_isLoading || !_hasMore) {
      return;
    }

    final requestId = _requestId;
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final result = await ApiService.fetchProducts(
        page: _page + 1,
        category: _selectedCategory,
        search: _searchController.text.trim(),
      );

      if (!mounted || requestId != _requestId) {
        return;
      }

      setState(() {
        _products.addAll(result.items);
        _page = result.currentPage;
        _hasMore = result.hasMore;
        _isLoading = false;
      });
    } on ApiException catch (error) {
      if (!mounted || requestId != _requestId) {
        return;
      }

      setState(() {
        _error = error.message;
        _isLoading = false;
      });
    }
  }

  void _selectCategory(String? slug) {
    setState(() {
      _selectedCategory = slug;
      _mainFilter = MainFilterType.all;
    });
    _refresh();
  }

  void _setMainFilter(MainFilterType filter) {
    setState(() => _mainFilter = MainFilterType.all);
  }

  List<Product> get _filteredProducts {
    List<Product> list = List<Product>.from(_products);

    if (_mainFilter == MainFilterType.product) {
      list = list.where((p) => p.type != 'service').toList();
    } else if (_mainFilter == MainFilterType.service) {
      list = list.where((p) => p.type == 'service').toList();
    }

    if (_onlyAvailable) {
      list = list
          .where((p) => p.isAvailable && (p.stock == null || p.stock! > 0))
          .toList();
    }

    switch (_sortOption) {
      case SortOption.priceLow:
        list.sort((a, b) => a.price.compareTo(b.price));
      case SortOption.priceHigh:
        list.sort((a, b) => b.price.compareTo(a.price));
      case SortOption.nameAsc:
        list.sort((a, b) => a.name.compareTo(b.name));
      case SortOption.newest:
        list.sort((a, b) => b.id.compareTo(a.id));
    }

    return list;
  }

  void _openFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: const Color(0xFFD1D5DB),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Filter & Urutkan',
                          style: TextStyle(
                              fontSize: 17,
                              fontWeight: FontWeight.w800,
                              color: _textDark),
                        ),
                        TextButton(
                          onPressed: () {
                            setState(() {
                              _sortOption = SortOption.newest;
                              _onlyAvailable = false;
                              _mainFilter = MainFilterType.all;
                            });
                            Navigator.pop(ctx);
                          },
                          child: const Text('Reset',
                              style: TextStyle(
                                  color: _primaryBlue,
                                  fontWeight: FontWeight.w700)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    const Text('Urutkan Berdasarkan',
                        style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: _textMuted)),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildSortChip(
                            'Terbaru', SortOption.newest, setModalState),
                        _buildSortChip('Harga Termurah', SortOption.priceLow,
                            setModalState),
                        _buildSortChip('Harga Termahal', SortOption.priceHigh,
                            setModalState),
                        _buildSortChip(
                            'Nama A-Z', SortOption.nameAsc, setModalState),
                      ],
                    ),
                    const SizedBox(height: 20),
                    const Text('Ketersediaan',
                        style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: _textMuted)),
                    const SizedBox(height: 8),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeTrackColor: _primaryBlue,
                      title: const Text('Hanya yang tersedia / ready',
                          style: TextStyle(
                              color: _textDark,
                              fontSize: 13.5,
                              fontWeight: FontWeight.w600)),
                      value: _onlyAvailable,
                      onChanged: (val) {
                        setModalState(() => _onlyAvailable = val);
                        setState(() => _onlyAvailable = val);
                      },
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton(
                        style: FilledButton.styleFrom(
                          backgroundColor: _primaryBlue,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () => Navigator.pop(ctx),
                        child: const Text('Terapkan Filter',
                            style: TextStyle(
                                fontWeight: FontWeight.w800, fontSize: 14)),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildSortChip(
      String label, SortOption opt, StateSetter setModalState) {
    final isSelected = _sortOption == opt;
    return GestureDetector(
      onTap: () {
        setModalState(() => _sortOption = opt);
        setState(() => _sortOption = opt);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? _primaryBlue : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? _primaryBlue : _borderLight,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : _textDark,
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final userName = user?.name ?? 'Pelanggan';
    final displayedProducts = _filteredProducts;

    return Scaffold(
      backgroundColor: _bgPage,
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(68),
        child: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          scrolledUnderElevation: 1,
          shadowColor: _primaryBlue.withValues(alpha: 0.08),
          automaticallyImplyLeading: false,
          titleSpacing: 14,
          title: Row(
            children: [
              // Website Brand Logo Circle (White/Blue DSC Badge)
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF1B64F2), Color(0xFF5B9DFA)],
                  ),
                  borderRadius: BorderRadius.circular(11),
                  boxShadow: [
                    BoxShadow(
                      color: _primaryBlue.withValues(alpha: 0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: const Center(
                  child: Text(
                    'DSC',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 12,
                      letterSpacing: -0.4,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      AppConfig.appName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 14.5,
                        color: _textDark,
                        letterSpacing: -0.2,
                      ),
                    ),
                    Text(
                      'Halo, $userName',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 11,
                        color: _textMuted,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          actions: [
            // User Avatar with green online indicator ring
            GestureDetector(
              onTap: () => ProfileQuickSheet.show(context),
              child: Tooltip(
                message: 'Menu Profil Saya',
                child: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: 34,
                      height: 34,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                            color: _primaryBlue.withValues(alpha: 0.35),
                            width: 1.6),
                        color: const Color(0xFFEFF6FF),
                      ),
                      child: Center(
                        child: Text(
                          userName.isNotEmpty ? userName[0].toUpperCase() : 'U',
                          style: const TextStyle(
                              color: _primaryBlue,
                              fontWeight: FontWeight.w800,
                              fontSize: 13),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: -1,
                      right: -1,
                      child: Container(
                        width: 9,
                        height: 9,
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981),
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 1.5),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 6),

            // Server / Activity Pulse button
            IconButton(
              visualDensity: VisualDensity.compact,
              icon: const Icon(Icons.dns_outlined,
                  color: Color(0xFF4B5563), size: 21),
              tooltip: 'Status Server & Jaringan',
              onPressed: () => ServerSettingsDialog.show(context),
            ),

            // Live Product Chat Consultation button with realtime unread badge
            Stack(
              alignment: Alignment.center,
              children: [
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: Icon(
                    _unreadChatCount > 0
                        ? Icons.chat_rounded
                        : Icons.chat_bubble_outline_rounded,
                    color: _unreadChatCount > 0
                        ? _primaryBlue
                        : const Color(0xFF4B5563),
                    size: 20,
                  ),
                  tooltip: 'Pusat Konsultasi & Diskusi Produk',
                  onPressed: () async {
                    await Navigator.of(context).push(
                      MaterialPageRoute(
                          builder: (_) => const UserChatListScreen()),
                    );
                    _checkUnreadChatCount();
                  },
                ),
                if (_unreadChatCount > 0)
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 4, vertical: 1),
                      decoration: BoxDecoration(
                        color: _primaryBlue,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.white, width: 1.2),
                      ),
                      constraints:
                          const BoxConstraints(minWidth: 14, minHeight: 14),
                      child: Text(
                        _unreadChatCount > 9 ? '9+' : '$_unreadChatCount',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 8,
                          fontWeight: FontWeight.w900,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),

            // Bell notification button
            Stack(
              alignment: Alignment.center,
              children: [
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: Icon(
                    _hasUnreadNotifs
                        ? Icons.notifications_active_outlined
                        : Icons.notifications_none_rounded,
                    color: _hasUnreadNotifs
                        ? _primaryBlue
                        : const Color(0xFF4B5563),
                    size: 22,
                  ),
                  tooltip: 'Pusat Notifikasi',
                  onPressed: () {
                    NotificationsSheet.show(
                      context,
                      onAllRead: () {
                        if (mounted) {
                          setState(() => _hasUnreadNotifs = false);
                        }
                      },
                    );
                  },
                ),
                if (_hasUnreadNotifs)
                  Positioned(
                    top: 10,
                    right: 10,
                    child: Container(
                      width: 7,
                      height: 7,
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444),
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 1.2),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(width: 8),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        color: _primaryBlue,
        backgroundColor: Colors.white,
        child: CustomScrollView(
          controller: _scrollController,
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            // Search Bar & Filter Section
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
                child: Container(
                  height: 46,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: _borderLight),
                    boxShadow: [
                      BoxShadow(
                        color: _primaryBlue.withValues(alpha: 0.05),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      const SizedBox(width: 14),
                      const Icon(Icons.search_rounded,
                          color: Color(0xFF9CA3AF), size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: _searchController,
                          textInputAction: TextInputAction.search,
                          style: const TextStyle(
                              color: _textDark,
                              fontSize: 13.5,
                              fontWeight: FontWeight.w500),
                          onSubmitted: (_) => _refresh(),
                          decoration: const InputDecoration(
                            isDense: true,
                            filled: false,
                            hintText: 'Cari jasa, router, server, software...',
                            hintStyle: TextStyle(
                                color: Color(0xFF9CA3AF), fontSize: 13),
                            border: InputBorder.none,
                            enabledBorder: InputBorder.none,
                            focusedBorder: InputBorder.none,
                            contentPadding: EdgeInsets.zero,
                          ),
                        ),
                      ),
                      if (_searchController.text.isNotEmpty)
                        IconButton(
                          icon: const Icon(Icons.clear_rounded,
                              size: 16, color: Color(0xFF9CA3AF)),
                          onPressed: () {
                            _searchController.clear();
                            _refresh();
                          },
                        ),
                      Container(
                        height: 20,
                        width: 1,
                        color: _borderLight,
                      ),
                      IconButton(
                        icon: const Icon(Icons.tune_rounded,
                            color: _primaryBlue, size: 20),
                        tooltip: 'Filter Lanjutan',
                        onPressed: _openFilterBottomSheet,
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Category Filter Horizontal Pills with Filter Button
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.only(top: 0, bottom: 12),
                child: SizedBox(
                  height: 36,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    children: [
                      _CategoryLightChip(
                        label: 'Semua',
                        selected: _selectedCategory == null,
                        onTap: () => _selectCategory(null),
                      ),
                      for (final category in _categories)
                        _CategoryLightChip(
                          label: category.name,
                          selected: _selectedCategory == category.slug,
                          onTap: () => _selectCategory(category.slug),
                        ),
                      GestureDetector(
                        onTap: _openFilterBottomSheet,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 7),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: _softBlue, width: 1.2),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.tune_rounded,
                                  size: 14, color: _primaryBlue),
                              SizedBox(width: 5),
                              Text(
                                'Filter',
                                style: TextStyle(
                                  color: _primaryBlue,
                                  fontWeight: FontWeight.w700,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Hero Promotion Banner Auto-Slider
            if (_selectedCategory == null && _searchController.text.isEmpty)
              SliverToBoxAdapter(
                child: _AutoPromoBannerSlider(
                  onFilterSelected: (type) => _setMainFilter(type),
                ),
              ),

            // Section Header: "Katalog Enterprise" with items count & Sort Icon
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Katalog Enterprise',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                        color: _textDark,
                        letterSpacing: -0.3,
                      ),
                    ),
                    InkWell(
                      onTap: _openFilterBottomSheet,
                      borderRadius: BorderRadius.circular(8),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 6, vertical: 4),
                        child: Row(
                          children: [
                            Text(
                              '${displayedProducts.length} item',
                              style: const TextStyle(
                                fontSize: 12.5,
                                color: _textMuted,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(width: 4),
                            const Icon(Icons.swap_vert_rounded,
                                size: 16, color: _primaryBlue),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Products Grid
            _buildProductsGrid(displayedProducts),

            if (_isLoading && _products.isNotEmpty)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 24),
                  child: Center(
                      child: CircularProgressIndicator(color: _primaryBlue)),
                ),
              ),

            const SliverToBoxAdapter(child: SizedBox(height: 32)),
          ],
        ),
      ),
    );
  }

  Widget _buildProductsGrid(List<Product> products) {
    if (products.isEmpty) {
      if (_isLoading) {
        return const SliverFillRemaining(
          hasScrollBody: false,
          child: Center(child: CircularProgressIndicator(color: _primaryBlue)),
        );
      }

      if (_error != null) {
        return SliverFillRemaining(
          hasScrollBody: false,
          child: Center(child: ErrorView(message: _error!, onRetry: _refresh)),
        );
      }

      return const SliverFillRemaining(
        hasScrollBody: false,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.search_off_rounded,
                  size: 54, color: Color(0xFF9CA3AF)),
              SizedBox(height: 12),
              Text(
                'Layanan atau produk tidak ditemukan.',
                style:
                    TextStyle(color: _textMuted, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),
      );
    }

    return SliverPadding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      sliver: SliverGrid(
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 0.68,
        ),
        delegate: SliverChildBuilderDelegate(
          (context, index) => EnterpriseProductCard(
            product: products[index],
            onReturnFromDetail: _silentRefreshProducts,
          ),
          childCount: products.length,
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Category Filter Chip (Light & Website Blue)
// ---------------------------------------------------------------------------

class _CategoryLightChip extends StatelessWidget {
  const _CategoryLightChip(
      {required this.label, required this.selected, required this.onTap});

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
          decoration: BoxDecoration(
            color: selected ? const Color(0xFF1B64F2) : Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color:
                  selected ? const Color(0xFF1B64F2) : const Color(0xFFE5E7EB),
              width: 1.0,
            ),
            boxShadow: selected
                ? [
                    BoxShadow(
                      color: const Color(0xFF1B64F2).withValues(alpha: 0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : null,
          ),
          child: Center(
            child: Text(
              label,
              style: TextStyle(
                color: selected ? Colors.white : const Color(0xFF4B5563),
                fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
                fontSize: 12.5,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Auto-Sliding Promo Banner Slider (Aligned with Website Blue Hero)
// ---------------------------------------------------------------------------

class _PromoItem {
  const _PromoItem({
    required this.tag,
    required this.tagColor,
    required this.title,
    required this.subtitle,
    required this.buttonText,
    required this.gradientColors,
    required this.borderColor,
    required this.icon,
    required this.filterType,
  });

  final String tag;
  final Color tagColor;
  final String title;
  final String subtitle;
  final String buttonText;
  final List<Color> gradientColors;
  final Color borderColor;
  final IconData icon;
  final MainFilterType filterType;
}

class _AutoPromoBannerSlider extends StatefulWidget {
  const _AutoPromoBannerSlider({required this.onFilterSelected});

  final ValueChanged<MainFilterType> onFilterSelected;

  @override
  State<_AutoPromoBannerSlider> createState() => _AutoPromoBannerSliderState();
}

class _AutoPromoBannerSliderState extends State<_AutoPromoBannerSlider> {
  late final PageController _pageController;
  Timer? _timer;
  int _currentPage = 0;

  static const List<_PromoItem> _promos = [
    _PromoItem(
      tag: 'HERO SOLUTION',
      tagColor: Color(0xFFFFFFFF),
      title: 'Instalasi Jaringan & Firewall',
      subtitle: 'Solusi MikroTik, Cisco & Fortinet untuk Keamanan Kantor Anda.',
      buttonText: 'Konsultasi Jaringan',
      gradientColors: [Color(0xFF1B64F2), Color(0xFF5B9DFA)],
      borderColor: Color(0xFF1B64F2),
      icon: Icons.router_rounded,
      filterType: MainFilterType.service,
    ),
    _PromoItem(
      tag: 'FLASH SALE 25%',
      tagColor: Color(0xFFFEF08A),
      title: 'Server Rack & Smart UPS APC',
      subtitle:
          'Garansi resmi distributor. Siap pasang on-site & diskon enterprise.',
      buttonText: 'Lihat Promo Hardware',
      gradientColors: [Color(0xFF2563EB), Color(0xFF38BDF8)],
      borderColor: Color(0xFF2563EB),
      icon: Icons.dns_rounded,
      filterType: MainFilterType.product,
    ),
    _PromoItem(
      tag: 'LISENSI RESMI',
      tagColor: Color(0xFFE9D5FF),
      title: 'Microsoft 365 & Cloud Business',
      subtitle:
          'Original product key langsung aktif di app dengan multi-seat instan.',
      buttonText: 'Aktivasi Lisensi Bisnis',
      gradientColors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
      borderColor: Color(0xFF4F46E5),
      icon: Icons.vpn_key_rounded,
      filterType: MainFilterType.product,
    ),
    _PromoItem(
      tag: 'SECURITY AUDIT',
      tagColor: Color(0xFFA7F3D0),
      title: 'Audit Keamanan & Pentest',
      subtitle:
          'Deteksi dini celah keamanan server & penguatan enkripsi perimeter.',
      buttonText: 'Jadwalkan Audit Sistem',
      gradientColors: [Color(0xFF0284C7), Color(0xFF0D9488)],
      borderColor: Color(0xFF0284C7),
      icon: Icons.security_rounded,
      filterType: MainFilterType.service,
    ),
  ];

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _startTimer();
  }

  void _startTimer() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (!mounted) return;
      final nextPage = (_currentPage + 1) % _promos.length;
      _pageController.animateToPage(
        nextPage,
        duration: const Duration(milliseconds: 650),
        curve: Curves.easeInOutCubic,
      );
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(
          height: 184,
          child: PageView.builder(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() => _currentPage = index);
            },
            itemCount: _promos.length,
            itemBuilder: (context, index) {
              final promo = _promos[index];
              return _buildSlideCard(promo);
            },
          ),
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(_promos.length, (i) {
            final isActive = i == _currentPage;
            return AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: isActive ? 22 : 6,
              height: 5,
              decoration: BoxDecoration(
                color: isActive
                    ? const Color(0xFF1B64F2)
                    : const Color(0xFFCBD5E1),
                borderRadius: BorderRadius.circular(10),
              ),
            );
          }),
        ),
      ],
    );
  }

  Widget _buildSlideCard(_PromoItem promo) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 4, 16, 4),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: promo.gradientColors,
        ),
        boxShadow: [
          BoxShadow(
            color: promo.gradientColors.first.withValues(alpha: 0.28),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Decorative Website Circles
          Positioned(
            top: -24,
            right: -18,
            child: Container(
              width: 110,
              height: 110,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.12),
              ),
            ),
          ),
          Positioned(
            bottom: -30,
            right: 55,
            child: Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.08),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            child: Row(
              children: [
                Expanded(
                  flex: 3,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          promo.tag,
                          style: TextStyle(
                            color: promo.tagColor,
                            fontSize: 9,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.7,
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        promo.title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w800,
                          height: 1.2,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        promo.subtitle,
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.9),
                          fontSize: 11,
                          height: 1.3,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 10),
                      InkWell(
                        onTap: () {
                          widget.onFilterSelected(promo.filterType);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              behavior: SnackBarBehavior.floating,
                              backgroundColor: Colors.white,
                              elevation: 6,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                                side: const BorderSide(
                                    color: Color(0xFFBFDBFE), width: 1.2),
                              ),
                              content: Text(
                                'Menampilkan kategori terkait: ${promo.title}',
                                style: const TextStyle(
                                    color: Color(0xFF111827),
                                    fontWeight: FontWeight.w700,
                                    fontSize: 12.5),
                              ),
                              duration: const Duration(seconds: 2),
                            ),
                          );
                        },
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 11, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            promo.buttonText,
                            style: TextStyle(
                              color: promo.gradientColors.first,
                              fontSize: 10.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  flex: 2,
                  child: Center(
                    child: Container(
                      width: 78,
                      height: 78,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.18),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                            color: Colors.white.withValues(alpha: 0.35),
                            width: 1.2),
                      ),
                      child: Icon(promo.icon, color: Colors.white, size: 38),
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
}

// ---------------------------------------------------------------------------
// Enterprise Product Card (Clean White & Royal Blue Website Style)
// ---------------------------------------------------------------------------

class EnterpriseProductCard extends StatelessWidget {
  const EnterpriseProductCard(
      {super.key, required this.product, this.onReturnFromDetail});

  final Product product;
  final VoidCallback? onReturnFromDetail;

  List<String> _extractSpecs(Product p) {
    final text = p.shortDescription.trim().isNotEmpty
        ? p.shortDescription
        : p.description;

    if (text.contains('•')) {
      final bullets = text
          .split('•')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty && e.length < 50)
          .take(4)
          .toList();
      if (bullets.isNotEmpty) return bullets;
    }

    if (text.contains('\n')) {
      final lines = text
          .split('\n')
          .map((e) => e.replaceAll(RegExp(r'^[-*•\d\.\s]+'), '').trim())
          .where((e) => e.isNotEmpty && e.length < 50)
          .take(4)
          .toList();
      if (lines.isNotEmpty) return lines;
    }

    if (text.contains(',')) {
      final parts = text
          .split(',')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty && e.length < 50)
          .take(4)
          .toList();
      if (parts.isNotEmpty) return parts;
    }

    if (p.type == 'service') {
      return ['Layanan IT Handal', 'Dukungan Onsite & Remote'];
    } else if (p.type == 'software') {
      return ['Lisensi Resmi & Aktif', 'Garansi & Support Penuh'];
    } else {
      return ['Perangkat Original', 'Garansi Resmi Distributor'];
    }
  }

  @override
  Widget build(BuildContext context) {
    final stock = product.stock;
    final isService = product.type == 'service';
    final isSoftware = product.type == 'software';
    final specs = _extractSpecs(product);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1B64F2).withValues(alpha: 0.06),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () async {
            await Navigator.of(context).push(
              MaterialPageRoute(
                  builder: (_) => ProductDetailScreen(product: product)),
            );
            onReturnFromDetail?.call();
          },
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Image Header Area (Compact & Proportional)
              Expanded(
                flex: 10,
                child: Container(
                  color: const Color(0xFFF8FAFC),
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      ProductImage(product: product, iconSize: 34),
                      Positioned(
                        top: 8,
                        left: 8,
                        child: Container(
                          constraints: const BoxConstraints(maxWidth: 130),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 7, vertical: 3),
                          decoration: BoxDecoration(
                            color: isService
                                ? const Color(0xFFE0F2FE)
                                : (isSoftware
                                    ? const Color(0xFFF3E8FF)
                                    : const Color(0xFFDBEAFE)),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: isService
                                  ? const Color(0xFF7DD3FC)
                                  : (isSoftware
                                      ? const Color(0xFFD8B4FE)
                                      : const Color(0xFF93C5FD)),
                              width: 1,
                            ),
                          ),
                          child: Text(
                            (product.categoryName?.isNotEmpty == true
                                    ? product.categoryName!
                                    : product.typeLabel)
                                .toUpperCase(),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: isService
                                  ? const Color(0xFF0369A1)
                                  : (isSoftware
                                      ? const Color(0xFF6D28D9)
                                      : const Color(0xFF1D4ED8)),
                              fontSize: 8.5,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // 2. Body Content Area (Compact, No Excess Empty Space)
              Expanded(
                flex: 11,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(10, 8, 10, 9),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Brand (or Category if no brand)
                      if (product.brand != null &&
                          product.brand!.trim().isNotEmpty) ...[
                        Text(
                          product.brand!.toUpperCase(),
                          style: const TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF6B7280),
                            letterSpacing: 0.3,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                      ],

                      // Product Title
                      Text(
                        product.name,
                        style: const TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF111827),
                          height: 1.2,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),

                      // Contextual Status Row (Service vs Stock/Rating)
                      if (isService)
                        Row(
                          children: [
                            const Icon(Icons.verified_user_outlined,
                                size: 12, color: Color(0xFF1B64F2)),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                specs.isNotEmpty
                                    ? specs.first
                                    : 'Layanan Teknisi IT',
                                style: const TextStyle(
                                  fontSize: 10,
                                  color: Color(0xFF6B7280),
                                  fontWeight: FontWeight.w600,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        )
                      else
                        Row(
                          children: [
                            const Icon(Icons.star_rounded,
                                size: 13, color: Color(0xFFF59E0B)),
                            const SizedBox(width: 3),
                            if (product.reviewsCount > 0) ...[
                              Text(
                                '${product.ratingAverage.toStringAsFixed(1)} (${product.reviewsCount})',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFFD97706),
                                ),
                              ),
                              const SizedBox(width: 5),
                            ],
                            Expanded(
                              child: Text(
                                stock == null
                                    ? 'Tersedia'
                                    : (stock > 0
                                        ? 'Stok: $stock'
                                        : 'Stok Habis'),
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: stock == 0
                                      ? const Color(0xFFDC2626)
                                      : const Color(0xFF059669),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),

                      const Spacer(),

                      // Price & Action Button Row
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  formatRupiah(product.price),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF1B64F2),
                                  ),
                                ),
                                if (!isService &&
                                    product.unit.isNotEmpty &&
                                    product.unit.toLowerCase() != 'jasa')
                                  Text(
                                    '/ ${product.unit}',
                                    style: const TextStyle(
                                        fontSize: 9.5,
                                        color: Color(0xFF6B7280)),
                                  ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 6),
                          Material(
                            color: const Color(0xFF1B64F2),
                            borderRadius: BorderRadius.circular(8),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(8),
                              onTap: () {
                                final added = context
                                    .read<CartProvider>()
                                    .add(product, 1);
                                final messenger = ScaffoldMessenger.of(context);
                                messenger.clearSnackBars();
                                messenger.showSnackBar(
                                  SnackBar(
                                    behavior: SnackBarBehavior.floating,
                                    duration:
                                        const Duration(milliseconds: 2200),
                                    dismissDirection:
                                        DismissDirection.horizontal,
                                    backgroundColor: Colors.white,
                                    elevation: 6,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                      side: BorderSide(
                                        color: added
                                            ? const Color(0xFFBFDBFE)
                                            : const Color(0xFFFECACA),
                                        width: 1.2,
                                      ),
                                    ),
                                    content: Row(
                                      children: [
                                        Icon(
                                          added
                                              ? Icons.check_circle_rounded
                                              : Icons.info_outline,
                                          color: added
                                              ? const Color(0xFF1B64F2)
                                              : const Color(0xFFDC2626),
                                          size: 20,
                                        ),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Text(
                                            added
                                                ? '${product.name} masuk ke keranjang'
                                                : 'Stok produk tidak mencukupi.',
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(
                                                color: Color(0xFF111827),
                                                fontWeight: FontWeight.w700,
                                                fontSize: 13),
                                          ),
                                        ),
                                      ],
                                    ),
                                    action: added
                                        ? SnackBarAction(
                                            label: 'Lihat Keranjang',
                                            backgroundColor:
                                                const Color(0xFF1B64F2),
                                            textColor: Colors.white,
                                            onPressed: () {
                                              messenger.hideCurrentSnackBar();
                                              Navigator.of(context).push(
                                                MaterialPageRoute(
                                                    builder: (_) =>
                                                        const CartScreen()),
                                              );
                                            },
                                          )
                                        : null,
                                  ),
                                );

                                Timer(const Duration(milliseconds: 2300), () {
                                  try {
                                    messenger.hideCurrentSnackBar();
                                  } catch (_) {}
                                });
                              },
                              child: const Padding(
                                padding: EdgeInsets.all(7),
                                child: Icon(Icons.add_shopping_cart_rounded,
                                    size: 16, color: Colors.white),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
