<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Gestion des comptes utilisateurs (réservée aux admins — SEC-13).
 *
 * Couvre les besoins support d'une inscription libre : retrouver un compte,
 * changer son rôle, renvoyer l'email de vérification, suspendre (corbeille)
 * et restaurer. Aucune suppression définitive depuis l'interface.
 */
class UserController extends Controller
{
    use \App\Http\Controllers\Concerns\SortsColumns;

    /** Rôles assignables depuis l'interface (allowlist Spatie). */
    private const ROLES = ['admin', 'professional', 'candidate'];

    /** Colonnes triables depuis la liste (allowlist). */
    private const SORTABLE = ['name', 'email', 'last_login_at', 'created_at'];

    public function index(Request $request)
    {
        $q = User::query()->with('roles:id,name');

        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }

        if ($request->filled('role') && in_array($request->string('role')->toString(), self::ROLES, true)) {
            $q->role($request->string('role')->toString());
        }

        if ($request->filled('verified')) {
            $request->string('verified')->toString() === 'yes'
                ? $q->whereNotNull('email_verified_at')
                : $q->whereNull('email_verified_at');
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
        }

        [$sort, $dir] = $this->sortParams($request, self::SORTABLE);

        $users = $q->orderBy($sort, $dir)->paginate(50)->withQueryString()
            ->through(fn (User $u) => [
                'id'            => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'roles'         => $u->roles->pluck('name')->values(),
                'verified'      => $u->email_verified_at !== null,
                'two_factor'    => $u->hasTwoFactorEnabled(),
                'last_login_at' => $u->last_login_at?->format('d/m/Y H:i'),
                'created_at'    => $u->created_at?->format('d/m/Y'),
                'deleted_at'    => $u->deleted_at?->format('d/m/Y H:i'),
                'is_self'       => $u->id === auth()->id(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'roles'   => self::ROLES,
            'filters' => $request->only(['search', 'role', 'verified', 'trashed', 'sort', 'dir']),
        ]);
    }

    /** Change le rôle d'un utilisateur (remplace les rôles existants). */
    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', self::ROLES)],
        ]);

        // Garde-fous : pas d'auto-rétrogradation, ni de rétrogradation du dernier admin.
        abort_if($user->id === auth()->id(), 422, 'Vous ne pouvez pas modifier votre propre rôle.');
        if ($user->hasRole('admin') && $data['role'] !== 'admin' && $this->isLastAdmin($user)) {
            abort(422, 'Impossible de rétrograder le dernier administrateur.');
        }

        $previous = $user->roles->pluck('name')->all();
        $user->syncRoles([$data['role']]);

        AuditLog::record('user.role_changed', $user, [
            'email' => $user->email,
            'from'  => $previous,
            'to'    => $data['role'],
        ]);

        return back()->with('success', "Rôle de {$user->email} : {$data['role']}.");
    }

    /** Renvoie l'email de vérification. */
    public function resendVerification(User $user)
    {
        abort_if($user->email_verified_at !== null, 422, 'Cet email est déjà vérifié.');

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', "L'email n'a pas pu être envoyé (SMTP). Réessayez plus tard.");
        }

        return back()->with('success', "Email de vérification renvoyé à {$user->email}.");
    }

    /** Suspend un compte (corbeille) : connexion impossible, données conservées. */
    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'Vous ne pouvez pas suspendre votre propre compte.');
        if ($user->hasRole('admin') && $this->isLastAdmin($user)) {
            abort(422, 'Impossible de suspendre le dernier administrateur.');
        }

        AuditLog::record('user.suspended', $user, ['email' => $user->email]);
        $user->delete();

        return back()->with('success', "Compte {$user->email} suspendu.");
    }

    /** Restaure un compte suspendu. */
    public function restore(int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        AuditLog::record('user.restored', $user, ['email' => $user->email]);
        return back()->with('success', "Compte {$user->email} restauré.");
    }

    /** Vrai si $user est le seul admin actif restant. */
    private function isLastAdmin(User $user): bool
    {
        return User::role('admin')->where('id', '!=', $user->id)->count() === 0;
    }
}
