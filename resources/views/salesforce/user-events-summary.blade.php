<x-layout>
    <x-slot:title>Home Page</x-slot>
    <main style="max-width: 960px; margin: 40px auto; font-family: Arial, sans-serif;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <h1 style="margin-bottom: 16px;">Mitoco Events Summary</h1>
            <a href="{{ route('salesforce.userlist') }}"
                style="display: inline-block; padding: 6px 10px; border: 1px solid #111827; background: #ffffff; color: #111827; border-radius: 4px; text-decoration: none;">
                Back to Users
            </a>
        </div>

        @if (!empty($summary))
            <div
                style="padding: 12px; background: #eef2ff; color: #1e1b4b; border: 1px solid #c7d2fe; margin-bottom: 16px;">
                <strong>Summary</strong>
                <div style="white-space: pre-wrap; margin-top: 8px;">{{ $summary }}</div>
            </div>
        @endif
    </main>
</x-layout>
