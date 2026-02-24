<x-layout>
    <x-slot:title>Home Page</x-slot>
    <main style="max-width: 960px; margin: 40px auto; font-family: Arial, sans-serif;">
        @if (!session('sf_access_token'))
            <a href="{{ route('sf.redirect') }}"
                style="display: inline-block; padding: 10px 14px; border: 1px solid #111827; background: #111827; color: #ffffff; border-radius: 4px; text-decoration: none;">
                Login with Salesforce
            </a>
        @endif
    </main>
</x-layout>
