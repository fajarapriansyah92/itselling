import 'package:intl/intl.dart';

final _rupiah = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

String formatRupiah(num value) => _rupiah.format(value);

String formatDateTime(DateTime value) => DateFormat('d MMM yyyy, HH:mm', 'id_ID').format(value.toLocal());
