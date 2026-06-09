import React from "react";
import { isChildActive, isMenuActive, menuItems } from "./adminMenuConfig";

export default function AdminSidebar({
    sidebarOpen,
    activeMenu,
    openMenus,
    onCloseSidebar,
    onMenuClick,
    onSubMenuClick,
}) {
    return (
        <>
            {sidebarOpen && (
                <button
                    type="button"
                    onClick={onCloseSidebar}
                    className="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"
                    aria-label="Tutup sidebar"
                />
            )}

            <aside
                className={`fixed inset-y-0 left-0 z-40 flex h-screen w-72 shrink-0 transform flex-col overflow-hidden bg-slate-950 text-white shadow-2xl transition-transform duration-300 lg:sticky lg:top-0 lg:translate-x-0 ${
                    sidebarOpen ? "translate-x-0" : "-translate-x-full"
                }`}
            >
                <SidebarBrand />

                <nav className="min-h-0 flex-1 space-y-2 overflow-y-auto overscroll-contain p-4 pr-3 scrollbar-thin scrollbar-track-slate-900 scrollbar-thumb-slate-700">
                    {menuItems.map((menu) => {
                        const hasChildren = Array.isArray(menu.children);
                        const active = isMenuActive(menu, activeMenu);
                        const open = Boolean(openMenus[menu.key]);

                        return (
                            <div key={menu.key}>
                                <SidebarMenuButton
                                    menu={menu}
                                    active={active}
                                    open={open}
                                    hasChildren={hasChildren}
                                    onClick={() => onMenuClick(menu)}
                                />

                                {hasChildren && open && (
                                    <div className="ml-5 mt-2 space-y-1 border-l border-white/10 pl-4">
                                        {menu.children.map((child) => (
                                            <SidebarSubMenuButton
                                                key={child.key}
                                                child={child}
                                                active={isChildActive(
                                                    child,
                                                    activeMenu
                                                )}
                                                onClick={() =>
                                                    onSubMenuClick(
                                                        menu.key,
                                                        child.key
                                                    )
                                                }
                                            />
                                        ))}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </nav>

                <SidebarInfo />
            </aside>
        </>
    );
}

function SidebarBrand() {
    return (
        <div className="shrink-0 border-b border-white/10 p-5">
            <div className="flex items-center gap-4">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-400 text-lg font-black text-slate-950 shadow-lg shadow-teal-950/30">
                    HR
                </div>

                <div className="min-w-0">
                    <p className="truncate text-xs font-bold uppercase tracking-[0.22em] text-teal-200">
                        Admin Panel
                    </p>

                    <h1 className="mt-1 truncate text-lg font-black text-white">
                        Rekrutmen
                    </h1>
                </div>
            </div>
        </div>
    );
}

function SidebarMenuButton({ menu, active, open, hasChildren, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`group flex w-full items-center gap-4 rounded-2xl px-4 py-3 text-left transition ${
                active
                    ? "bg-teal-400 text-slate-950 shadow-lg shadow-teal-950/30"
                    : "text-slate-300 hover:bg-white/10 hover:text-white"
            }`}
        >
            <span
                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg font-black ${
                    active
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
                        active ? "text-slate-700" : "text-slate-400"
                    }`}
                >
                    {menu.description}
                </span>
            </span>

            {hasChildren && (
                <span
                    className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-sm font-black transition ${
                        active
                            ? "bg-slate-950/10 text-slate-950"
                            : "bg-white/5 text-slate-300 group-hover:bg-white/10"
                    }`}
                >
                    {open ? "−" : "+"}
                </span>
            )}
        </button>
    );
}

function SidebarSubMenuButton({ child, active, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-bold transition ${
                active
                    ? "bg-white text-slate-950 shadow-sm"
                    : "text-slate-400 hover:bg-white/10 hover:text-white"
            }`}
        >
            <span
                className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-black ${
                    active
                        ? "bg-teal-100 text-teal-700"
                        : "bg-white/10 text-teal-200"
                }`}
            >
                {child.icon || "•"}
            </span>

            <span className="min-w-0 flex-1">
                <span className="block truncate">{child.label}</span>

                {child.description && (
                    <span
                        className={`mt-0.5 block truncate text-xs font-semibold ${
                            active ? "text-slate-500" : "text-slate-500"
                        }`}
                    >
                        {child.description}
                    </span>
                )}
            </span>

            {active && <span className="shrink-0 text-teal-600">●</span>}
        </button>
    );
}

function SidebarInfo() {
    return (
        <div className="shrink-0 border-t border-white/10 p-4">
            <div className="rounded-3xl bg-white/10 p-4">
                {/* 
                <p className="text-xs font-bold uppercase tracking-wide text-teal-200">
                    Informasi
                </p>

                <p className="mt-2 text-sm leading-6 text-slate-300">
                    Gunakan menu di atas untuk mengelola proses rekrutmen.
                </p>
                */}
            </div>
        </div>
    );
}
