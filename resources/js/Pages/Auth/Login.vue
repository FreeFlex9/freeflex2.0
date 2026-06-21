<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Entrar" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <h2 class="text-center text-xl font-bold text-gray-800 mb-6">Entrar</h2>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="CPF, CNPJ ou e-mail" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="text"
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

            <div v-if="canResetPassword" class="mt-3 flex justify-end items-center gap-2 text-sm text-gray-500">
                <Link :href="route('password.request')" class="underline hover:text-gray-700">
                    Esqueci a senha (prestador)
                </Link>
                <span>|</span>
                <Link :href="route('password.request')" class="underline hover:text-gray-700">
                    (empresa)
                </Link>
            </div>

            <div class="mt-5">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Entrar
                </PrimaryButton>
            </div>

            <div class="mt-6 flex flex-col items-center gap-2 text-sm">
                <Link :href="route('register')" class="text-blue-500 hover:underline">
                    Cadastrar como prestador
                </Link>
                <Link :href="route('empresa.register')" class="text-blue-500 hover:underline">
                    Cadastrar como empresa
                </Link>
                <Link :href="route('home')" class="text-gray-400 hover:underline">
                    Voltar para página inicial
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
