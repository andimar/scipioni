import 'package:flutter/material.dart';

import 'api_client.dart';
import 'models.dart';

class ScipioniDemoApp extends StatelessWidget {
  const ScipioniDemoApp({super.key});

  @override
  Widget build(BuildContext context) {
    const burgundy = Color(0xFF6A1F2B);
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Scipioni Club Demo',
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: const Color(0xFFF5EFE6),
        colorScheme: const ColorScheme.light(
          primary: burgundy,
          secondary: Color(0xFFB76E4D),
          surface: Colors.white,
          onPrimary: Colors.white,
          onSurface: Color(0xFF211A17),
        ),
      ),
      home: const DemoShell(),
    );
  }
}

class DemoShell extends StatefulWidget {
  const DemoShell({super.key});

  @override
  State<DemoShell> createState() => _DemoShellState();
}

class _DemoShellState extends State<DemoShell> {
  final ApiClient _apiClient = ApiClient();
  final TextEditingController _emailController =
      TextEditingController(text: 'cliente@example.com');
  final TextEditingController _passwordController =
      TextEditingController(text: 'password');

  int _index = 0;
  bool _loading = true;
  bool _authLoading = false;
  String? _errorMessage;
  String? _token;
  List<ApiEvent> _events = const [];
  List<ApiBooking> _bookings = const [];
  ApiUser? _user;
  int _selectedEventIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadEvents();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _loadEvents() async {
    setState(() {
      _loading = true;
      _errorMessage = null;
    });

    try {
      final events = await _apiClient.fetchEvents();
      final nextIndex = events.isEmpty
          ? 0
          : (_selectedEventIndex.clamp(0, events.length - 1) as int);
      setState(() {
        _events = events;
        _selectedEventIndex = nextIndex;
        _loading = false;
      });
      if (_token != null) {
        await _loadPrivateArea();
      }
    } catch (error) {
      setState(() {
        _errorMessage = error.toString();
        _loading = false;
      });
    }
  }

  Future<void> _login() async {
    setState(() {
      _authLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _apiClient.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
        deviceName: 'flutter-web-demo',
      );
      setState(() {
        _token = result.token;
        _user = result.user;
      });
      await _loadPrivateArea();
    } catch (error) {
      setState(() {
        _errorMessage = error.toString();
      });
    } finally {
      if (mounted) {
        setState(() {
          _authLoading = false;
        });
      }
    }
  }

