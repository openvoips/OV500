<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class LegacyPortalController extends Controller
{
    /**
     * Legacy CodeIgniter controller names that can be resolved by the standalone
     * Laravel portal without booting the old portal/application stack.
     *
     * @var array<string, array{label: string, target?: string, status: string}>
     */
    private const CONTROLLERS = [
        'account' => ['label' => 'Account profile', 'status' => 'pending'],
        'bundle' => ['label' => 'Bundle plans', 'status' => 'pending'],
        'carriers' => ['label' => 'Carriers', 'target' => '/admin/carriers', 'status' => 'redirect'],
        'currency' => ['label' => 'Currency', 'status' => 'pending'],
        'dashboard' => ['label' => 'Dashboard', 'target' => '/admin', 'status' => 'redirect'],
        'dialplans' => ['label' => 'Dial plans', 'status' => 'pending'],
        'dids' => ['label' => 'DID inventory', 'target' => '/admin/dids', 'status' => 'redirect'],
        'download' => ['label' => 'Downloads', 'status' => 'pending'],
        'endpoints' => ['label' => 'Customer SIP accounts', 'target' => '/admin/customer-sip-accounts', 'status' => 'redirect'],
        'login' => ['label' => 'Login', 'target' => '/admin/login', 'status' => 'redirect'],
        'logout' => ['label' => 'Logout', 'target' => '/admin/logout', 'status' => 'redirect'],
        'module' => ['label' => 'Modules', 'status' => 'pending'],
        'page' => ['label' => 'Pages', 'status' => 'pending'],
        'providers' => ['label' => 'Providers', 'target' => '/admin/carriers', 'status' => 'redirect'],
        'ratecard' => ['label' => 'Rate cards', 'target' => '/admin/customer-rates', 'status' => 'redirect'],
        'rates' => ['label' => 'Rates', 'target' => '/admin/customer-rates', 'status' => 'redirect'],
        'recyclebin' => ['label' => 'Recycle bin', 'status' => 'pending'],
        'reports' => ['label' => 'Reports', 'status' => 'pending'],
        'roles' => ['label' => 'Roles', 'status' => 'pending'],
        'routes' => ['label' => 'Routes', 'status' => 'pending'],
        'sitesetup' => ['label' => 'Site setup', 'status' => 'pending'],
        'sysconfig' => ['label' => 'System configuration', 'status' => 'pending'],
        'tariffs' => ['label' => 'Tariffs', 'target' => '/admin/customer-rates', 'status' => 'redirect'],
        'upload' => ['label' => 'Uploads', 'status' => 'pending'],
        'users' => ['label' => 'Users', 'status' => 'pending'],
    ];

    public function dashboard(): RedirectResponse
    {
        return redirect('/admin');
    }

    public function handle(
        Request $request,
        string $legacyController,
        ?string $legacyAction = null,
        ?string $legacyParameters = null,
    ): RedirectResponse|View {
        $controller = strtolower($legacyController);
        $definition = self::CONTROLLERS[$controller] ?? null;

        if ($definition !== null && ($definition['status'] ?? null) === 'redirect' && isset($definition['target'])) {
            return redirect($definition['target']);
        }

        return view('legacy.controller', [
            'action' => $legacyAction,
            'controller' => $controller,
            'controllers' => self::CONTROLLERS,
            'definition' => $definition,
            'parameters' => $legacyParameters,
            'requestedPath' => $request->path(),
        ]);
    }
}
