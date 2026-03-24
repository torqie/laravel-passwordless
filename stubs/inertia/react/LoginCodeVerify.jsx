import { useForm } from '@inertiajs/react'

export default function LoginCodeVerify({ email }) {
    const { data, setData, post, processing, errors } = useForm({ code: '' })

    function submit(e) {
        e.preventDefault()
        post(route('passwordless.login-code.authenticate'))
    }

    return (
        <form onSubmit={submit}>
            <div>
                <label htmlFor="code">Enter your login code</label>
                <input
                    id="code"
                    type="text"
                    value={data.code}
                    onChange={e => setData('code', e.target.value)}
                    placeholder="123456"
                    autoComplete="one-time-code"
                    required
                />
                {errors.code && <span>{errors.code}</span>}
            </div>

            <button type="submit" disabled={processing}>
                Verify code
            </button>
        </form>
    )
}

