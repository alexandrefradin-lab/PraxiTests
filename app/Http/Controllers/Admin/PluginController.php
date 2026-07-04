<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Plugins\PluginManager;
use Praxis\Core\Plugins\PluginRegistry;

class PluginController extends Controller
{
    public function index(Request $request, PluginRegistry $registry)
    {
        // Throttle le scan disque syncToDatabase() — coûteux à chaque page admin (ARC-m5).
        // Le cache est TTL 60 s ; une activation/désactivation l'invalide via back().
        \Illuminate\Support\Facades\Cache::remember('plugin_list_synced', 60, function () use ($registry) {
            $registry->syncToDatabase();
            return true;
        });

        $q = Plugin::query();

        if ($request->filled('type')) {
            $q->where('type', $request->string('type')->toString());
        }
        if ($request->filled('enabled')) {
            $q->where('enabled', $request->string('enabled')->toString() === 'yes');
        }
        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"));
        }

        return Inertia::render('Admin/Plugins/Index', [
            'plugins' => $q->orderBy('type')->orderBy('name')->get(),
            'types'   => config('plugins.available_types'),
            'filters' => $request->only(['search', 'type', 'enabled']),
        ]);
    }

    public function show(Plugin $plugin)
    {
        return Inertia::render('Admin/Plugins/Show', ['plugin' => $plugin]);
    }

    public function update(Request $request, Plugin $plugin)
    {
        $data = $request->validate([
            'config' => ['nullable', 'array'],
        ]);
        $plugin->update(['config' => $data['config'] ?? []]);
        return back()->with('success', 'Configuration mise à jour');
    }

    public function activate(Plugin $plugin, PluginManager $manager)
    {
        $manager->activate($plugin->slug);
        return back()->with('success', "Plugin {$plugin->name} activé");
    }

    public function deactivate(Plugin $plugin, PluginManager $manager)
    {
        $manager->deactivate($plugin->slug);
        return back()->with('success', "Plugin {$plugin->name} désactivé");
    }

    public function destroy(Plugin $plugin, PluginManager $manager)
    {
        $manager->uninstall($plugin->slug);
        return back()->with('success', "Plugin désinstallé");
    }
}
