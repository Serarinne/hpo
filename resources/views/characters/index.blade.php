<!DOCTYPE html>
<html lang="en-US" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Character Management - {{ env('APP_NAME') }}</title>
    <x-assets />
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .break-inside-avoid { break-inside: avoid; }
        .card-hover { transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease; }
        .card-hover:hover { transform: translateY(-4px); border-color: rgba(34, 211, 238, 0.28); box-shadow: 0 14px 30px rgba(0,0,0,0.25); }
        summary::-webkit-details-marker { display: none; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        details.custom-dropdown > summary {
            list-style: none;
        }
        details.custom-dropdown > summary::-webkit-details-marker {
            display: none;
        }
    </style>
    <style>
    .swal2-popup {
        border-radius: 1.5rem !important;
    }

    .swal2-select {
        background: #020617 !important;
        color: #e2e8f0 !important;
        border: 1px solid #1e293b !important;
        border-radius: 0.75rem !important;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.25) !important;
    }

    .swal2-select:focus {
        outline: none !important;
        border-color: rgb(6 182 212 / 0.6) !important;
        box-shadow: 0 0 0 3px rgba(6,182,212,.18) !important;
    }

    .swal2-validation-message {
        background: rgba(127, 29, 29, 0.25) !important;
        color: #fecaca !important;
        border-radius: 0.75rem !important;
    }
