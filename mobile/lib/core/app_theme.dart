import 'package:flutter/material.dart';

class AppTheme {
  AppTheme._();

  static const brand = Color(0xFF00DFB6); // Neon Cyan
  static const brandLight = Color(0xFF00E5A3); // Neon Teal
  static const brandAccent = Color(0xFF38BDF8); // Neon Blue
  static const ink = Colors.white; // Text primary in dark mode
  static const inkLight = Color(0xFFCBD5E1); // Slate 300
  static const muted = Color(0xFF7E92A6); // Slate 400
  static const background = Color(0xFF090E17); // Dark Background
  static const surface = Color(0xFF101924); // Dark Card / Surface
  static const border = Color(0xFF1A2838); // Dark Border

  static const hardwareColor = Color(0xFF10B981);
  static const serviceColor = Color(0xFF38BDF8);
  static const softwareColor = Color(0xFFA855F7);

  // Enterprise Cyber-Dark Theme Colors (Matching Reference)
  static const darkBackground = Color(0xFF090E17);
  static const darkSurface = Color(0xFF0D1522);
  static const darkCard = Color(0xFF101924);
  static const darkBorder = Color(0xFF1A2838);
  static const darkBorderLight = Color(0xFF22354A);
  static const neonCyan = Color(0xFF00DFB6);
  static const neonTeal = Color(0xFF00E5A3);
  static const neonBlue = Color(0xFF38BDF8);
  static const darkMuted = Color(0xFF6B8299);
  static const darkSubtext = Color(0xFF8FA0B2);

  static ThemeData get darkEnterprise {
    final scheme = ColorScheme.dark(
      primary: neonCyan,
      onPrimary: const Color(0xFF090E17),
      secondary: neonBlue,
      onSecondary: const Color(0xFF090E17),
      surface: darkCard,
      onSurface: Colors.white,
      error: const Color(0xFFEF4444),
      onError: Colors.white,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: scheme,
      scaffoldBackgroundColor: darkBackground,
      fontFamily: 'Roboto',
      dividerColor: darkBorder,
      appBarTheme: const AppBarTheme(
        backgroundColor: darkBackground,
        foregroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: TextStyle(
          color: Colors.white,
          fontSize: 18,
          fontWeight: FontWeight.w800,
          letterSpacing: -0.2,
        ),
        iconTheme: IconThemeData(color: Colors.white),
      ),
      cardTheme: CardThemeData(
        color: darkCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: darkBorder),
        ),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: darkCard,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
          side: const BorderSide(color: darkBorder),
        ),
        titleTextStyle: const TextStyle(
          color: Colors.white,
          fontSize: 18,
          fontWeight: FontWeight.w800,
        ),
        contentTextStyle: const TextStyle(
          color: darkSubtext,
          fontSize: 14,
          height: 1.5,
        ),
      ),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: darkCard,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
          side: BorderSide(color: darkBorder),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: neonCyan,
          foregroundColor: const Color(0xFF090E17),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
          textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: neonCyan,
          side: const BorderSide(color: neonCyan, width: 1.1),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: neonCyan,
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: darkCard,
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        hintStyle: const TextStyle(color: darkMuted, fontSize: 13.5),
        labelStyle: const TextStyle(color: darkSubtext, fontSize: 13.5),
        prefixIconColor: darkMuted,
        suffixIconColor: darkMuted,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: darkBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: darkBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: neonCyan, width: 1.4),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: Colors.white,
        contentTextStyle: const TextStyle(color: Color(0xFF111827), fontSize: 13, fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.2),
        ),
        behavior: SnackBarBehavior.floating,
        elevation: 6,
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: darkBackground,
        indicatorColor: darkCard,
        elevation: 0,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(color: neonCyan, fontWeight: FontWeight.w800, fontSize: 12);
          }
          return const TextStyle(color: darkMuted, fontWeight: FontWeight.w500, fontSize: 12);
        }),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: neonCyan, size: 24);
          }
          return const IconThemeData(color: darkMuted, size: 24);
        }),
      ),
    );
  }

  // Backward compatibility alias
  static ThemeData get light => darkEnterprise;
}

