<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({ email: String })

const form = useForm({ code: '' })

const submit = () => form.post(route('passwordless.login-code.authenticate'))
</script>

<template>
    <form @submit.prevent="submit">
        <div>
            <label for="code">Enter your login code</label>
            <input
                id="code"
                v-model="form.code"
                type="text"
                placeholder="123456"
                autocomplete="one-time-code"
                required
            />
            <span v-if="form.errors.code">{{ form.errors.code }}</span>
        </div>

        <button type="submit" :disabled="form.processing">
            Verify code
        </button>
    </form>
</template>

