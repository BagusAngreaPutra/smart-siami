<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Auditor = 'auditor';
    case Auditee = 'auditee';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Auditor => 'Auditor',
            self::Auditee => 'Auditee',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Auditor => 'auditor.dashboard',
            self::Auditee => 'auditee.dashboard',
        };
    }

    /**
     * @return array<int, array{label: string, tone: string, items: array<int, array{label: string, route: string}>}>
     */
    public function sidebarGroups(): array
    {
        return match ($this) {
            self::Admin => [
                [
                    'label' => 'Akses Utama',
                    'tone' => 'overview',
                    'items' => [
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                    ],
                ],
                [
                    'label' => 'Persiapan Audit',
                    'tone' => 'setup',
                    'items' => [
                        ['label' => 'Periode Audit', 'route' => 'admin.periods'],
                        ['label' => 'Unit dan Pengguna', 'route' => 'admin.users'],
                        ['label' => 'Standar dan Instrumen AMI', 'route' => 'admin.standards'],
                        ['label' => 'Penugasan Audit', 'route' => 'admin.assignments'],
                    ],
                ],
                [
                    'label' => 'Pemantauan',
                    'tone' => 'process',
                    'items' => [
                        ['label' => 'Monitoring', 'route' => 'admin.monitoring'],
                    ],
                ],
                [
                    'label' => 'Output & Sistem',
                    'tone' => 'report',
                    'items' => [
                        ['label' => 'Laporan', 'route' => 'admin.reports'],
                        ['label' => 'Pengaturan', 'route' => 'admin.settings'],
                    ],
                ],
            ],
            self::Auditor => [
                [
                    'label' => 'Akses Utama',
                    'tone' => 'overview',
                    'items' => [
                        ['label' => 'Dashboard', 'route' => 'auditor.dashboard'],
                        ['label' => 'Panduan', 'route' => 'auditor.guide'],
                        ['label' => 'Tugas Audit', 'route' => 'auditor.tasks'],
                    ],
                ],
                [
                    'label' => 'Pemeriksaan Audit',
                    'tone' => 'process',
                    'items' => [
                        ['label' => 'Desk Evaluation', 'route' => 'auditor.desk-evaluation'],
                        ['label' => 'Klarifikasi', 'route' => 'auditor.clarifications'],
                        ['label' => 'Visitasi', 'route' => 'auditor.visitations'],
                    ],
                ],
                [
                    'label' => 'Temuan & Perbaikan',
                    'tone' => 'finding',
                    'items' => [
                        ['label' => 'Temuan', 'route' => 'auditor.findings'],
                        ['label' => 'Verifikasi Perbaikan', 'route' => 'auditor.follow-up-verifications'],
                    ],
                ],
                [
                    'label' => 'Dokumen Akhir',
                    'tone' => 'report',
                    'items' => [
                        ['label' => 'Laporan Saya', 'route' => 'auditor.reports'],
                    ],
                ],
            ],
            self::Auditee => [
                [
                    'label' => 'Akses Utama',
                    'tone' => 'overview',
                    'items' => [
                        ['label' => 'Dashboard', 'route' => 'auditee.dashboard'],
                        ['label' => 'Panduan', 'route' => 'auditee.guide'],
                        ['label' => 'Profil Unit', 'route' => 'auditee.unit-profile'],
                    ],
                ],
                [
                    'label' => 'Pengisian Audit',
                    'tone' => 'setup',
                    'items' => [
                        ['label' => 'Evaluasi Diri', 'route' => 'auditee.self-evaluations'],
                        ['label' => 'Bukti Dokumen', 'route' => 'auditee.documents'],
                    ],
                ],
                [
                    'label' => 'Komunikasi & Visitasi',
                    'tone' => 'process',
                    'items' => [
                        ['label' => 'Klarifikasi Auditor', 'route' => 'auditee.clarifications'],
                        ['label' => 'Jadwal Visitasi', 'route' => 'auditee.visit-schedules'],
                    ],
                ],
                [
                    'label' => 'Temuan & Laporan',
                    'tone' => 'finding',
                    'items' => [
                        ['label' => 'Tindak Lanjut Temuan', 'route' => 'auditee.findings-followups'],
                        ['label' => 'Laporan Unit', 'route' => 'auditee.reports'],
                    ],
                ],
            ],
        };
    }

    /**
     * @return array<int, array{label: string, route: string}>
     */
    public function sidebarItems(): array
    {
        return array_merge(...array_map(
            fn (array $group): array => $group['items'],
            $this->sidebarGroups(),
        ));
    }
}