</style>
</head>
<body class="bg-slate-950 text-slate-200 font-sans min-h-screen flex flex-col selection:bg-cyan-500 selection:text-white">
    <x-navbar />
    
    <main class="flex-grow pt-8 pb-32 sm:pt-12 relative overflow-hidden text-slate-300">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-cyan-500/10 blur-[120px] pointer-events-none rounded-full" aria-hidden="true"></div>

        <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-6 border-b border-white/10 pb-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-widest">
                        <span class="text-cyan-400">Admin Panel</span>
                        <span class="text-slate-700">&bull;</span>
                        <span class="text-slate-400">Characters</span>
                        <span class="text-slate-700">&bull;</span>
                        <span class="text-slate-500 bg-slate-800/50 px-2 py-0.5 rounded-md border border-white/5">Listing</span>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-none">
                        Character <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 drop-shadow-sm">Management</span>
                    </h1>
                    <p class="text-sm text-slate-400 font-medium pt-1">Browse, filter, and manage all your character entities.</p>
                </div>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="relative mb-8 z-30 group">
                <div class="absolute inset-0 bg-slate-900/60 border border-white/10 rounded-[2rem] shadow-xl backdrop-blur-md overflow-hidden pointer-events-none z-0">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-cyan-500/10 blur-[80px] group-hover:bg-cyan-500/20 transition-colors"></div>
                </div>

                <div class="relative z-10 flex flex-col gap-5 p-5 sm:p-6">
                    <div class="w-full relative z-10">
                        <label for="search" class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Search Query</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name, tags, or description..." class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-slate-600 focus:ring-2 focus:ring-cyan-500/50 outline-none transition-all shadow-inner">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-end flex-wrap relative z-20">
                        <div class="w-full md:w-36 relative">
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Rating</label>
                            <details class="group custom-dropdown relative">
                                <summary class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-cyan-500/50 outline-none transition-all shadow-inner cursor-pointer list-none flex justify-between items-center select-none">
                                    @php $ratingCount = count(request('rating', [])); @endphp
                                    <span class="text-sm truncate mr-2 {{ $ratingCount > 0 ? 'text-cyan-400 font-bold' : 'text-slate-400 font-medium' }}">{{ $ratingCount > 0 ? $ratingCount . ' Selected' : 'All Ratings' }}</span>
                                    <svg class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="absolute top-full left-0 z-50 w-full mt-2 bg-slate-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl p-2 flex flex-col gap-1 max-h-60 overflow-y-auto">
                                    @foreach(['general', 'sensitive', 'questionable', 'explicit', 'unknown'] as $rating)
                                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800/50 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" name="rating[]" value="{{ $rating }}" @checked(in_array($rating, request('rating', []))) class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-900 shadow-inner">
                                            <span class="text-xs font-bold text-slate-300 capitalize">{{ $rating }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="w-full md:w-36 relative">
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">SEO Status</label>
                            <details class="group custom-dropdown relative">
                                <summary class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-cyan-500/50 outline-none transition-all shadow-inner cursor-pointer list-none flex justify-between items-center select-none">
                                    @php $seoCount = count(request('seo', [])); @endphp
                                    <span class="text-sm truncate mr-2 {{ $seoCount > 0 ? 'text-purple-400 font-bold' : 'text-slate-400 font-medium' }}">{{ $seoCount > 0 ? $seoCount . ' Selected' : 'All SEO' }}</span>
                                    <svg class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="absolute top-full left-0 z-50 w-full mt-2 bg-slate-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl p-2 flex flex-col gap-1">
                                    @foreach(['filled', 'empty'] as $seo)
                                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800/50 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" name="seo[]" value="{{ $seo }}" @checked(in_array($seo, request('seo', []))) class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-purple-500 focus:ring-purple-500 focus:ring-offset-slate-900 shadow-inner">
                                            <span class="text-xs font-bold text-slate-300 capitalize">{{ $seo }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="w-full md:w-36 relative">
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Workflow</label>
                            <details class="group custom-dropdown relative">
                                <summary class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-cyan-500/50 outline-none transition-all shadow-inner cursor-pointer list-none flex justify-between items-center select-none">
                                    @php $workflowCount = count(request('workflow', [])); @endphp
                                    <span class="text-sm truncate mr-2 {{ $workflowCount > 0 ? 'text-emerald-400 font-bold' : 'text-slate-400 font-medium' }}">{{ $workflowCount > 0 ? $workflowCount . ' Selected' : 'All Flow' }}</span>
                                    <svg class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="absolute top-full left-0 z-50 w-full mt-2 bg-slate-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl p-2 flex flex-col gap-1">
                                    @foreach(['debug', 'ready'] as $workflow)
                                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800/50 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" name="workflow[]" value="{{ $workflow }}" @checked(in_array($workflow, request('workflow', []))) class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-900 shadow-inner">
                                            <span class="text-xs font-bold text-slate-300 capitalize">{{ $workflow }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="w-full md:w-40 relative">
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Has Wallpaper</label>
                            <details class="group custom-dropdown relative">
                                <summary class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-cyan-500/50 outline-none transition-all shadow-inner cursor-pointer list-none flex justify-between items-center select-none">
                                    @php $wallpaperCount = count(request('has_wallpaper', [])); @endphp
                                    <span class="text-sm truncate mr-2 {{ $wallpaperCount > 0 ? 'text-pink-400 font-bold' : 'text-slate-400 font-medium' }}">{{ $wallpaperCount > 0 ? $wallpaperCount . ' Selected' : 'Any' }}</span>
                                    <svg class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="absolute top-full left-0 z-50 w-full mt-2 bg-slate-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl p-2 flex flex-col gap-1">
                                    @foreach(['yes', 'no'] as $status)
                                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800/50 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" name="has_wallpaper[]" value="{{ $status }}" @checked(in_array($status, request('has_wallpaper', []))) class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-pink-500 focus:ring-pink-500 focus:ring-offset-slate-900 shadow-inner">
                                            <span class="text-xs font-bold text-slate-300 capitalize">{{ $status }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="w-full md:w-36 relative">
                            <label class="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Has Series</label>
                            <details class="group custom-dropdown relative">
                                <summary class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-cyan-500/50 outline-none transition-all shadow-inner cursor-pointer list-none flex justify-between items-center select-none">
                                    @php $seriesCount = count(request('has_series', [])); @endphp
                                    <span class="text-sm truncate mr-2 {{ $seriesCount > 0 ? 'text-orange-400 font-bold' : 'text-slate-400 font-medium' }}">{{ $seriesCount > 0 ? $seriesCount . ' Selected' : 'Any' }}</span>
                                    <svg class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </summary>
                                <div class="absolute top-full left-0 z-50 w-full mt-2 bg-slate-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl p-2 flex flex-col gap-1">
                                    @foreach(['yes', 'no'] as $status)
                                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-800/50 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" name="has_series[]" value="{{ $status }}" @checked(in_array($status, request('has_series', []))) class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-orange-500 focus:ring-orange-500 focus:ring-offset-slate-900 shadow-inner">
                                            <span class="text-xs font-bold text-slate-300 capitalize">{{ $status }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="w-full md:w-auto flex flex-1 gap-3 md:justify-end">
                            <button type="submit" class="flex-1 md:flex-none px-7 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-black rounded-xl shadow-[0_0_15px_rgba(34,211,238,0.2)] hover:shadow-[0_0_25px_rgba(34,211,238,0.4)] transition-all flex items-center justify-center gap-2 outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Filter
                            </button>
                            @if(request()->anyFilled(['search', 'rating', 'seo', 'workflow', 'has_wallpaper', 'has_series']))
                                <a href="{{ url()->current() }}" class="flex-1 md:flex-none px-5 py-3 bg-slate-800 hover:bg-red-500/20 border border-transparent hover:border-red-500/30 text-slate-300 hover:text-red-400 font-bold rounded-xl transition-all text-center flex items-center justify-center outline-none">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-5 mb-12 relative z-10">
                @forelse($characters as $character)
                    @php
    $firstSeries = $character->series->first();
    $seriesSlug = $firstSeries?->slug ?? 'unknown';
    $seriesName = $firstSeries?->name ?? '';
    
    $firstLetter = Str::upper(Str::substr($character->name, 0, 1));
    $hasImage = !empty($character->image);

    $ratingStyles = [
        'general'      => ['text' => 'GEN', 'class' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'],
        'sensitive'    => ['text' => 'SEN', 'class' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30 shadow-[0_0_15px_rgba(234,179,8,0.2)]'],
        'questionable' => ['text' => 'QST', 'class' => 'bg-orange-500/20 text-orange-400 border-orange-500/30 shadow-[0_0_15px_rgba(249,115,22,0.2)]'],
        'explicit'     => ['text' => 'EXP', 'class' => 'bg-rose-500/20 text-rose-400 border-rose-500/30 shadow-[0_0_15px_rgba(244,63,94,0.2)]'],
        'unknown'      => ['text' => 'UNK', 'class' => 'bg-slate-500/20 text-slate-300 border-slate-500/30 shadow-[0_0_15px_rgba(100,116,139,0.2)]'],
    ];

    $currentRating = $ratingStyles[$character->rating] ?? $ratingStyles['unknown'];
@endphp

<article
    id="character-card-{{ $character->id }}"
    x-data="{ isDebug: {{ $character->debug ? 'true' : 'false' }} }"
    class="break-inside-avoid block group relative rounded-[1.5rem] overflow-hidden border border-white/5 bg-slate-950 aspect-square shadow-lg hover:shadow-[0_0_25px_rgba(34,211,238,0.2)] hover:border-cyan-500/40 transform hover:-translate-y-1 transition-all duration-300 outline-none"
    title="{{ $character->name }}"
    aria-label="{{ $character->name }}"
    itemscope
    itemtype="https://schema.org/Person"
>
    <a href="{{ route('characters.edit', ['id' => $character->id]) }}" class="absolute inset-0 z-10 outline-none"></a>

    <div class="absolute inset-0 z-0 {{ !$hasImage ? 'flex justify-center items-center p-6 bg-slate-900/50 backdrop-blur-sm' : 'bg-slate-950' }}">
        @if($hasImage)
            <picture class="w-full h-full block group-hover:scale-110 transition-transform duration-700 ease-out transform-gpu">
                <source srcset="{{ $character->image['webp'] }}" type="image/webp">
                <img itemprop="image" src="{{ $character->image['jpg'] }}" alt="{{ $character->name }}" loading="lazy" decoding="async" width="400" height="400" class="w-full h-full object-cover" />
            </picture>
        @else
            <div class="w-20 h-20 bg-cyan-500/10 border border-cyan-500/30 rounded-[1.25rem] flex items-center justify-center text-cyan-400 font-black text-4xl uppercase shadow-[inset_0_0_20px_rgba(34,211,238,0.1)] group-hover:shadow-[inset_0_0_30px_rgba(34,211,238,0.3)] group-hover:bg-cyan-500/20 transition-all duration-500" aria-hidden="true">
                {{ $firstLetter }}
            </div>
        @endif
    </div>

    <div class="absolute top-3 left-3 flex flex-col gap-2 z-30 pointer-events-none">
        <button type="button" 
            @click.prevent.stop="isDebug = !isDebug; window.toggleDebug({{ $character->id }}, isDebug)" 
            class="pointer-events-auto w-8 h-8 rounded-xl border flex items-center justify-center transition-all duration-300 backdrop-blur-md outline-none" 
            :class="isDebug ? 'bg-amber-500/20 text-amber-400 border-amber-500/50 shadow-[0_0_15px_rgba(245,158,11,0.3)]' : 'bg-slate-900/60 text-slate-400 border-white/10 hover:border-cyan-500/40 hover:text-cyan-400 hover:shadow-[0_0_15px_rgba(34,211,238,0.2)]'" 
            title="Toggle Debug Mode">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg> 
        </button>

        <a href="{{ route('characters.merge.form', ['id' => $character->id]) }}"
           @click.stop
           class="pointer-events-auto w-8 h-8 rounded-xl border flex items-center justify-center transition-all duration-300 backdrop-blur-md outline-none bg-violet-500/20 text-violet-400 border-violet-500/40 shadow-[0_0_15px_rgba(139,92,246,0.2)] hover:bg-violet-500/30 hover:border-violet-400/60 hover:scale-105"
           title="Merge Character"
           aria-label="Merge {{ $character->name }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h3m5 0h-3M12 7v10m-4-4 4 4 4-4"></path>
            </svg>
        </a>

        <button type="button"
    @click.prevent.stop="deleteCharacterCard({{ $character->id }}, @js($character->name))"
    class="pointer-events-auto w-8 h-8 rounded-xl border flex items-center justify-center transition-all duration-300 backdrop-blur-md outline-none bg-rose-500/20 text-rose-400 border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.2)] hover:bg-rose-500/30 hover:border-rose-400/60 hover:scale-105"
    title="Delete Character"
    aria-label="Delete {{ $character->name }}">
    </div>

    <div class="absolute top-3 right-3 z-30 pointer-events-auto">
        <button type="button" 
            @click.prevent.stop="window.toggleRatingDropdown({{ $character->id }}, $event)" 
            class="h-8 px-3 rounded-xl border backdrop-blur-md transition-all duration-300 flex items-center justify-center font-black text-[10px] uppercase tracking-widest outline-none group/rating hover:scale-105 {{ $currentRating['class'] }}" 
            data-rating="{{ $character->rating }}">
            <span class="rating-text drop-shadow-md">{{ $currentRating['text'] }}</span>
        </button>
    </div>

    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent pointer-events-none z-10 opacity-80 group-hover:opacity-100 transition-opacity duration-500"></div>

    <div class="absolute inset-x-0 bottom-0 p-5 pointer-events-none z-20 flex flex-col justify-end">
        <h3 class="text-white font-black text-base sm:text-lg leading-tight line-clamp-1 group-hover:text-cyan-400 transition-colors duration-300 drop-shadow-md" itemprop="name">
            {{ $character->name }}
        </h3>
        
        @if($seriesName)
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-400 mt-1.5 line-clamp-1 group-hover:text-cyan-300 transition-colors duration-300" itemprop="memberOf">
                {{ $seriesName }}
            </p>
        @endif
    </div>
</article>
                @empty
                    <div class="col-span-full w-full flex flex-col items-center justify-center py-20 px-4 text-center bg-slate-900/40 border border-dashed border-slate-700 rounded-[2rem] backdrop-blur-md shadow-inner">
                        <div class="w-20 h-20 mb-5 rounded-full bg-slate-800 flex items-center justify-center">
                            <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a6 6 0 00-6 6h12a6 6 0 00-6-6z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-white mb-2">No Characters Found</h3>
                        <p class="text-sm font-medium text-slate-500 max-w-sm mx-auto">We couldn't find any characters matching your current filter criteria. Try adjusting your search or clearing the filters.</p>
                    </div>
                @endforelse
            </div>

            @if($characters->hasPages())
                {{ $characters->withQueryString()->links('components.pagination') }}
            @endif
        </div>
    </main>
    
    <x-footer />

    <script>
    function deleteCharacterCard(characterId, characterName) {
        return window.__deleteCharacterCard(characterId, characterName);
    }

    window.__deleteCharacterCard = async function(characterId, characterName) {
        if (!ensureSwal()) return;

        const result = await Swal.fire({
            title: 'Delete Character?',
            text: 'Character "' + characterName + '" and its relationships will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            background: '#020617',
            color: '#e2e8f0'
        });

        if (!result.isConfirmed) return;

        try {
            const urlTemplate = "{{ route('characters.delete', ['id' => '__ID__']) }}";
            const url = urlTemplate.replace('__ID__', characterId);

            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await parseJsonSafe(response);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to delete character.');
            }

            const element = document.getElementById('character-card-' + characterId);
            if (element) {
                element.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                element.style.opacity = '0';
                element.style.transform = 'scale(0.96)';
                setTimeout(function() { element.remove(); }, 260);
            }

            showToast('success', data.message, '#052e16', '#d1fae5');
        } catch (error) {
            console.error('Delete error:', error);
            showToast('error', error.message || 'Failed to delete character.', '#450a0a', '#fee2e2');
        }
    };
</script>

    <script>
    var metaTag = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = metaTag ? metaTag.getAttribute('content') : '';

    function ensureSwal() {
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded.');
            alert('SweetAlert2 is not loaded. Check <x-assets />.');
            return false;
        }
        return true;
    }

    function adminModal(options) {
        return Swal.fire({
            theme: 'dark',
            background: '#020617',
            color: '#e2e8f0',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-[1.5rem] border border-white/10 shadow-2xl',
                title: 'text-white text-xl font-black',
                htmlContainer: 'text-slate-300 text-sm',
                actions: 'gap-3',
                confirmButton: 'px-5 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-white font-bold transition-all',
                cancelButton: 'px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold transition-all'
            },
            ...options
        });
    }

    function showToast(icon, title, background, color) {
        if (!ensureSwal()) return;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
            background: background || '#0f172a',
            color: color || '#e2e8f0',
            theme: 'dark'
        });
    }

    async function parseJsonSafe(response) {
        const text = await response.text();
        try {
            return text ? JSON.parse(text) : {};
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw new Error('Server did not return valid JSON.');
        }
    }

    window.deleteCharacterCard = async function(characterId, characterName) {
        if (!ensureSwal()) return;

        const urlTemplate = "{{ route('characters.delete', ['id' => '__ID__']) }}";
        const url = urlTemplate.replace('__ID__', characterId);

        const result = await adminModal({
            title: 'Delete Character?',
            html: `
                <div class="text-left">
                    <p class="text-slate-300">
                        Character <span class="font-bold text-rose-400">"${characterName}"</span>
                        dan seluruh relasinya akan dihapus permanen.
                    </p>
                </div>
            `,
            icon: 'warning',
            iconColor: '#f43f5e',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#f43f5e',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: async () => {
                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await parseJsonSafe(response);

                    if (!response.ok || !data.success) {
                        Swal.showValidationMessage(data.message || 'Failed to delete character.');
                        return false;
                    }

                    return data;
                } catch (error) {
                    Swal.showValidationMessage(error.message || 'Network error while deleting character.');
                    return false;
                }
            }
        });

        if (!result.isConfirmed || !result.value) return;

        const element = document.getElementById('character-card-' + characterId);
        if (element) {
            element.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
            element.style.opacity = '0';
            element.style.transform = 'scale(0.96)';
            setTimeout(function() { element.remove(); }, 260);
        }

        showToast('success', result.value.message || 'Character deleted.', '#052e16', '#d1fae5');
    };

    window.toggleRatingDropdown = async function(characterId, event) {
        if (!ensureSwal()) return;

        const button = event.currentTarget;
        const currentRating = button.getAttribute('data-rating');

        const ratingOptions = {
            general: 'General (GEN)',
            sensitive: 'Sensitive (SEN)',
            questionable: 'Questionable (QST)',
            explicit: 'Explicit (EXP)',
            unknown: 'Unknown (UNK)'
        };

        const urlTemplate = "{{ route('characters.update-rating', ['id' => '__ID__']) }}";
        const url = urlTemplate.replace('__ID__', characterId);

        const result = await adminModal({
            title: 'Change Character Rating',
            input: 'select',
            inputOptions: ratingOptions,
            inputValue: currentRating,
            inputPlaceholder: 'Select rating',
            showCancelButton: true,
            confirmButtonText: 'Save Rating',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            inputValidator: (value) => {
                if (!value) {
                    return 'Please select a rating.';
                }
            },
            preConfirm: async (selectedRating) => {
                try {
                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ rating: selectedRating })
                    });

                    const data = await parseJsonSafe(response);

                    if (!response.ok || !data.success) {
                        Swal.showValidationMessage(data.message || 'Failed to update rating.');
                        return false;
                    }

                    return {
                        rating: selectedRating,
                        message: data.message || 'Rating updated.'
                    };
                } catch (error) {
                    Swal.showValidationMessage(error.message || 'Network error while updating rating.');
                    return false;
                }
            }
        });

        if (!result.isConfirmed || !result.value) return;

        updateRatingButtonUI(button, result.value.rating);
        showToast('success', result.value.message, '#082f49', '#e0f2fe');
    };

    function updateRatingButtonUI(buttonElement, newRating) {
        const textSpan = buttonElement.querySelector('.rating-text');
        const baseClasses = "h-8 px-3 rounded-xl border backdrop-blur-md transition-all duration-300 flex items-center justify-center font-black text-[10px] uppercase tracking-widest outline-none group/rating hover:scale-105";

        const map = {
            general: {
                text: 'GEN',
                classes: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
            },
            sensitive: {
                text: 'SEN',
                classes: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30 shadow-[0_0_15px_rgba(234,179,8,0.2)]'
            },
            questionable: {
                text: 'QST',
                classes: 'bg-orange-500/20 text-orange-400 border-orange-500/30 shadow-[0_0_15px_rgba(249,115,22,0.2)]'
            },
            explicit: {
                text: 'EXP',
                classes: 'bg-rose-500/20 text-rose-400 border-rose-500/30 shadow-[0_0_15px_rgba(244,63,94,0.2)]'
            },
            unknown: {
                text: 'UNK',
                classes: 'bg-slate-500/20 text-slate-300 border-slate-500/30 shadow-[0_0_15px_rgba(100,116,139,0.2)]'
            }
        };

        const config = map[newRating] || map.unknown;
        buttonElement.className = baseClasses + ' ' + config.classes;
        textSpan.textContent = config.text;
        buttonElement.setAttribute('data-rating', newRating);
    }
</script>

    <script>
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('details.custom-dropdown');
            dropdowns.forEach(function(details) {
                if (!details.contains(event.target)) {
                    details.removeAttribute('open');
                }
            });
        });
    </script>
</body>
</html>