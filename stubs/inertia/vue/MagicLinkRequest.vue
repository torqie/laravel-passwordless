<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ email: '' })

const submit = () => form.post(route('passwordless.magic-link.send'))
</script>

<template>
    <form @submit.prevent="submit">
        <div>
            <label for="email">Email address</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="your@email.com"
                autocomplete="email"
                required
            />
            <span v-if="form.errors.email">{{ form.errors.email }}</span>
        </div>

        <button type="submit" :disabled="form.processing">
            Send magic link
        </button>
    </form>
</template>