  Future<void> _loadPrivateArea() async {
    if (_token == null) {
      return;
    }

    try {
      final profile = await _apiClient.fetchProfile(_token!);
      final bookings = await _apiClient.fetchBookings(_token!);
      if (!mounted) {
        return;
      }
      setState(() {
        _user = profile;
        _bookings = bookings;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _errorMessage = error.toString();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final selectedEventIndex = _events.isEmpty
        ? 0
        : (_selectedEventIndex.clamp(0, _events.length - 1) as int);
    final currentEvent = _events.isEmpty
        ? null
        : _events[selectedEventIndex];

    final pages = [
      _HomePage(
        loading: _loading,
        featuredEvent: currentEvent,
        events: _events,
        onOpenEvents: () => setState(() => _index = 1),
        onRetry: _loadEvents,
      ),
      _EventsPage(
        events: _events,
        selectedIndex: _selectedEventIndex,
        onSelectEvent: (value) => setState(() => _selectedEventIndex = value),
      ),
      _BookingsPage(
        bookings: _bookings,
        isAuthenticated: _token != null,
      ),
      _ProfilePage(
        user: _user,
        isAuthenticated: _token != null,
      ),
    ];

    return Scaffold(
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xFFF7F0E7), Color(0xFFE7D9C6)],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              _TopBar(
                emailController: _emailController,
                passwordController: _passwordController,
                user: _user,
                authLoading: _authLoading,
                onLogin: _login,
                apiBaseUrl: ApiClient.defaultBaseUrl,
              ),
              if (_errorMessage != null)
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 8),
                  child: Material(
                    color: const Color(0xFFF8D7DA),
                    borderRadius: BorderRadius.circular(18),
                    child: Padding(
                      padding: const EdgeInsets.all(14),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline, color: Color(0xFF842029)),
                          const SizedBox(width: 10),
                          Expanded(child: Text(_errorMessage!)),
                        ],
                      ),
                    ),
                  ),
                ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                  child: pages[_index],
                ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), label: 'Home'),
          NavigationDestination(icon: Icon(Icons.local_activity_outlined), label: 'Eventi'),
          NavigationDestination(icon: Icon(Icons.bookmark_outline), label: 'Prenotazioni'),
          NavigationDestination(icon: Icon(Icons.person_outline), label: 'Profilo'),
        ],
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  const _TopBar({
    required this.emailController,
    required this.passwordController,
    required this.user,
    required this.authLoading,
    required this.onLogin,
    required this.apiBaseUrl,
  });

  final TextEditingController emailController;
  final TextEditingController passwordController;
  final ApiUser? user;
  final bool authLoading;
  final VoidCallback onLogin;
  final String apiBaseUrl;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Wrap(
            spacing: 14,
            runSpacing: 14,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xFF6A1F2B),
                  borderRadius: BorderRadius.circular(16),
                ),
                alignment: Alignment.center,
                child: const Text(
                  'MS',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700),
                ),
              ),
              const SizedBox(
                width: 260,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Scipioni Club', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
                    SizedBox(height: 2),
                    Text('Demo browser collegata al backend Laravel reale.'),
                  ],
                ),
              ),
              _InfoChip(label: 'API: $apiBaseUrl'),
              if (user != null)
                _InfoChip(label: 'Cliente: ${user!.fullName}')
              else ...[
                SizedBox(
                  width: 220,
                  child: TextField(
                    controller: emailController,
                    decoration: const InputDecoration(
                      labelText: 'Email demo',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                SizedBox(
                  width: 180,
                  child: TextField(
                    controller: passwordController,
                    obscureText: true,
                    decoration: const InputDecoration(
                      labelText: 'Password',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                FilledButton(
                  onPressed: authLoading ? null : onLogin,
                  child: Text(authLoading ? 'Accesso...' : 'Accedi alla demo'),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _HomePage extends StatelessWidget {
  const _HomePage({
    required this.loading,
    required this.featuredEvent,
    required this.events,
    required this.onOpenEvents,
    required this.onRetry,
  });

  final bool loading;
  final ApiEvent? featuredEvent;
  final List<ApiEvent> events;
  final VoidCallback onOpenEvents;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (featuredEvent == null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Nessun evento pubblicato al momento.'),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: onRetry, child: const Text('Ricarica')),
          ],
        ),
      );
    }

    return ListView(
      children: [
        Container(
          padding: const EdgeInsets.all(28),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(32),
            gradient: const LinearGradient(
              colors: [Color(0xFF2D201C), Color(0xFF6A1F2B)],
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _chip(featuredEvent!.isFeatured ? 'Evento in evidenza' : 'Evento'),
                  _chip(featuredEvent!.categoryName),
                  _chip(_availabilityLabel(featuredEvent!)),
                ],
              ),
              const SizedBox(height: 18),
              Text(
                featuredEvent!.title,
                style: const TextStyle(
                  fontSize: 34,
                  height: 1.05,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 14),
              Text(
                featuredEvent!.summary,
                style: const TextStyle(
                  fontSize: 16,
                  height: 1.45,
                  color: Color(0xFFF7EDE2),
                ),
              ),
              const SizedBox(height: 18),
              Text(
                '${_formatDate(featuredEvent!.startsAt)}  |  ${_formatPrice(featuredEvent!.price)}',
                style: const TextStyle(
                  color: Color(0xFFEFD9C3),
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: onOpenEvents,
                child: const Text('Apri catalogo eventi'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 18),
        Wrap(
          spacing: 16,
          runSpacing: 16,
          children: events
              .map(
                (event) => SizedBox(
                  width: 320,
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            event.title,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(event.summary),
                          const SizedBox(height: 12),
                          Text(
                            _formatDate(event.startsAt),
                            style: const TextStyle(
                              color: Color(0xFF6A1F2B),
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(event.venueName),
                        ],
                      ),
                    ),
                  ),
                ),
              )
              .toList(),
        ),
      ],
    );
  }
}

class _EventsPage extends StatelessWidget {
  const _EventsPage({
    required this.events,
    required this.selectedIndex,
    required this.onSelectEvent,
  });

  final List<ApiEvent> events;
  final int selectedIndex;
  final ValueChanged<int> onSelectEvent;

  @override
  Widget build(BuildContext context) {
    if (events.isEmpty) {
      return const Center(child: Text('Nessun evento disponibile.'));
    }

    final current = events[selectedIndex.clamp(0, events.length - 1) as int];
    return LayoutBuilder(
      builder: (context, constraints) {
        final stacked = constraints.maxWidth < 980;
        final catalog = Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Eventi e degustazioni',
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [_chip('Dati reali'), _chip('Backend Laravel')],
                ),
                const SizedBox(height: 16),
                ...List.generate(events.length, (index) {
                  final event = events[index];
                  final selected = index == selectedIndex;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: InkWell(
                      onTap: () => onSelectEvent(index),
                      borderRadius: BorderRadius.circular(22),
                      child: Ink(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(22),
                          color: selected
                              ? const Color(0xFF6A1F2B).withValues(alpha: 0.08)
                              : Colors.white,
                          border: Border.all(
                            color: selected
                                ? const Color(0xFF6A1F2B)
                                : const Color(0xFFE7D9C6),
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(event.title,
                                style: const TextStyle(fontWeight: FontWeight.w700)),
                            const SizedBox(height: 4),
                            Text(event.summary,
                                maxLines: 2, overflow: TextOverflow.ellipsis),
                          ],
                        ),
                      ),
                    ),
                  );
                }),
              ],
            ),
          ),
        );
        final detail = Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(current.title,
                    style: const TextStyle(
                        fontSize: 24, fontWeight: FontWeight.w700)),
                const SizedBox(height: 10),
                Text(current.subtitle.isEmpty ? current.summary : current.subtitle),
                const SizedBox(height: 14),
                Text(_formatDate(current.startsAt),
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, color: Color(0xFF6A1F2B))),
                const SizedBox(height: 6),
                Text(current.venueName),
                const SizedBox(height: 6),
                Text(_formatPrice(current.price)),
                const SizedBox(height: 6),
                Text(_availabilityLabel(current)),
                const SizedBox(height: 18),
                Text(current.description),
              ],
            ),
          ),
        );
        if (stacked) {
          return ListView(children: [catalog, const SizedBox(height: 16), detail]);
        }
        return Row(
          children: [
            Expanded(child: catalog),
            const SizedBox(width: 16),
            Expanded(child: detail),
          ],
        );
      },
    );
  }
}

