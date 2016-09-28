<?php

namespace RHildred\Editable;


use \Michelf\MarkdownExtra, \kbjr\Git;

class Markdown
{
    public static function render($sFname){
        echo '<div class="editable" id="' . str_replace('.', '_', $sFname) . '">' . MarkdownExtra::defaultTransform(file_get_contents($sFname)) . "</div>";

    }

    public static function save($sId, $sMarkdown, $sReferer){
        if(!isset($_SESSION["currentuser"])) throw new Exception('no user logged in');

        //convert to html
        $sHtml = MarkdownExtra::defaultTransform($sMarkdown);

        //save the file
        $sFname = str_replace('_', '.', $sId);
        $sDir = __DIR__ . '/../../../../../../www/';
        file_put_contents($sDir . $sFname, $sMarkdown);

        //delete refering file from cache
        $sIndex = $sDir . $sReferer . "." . date("Y") . ".html";
        if(file_exists($sIndex))unlink($sIndex);

        // now we need to commit the file
        putenv('DYLD_LIBRARY_PATH=/usr/lib/:$DYLD_LIBRARY_PATH');
        $repo = Git::open(__DIR__ . '/../../../../../..');  // -or- Git::create('/path/to/repo')
        $repo->add("www/" . $sFname);
        $repo->commit('web commit from ' . $_SESSION["currentuser"]->name);

        echo $sHtml;

    }
}
