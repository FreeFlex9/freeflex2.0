<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';

import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('empresa.login.submit'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Entrar como Empresa" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <h2 class="text-center text-lg font-semibold text-gray-800 mb-6">Acesso Empresa</h2>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail corporativo" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Senha" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Lembrar acesso</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton
                    class="w-full justify-center bg-emerald-500 hover:bg-emerald-600"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Entrar
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center text-sm text-gray-500">
                Não tem conta?
                <Link :href="route('empresa.register')" class="text-emerald-500 dark:text-emerald-400 font-medium hover:underline">
                    Cadastrar empresa
                </Link>
            </div>

            <div class="mt-3 text-center text-sm text-gray-400">
                <Link :href="route('login')" class="hover:underline">
                    Sou prestador de serviços →
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
