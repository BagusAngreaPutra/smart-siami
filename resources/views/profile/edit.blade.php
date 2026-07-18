@extends('layouts.app')

@section('title', 'Profil Akun - SMART SIAMI')
@section('page_title', 'Profil Akun')

@section('content')
    <div class="crm-profile-layout">
    <div class="panel crm-profile-card crm-profile-account-card">
        <div class="crm-profile-section-heading">
            <span class="crm-profile-section-icon tone-blue" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </span>
            <div>
                <span class="crm-profile-kicker">Informasi pribadi</span>
                <h3 class="panel-title">Data akun</h3>
                <p class="muted">Perbarui identitas dan informasi kontak yang digunakan di SMART SIAMI.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @php
            $focusX = (int) old('profile_photo_focus_x', $user->profile_photo_focus_x ?? 50);
            $focusY = (int) old('profile_photo_focus_y', $user->profile_photo_focus_y ?? 50);
            $initials = strtoupper(collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join(''));
            $photoUrl = $user->profile_photo_path
                ? route('profile.photo.show', $user).'?v='.substr(md5($user->profile_photo_path), 0, 12)
                : null;
        @endphp

        <div class="profile-photo-card crm-profile-photo-card">
            <div class="profile-photo-preview @if ($photoUrl) has-photo @endif" data-photo-preview style="--photo-x: {{ $focusX }}%; --photo-y: {{ $focusY }}%; @if ($photoUrl) --photo-url: url('{{ $photoUrl }}'); @endif">
                @unless ($photoUrl)
                    <span data-photo-preview-initials>{{ $initials ?: 'U' }}</span>
                @endunless
            </div>
            <div class="crm-profile-photo-copy">
                <div class="crm-profile-photo-title">
                    <strong>Foto profil</strong>
                    <span class="crm-online-pill"><i></i> Online</span>
                </div>
                <p class="muted">Gunakan foto JPG atau PNG maksimal 2 MB. Atur fokus agar bagian wajah/area penting tetap terlihat pada avatar bulat.</p>
                <div class="actions">
                    <button class="button secondary" type="button" data-open-photo-focus @disabled(! $user->profile_photo_path)>
                        <svg class="crm-inline-button-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M21 16v3a2 2 0 0 1-2 2h-3M8 21H5a2 2 0 0 1-2-2v-3"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        Atur Sorotan
                    </button>
                    @if ($user->profile_photo_path)
                        <form method="post" action="{{ route('profile.photo.destroy') }}" onsubmit="return confirm('Hapus foto profil?');">
                            @csrf
                            @method('delete')
                            <button class="button secondary crm-profile-delete-button" type="submit">
                                <svg class="crm-inline-button-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"></path></svg>
                                Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>
                @if ($user->profile_photo_path)
                    <p class="muted photo-focus-help">Sorotan saat ini: {{ $focusX }}% / {{ $focusY }}%</p>
                @endif
            </div>
        </div>

        <form method="post" action="{{ route('profile.update') }}" class="form-grid crm-profile-form" enctype="multipart/form-data">
            @csrf
            @method('patch')

            <div class="form-field full">
                <label for="profile_photo">Unggah / ganti foto profil</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/png,image/jpeg">
                <small class="crm-field-help">Pilih foto persegi agar hasil avatar terlihat paling baik.</small>
                @error('profile_photo')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full" hidden>
                <input id="profile_photo_focus_x" name="profile_photo_focus_x" type="hidden" value="{{ $focusX }}" data-photo-focus-x>
                <input id="profile_photo_focus_y" name="profile_photo_focus_y" type="hidden" value="{{ $focusY }}" data-photo-focus-y>
            </div>

            @error('profile_photo_focus_x')
                <div class="error">{{ $message }}</div>
            @enderror
            @error('profile_photo_focus_y')
                <div class="error">{{ $message }}</div>
            @enderror

            <div class="form-field">
                <label for="name">Nama</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Contoh: Ahmad Fauzan" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@institusi.ac.id" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="phone">Nomor telepon</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 0812 3456 7890">
                @error('phone')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nip_nidn">NIP/NIDN</label>
                <input id="nip_nidn" value="{{ $user->nip_nidn }}" disabled>
            </div>

            <div class="form-field full crm-profile-form-actions">
                <button type="submit">
                    <svg class="crm-inline-button-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><path d="M17 21v-8H7v8M7 3v5h8"></path></svg>
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>

    <div class="photo-focus-modal" data-photo-focus-modal hidden>
        <div class="photo-focus-dialog" role="dialog" aria-modal="true" aria-labelledby="photo-focus-title">
            <div class="toolbar">
                <div>
                    <h3 id="photo-focus-title" class="panel-title">Atur Sorotan Foto</h3>
                    <p class="muted">Drag foto sampai bagian penting berada di area lingkaran.</p>
                </div>
                <button class="button secondary" type="button" data-close-photo-focus>Tutup</button>
            </div>

            <div class="photo-crop-stage">
                <div class="photo-crop-frame @if ($photoUrl) has-photo @endif" data-photo-crop-frame style="--photo-x: {{ $focusX }}%; --photo-y: {{ $focusY }}%; @if ($photoUrl) --photo-url: url('{{ $photoUrl }}'); @endif">
                    @unless ($photoUrl)
                        <span data-photo-crop-empty>{{ $initials ?: 'U' }}</span>
                    @endunless
                </div>
            </div>

            <div class="toolbar photo-focus-actions">
                <div class="actions">
                    <button class="button secondary" type="button" data-photo-focus-center>Tengah</button>
                    <span class="muted" data-photo-focus-value>{{ $focusX }}% / {{ $focusY }}%</span>
                </div>
                <button class="button" type="button" data-save-photo-focus>Simpan Sorotan</button>
            </div>
        </div>
    </div>

    <div class="panel crm-profile-card crm-profile-security-card">
        <div class="crm-profile-section-heading compact">
            <span class="crm-profile-section-icon tone-orange" aria-hidden="true">
                <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"></path></svg>
            </span>
            <div>
                <span class="crm-profile-kicker">Keamanan</span>
                <h3 class="panel-title">Kata sandi</h3>
                <p class="muted">Gunakan minimal 8 karakter dan hindari kata sandi yang mudah ditebak.</p>
            </div>
        </div>

        <div class="crm-account-summary">
            <div><span>Peran akun</span><strong>{{ $user->role->label() }}</strong></div>
            <div><span>Unit</span><strong>{{ $user->unit?->nama ?? 'Tidak terikat unit' }}</strong></div>
        </div>

        @if (session('password_status'))
            <div class="status">{{ session('password_status') }}</div>
        @endif

        <form method="post" action="{{ route('profile.password') }}" class="form-grid">
            @csrf
            @method('patch')

            <div class="form-field">
                <label for="current_password">Kata sandi saat ini</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi saat ini" required>
                @error('current_password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password">Kata sandi baru</label>
                <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Konfirmasi kata sandi baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi kata sandi baru" required>
            </div>

            <div class="form-field full crm-profile-form-actions">
                <button type="submit">
                    <svg class="crm-inline-button-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"></path></svg>
                    Ubah Kata Sandi
                </button>
            </div>
        </form>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const preview = document.querySelector('[data-photo-preview]');
            const fileInput = document.querySelector('#profile_photo');
            const focusX = document.querySelector('[data-photo-focus-x]');
            const focusY = document.querySelector('[data-photo-focus-y]');
            const modal = document.querySelector('[data-photo-focus-modal]');
            const cropFrame = document.querySelector('[data-photo-crop-frame]');
            const focusValue = document.querySelector('[data-photo-focus-value]');
            const openButtons = document.querySelectorAll('[data-open-photo-focus]');
            const closeButton = document.querySelector('[data-close-photo-focus]');
            const saveButton = document.querySelector('[data-save-photo-focus]');
            const centerButton = document.querySelector('[data-photo-focus-center]');
            const focusSaveUrl = @json(route('profile.photo.focus'));
            const csrfToken = document.querySelector('input[name="_token"]')?.value;

            if (! preview || ! focusX || ! focusY) {
                return;
            }

            let objectUrl = null;
            let draftX = Number(focusX.value || 50);
            let draftY = Number(focusY.value || 50);
            let dragStart = null;
            let hasPhoto = preview.classList.contains('has-photo');
            let hasUnsavedFile = false;

            const clamp = (value) => Math.max(0, Math.min(100, Math.round(value)));

            const applyFocus = () => {
                preview.style.setProperty('--photo-x', `${focusX.value}%`);
                preview.style.setProperty('--photo-y', `${focusY.value}%`);
                cropFrame?.style.setProperty('--photo-x', `${focusX.value}%`);
                cropFrame?.style.setProperty('--photo-y', `${focusY.value}%`);
                document.querySelectorAll('.topbar-actions .avatar, .sidebar-user-avatar .avatar').forEach((avatar) => {
                    avatar.style.setProperty('--photo-x', `${focusX.value}%`);
                    avatar.style.setProperty('--photo-y', `${focusY.value}%`);
                });
                if (focusValue) focusValue.textContent = `${focusX.value}% / ${focusY.value}%`;
            };

            const applyDraftFocus = () => {
                cropFrame?.style.setProperty('--photo-x', `${draftX}%`);
                cropFrame?.style.setProperty('--photo-y', `${draftY}%`);
                if (focusValue) focusValue.textContent = `${draftX}% / ${draftY}%`;
            };

            const setModalImage = (url) => {
                if (! cropFrame || ! url) return;

                cropFrame.querySelector('[data-photo-crop-empty]')?.remove();
                cropFrame.classList.add('has-photo');
                cropFrame.style.setProperty('--photo-url', `url("${url}")`);
                hasPhoto = true;
                openButtons.forEach((button) => button.disabled = false);
            };

            const openModal = () => {
                if (! modal || ! hasPhoto) return;
                draftX = Number(focusX.value || 50);
                draftY = Number(focusY.value || 50);
                applyDraftFocus();
                modal.hidden = false;
            };

            const closeModal = () => {
                if (modal) modal.hidden = true;
                draftX = Number(focusX.value || 50);
                draftY = Number(focusY.value || 50);
                applyDraftFocus();
            };

            openButtons.forEach((button) => button.addEventListener('click', openModal));
            closeButton?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            centerButton?.addEventListener('click', () => {
                draftX = 50;
                draftY = 50;
                applyDraftFocus();
            });
            saveButton?.addEventListener('click', () => {
                focusX.value = draftX;
                focusY.value = draftY;
                applyFocus();
                if (modal) modal.hidden = true;

                if (hasUnsavedFile || ! focusSaveUrl || ! csrfToken) {
                    return;
                }

                fetch(focusSaveUrl, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        profile_photo_focus_x: draftX,
                        profile_photo_focus_y: draftY,
                    }),
                }).catch(() => {
                    const toast = document.createElement('div');
                    toast.className = 'toast warning';
                    toast.textContent = 'Sorotan berubah di layar, tetapi belum tersimpan. Klik Simpan Profil.';
                    document.querySelector('.toast-stack')?.appendChild(toast);
                });
            });

            cropFrame?.addEventListener('pointerdown', (event) => {
                if (! hasPhoto) return;
                cropFrame.setPointerCapture(event.pointerId);
                dragStart = {
                    pointerX: event.clientX,
                    pointerY: event.clientY,
                    focusX: draftX,
                    focusY: draftY,
                    width: cropFrame.clientWidth || 1,
                    height: cropFrame.clientHeight || 1,
                };
                cropFrame.classList.add('is-dragging');
            });

            cropFrame?.addEventListener('pointermove', (event) => {
                if (! dragStart) return;
                const dx = event.clientX - dragStart.pointerX;
                const dy = event.clientY - dragStart.pointerY;
                draftX = clamp(dragStart.focusX + (dx / dragStart.width) * 100);
                draftY = clamp(dragStart.focusY + (dy / dragStart.height) * 100);
                applyDraftFocus();
            });

            const stopDragging = () => {
                dragStart = null;
                cropFrame?.classList.remove('is-dragging');
            };

            cropFrame?.addEventListener('pointerup', stopDragging);
            cropFrame?.addEventListener('pointercancel', stopDragging);

            fileInput?.addEventListener('change', () => {
                const file = fileInput.files?.[0];
                if (! file) {
                    return;
                }

                hasUnsavedFile = true;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = URL.createObjectURL(file);
                preview.querySelector('[data-photo-preview-initials]')?.remove();
                preview.classList.add('has-photo');
                preview.style.setProperty('--photo-url', `url("${objectUrl}")`);
                setModalImage(objectUrl);
                document.querySelectorAll('.topbar-actions .avatar, .sidebar-user-avatar .avatar').forEach((avatar) => {
                    avatar.textContent = '';
                    avatar.classList.add('has-photo');
                    avatar.style.setProperty('--photo-url', `url("${objectUrl}")`);
                });
                focusX.value = 50;
                focusY.value = 50;
                draftX = 50;
                draftY = 50;
                applyFocus();
                openModal();
            });
        })();
    </script>
@endpush
