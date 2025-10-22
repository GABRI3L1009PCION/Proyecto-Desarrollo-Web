@extends('layouts.app')
@section('title', 'Perfil pendiente de activación | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Estudiante</p>
            </div>

            <ul class="menu">
                <li class="menu-item active">
                    <a href="#"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a>
                </li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-circle-info"></i> Perfil pendiente</h2>
                <p class="welcome">Bienvenido, <strong>{{ $user->name }}</strong></p>
            </header>

            <section class="empty-profile">
                <div class="info-box">
                    <i class="fa-solid fa-user-clock"></i>
                    <h3>Tu perfil de estudiante aún no está activado</h3>
                    <p>
                        Has ingresado correctamente al sistema, pero todavía no has sido registrado
                        oficialmente como estudiante por la administración.
                        <br><br>
                        Una vez que tu cuenta sea vinculada a un registro en la base de datos de alumnos,
                        podrás acceder a tus cursos, calificaciones y reportes académicos.
                    </p>
                </div>
            </section>
        </main>
    </div>

    <style>
        /* === ESTILOS ESPECÍFICOS DE ESTA VISTA === */
        .empty-profile {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
        }
        .info-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            color: var(--primary-light);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 700px;
        }
        .info-box i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        .info-box h3 {
            font-weight: 600;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }
        .info-box p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }
    </style>
@endsection
