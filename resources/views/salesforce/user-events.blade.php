<x-layout>
    <x-slot:title>Home Page</x-slot>
    <main style="max-width: 960px; margin: 40px auto; font-family: Arial, sans-serif;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <h1 style="margin-bottom: 16px;">Mitoco Events</h1>
            @if (!empty($hasSubmittedDates) && !empty($events))
                <form method="POST" action="{{ route('salesforce.user.event.summary', ['userId' => $userId]) }}">
                    @csrf
                    <textarea name="events" hidden>@json($events ?? [])</textarea>
                    <button type="submit"
                        style="display: inline-block; padding: 6px 10px; border: 1px solid #111827; background: #ffffff; color: #111827; border-radius: 4px; text-decoration: none;">
                        Get Summary
                    </button>
                </form>
            @endif
            <a href="{{ route('salesforce.userlist') }}"
                style="display: inline-block; padding: 6px 10px; border: 1px solid #111827; background: #ffffff; color: #111827; border-radius: 4px; text-decoration: none;">
                Back to Users
            </a>
        </div>

        <form method="GET" action="{{ route('salesforce.user.event', ['userId' => $userId]) }}"
            style="display: flex; align-items: end; gap: 12px; margin-bottom: 16px;">
            <div>
                <label for="start_date" style="display: block; font-size: 14px; margin-bottom: 4px;">Start Date</label>
                <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $startDate ?? '') }}"
                    style="padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <div>
                <label for="end_date" style="display: block; font-size: 14px; margin-bottom: 4px;">End Date</label>
                <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $endDate ?? '') }}"
                    style="padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <button type="submit"
                style="display: inline-block; padding: 6px 10px; border: 1px solid #111827; background: #ffffff; color: #111827; border-radius: 4px; text-decoration: none;">
                Filter Events
            </button>
        </form>

        @if ($errors->any())
            <div style="padding: 12px; background: #fde8e8; color: #7f1d1d; border: 1px solid #fecaca; margin-bottom: 16px;">
                @foreach ($errors->all() as $errorMessage)
                    <div>{{ $errorMessage }}</div>
                @endforeach
            </div>
        @endif

        @if (!empty($error))
            <div style="padding: 12px; background: #fde8e8; color: #7f1d1d; border: 1px solid #fecaca;">
                {{ $error }}
            </div>
        @elseif (!empty($hasSubmittedDates))
            @if (!empty($summary))
                <div
                    style="padding: 12px; background: #eef2ff; color: #1e1b4b; border: 1px solid #c7d2fe; margin-bottom: 16px;">
                    <strong>Summary</strong>
                    <div style="white-space: pre-wrap; margin-top: 8px;">{{ $summary }}</div>
                </div>
            @endif
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">SN</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Subject</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">Start</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb;">End</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events ?? [] as $event)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $loop->iteration }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $event['Subject'] ?? '' }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $event['StartDateTime'] ?? '' }}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                                {{ $event['EndDateTime'] ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 8px;">No events found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </main>
</x-layout>
