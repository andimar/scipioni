import 'package:flutter_test/flutter_test.dart';
import 'package:scipioni_app/main.dart';

void main() {
  testWidgets('renders demo shell', (WidgetTester tester) async {
    await tester.pumpWidget(const ScipioniDemoApp());

    expect(find.text('Scipioni Club'), findsOneWidget);
    expect(find.text('Home'), findsOneWidget);
    expect(find.text('Eventi'), findsOneWidget);
  });
}
