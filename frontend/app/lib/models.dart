class ApiEvent {
  const ApiEvent({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.summary,
    required this.description,
    required this.categoryName,
    required this.startsAt,
    required this.venueName,
    required this.availableSeats,
    required this.price,
    required this.bookingStatus,
    required this.isFeatured,
  });

  factory ApiEvent.fromJson(Map<String, dynamic> json) {
    final category = json['category'] as Map<String, dynamic>?;
    return ApiEvent(
      id: _asInt(json['id']),
      title: (json['title'] ?? '') as String,
      subtitle: (json['subtitle'] ?? '') as String,
      summary: (json['short_description'] ?? '') as String,
      description: (json['full_description'] ?? '') as String,
      categoryName: (category?['name'] ?? 'Evento') as String,
      startsAt: DateTime.tryParse((json['starts_at'] ?? '') as String),
      venueName: (json['venue_name'] ?? '') as String,
      availableSeats: _asInt(json['available_seats']),
      price: _asDouble(json['price']),
      bookingStatus: (json['booking_status'] ?? 'open') as String,
      isFeatured: (json['is_featured'] ?? false) as bool,
    );
  }

  final int id;
  final String title;
  final String subtitle;
  final String summary;
  final String description;
  final String categoryName;
  final DateTime? startsAt;
  final String venueName;
  final int availableSeats;
  final double price;
  final String bookingStatus;
  final bool isFeatured;
}

class ApiUserProfile {
  const ApiUserProfile({
    required this.gender,
    required this.ageRange,
    required this.originCity,
    required this.romeArea,
    required this.foodPreferences,
    required this.eventPreferences,
    required this.marketingConsent,
  });

  factory ApiUserProfile.fromJson(Map<String, dynamic>? json) {
    final source = json ?? <String, dynamic>{};
    return ApiUserProfile(
      gender: (source['gender'] ?? '') as String,
      ageRange: (source['age_range'] ?? '') as String,
      originCity: (source['origin_city'] ?? '') as String,
      romeArea: (source['rome_area'] ?? '') as String,
      foodPreferences: ((source['food_preferences'] ?? const <dynamic>[]) as List)
          .map((value) => value.toString())
          .toList(),
      eventPreferences:
          ((source['event_preferences'] ?? const <dynamic>[]) as List)
              .map((value) => value.toString())
              .toList(),
      marketingConsent: (source['marketing_consent'] ?? false) as bool,
    );
  }

  final String gender;
  final String ageRange;
  final String originCity;
  final String romeArea;
  final List<String> foodPreferences;
  final List<String> eventPreferences;
  final bool marketingConsent;
}

class ApiUser {
  const ApiUser({
    required this.id,
    required this.fullName,
    required this.email,
    required this.phone,
    required this.profile,
  });

  factory ApiUser.fromJson(Map<String, dynamic> json) {
    return ApiUser(
      id: _asInt(json['id']),
      fullName: (json['full_name'] ?? '') as String,
      email: (json['email'] ?? '') as String,
      phone: (json['phone'] ?? '') as String,
      profile: ApiUserProfile.fromJson(json['profile'] as Map<String, dynamic>?),
    );
  }

  final int id;
  final String fullName;
  final String email;
  final String phone;
  final ApiUserProfile profile;
}

class ApiBooking {
  const ApiBooking({
    required this.id,
    required this.status,
    required this.seatsReserved,
    required this.createdAt,
    required this.event,
  });

  factory ApiBooking.fromJson(Map<String, dynamic> json) {
    return ApiBooking(
      id: _asInt(json['id']),
      status: (json['status'] ?? '') as String,
      seatsReserved: _asInt(json['seats_reserved'], fallback: 1),
      createdAt: DateTime.tryParse((json['created_at'] ?? '') as String),
      event: ApiEvent.fromJson(json['event'] as Map<String, dynamic>),
    );
  }

  final int id;
  final String status;
  final int seatsReserved;
  final DateTime? createdAt;
  final ApiEvent event;
}

class LoginResult {
  const LoginResult({
    required this.token,
    required this.user,
  });

  final String token;
  final ApiUser user;
}

int _asInt(Object? value, {int fallback = 0}) {
  if (value is int) {
    return value;
  }
  if (value is num) {
    return value.toInt();
  }
  return int.tryParse(value?.toString() ?? '') ?? fallback;
}

double _asDouble(Object? value, {double fallback = 0}) {
  if (value is double) {
    return value;
  }
  if (value is num) {
    return value.toDouble();
  }
  return double.tryParse(value?.toString() ?? '') ?? fallback;
}
