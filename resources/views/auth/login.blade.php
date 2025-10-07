@extends('layouts.app')
@section('title', 'Iniciar Sesión | Academy')

@section('content')
    <style>
        :root{
            --bg:#0A1F44;
            --primary:#2F5DFF;
            --primary-light:#3B6DFF;
            --text:#FFFFFF;
            --text-muted:#B0B8C2;
            --error:#FF5C5C;
            --radius:10px;
            font-family:"Poppins",sans-serif;
        }

        body{
            margin:0;
            padding:0;
            min-height:100vh;
            background-color:var(--bg);
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--text);
        }

        .card{
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.1);
            padding:2rem;
            border-radius:var(--radius);
            width:100%;
            max-width:380px;
            box-shadow:0 8px 20px rgba(0,0,0,0.3);
            backdrop-filter:blur(10px);
        }

        h3{
            text-align:center;
            color:var(--text);
            font-weight:700;
            margin-bottom:1.5rem;
            letter-spacing:1px;
        }

        label{
            font-weight:500;
            color:var(--text-muted);
            font-size:0.9rem;
        }

        input{
            background:#102750;
            color:var(--text);
            border:1px solid #1d335f;
            border-radius:var(--radius);
            padding:0.6rem 0.9rem;
            width:100%;
            margin-top:4px;
            outline:none;
            transition:all 0.2s ease-in-out;
        }
        input:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 2px rgba(47,93,255,0.3);
        }

        .btn{
            display:block;
            width:100%;
            border:none;
            border-radius:var(--radius);
            background:var(--primary);
            color:#fff;
            font-weight:600;
            font-size:1rem;
            padding:0.7rem;
            margin-top:0.5rem;
            transition:background 0.3s ease;
        }
        .btn:hover{
            background:var(--primary-light);
        }

        a{
            color:var(--primary-light);
            text-decoration:none;
            font-weight:500;
        }
        a:hover{text-decoration:underline;}

        p.text-center{
            color:var(--text-muted);
            margin-top:1rem;
            font-size:0.9rem;
        }

        .msg{
            text-align:center;
            margin-top:1rem;
            font-weight:600;
            color:var(--error);
            min-height:20px;
        }
    </style>

    <div class="card">
        <h3>INICIAR SESIÓN</h3>
        <form id="loginForm">@csrf
            <div class="mb-3">
                <label for="email">CORREO ELECTRÓNICO</label>
                <input type="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password">CONTRASEÑA</label>
                <input type="password" id="password" required>
            </div>
            <button class="btn">INGRESAR</button>
            <p class="text-center">¿NO TIENES UNA CUENTA? <a href="{{ url('/register') }}">REGÍSTRATE</a></p>
        </form>
        <div id="loginMessage" class="msg"></div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async e=>{
            e.preventDefault();
            const email=document.getElementById('email').value.trim();
            const password=document.getElementById('password').value.trim();
            const msg=document.getElementById('loginMessage');
            msg.textContent='Verificando...';
            try{
                const res=await fetch("{{ url('api/v1/auth/login') }}",{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json'},
                    body:JSON.stringify({email,password})
                });
                const data=await res.json();
                if(res.ok){
                    localStorage.setItem('token',data.token);
                    window.location.href='/dashboard';
                }else{
                    msg.textContent=data.message||'Credenciales inválidas';
                }
            }catch(err){
                msg.textContent='Error de conexión con el servidor.';
            }
        });
    </script>
@endsection
