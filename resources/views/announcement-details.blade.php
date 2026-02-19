<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $announcement->title }} | Barangay Aliaga</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#16a34a',
                        'primary-foreground': '#ffffff',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('welcome') }}" class="flex items-center space-x-2 text-gray-700 hover:text-primary transition">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    <span class="font-medium">Back to Home</span>
                </a>
                <span class="text-sm font-semibold text-gray-400">Announcement Details</span>
            </div>
        </div>
    </header>

    <main class="py-12 px-4 sm:px-6 lg:px-8">
        <article class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            
            <div class="relative h-[300px] md:h-[400px] w-full bg-gray-200">
                @php
                    $imgSrc = $announcement->cover_image ? asset('storage/' . $announcement->cover_image) : 'https://placehold.co/1200x600/e2e8f0/1e293b?text=Announcement';
                @endphp
                <img src="{{ $imgSrc }}" alt="{{ $announcement->title }}" class="w-full h-full object-cover">
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <div class="flex items-center space-x-3 mb-4">
                        @if($announcement->priority === 'emergency')
                            <span class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">Emergency</span>
                        @elseif($announcement->is_pinned)
                            <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">Featured</span>
                        @endif
                        <span class="bg-black/30 backdrop-blur-sm border border-white/20 px-3 py-1 rounded-full text-xs font-medium">
                            {{ \Carbon\Carbon::parse($announcement->published_at)->format('F d, Y') }}
                        </span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-bold leading-tight text-shadow-sm">
                        {{ $announcement->title }}
                    </h1>
                </div>
            </div>

            <div class="p-8 md:p-12">
                <div class="flex items-center justify-between border-b border-gray-100 pb-8 mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-primary">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Posted by</p>
                            <p class="font-medium text-gray-900">Barangay Secretariat</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-primary transition" title="Share">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="prose prose-lg prose-green max-w-none text-gray-600">
                    {!! $announcement->content !!}
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-center">
                <a href="{{ route('welcome') }}#announcements" class="text-primary hover:text-green-800 font-medium text-sm">
                    View Other Announcements
                </a>
            </div>
        </article>
    </main>

    @include('partials.footer')

    <script>
        lucide.createIcons();
    </script>
</body>
</html>