class _BookingsPage extends StatelessWidget {
  const _BookingsPage({
    required this.bookings,
    required this.isAuthenticated,
  });

  final List<ApiBooking> bookings;
  final bool isAuthenticated;

  @override
  Widget build(BuildContext context) {
    if (!isAuthenticated) {
      return const Center(child: Text('Accedi con il cliente demo per vedere prenotazioni reali.'));
    }

    return ListView(
      children: [
        const Card(
          child: Padding(
            padding: EdgeInsets.all(20),
            child: Text(
              'Le mie prenotazioni',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700),
            ),
          ),
        ),
        const SizedBox(height: 16),
        ...bookings.map(
          (booking) => Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Card(
              child: ListTile(
                title: Text(booking.event.title),
                subtitle: Text(
                  '${_formatDate(booking.event.startsAt)}  |  ${booking.seatsReserved} ospiti',
                ),
                trailing: _chip(booking.status),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _ProfilePage extends StatelessWidget {
  const _ProfilePage({
    required this.user,
    required this.isAuthenticated,
  });

  final ApiUser? user;
  final bool isAuthenticated;

  @override
  Widget build(BuildContext context) {
    if (!isAuthenticated || user == null) {
      return const Center(child: Text('Accedi con il cliente demo per vedere il profilo reale.'));
    }

    return ListView(
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Profilo cliente',
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 10),
                Text(user!.fullName),
                Text(user!.email),
                Text(user!.phone),
                const SizedBox(height: 12),
                Text(
                    '${user!.profile.ageRange}  |  ${user!.profile.originCity}  |  ${user!.profile.romeArea}'),
                const SizedBox(height: 8),
                Text('Preferenze food: ${user!.profile.foodPreferences.join(', ')}'),
                const SizedBox(height: 8),
                Text(
                    'Preferenze eventi: ${user!.profile.eventPreferences.join(', ')}'),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return _chip(label, background: Colors.white);
  }
}

Widget _chip(String text, {Color? background}) => Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: background ?? const Color(0xFFE7D9C6),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        text,
        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
      ),
    );

String _formatDate(DateTime? value) {
  if (value == null) {
    return 'Data da definire';
  }

  return '${value.day}/${value.month}/${value.year} ${value.hour.toString().padLeft(2, '0')}:${value.minute.toString().padLeft(2, '0')}';
}

String _formatPrice(double value) => '${value.toStringAsFixed(0)} euro a persona';

String _availabilityLabel(ApiEvent event) {
  if (event.bookingStatus == 'waitlist') {
    return 'Lista attesa';
  }
  if (event.availableSeats <= 0) {
    return 'Posti esauriti';
  }
  return '${event.availableSeats} posti rimasti';
}
