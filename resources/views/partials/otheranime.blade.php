@if (!empty($otherAnime))
<div class="flex flex-wrap -mx-2" id="other-anime-list">
    @foreach ($otherAnime as $other)
        <div class="w-1/2 md:w-1/5 px-2 mb-4">
            <a href="/anime/{{ $other->id }}/{{ Str::slug($other->title) }}" class="block border p-2 h-full rounded-lg">
                <div class="h-full flex flex-col items-center">
                    <img src="{{ $other->picture }}" onerror="this.onerror=null; this.src='/img/notfound.gif';" alt="{{ $other->title }}" class="h-16 w-12 mb-2 mt-1 rounded">
                    <h5 class="text-center text-blue-600 dark:text-blue-400 hover:underline">{{ Str::limit($other->title, 40) }}</h5>
                    @auth
                        @if (!empty($other->list_status))
                            <span class="inline-block bg-gray-500 text-white text-sm rounded px-2 py-1 mt-auto">{{ $other->list_status }}</span>
                        @endif
                    @endauth
                </div>
            </a>
        </div>
    @endforeach
</div>
{{ $otherAnime->links('partials.other-anime-pagination') }}
@endif
