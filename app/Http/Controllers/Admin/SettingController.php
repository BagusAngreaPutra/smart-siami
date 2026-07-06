<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FindingCategory;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Support\LetterheadDocumentParser;
use App\Support\LetterheadTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.settings.index', [
            'activeTab' => $request->query('tab', 'identity'),
            'settings' => Setting::query()->pluck('value', 'key'),
            'categories' => FindingCategory::query()->orderBy('urutan')->orderBy('nama')->get(),
            'templates' => NotificationTemplate::query()->orderBy('tipe')->get(),
            'fileTypes' => ['pdf', 'docx', 'xlsx', 'jpg', 'png'],
            'allowedFileTypes' => allowedUploadExtensions(),
            'paperSizes' => ['A4', 'F4', 'Letter', 'Legal'],
            'fontFamilies' => ['Arial', 'Helvetica', 'Times New Roman', 'Courier'],
        ]);
    }

    public function updateIdentity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_institusi' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'nama_lpm' => ['nullable', 'string', 'max:255'],
            'email_lpm' => ['nullable', 'email', 'max:255'],
        ]);

        Setting::putValue('nama_institusi', $validated['nama_institusi']);
        Setting::putValue('nama_lpm', $validated['nama_lpm'] ?? '');
        Setting::putValue('email_lpm', $validated['email_lpm'] ?? '');

        if ($request->hasFile('logo')) {
            $old = Setting::getValue('logo_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }

            Setting::putValue('logo_path', $request->file('logo')->store('settings', 'public'));
        }

        return back()->with('status', 'Identitas institusi berhasil disimpan.');
    }

    public function updateUpload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_file_size_mb' => ['required', 'integer', 'min:1', 'max:100'],
            'allowed_file_types' => ['required', 'array', 'min:1'],
            'allowed_file_types.*' => ['in:pdf,docx,xlsx,jpg,png'],
        ]);

        Setting::putValue('max_file_size_mb', (string) $validated['max_file_size_mb']);
        Setting::putValue('allowed_file_types', implode(',', $validated['allowed_file_types']));

        return back()->with('status', 'Batas unggah file berhasil disimpan.');
    }

    public function updateReportFormat(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_paper_size' => ['required', 'in:A4,F4,Letter,Legal'],
            'report_orientation' => ['required', 'in:portrait,landscape'],
            'report_margin_top_cm' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'report_margin_right_cm' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'report_margin_bottom_cm' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'report_margin_left_cm' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'report_font_family' => ['required', 'in:Arial,Helvetica,Times New Roman,Courier'],
            'report_font_size' => ['required', 'integer', 'min:9', 'max:16'],
            'report_line_height' => ['required', 'numeric', 'min:1.15', 'max:1.8'],
            'report_table_density' => ['required', 'in:compact,normal,loose'],
            'report_show_visual_summary' => ['nullable', 'boolean'],
            'report_letterhead_mode' => ['required', 'in:default,custom'],
            'report_letterhead_institution' => ['required', 'string', 'max:255'],
            'report_letterhead_unit' => ['nullable', 'string', 'max:255'],
            'report_letterhead_address' => ['nullable', 'string', 'max:500'],
            'report_letterhead_contact' => ['nullable', 'string', 'max:500'],
            'report_letterhead_file' => ['nullable', 'file', 'mimes:pdf,docx', 'max:5120'],
            'report_letterhead_logo_width' => ['required', 'integer', 'min:50', 'max:130'],
            'report_letterhead_institution_font_size' => ['required', 'integer', 'min:12', 'max:24'],
            'report_letterhead_unit_font_size' => ['required', 'integer', 'min:10', 'max:20'],
            'report_letterhead_address_font_size' => ['required', 'integer', 'min:9', 'max:16'],
            'report_letterhead_institution_bold' => ['nullable', 'boolean'],
            'report_letterhead_unit_bold' => ['nullable', 'boolean'],
            'report_letterhead_address_bold' => ['nullable', 'boolean'],
        ]);

        $docxLetterheadApplied = false;

        foreach ($validated as $key => $value) {
            if (in_array($key, [
                'report_show_visual_summary',
                'report_letterhead_file',
                'report_letterhead_institution_bold',
                'report_letterhead_unit_bold',
                'report_letterhead_address_bold',
            ], true)) {
                continue;
            }

            Setting::putValue($key, (string) $value);
        }

        Setting::putValue('report_show_visual_summary', $request->boolean('report_show_visual_summary') ? '1' : '0');
        Setting::putValue('report_letterhead_institution_bold', $request->boolean('report_letterhead_institution_bold') ? '1' : '0');
        Setting::putValue('report_letterhead_unit_bold', $request->boolean('report_letterhead_unit_bold') ? '1' : '0');
        Setting::putValue('report_letterhead_address_bold', $request->boolean('report_letterhead_address_bold') ? '1' : '0');

        if ($request->hasFile('report_letterhead_file')) {
            $old = Setting::getValue('report_letterhead_file_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }

            $file = $request->file('report_letterhead_file');
            Setting::putValue('report_letterhead_file_path', $file->store('settings/letterheads', 'public'));
            Setting::putValue('report_letterhead_file_name', $file->getClientOriginalName());
            Setting::putValue('report_letterhead_file_type', $file->getClientOriginalExtension());

            if (strtolower($file->getClientOriginalExtension()) === 'docx') {
                $parsed = LetterheadDocumentParser::mapLinesToLetterhead(
                    LetterheadDocumentParser::linesFromDocx($file->getRealPath())
                );

                foreach ([
                    'report_letterhead_institution' => $parsed['institution'],
                    'report_letterhead_unit' => $parsed['unit'],
                    'report_letterhead_address' => $parsed['address'],
                    'report_letterhead_contact' => $parsed['contact'],
                ] as $key => $value) {
                    if ($value !== null && $value !== '') {
                        Setting::putValue($key, $value);
                        $docxLetterheadApplied = true;
                    }
                }
            }
        }

        $message = $docxLetterheadApplied
            ? 'Format cetak laporan berhasil disimpan. Isi kop dari DOCX berhasil diterapkan ke laporan.'
            : 'Format cetak laporan berhasil disimpan.';

        return back()->with('status', $message);
    }

    public function letterheadTemplatePdf(): Response
    {
        return response(LetterheadTemplate::pdf(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="template-kop-universitas-jds.pdf"',
        ]);
    }

    public function letterheadTemplateDocx(): Response
    {
        return response(LetterheadTemplate::docx(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="template-kop-universitas-jds.docx"',
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        FindingCategory::query()->create($this->validatedCategory($request));

        return back()->with('status', 'Kategori temuan berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, FindingCategory $category): RedirectResponse
    {
        $category->update($this->validatedCategory($request));

        return back()->with('status', 'Kategori temuan berhasil diperbarui.');
    }

    public function toggleCategory(FindingCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', 'Status kategori temuan berhasil diubah.');
    }

    public function updateTemplates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'templates' => ['required', 'array'],
            'templates.*.judul_template' => ['required', 'string', 'max:255'],
            'templates.*.isi_template' => ['required', 'string'],
            'email_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['templates'] as $id => $payload) {
            NotificationTemplate::query()->whereKey($id)->update($payload);
        }

        Setting::putValue('email_notifications_enabled', $request->boolean('email_notifications_enabled') ? '1' : '0');

        return back()->with('status', 'Template notifikasi berhasil disimpan.');
    }

    private function validatedCategory(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'warna_hex' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'urutan' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
