<script>
    import { useForm } from '@inertiajs/svelte'

    export let email

    const form = useForm({ code: '' })
</script>

<form on:submit|preventDefault={() => form.post(route('passwordless.login-code.authenticate'))}>
    <div>
        <label for="code">Enter your login code</label>
        <input
            id="code"
            type="text"
            bind:value={$form.code}
            placeholder="123456"
            autocomplete="one-time-code"
            required
        />
        {#if $form.errors.code}
            <span>{$form.errors.code}</span>
        {/if}
    </div>

    <button type="submit" disabled={$form.processing}>
        Verify code
    </button>
</form>

