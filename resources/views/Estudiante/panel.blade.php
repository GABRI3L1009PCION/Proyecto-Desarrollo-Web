@extends('layouts.app')
@section('title', 'Panel del Estudiante | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/student_dashboard.css') }}">

    <div class="dashboard-container">
        <!-- 🌟 Encabezado -->
        <header class="dashboard-header">
            <h2>🎓 Bienvenido, {{ Auth::user()->name }}</h2>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </button>
            </form>
        </header>

        <!-- 🧭 Navegación -->
        <nav class="dashboard-nav">
            <a href="#" class="nav-link active"><i class="fa-solid fa-house"></i> Inicio</a>
            <a href="#"><i class="fa-solid fa-book"></i> Mis Cursos</a>
            <a href="#"><i class="fa-solid fa-file-lines"></i> Tareas</a>
            <a href="#"><i class="fa-solid fa-graduation-cap"></i> Calificaciones</a>
            <a href="#"><i class="fa-solid fa-envelope"></i> Mensajes</a>
        </nav>

        <!-- 📊 Contenido principal -->
        <main class="dashboard-content">
            <div class="card welcome-card">
                <h3>¡Hola {{ Auth::user()->name }}!</h3>
                <p>Bienvenido al portal del estudiante de <strong>Código Rapidito</strong>.
                    Aquí podrás acceder a tus cursos, tareas, calificaciones y más.</p>
            </div>

            <div class="grid">
                <div class="card">
                    <h4><i class="fa-solid fa-book"></i> Cursos activos</h4>
                    <p>Explora los cursos en los que estás inscrito y revisa tu progreso.</p>
                </div>
                <div class="card">
                    <h4><i class="fa-solid fa-file-lines"></i> Últimas tareas</h4>
                    <p>Consulta tus entregas más recientes y verifica tus notas.</p>
                </div>
                <div class="card">
                    <h4><i class="fa-solid fa-calendar-days"></i> Próximos eventos</h4>
                    <p>Mantente al tanto de las fechas importantes del ciclo académico.</p>
                </div>
            </div>
        </main>
    </div>

@endsection
