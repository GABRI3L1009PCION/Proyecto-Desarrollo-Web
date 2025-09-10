{{-- resources/views/Administrador/panel.blade.php --}}
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary-color:#3c4b64; --secondary-color:#4e5d78; --accent-color:#e63946; }
        body{ background:#f5f7fb; }
        .sidebar{ background:var(--primary-color); color:#fff; height:100vh; position:fixed; padding-top:60px; }
        .sidebar .nav-link{ color:rgba(255,255,255,.85); }
        .sidebar .nav-link.active{ background:var(--accent-color); color:#fff; }
        .main-content{ margin-left:250px; padding:20px; padding-top:80px; }
        .navbar{ background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.08); position:fixed; width:calc(100% - 250px); margin-left:250px; z-index:1000; }
        .card{ border:none; box-shadow:0 4px 6px rgba(0,0,0,.05); border-radius:10px; }
        .kpi-card{ text-align:center; }
        .kpi-value{ font-size:28px; font-weight:800; color:var(--primary-color); }
        .kpi-label{ color:#6c757d; }
        .section{ display:none; } .section.active{ display:block; }
        .chart-container{ position:relative; height:300px; }
    </style>
</head>
<body>
{{-- Sidebar --}}
<div class="sidebar" style="width:250px;">
    <div class="d-flex justify-content-center mb-4">
        <h4 class="text-white">Sistema Educativo</h4>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link active" href="#" data-section="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-section="branches"><i class="bi bi-building"></i> Sucursales</a></li>
        {{-- agrega aquí más secciones cuando tengas vistas/CRUDs --}}
    </ul>
</div>

{{-- Topbar --}}
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://via.placeholder.com/32" alt="Admin" class="rounded-circle me-2">
                    <span>{{ auth()->user()->name ?? 'Administrador' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="#">Perfil</a></li>
                    <li><a class="dropdown-item" href="#">Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

{{-- Main --}}
<div class="main-content">
    {{-- DASHBOARD --}}
    <div id="dashboard" class="section active">
        <h2 class="mb-4">Dashboard</h2>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card kpi-card"><div class="card-body">
                        <div class="kpi-value">{{ number_format($kpi['branches']) }}</div>
                        <div class="kpi-label">Total de Sucursales</div>
                    </div></div>
            </div>
            <div class="col-md-3">
                <div class="card kpi-card"><div class="card-body">
                        <div class="kpi-value">{{ number_format($kpi['courses']) }}</div>
                        <div class="kpi-label">Cursos</div>
                    </div></div>
            </div>
            <div class="col-md-3">
                <div class="card kpi-card"><div class="card-body">
                        <div class="kpi-value">{{ number_format($kpi['offerings']) }}</div>
                        <div class="kpi-label">Ofertas publicadas</div>
                    </div></div>
            </div>
            <div class="col-md-3">
                <div class="card kpi-card"><div class="card-body">
                        <div class="kpi-value">{{ number_format($kpi['students']) }}</div>
                        <div class="kpi-label">Alumnos</div>
                    </div></div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Inscripciones por Mes</div>
                    <div class="card-body"><div class="chart-container"><canvas id="enrollmentsChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Alumnos por Sucursal (Top 5)</div>
                    <div class="card-body"><div class="chart-container"><canvas id="studentsByBranchChart"></canvas></div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Cursos con Mayor Demanda (Top 5)</div>
            <div class="card-body"><div class="chart-container"><canvas id="popularCoursesChart"></canvas></div></div>
        </div>
    </div>

    {{-- SUCURSALES --}}
    <div id="branches" class="section">
        <h2 class="mb-4">Sucursales</h2>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Listado de Sucursales</span>
                <a class="btn btn-primary btn-sm" href="{{ route('branches.index') }}"><i class="bi bi-plus-circle"></i> Gestionar</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th># Ofertas</th>
                            <th># Alumnos</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($branches as $b)
                            <tr>
                                <td>{{ $b->nombre }}</td>
                                <td>{{ $b->direccion }}</td>
                                <td>{{ $b->telefono }}</td>
                                <td>{{ $b->offerings_count }}</td>
                                <td>{{ $b->students_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Sin sucursales aún.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Navegación secciones
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            const target = link.getAttribute('data-section');
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(target).classList.add('active');
        });
    });

    // Datos desde el servidor
    const enrollmentsLabels = @json($charts['enrollments']['labels']);
    const enrollmentsData   = @json($charts['enrollments']['data']);

    const studentsByBranchLabels = @json($charts['studentsByBranch']['labels']);
    const studentsByBranchData   = @json($charts['studentsByBranch']['data']);

    const popularCoursesLabels = @json($charts['popularCourses']['labels']);
    const popularCoursesData   = @json($charts['popularCourses']['data']);

    // Charts
    new Chart(document.getElementById('enrollmentsChart').getContext('2d'), {
        type:'line',
        data:{ labels:enrollmentsLabels, datasets:[{ label:'Inscripciones', data:enrollmentsData, borderColor:'rgba(54,162,235,1)', backgroundColor:'rgba(54,162,235,.2)', borderWidth:2, tension:.3 }] },
        options:{ maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });

    new Chart(document.getElementById('studentsByBranchChart').getContext('2d'), {
        type:'bar',
        data:{ labels:studentsByBranchLabels, datasets:[{ label:'Alumnos', data:studentsByBranchData, backgroundColor:'rgba(75,192,192,.7)' }] },
        options:{ maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });

    new Chart(document.getElementById('popularCoursesChart').getContext('2d'), {
        type:'doughnut',
        data:{ labels:popularCoursesLabels, datasets:[{ data:popularCoursesData, backgroundColor:['#ef476f','#118ab2','#ffd166','#06d6a0','#8338ec'] }] },
        options:{ maintainAspectRatio:false, plugins:{ legend:{ position:'right' } } }
    });
</script>
</body>
</html>
