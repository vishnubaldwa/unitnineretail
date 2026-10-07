<?php
define('UNR_ADMIN', true);
session_start();

$config = require __DIR__ . '/config.php';
$isLoggedIn = !empty($_SESSION['unr_admin_logged']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['company_name']) ?> - Email Admin Portal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

<?php if (!$isLoggedIn): ?>
<!-- ================= LOGIN SCREEN ================= -->
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 mb-4 ring-8 ring-brand-50/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($config['company_name']) ?></h1>
            <p class="text-slate-500 text-sm mt-1">Email Management Admin Portal</p>
        </div>

        <div id="loginAlert" class="hidden mb-4 p-3.5 rounded-xl text-sm bg-rose-50 text-rose-700 border border-rose-100"></div>

        <form id="loginForm" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Username</label>
                <input type="text" id="loginUsername" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm" placeholder="Enter username">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="loginPassword" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm" placeholder="••••••••">
                    <button type="button" onclick="togglePassVisibility('loginPassword')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-xs font-medium">Show</button>
                </div>
            </div>
            <button type="submit" id="loginSubmitBtn" class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-all shadow-md shadow-brand-500/25 flex items-center justify-center gap-2 text-sm">
                <span>Sign In to Dashboard</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <a href="/" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 font-medium">
                <span>Open Employee Webmail</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>
    </div>
</div>

<script>
function togglePassVisibility(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('loginSubmitBtn');
    const alertBox = document.getElementById('loginAlert');
    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Verifying...`;
    alertBox.classList.add('hidden');

    try {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', document.getElementById('loginUsername').value);
        formData.append('password', document.getElementById('loginPassword').value);

        const res = await fetch('api.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            window.location.reload();
        } else {
            alertBox.textContent = data.error || 'Invalid credentials';
            alertBox.classList.remove('hidden');
        }
    } catch (err) {
        alertBox.textContent = 'Server error. Please check your connection.';
        alertBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<span>Sign In to Dashboard</span>`;
    }
});
</script>

<?php else: ?>
<!-- ================= LOGGED IN DASHBOARD ================= -->
<div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold shadow-md shadow-brand-500/20">
                        UN
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 text-base"><?= htmlspecialchars($config['company_name']) ?></span>
                        <span class="block text-xs text-slate-400 font-medium">Mailbox Administrator</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-600 hover:text-brand-600 bg-slate-100 hover:bg-brand-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Open Webmail</span>
                    </a>

                    <button onclick="openAdminSettingsModal()" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Settings
                    </button>

                    <button onclick="logout()" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Mailboxes</p>
                        <h3 id="statTotalAccounts" class="text-3xl font-extrabold text-slate-900 mt-1">--</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active on domain
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Storage Used</p>
                        <h3 id="statTotalDisk" class="text-3xl font-extrabold text-slate-900 mt-1">-- MB</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <span>Hostinger VPS Storage</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mail Domain</p>
                        <h3 class="text-xl font-bold text-slate-900 mt-2 truncate"><?= htmlspecialchars($config['domain']) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> cPanel UAPI Connected
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/70 shadow-sm mb-6">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <input type="text" id="searchInput" oninput="filterAccounts()" placeholder="Search employees by email..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="loadAccounts()" class="p-2.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors" title="Refresh list">
                        <svg id="refreshIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>

                    <button onclick="openCreateModal()" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-brand-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Create New Email ID</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Accounts Table -->
        <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/75 border-b border-slate-200/70 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Employee Email</th>
                            <th class="px-6 py-4">Storage Usage</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accountsTableBody" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <svg class="animate-spin h-6 w-6 text-brand-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Loading mailboxes...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Create Email Modal -->
<div id="createModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-900">Create New Employee Email</h3>
            <button onclick="closeModal('createModal')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div id="createAlert" class="hidden mb-4 p-3 rounded-xl text-sm bg-rose-50 text-rose-700 border border-rose-100"></div>

        <form id="createEmailForm" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <div class="flex rounded-xl shadow-sm">
                    <input type="text" id="newEmailPrefix" required placeholder="e.g. rahul, support, sales" class="flex-1 px-4 py-2.5 rounded-l-xl border border-r-0 border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <span class="inline-flex items-center px-4 rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 text-slate-500 font-medium text-sm">
                        @<?= htmlspecialchars($config['domain']) ?>
                    </span>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                    <button type="button" onclick="generatePassword('newEmailPass')" class="text-xs text-brand-600 hover:text-brand-700 font-semibold">⚡ Auto Generate</button>
                </div>
                <div class="relative">
                    <input type="text" id="newEmailPass" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-mono" placeholder="Enter or generate password">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Storage Quota</label>
                <select id="newEmailQuota" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="500">500 MB</option>
                    <option value="1024" selected>1024 MB (1 GB) - Recommended</option>
                    <option value="2048">2048 MB (2 GB)</option>
                    <option value="5120">5120 MB (5 GB)</option>
                    <option value="0">Unlimited</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createModal')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancel</button>
                <button type="submit" id="createSubmitBtn" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-brand-500/20">
                    Create Mailbox
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Account Created Success Modal (with Copy for WhatsApp) -->
<div id="successModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-center">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-4 ring-8 ring-emerald-50/50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-1">Mailbox Created!</h3>
        <p class="text-xs text-slate-500 mb-6">Account is live and ready for use.</p>

        <div class="bg-slate-50 rounded-xl p-4 text-left space-y-2 mb-6 border border-slate-100 text-xs font-mono">
            <div><span class="text-slate-400">Email:</span> <strong id="succEmail" class="text-slate-800"></strong></div>
            <div><span class="text-slate-400">Password:</span> <strong id="succPass" class="text-slate-800"></strong></div>
            <div><span class="text-slate-400">Webmail:</span> <a href="/" target="_blank" class="text-brand-600 underline">https://<?= htmlspecialchars($config['domain']) ?>/</a></div>
        </div>

        <button onclick="copyCredentialsWhatsApp()" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-emerald-600/20 mb-3 flex items-center justify-center gap-2">
            <span>📋 Copy Details for Employee (WhatsApp)</span>
        </button>

        <button onclick="closeModal('successModal')" class="w-full py-2.5 text-xs text-slate-500 hover:text-slate-700 font-medium">
            Done
        </button>
    </div>
