<?php
function recursivelyDelete($path)
{
	if(substr($path, -1) == "/")
	{
		$path = substr($path, 0, -1);
	}
	if(!file_exists($path))
	{
		return;
	}
	if(is_dir($path))
	{
		foreach(scandir($path) as $file)
		{
			if(!in_array($file, [
				".",
				".."
			]))
			{
				recursivelyDelete($path."/".$file);
			}
		}
		rmdir($path);
	}
	else
	{
		unlink($path);
	}
}

if(is_file("Universal Bypass for Chromium-based browsers.zip"))
{
	unlink("Universal Bypass for Chromium-based browsers.zip");
}
if(is_dir(".firefox"))
{
	recursivelyDelete(".firefox");
}

echo "Indexing...\n";
$index = [];
function recursivelyIndex($dir)
{
	global $index;
	foreach(scandir($dir) as $f)
	{
		if(substr($f, 0, 1) != ".")
		{
			$fn = $dir."/".$f;
			if(is_dir($fn))
			{
				mkdir(".firefox/".$fn);
				recursivelyIndex($fn);
			}
			else
			{
				array_push($index, substr($fn, 2));
			}
		}
	}
}
mkdir(".firefox");
recursivelyIndex(".");

echo "Building...\n";
function createZip($file)
{
	$zip = new ZipArchive();
	$zip->open($file, ZipArchive::CREATE + ZipArchive::EXCL + ZipArchive::CHECKCONS) or die("Failed to create {$file}.\n");
	return $zip;
}
$build = createZip("Universal Bypass for Chromium-based browsers.zip");
foreach($index as $fn)
{
	if($fn == "README.md" || $fn == "injection_script.js" || $fn == "rules.json")
	{
		continue;
	}
	if($fn == "manifest.json")
	{
		$json = json_decode(file_get_contents($fn), true);
		unset($json["browser_specific_settings"]);
		$json["incognito"] = "split";
		$build->addFromString($fn, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}
	else
	{
		$build->addFile($fn, $fn);
	}
	copy($fn, ".firefox/".$fn);
}
$build->close();
passthru(".firefox_build.bat");
recursivelyDelete(".firefox");
