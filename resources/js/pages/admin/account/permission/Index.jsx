import React, { useEffect, useMemo, useRef, useState } from "react";

export default function PermissionPage({ actionSignals }) {
    const firstActionSignalRender = useRef(true);

    const [roles, setRoles] = useState([]);
    const [menus, setMenus] = useState([]);
    const [selectedRoleId, setSelectedRoleId] = useState("");
    const [selectedRole, setSelectedRole] = useState(null);
    const [selectedPermissionIds, setSelectedPermissionIds] = useState([]);

    const [loading, setLoading] = useState(false);
    const [pageLoading, setPageLoading] = useState(false);
    const [search, setSearch] = useState("");

    const getCsrfToken = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    };

    const fetchMenu = async () => {
        setPageLoading(true);

        try {
            const response = await fetch("/admin/account/permission/menu", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                setRoles(result.data?.roles || []);
                setMenus(result.data?.menus || []);
            } else {
                alert(result.message || "Gagal mengambil menu permission.");
            }
        } catch (error) {
            console.error("Gagal mengambil menu permission:", error);
            alert("Terjadi kesalahan saat mengambil menu permission.");
        } finally {
            setPageLoading(false);
        }
    };

    const fetchRolePermissions = async (roleId) => {
        if (!roleId) {
            setSelectedRole(null);
            setSelectedPermissionIds([]);
            return;
        }

        setPageLoading(true);

        try {
            const response = await fetch(
                `/admin/account/permission/role/${roleId}/permissions`,
                {
                    headers: {
                        Accept: "application/json",
                    },
                }
            );

            const result = await response.json();

            if (response.ok && result.success) {
                setSelectedRole(result.data?.role || null);
                setSelectedPermissionIds(
                    (result.data?.permission_ids || []).map((id) => String(id))
                );
            } else {
                alert(result.message || "Gagal mengambil permission role.");
            }
        } catch (error) {
            console.error("Gagal mengambil permission role:", error);
            alert("Terjadi kesalahan saat mengambil permission role.");
        } finally {
            setPageLoading(false);
        }
    };

    useEffect(() => {
        fetchMenu();
    }, []);

    useEffect(() => {
        if (firstActionSignalRender.current) {
            firstActionSignalRender.current = false;
            return;
        }

        if (actionSignals?.accountPermission > 0) {
            alert("Permission dibuat dari seeder. Gunakan Setting Permission untuk mengatur akses role.");
        }
    }, [actionSignals?.accountPermission]);

    const filteredMenus = useMemo(() => {
        const keyword = search.toLowerCase().trim();

        if (!keyword) {
            return menus;
        }

        return menus
            .map((group) => {
                const modules = group.modules
                    .map((module) => {
                        const groupMatch = String(group.group || "")
                            .toLowerCase()
                            .includes(keyword);

                        const moduleMatch = String(module.module || "")
                            .toLowerCase()
                            .includes(keyword);

                        const permissions = module.permissions.filter((permission) => {
                            const name = String(permission.name || "").toLowerCase();
                            const action = String(permission.action_label || "").toLowerCase();

                            return (
                                groupMatch ||
                                moduleMatch ||
                                name.includes(keyword) ||
                                action.includes(keyword)
                            );
                        });

                        if (groupMatch || moduleMatch || permissions.length > 0) {
                            return {
                                ...module,
                                permissions:
                                    groupMatch || moduleMatch
                                        ? module.permissions
                                        : permissions,
                            };
                        }

                        return null;
                    })
                    .filter(Boolean);

                if (modules.length > 0) {
                    return {
                        ...group,
                        modules,
                    };
                }

                return null;
            })
            .filter(Boolean);
    }, [menus, search]);

    const handleSelectRole = async (event) => {
        const roleId = event.target.value;

        setSelectedRoleId(roleId);
        await fetchRolePermissions(roleId);
    };

    const isChecked = (permissionId) => {
        return selectedPermissionIds.includes(String(permissionId));
    };

    const togglePermission = (permissionId) => {
        if (selectedRole?.is_superadmin) return;

        const id = String(permissionId);

        setSelectedPermissionIds((prev) => {
            if (prev.includes(id)) {
                return prev.filter((item) => item !== id);
            }

            return [...prev, id];
        });
    };

    const toggleModule = (permissions) => {
        if (selectedRole?.is_superadmin) return;

        const ids = permissions.map((item) => String(item.id));
        const allChecked = ids.every((id) => selectedPermissionIds.includes(id));

        setSelectedPermissionIds((prev) => {
            if (allChecked) {
                return prev.filter((id) => !ids.includes(id));
            }

            return Array.from(new Set([...prev, ...ids]));
        });
    };

    const toggleGroup = (group) => {
        if (selectedRole?.is_superadmin) return;

        const ids = group.modules.flatMap((module) =>
            module.permissions.map((permission) => String(permission.id))
        );

        const allChecked = ids.every((id) => selectedPermissionIds.includes(id));

        setSelectedPermissionIds((prev) => {
            if (allChecked) {
                return prev.filter((id) => !ids.includes(id));
            }

            return Array.from(new Set([...prev, ...ids]));
        });
    };

    const savePermissions = async () => {
        if (!selectedRoleId) {
            alert("Pilih role terlebih dahulu.");
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(
                `/admin/account/permission/role/${selectedRoleId}/permissions`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": getCsrfToken(),
                    },
                    body: JSON.stringify({
                        permission_ids: selectedPermissionIds,
                    }),
                }
            );

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Permission role gagal disimpan.");
                return;
            }

            alert(result.message || "Permission role berhasil disimpan.");

            await fetchMenu();
            await fetchRolePermissions(selectedRoleId);
        } catch (error) {
            console.error("Gagal menyimpan permission role:", error);
            alert("Terjadi kesalahan saat menyimpan permission role.");
        } finally {
            setLoading(false);
        }
    };

    const syncSuperadmin = async () => {
        setLoading(true);

        try {
            const response = await fetch("/admin/account/permission/sync-superadmin", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert(result.message || "Permission Superadmin berhasil disinkronkan.");

                if (selectedRoleId) {
                    await fetchRolePermissions(selectedRoleId);
                }
            } else {
                alert(result.message || "Gagal sync Superadmin.");
            }
        } catch (error) {
            console.error("Gagal sync Superadmin:", error);
            alert("Terjadi kesalahan saat sync Superadmin.");
        } finally {
            setLoading(false);
        }
    };

    const actionOrder = ["View", "Create", "Update", "Delete", "Setting"];

    return (
        <div className="space-y-6">
            <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div className="grid gap-5 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-black text-slate-700">
                                Role
                            </label>

                            <select
                                value={selectedRoleId}
                                onChange={handleSelectRole}
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                            >
                                <option value="">Pilih Role</option>

                                {roles.map((role) => (
                                    <option key={role.id} value={role.id}>
                                        {role.name} - {role.guard_name}
                                        {role.is_superadmin ? " - FULL ACCESS" : ""}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-black text-slate-700">
                                Search Permission
                            </label>

                            <input
                                type="text"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Cari Master Data, Jabatan, View..."
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                            />
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <button
                            type="button"
                            onClick={syncSuperadmin}
                            disabled={loading}
                            className="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60"
                        >
                            Sync Superadmin
                        </button>

                        <button
                            type="button"
                            onClick={savePermissions}
                            disabled={loading || !selectedRoleId || selectedRole?.is_superadmin}
                            className="rounded-2xl bg-teal-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {loading ? "Menyimpan..." : "Simpan Permission"}
                        </button>
                    </div>
                </div>

                {selectedRole?.is_superadmin && (
                    <div className="mt-5 rounded-2xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-700">
                        Role Superadmin otomatis mendapatkan semua permission. Checkbox dikunci agar tetap full access.
                    </div>
                )}
            </div>

            <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-6 py-5">
                    <h2 className="text-lg font-black text-slate-950">
                        Menu Permission
                    </h2>

                    <p className="mt-1 text-sm font-semibold text-slate-500">
                        Contoh: Master Data → Jabatan memiliki View, Create, Update, Delete.
                    </p>
                </div>

                {pageLoading ? (
                    <div className="px-6 py-16 text-center text-sm font-black text-slate-500">
                        Memuat permission...
                    </div>
                ) : filteredMenus.length > 0 ? (
                    <div className="space-y-6 p-6">
                        {filteredMenus.map((group) => {
                            const groupPermissionIds = group.modules.flatMap((module) =>
                                module.permissions.map((permission) => String(permission.id))
                            );

                            const groupChecked =
                                groupPermissionIds.length > 0 &&
                                groupPermissionIds.every((id) =>
                                    selectedPermissionIds.includes(id)
                                );

                            return (
                                <div
                                    key={group.group}
                                    className="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50"
                                >
                                    <div className="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <h3 className="text-base font-black text-slate-950">
                                                {group.group}
                                            </h3>

                                            <p className="mt-1 text-xs font-bold text-slate-500">
                                                {group.modules.length} menu
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            disabled={!selectedRoleId || selectedRole?.is_superadmin}
                                            onClick={() => toggleGroup(group)}
                                            className="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {groupChecked ? "Uncheck Semua Group" : "Check Semua Group"}
                                        </button>
                                    </div>

                                    <div className="overflow-x-auto">
                                        <table className="min-w-full">
                                            <thead>
                                                <tr className="bg-slate-100">
                                                    <th className="w-[260px] px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                                                        Menu
                                                    </th>

                                                    {actionOrder.map((action) => (
                                                        <th
                                                            key={action}
                                                            className="px-5 py-4 text-center text-xs font-black uppercase tracking-[0.12em] text-slate-500"
                                                        >
                                                            {action}
                                                        </th>
                                                    ))}

                                                    <th className="px-5 py-4 text-right text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                                                        Semua
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody className="divide-y divide-slate-200 bg-white">
                                                {group.modules.map((module) => {
                                                    const modulePermissionIds =
                                                        module.permissions.map((permission) =>
                                                            String(permission.id)
                                                        );

                                                    const moduleChecked =
                                                        modulePermissionIds.length > 0 &&
                                                        modulePermissionIds.every((id) =>
                                                            selectedPermissionIds.includes(id)
                                                        );

                                                    return (
                                                        <tr key={module.module} className="hover:bg-slate-50">
                                                            <td className="px-5 py-4">
                                                                <div className="font-black text-slate-900">
                                                                    {module.module}
                                                                </div>

                                                                <div className="mt-1 text-xs font-semibold text-slate-500">
                                                                    {module.permissions.length} permission
                                                                </div>
                                                            </td>

                                                            {actionOrder.map((action) => {
                                                                const permission = module.permissions.find(
                                                                    (item) => item.action_label === action
                                                                );

                                                                if (!permission) {
                                                                    return (
                                                                        <td
                                                                            key={action}
                                                                            className="px-5 py-4 text-center text-slate-300"
                                                                        >
                                                                            -
                                                                        </td>
                                                                    );
                                                                }

                                                                return (
                                                                    <td key={action} className="px-5 py-4 text-center">
                                                                        <label className="inline-flex cursor-pointer items-center justify-center">
                                                                            <input
                                                                                type="checkbox"
                                                                                checked={isChecked(permission.id)}
                                                                                disabled={
                                                                                    !selectedRoleId ||
                                                                                    selectedRole?.is_superadmin
                                                                                }
                                                                                onChange={() =>
                                                                                    togglePermission(permission.id)
                                                                                }
                                                                                className="h-5 w-5 rounded border-slate-300 text-teal-600 focus:ring-teal-500 disabled:cursor-not-allowed disabled:opacity-50"
                                                                            />
                                                                        </label>
                                                                    </td>
                                                                );
                                                            })}

                                                            <td className="px-5 py-4 text-right">
                                                                <button
                                                                    type="button"
                                                                    disabled={
                                                                        !selectedRoleId ||
                                                                        selectedRole?.is_superadmin
                                                                    }
                                                                    onClick={() => toggleModule(module.permissions)}
                                                                    className="rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                                                >
                                                                    {moduleChecked ? "Uncheck" : "Check"}
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="px-6 py-16 text-center">
                        <h3 className="text-lg font-black text-slate-900">
                            Permission tidak ditemukan
                        </h3>

                        <p className="mt-2 text-sm font-semibold text-slate-500">
                            Jalankan seeder permission atau ubah kata kunci pencarian.
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}