</div>

<!-- 3. Change Password Modal -->
<div id="changePassModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <h3 class="text-lg font-bold text-slate-900 mb-1">Reset Password</h3>
        <p id="resetPassEmail" class="text-xs text-slate-500 mb-5 font-mono"></p>

        <form id="changePassForm" class="space-y-4">
            <input type="hidden" id="resetPassTarget">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">New Password</label>
                    <button type="button" onclick="generatePassword('resetNewPass')" class="text-xs text-brand-600 font-semibold">⚡ Auto Generate</button>
                </div>
                <input type="text" id="resetNewPass" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-mono">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('changePassModal')" class="px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-brand-500/20">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Change Quota Modal -->
<div id="changeQuotaModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <h3 class="text-lg font-bold text-slate-900 mb-1">Change Storage Quota</h3>
        <p id="quotaEmail" class="text-xs text-slate-500 mb-5 font-mono"></p>

        <form id="changeQuotaForm" class="space-y-4">
            <input type="hidden" id="quotaTarget">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Quota Limit</label>
                <select id="quotaNewLimit" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="500">500 MB</option>
                    <option value="1024">1024 MB (1 GB)</option>
                    <option value="2048">2048 MB (2 GB)</option>
                    <option value="5120">5120 MB (5 GB)</option>
                    <option value="0">Unlimited</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('changeQuotaModal')" class="px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-brand-500/20">
                    Save Quota
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Admin Settings Modal -->
<div id="adminSettingsModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <h3 class="text-lg font-bold text-slate-900 mb-1">Admin Portal Settings</h3>
        <p class="text-xs text-slate-500 mb-5">Change your owner portal password</p>

        <form id="adminSettingsForm" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Current Admin Password</label>
                <input type="password" id="currAdminPass" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">New Admin Password</label>
                <input type="password" id="newAdminPass" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('adminSettingsModal')" class="px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl text-sm">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let allAccounts = [];

// Load Accounts from cPanel
async function loadAccounts() {
    const tbody = document.getElementById('accountsTableBody');
    const refreshIcon = document.getElementById('refreshIcon');
    refreshIcon.classList.add('animate-spin');

    try {
        const res = await fetch('api.php?action=list');
        const data = await res.json();

        if (data.success) {
            allAccounts = data.data || [];
            renderAccounts(allAccounts);
            updateStats(allAccounts);
        } else {
            tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-rose-500 text-sm">${data.error || 'Failed to load accounts'}</td></tr>`;
        }
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-rose-500 text-sm">Connection error</td></tr>`;
    } finally {
        refreshIcon.classList.remove('animate-spin');
    }
}

function updateStats(accounts) {
    document.getElementById('statTotalAccounts').textContent = accounts.length;
    let totalDiskMB = 0;
    accounts.forEach(acc => {
        totalDiskMB += parseFloat(acc.diskused || 0);
    });
    document.getElementById('statTotalDisk').textContent = totalDiskMB.toFixed(1) + ' MB';
}

