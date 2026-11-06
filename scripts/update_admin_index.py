with open('resources/views/admin/posts/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add tab button
tab_target = """                <a href="{{ route('admin.posts.index', ['tab' => 'clusters']) }}"
                    class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all inline-flex items-center gap-2 {{ $activeTab === 'clusters' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="layers" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Cluster Konten</span>
                    <span
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'clusters' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">{{ $totalClusters }}</span>
                </a>
            </div>"""

tab_repl = """                <a href="{{ route('admin.posts.index', ['tab' => 'clusters']) }}"
                    class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all inline-flex items-center gap-2 {{ $activeTab === 'clusters' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="layers" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Cluster Konten</span>
                    <span
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'clusters' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">{{ $totalClusters }}</span>
                </a>
                <a href="{{ route('admin.posts.index', ['tab' => 'trending']) }}"
                    class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all inline-flex items-center gap-2 {{ $activeTab === 'trending' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <i data-lucide="trending-up" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Google Trends</span>
                    <span
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'trending' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">{{ count($trendingReports[0]['topics'] ?? []) }}</span>
                </a>
            </div>"""

# 2. Add action button area for trending
action_target = """            @else
                <button type="button" onclick="openClusterModal()\""""

action_repl = """            @elseif($activeTab === 'trending')
                <div class="h-10 px-3.5 rounded-[12px] text-[12px] font-semibold text-black/60 dark:text-white/60 bg-black/5 dark:bg-white/10 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>10 Topik Bisnis Terhangat</span>
                </div>
            @else
                <button type="button" onclick="openClusterModal()\""""

# 3. Add tab content
content_target = """                    </table>
                </div>
            </div>
        @endif

    </div>"""

content_repl = """                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 4: GOOGLE TRENDS ARTICLES -->
        @if ($activeTab === 'trending')
            @include('admin.posts.trending.index')
        @endif

    </div>"""

c_norm = content.replace('\r\n', '\n')
assert tab_target.replace('\r\n', '\n') in c_norm, "tab_target not found"
c_norm = c_norm.replace(tab_target.replace('\r\n', '\n'), tab_repl.replace('\r\n', '\n'), 1)

assert action_target.replace('\r\n', '\n') in c_norm, "action_target not found"
c_norm = c_norm.replace(action_target.replace('\r\n', '\n'), action_repl.replace('\r\n', '\n'), 1)

assert content_target.replace('\r\n', '\n') in c_norm, "content_target not found"
c_norm = c_norm.replace(content_target.replace('\r\n', '\n'), content_repl.replace('\r\n', '\n'), 1)

with open('resources/views/admin/posts/index.blade.php', 'w', encoding='utf-8', newline='\r\n') as f:
    f.write(c_norm)

print("Updated resources/views/admin/posts/index.blade.php successfully!")
