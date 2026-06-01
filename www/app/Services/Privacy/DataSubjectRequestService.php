<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\Customer;
use App\Models\DataSubjectRequest;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataSubjectRequestService
{
    public function __construct(
        private readonly DataAnonymizationService $anonymizer
    ) {}

    /**
     * Abre uma nova solicitação de direitos.
     */
    public function createRequest(
        int $companyId,
        string $subjectType,
        string $subjectUuid,
        string $requestType,
        int $deadlineDays = 15
    ): DataSubjectRequest {
        return DataSubjectRequest::create([
            'company_id' => $companyId,
            'subject_type' => $subjectType,
            'subject_uuid' => $subjectUuid,
            'request_type' => $requestType,
            'status' => 'pending',
            'deadline_at' => Carbon::now()->addDays($deadlineDays),
        ]);
    }

    /**
     * Finaliza a solicitação.
     */
    public function completeRequest(string $uuid, string $responseContent, ?string $evidenceNotes = null): bool
    {
        $request = DataSubjectRequest::where('uuid', $uuid)->first();
        if (! $request) {
            return false;
        }

        return $request->update([
            'status' => 'completed',
            'response_content' => $responseContent,
            'evidence_notes' => $evidenceNotes,
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Executa a eliminação segura/anonimização do titular nas tabelas.
     */
    public function executeErasure(string $subjectType, string $subjectUuid): bool
    {
        return DB::transaction(function () use ($subjectType, $subjectUuid) {
            if ($subjectType === 'customer') {
                $customer = Customer::where('uuid', $subjectUuid)->first();
                if ($customer) {
                    $customer->update([
                        'name' => $this->anonymizer->anonymizeName(),
                        'email' => 'anon-'.$customer->id.'@comanda.com',
                        'phone' => null,
                        'document' => null,
                        'birth_date' => null,
                        'status' => 'inactive',
                    ]);
                    $customer->delete(); // soft delete

                    return true;
                }
            }

            if ($subjectType === 'employee') {
                $employee = Employee::where('uuid', $subjectUuid)->first();
                if ($employee) {
                    $employee->update([
                        'name' => $this->anonymizer->anonymizeName(),
                        'email' => 'anon-emp-'.$employee->id.'@comanda.com',
                        'phone' => null,
                        'document' => null,
                        'birth_date' => null,
                        'status' => 'inactive',
                    ]);
                    $employee->delete(); // soft delete

                    return true;
                }
            }

            if ($subjectType === 'user') {
                $user = User::where('uuid', $subjectUuid)->first();
                if ($user) {
                    $user->update([
                        'name' => $this->anonymizer->anonymizeName(),
                        'email' => 'anon-usr-'.$user->id.'@comanda.com',
                        'status' => 'inactive',
                    ]);
                    $user->delete(); // soft delete

                    return true;
                }
            }

            return false;
        });
    }
}
