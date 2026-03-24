import { useForm } from '@inertiajs/react'

export default function MagicLinkRequest() {
    const { data, setData, post, processing, errors } = useForm({ email: '' })

    function submit(e) {
        e.preventDefault()
        post(route('passwordless.magic-link.send'))
    }

    return (
        <form onSubmit={submit}>
            <div>
                <label htmlFor="email">Email address</label>
                <input
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={e => setData('email', e.target.value)}
                    placeholder="your@email.com"
                    autoComplete="email"
                    required
                />
                {errors.email && <span>{errors.email}</span>}
            </div>

            <button type="submit" disabled={processing}>
                Send magic link
            </button>
        </form>
    )
}

