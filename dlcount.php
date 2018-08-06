<?php
/*****************************************************************************
  Counts release downloads on GitHub
  Copyright (C) 2018 Sylvain Hallé
 
  Usage: php dlcount.php [options] user1/repo1 [user2/repo2 ...]
  
  Options:
  --total    Only show total for all releases
  --image    Output an SVG of total download count to stdout
  
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *****************************************************************************/

/* A whitelist of repositories for which the script accepts queries.
   Used to avoid anybody querying *your* script for *their* repos. */
$REPO_WHITELIST = array(
	"user/repo"
);

/* The SVG image to output */
$SVG_IMG = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!-- Created with Inkscape (http://www.inkscape.org/) -->

<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   width="95.399063mm"
   height="13.415492mm"
   viewBox="0 0 95.399062 13.415492"
   version="1.1"
   id="svg8"
   inkscape:version="0.92.3 (2405546, 2018-03-11)"
   sodipodi:docname="dlimg.svg">
  <defs
     id="defs2" />
  <sodipodi:namedview
     id="base"
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1.0"
     inkscape:pageopacity="0.0"
     inkscape:pageshadow="2"
     inkscape:zoom="0.71"
     inkscape:cx="-231.6208"
     inkscape:cy="-289.25568"
     inkscape:document-units="mm"
     inkscape:current-layer="layer1"
     showgrid="false"
     fit-margin-top="0"
     fit-margin-left="0"
     fit-margin-right="0"
     fit-margin-bottom="0"
     inkscape:window-width="1920"
     inkscape:window-height="1052"
     inkscape:window-x="0"
     inkscape:window-y="0"
     inkscape:window-maximized="1" />
  <metadata
     id="metadata5">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <g
     inkscape:label="Layer 1"
     inkscape:groupmode="layer"
     id="layer1"
     transform="translate(-23.485384,-76.815521)">
    <rect
       y="76.815521"
       x="52.924938"
       height="13.415489"
       width="65.959511"
       id="rect854"
       style="fill:#e6e6e6;fill-opacity:1;stroke:none;stroke-width:0.30000001;stroke-linecap:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0" />
    <rect
       style="fill:#000080;fill-opacity:1;stroke:none;stroke-width:0.30000001;stroke-linecap:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0"
       id="rect844"
       width="29.439554"
       height="13.415492"
       x="23.485384"
       y="76.815521" />
    <text
       xml:space="preserve"
       style="font-style:normal;font-weight:normal;font-size:10.58333302px;line-height:1.25;font-family:sans-serif;letter-spacing:0px;word-spacing:0px;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
       x="25.469505"
       y="87.375748"
       id="text817"><tspan
         sodipodi:role="line"
         id="tspan815"
         x="25.469505"
         y="87.375748"
         style="stroke-width:0.26458332"><tspan
           style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-family:monospace;-inkscape-font-specification:'monospace Bold';fill:#ffffff"
           id="tspan819">XXXX</tspan></tspan></text>	
    <text
       id="text852"
       y="87.468758"
       x="57.523998"
       style="font-style:normal;font-weight:normal;font-size:10.58333302px;line-height:1.25;font-family:sans-serif;letter-spacing:0px;word-spacing:0px;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
       xml:space="preserve"><tspan
         style="stroke-width:0.26458332"
         y="87.468758"
         x="57.523998"
         id="tspan850"
         sodipodi:role="line">downloads<tspan
   id="tspan848"
   style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-family:monospace;-inkscape-font-specification:'monospace Bold';fill:#ffffff" /></tspan></text>
  </g>
</svg>
EOD;

$config = array(
	"only-total" => false,
	"generate-image" => false
);
$to_set = "";
$repos = array();
if (isset($argv))
{
	for ($i = 1; $i < count($argv); $i++)
	{
		$arg = $argv[$i];
		if ($arg === "--total")
		{
			$config["only-total"] = true;
		}
		else if ($arg === "--image")
		{
			$config["generate-image"] = true;
		}
		else
		{
			$repos[] = $arg;
		}
	}
}

if (isset($_REQUEST["repo"]))
{
	$config["generate-image"] = true;
	$config["only-total"] = true;
	if (in_array($_REQUEST["repo"], $REPO_WHITELIST))
	{
		$repos[] = $_REQUEST["repo"];
	}
}
if (empty($repos) && $config["generate-image"])
{
	// No repo or repo not in whitelist: Bad request
	http_response_code(404);
	//echo "This repository is not whitelisted.";
	exit(0);
}

$dl_cnt = 0;
foreach ($repos as $repo)
{
	list($user, $repo_name) = explode("/", $repo);
	printout("Stats for ".$user."/".$repo_name."\n\n");
	$gh_url = "https://api.github.com/repos/".$user."/".$repo_name."/releases";
	$contents = get_ssl_page($gh_url);
	$json = json_decode($contents);
	$dl_cnt = 0;
	foreach ($json as $release_nb => $release)
	{
		$r_dl = 0;
		if (!$config["only-total"])
			printout($release->name.":\n");
		foreach ($release->assets as $asset)
		{
			$r_dl += $asset->download_count;
			if (!$config["only-total"])
				printout("  ".$asset->name.": ".$asset->download_count."\n");
		}
		if (!$config["only-total"])
			printout("  Total: ".$r_dl."\n");
		$dl_cnt += $r_dl;
	}
	printout("TOTAL: $dl_cnt\n");
}

if ($config["generate-image"])
{
	if (isset($_REQUEST))
	{
		header("Content-Type: image/svg+xml");
	}
	$out_img = str_replace("XXXX", sprintf("%4d", $dl_cnt), $SVG_IMG);
	echo $out_img;
}

/**
 * Prints something to the standard output, only if the program is not
 * started from an HTTP request
 */
function printout($string)
{
	global $config;
	if ($config["generate-image"])
		return;
	echo $string;
}

/**
 * Crude way of getting an https:// URL
 */
function get_ssl_page($url) 
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP 7");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
// :tabWidth=4:folding=explicit:wrap=none:
?>