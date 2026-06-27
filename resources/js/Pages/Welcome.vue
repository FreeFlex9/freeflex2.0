<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
});
</script>

<template>
    <Head title="Início" />

    <div class="min-h-screen bg-white">
        <!-- Navbar -->
        <nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <div class="flex justify-center items-center gap-2">
                            <img src="/images/logoFreeFlex.png" alt="FreeFlex" class="h-10 w-auto rounded-full" />
                            <span class="text-xl font-bold text-teal-600">FreeFlex</span>
                        </div> 
                    </div>

                    <!-- Nav links -->
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('empresa.login')"
                            class="text-sm font-semibold text-teal-600 border border-teal-500 px-4 py-2 rounded-lg hover:bg-teal-50 transition"
                        >
                            Sou Empresa
                        </Link>
                        <Link
                            :href="route('prestador.login')"
                            class="text-sm font-semibold bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition"
                        >
                            Sou Prestador
                        </Link>
                    </div> 
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center">
                <span class="inline-block text-xs font-semibold text-emerald-500 bg-emerald-50 rounded-full px-3 py-1 mb-6 tracking-wide uppercase">
                    Plataforma de Freelancers
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                    Conectamos <span class="text-emerald-500">empresas</span> aos melhores
                    <span class="text-orange-400">prestadores</span> de serviço
                </h1>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto mb-10">
                    Contrate profissionais qualificados para projetos pontuais ou de longa duração,
                    com segurança, agilidade e sem burocracia.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        v-if="canRegister"
                        :href="route('empresa.register')"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-500 text-white font-semibold px-8 py-4 rounded-xl hover:bg-emerald-600 transition text-base shadow-lg shadow-emerald-200"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Sou Empresa — Começar grátis
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('prestador.register')"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 border-2 border-orange-400 text-orange-500 font-semibold px-8 py-4 rounded-xl hover:bg-orange-50 transition text-base"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Sou Prestador — Criar perfil
                    </Link>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="py-12 bg-emerald-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-orange-400">
                    <div>
                        <div class="text-3xl font-extrabold">10k+</div>
                        <div class="text-emerald-400 text-sm mt-1">Prestadores ativos</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold">2k+</div>
                        <div class="text-emerald-400 text-sm mt-1">Empresas cadastradas</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold">50k+</div>
                        <div class="text-emerald-400 text-sm mt-1">Projetos concluídos</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold">98%</div>
                        <div class="text-emerald-400 text-sm mt-1">Satisfação</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Como funciona -->
        <section id="como-funciona" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Como funciona</h2>
                    <p class="text-gray-500 max-w-xl mx-auto">Um processo simples e transparente para empresas e prestadores.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div class="text-orange-400 font-bold text-sm mb-2">Passo 1</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Publique seu projeto</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">A empresa descreve o que precisa, prazo e orçamento. Em minutos seu projeto fica visível para centenas de profissionais.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="text-orange-400 font-bold text-sm mb-2">Passo 2</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Receba propostas</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Prestadores qualificados enviam propostas. Compare perfis, portfólios e avaliações para escolher o ideal.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="text-orange-400 font-bold text-sm mb-2">Passo 3</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Trabalhe com segurança</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Pagamento garantido via escrow. O prestador recebe somente após a entrega aprovada pela empresa.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Para Empresas -->
        <section id="para-empresas" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-block text-xs font-semibold text-emerald-500 bg-emerald-50 rounded-full px-3 py-1 mb-4 uppercase tracking-wide">Para Empresas</span>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Contrate os melhores profissionais sem complicação</h2>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm">Publique projetos gratuitamente e receba propostas em até 24h</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm">Perfis verificados com portfólio, avaliações e histórico</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm">Contratos e pagamentos 100% dentro da plataforma</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link
                                :href="route('empresa.register')"
                                class="inline-flex items-center gap-2 bg-emerald-500 text-white font-semibold px-6 py-3 rounded-xl hover:bg-emerald-600 transition"
                            >
                                Cadastrar minha empresa
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                    <div class="bg-emerald-50 rounded-3xl p-10 flex items-center justify-center">
                        <svg class="w-48 h-48 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Para Prestadores -->
        <section id="para-prestadores" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="bg-emerald-500 rounded-3xl p-10 flex items-center justify-center order-last lg:order-first">
                        <svg class="w-48 h-48 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="inline-block text-xs font-semibold text-orange-500 bg-orange-100 rounded-full px-3 py-1 mb-4 uppercase tracking-wide">Para Prestadores</span>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Trabalhe de onde quiser e receba pelo que merece</h2>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm">Crie seu perfil e apareça para centenas de empresas</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm">Defina sua própria taxa e negocie diretamente</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-600 text-sm">Receba em até 2 dias úteis após a conclusão</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <Link
                                :href="route('prestador.register')"
                                class="inline-flex items-center gap-2 border-2 border-orange-400 text-orange-500 font-semibold px-6 py-3 rounded-xl hover:bg-orange-50 transition"
                            >
                                Criar meu perfil de prestador
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-10 border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center">
                    <img src="/images/logoFreeFlex.png" alt="FreeFlex" class="h-7 w-auto" />
                </div>
                <p class="text-sm text-gray-400">&copy; {{ new Date().getFullYear() }} FreeFlex. Todos os direitos reservados.</p>
                <div class="flex items-center gap-4">
                    <Link :href="route('empresa.login')" class="text-sm text-gray-500 hover:text-teal-600 transition">Empresa</Link>
                    <Link :href="route('prestador.login')" class="text-sm text-gray-500 hover:text-orange-500 transition">Prestador</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
