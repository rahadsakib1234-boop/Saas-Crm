<x-admin::layouts.anonymous>
    <x-slot:title>
        Sign In — ComplyBuild
    </x-slot>

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4 py-8">
        <!-- Logo & Branding -->
        <div class="flex flex-col items-center mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold text-white tracking-tight">ComplyBuild</span>
            </div>
            <p class="text-slate-400 text-sm">Deal Intelligence CRM</p>
        </div>

        <!-- Login Card -->
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-black/20 overflow-hidden">
                @if(session('error'))
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-800">
                        <p class="text-sm text-yellow-600 dark:text-yellow-400">{{ session('warning') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Login Form - Using relative URL to avoid proxy issues -->
                <form method="POST" action="/admin/login">
                    @csrf
                    <div class="p-6 sm:p-8">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 text-center">
                            Sign in to your account
                        </h1>

                        <!-- Email -->
                        <div class="mb-5">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Email Address
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="you@company.com"
                                required
                                autocomplete="email"
                            />
                        </div>

                        <!-- Password -->
                        <div class="mb-6 relative">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Password
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                onclick="switchVisibility()"
                                class="absolute right-3 top-9 text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors"
                            >
                                <svg class="w-5 h-5" id="eyeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-6 flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-slate-400">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
                        >
                            Sign In
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 sm:px-8 py-4 bg-gray-50 dark:bg-slate-700/50 border-t border-gray-100 dark:border-slate-600">
                        <a
                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors"
                            href="/admin/forget-password"
                        >
                            Forgot your password?
                        </a>
                    </div>
                </form>
            </div>

            <!-- Subtle Footer -->
            <p class="text-center text-slate-500 text-xs mt-6">
                Powered by <span class="text-slate-400">ComplyBuild</span>
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchVisibility() {
                let passwordField = document.getElementById("password");
                let eyeIcon = document.getElementById("eyeIcon");
                
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
                } else {
                    passwordField.type = "password";
                    eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                }
            }
        </script>
    @endpush
</x-admin::layouts.anonymous>
