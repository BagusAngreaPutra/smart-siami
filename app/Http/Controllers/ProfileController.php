<?php

namespace App\Http\Controllers;

use App\Models\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('unit'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'profile_photo_focus_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'profile_photo_focus_y' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
        unset($validated['profile_photo']);
        $validated['profile_photo_focus_x'] = $validated['profile_photo_focus_x'] ?? $user->profile_photo_focus_x ?? 50;
        $validated['profile_photo_focus_y'] = $validated['profile_photo_focus_y'] ?? $user->profile_photo_focus_y ?? 50;

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update($validated);

        return back()->with('status', 'Profil akun berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return back()->with('status', 'Foto profil berhasil dihapus.');
    }

    public function updatePhotoFocus(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'profile_photo_focus_x' => ['required', 'integer', 'min:0', 'max:100'],
            'profile_photo_focus_y' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $request->user()->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sorotan foto profil berhasil disimpan.']);
        }

        return back()->with('status', 'Sorotan foto profil berhasil disimpan.');
    }

    public function photo(Request $request, User $user): BinaryFileResponse
    {
        abort_unless($request->user()->is($user) || $request->user()->role->value === 'admin', 403);
        abort_unless($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path), 404);

        return response()->file(Storage::disk('public')->path($user->profile_photo_path));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('password_status', 'Kata sandi berhasil diperbarui.');
    }
}
