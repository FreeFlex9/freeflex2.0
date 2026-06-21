<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    cnpj: '',
    nome_fantasia: '',
    email: '',
    telefone: '',
    cep: '',
    logradouro: '',
    numero: '',
    complemento: '',
    bairro: '',
    cidade: '',
    estado: '',
    password: '',
    password_confirmation: '',
});

function maskCnpj(v) {
    const d = v.replace(/\D/g, '').slice(0, 14);
    if (d.length <= 2) return d;
    if (d.length <= 5) return `${d.slice(0, 2)}.${d.slice(2)}`;
    if (d.length <= 8) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5)}`;
    if (d.length <= 12) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8)}`;
    return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8, 12)}-${d.slice(12)}`;
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

function onCnpjInput(e) {
    form.cnpj = maskCnpj(e.target.value);
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
    form.post(route('empresa.register.submit'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Cadastro da Empresa" />

    <AuthenticationCard :wide="true">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <h2 class="text-center text-xl font-bold text-gray-800 mb-6">Cadastro da Empresa</h2>

        <form @submit.prevent="submit">
            <!-- CNPJ e Nome Fantasia -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="cnpj" value="CNPJ *" />
                    <TextInput
                        id="cnpj"
                        :value="form.cnpj"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="00.000.000/0000-00"
                        required
                        autofocus
                        @input="onCnpjInput"
                    />
                    <InputError class="mt-2" :message="form.errors.cnpj" />
                </div>
                <div>
                    <InputLabel for="nome_fantasia" value="Nome Fantasia *" />
                    <TextInput
                        id="nome_fantasia"
                        v-model="form.nome_fantasia"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="organization"
                    />
                    <InputError class="mt-2" :message="form.errors.nome_fantasia" />
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

            <h3 class="text-sm font-semibold text-gray-700 mb-3">Endereço da Empresa *</h3>

            <!-- CEP e Logradouro -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="cep" value="CEP" />
                    <TextInput
                        id="cep"
                        :value="form.cep"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="00000-000"
                        @input="onCepInput"
                    />
                    <InputError class="mt-2" :message="form.errors.cep" />
                </div>
                <div>
                    <InputLabel for="logradouro" value="Endereço (Rua/Av.)" />
                    <TextInput
                        id="logradouro"
                        v-model="form.logradouro"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.logradouro" />
                </div>
            </div>

            <!-- Número, Complemento e Bairro -->
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div>
                    <InputLabel for="numero" value="Número" />
                    <TextInput
                        id="numero"
                        v-model="form.numero"
                        type="text"
                        class="mt-1 block w-full"
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
                    <InputLabel for="bairro" value="Bairro" />
                    <TextInput
                        id="bairro"
                        v-model="form.bairro"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.bairro" />
                </div>
            </div>

            <!-- Cidade e Estado -->
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="col-span-2">
                    <InputLabel for="cidade" value="Cidade" />
                    <TextInput
                        id="cidade"
                        v-model="form.cidade"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.cidade" />
                </div>
                <div>
                    <InputLabel for="estado" value="Estado (UF)" />
                    <TextInput
                        id="estado"
                        v-model="form.estado"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="2"
                    />
                    <InputError class="mt-2" :message="form.errors.estado" />
                </div>
            </div>

            <hr class="my-5 border-gray-200" />

            <!-- Senha -->
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
                <Link :href="route('login')" class="text-blue-500 hover:underline">
                    Voltar para login
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
