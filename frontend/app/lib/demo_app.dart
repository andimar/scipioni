import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

import 'api_client.dart';
import 'models.dart';

class ScipioniDemoApp extends StatelessWidget {
  const ScipioniDemoApp({super.key});

  @override
  Widget build(BuildContext context) {
    const antiqueCream = Color(0xFFFBF9F5);
    const deepBurgundy = Color(0xFF2A0002);
    const brass = Color(0xFF775A19);
    const ink = Color(0xFF1B1C1A);

    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Magazzino Scipioni Club',
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: antiqueCream,
        colorScheme: const ColorScheme.light(
          primary: deepBurgundy,
          secondary: brass,
          surface: antiqueCream,
          onSurface: ink,
          onPrimary: Colors.white,
        ),
        textTheme: const TextTheme(
          displayLarge: TextStyle(
            fontFamily: 'Georgia',
            fontSize: 54,
            height: 1.02,
            fontWeight: FontWeight.w400,
            color: deepBurgundy,
          ),
          headlineLarge: TextStyle(
            fontFamily: 'Georgia',
            fontSize: 40,
            height: 1.08,
            fontWeight: FontWeight.w400,
            color: deepBurgundy,
          ),
          headlineMedium: TextStyle(
            fontFamily: 'Georgia',
            fontSize: 28,
            height: 1.15,
            fontWeight: FontWeight.w400,
            color: deepBurgundy,
          ),
          bodyLarge: TextStyle(
            fontSize: 18,
            height: 1.5,
            color: ink,
          ),
          bodyMedium: TextStyle(
            fontSize: 15,
            height: 1.55,
            color: Color(0xFF544341),
          ),
          labelSmall: TextStyle(
            fontSize: 11,
            letterSpacing: 2,
            fontWeight: FontWeight.w700,
            color: brass,
          ),
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
    final currentEvent = _events.isEmpty ? null : _events[selectedEventIndex];

    final pages = [
      _HomePage(
        loading: _loading,
        featuredEvent: currentEvent,
        events: _events,
        bookings: _bookings,
        isAuthenticated: _token != null,
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
            colors: [Color(0xFFFDFBF7), Color(0xFFF3ECE3)],
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
                  padding: const EdgeInsets.fromLTRB(24, 0, 24, 12),
                  child: Container(
                    decoration: BoxDecoration(
                      color: const Color(0xFFFBEBEB),
                      border: Border.all(color: const Color(0xFFBA1A1A)),
                    ),
                    padding: const EdgeInsets.all(14),
                    child: Row(
                      children: [
                        const Icon(Icons.error_outline, color: Color(0xFFBA1A1A)),
                        const SizedBox(width: 10),
                        Expanded(child: Text(_errorMessage!)),
                      ],
                    ),
                  ),
                ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(24, 4, 24, 24),
                  child: pages[_index],
                ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: NavigationBarTheme(
        data: NavigationBarThemeData(
          backgroundColor: const Color(0xFFFDFBF7).withValues(alpha: 0.95),
          indicatorColor: Colors.transparent,
          labelTextStyle: WidgetStateProperty.resolveWith(
            (states) => TextStyle(
              fontSize: 10,
              letterSpacing: 1.8,
              fontWeight: states.contains(WidgetState.selected)
                  ? FontWeight.w700
                  : FontWeight.w500,
              color: states.contains(WidgetState.selected)
                  ? const Color(0xFF2A0002)
                  : const Color(0xFF877270),
            ),
          ),
        ),
        child: NavigationBar(
          selectedIndex: _index,
          onDestinationSelected: (value) => setState(() => _index = value),
          destinations: const [
            NavigationDestination(icon: Icon(Icons.home_outlined), label: 'HOME'),
            NavigationDestination(icon: Icon(Icons.wine_bar_outlined), label: 'EVENTI'),
            NavigationDestination(
                icon: Icon(Icons.event_available_outlined), label: 'BOOKING'),
            NavigationDestination(icon: Icon(Icons.person_outline), label: 'PROFILO'),
          ],
        ),
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
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 16),
      child: Container(
        decoration: BoxDecoration(
          border: Border.all(color: const Color(0xFFD7C6BF)),
          color: const Color(0xFFFDFBF7),
          boxShadow: const [
            BoxShadow(
              color: Color.fromRGBO(74, 14, 14, 0.07),
              blurRadius: 32,
              offset: Offset(0, 16),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Wrap(
            spacing: 20,
            runSpacing: 18,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              const _LogoLockup(),
              SizedBox(
                width: 280,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Club riservato e degustazioni private',
                      style: theme.textTheme.labelSmall,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Demo collegata al backend Laravel reale.',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: const Color(0xFF544341),
                      ),
                    ),
                  ],
                ),
              ),
              _InfoChip(label: 'API ${apiBaseUrl.replaceFirst('http://', '').replaceFirst('https://', '')}'),
              if (user != null)
                _InfoChip(label: 'Membro ${user!.fullName}')
              else ...[
                _EditorialField(
                  width: 240,
                  label: 'Email demo',
                  child: TextField(
                    controller: emailController,
                    decoration: const InputDecoration(border: InputBorder.none),
                  ),
                ),
                _EditorialField(
                  width: 180,
                  label: 'Password',
                  child: TextField(
                    controller: passwordController,
                    obscureText: true,
                    decoration: const InputDecoration(border: InputBorder.none),
                  ),
                ),
                _PrimaryButton(
                  label: authLoading ? 'ACCESSO...' : 'ACCEDI',
                  onPressed: authLoading ? null : onLogin,
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
    required this.bookings,
    required this.isAuthenticated,
    required this.onOpenEvents,
    required this.onRetry,
  });

  final bool loading;
  final ApiEvent? featuredEvent;
  final List<ApiEvent> events;
  final List<ApiBooking> bookings;
  final bool isAuthenticated;
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

    final theme = Theme.of(context);
    return ListView(
      children: [
        Container(
          constraints: const BoxConstraints(minHeight: 420),
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF1F1F1A), Color(0xFF5A6758), Color(0xFF7A7A3E)],
            ),
          ),
          child: Stack(
            children: [
              Positioned(
                right: 22,
                top: 36,
                child: Text(
                  'RISERVATO',
                  style: theme.textTheme.labelSmall?.copyWith(
                    color: Colors.white.withValues(alpha: 0.35),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(28, 32, 28, 36),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 8),
                    const SizedBox(
                      width: 56,
                      child: _BrandWordmark(inverted: true),
                    ),
                    const SizedBox(height: 28),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _tag('ESPERIENZA ESCLUSIVA', inverted: true),
                        _tag(featuredEvent!.categoryName.toUpperCase(), inverted: true),
                      ],
                    ),
                    const SizedBox(height: 20),
                    ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 560),
                      child: Text(
                        featuredEvent!.title,
                        style: theme.textTheme.displayLarge?.copyWith(
                          color: Colors.white,
                          fontSize: 56,
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 560),
                      child: Text(
                        featuredEvent!.summary,
                        style: theme.textTheme.bodyLarge?.copyWith(
                          color: const Color(0xFFF2F0ED),
                        ),
                      ),
                    ),
                    const SizedBox(height: 28),
                    Wrap(
                      spacing: 14,
                      runSpacing: 14,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        _PrimaryButton(
                          label: 'PRENOTA ORA',
                          onPressed: onOpenEvents,
                        ),
                        _DatePlate(
                          label:
                              '${_formatDate(featuredEvent!.startsAt)}  ·  ${_formatPrice(featuredEvent!.price)}',
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 36),
        if (isAuthenticated && bookings.isNotEmpty) ...[
          _SectionHeader(
            title: 'Prossime prenotazioni',
            actionLabel: 'AREA RISERVATA',
          ),
          const SizedBox(height: 18),
          ...bookings.take(2).map(
                (booking) => Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: _BookingEditorialCard(booking: booking),
                ),
              ),
          const SizedBox(height: 36),
        ],
        _SectionHeader(
          title: 'Consigliati per te',
          actionLabel: 'CATALOGO',
        ),
        const SizedBox(height: 18),
        Wrap(
          spacing: 22,
          runSpacing: 22,
          children: events
              .take(3)
              .map(
                (event) => SizedBox(
                  width: 320,
                  child: _EventEditorialCard(
                    event: event,
                    compact: true,
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
        final left = Container(
          decoration: _panelDecoration(),
          padding: const EdgeInsets.all(22),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const _SectionHeader(title: 'Eventi esclusivi', actionLabel: 'DATI REALI'),
              const SizedBox(height: 18),
              ...List.generate(events.length, (index) {
                final event = events[index];
                final selected = index == selectedIndex;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 14),
                  child: InkWell(
                    onTap: () => onSelectEvent(index),
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        border: Border.all(
                          color: selected
                              ? const Color(0xFF775A19)
                              : const Color(0xFFD7C6BF),
                        ),
                        color: selected
                            ? const Color(0xFF4A0E0E).withValues(alpha: 0.04)
                            : Colors.transparent,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            event.title,
                            style: Theme.of(context)
                                .textTheme
                                .headlineMedium
                                ?.copyWith(fontSize: 23),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            event.summary,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            _formatDate(event.startsAt),
                            style: Theme.of(context).textTheme.labelSmall,
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
            ],
          ),
        );

        final right = _EventEditorialCard(event: current);

        if (stacked) {
          return ListView(children: [left, const SizedBox(height: 18), right]);
        }

        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: left),
            const SizedBox(width: 20),
            Expanded(child: right),
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
        const _SectionHeader(title: 'Le mie prenotazioni', actionLabel: 'MEMBER CLUB'),
        const SizedBox(height: 18),
        ...bookings.map(
          (booking) => Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: _BookingEditorialCard(booking: booking),
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
        const _SectionHeader(title: 'Profilo membro', actionLabel: 'PRIVATE AREA'),
        const SizedBox(height: 18),
        Container(
          decoration: _panelDecoration(),
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(user!.fullName, style: Theme.of(context).textTheme.headlineLarge),
              const SizedBox(height: 10),
              Text(user!.email),
              Text(user!.phone),
              const SizedBox(height: 24),
              Wrap(
                spacing: 10,
                runSpacing: 10,
                children: [
                  _tag(user!.profile.ageRange.toUpperCase()),
                  _tag(user!.profile.originCity.toUpperCase()),
                  _tag(user!.profile.romeArea.toUpperCase()),
                ],
              ),
              const SizedBox(height: 22),
              _profileBlock(
                'Preferenze gastronomiche',
                user!.profile.foodPreferences.join(' · '),
              ),
              const SizedBox(height: 16),
              _profileBlock(
                'Preferenze eventi',
                user!.profile.eventPreferences.join(' · '),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _profileBlock(String title, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title.toUpperCase(),
          style: const TextStyle(
            fontSize: 11,
            letterSpacing: 1.8,
            fontWeight: FontWeight.w700,
            color: Color(0xFF775A19),
          ),
        ),
        const SizedBox(height: 8),
        Text(value),
      ],
    );
  }
}

class _LogoLockup extends StatelessWidget {
  const _LogoLockup();

  @override
  Widget build(BuildContext context) {
    return const SizedBox(
      width: 210,
      child: _BrandWordmark(),
    );
  }
}

class _BrandWordmark extends StatelessWidget {
  const _BrandWordmark({this.inverted = false});

  final bool inverted;

  @override
  Widget build(BuildContext context) {
    return SvgPicture.asset(
      'assets/brand/logo.svg',
      colorFilter: ColorFilter.mode(
        inverted ? Colors.white : const Color(0xFF2A0002),
        BlendMode.srcIn,
      ),
    );
  }
}

class _EditorialField extends StatelessWidget {
  const _EditorialField({
    required this.width,
    required this.label,
    required this.child,
  });

  final double width;
  final String label;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Container(
        decoration: const BoxDecoration(
          border: Border(
            bottom: BorderSide(color: Color(0xFF544341), width: 1),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label.toUpperCase(),
              style: Theme.of(context).textTheme.labelSmall,
            ),
            child,
          ],
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFFD7C6BF)),
        color: const Color(0xFFF7F3EE),
      ),
      child: Text(
        label.toUpperCase(),
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: const Color(0xFF544341),
            ),
      ),
    );
  }
}

class _PrimaryButton extends StatelessWidget {
  const _PrimaryButton({required this.label, required this.onPressed});

  final String label;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return FilledButton(
      style: FilledButton.styleFrom(
        backgroundColor: const Color(0xFF4A0E0E),
        foregroundColor: const Color(0xFFFBF9F5),
        shape: const RoundedRectangleBorder(),
        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 16),
      ),
      onPressed: onPressed,
      child: Text(
        label,
        style: const TextStyle(
          letterSpacing: 1.4,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title, required this.actionLabel});

  final String title;
  final String actionLabel;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Expanded(
          child: Text(
            title,
            style: Theme.of(context).textTheme.headlineLarge,
          ),
        ),
        Text(
          actionLabel,
          style: Theme.of(context).textTheme.labelSmall,
        ),
      ],
    );
  }
}

class _EventEditorialCard extends StatelessWidget {
  const _EventEditorialCard({
    required this.event,
    this.compact = false,
  });

