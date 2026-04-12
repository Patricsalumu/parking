<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Acces;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('name', 'asc')->paginate(20);
        $users->appends($request->only('q'));

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|string',
            'blocked' => 'sometimes|nullable|boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['blocked'] = $request->has('blocked') ? 1 : 0;
        $user = User::create($data);

        // Save acces permissions (checkboxes grouped under `acces`)
        $accessInputs = $request->input('acces', []);
        $acces = new Acces();
        $acces->user_id = $user->id;
        $acces->reduction = isset($accessInputs['reduction']) ? 1 : 0;
        $acces->antidate = isset($accessInputs['antidate']) ? 1 : 0;
        $acces->modification = isset($accessInputs['modification']) ? 1 : 0;
        $acces->entree = isset($accessInputs['entree']) ? 1 : 0;
        $acces->facturation = isset($accessInputs['facturation']) ? 1 : 0;
        $acces->sortie = isset($accessInputs['sortie']) ? 1 : 0;
        $acces->save();
        return redirect()->route('users.index')->with('success','User created');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|string',
            'blocked' => 'sometimes|nullable|boolean',
        ]);
        if ($request->filled('password')) {
            $request->validate(['password' => 'confirmed|min:6']);
            $data['password'] = Hash::make($request->password);
        }
        $data['blocked'] = $request->has('blocked') ? 1 : 0;
        $user->update($data);

        // Update acces permissions if provided
        $accessInputs = $request->input('acces', []);
        $accesData = [
            'reduction' => isset($accessInputs['reduction']) ? 1 : 0,
            'antidate' => isset($accessInputs['antidate']) ? 1 : 0,
            'modification' => isset($accessInputs['modification']) ? 1 : 0,
            'entree' => isset($accessInputs['entree']) ? 1 : 0,
            'facturation' => isset($accessInputs['facturation']) ? 1 : 0,
            'sortie' => isset($accessInputs['sortie']) ? 1 : 0,
        ];
        $user->acces()->updateOrCreate(['user_id' => $user->id], $accesData);
        return redirect()->route('users.index')->with('success','User updated');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success','User deleted');
    }
}
