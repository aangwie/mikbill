import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:billnesia_mobile/main.dart';

void main() {
  testWidgets('BillnesiaApp loads and shows loading indicator', (
    WidgetTester tester,
  ) async {
    // Provide mock SharedPreferences so StorageService works in tests.
    SharedPreferences.setMockInitialValues({});

    // Build the app and trigger a frame.
    await tester.pumpWidget(const BillnesiaApp());

    // The AuthWrapper should initially show a loading indicator
    // while checking login status.
    expect(find.text('Memuat...'), findsOneWidget);
  });
}
