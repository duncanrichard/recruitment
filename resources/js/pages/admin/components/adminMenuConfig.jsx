import DashboardPage from "../pages/DashboardPage";
import TahapanSeleksiPage from "../pages/TahapanSeleksiPage";

import DataPelamarPage from "../pages/data-pelamar/Index";
import DetailDataPelamarPage from "../pages/data-pelamar/Detail";

import JadwalTestZoomPage from "../pages/jadwal-test/zoom/Index";
import JadwalTestMmpiPage from "../pages/jadwal-test/mmpi/Index";

import DaftarHadirZoomPage from "../pages/daftar-hadir/zoom/Index";
import DetailDaftarHadirZoomPage from "../pages/daftar-hadir/zoom/Detail";
import DaftarHadirMmpiPage from "../pages/daftar-hadir/mmpi/Index";
import DetailDaftarHadirMmpiPage from "../pages/daftar-hadir/mmpi/Detail";

import PosisiPage from "../pages/master-data/posisi/Index";
import JabatanPage from "../pages/master-data/jabatan/Index";
import DivisiPage from "../pages/master-data/divisi/Index";
import PendidikanPage from "../pages/master-data/pendidikan/Index";
import AgamaPage from "../pages/master-data/agama/Index";
import KewarganegaraanPage from "../pages/master-data/kewarganegaraan/Index";
import StatusPernikahanPage from "../pages/master-data/status-pernikahan/Index";
import OpsiKacamataPage from "../pages/master-data/opsi-kacamata/Index";
import SumberInformasiPage from "../pages/master-data/sumber-informasi/Index";
import DataPerusahaanPage from "../pages/master-data/perusahaan/Index";

import InterviewerPage from "../pages/rangkaian-interview/interviewer/Index";
import JadwalInterviewPage from "../pages/rangkaian-interview/jadwal-interview/Index";
import KandidatInterviewPage from "../pages/rangkaian-interview/kandidat/Index";

export const defaultMenuKey = "dashboard";

export const initialActionSignals = {
    dataPelamar: 0,
    pendaftarSemua: 0,
    pendaftarBaru: 0,
    pendaftarArsip: 0,
    tahapan: 0,

    jadwalTestZoom: 0,
    jadwalTestMmpi: 0,

    interviewer: 0,
    jadwalInterview: 0,
    kandidatInterview: 0,

    masterPosisi: 0,
    masterJabatan: 0,
    masterDivisi: 0,
    masterPendidikan: 0,
    masterAgama: 0,
    masterKewarganegaraan: 0,
    masterStatusPernikahan: 0,
    masterOpsiKacamata: 0,
    masterSumberInformasi: 0,
    masterPerusahaan: 0,
};

