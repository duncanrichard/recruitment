import "../../../css/app.css";

import React, { useState } from "react";
import { createRoot } from "react-dom/client";

import DashboardPage from "./pages/DashboardPage";
import PendaftarPage from "./pages/PendaftarPage";
import PendaftarBaruPage from "./pages/PendaftarBaruPage";
import PendaftarArsipPage from "./pages/PendaftarArsipPage";
import TahapanSeleksiPage from "./pages/TahapanSeleksiPage";
import DataPelamarPage from "./pages/data-pelamar/Index";
import PosisiPage from "./pages/master-data/posisi/Index";
import PendidikanPage from "./pages/master-data/pendidikan/Index";
import AgamaPage from "./pages/master-data/agama/Index";
import KewarganegaraanPage from "./pages/master-data/kewarganegaraan/Index";
import StatusPernikahanPage from "./pages/master-data/status-pernikahan/Index";
import OpsiKacamataPage from "./pages/master-data/opsi-kacamata/Index";
import SumberInformasiPage from "./pages/master-data/sumber-informasi/Index";
import DataPerusahaanPage from "./pages/master-data/perusahaan/Index";

function AdminPage() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [activeMenu, setActiveMenu] = useState("dashboard");

    const [actionSignals, setActionSignals] = useState({
        dataPelamar: 0,
        pendaftarSemua: 0,
        pendaftarBaru: 0,
        pendaftarArsip: 0,
        tahapan: 0,
        masterPosisi: 0,
        masterPendidikan: 0,
        masterAgama: 0,
        masterKewarganegaraan: 0,
        masterStatusPernikahan: 0,
        masterOpsiKacamata: 0,
        masterSumberInformasi: 0,
        masterPerusahaan: 0,
    });

    const [openMenus, setOpenMenus] = useState({});

    const menuItems = [
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
                    label: "Posisi",
                    description: "Kelola posisi pekerjaan",
                    icon: "◆",
                    component: PosisiPage,
                    action: {
                        label: "Tambah Posisi",
                        signalKey: "masterPosisi",
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
            key: "pendaftar",
            label: "Data Pendaftar",
            description: "Kelola data pendaftar",
            icon: "◉",
            children: [
                {
                    key: "pendaftar-semua",
                    label: "Semua Pendaftar",
                    description: "Semua data pendaftar",
                    icon: "●",
                    component: PendaftarPage,
                },
                {
                    key: "pendaftar-baru",
                    label: "Pendaftar Baru",
                    description: "Data pendaftar terbaru",
                    icon: "✦",
                    component: PendaftarBaruPage,
                },
                {
                    key: "pendaftar-arsip",
                    label: "Arsip Pendaftar",
                    description: "Data pendaftar arsip",
                    icon: "◌",
                    component: PendaftarArsipPage,
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

    const flattenMenus = menuItems.flatMap((menu) => {
        if (menu.children) {
            return menu.children.map((child) => ({
                ...child,
                parentKey: menu.key,
                parentLabel: menu.label,
            }));
        }

        return [menu];
    });

    const activeMenuData =
        flattenMenus.find((item) => item.key === activeMenu) || menuItems[0];

    const ActiveComponent = activeMenuData.component || DashboardPage;

    const handleMenuClick = (menu) => {
        const hasChildren = Array.isArray(menu.children);

        if (hasChildren) {
            const isCurrentlyOpen = Boolean(openMenus[menu.key]);

            setOpenMenus((prev) => ({
                ...prev,
                [menu.key]: !isCurrentlyOpen,
            }));

            setSidebarOpen(false);
            return;
        }

        setActiveMenu(menu.key);
        setOpenMenus({});
        setSidebarOpen(false);
    };

    const handleSubMenuClick = (parentKey, childKey) => {
        setActiveMenu(childKey);

        setOpenMenus((prev) => ({
            ...prev,
            [parentKey]: true,
        }));

        setSidebarOpen(false);
    };

    const handleHeaderAction = () => {
        const action = activeMenuData.action;

        if (!action) return;

        setActionSignals((prev) => ({
            ...prev,
            [action.signalKey]: (prev[action.signalKey] || 0) + 1,
        }));
    };

    return (
        <main className="min-h-screen bg-slate-100 text-slate-900">
            <div className="flex min-h-screen">
                {sidebarOpen && (
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(false)}
                        className="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"
                        aria-label="Tutup sidebar"
                    />
                )}

                <aside
                    className={`fixed inset-y-0 left-0 z-40 w-72 transform bg-slate-950 text-white shadow-2xl transition-transform duration-300 lg:static lg:translate-x-0 ${
                        sidebarOpen ? "translate-x-0" : "-translate-x-full"
                    }`}
                >
                    <div className="flex h-full flex-col">
                        <div className="border-b border-white/10 p-5">
                            <div className="flex items-center gap-4">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-400 text-lg font-black text-slate-950 shadow-lg shadow-teal-950/30">
                                    HR
                                </div>

                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.22em] text-teal-200">
                                        Admin Panel
                                    </p>

                                    <h1 className="mt-1 text-lg font-black text-white">
                                        Rekrutmen
                                    </h1>
                                </div>
                            </div>
                        </div>

                        <nav className="flex-1 space-y-2 overflow-y-auto p-4">
                            {menuItems.map((menu) => {
                                const hasChildren = Array.isArray(menu.children);

                                const childKeys = hasChildren
                                    ? menu.children.map((child) => child.key)
                                    : [];

                                const isActive =
                                    activeMenu === menu.key ||
                                    childKeys.includes(activeMenu);

                                const isOpen = Boolean(openMenus[menu.key]);

                                return (
                                    <div key={menu.key}>
                                        <button
                                            type="button"
                                            onClick={() => handleMenuClick(menu)}
                                            className={`group flex w-full items-center gap-4 rounded-2xl px-4 py-3 text-left transition ${
                                                isActive
                                                    ? "bg-teal-400 text-slate-950 shadow-lg shadow-teal-950/30"
                                                    : "text-slate-300 hover:bg-white/10 hover:text-white"
                                            }`}
                                        >
                                            <span
                                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg font-black ${
                                                    isActive
                                                        ? "bg-slate-950 text-teal-200"
                                                        : "bg-white/10 text-teal-200 group-hover:bg-white/15"
                                                }`}
                                            >
                                                {menu.icon}
                                            </span>

                                            <span className="min-w-0 flex-1">
                                                <span className="block truncate text-sm font-black">
                                                    {menu.label}
                                                </span>

                                                <span
                                                    className={`mt-0.5 block truncate text-xs ${
                                                        isActive
                                                            ? "text-slate-700"
                                                            : "text-slate-400"
                                                    }`}
                                                >
                                                    {menu.description}
                                                </span>
                                            </span>

                                            {hasChildren && (
                                                <span
                                                    className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-sm font-black transition ${
                                                        isActive
                                                            ? "bg-slate-950/10 text-slate-950"
                                                            : "bg-white/5 text-slate-300 group-hover:bg-white/10"
                                                    }`}
                                                >
                                                    {isOpen ? "−" : "+"}
                                                </span>
                                            )}
                                        </button>

                                        {hasChildren && isOpen && (
                                            <div className="ml-5 mt-2 space-y-1 border-l border-white/10 pl-4">
                                                {menu.children.map((child) => {
                                                    const isChildActive =
                                                        activeMenu === child.key;

                                                    return (
                                                        <button
                                                            key={child.key}
                                                            type="button"
                                                            onClick={() =>
                                                                handleSubMenuClick(
                                                                    menu.key,
                                                                    child.key
                                                                )
                                                            }
                                                            className={`flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-bold transition ${
                                                                isChildActive
                                                                    ? "bg-white text-slate-950 shadow-sm"
                                                                    : "text-slate-400 hover:bg-white/10 hover:text-white"
                                                            }`}
                                                        >
                                                            <span
                                                                className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-black ${
                                                                    isChildActive
                                                                        ? "bg-teal-100 text-teal-700"
                                                                        : "bg-white/10 text-teal-200"
                                                                }`}
                                                            >
                                                                {child.icon || "•"}
                                                            </span>

                                                            <span className="min-w-0 flex-1">
                                                                <span className="block truncate">
                                                                    {child.label}
                                                                </span>

                                                                {child.description && (
                                                                    <span
                                                                        className={`mt-0.5 block truncate text-xs font-semibold ${
                                                                            isChildActive
                                                                                ? "text-slate-500"
                                                                                : "text-slate-500"
                                                                        }`}
                                                                    >
                                                                        {
                                                                            child.description
                                                                        }
                                                                    </span>
                                                                )}
                                                            </span>

                                                            {isChildActive && (
                                                                <span className="text-teal-600">
                                                                    ●
                                                                </span>
                                                            )}
                                                        </button>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </nav>

                        <div className="border-t border-white/10 p-4">
                            <div className="rounded-3xl bg-white/10 p-4">
                                <p className="text-xs font-bold uppercase tracking-wide text-teal-200">
                                    Informasi
                                </p>

                                <p className="mt-2 text-sm leading-6 text-slate-300">
                                    Gunakan menu di atas untuk mengelola proses rekrutmen.
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>

                <section className="flex min-w-0 flex-1 flex-col">
                    <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                        <div className="flex items-center justify-between gap-4 px-5 py-4 sm:px-8">
                            <div className="flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setSidebarOpen(true)}
                                    className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-700 transition hover:bg-slate-200 lg:hidden"
                                >
                                    ☰
                                </button>

                                <div>
                                    <p className="text-xs font-bold uppercase tracking-wide text-teal-700">
                                        {activeMenuData.parentLabel ||
                                            "Sistem Rekrutmen"}
                                    </p>

                                    <h2 className="text-lg font-black text-slate-950 sm:text-xl">
                                        {activeMenuData.label}
                                    </h2>
                                </div>
                            </div>

                            <div className="flex items-center gap-3">
                                {activeMenuData.action && (
                                    <button
                                        type="button"
                                        onClick={handleHeaderAction}
                                        className="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700"
                                    >
                                        {activeMenuData.action.label}
                                    </button>
                                )}
                            </div>
                        </div>
                    </header>

                    <div className="flex-1 p-5 sm:p-8">
                        <ActiveComponent actionSignals={actionSignals} />
                    </div>
                </section>
            </div>
        </main>
    );
}

const rootElement = document.getElementById("admin-root");

if (rootElement) {
    createRoot(rootElement).render(<AdminPage />);
}