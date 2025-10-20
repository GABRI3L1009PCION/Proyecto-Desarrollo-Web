@extends('layouts.app')
@section('title', 'Inicio | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/welcome.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <!-- ===== HERO PRINCIPAL ===== -->
    <section class="hero">
        <div class="overlay"></div>

        <div class="hero-content">
            <img src="{{ asset('images/logo2.png') }}" alt="Código Rapidito" class="logo">
            <h1 class="title">CÓDIGO RAPIDITO</h1>
            <p class="subtitle">Transformando la gestión académica con innovación y tecnología ⚡</p>
            <button class="btn-enter" id="btn-enter">Entrar al Sistema</button>
        </div>

        <!-- Indicador de scroll -->
        <div class="scroll-indicator" id="scroll-indicator" onclick="window.scrollTo({top: window.innerHeight, behavior: 'smooth'});">
            <span>Desliza hacia abajo</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </section>

    <!-- ===== ACERCA DEL SISTEMA ===== -->
    <section class="about">
        <h2>¿Qué es Código Rapidito?</h2>
        <p>
            Es una plataforma académica desarrollada para optimizar la gestión de estudiantes, cursos, calificaciones y reportes
            dentro de instituciones educativas. Su enfoque combina rendimiento, seguridad y una interfaz moderna basada en
            los estándares de diseño web más actuales.
        </p>
    </section>

    <!-- ===== FUNCIONALIDADES ===== -->
    <section class="features">
        <h2>Principales Funcionalidades</h2>
        <div class="feature-grid">
            <div class="feature-item">
                <i class="fa-solid fa-user-graduate"></i>
                <h3>Gestión de Alumnos</h3>
                <p>Registra, edita y administra información de estudiantes en tiempo real.</p>
            </div>
            <div class="feature-item">
                <i class="fa-solid fa-book-open"></i>
                <h3>Control de Cursos</h3>
                <p>Gestiona materias, horarios y asignaciones de docentes fácilmente.</p>
            </div>
            <div class="feature-item">
                <i class="fa-solid fa-chart-line"></i>
                <h3>Reportes Inteligentes</h3>
                <p>Genera informes detallados sobre rendimiento académico y estadísticas.</p>
            </div>
        </div>
    </section>

    <!-- ===== TECNOLOGÍAS ===== -->
    <section class="tech">
        <h2>Tecnologías que impulsan el sistema</h2>
        <div class="tech-icons">
            <div><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg" alt="Laravel"><span>Laravel</span></div>
            <div><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="MySQL"><span>MySQL</span></div>
            <div><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" alt="HTML5"><span>HTML5</span></div>
            <div><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" alt="CSS3"><span>CSS3</span></div>
            <div><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JS"><span>JavaScript</span></div>
        </div>
    </section>

    <!-- ===== VISIÓN ===== -->
    <section class="vision">
        <h2>Nuestra Visión</h2>
        <p>
            Ser un referente en soluciones académicas inteligentes en Latinoamérica, aportando herramientas tecnológicas que
            fortalezcan la educación moderna y digital.
        </p>
    </section>

    <!-- ===== PIE FINAL ===== -->
    <footer>
        <p>© 2025 Código Rapidito — Desarrollado por <strong>Masturbanda</strong></p>
    </footer>

    <!-- ===== SCRIPT DE INTERACTIVIDAD ===== -->
    <script>
        // Ocultar flecha al hacer scroll
        window.addEventListener('scroll', () => {
            const indicator = document.getElementById('scroll-indicator');
            if (window.scrollY > 50) {
                indicator.style.opacity = '0';
                indicator.style.pointerEvents = 'none';
            } else {
                indicator.style.opacity = '1';
                indicator.style.pointerEvents = 'auto';
            }
        });

        // Movimiento parallax del fondo
        window.addEventListener('scroll', () => {
            const hero = document.querySelector('.hero');
            const scroll = window.scrollY;
            hero.style.backgroundPositionY = `${scroll * 0.4}px`;
        });

        // Movimiento de partículas según mouse
        document.addEventListener('mousemove', e => {
            document.body.style.setProperty('--mouseX', `${e.clientX}px`);
            document.body.style.setProperty('--mouseY', `${e.clientY}px`);
        });

        // Efecto aparición al hacer scroll
        const sections = document.querySelectorAll('section');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('show');
            });
        }, {threshold: 0.2});
        sections.forEach(sec => observer.observe(sec));

        // Redirección suave al login
        const btn = document.getElementById('btn-enter');
        btn.addEventListener('click', () => {
            btn.classList.add('clicked');
            setTimeout(() => window.location.href='/login', 700);
        });
    </script>
@endsection
