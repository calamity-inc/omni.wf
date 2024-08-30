<?php
$ext = empty($_DYNSTAT) ? ".php" : "";
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
	<div class="container-fluid">
		<a class="navbar-brand" href="/" <?php if ($_SERVER["REQUEST_URI"] == "/"): ?> onclick="event.preventDefault();" <?php endif; ?>>wfdata.io</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content" aria-controls="navbar-content" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbar-content">
			<div class="navbar-nav me-auto mb-2 mb-lg-0">
				<a class="nav-link<?=(substr($_SERVER["REQUEST_URI"], 0, 6) == "/arbys" ? " active" : ""); ?>" href="/arbys<?=$ext;?>">Arbitration Schedule</a>
				<a class="nav-link<?=(substr($_SERVER["REQUEST_URI"], 0, 10) == "/rivencalc" ? " active" : ""); ?>" href="/rivencalc<?=$ext;?>">Riven Calculator</a>
				<a class="nav-link<?=(substr($_SERVER["REQUEST_URI"], 0, 8) == "/profile" ? " active" : ""); ?>" href="/profile<?=$ext;?>">Profile Viewer</a>
				<?php if (true): ?>
				<a class="nav-link<?=(substr($_SERVER["REQUEST_URI"], 0, 7) == "/glyphs" ? " active" : ""); ?>" href="/glyphs<?=$ext;?>">Glyphs</a>
				<a class="nav-link<?=(substr($_SERVER["REQUEST_URI"], 0, 12) == "/prime-vault" ? " active" : ""); ?>" href="/prime-vault<?=$ext;?>">Prime Vault</a>
				<a class="nav-link<?=(substr($_SERVER["REQUEST_URI"], 0, 11) == "/text-icons" ? " active" : ""); ?>" href="/text-icons<?=$ext;?>">Text Icons</a>
				<?php else: ?>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle <?=((substr($_SERVER["REQUEST_URI"], 0, 7) == "/glyphs" || substr($_SERVER["REQUEST_URI"], 0, 11) == "/text-icons") ? " active" : ""); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Misc</a>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item <?=(substr($_SERVER["REQUEST_URI"], 0, 7) == "/glyphs" ? " active" : ""); ?>" href="/glyphs<?=$ext;?>">Glyphs</a></li>
						<li><a class="dropdown-item <?=(substr($_SERVER["REQUEST_URI"], 0, 12) == "/prime-vault" ? " active" : ""); ?>" href="/prime-vault<?=$ext;?>">Prime Vault</a></li>
						<li><a class="dropdown-item <?=(substr($_SERVER["REQUEST_URI"], 0, 11) == "/text-icons" ? " active" : ""); ?>" href="/text-icons<?=$ext;?>">Text Icons</a></li>
					</ul>
				</li>
				<?php endif; ?>
			</div>
			<div class="nav-item dropdown mb-2 mb-lg-0">
				<a id="lang-dropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">English</a>
				<ul class="dropdown-menu dropdown-menu-end">
					<li><a class="dropdown-item active" href="#" data-lang="en" onclick="event.preventDefault();setLanguage('en');">English</a></li>
					<li><a class="dropdown-item" href="#" data-lang="de" onclick="event.preventDefault();setLanguage('de');">Deutsch</a></li>
					<li><a class="dropdown-item" href="#" data-lang="es" onclick="event.preventDefault();setLanguage('es');">Español</a></li>
					<li><a class="dropdown-item" href="#" data-lang="fr" onclick="event.preventDefault();setLanguage('fr');">Français</a></li>
					<li><a class="dropdown-item" href="#" data-lang="it" onclick="event.preventDefault();setLanguage('it');">Italiano</a></li>
					<li><a class="dropdown-item" href="#" data-lang="ja" onclick="event.preventDefault();setLanguage('ja');">日本語</a></li>
					<li><a class="dropdown-item" href="#" data-lang="ko" onclick="event.preventDefault();setLanguage('ko');">한국어</a></li>
					<li><a class="dropdown-item" href="#" data-lang="pl" onclick="event.preventDefault();setLanguage('pl');">Polski</a></li>
					<li><a class="dropdown-item" href="#" data-lang="pt" onclick="event.preventDefault();setLanguage('pt');">Português</a></li>
					<li><a class="dropdown-item" href="#" data-lang="ru" onclick="event.preventDefault();setLanguage('ru');">Русский</a></li>
					<li><a class="dropdown-item" href="#" data-lang="tr" onclick="event.preventDefault();setLanguage('tr');">Türkçe</a></li>
					<li><a class="dropdown-item" href="#" data-lang="uk" onclick="event.preventDefault();setLanguage('uk');">Українська</a></li>
					<li><a class="dropdown-item" href="#" data-lang="zh" onclick="event.preventDefault();setLanguage('zh');">简体中文</a></li>
					<li><a class="dropdown-item" href="#" data-lang="tc" onclick="event.preventDefault();setLanguage('tc');">繁體中文</a></li>
					<li><a class="dropdown-item" href="#" data-lang="th" onclick="event.preventDefault();setLanguage('th');">แบบไทย</a></li>
				</ul>
			</div>
		</div>
	</div>
</nav>