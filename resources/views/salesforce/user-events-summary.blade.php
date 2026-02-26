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

        @if (!empty($summaryText))
            <div
                style="padding: 12px; background: #eef2ff; color: #1e1b4b; border: 1px solid #c7d2fe; margin-bottom: 16px;">
                <strong>Summary</strong>
                <div style="white-space: pre-wrap; margin-top: 8px;">{{ $summaryText }}</div>
            </div>
        @endif

        @if (!empty($categoryDetails))
            <div style="padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 16px;">
                <strong>Categorized Events</strong>
                @foreach ($categoryDetails as $category)
                    <details
                        style="margin-top: 12px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; background: #ffffff;">
                        <summary style="cursor: pointer; font-weight: 600;">
                            {{ $category['name'] }} ({{ $category['count'] }})
                        </summary>
                        @if (!empty($category['events']))
                            <ul style="margin-top: 10px; padding-left: 18px;">
                                @foreach ($category['events'] as $event)
                                    <li style="margin-bottom: 8px;">
                                        <div>{{ $event['subject'] }}</div>
                                        @if (!empty($event['startDateTime']) || !empty($event['endDateTime']))
                                            <div style="font-size: 12px; color: #4b5563;">
                                                {{ $event['startDateTime'] ?: '-' }} to
                                                {{ $event['endDateTime'] ?: '-' }}
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div style="margin-top: 8px; font-size: 14px; color: #6b7280;">No events listed.</div>
                        @endif
                    </details>
                @endforeach
            </div>
        @endif

        @if (!empty($chartLabels) && !empty($chartCounts))
            <div style="padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 16px;">
                <strong>Event Categories</strong>
                <canvas id="eventSummaryChart" style="margin-top: 12px;"></canvas>
            </div>
        @endif



    </main>

    @if (!empty($chartLabels) && !empty($chartCounts))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartLabels = @json($chartLabels);
            const chartCounts = @json($chartCounts);
            const chartElement = document.getElementById('eventSummaryChart');

            if (chartElement) {
                new Chart(chartElement, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Events',
                            data: chartCounts,
                            backgroundColor: '#60a5fa',
                            borderColor: '#2563eb',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endif
</x-layout>
