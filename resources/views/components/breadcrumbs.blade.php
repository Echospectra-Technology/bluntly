@props(['items' => []])

@if(count($items) > 1)
<nav aria-label="Breadcrumb" class="py-4 px-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600" itemscope itemtype="https://schema.org/BreadcrumbList">
        @foreach($items as $index => $item)
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                @if($loop->last)
                    <span class="text-gray-900 font-medium" itemprop="name">{{ $item['name'] }}</span>
                    <meta itemprop="position" content="{{ $index + 1 }}" />
                @else
                    <a href="{{ $item['url'] }}" 
                       class="hover:text-gray-900 transition-colors"
                       itemprop="item">
                        <span itemprop="name">{{ $item['name'] }}</span>
                    </a>
                    <meta itemprop="position" content="{{ $index + 1 }}" />
                    <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif