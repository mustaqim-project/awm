{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>Cooca Blog - Panduan Bisnis &amp; Kasir UMKM</title>
        <link>{{ url('/blog') }}</link>
        <description>Edukasi operasional bisnis, kalkulasi HPP, manajemen stok, dan software kasir POS untuk UMKM Indonesia.</description>
        <language>id-ID</language>
        <atom:link href="{{ url('/feed/articles.xml') }}" rel="self" type="application/rss+xml" />
        <image>
            <url>{{ asset('assets/image/1785229034_logo_dark.png') }}</url>
            <title>Cooca</title>
            <link>{{ url('/') }}</link>
        </image>
        @foreach ($posts as $post)
            <item>
                <title><![CDATA[{{ $post->title }}]]></title>
                <link>{{ route('blog.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
                <description><![CDATA[{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 200) }}]]></description>
                <content:encoded><![CDATA[
                    {!! $post->content !!}
                    <hr/>
                    <p>Diterbitkan pertama kali oleh <a href="{{ url('/') }}">Cooca - Software Kasir POS &amp; ERP UMKM Indonesia</a>. Dapatkan modul kasir gratis, pembukuan otomatis, dan kalkulator HPP di <a href="{{ url('/') }}">cooca.id</a>.</p>
                ]]></content:encoded>
                <pubDate>{{ $post->published_at ? $post->published_at->toRssString() : now()->toRssString() }}</pubDate>
                <author>support@cooca.id (Tim Edukasi Cooca)</author>
            </item>
        @endforeach
    </channel>
</rss>
