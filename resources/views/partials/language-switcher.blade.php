{{-- resources/views/partials/language-switcher.blade.php --}}
{{-- navbar এ notification bell এর পাশে বসবে --}}

<style>
.lang-switch-wrap { position: relative; display: flex; align-items: center; list-style: none; }
.lang-switch-btn {
    background: none; border: none; color: #6b7280; padding: 6px 10px;
    cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; gap: 4px;
    line-height: normal;
}
.lang-switch-btn:hover { color: #1f2937; }
.lang-dropdown {
    position: absolute; right: 0; top: 100%; margin-top: 8px;
    width: 180px; background: #fff; border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15); z-index: 1050; display: none; overflow: hidden;
}
.lang-dropdown.show { display: block; }
.lang-item {
    display: flex; align-items: center; gap: 10px; padding: 9px 14px;
    font-size: .85rem; color: #374151; text-decoration: none; transition: background .15s;
}
.lang-item:hover { background: #f8fafc; color: #1f2937; text-decoration: none; }
.lang-item.active { background: #eff6ff; font-weight: 700; color: #2563eb; }
.lang-item .flag { font-size: 1.1rem; }
</style>

<li class="lang-switch-wrap nav-item" id="langSwitchWrap">
    <button class="lang-switch-btn" id="langSwitchBtn" title="{{ __('nav.language') }}">
        <i class="fas fa-globe"></i>
    </button>

    <div class="lang-dropdown" id="langDropdown">
        @foreach(\App\Http\Middleware\SetLocale::SUPPORTED_LOCALES as $code => $info)
        <a href="{{ route('language.switch', $code) }}" class="lang-item {{ app()->getLocale() === $code ? 'active' : '' }}">
            <span class="flag">{{ $info['flag'] }}</span>
            <span>{{ $info['native'] }}</span>
        </a>
        @endforeach
    </div>
</li>

<script>
(function () {
    const btn = document.getElementById('langSwitchBtn');
    const dropdown = document.getElementById('langDropdown');

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('langSwitchWrap');
        if (!wrap.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
})();
</script>
