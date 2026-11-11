import 'package:flutter_test/flutter_test.dart';
import 'package:itselling_mobile/core/formatters.dart';

void main() {
  test('formatRupiah menampilkan harga tanpa desimal', () {
    expect(formatRupiah(1500000), 'Rp 1.500.000');
  });
}
