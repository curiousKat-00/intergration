# ---- Stage 1: Build the React frontend ----
# Use an official Node.js runtime as a parent image
FROM node:18-alpine as builder

# Set the working directory
WORKDIR /app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application code
COPY . .

# Build the React app for production
RUN npm run build


# ---- Stage 2: Setup the production PHP server ----
# Use an official PHP image with Apache
FROM php:8.1-apache

# Install the cURL extension needed for the PayFast ITN script
RUN docker-php-ext-install curl

# Enable Apache's rewrite module for .htaccess to work
RUN a2enmod rewrite

# Set the working directory for Apache
WORKDIR /var/www/html

# Remove the default Apache content and copy our application
RUN rm -rf /var/www/html/*
COPY --from=builder /app/build /var/www/html/
COPY --from=builder /app/api /var/www/html/api/
COPY --from=builder /app/.htaccess /var/www/html/