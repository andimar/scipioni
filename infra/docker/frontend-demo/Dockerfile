FROM debian:12-slim AS builder

ARG FLUTTER_CHANNEL=stable
ARG SCIPIONI_API_BASE_URL=https://api-staging.example.com/api/v1

ENV DEBIAN_FRONTEND=noninteractive
ENV FLUTTER_HOME=/opt/flutter
ENV PATH="${FLUTTER_HOME}/bin:${PATH}"

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    unzip \
    xz-utils \
    zip \
    libglu1-mesa \
    && rm -rf /var/lib/apt/lists/*

RUN git clone --depth 1 --branch ${FLUTTER_CHANNEL} https://github.com/flutter/flutter.git ${FLUTTER_HOME}

WORKDIR /app/frontend/app

COPY frontend/app /app/frontend/app

RUN flutter config --enable-web \
    && flutter pub get \
    && flutter build web --release --dart-define=SCIPIONI_API_BASE_URL=${SCIPIONI_API_BASE_URL}

FROM nginx:1.27-alpine

COPY infra/docker/frontend-demo/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /app/frontend/app/build/web /usr/share/nginx/html
