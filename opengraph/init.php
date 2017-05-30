<?php
class OpenGraph extends Plugin {
        function about() {
                return array(1.0,
                             "Adds Open Graph Meta tags to shared articles",
                             "Hypfer",
                             true,
                             "");
        }


        function init($host) {
                $host->add_hook($host::HOOK_FORMAT_ARTICLE, $this);
        }

        function api_version() {
                return 2;
        }

        function hook_format_article($content, $line, $zoom_mode) {

                if($zoom_mode) {
                        $doc = DOMDocument::loadHTML($content);
                        $head = $doc->getElementsByTagName('head');
                        if ($head->length==1) {
                                $head = $head->item(0);

                                //title
                                //There isn't any "hard" limit on length in the standard,
                                //however 150 chars seems reasonable to me for a title
                                $shortTitle = OpenGraph::shorten($line["title"], 150);

                                $titleElem = $doc->createElement("meta");
                                $titleElem->setAttribute("property","og:title");
                                $titleElem->setAttribute("content", $shortTitle);
                                $head->appendChild($titleElem);

                                //type
                                $typeElem = $doc->createElement("meta");
                                $typeElem->setAttribute("property", "og:type");
                                $typeElem->setAttribute("content", "article");
                                $head->appendChild($typeElem);

                                //description
                                //There isn't any "hard" limit on length in the standard either
                                //Facebook seems to use 300 chars or so.
                                $contentText = trim(preg_replace('/\s+/', ' ', strip_tags($line["content"])));
                                $shortContent = OpenGraph::shorten($contentText, 300);

                                $descriptionElem = $doc->createElement("meta");
                                $descriptionElem->setAttribute("property", "og:description");
                                $descriptionElem->setAttribute("content", $shortContent);
                                $head->appendChild($descriptionElem);

                                $descriptionMetaElem = $doc->createElement("meta");
                                $descriptionMetaElem->setAttribute("name", "description");
                                $descriptionMetaElem->setAttribute("content", $shortContent);
                                $head->appendChild($descriptionMetaElem);

                                //image
                                $images = DOMDocument::loadHTML($line["content"])->getElementsByTagName('img');
                                if($images->length >=1) {
                                        //Maybe check the size here?
                                        $img = $images->item(0);

                                        $imageElem = $doc->createElement("meta");
                                        $imageElem->setAttribute("property", "og:image");
                                        $imageElem->setAttribute("content",$img->getAttribute("src"));
                                        $head->appendChild($imageElem);
                                }

                                //og:url ?
                        }
                        return $doc->saveHTML();
                } else {
                        return $content;
                }
        }

        function shorten($string, $length) {
                return strlen($string) > $length ? substr($string,0,($length-3))."..." : $string;
        }
}
?>
