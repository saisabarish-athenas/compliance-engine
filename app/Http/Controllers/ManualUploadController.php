<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManualUploadController extends Controller
{
    public function processManualUploads(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::findOrFail($batch);
            $user = Auth::user();

            if ($batchModel->tenant_id !== $user->tenant_id) {
                abort(403, 'Unauthorized access to batch');
            }

            // Check if user has MINIMAL subscription
            $tenant = DB::table('tenants')->where('id', $user->tenant_id)->first();
            if ($tenant->subscription_type !== 'MINIMAL') {
                return redirect()->route('compliance.dashboard')
                    ->with('error', 'Manual upload processing is for MINIMAL subscription only.');
            }

            // Count uploaded files
            $uploadCount = DB::table('compliance_attachments')
                ->where('batch_id', $batch)
                ->where('tenant_id', $user->tenant_id)
                ->where('upload_type', 'manual')
                ->count();

            if ($uploadCount === 0) {
                return redirect()->route('compliance.dashboard')
                    ->with('error', 'No manual uploads found for this batch.');
            }

            // Update batch status
            $batchModel->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);

            // Log audit
            DB::table('compliance_audit_logs')->insert([
                'tenant_id' => $user->tenant_id,
                'user_id' => Auth::id(),
                'action' => 'MANUAL_UPLOADS_PROCESSED',
                'batch_id' => $batch,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => json_encode(['upload_count' => $uploadCount]),
                'created_at' => now(),
            ]);

            return redirect()->route('compliance.dashboard')
                ->with('success', "Manual uploads processed successfully! {$uploadCount} files uploaded.");

        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to process manual uploads: ' . $e->getMessage());
        }
    }
}