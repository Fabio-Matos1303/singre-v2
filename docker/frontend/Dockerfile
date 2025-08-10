# Multi-stage Dockerfile for Vue (Vite) frontend

# Build stage (used when a real Vue project exists)
FROM node:20-alpine AS builder
ENV NODE_ENV=production
WORKDIR /app

# Copy dependency manifests first for better layer caching
COPY ./frontend/package*.json ./
RUN if [ -f package-lock.json ]; then npm ci; elif [ -f package.json ]; then npm install; fi

# Copy the rest of the source and build
COPY ./frontend/ ./
RUN if [ -f package.json ]; then npm run build; fi

# Runtime: Nginx serving either built assets or static placeholders
FROM nginx:1.27-alpine AS final-static
COPY ./docker/nginx/frontend.conf /etc/nginx/conf.d/default.conf
COPY ./frontend /usr/share/nginx/html

FROM nginx:1.27-alpine AS final-built
COPY ./docker/nginx/frontend.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /app/dist /usr/share/nginx/html
