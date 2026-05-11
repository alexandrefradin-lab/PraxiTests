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
    public function index(PluginRegistry $registry)
    {
        $registry->syncToDatabase();
        $plugins = Plugin::orderBy('type')->orderBy('name')->get();

        return Inertia::render('Admin/Plugins/Index', [
            'plugins' => $plugins,
            'types'   => config('plugins.available_types'),
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
