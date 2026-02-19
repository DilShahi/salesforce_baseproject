<x-layout>
    <x-slot:title>Salesforce Users</x-slot:title>
    <main style="max-width: 960px; margin: 40px auto; font-family: Arial, sans-serif;">
        <h1 style="margin-bottom: 16px;">Salesforce Users</h1>

        @if (!empty($error))
            <div style="padding: 12px; background: #fde8e8; color: #7f1d1d; border: 1px solid #fecaca;">
                {{ $error }}
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">SN</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Name</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Username</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Email</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Active</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users ?? [] as $user)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $loop->iteration }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $user['Name'] ?? '' }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $user['Username'] ?? '' }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $user['Email'] ?? '' }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $user['IsActive'] ?? false ? 'Yes' : 'No' }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                <a href="{{ route('salesforce.user.event', ['userId' => $user['Id']]) }}" type="button"
                                    style="white-space: nowrap;background:black;color:white;padding:10px;text-decoration:none">
                                    Mitoco Events
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 8px;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </main>
</x-layout>
