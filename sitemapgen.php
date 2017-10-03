<?php
include_once 'vendor/autoload.php';

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Carbon\Carbon;

// TODO: Put this in a class.

// ---- CONFIGURATION PARAMETERS ----

$url = ''; // FIXME: Your url for the sitemap.xml

$changeFreq = 'monthly';
$lastmod = Carbon::now()->toW3cString();
$fileNamesToInclude = '.html$|.htm$|.php$'; // IDEA: use an array, and then implode it with |
$fileNamesNotToInclude = '^mail.php$|^sitemapgen.php$';
$imageFoldersToExclude = 'vendor|assets';
$fileFoldersToExclude = 'vendor|assets|images|cgi-bin'; // IDEA: use an array, and then implode it with |

// ---- MAP/CRAWL IMAGES ----

$imgXML = "<url><loc>$url</loc>";

$imageFinder = new Finder();
// CHECK: Add ->notName('/' . $fileNamesToInclude . '/'); or not?
$images = $imageFinder->files()->in(__DIR__)->notPath('/' . $imageFoldersToExclude . '/');
foreach ($images as $img) {
    $mimeType = mime_content_type($img->getRealPath());
    if (preg_match('/image/i', $mimeType)) {
        $urlImagePath = $url . '/' . $img->getRelativePathName();
        $imgXML .= "<image:image><image:loc>" . $urlImagePath . "</image:loc></image:image>";
    }
}

$imgXML .= "<lastmod>$lastmod</lastmod><changefreq>$changeFreq</changefreq></url>";

// ---- MAP/CRAWL FILES ----

$fileXML = "";
$fileFolderFinder = new Finder();
$fileFolders = $fileFolderFinder->files()->in(__DIR__)
        ->name('/'.$fileNamesToInclude.'/')
    ->notName('/' . $fileNamesNotToInclude . '/');
foreach ($fileFolders as $ff) {
    ->notPath('/'.$fileFoldersToExclude.'/')
    $lastmod = Carbon::createFromTimestamp($ff->getMTime())->toW3cString();
    $filename = $ff->getRelativePathName();
    $fileXML .= "<url><loc>$filename</loc><lastmod>$lastmod</lastmod></url>";
}

// ---- CREATE THE XML STRING ----

$xml = '<?xml version="1.0" encoding="UTF-8"?>' .
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
    'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' .
    'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
    'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 ' .
    'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
$xml .= $imgXML . $fileXML . '</urlset>';

// ---- REMOVE/CREATE SITEMAP.XML ----

$fs = new Filesystem();
$fs->remove('sitemap.xml');
$fs->appendToFile('sitemap.xml', $xml);
