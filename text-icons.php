<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
	<title>Text Icons | wfdata.io</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="icon" href="https://browse.wf/Lotus/Interface/Icons/Categories/GrimoireModIcon.png">
	<style id="scale-style">.icon{height:1em}</style>
</head>
<body data-bs-theme="dark">
	<?php require "components/navbar.php"; ?>
	<div class="container-fluid pt-3">
		<div class="row">
			<div class="col-6">
				<label for="platform" class="form-label">Platform</label>
				<select class="form-control" id="platform">
					<option value="DIT_AUTO">Agnostic</option>
					<option value="DIT_PC">PC</option>
					<option value="DIT_STEAM">Steam</option>
					<option value="DIT_PS4">PlayStation 4</option>
					<option value="DIT_PS5">PlayStation 5</option>
					<option value="DIT_XBONE">Xbox One</option>
					<option value="DIT_SWITCH">Switch</option>
					<option value="DIT_IOS">iOS</option>
				</select>
			</div>
			<div class="col-6">
				<label for="scale" class="form-label">Scale</label>
				<input id="scale" type="range" class="form-range" min="1.0" value="1.0" max="8.0" step="0.001" style="margin-top:6px" />
			</div>
		</div>
		<table class="w-100 mt-3 mb-3"></table>
	</div>
	<script src="common.js"></script>
	<script>
		fetch("https://browse.wf/warframe-public-export-plus/ExportTextIcons.json").then(res => res.json()).then(ExportTextIcons =>
		{
			window.ExportTextIcons = ExportTextIcons;

			updateTable();

			document.getElementById("platform").oninput = function()
			{
				updateTable();
			};
		});

		function updateTable()
		{
			const platform = document.getElementById("platform").value;

			document.querySelector("table").innerHTML = "";
			Object.entries(ExportTextIcons).forEach(([key, value]) =>
			{
				if (value[platform])
				{
					const tr = document.createElement("tr");
					{
						const td = document.createElement("td");
						td.style.float = "right";
						{
							const img = document.createElement("img");
							img.src = "https://browse.wf" + value[platform];
							img.className = "icon";
							td.appendChild(img);
						}
						tr.appendChild(td);
					}
					{
						const td = document.createElement("td");
						td.textContent = "<" + key + ">";
						tr.appendChild(td);
					}
					document.querySelector("table").appendChild(tr);
				}
			});
		}

		document.getElementById("scale").oninput = function()
		{
			document.getElementById("scale-style").innerHTML = ".icon{height:"+this.value+"em}";
		};
	</script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
