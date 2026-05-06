<?php

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'ignoreNonStrings' => false,
    'cachePath'     => storage_path('app/purifier'),
    'cacheFileMode' => 0755,

    'settings' => [
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src],*[style|class]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],

        // Used by the blog editor — covers exactly what the Tiptap toolbar produces:
        // headings (h2/h3 only, configured in StarterKit), bold/italic/strike, lists,
        // blockquote, inline code, code block, hard break, hr, links, images.
        // figure/figcaption and table elements omitted because HTMLPurifier doesn't
        // support them under HTML 4.01 without custom element definitions.
        'rich' => [
            'HTML.Doctype'        => 'HTML 4.01 Transitional',
            'HTML.Allowed'        => 'h2,h3,p,strong,em,u,s,blockquote,code,pre,ul,ol,li,a[href|target|rel],img[src|alt|title|width|height],br,hr',
            'HTML.TargetBlank'    => true,
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
            'URI.AllowedSchemes'  => ['http' => true, 'https' => true, 'mailto' => true],
        ],

        'titles' => [
            'HTML.Allowed' => 'b,strong,i,em,u',
        ],
    ],
];
