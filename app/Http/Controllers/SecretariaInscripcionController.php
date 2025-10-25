<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Offering;
use App\Models\Branch;
use App\Models\Course;
use Illuminate\Http\Request;

class SecretariaInscripcionController extends Controller
{
    /**
     * 📋 Mostrar lista de inscripciones con filtros.
     */
    public function index(Request $request)
    {
        $query = Enrollment::with([
            'student.user',
            'offering.course',
            'offering.teacher.user',
            'offering.branch'
        ]);

        // === 🧩 FILTROS ===
        if ($request->filled('estado')) {
            $query->where('status', $request->estado);
        }

        if ($request->filled('curso')) {
            $query->whereHas('offering.course', function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->curso}%");
            });
        }

        if ($request->filled('grado')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade', $request->grado);
            });
        }

        if ($request->filled('sucursal')) {
            $query->whereHas('offering.branch', function ($q) use ($request) {
                $q->where('nombre', $request->sucursal);
            });
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        // === 🔃 ORDEN ===
        if ($request->orden === 'recientes') {
            $query->orderByDesc('fecha');
        } elseif ($request->orden === 'antiguas') {
            $query->orderBy('fecha');
        } else {
            $query->latest();
        }

        // === 📦 DATOS PARA LA VISTA ===
        $inscripciones = $query->get();
        $students = Student::with('branch')->orderBy('nombres')->get();
        $offerings = Offering::with(['course', 'teacher.user', 'branch'])->get();
        $branches = Branch::orderBy('nombre')->get();
        $cursos = Course::orderBy('nombre')->get();
        $inscripcionesData = Enrollment::select('student_id', 'offering_id')->get();

        return view('Secretaria.sec_inscripciones', compact(
            'inscripciones',
            'students',
            'offerings',
            'branches',
            'cursos',
            'inscripcionesData'
        ));
    }

    /**
     * ➕ Crear una nueva inscripción.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'offering_id' => 'required|exists:offerings,id',
        ]);

        // ⚠️ Evitar duplicados
        $exists = Enrollment::where('student_id', $data['student_id'])
            ->where('offering_id', $data['offering_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'El alumno ya está inscrito en este curso.');
        }

        // ✅ Crear inscripción
        $enrollment = Enrollment::create([
            'student_id'  => $data['student_id'],
            'offering_id' => $data['offering_id'],
            'fecha'       => now(),
            'status'      => 'activa',
        ]);

        // 🔻 Reducir cupo si aplica
        $offering = Offering::find($data['offering_id']);
        if ($offering && $offering->cupo > 0) {
            $offering->decrement('cupo');
        }

        return redirect()->back()->with('success', 'Inscripción registrada correctamente.');
    }

    /**
     * ✏️ Actualizar el estado de una inscripción.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:activa,retirada,finalizada',
        ]);

        $enrollment = Enrollment::findOrFail($id);
        $oldStatus = $enrollment->status;
        $enrollment->status = $request->status;
        $enrollment->save();

        // 🔁 Si pasa de activa → retirada/finalizada, liberar cupo
        if (in_array($request->status, ['retirada', 'finalizada']) && $oldStatus === 'activa') {
            $offering = Offering::find($enrollment->offering_id);
            if ($offering) {
                $offering->increment('cupo');
            }
        }

        return redirect()->back()->with('success', 'Estado de la inscripción actualizado correctamente.');
    }

    /**
     * 🗑️ Eliminar una inscripción.
     */
    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        // 🔁 Restaurar cupo antes de eliminar
        $offering = Offering::find($enrollment->offering_id);
        if ($offering) {
            $offering->increment('cupo');
        }

        $enrollment->delete();

        return redirect()->back()->with('success', 'Inscripción eliminada correctamente.');
    }
}
