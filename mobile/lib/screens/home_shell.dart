import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/cart_provider.dart';
import '../services/api_service.dart';
import 'cart_screen.dart';
import 'catalog_screen.dart';
import 'orders_screen.dart';
import 'profile_screen.dart';

class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  static const _catalogTab = 0;
  static const _cartTab = 1;
  static const _ordersTab = 2;
  static const _profileTab = 3;

  final _ordersKey = GlobalKey<OrdersScreenState>();
  int _index = 0;

  late final List<Widget> _pages = [
    const CatalogScreen(),
    const CartScreen(),
    OrdersScreen(key: _ordersKey),
    const ProfileScreen(),
  ];

  void _selectTab(int index) {
    setState(() => _index = index);

    if (index == _catalogTab) {
      ApiService.fetchProducts(page: 1);
    } else if (index == _cartTab) {
      final cart = context.read<CartProvider>();
      ApiService.fetchCart(itemsCount: cart.totalQuantity, totalPrice: cart.totalPrice);
    } else if (index == _ordersTab) {
      _ordersKey.currentState?.reload();
    } else if (index == _profileTab) {
      ApiService.fetchProfile().ignore();
    }
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = context.watch<CartProvider>().totalQuantity;

    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FC),
      body: IndexedStack(index: _index, children: _pages),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          border: const Border(top: BorderSide(color: Color(0xFFE5E7EB), width: 1)),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF1B64F2).withValues(alpha: 0.06),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: NavigationBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          indicatorColor: const Color(0xFFEFF6FF),
          selectedIndex: _index,
          onDestinationSelected: _selectTab,
          labelTextStyle: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.selected)) {
              return const TextStyle(color: Color(0xFF1B64F2), fontWeight: FontWeight.w800, fontSize: 11);
            }
            return const TextStyle(color: Color(0xFF6B7280), fontWeight: FontWeight.w600, fontSize: 11);
          }),
          destinations: [
            const NavigationDestination(
              icon: Icon(Icons.storefront_outlined, color: Color(0xFF6B7280)),
              selectedIcon: Icon(Icons.storefront_rounded, color: Color(0xFF1B64F2)),
              label: 'Katalog',
            ),
            NavigationDestination(
              icon: Badge(
                isLabelVisible: cartCount > 0,
                backgroundColor: const Color(0xFFEF4444),
                label: Text('$cartCount', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                child: const Icon(Icons.shopping_cart_outlined, color: Color(0xFF6B7280)),
              ),
              selectedIcon: Badge(
                isLabelVisible: cartCount > 0,
                backgroundColor: const Color(0xFFEF4444),
                label: Text('$cartCount', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                child: const Icon(Icons.shopping_cart_rounded, color: Color(0xFF1B64F2)),
              ),
              label: 'Keranjang',
            ),
            const NavigationDestination(
              icon: Icon(Icons.receipt_long_outlined, color: Color(0xFF6B7280)),
              selectedIcon: Icon(Icons.receipt_long_rounded, color: Color(0xFF1B64F2)),
              label: 'Pesanan',
            ),
            const NavigationDestination(
              icon: Icon(Icons.person_outline_rounded, color: Color(0xFF6B7280)),
              selectedIcon: Icon(Icons.person_rounded, color: Color(0xFF1B64F2)),
              label: 'Profil',
            ),
          ],
        ),
      ),
    );
  }
}
