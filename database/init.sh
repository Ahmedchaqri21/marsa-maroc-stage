#!/bin/bash
# Database initialization script for Docker
# This script runs automatically when the MySQL container starts

echo "Initializing Marsa Maroc database..."

# Wait for MySQL to be ready
sleep 10

# The schema.sql file will be automatically executed by MySQL container
# This file just ensures proper initialization order

echo "Database initialization completed!"