export const menuItems = [
    {
        key: "dashboard",
        label: "Dashboard",
        description: "Ringkasan utama",
        icon: "⌂",
        component: DashboardPage,
    },
    {
        key: "master-data",
        label: "Master Data",
        description: "Kelola data referensi",
        icon: "▦",
        children: [
            {
                key: "master-posisi",
                label: "Posisi Pelamar",
                description: "Kelola posisi pekerjaan",
                icon: "◆",
                component: PosisiPage,
                action: {
                    label: "Tambah Posisi",
                    signalKey: "masterPosisi",
                },
            },
            {
                key: "master-jabatan",
                label: "Jabatan",
                description: "Kelola data jabatan",
                icon: "▧",
                component: JabatanPage,
                action: {
                    label: "Tambah Jabatan",
                    signalKey: "masterJabatan",
                },
            },
            {
                key: "master-divisi",
                label: "Divisi",
                description: "Kelola data divisi",
                icon: "▦",
                component: DivisiPage,
                action: {
                    label: "Tambah Divisi",
                    signalKey: "masterDivisi",
                },
            },
            {
                key: "master-pendidikan",
                label: "Pendidikan",
                description: "Kelola data pendidikan",
                icon: "◇",
                component: PendidikanPage,
                action: {
                    label: "Tambah Pendidikan",
                    signalKey: "masterPendidikan",
                },
            },
            {
                key: "master-agama",
                label: "Agama",
                description: "Kelola data agama",
                icon: "◈",
                component: AgamaPage,
                action: {
                    label: "Tambah Agama",
                    signalKey: "masterAgama",
                },
            },
            {
                key: "master-kewarganegaraan",
                label: "Kewarganegaraan",
                description: "Kelola data kewarganegaraan",
                icon: "◎",
                component: KewarganegaraanPage,
                action: {
                    label: "Tambah Kewarganegaraan",
                    signalKey: "masterKewarganegaraan",
                },
            },
            {
                key: "master-status-pernikahan",
                label: "Status Pernikahan",
                description: "Kelola status pernikahan",
                icon: "◌",
                component: StatusPernikahanPage,
                action: {
                    label: "Tambah Status Pernikahan",
                    signalKey: "masterStatusPernikahan",
                },
            },
            {
                key: "master-opsi-kacamata",
                label: "Opsi Kacamata",
                description: "Kelola opsi kacamata",
                icon: "◍",
                component: OpsiKacamataPage,
                action: {
                    label: "Tambah Opsi Kacamata",
                    signalKey: "masterOpsiKacamata",
                },
            },
            {
                key: "master-sumber-informasi",
                label: "Sumber Informasi",
                description: "Kelola sumber informasi",
                icon: "◉",
                component: SumberInformasiPage,
                action: {
                    label: "Tambah Sumber Informasi",
                    signalKey: "masterSumberInformasi",
                },
            },
            {
                key: "master-perusahaan",
                label: "Data Perusahaan",
                description: "Kelola data perusahaan",
                icon: "▥",
                component: DataPerusahaanPage,
                action: {
                    label: "Tambah Perusahaan",
                    signalKey: "masterPerusahaan",
                },
            },
        ],
    },
    {
        key: "data-pelamar",
        label: "Data Pelamar",
        description: "Kelola data riwayat diri",
        icon: "▤",
        component: DataPelamarPage,
        action: {
            label: "Tambah Pelamar",
            signalKey: "dataPelamar",
        },
    },
    {
        key: "jadwal-test",
        label: "Data Jadwal Test",
        description: "Kelola jadwal test",
        icon: "◷",
        children: [
            {
                key: "jadwal-test-zoom",
                label: "Zoom",
                description: "Kelola jadwal test Zoom",
                icon: "◎",
                component: JadwalTestZoomPage,
                action: {
                    label: "Tambah Jadwal Zoom",
                    signalKey: "jadwalTestZoom",
                },
            },
            {
                key: "jadwal-test-mmpi",
                label: "MMPI",
                description: "Kelola jadwal test MMPI",
                icon: "◉",
                component: JadwalTestMmpiPage,
                action: {
                    label: "Tambah Jadwal MMPI",
                    signalKey: "jadwalTestMmpi",
                },
            },
        ],
    },
    {
        key: "daftar-hadir",
        label: "Daftar Hadir",
        description: "Kelola kehadiran",
        icon: "▨",
        children: [
            {
                key: "daftar-hadir-zoom",
                label: "Zoom",
                description: "Daftar hadir test Zoom",
                icon: "◎",
                component: DaftarHadirZoomPage,
            },
            {
                key: "daftar-hadir-mmpi",
                label: "MMPI",
                description: "Daftar hadir test MMPI",
                icon: "◉",
                component: DaftarHadirMmpiPage,
            },
        ],
    },
    {
        key: "rangkaian-interview",
        label: "Interview",
        description: "Kelola proses interview",
        icon: "▣",
        children: [
            {
                key: "interviewer",
                label: "Interviewer",
                description: "Kelola data interviewer",
                icon: "◉",
                component: InterviewerPage,
                action: {
                    label: "Tambah Interviewer",
                    signalKey: "interviewer",
                },
            },
            {
                key: "jadwal-interview",
                label: "Jadwal Interview",
                description: "Kelola jadwal interview",
                icon: "◷",
                component: JadwalInterviewPage,
                action: {
                    label: "Tambah Jadwal Interview",
                    signalKey: "jadwalInterview",
                },
            },
            {
                key: "kandidat-interview",
                label: "Kandidat",
                description: "Kelola kandidat interview",
                icon: "▤",
                component: KandidatInterviewPage,
                action: {
                    label: "Tambah Kandidat",
                    signalKey: "kandidatInterview",
                },
            },
        ],
    },
    {
        key: "tahapan",
        label: "Tahapan Seleksi",
        description: "Kelola proses seleksi",
        icon: "▣",
        component: TahapanSeleksiPage,
    },
];

