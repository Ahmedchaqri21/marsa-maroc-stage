#!/bin/bash
# Docker startup script for Marsa Maroc Port Management System

echo "🚀 Starting Marsa Maroc Port Management System..."

# Function to check if MySQL is ready
wait_for_mysql() {
    echo "⏳ Waiting for MySQL database to be ready..."
    until docker-compose exec mysql mysqladmin ping -h"localhost" -u"root" -p"root_password_123" --silent; do
        echo "   MySQL is unavailable - sleeping"
        sleep 2
    done
    echo "✅ MySQL is ready!"
}

# Function to check if containers are running
check_containers() {
    echo "📊 Checking container status..."
    docker-compose ps
}

# Main startup sequence
main() {
    # Stop any existing containers
    echo "🛑 Stopping existing containers..."
    docker-compose down

    # Start MySQL first
    echo "🗄️ Starting MySQL database..."
    docker-compose up -d mysql

    # Wait for MySQL to be ready
    wait_for_mysql

    # Start remaining services
    echo "🌐 Starting web application and phpMyAdmin..."
    docker-compose up -d

    # Wait a moment for services to stabilize
    sleep 5

    # Check final status
    check_containers

    echo ""
    echo "🎉 Marsa Maroc application started successfully!"
    echo ""
    echo "📋 Access URLs:"
    echo "   🌐 Web Application: http://localhost:8080"
    echo "   🗄️ phpMyAdmin: http://localhost:8081"
    echo "   📊 Database: localhost:3306"
    echo ""
    echo "🔑 Database Credentials:"
    echo "   Database: gestion_operations_portuaires"
    echo "   Username: marsa_user"
    echo "   Password: marsa_password_123"
    echo "   Root Password: root_password_123"
    echo ""
    echo "🔧 Useful Commands:"
    echo "   View logs: docker-compose logs -f"
    echo "   Stop services: docker-compose down"
    echo "   Restart: docker-compose restart"
}

# Run main function
main
