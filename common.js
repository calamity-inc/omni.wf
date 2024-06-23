const this_page_nav_link = document.querySelector(".nav-link.active");
if (this_page_nav_link)
{
	this_page_nav_link.onclick = event => event.preventDefault();
}

// Localisation

function getDictPromise()
{
	return fetch("https://browse.wf/warframe-public-export-plus/dict." + (localStorage.getItem("lang") ?? "en") + ".json").then(res => res.json());
}

function setLanguage(code)
{
	setLanguageIndicator(code);

	fetch("https://browse.wf/warframe-public-export-plus/dict." + code + ".json").then(res => res.json()).then(function(dict)
	{
		window.dict = dict;
		if ("onLanguageUpdate" in window)
		{
			onLanguageUpdate();
		}
	});

	localStorage.setItem("lang", code);
}

function setLanguageIndicator(code)
{
	document.querySelectorAll(".dropdown-item[data-lang]").forEach(elm => elm.classList.remove("active"));
	const item = document.querySelector(".dropdown-item[data-lang=" + code + "]");
	item.classList.add("active");
	document.getElementById("lang-dropdown").textContent = item.textContent;
}

if (localStorage.getItem("lang"))
{
	setLanguageIndicator(localStorage.getItem("lang"));
}

// Text icons

function resolveTextIcons(text)
{
	return text.replaceAll(/<[^>]+>/g, (match) => {
		const name = match.split("<").join("").split(">").join("");
		if (ExportTextIcons[name]?.DIT_AUTO)
		{
			return "<img style='height:1em;position:relative;bottom:2px' src='https://browse.wf" + ExportTextIcons[name].DIT_AUTO + "' />";
		}
		//console.warn("Failed to resolve text icon:", name);
		return "&lt;" + name + "&gt;";
	});
}
