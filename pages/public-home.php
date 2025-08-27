<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsa Maroc - Leader de la Gestion Portuaire</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 50%, #3182ce 100%);
            color: white;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a365d;
            font-size: 24px;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-menu a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(26, 54, 93, 0.9), rgba(49, 130, 206, 0.8)), 
                        url('assets/images/SNY00118-Edit_1.jpg') center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.05) 50%, transparent 70%);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes shine {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        .hero-logo {
            width: 120px;
            height: auto;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-content {
            max-width: 800px;
            padding: 2rem;
            z-index: 2;
            position: relative;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.6);
        }

        /* Services Section */
        .services {
            padding: 100px 0;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 3rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 1rem;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .section-title p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .service-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .service-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .service-card p {
            color: #666;
            line-height: 1.8;
        }

        /* Gallery Section */
        .gallery {
            padding: 6rem 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(26, 54, 93, 0.9));
            color: white;
            padding: 2rem 1.5rem 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        .gallery-overlay h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .gallery-overlay p {
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.4;
        }

        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            padding: 80px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            color: #ff6b35;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #1a202c;
            color: white;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #ff6b35;
        }

        .footer-section a {
            color: #cbd5e0;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ff6b35;
        }

        .footer-bottom {
            border-top: 1px solid #2d3748;
            padding-top: 2rem;
            text-align: center;
            color: #a0aec0;
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                padding: 1rem;
            }
            
            .logo-text {
                font-size: 1.4rem;
            }
            
            .nav-menu {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .service-card {
                padding: 2rem;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .gallery-item img {
                height: 200px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="assets/images/unnamed.png" alt="Marsa Maroc Logo" class="logo-img" style="height: 50px;">
            </div>
            <nav class="nav-menu">
                <a href="#accueil" class="active">Accueil</a>
                <a href="#services">Services</a>
                <a href="#galerie">Galerie</a>
                <a href="#contact">Contact</a>
            </nav>
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="accueil">
        <div class="hero-content">
            <h1>Leader Portuaire Marocain</h1>
            <p>Excellence, Innovation et Performance dans la gestion portuaire et les services maritimes. Votre partenaire de confiance pour toutes vos opérations logistiques.</p>
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-compass"></i>
                    Découvrir nos Services
                </a>
                <a href="#contact" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i>
                    Nous Contacter
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-title">
                <h2>Nos Services</h2>
                <p>Des solutions complètes et innovantes pour répondre à tous vos besoins maritimes et portuaires</p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-ship"></i>
                    </div>
                    <h3>Gestion de Terminaux</h3>
                    <p>Exploitation et gestion de 3 terminaux et quais spécialisés avec une infrastructure moderne de 53.720 m² de terres-pleins.</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <h3>Manutention de Minerais</h3>
                    <p>Spécialistes dans la manutention de minerais, principalement le soufre et le gypse, avec un traitement de près de 3 millions de tonnes par an.</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-anchor"></i>
                    </div>
                    <h3>Services aux Navires</h3>
                    <p>Services complets de remorquage et pilotage pour accompagner les navires dans leurs opérations portuaires en toute sécurité.</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3>Entreposage Spécialisé</h3>
                    <p>Solutions de stockage sécurisées adaptées aux minerais et marchandises en vrac avec une capacité d'infrastructure optimisée.</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <h3>Logistique Portuaire</h3>
                    <p>Chaîne logistique intégrée pour l'import-export de minerais avec traçabilité complète et optimisation des flux.</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Performance & Innovation</h3>
                    <p>Excellence opérationnelle dans le traitement de 3 millions de tonnes annuelles avec des standards de qualité internationaux.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="galerie">
        <div class="container">
            <div class="section-title">
                <h2>Notre Infrastructure</h2>
                <p>Découvrez nos installations portuaires modernes et notre expertise dans la gestion maritime</p>
            </div>
            
            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="assets/images/LY2A4042-min.jpg" alt="Port de Safi - Terminal à conteneurs Marsa Maroc">
                    <div class="gallery-overlay">
                        <h3>Terminal à Conteneurs Marsa Maroc</h3>
                        <p>Vue aérienne de notre terminal à conteneurs avec grues portiques et infrastructure moderne pour la manutention efficace des marchandises.</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="assets/images/port-ships.jpg" alt="Services aux navires - Port Marsa Maroc">
                    <div class="gallery-overlay">
                        <h3>Services aux Navires</h3>
                        <p>Accueil et assistance complète des navires commerciaux avec services de remorquage et pilotage dans les ports marocains.</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="assets/images/download.jpg" alt="Grues portuaires - Équipements Marsa Maroc">
                    <div class="gallery-overlay">
                        <h3>Équipements Portuaires</h3>
                        <p>Grues de déchargement et équipements de manutention spécialisés pour le traitement des minerais et marchandises en vrac.</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="assets/images/port-containers.jpg" alt="Stockage de conteneurs - Port Marsa Maroc">
                    <div class="gallery-overlay">
                        <h3>Stockage de Minerais</h3>
                        <p>Zones de stockage spécialisées pour le soufre et le gypse sur nos 53.720 m² de terres-pleins sécurisées au port de Safi.</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="assets/images/port-container-terminal.jpg" alt="Logistique portuaire - Terminal Marsa Maroc">
                    <div class="gallery-overlay">
                        <h3>Logistique Intégrée</h3>
                        <p>Chaîne logistique maritime complète avec traçabilité et optimisation des flux pour nos 3 millions de tonnes annuelles.</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="assets/images/centre-controle-operationnel.jpg" alt="Centre de contrôle - Technologie Marsa Maroc">
                    <div class="gallery-overlay">
                        <h3>Centre de Contrôle</h3>
                        <p>Technologie avancée de monitoring et contrôle des opérations portuaires pour une gestion optimale 24h/24.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>3</h3>
                    <p>Terminaux & Quais Gérés</p>
                </div>
                <div class="stat-item">
                    <h3>3M</h3>
                    <p>Tonnes/An</p>
                </div>
                <div class="stat-item">
                    <h3>53.720</h3>
                    <p>m² de Terres-pleins</p>
                </div>
                <div class="stat-item">
                    <h3>2</h3>
                    <p>Principaux Minerais</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Marsa Maroc</h3>
                    <p>Leader de la gestion portuaire au Maroc, nous nous engageons à fournir des services de classe mondiale avec innovation et excellence.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Services</h3>
                    <a href="#">Gestion de Terminaux</a>
                    <a href="#">Manutention de Minerais</a>
                    <a href="#">Remorquage et Pilotage</a>
                    <a href="#">Entreposage Spécialisé</a>
                </div>
                
                <div class="footer-section">
                    <h3>Contact</h3>
                    <a href="#"><i class="fas fa-phone"></i> +212 5 24 46 23 90</a>
                    <a href="#"><i class="fas fa-envelope"></i> safi@marsamaroc.co.ma</a>
                    <a href="#"><i class="fas fa-map-marker-alt"></i> Safi, Maroc</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Marsa Maroc. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header background on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(26, 54, 93, 0.95)';
            } else {
                header.style.background = 'linear-gradient(135deg, #1a365d 0%, #2c5282 50%, #3182ce 100%)';
            }
        });

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-item h3');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = parseInt(counter.textContent.replace(/\D/g, ''));
                        const suffix = counter.textContent.replace(/[\d\s]/g, '');
                        let count = 0;
                        const increment = target / 100;
                        
                        const timer = setInterval(() => {
                            count += increment;
                            if (count >= target) {
                                counter.textContent = target + suffix;
                                clearInterval(timer);
                            } else {
                                counter.textContent = Math.floor(count) + suffix;
                            }
                        }, 20);
                        
                        observer.unobserve(counter);
                    }
                });
            });
            
            counters.forEach(counter => observer.observe(counter));
        }

        // Initialize counter animation
        animateCounters();

        // Service cards hover effect
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Gallery items animation on scroll
        function animateGalleryItems() {
            const galleryItems = document.querySelectorAll('.gallery-item');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            galleryItems.forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(30px)';
                item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(item);
            });
        }

        // Initialize gallery animation
        document.addEventListener('DOMContentLoaded', animateGalleryItems);

        // Navigation active state update on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-menu a');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>