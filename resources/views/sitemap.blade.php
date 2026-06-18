<?= '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('listings.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach ($listings as $listing)
        <url>
            <loc>{{ route('listings.show', $listing) }}</loc>
            @if ($listing->updated_at)
                <lastmod>{{ $listing->updated_at->toIso8601String() }}</lastmod>
            @endif
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>