  final ApiEvent event;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: _panelDecoration(),
      padding: EdgeInsets.all(compact ? 18 : 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            event.categoryName.toUpperCase(),
            style: Theme.of(context).textTheme.labelSmall,
          ),
          const SizedBox(height: 10),
          Text(
            event.title,
            style: Theme.of(context)
                .textTheme
                .headlineMedium
                ?.copyWith(fontSize: compact ? 24 : 30),
          ),
          const SizedBox(height: 10),
          Text(event.subtitle.isEmpty ? event.summary : event.subtitle),
          const SizedBox(height: 18),
          _metricRow(Icons.schedule_outlined, _formatDate(event.startsAt)),
          const SizedBox(height: 8),
          _metricRow(Icons.location_on_outlined, event.venueName),
          const SizedBox(height: 8),
          _metricRow(Icons.wine_bar_outlined, _formatPrice(event.price)),
          const SizedBox(height: 8),
          _metricRow(Icons.workspace_premium_outlined, _availabilityLabel(event)),
          if (!compact) ...[
            const SizedBox(height: 18),
            Text(event.description),
          ],
        ],
      ),
    );
  }

  Widget _metricRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 18, color: const Color(0xFF775A19)),
        const SizedBox(width: 10),
        Expanded(child: Text(text)),
      ],
    );
  }
}

class _BookingEditorialCard extends StatelessWidget {
  const _BookingEditorialCard({required this.booking});

  final ApiBooking booking;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: _panelDecoration(),
      padding: const EdgeInsets.all(22),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            booking.status.toUpperCase(),
            style: Theme.of(context).textTheme.labelSmall,
          ),
          const SizedBox(height: 12),
          Text(
            booking.event.title,
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          const SizedBox(height: 14),
          Text(_formatDate(booking.event.startsAt)),
          const SizedBox(height: 6),
          Text('${booking.seatsReserved} ospiti'),
        ],
      ),
    );
  }
}

class _DatePlate extends StatelessWidget {
  const _DatePlate({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
      ),
      child: Text(
        label.toUpperCase(),
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: const Color(0xFFF2F0ED),
            ),
      ),
    );
  }
}

BoxDecoration _panelDecoration() => const BoxDecoration(
      color: Color(0xFFFBF9F5),
      boxShadow: [
        BoxShadow(
          color: Color.fromRGBO(74, 14, 14, 0.07),
          blurRadius: 30,
          offset: Offset(0, 14),
        ),
      ],
    );

Widget _tag(String text, {bool inverted = false}) => Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        border: Border.all(
          color: inverted
              ? Colors.white.withValues(alpha: 0.28)
              : const Color(0xFFD7C6BF),
        ),
        color: inverted
            ? Colors.white.withValues(alpha: 0.04)
            : const Color(0xFFF7F3EE),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 11,
          letterSpacing: 1.8,
          fontWeight: FontWeight.w700,
          color: inverted ? Colors.white : const Color(0xFF775A19),
        ),
      ),
    );

String _formatDate(DateTime? value) {
  if (value == null) {
    return 'Data da definire';
  }

  return '${value.day}/${value.month}/${value.year} ${value.hour.toString().padLeft(2, '0')}:${value.minute.toString().padLeft(2, '0')}';
}

String _formatPrice(double value) => '€ ${value.toStringAsFixed(2)}';

String _availabilityLabel(ApiEvent event) {
  if (event.bookingStatus == 'waitlist') {
    return 'Lista attesa';
  }
  if (event.availableSeats <= 0) {
    return 'Posti esauriti';
  }
  return '${event.availableSeats} posti rimasti';
}
