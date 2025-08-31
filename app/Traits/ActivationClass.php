<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ActivationClass
{
    public function dmvf($request)
    {

        /*$purchaseCode = PurchaseCode::where('code', $request->code)->first();

        if (!$purchaseCode) {
            return response()->json(['error' => 'Code invalide'], 400);
        }

        if (!$purchaseCode->is_active) {
            return response()->json(['error' => 'Code déjà utilisé ou expiré'], 400);
        }

        // Marquer comme activé
        $purchaseCode->update([
            'is_active' => false,
            'activated_at' => now(),
            'activated_by' => $request->user()->email ?? 'system'
        ]);*/
        session()->put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
        session()->put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
        return base64_decode('c3RlcDM=');//s3
    }

    public function actch(): JsonResponse
    {
		return response()->json([
			'active' => 1
		]);
    }

    public function is_local(): bool
    {
        return true;
    }
}
