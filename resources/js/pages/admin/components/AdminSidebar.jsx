import React from "react";
import { isChildActive, isMenuActive, menuItems } from "./adminMenuConfig";

export default function AdminSidebar({
    sidebarOpen,
    collapsed,
    activeMenu,
    openMenus,
    onCloseSidebar,
    onMenuClick,
    onSubMenuClick,
    onToggleCollapsed,
    onExpand,
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
                className={`fixed inset-y-0 left-0 z-40 flex h-screen w-72 shrink-0 transform flex-col overflow-hidden bg-slate-950 text-white shadow-2xl transition-all duration-300 lg:sticky lg:top-0 lg:translate-x-0 ${collapsed ? "lg:w-24" : "lg:w-72"} ${
                    sidebarOpen ? "translate-x-0" : "-translate-x-full"
                }`}
            >
                <SidebarBrand collapsed={collapsed} onToggle={onToggleCollapsed} />

                <nav className={`min-h-0 flex-1 space-y-2 overflow-y-auto overflow-x-hidden overscroll-contain scrollbar-thin scrollbar-track-slate-900 scrollbar-thumb-slate-700 ${collapsed ? "lg:px-3 lg:py-4" : "p-4 pr-3"}`}>
                    {menuItems.map((menu) => {
                        const hasChildren = Array.isArray(menu.children);
                        const active = isMenuActive(menu, activeMenu);
                        const open = Boolean(openMenus[menu.key]);
                        const sectionLabel = getSectionLabel(menu.key);

                        return (
                            <React.Fragment key={menu.key}>
                                {sectionLabel && (
                                    <div className={`pt-3 first:pt-0 ${collapsed ? "lg:px-2" : "px-3"}`}>
                                        <div className={`flex items-center gap-2 ${collapsed ? "lg:justify-center" : ""}`}>
                                            <span className="h-px flex-1 bg-white/10" />
                                            <span className={`text-[9px] font-black uppercase tracking-[0.18em] text-slate-500 ${collapsed ? "lg:hidden" : ""}`}>{sectionLabel}</span>
                                            <span className="h-px flex-1 bg-white/10" />
                                        </div>
                                    </div>
                                )}

                                <div>
                                    <SidebarMenuButton
                                        menu={menu}
                                        active={active}
                                        open={open}
                                        hasChildren={hasChildren}
                                        collapsed={collapsed}
                                        onClick={() => {
                                            if (collapsed && hasChildren) onExpand?.();
                                            onMenuClick(menu);
                                        }}
                                    />

                                    {hasChildren && open && (
                                        <div className={`ml-5 mt-2 space-y-1 border-l border-indigo-300/20 pl-4 ${collapsed ? "lg:hidden" : ""}`}>
                                            {menu.children.map((child) => (
                                                <SidebarSubMenuButton key={child.key} child={child} active={isChildActive(child, activeMenu)} onClick={() => onSubMenuClick(menu.key, child.key)} />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </React.Fragment>
                        );
                    })}
                </nav>

                <SidebarInfo collapsed={collapsed} />
            </aside>
        </>
    );
}

function SidebarBrand({ collapsed, onToggle }) {
    return (
        <div className={`relative shrink-0 border-b border-white/10 ${collapsed ? "lg:px-3 lg:py-4" : "p-5"}`}>
            <div className={`flex items-center ${collapsed ? "lg:justify-center" : "gap-4"}`}>
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-400 text-lg font-black text-slate-950 shadow-lg shadow-indigo-950/30">
                    HR
                </div>

                <div className={`min-w-0 ${collapsed ? "lg:hidden" : ""}`}>
                    <p className="truncate text-xs font-bold uppercase tracking-[0.22em] text-indigo-200">
                        Admin Panel
                    </p>

                    <h1 className="mt-1 truncate text-lg font-black text-white">
                        Rekrutmen
                    </h1>
                </div>
            </div>

            <button
                type="button"
                onClick={onToggle}
                className={`hidden h-8 w-8 items-center justify-center rounded-xl border border-indigo-300/30 bg-indigo-500 text-lg font-black text-white shadow-lg shadow-indigo-950/30 transition hover:bg-violet-500 lg:flex ${collapsed ? "relative mx-auto mt-3" : "absolute right-4 top-1/2 -translate-y-1/2"}`}
                aria-label={collapsed ? "Buka sidebar" : "Tutup sidebar"}
                title={collapsed ? "Buka sidebar" : "Tutup sidebar"}
            >
                {collapsed ? "›" : "‹"}
            </button>
        </div>
    );
}

function getSectionLabel(menuKey) {
    if (menuKey === "dashboard") return "Workspace";
    if (menuKey === "master-data") return "Persiapan Data";
    if (menuKey === "data-pelamar") return "Alur Rekrutmen";
    return null;
}

function SidebarMenuButton({ menu, active, open, hasChildren, collapsed, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            title={collapsed ? menu.label : undefined}
            aria-label={menu.label}
            className={`group flex w-full items-center rounded-2xl py-3 text-left transition ${collapsed ? "lg:justify-center lg:px-2" : "gap-4 px-4"} ${
                active
                    ? "bg-indigo-400 text-slate-950 shadow-lg shadow-indigo-950/30"
                    : "text-slate-300 hover:bg-white/10 hover:text-white"
            }`}
        >
            <span
                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg font-black ${
                    active
                        ? "bg-slate-950 text-indigo-200"
                        : "bg-white/10 text-indigo-200 group-hover:bg-white/15"
                }`}
            >
                {menu.icon}
            </span>

            <span className={`min-w-0 flex-1 ${collapsed ? "lg:hidden" : ""}`}>
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

            {hasChildren && !collapsed && (
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
                        ? "bg-indigo-100 text-indigo-700"
                        : "bg-white/10 text-indigo-200"
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

            {active && <span className="shrink-0 text-indigo-600">●</span>}
        </button>
    );
}

function SidebarInfo({ collapsed }) {
    return (
        <div className={`shrink-0 border-t border-white/10 p-4 ${collapsed ? "lg:hidden" : ""}`}>
            <div className="rounded-3xl bg-white/10 p-4">
                {/* 
                <p className="text-xs font-bold uppercase tracking-wide text-indigo-200">
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