export const detailMenus = {
    "data-pelamar-detail": {
        key: "data-pelamar-detail",
        label: "Detail Pelamar",
        parentLabel: "Data Pelamar",
        description: "Detail lengkap data pelamar",
        icon: "▤",
        component: DetailDataPelamarPage,
    },
    "daftar-hadir-zoom-detail": {
        key: "daftar-hadir-zoom-detail",
        label: "Detail Daftar Hadir Zoom",
        parentLabel: "Daftar Hadir",
        description: "Detail peserta berdasarkan tanggal test Zoom",
        icon: "◎",
        component: DetailDaftarHadirZoomPage,
    },
    "daftar-hadir-mmpi-detail": {
        key: "daftar-hadir-mmpi-detail",
        label: "Detail Daftar Hadir MMPI",
        parentLabel: "Daftar Hadir",
        description: "Detail peserta berdasarkan tanggal test MMPI",
        icon: "◉",
        component: DetailDaftarHadirMmpiPage,
    },
};

export function getFlattenMenus() {
    return menuItems.flatMap((menu) => {
        if (Array.isArray(menu.children)) {
            return menu.children.map((child) => ({
                ...child,
                parentKey: menu.key,
                parentLabel: menu.label,
            }));
        }

        return [menu];
    });
}

export function getActiveMenuData(activeMenu) {
    if (detailMenus[activeMenu]) {
        return detailMenus[activeMenu];
    }

    return (
        getFlattenMenus().find((item) => item.key === activeMenu) ||
        menuItems.find((item) => item.key === defaultMenuKey) ||
        menuItems[0]
    );
}

export function getMenuParentKeyByChildKey(childKey) {
    const parentMenu = menuItems.find((menu) =>
        Array.isArray(menu.children)
            ? menu.children.some((child) => child.key === childKey)
            : false
    );

    if (parentMenu?.key) {
        return parentMenu.key;
    }

    if (
        childKey === "daftar-hadir-zoom-detail" ||
        childKey === "daftar-hadir-mmpi-detail"
    ) {
        return "daftar-hadir";
    }

    if (childKey === "data-pelamar-detail") {
        return "data-pelamar";
    }

    return null;
}

export function isMenuActive(menu, activeMenu) {
    const childKeys = Array.isArray(menu.children)
        ? menu.children.map((child) => child.key)
        : [];

    return (
        activeMenu === menu.key ||
        childKeys.includes(activeMenu) ||
        (activeMenu === "data-pelamar-detail" && menu.key === "data-pelamar") ||
        (activeMenu === "daftar-hadir-zoom-detail" &&
            menu.key === "daftar-hadir") ||
        (activeMenu === "daftar-hadir-mmpi-detail" &&
            menu.key === "daftar-hadir")
    );
}

export function isChildActive(child, activeMenu) {
    return (
        activeMenu === child.key ||
        (activeMenu === "daftar-hadir-zoom-detail" &&
            child.key === "daftar-hadir-zoom") ||
        (activeMenu === "daftar-hadir-mmpi-detail" &&
            child.key === "daftar-hadir-mmpi")
    );
}