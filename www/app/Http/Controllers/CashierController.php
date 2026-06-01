<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CashierShift\CloseCashierShiftAction;
use App\Actions\CashierShift\OpenCashierShiftAction;
use App\Models\CashierShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', CashierShift::class);

        /** @var \App\Models\Employee|null $employee */
        $employee = Auth::guard('employee')->user();
        $companyId = $employee ? $employee->company_id : null;

        // Turno ativo
        $activeShift = CashierShift::where('status', 'open');
        if ($companyId) {
            $activeShift->where('company_id', $companyId);
        }
        $activeShift = $activeShift->first();

        // Histórico de turnos
        $shifts = CashierShift::orderBy('created_at', 'desc')->with(['openedByEmployee', 'closedByEmployee']);
        if ($companyId) {
            $shifts->where('company_id', $companyId);
        }
        $shifts = $shifts->take(10)->get();

        return view('admin.cashier.index', compact('activeShift', 'shifts'));
    }

    public function store(Request $request, OpenCashierShiftAction $openAction)
    {
        Gate::authorize('create', CashierShift::class);

        /** @var \App\Models\Employee|null $employee */
        $employee = Auth::guard('employee')->user();

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'unit_id' => 'required|exists:company_units,id',
            'opening_amount' => 'required|numeric|min:0',
        ]);

        // Verifica se já existe um caixa aberto
        $existing = CashierShift::where('company_id', $validated['company_id'])
            ->where('unit_id', $validated['unit_id'])
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um turno de caixa aberto para esta unidade.',
                ], 422);
            }

            return redirect()->back()->withErrors(['error' => 'Já existe um turno de caixa aberto para esta unidade.']);
        }

        $data = [
            'company_id' => $validated['company_id'],
            'unit_id' => $validated['unit_id'],
            'opened_by' => $employee ? $employee->id : 1,
            'opening_amount_cents' => (int) round($validated['opening_amount'] * 100),
        ];

        $shift = $openAction->execute($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shift_uuid' => $shift->uuid,
            ], 201);
        }

        return redirect()->route('admin.cashier.index')
            ->with('success', 'Turno de caixa aberto com sucesso.');
    }

    public function close(Request $request, string $uuid, CloseCashierShiftAction $action)
    {
        /** @var \App\Models\CashierShift $shift */
        $shift = CashierShift::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $shift);

        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
        ]);

        /** @var \App\Models\Employee|null $employee */
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee ? $employee->id : 1;
        $closingAmountCents = (int) round($validated['closing_amount'] * 100);

        $action->execute($shift, $employeeId, $closingAmountCents);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Turno de caixa fechado com sucesso.',
            ]);
        }

        return redirect()->route('admin.cashier.index')
            ->with('success', 'Turno de caixa fechado com sucesso.');
    }

    public function show(string $uuid): View
    {
        $shift = CashierShift::where('uuid', $uuid)
            ->with(['openedByEmployee', 'closedByEmployee'])
            ->firstOrFail();

        Gate::authorize('view', $shift);

        return view('admin.cashier.show', compact('shift'));
    }
}
