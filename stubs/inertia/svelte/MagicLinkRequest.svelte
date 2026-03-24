<script>
    import { useForm } from '@inertiajs/svelte'

    const form = useForm({ email: '' })
</script>

<form on:submit|preventDefault={() => form.post(route('passwordless.magic-link.send'))}>
    <div>
        <label for="email">Email address</label>
        <input
            id="email"
            type="email"
            bind:value={$form.email}
            placeholder="your@email.com"
            autocomplete="email"
            required
        />
        {#if $form.errors.email}
            <span>{$form.errors.email}</span>
        {/if}
    </div>

    <button type="submit" disabled={$form.processing}>
        Send magic link
    </button>
</form>

