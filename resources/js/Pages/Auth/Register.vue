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
    name: '',
    cpf: '',
    email: '',
    telefone: '',
    cep: '',
    logradouro: '',
    numero: '',
    complemento: '',
    bairro: '',
    cidade: '',
    estado: '',
    is_mei: false,
    password: '',
    password_confirmation: '',
});

function maskCpf(v) {
    const d = v.replace(/\D/g, '').slice(0, 11);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`;
    if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
    return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
}

function maskPhone(v) {
    const d = v.replace(/\D/g, '').slice(0, 11);
    if (d.length <= 0) return '';
    if (d.length <= 2) return `(${d}`;
    if (d.length <= 7) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
    return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
}

function maskCep(v) {
    const d = v.replace(/\D/g, '').slice(0, 8);
    if (d.length <= 5) return d;
    return `${d.slice(0, 5)}-${d.slice(5)}`;
}

function onCpfInput(e) {
    form.cpf = maskCpf(e.target.value);
}

function onPhoneInput(e) {
    form.telefone = maskPhone(e.target.value);
}

async function onCepInput(e) {
    form.cep = maskCep(e.target.value);
    const clean = form.cep.replace(/\D/g, '');
    if (clean.length === 8) {
        try {
            const res = await fetch(`https://viacep.com.br/ws/${clean}/json/`);
            const data = await res.json();
            if (!data.erro) {
                form.logradouro = data.logradouro || '';
                form.bairro = data.bairro || '';
                form.cidade = data.localidade || '';
                form.estado = data.uf || '';
            }
        } catch { /* ignore */ }
    }
}

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Cadastro do Prestador" />

    <AuthenticationCard :wide="true">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <h2 class="text-center text-xl font-bold text-gray-800 mb-6">Cadastro do Prestador</h2>

        <form @submit.prevent="submit">
            <!-- Nome e CPF -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="name" value="Nome Completo *" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="cpf" value="CPF *" />
                    <TextInput
                        id="cpf"
                        :value="form.cpf"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="000.000.000-00"
                        required
                        @input="onCpfInput"
                    />
                    <InputError class="mt-2" :message="form.errors.cpf" />
                </div>
            </div>

            <!-- Email e Telefone -->
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <InputLabel for="email" value="Email *" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel for="telefone" value="Telefone *" />
                    <TextInput
                        id="telefone"
                        :value="form.telefone"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="(XX) XXXXX-XXXX"
                        required
                        @input="onPhoneInput"
                    />
                    <InputError class="mt-2" :message="form.errors.telefone" />
                </div>
            </div>

            <hr class="my-5 border-gray-200" />

            <h3 class="text-sm font-semibold text-gray-700 mb-3">Endereço</h3>

            <!-- CEP e Logradouro -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="cep" value="CEP *" />
                    <TextInput
                        id="cep"
                        :value="form.cep"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="00000-000"
                        required
                        @input="onCepInput"
                    />
                    <InputError class="mt-2" :message="form.errors.cep" />
                </div>
                <div>
                    <InputLabel for="logradouro" value="Endereço (Rua/Av.) *" />
                    <TextInput
                        id="logradouro"
                        v-model="form.logradouro"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.logradouro" />
                </div>
            </div>

            <!-- Número, Complemento e Bairro -->
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div>
                    <InputLabel for="numero" value="Número *" />
                    <TextInput
                        id="numero"
                        v-model="form.numero"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.numero" />
                </div>
                <div>
                    <InputLabel for="complemento" value="Complemento" />
                    <TextInput
                        id="complemento"
                        v-model="form.complemento"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.complemento" />
                </div>
                <div>
                    <InputLabel for="bairro" value="Bairro *" />
                    <TextInput
                        id="bairro"
                        v-model="form.bairro"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.bairro" />
                </div>
            </div>

            <!-- Cidade e Estado -->
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="col-span-2">
                    <InputLabel for="cidade" value="Cidade *" />
                    <TextInput
                        id="cidade"
                        v-model="form.cidade"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.cidade" />
                </div>
                <div>
                    <InputLabel for="estado" value="Estado (UF) *" />
                    <TextInput
                        id="estado"
                        v-model="form.estado"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="2"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.estado" />
                </div>
            </div>

            <hr class="my-5 border-gray-200" />

            <!-- MEI e Senha -->
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <Checkbox v-model:checked="form.is_mei" name="is_mei" />
                        <span class="text-sm text-gray-600">Sou MEI (Opcional)</span>
                    </label>
                </div>
                <div>
                    <InputLabel for="password" value="Senha *" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar Senha *" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Cadastrar
                </PrimaryButton>
            </div>

            <div class="mt-4 text-center text-sm text-gray-500">
                <Link :href="route('login')" class="text-green-500 hover:underline">
                    Voltar para login
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
