<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = 'web';

        $modules = [
            /*
            |--------------------------------------------------------------------------
            | Master Data
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Master Data',
                'label' => 'Posisi Pelamar',
                'key' => 'admin.master-data.posisi',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Jabatan',
                'key' => 'admin.master-data.jabatan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Divisi',
                'key' => 'admin.master-data.divisi',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Pendidikan',
                'key' => 'admin.master-data.pendidikan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Agama',
                'key' => 'admin.master-data.agama',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Kewarganegaraan',
                'key' => 'admin.master-data.kewarganegaraan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Status Pernikahan',
                'key' => 'admin.master-data.status-pernikahan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Opsi Kacamata',
                'key' => 'admin.master-data.opsi-kacamata',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Sumber Informasi',
                'key' => 'admin.master-data.sumber-informasi',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Data Perusahaan',
                'key' => 'admin.master-data.perusahaan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Data Pelamar
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Data Pelamar',
                'label' => 'Data Pelamar',
                'key' => 'admin.data-pelamar',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                    'detail' => 'Detail',
                    'send-message' => 'Send Message',
                    'download-document' => 'Download Document',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Permintaan Kandidat
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Permintaan Kandidat',
                'label' => 'Permintaan Kandidat Recruitment',
                'key' => 'admin.permintaan-kandidat-recruitment',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'show' => 'Detail',
                    'update' => 'Update',
                    'status' => 'Update Status',
                    'destroy' => 'Delete',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Jadwal Test
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Jadwal Test',
                'label' => 'Zoom',
                'key' => 'admin.jadwal-test.zoom',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                    'detail' => 'Detail',
                    'options' => 'Options',
                ],
            ],
            [
                'group' => 'Jadwal Test',
                'label' => 'MMPI',
                'key' => 'admin.jadwal-test.mmpi',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'destroy' => 'Delete',
                    'options' => 'Options',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Daftar Hadir
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Daftar Hadir',
                'label' => 'Zoom',
                'key' => 'admin.daftar-hadir.zoom',
                'actions' => [
                    'list' => 'View',
                    'detail' => 'Detail',
                    'update-hasil-test' => 'Update Hasil Test',
                ],
            ],
            [
                'group' => 'Daftar Hadir',
                'label' => 'MMPI',
                'key' => 'admin.daftar-hadir.mmpi',
                'actions' => [
                    'list' => 'View',
                    'detail' => 'Detail',
                    'update-kehadiran' => 'Update Kehadiran',
                    'update-hasil-test' => 'Update Hasil Test',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Interview
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Interview',
                'label' => 'Interviewer',
                'key' => 'admin.rangkaian-interview.interviewer',
                'actions' => [
                    'list' => 'View',
                    'options' => 'Options',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Interview',
                'label' => 'Jadwal Interview',
                'key' => 'admin.rangkaian-interview.jadwal-interview',
                'actions' => [
                    'list' => 'View',
                    'options' => 'Options',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Interview',
                'label' => 'Kandidat Interview',
                'key' => 'admin.rangkaian-interview.kandidat',
                'actions' => [
                    'list' => 'View',
                    'options' => 'Options',
                    'detail' => 'Detail',
                    'store' => 'Create',
                    'update' => 'Update',
                    'update-tanggal' => 'Update Tanggal',
                    'update-hasil' => 'Update Hasil',
                    'destroy-kandidat' => 'Delete Kandidat',
                    'destroy' => 'Delete',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Review Management
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Review Management',
                'label' => 'Review Kandidat',
                'key' => 'admin.review-management',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create Review',
                    'show' => 'Detail Review',
                    'update' => 'Update Review',
                    'destroy' => 'Delete Review',
                ],
            ],
            /*
            |--------------------------------------------------------------------------
            | Offering Letter
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Offering Letter',
                'label' => 'Jadwal Offering Letter',
                'key' => 'admin.jadwal-ol',
                'actions' => [
                    'list' => 'View',
                    'candidates' => 'Candidate Options',
                    'store' => 'Create',
                    'update' => 'Update',
                    'update-status' => 'Update Status',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Report',
                'label' => 'Data Pelamar',
                'key' => 'admin.report.data-pelamar',
                'actions' => ['list' => 'View', 'export' => 'Export'],
            ],
            [
                'group' => 'Report',
                'label' => 'Hasil Test Zoom',
                'key' => 'admin.report.hasil-test-zoom',
                'actions' => ['list' => 'View', 'export' => 'Export'],
            ],
            [
                'group' => 'Report',
                'label' => 'Hasil Test MMPI',
                'key' => 'admin.report.hasil-test-mmpi',
                'actions' => ['list' => 'View', 'export' => 'Export'],
            ],
            [
                'group' => 'Report',
                'label' => 'Interview Kandidat',
                'key' => 'admin.report.interview-kandidat',
                'actions' => ['list' => 'View', 'export' => 'Export'],
            ],
            [
                'group' => 'Report',
                'label' => 'Offering Letter',
                'key' => 'admin.report.offering-letter',
                'actions' => ['list' => 'View', 'export' => 'Export'],
            ],
            [
                'group' => 'Report',
                'label' => 'Interviewer',
                'key' => 'admin.report.interviewer',
                'actions' => ['list' => 'View', 'export' => 'Export'],
            ],

            /*
            |--------------------------------------------------------------------------
            | Account
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Account',
                'label' => 'Role',
                'key' => 'admin.account.role',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Account',
                'label' => 'User',
                'key' => 'admin.account.user',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Account',
                'label' => 'Permission',
                'key' => 'admin.account.permission',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                    'setting' => 'Setting',
                ],
            ],
            [
                'group' => 'System',
                'label' => 'Integration Alerts',
                'key' => 'admin.integration-alert',
                'actions' => [
                    'list' => 'View',
                    'retry' => 'Retry',
                    'acknowledge' => 'Acknowledge',
                ],
            ],
            [
                'group' => 'System',
                'label' => 'Recruitment Audit',
                'key' => 'admin.recruitment-audit',
                'actions' => [
                    'list' => 'View',
                    'detail' => 'Detail',
                    'export' => 'Export',
                ],
            ],
            [
                'group' => 'AI Recruitment',
                'label' => 'AI Recruitment',
                'key' => 'admin.ai-recruitment',
                'actions' => [
                    'list' => 'View',
                    'analyze' => 'Analyze',
                ],
            ],
        ];

        foreach ($modules as $module) {
            foreach ($module['actions'] as $actionKey => $actionLabel) {
                Permission::firstOrCreate([
                    'name' => $module['key'].'.'.$actionKey,
                    'guard_name' => $guardName,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Superadmin Full Access
        |--------------------------------------------------------------------------
        */
        $superadmin = Role::firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => $guardName,
        ]);

        $superadmin->syncPermissions(
            Permission::query()
                ->where('guard_name', $guardName)
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
