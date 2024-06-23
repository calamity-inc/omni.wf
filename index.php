<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
	<title>Warframe Omni Tool</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="icon" href="https://browse.wf/Lotus/Interface/Icons/Categories/GrimoireModIcon.png">
	<style>
		.glyph-socials svg
		{
			fill: currentColor;
			height: 26px;
			margin-right: 5px;
		}
	</style>
</head>
<body data-bs-theme="dark">
	<?php require "components/navbar.php"; ?>
	<div class="container p-3">
		<p>It's like a search engine, but for space ninjas.<!-- Powered by <a href="https://browse.wf" target="_blank">browse.wf</a>.--></p>
		<input id="query" class="form-control" autofocus />
		<div id="results" class="mt-3"></div>
	</div>
	<script src="common.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min.js"></script>
	<script>
		const params = new URLSearchParams(location.hash.replace("#", ""));
		if (params.has("q"))
		{
			document.getElementById("query").value = params.get("q");
			document.getElementById("results").textContent = "Loading...";
		}

		document.getElementById("query").oninput = function()
		{
			document.getElementById("results").textContent = "Sorry, data is still downloading. Your query will be processed ASAP.";
		};

		Promise.all([
			getDictPromise(),
			fetch("https://browse.wf/warframe-public-export-plus/ExportWarframes.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportWeapons.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportUpgrades.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportResources.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportFlavour.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportCustoms.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportRewards.json").then(res => res.json()),
			fetch("https://browse.wf/warframe-public-export-plus/ExportRegions.json").then(res => res.json()),
			fetch("supplemental-data/glyphs.json").then(res => res.json())
			]).then(([
				dict,
				ExportWarframes,
				ExportWeapons,
				ExportUpgrades,
				ExportResources,
				ExportFlavour,
				ExportCustoms,
				ExportRewards,
				ExportRegions,
				supplementalGlyphData
			]) =>
		{
			window.dict = dict;
			window.dict_entries = Object.entries(window.dict).sort(([key1, value1], [key2, value2]) => value1.length - value2.length);
			window.ExportWarframes_entries = Object.entries(ExportWarframes);
			window.ExportWeapons_entries = Object.entries(ExportWeapons);
			window.ExportUpgrades_entries = Object.entries(ExportUpgrades);
			window.ExportResources_entries = Object.entries(ExportResources);
			window.ExportFlavour_entries = Object.entries(ExportFlavour);
			window.ExportCustoms_entries = Object.entries(ExportCustoms);
			window.ExportRewards_entries = Object.entries(ExportRewards);
			window.ExportRegions = ExportRegions;
			window.supplementalGlyphData = supplementalGlyphData;

			updateMissionDeckNames();

			if (document.getElementById("query").value)
			{
				doQuery(document.getElementById("query").value);
			}

			document.getElementById("query").oninput = function()
			{
				if (this.value.length < 2)
				{
					location.hash = "";
					document.getElementById("results").innerHTML = "";
				}
				else
				{
					location.hash = "q=" + encodeURIComponent(this.value);
					doQuery(this.value);
				}
			};

			onLanguageUpdate = function()
			{
				window.dict_entries = Object.entries(window.dict).sort(([key1, value1], [key2, value2]) => value1.length - value2.length);

				updateMissionDeckNames();

				const params = new URLSearchParams(location.hash.replace("#", ""));
				if (params.has("q"))
				{
					doQuery(params.get("q"));
				}
			};
		});

		function updateMissionDeckNames()
		{
			window.missionDeckNames = {
				"/Lotus/Types/Game/MissionDecks/SortieRewards": "Sortie",
				"/Lotus/Types/Game/MissionDecks/ArchonSortieRewards": "Archon Hunt",
			};
			Object.values(ExportRegions).forEach(region => {
				region.rewardManifests.forEach(deckName => {
					window.missionDeckNames[deckName] = dict[region.name] + " (" + dict[region.systemName] + ")";
				});
			});
		}

		function doQuery(query)
		{
			console.time("Input to language tags");
			let results = getDictEntriesFromQuery(query).reduce((arr, [key, value]) =>
			{
				arr.push({ type: "tag", key, value });
				return arr;
			}, []);
			console.timeEnd("Input to language tags");

			console.time("resolveTagsToUses");
			results = resolveTagsToUses(results);
			console.timeEnd("resolveTagsToUses");

			console.time("Sort results");
			results.sort((a, b) => {
				if (a.type == "warframe" && b.type != "warframe") {
					return -1;
				}
				if (a.type != "warframe" && b.type == "warframe") {
					return 1;
				}
				return (a.type == "tag") - (b.type == "tag");
			});
			console.timeEnd("Sort results");

			const tags_shown = {};
			document.getElementById("results").textContent = results.length == 0 ? "Found 0 results." : "";
			results.forEach(result =>
			{
				if (result.type == "tag" && (result.key in tags_shown))
				{
					return;
				}

				let root = document.createElement("div");
				root.className = "card mb-3";
				root = document.getElementById("results").appendChild(root);

				if (typeof result.value == "object"
					&& "icon" in result.value
					)
				{
					const row = root.appendChild(document.createElement("div"));
					row.className = "row g-0";
					{
						const col = document.createElement("div");
						col.className = "col-2";
						{
							const img = document.createElement("img");
							img.className = "img-fluid rounded-start";
							img.src = "https://browse.wf" + result.value.icon;
							col.appendChild(img);
						}
						row.appendChild(col);
					}
					{
						root = row.appendChild(document.createElement("div"));
						root.className = "col-10";
					}
				}

				root = root.appendChild(document.createElement("div"));
				root.className = "card-body";

				if (typeof result.value == "object"
					&& "name" in result.value
					)
				{
					const title = document.createElement("h5");
					title.className = "card-title";
					title.textContent = dict[result.value.name] + " ";
					{
						const a = document.createElement("a");
						a.textContent = "📖";
						a.title = "See other languages";
						a.href = "https://browse.wf" + result.value.name;
						a.target = "_blank";
						a.style.textDecoration = "none";
						title.appendChild(a);
					}
					root.appendChild(title);
				}

				{
					const subtitle = document.createElement("h6");
					subtitle.className = "card-subtitle mb-2 text-body-secondary";
					subtitle.textContent = result.key + " ";
					{
						const a = document.createElement("a");
						a.textContent = "📖";
						a.title = "View raw data";
						a.href = "https://browse.wf" + result.key;
						a.target = "_blank";
						a.style.textDecoration = "none";
						subtitle.appendChild(a);
					}
					root.appendChild(subtitle);
				}

				if (typeof result.value == "object"
					&& "description" in result.value
					)
				{
					tags_shown[result.value.description] = true;

					let p = document.createElement("p");
					p.className = "card-text";
					p.textContent = dict[result.value.description] + " ";
					{
						const a = document.createElement("a");
						a.textContent = "📖";
						a.title = "See other languages";
						a.href = "https://browse.wf" + result.value.description;
						a.target = "_blank";
						a.style.textDecoration = "none";
						p.appendChild(a);
					}
					root.appendChild(p);
				}

				if (result.type == "weapon")
				{
					let p = document.createElement("p");
					p.className = "card-text";
					p.innerHTML = "Riven Disposition: " + result.value.omegaAttenuation + "x (<span class='font-monospace'>●" + (result.value.omegaAttenuation >= 0.7 ? "●" : "○") + (result.value.omegaAttenuation >= 0.9 ? "●" : "○") + (result.value.omegaAttenuation >= 1.11 ? "●" : "○") + (result.value.omegaAttenuation >= 1.31 ? "●" : "○") + "</span>) &middot; ";
					{
						const a = document.createElement("a");
						a.textContent = "View stat ranges";
						a.href = "/rivencalc#weapon=" + encodeURIComponent(dict[result.value.name]);
						a.target = "_blank";
						a.style.textDecoration = "none";
						p.appendChild(a);
					}
					root.appendChild(p);
				}
				else if (result.type == "upgrade")
				{
					let p = document.createElement("p");
					p.className = "card-text";
					if (result.value.fusionLimit == 0)
					{
						p.textContent = "This mod can not be upgraded.";
					}
					else
					{
						p.textContent = "Max Rank: " + result.value.fusionLimit + " (" + ("●".repeat(result.value.fusionLimit)) + ")";
					}
					root.appendChild(p);
				}
				else if (result.type == "flavour")
				{
					let have_obtain_info = false;
					if (result.key in supplementalGlyphData)
					{
						if (supplementalGlyphData[result.key].promo_code)
						{
							have_obtain_info = true;

							let p = document.createElement("p");
							p.className = "card-text";
							p.textContent = "Promo Code: ";
							{
								let a = document.createElement("a");
								a.href = "https://www.warframe.com/promocode?code=" + supplementalGlyphData[result.key]?.promo_code;
								a.target = "_blank";
								a.textContent = supplementalGlyphData[result.key]?.promo_code;
								p.appendChild(a);
							}
							root.appendChild(p);

							p = document.createElement("p");
							p.className = "card-text";
							p.textContent = "If the promo code no longer works, ";
							{
								let a = document.createElement("a");
								a.href = "https://github.com/calamity-inc/omni.wf/issues";
								a.target = "_blank";
								a.textContent = "please let us know.";
								p.appendChild(a);
							}
							root.appendChild(p);
						}
						else if (supplementalGlyphData[result.key].twitch
							|| supplementalGlyphData[result.key].youtube
							|| supplementalGlyphData[result.key].discord
							|| supplementalGlyphData[result.key].twitter
							|| supplementalGlyphData[result.key].mixer
							|| supplementalGlyphData[result.key]["other-site"]
							|| supplementalGlyphData[result.key].markdown
							)
						{
							have_obtain_info = true;

							let p = document.createElement("p");
							p.className = "card-text";
							p.textContent = "The following information is user-contributed. ";
							{
								let a = document.createElement("a");
								a.href = "https://github.com/calamity-inc/omni.wf/issues";
								a.target = "_blank";
								a.textContent = "Report issues you find here.";
								p.appendChild(a);
							}
							root.appendChild(p);

							if (supplementalGlyphData[result.key].twitch
								|| supplementalGlyphData[result.key].youtube
								|| supplementalGlyphData[result.key].discord
								|| supplementalGlyphData[result.key].twitter
								|| supplementalGlyphData[result.key].mixer
								|| supplementalGlyphData[result.key]["other-site"]
								)
							{
								let p = document.createElement("p");
								p.className = "card-text glyph-socials";

								if (supplementalGlyphData[result.key].twitch)
								{
									let a = document.createElement("a");
									a.href = supplementalGlyphData[result.key].twitch;
									a.target = "_blank";
									a.title = "Twitch";
									a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M391.2 103.5H352.5v109.7h38.6zM285 103H246.4V212.8H285zM120.8 0 24.3 91.4V420.6H140.1V512l96.5-91.4h77.3L487.7 256V0zM449.1 237.8l-77.2 73.1H294.6l-67.6 64v-64H140.1V36.6H449.1z"/></svg>`;
									p.appendChild(a);
								}

								if (supplementalGlyphData[result.key].youtube)
								{
									let a = document.createElement("a");
									a.href = supplementalGlyphData[result.key].youtube;
									a.target = "_blank";
									a.title = "Youtube";
									a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z"/></svg>`;
									p.appendChild(a);
								}

								if (supplementalGlyphData[result.key].discord)
								{
									let a = document.createElement("a");
									a.href = supplementalGlyphData[result.key].discord;
									a.target = "_blank";
									a.title = "Discord";
									a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M524.5 69.8a1.5 1.5 0 0 0 -.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0 -1.9 .9 337.5 337.5 0 0 0 -14.9 30.6 447.8 447.8 0 0 0 -134.4 0 309.5 309.5 0 0 0 -15.1-30.6 1.9 1.9 0 0 0 -1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0 -.8 .7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4A487.7 487.7 0 0 0 176 479.9a1.9 1.9 0 0 0 2.1-.7A348.2 348.2 0 0 0 208.1 430.4a1.9 1.9 0 0 0 -1-2.6 321.2 321.2 0 0 1 -45.9-21.9 1.9 1.9 0 0 1 -.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9 .2c2.9 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1 -.2 3.1 301.4 301.4 0 0 1 -45.9 21.8 1.9 1.9 0 0 0 -1 2.6 391.1 391.1 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1 .7A486 486 0 0 0 610.7 405.7a1.9 1.9 0 0 0 .8-1.4C623.7 277.6 590.9 167.5 524.5 69.8zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2S193.1 219.1 222.5 219.1c29.7 0 53.3 26.8 52.8 59.2C275.3 311 251.9 337.6 222.5 337.6zm195.4 0c-29 0-52.8-26.6-52.8-59.2S388.4 219.1 417.9 219.1c29.7 0 53.3 26.8 52.8 59.2C470.7 311 447.5 337.6 417.9 337.6z"/></svg>`;
									p.appendChild(a);
								}

								if (supplementalGlyphData[result.key].twitter)
								{
									let a = document.createElement("a");
									a.href = supplementalGlyphData[result.key].twitter;
									a.target = "_blank";
									a.title = "X (formerly known as Twitter)";
									a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>`;
									p.appendChild(a);
								}

								if (supplementalGlyphData[result.key].mixer)
								{
									let a = document.createElement("a");
									a.href = supplementalGlyphData[result.key].mixer;
									a.target = "_blank";
									a.title = "Mixer";
									a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M114.6 76.1a45.7 45.7 0 0 0 -67.5-6.4c-17.6 16.2-19 43.5-4.8 62.8l91.8 123L41.8 379.6c-14.2 19.3-13.1 46.6 4.7 62.8A45.7 45.7 0 0 0 114 435.9L242.9 262.7a12.1 12.1 0 0 0 0-14.2zM470.2 379.6 377.9 255.5l91.8-123c14.2-19.3 12.8-46.6-4.8-62.8a45.7 45.7 0 0 0 -67.5 6.4l-128 172.1a12.1 12.1 0 0 0 0 14.2L398 435.9a45.7 45.7 0 0 0 67.5 6.4C483.4 426.2 484.5 398.8 470.2 379.6z"/></svg>`;
									p.appendChild(a);
								}

								if (supplementalGlyphData[result.key]["other-site"])
								{
									let a = document.createElement("a");
									a.href = supplementalGlyphData[result.key]["other-site"];
									a.target = "_blank";
									a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>`;
									p.appendChild(a);
								}

								root.appendChild(p);
							}

							{
								var converter = new showdown.Converter();
								converter.setOption("openLinksInNewWindow", true);
								converter.setOption("strikethrough", true);
								let div = document.createElement("div");
								div.className = "card-text";
								div.innerHTML = converter.makeHtml(supplementalGlyphData[result.key].markdown ?? "No instructions were provided. Check the socials above for more information.");
								root.appendChild(div);
							}
						}
					}

					if (!have_obtain_info
						&& result.key.substr(0, 48) == "/Lotus/Types/StoreItems/AvatarImages/FanChannel/"
						)
					{
						let p = document.createElement("p");
						p.className = "card-text";
						p.textContent = "If you know how to obtain this glyph, ";
						{
							let a = document.createElement("a");
							a.href = "https://github.com/calamity-inc/omni.wf/issues";
							a.target = "_blank";
							a.textContent = "please let us know.";
							p.appendChild(a);
						}
						root.appendChild(p);
					}
				}
				else if (result.type == "tag")
				{
					let p = document.createElement("p");
					p.className = "card-text";
					p.textContent = result.value + " ";
					{
						const a = document.createElement("a");
						a.textContent = "📖";
						a.title = "See other languages";
						a.href = "https://browse.wf" + result.key;
						a.target = "_blank";
						a.style.textDecoration = "none";
						p.appendChild(a);
					}
					root.appendChild(p);
				}

				if (result.type == "upgrade" || result.type == "resource")
				{
					const storeItem = "/Lotus/StoreItems/" + result.key.substring(7);
					const sources = [];
					ExportRewards_entries.forEach(([deckName, tiers]) =>
					{
						for (let i = 0; i != tiers.length; ++i)
						{
							for (const reward of tiers[i])
							{
								if (reward.type == storeItem
									&& reward.probability // ignoring relics for now
									)
								{
									if (deckName in missionDeckNames)
									{
										sources.push({
											deckName: missionDeckNames[deckName] ?? deckName,
											rotation: i,
											itemCount: reward.itemCount,
											probability: reward.probability
										});
									}
									else
									{
										console.warn("Mission deck has", dict[result.value.name], "but no human friendly name:", deckName);
									}
								}
							}
						}
					});
					if (sources.length != 0)
					{
						sources.sort((a, b) => (b.itemCount * b.probability) - (a.itemCount * a.probability));

						let ul = document.createElement("ul");
						sources.forEach(source => {
							let li = document.createElement("li");
							li.textContent = source.deckName + ", Rotation " + "ABCD"[source.rotation] + " gives " + source.itemCount + " @ " + (source.probability * 100).toFixed(2) + "%";
							ul.appendChild(li);
						});
						root.appendChild(ul);
					}
				}
			});
		}

		function getDictEntriesFromQuery(query)
		{
			let num_results = 0;
			query = query.toLowerCase();
			return dict_entries.filter(([key, value]) =>
			{
				value = value.toLowerCase();
				return value == query
					|| (
						(value.substr(0, query.length) == query
						|| value.indexOf(" " + query) !== -1
						)
						&& ++num_results < 100
						)
					;
			});
		}

		function resolveTagsToUses(results)
		{
			const res = [];
			for (const result of results)
			{
				if (result.type == "tag")
				{
					let entry = ExportWarframes_entries.find(([uniqueName, item]) => item.name == result.key);
					if (entry)
					{
						res.push({ type: "warframe", key: entry[0], value: entry[1] });
						continue;
					}
					entry = ExportWeapons_entries.find(([uniqueName, item]) => item.name == result.key && item.totalDamage != 0);
					if (entry)
					{
						res.push({ type: "weapon", key: entry[0], value: entry[1] });
						continue;
					}
					entry = ExportUpgrades_entries.find(([uniqueName, item]) => item.name == result.key);
					if (entry)
					{
						res.push({ type: "upgrade", key: entry[0], value: entry[1] });
						continue;
					}
					entry = ExportResources_entries.find(([uniqueName, item]) => item.name == result.key);
					if (entry)
					{
						res.push({ type: "resource", key: entry[0], value: entry[1] });
						continue;
					}
					entry = ExportFlavour_entries.find(([uniqueName, item]) => item.name == result.key);
					if (entry)
					{
						res.push({ type: "flavour", key: entry[0], value: entry[1] });
						continue;
					}
					entry = ExportCustoms_entries.find(([uniqueName, item]) => item.name == result.key);
					if (entry)
					{
						res.push({ type: "custom", key: entry[0], value: entry[1] });
						continue;
					}
				}
				res.push(result);
			}
			return res;
		}
	</script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
