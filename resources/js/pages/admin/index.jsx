import "../../../css/app.css";

import React, { useState } from "react";
import { createRoot } from "react-dom/client";

import DashboardPage from "./pages/DashboardPage";
import PendaftarPage from "./pages/PendaftarPage";
import PendaftarBaruPage from "./pages/PendaftarBaruPage";
import PendaftarArsipPage from "./pages/PendaftarArsipPage";
import TahapanSeleksiPage from "./pages/TahapanSeleksiPage";


function AdminPage() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [activeMenu, setActiveMenu] = useState("dashboard");
    const [openMenus, setOpenMenus] = useState({
        pendaftar: true,
    });

    const menuItems = [
        {
            key: "dashboard",
            label: "Dashboard",
            description: "Ringkasan utama",
            icon: "⌂",
            component: DashboardPage,
        },
        {
            key: "pendaftar",
            label: "Data Pendaftar",
            description: "Kelola data pelamar",
            icon: "◉",
            children: [
                {
                    key: "pendaftar-semua",
                    label: "Semua Pendaftar",
                    component: PendaftarPage,
                },
                {
                    key: "pendaftar-baru",
                    label: "Pendaftar Baru",
                    component: PendaftarBaruPage,
                },
                {
                    key: "pendaftar-arsip",
                    label: "Arsip Pendaftar",
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
                parentLabel: menu.label,
            }));
        }

        return [menu];
    });

    const activeMenuData =
        flattenMenus.find((item) => item.key === activeMenu) || menuItems[0];

    const ActiveComponent = activeMenuData.component || DashboardPage;

    const toggleMenu = (key) => {
        setOpenMenus((prev) => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    const handleMenuClick = (menu) => {
        if (menu.children) {
            toggleMenu(menu.key);

            if (menu.children.length > 0) {
                setActiveMenu(menu.children[0].key);
            }

            return;
        }

        setActiveMenu(menu.key);
        setSidebarOpen(false);
    };

    const handleSubMenuClick = (childKey) => {
        setActiveMenu(childKey);
        setSidebarOpen(false);
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
                        <div className="border-b border-white/10 p-6">
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

                                const isOpen = openMenus[menu.key];

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
                                                <span className="block text-sm font-black">
                                                    {menu.label}
                                                </span>
                                                <span
                                                    className={`mt-0.5 block text-xs ${
                                                        isActive
                                                            ? "text-slate-700"
                                                            : "text-slate-400"
                                                    }`}
                                                >
                                                    {menu.description}
                                                </span>
                                            </span>

                                            {hasChildren && (
                                                <span className="text-sm font-black">
                                                    {isOpen ? "−" : "+"}
                                                </span>
                                            )}
                                        </button>

                                        {hasChildren && isOpen && (
                                            <div className="ml-6 mt-2 space-y-1 border-l border-white/10 pl-4">
                                                {menu.children.map((child) => {
                                                    const isChildActive =
                                                        activeMenu === child.key;

                                                    return (
                                                        <button
                                                            key={child.key}
                                                            type="button"
                                                            onClick={() =>
                                                                handleSubMenuClick(
                                                                    child.key
                                                                )
                                                            }
                                                            className={`flex w-full items-center justify-between rounded-xl px-4 py-2 text-left text-sm font-bold transition ${
                                                                isChildActive
                                                                    ? "bg-white text-slate-950"
                                                                    : "text-slate-400 hover:bg-white/10 hover:text-white"
                                                            }`}
                                                        >
                                                            <span>{child.label}</span>
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
                                    Menu dan submenu bisa ditambahkan dari array{" "}
                                    <span className="font-bold text-white">
                                        menuItems
                                    </span>
                                    .
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
                                <button
                                    type="button"
                                    className="hidden rounded-2xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200 sm:inline-flex"
                                >
                                    Export
                                </button>

                                <button
                                    type="button"
                                    className="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700"
                                >
                                    Tambah Data
                                </button>
                            </div>
                        </div>
                    </header>

                    <div className="flex-1 p-5 sm:p-8">
                        <ActiveComponent />
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