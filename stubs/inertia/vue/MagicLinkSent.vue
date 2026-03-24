<template>
    <div>
        <p>Magic link sent! Check your email and click the link to sign in.</p>
        <p>The link will expire in {{ $page.props.ttl ?? 15 }} minutes.</p>
    </div>
</template>