function renderAccounts(accounts) {
    const tbody = document.getElementById('accountsTableBody');
    if (!accounts.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                    <p class="font-medium text-slate-600 mb-1">No email accounts yet</p>
                    <p class="text-xs">Click "Create New Email ID" to create your first employee mailbox.</p>
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = accounts.map(acc => {
        const pct = Math.min(100, Math.round(acc.diskusedpercent || 0));
        let barColor = 'bg-brand-500';
        if (pct > 80) barColor = 'bg-rose-500';
        else if (pct > 60) barColor = 'bg-amber-500';

        const isSuspended = acc.suspended_in || acc.suspended_out;
        const quotaText = (acc.diskquota == 0 || acc.diskquota == '0') ? 'Unlimited' : acc.diskquota + ' MB';

        return `
            <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs uppercase">
                            ${acc.email.substring(0, 2)}
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900 block">${acc.email}</span>
                            <span class="text-xs text-slate-400">Login: ${acc.login || acc.email}</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="w-48">
                        <div class="flex justify-between text-xs font-medium text-slate-600 mb-1">
                            <span>${(acc.diskused || 0)} MB</span>
                            <span class="text-slate-400">of ${quotaText}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="${barColor} h-1.5 rounded-full" style="width: ${pct}%"></div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    ${isSuspended ? 
                        `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>` : 
                        `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>`
                    }
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="inline-flex items-center gap-1.5">
                        <button onclick="openResetModal('${acc.email}')" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg" title="Change Password">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </button>
                        <button onclick="openQuotaModal('${acc.email}', '${acc.diskquota}')" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg" title="Change Quota">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                        </button>
                        <button onclick="deleteAccount('${acc.email}')" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg" title="Delete Mailbox">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function filterAccounts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allAccounts.filter(acc => acc.email.toLowerCase().includes(q));
    renderAccounts(filtered);
}

// Generate secure random password
function generatePassword(targetId) {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById(targetId).value = pass;
}

// Modal Helpers
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openCreateModal() {
    document.getElementById('newEmailPrefix').value = '';
    generatePassword('newEmailPass');
    document.getElementById('createAlert').classList.add('hidden');
    openModal('createModal');
}

function openResetModal(email) {
    document.getElementById('resetPassTarget').value = email;
    document.getElementById('resetPassEmail').textContent = email;
    generatePassword('resetNewPass');
    openModal('changePassModal');
}

function openQuotaModal(email, currentQuota) {
    document.getElementById('quotaTarget').value = email;
    document.getElementById('quotaEmail').textContent = email;
    document.getElementById('quotaNewLimit').value = currentQuota || 1024;
    openModal('changeQuotaModal');
}

function openAdminSettingsModal() {
    openModal('adminSettingsModal');
}

// Form Handlers
document.getElementById('createEmailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('createSubmitBtn');
    const alertBox = document.getElementById('createAlert');
    btn.disabled = true;
    btn.textContent = 'Creating...';
    alertBox.classList.add('hidden');

    const email = document.getElementById('newEmailPrefix').value;
    const password = document.getElementById('newEmailPass').value;
    const quota = document.getElementById('newEmailQuota').value;

    const fd = new FormData();
    fd.append('action', 'create');
    fd.append('email', email);
    fd.append('password', password);
    fd.append('quota', quota);

    try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            closeModal('createModal');
            document.getElementById('succEmail').textContent = data.data.email;
            document.getElementById('succPass').textContent = data.data.password;
            openModal('successModal');
            loadAccounts();
        } else {
            alertBox.textContent = data.error || 'Failed to create email';
            alertBox.classList.remove('hidden');
        }
    } catch (err) {
        alertBox.textContent = 'Connection error';
        alertBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Create Mailbox';
    }
});

// Copy Credentials for WhatsApp
function copyCredentialsWhatsApp() {
    const email = document.getElementById('succEmail').textContent;
    const pass = document.getElementById('succPass').textContent;
    const text = `Welcome to Unit Nine Retail!\n\nYour official work email has been created:\n📧 Email: ${email}\n🔑 Password: ${pass}\n🌐 Login Webmail: https://<?= htmlspecialchars($config['domain']) ?>/\n\nPlease log in and keep your password safe.`;
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard! You can paste and send directly to the employee via WhatsApp or Email.');
}

// Change Password Handler
document.getElementById('changePassForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('resetPassTarget').value;
    const password = document.getElementById('resetNewPass').value;

    const fd = new FormData();
    fd.append('action', 'change_password');
    fd.append('email', email);
    fd.append('password', password);

    const res = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        alert(data.data.message);
        closeModal('changePassModal');
    } else {
        alert(data.error || 'Failed to update password');
    }
});

// Change Quota Handler
document.getElementById('changeQuotaForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('quotaTarget').value;
    const quota = document.getElementById('quotaNewLimit').value;

    const fd = new FormData();
    fd.append('action', 'change_quota');
    fd.append('email', email);
    fd.append('quota', quota);

    const res = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        alert(data.data.message);
        closeModal('changeQuotaModal');
        loadAccounts();
    } else {
        alert(data.error || 'Failed to update quota');
    }
});

// Delete Account
async function deleteAccount(email) {
    if (!confirm(`Are you sure you want to permanently delete ${email}? All emails in this mailbox will be deleted.`)) return;

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('email', email);

    const res = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        alert(data.data.message);
        loadAccounts();
    } else {
        alert(data.error || 'Failed to delete');
    }
}

// Admin Settings (Change admin pass)
document.getElementById('adminSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('action', 'update_admin_pass');
    fd.append('current_pass', document.getElementById('currAdminPass').value);
    fd.append('new_pass', document.getElementById('newAdminPass').value);

    const res = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        alert('Admin password updated successfully!');
        closeModal('adminSettingsModal');
    } else {
        alert(data.error || 'Failed to update password');
    }
});

// Logout
async function logout() {
    await fetch('api.php?action=logout');
    window.location.reload();
}

// Initial load
loadAccounts();
</script>
<?php endif; ?>

</body>
</html>
