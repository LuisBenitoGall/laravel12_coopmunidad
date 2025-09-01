<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

//Models:
use App\Models\BlockedIp;

class BlockedIpController extends Controller{
    /**
     * 1. Listado de IP bloqueadas.
     * 2. Guarda IP bloqueada.
     * 3. Actualizar IP bloqueada.
     * 4. Eliminar IP bloqueda.
     */
    
    /**
     * 1. Listado de IP bloqueadas.
     */
    public function index(Request $request){
        $query = BlockedIp::query();

        if ($request->filled('ip')) {
            $query->where('ip', 'like', '%' . $request->ip . '%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        return Inertia::render('Admin/BlockedIps/Index', [
            'blockedIps' => $query->orderBy('created_at', 'desc')->paginate(10),
            'filters' => $request->only('ip', 'active'),
        ]);
    }

    /**
     * 2. Guarda IP bloqueada.
     */
    public function store(Request $request){
        $request->validate([
            'ip' => 'required|ip|unique:blocked_ips,ip',
            'reason' => 'nullable|string',
        ]);

        BlockedIp::create($request->only('ip', 'reason') + ['active' => true]);

        return redirect()->route('blocked-ips.index')->with('msg', __('ip_bloqueada_guardada'));
    }

    /**
     * 3. Actualizar IP bloqueada.
     */
    public function update(Request $request, BlockedIp $blockedIp){
        $request->validate([
            'reason' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $blockedIp->update($request->only('reason', 'active'));

        return redirect()->route('blocked-ips.index')->with('msg', __('ip_actualizada'));
    }

    /**
     * 4. Eliminar IP bloqueda.
     */
    public function destroy(BlockedIp $blockedIp){
        $blockedIp->delete();

        return redirect()->route('blocked-ips.index')->with('msg', __('ip_eliminada'));
    }
}
